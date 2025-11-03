const { Client } = require('whatsapp-web.js');
const WhatsAppConfig = require('../config/WhatsAppConfig');

class ClientFactory {
    constructor(authStrategyFactory) {
        this.authStrategyFactory = authStrategyFactory;
    }

    create(tenantId, accountId, sessionId) {
        const config = WhatsAppConfig.get();
        const authStrategy = this.authStrategyFactory.create(sessionId);

        const client = new Client({
            authStrategy,
            puppeteer: {
                headless: true,
                args: config.puppeteerArgs,
                timeout: 0
            },
            webVersionCache: {
                type: 'remote',
                remotePath: config.webVersion,
            },
            authTimeoutMs: 0,
            qrMaxRetries: config.qrMaxRetries
        });

        // Attach metadata
        client.sessionId = sessionId;
        client.tenantId = tenantId;
        client.accountId = accountId;
        client.isReady = false;
        client.qrCode = null;

        return client;
    }
}

module.exports = ClientFactory;
