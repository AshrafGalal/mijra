const { RemoteAuth, LocalAuth } = require('whatsapp-web.js');
const { MongoStore } = require('wwebjs-mongo');

class AuthStrategyFactory {
    constructor(isDevelopment, mongoStore = null) {
        this.isDevelopment = isDevelopment;
        this.mongoStore = mongoStore;
    }

    create(sessionId) {
        if (this.isDevelopment) {
            return new LocalAuth({ clientId: sessionId });
        }

        return new RemoteAuth({
            clientId: sessionId,
            dataPath: null,
            store: this.mongoStore,
            backupSyncIntervalMs: 60000
        });
    }
}

module.exports = AuthStrategyFactory;
