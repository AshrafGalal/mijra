const mongoose = require('mongoose');
const {MongoStore} = require('wwebjs-mongo');
const {MessageMedia} = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');
const WhatsAppConfig = require('../config/WhatsAppConfig');
const SessionManager = require('../session/SessionManager');
const AuthStrategyFactory = require('../auth/AuthStrategyFactory');
const ClientFactory = require('../client/ClientFactory');
const ClientEventHandler = require('../events/ClientEventHandler');
const LaravelApiService = require('./LaravelApiService');
const MessageDataPreparer = require("../utils/MessageDataPreparer");

class WhatsAppService {
    constructor() {
        this.clients = new Map();
        this.config = WhatsAppConfig.get();
        this.sessionManager = null;
        this.authStrategyFactory = null;
        this.clientFactory = null;
        this.laravelService = null;
        this.io = null;
        this.mongoStore = null;
    }

    async initialize() {
        console.log('Starting WhatsApp Service...');
        console.log(`Environment: ${this.config.isDevelopment ? 'DEVELOPMENT' : 'PRODUCTION'}`);
        console.log(`Auth Strategy: ${this.config.isDevelopment ? 'LocalAuth (fast)' : 'RemoteAuth (MongoDB)'}`);

        // Initialize services
        this.laravelService = new LaravelApiService(this.config.laravelUrl, this.config.apiToken);

        if (!this.config.isDevelopment) {
            await this._initializeProduction();
        }

        this.sessionManager = new SessionManager(this.config.isDevelopment, mongoose);
        this.authStrategyFactory = new AuthStrategyFactory(this.config.isDevelopment, this.mongoStore);
        this.clientFactory = new ClientFactory(this.authStrategyFactory);

        await this._restoreSessions();

        console.log('WhatsApp Service initialized ✅');
        console.log(`Active clients: ${this.clients.size}`);
    }

    async _initializeProduction() {
        console.log('Connecting to MongoDB...');
        await mongoose.connect(this.config.mongoUrl);
        this.mongoStore = new MongoStore({mongoose});
        console.log('MongoDB connected ✅');
        await new Promise(resolve => setTimeout(resolve, 2000));
    }

    async _restoreSessions() {
        const sessions = await this.sessionManager.listSessions();
        console.log(`Found ${sessions.length} session(s)`);

        for (const {sessionId, tenantId, accountId} of sessions) {
            console.log(`  - ${sessionId}`);
            console.log(`    Restoring session...`);

            this.getOrCreateClient(tenantId, accountId).catch(err => {
                console.error(`    Error restoring ${sessionId}:`, err.message);
            });

            const delay = this.config.isDevelopment ? 1000 : 2000;
            await new Promise(resolve => setTimeout(resolve, delay));
        }

        if (sessions.length === 0) {
            console.log('No saved sessions found. Fresh start.');
        }
    }

    async cleanup() {
        console.log('Cleaning up clients...');
        console.log('Preserving sessions for next restart');
        this.clients.clear();

        if (!this.config.isDevelopment && mongoose.connection.readyState === 1) {
            await mongoose.disconnect();
            console.log('MongoDB disconnected');
        }
    }

    setSocket(io) {
        this.io = io;
    }

    async getOrCreateClient(tenantId, accountId) {
        const sessionId = this.sessionManager.getSessionId(tenantId, accountId);

        // Return existing client if already in memory
        if (this.clients.has(sessionId)) {
            const existingClient = this.clients.get(sessionId);
            console.log(`Client already in memory: ${sessionId} (Ready: ${existingClient.isReady})`);
            return existingClient;
        }

        const sessionExists = await this.sessionManager.exists(sessionId);
        console.log(`Session exists for ${sessionId}: ${sessionExists}`);

        try {
            const client = this.clientFactory.create(tenantId, accountId, sessionId);
            // Setup event handlers
            const eventHandler = new ClientEventHandler(
                client,
                this.laravelService,
                this.sessionManager,
                this.io,
            );
            eventHandler.attachAllEvents();

            this.clients.set(sessionId, client);

            console.log(`Initializing client: ${sessionId}`);
            console.log(`Auth: ${this.config.isDevelopment ? 'LocalAuth' : 'RemoteAuth'}`);
            console.log(`Will ${sessionExists ? 'restore from' : 'create new'} session`);

            // Initialize in the background
            this._initializeClientAsync(client, sessionId);

            return client;
        } catch (error) {
            console.error(`Error creating client ${sessionId}:`, error);
            this.clients.delete(sessionId);
            throw error;
        }
    }

    _initializeClientAsync(client, sessionId) {
        setImmediate(async () => {
            try {
                await client.initialize();
                console.log(`✓ Client initialization completed for ${sessionId}`);
            } catch (initError) {
                if (this._isNavigationError(initError)) {
                    console.log(`⚠️ Navigation error for ${sessionId}, but client may still connect`);
                } else {
                    console.error(`Error initializing ${sessionId}:`, initError.message);
                    this.clients.delete(sessionId);
                }
            }
        });
    }

    _isNavigationError(error) {
        const navErrors = ['Navigation', 'Execution context', 'destroyed'];
        return navErrors.some(err => error.message.includes(err));
    }

    getClient(tenantId, accountId) {
        const sessionId = this.sessionManager.getSessionId(tenantId, accountId);
        return this.clients.get(sessionId);
    }

    async sendMessage(tenantId, accountId, to, message, mediaPath = null, replyToMessageId = null) {
        const client = this.getClient(tenantId, accountId);
        if (!client) {
            throw new Error('Client not found');
        }

        const chatId = to.includes('@c.us') ? to : `${to}@c.us`;

        client._sendingViaApi = true;

        let options = {};
        let msg = null;

        if (replyToMessageId) {
            options.quotedMessageId = replyToMessageId;
        }
        // If sending media
        console.log('media exists or not :', fs.existsSync(mediaPath));

        if (mediaPath && fs.existsSync(mediaPath)) {
            const fullPath = path.resolve(mediaPath);
            const media = MessageMedia.fromFilePath(fullPath);
            // Add caption if provided
            if (message) options.caption = message;

            msg = await client.sendMessage(chatId, media, options);
        } else {
            // Text-only message
            msg = await client.sendMessage(chatId, message, options);
        }

        return MessageDataPreparer.prepareSingleMessage(msg, client, tenantId, accountId);
    }


    async syncRecentChats(tenantId, accountId) {
        const client = this.getClient(tenantId, accountId);
        const chats = await client.getChats();
        const sevenDaysAgo = Date.now() - 2 * 24 * 60 * 60 * 1000;

        const recentChats = chats.filter(chat => {
            const lastMsgTime = chat.timestamp ? chat.timestamp * 1000 : 0;
            return lastMsgTime >= sevenDaysAgo;
        });

        for (const chat of recentChats) {
            try {
                const chatData = await MessageDataPreparer.prepareChatData(
                    chat,
                    client,
                    tenantId,
                    accountId
                );
                this.laravelService.send(`/${tenantId}/whatsapp/sync`, chatData);
            } catch (err) {
                console.error(`❌ Failed to prepare chat ${chat.id._serialized}:`, err.message);
                return;
            }
        }
        this.laravelService.send(`/${tenantId}/whatsapp/${accountId}/sync/finished`);

    }


    async sendReaction(req) {
        const {tenant_id, account_id, externalMessageId, reaction} = req.body;

        const client = this.getClient(tenant_id, account_id);
        try {
            // Get the message object directly by ID
            const message = await client.getMessageById(externalMessageId);
            // Send reaction
            await message.react(reaction);
            return true;
        } catch (error) {
            console.error('Failed to send reaction:', error);
            return false;
        }
    }

    async logout(tenantId, accountId) {
        const client = this.getClient(tenantId, accountId);
        if (!client) return;

        await client.logout();
        await this.sessionManager.delete(client.sessionId);
        this.clients.delete(client.sessionId);
    }
}

module.exports = new WhatsAppService();
