class WhatsAppConfig {
    static get() {
        return {
            laravelUrl: process.env.BACKEND_URL,
            apiToken: process.env.API_SECRET_TOKEN,
            mongoUrl: process.env.MONGO_URI,
            isDevelopment: true,
            puppeteerArgs: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--disable-extensions',
                '--disable-web-security',
                '--disable-features=IsolateOrigins,site-per-process',
                '--disable-blink-features=AutomationControlled'
            ],
            webVersion: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html',
            qrMaxRetries: 4,
            backupSyncIntervalMs: 60000,
            recentChatsDays: 7,
            chatFetchLimit: 10
        };
    }
}

module.exports = WhatsAppConfig;
