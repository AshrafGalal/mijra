const fs = require('fs');
const path = require('path');

class SessionManager {
    constructor(isDevelopment, mongoose = null) {
        this.isDevelopment = isDevelopment;
        this.mongoose = mongoose;
        this.sessionsPath = path.join(process.cwd(), '.wwebjs_auth');
    }

    getSessionId(tenantId, accountId) {
        return `${tenantId}_${accountId}`;
    }

    async exists(sessionId) {
        if (this.isDevelopment) {
            return this._localSessionExists(sessionId);
        }
        return await this._remoteSessionExists(sessionId);
    }

    async delete(sessionId) {
        if (this.isDevelopment) {
            return this._deleteLocalSession(sessionId);
        }
        return await this._deleteRemoteSession(sessionId);
    }

    async listSessions() {
        if (this.isDevelopment) {
            return this._listLocalSessions();
        }
        return await this._listRemoteSessions();
    }

    // Private methods for local sessions
    _localSessionExists(sessionId) {
        const sessionPath = path.join(this.sessionsPath, `session-${sessionId}`);
        return fs.existsSync(sessionPath);
    }

    _deleteLocalSession(sessionId) {
        const sessionPath = path.join(this.sessionsPath, `session-${sessionId}`);
        try {
            if (fs.existsSync(sessionPath)) {
                fs.rmSync(sessionPath, { recursive: true, force: true });
                console.log(`✓ Deleted local session: ${sessionId}`);
                return true;
            }
        } catch (error) {
            console.error(`Error deleting local session ${sessionId}:`, error.message);
            return false;
        }
        return false;
    }

    _listLocalSessions() {
        if (!fs.existsSync(this.sessionsPath)) {
            return [];
        }

        return fs.readdirSync(this.sessionsPath)
            .filter(dir => dir.startsWith('session-'))
            .map(dir => dir.replace('session-', ''))
            .map(sessionId => {
                const [tenantId, accountId] = sessionId.split('_');
                return { sessionId, tenantId, accountId };
            })
            .filter(s => s.tenantId && s.accountId);
    }

    // Private methods for remote sessions
    async _remoteSessionExists(sessionId) {
        try {
            const db = this.mongoose.connection.db;
            const filesCollection = db.collection(`whatsapp-RemoteAuth-${sessionId}.files`);
            const fileCount = await filesCollection.countDocuments();
            return fileCount > 0;
        } catch (error) {
            return false;
        }
    }

    async _deleteRemoteSession(sessionId) {
        try {
            const db = this.mongoose.connection.db;
            await db.dropCollection(`whatsapp-RemoteAuth-${sessionId}.files`);
            await db.dropCollection(`whatsapp-RemoteAuth-${sessionId}.chunks`);
            console.log(`✓ Deleted remote session: ${sessionId}`);
            return true;
        } catch (error) {
            console.error(`Error deleting remote session ${sessionId}:`, error.message);
            return false;
        }
    }

    async _listRemoteSessions() {
        const db = this.mongoose.connection.db;
        const collections = await db.listCollections().toArray();

        return collections
            .filter(c => c.name.includes('RemoteAuth') && c.name.endsWith('.files'))
            .map(c => {
                const match = c.name.match(/whatsapp-RemoteAuth-(.+)\.files$/);
                if (match) {
                    const sessionId = match[1];
                    const [tenantId, accountId] = sessionId.split('_');
                    return { sessionId, tenantId, accountId };
                }
                return null;
            })
            .filter(s => s && s.tenantId && s.accountId);
    }
}

module.exports = SessionManager;
