const qrcode = require('qrcode-terminal');
const MessageDataPreparer = require('../utils/MessageDataPreparer');

class ClientEventHandler {
    constructor(client, laravelService, sessionManager, io) {
        this.client = client;
        this.laravelService = laravelService;
        this.sessionManager = sessionManager;
        this.io = io;

    }

    attachAllEvents() {
        this._onLoadingScreen();
        this._onQR();
        this._onReady();
        this._onAuthenticated();
        this._onAuthFailure();
        this._onDisconnected();
        this._onMessage();
        this._onMessageCreate();
        this._onMessageAck();
        this._onRemoteSessionSaved();
        this._onMessageReaction();
    }

    _onLoadingScreen() {
        this.client.on('loading_screen', (percent, message) => {
            console.log(`Loading ${this.client.sessionId}: ${percent}% - ${message}`);
        });
    }

    _onQR() {
        this.client.on('qr', async (qr) => {
            console.log(`⚠️ QR Code for ${this.client.sessionId}`);
            qrcode.generate(qr, {small: true});
            this.client.qrCode = qr;

            if (this.io) {
                this.io.emit('qr', {
                    tenant_id: this.client.tenantId,
                    account_id: this.client.accountId,
                    qr,
                });
            }
        });
    }

    _onReady() {
        this.client.on('ready', async () => {
            console.log(`✅ ${this.client.sessionId} is ready`);
            this.client.isReady = true;
            this.client.qrCode = null;
        });
    }


    _onAuthenticated() {
        this.client.on('authenticated', async () => {
            console.log(`✅ ${this.client.sessionId} authenticated`);
        });
    }

    _onAuthFailure() {
        this.client.on('auth_failure', async (msg) => {
            console.error(`❌ Auth failure for ${this.client.sessionId}:`, msg);
            await this.sessionManager.delete(this.client.sessionId);
        });
    }

    _onDisconnected() {
        this.client.on('disconnected', async (reason) => {
            console.log(`❌ ${this.client.sessionId} disconnected: ${reason}`);
            this.client.isReady = false;

            if (reason === 'LOGOUT') {
                await this.sessionManager.delete(this.client.sessionId);
            }
        });
    }

    _onMessage() {
        this.client.on('message', async (message) => {
            if (message.fromMe) return;
            console.log('message incoming event')
            const {tenantId, accountId} = this.client;
            const preparedData = await MessageDataPreparer.prepareSingleMessage(
                message,
                this.client,
                tenantId,
                accountId
            );

            await this.laravelService.send(`/${tenantId}/whatsapp/received`, preparedData);
        });
    }

    _onMessageCreate() {
        this.client.on('message_create', async (message) => {
            if (!message.fromMe) return;
            // 🛑 Skip messages we already sent (avoid duplicate handling)

            if (this.client._sendingViaApi) {
                this.client._sendingViaApi = false;
                return;
            }

            const {tenantId, accountId} = this.client;
            const preparedData = await MessageDataPreparer.prepareSingleMessage(
                message,
                this.client,
                tenantId,
                accountId
            );
            await this.laravelService.send(`/${tenantId}/whatsapp/received`, preparedData);
        });
    }

    _onMessageAck() {
        this.client.on('message_ack', (message) => {
            try {
                const external_message_id = message.id?._serialized;
                const {tenantId} = this.client;


                console.log('external_message_id', external_message_id);
                setTimeout(() => {
                    this.laravelService.send(`/${tenantId}/whatsapp/messages/ack`, {
                        external_message_id,
                        ack: message.ack,
                    });
                }, 2000);
            } catch (error) {
                console.error('Error sending ACK to Laravel:', error.message);
            }
        });
    }

    _onMessageReaction() {
        this.client.on('message_reaction', (reaction) => {
            try {
                const {tenantId} = this.client;
                const msgId = reaction.msgId?._serialized || null;
                const sender = reaction.senderId?._serialized || reaction.senderId || null;
                const emoji = reaction.reaction || reaction.text;

                console.log(`💬 Reaction detected: ${emoji} on message ${msgId} by ${sender}`);
                const myId = this.client.info?.wid?._serialized; // your own WhatsApp ID
                if (sender === myId) {
                    console.log('Ignoring self-reaction');
                    return;
                }

                setTimeout(() => {
                    this.laravelService.send(`/${tenantId}/whatsapp/messages/reaction`, {
                        external_message_id: msgId,
                        emoji: emoji,
                    });
                }, 2000);
            } catch (error) {
                console.error('Error message_reaction to Laravel:', error.message);
            }
        });
    }

    _onRemoteSessionSaved() {
        if (!this.sessionManager.isDevelopment) {
            this.client.on('remote_session_saved', async () => {
                console.log(`💾 Remote session saved for ${this.client.sessionId}`);
            });
        }
    }
}

module.exports = ClientEventHandler;
