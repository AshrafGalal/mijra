const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const WhatsAppRoutes = require('./routes/whatsapp.routes');

class Application {
    constructor(whatsappService) {
        this.app = express();
        this.server = http.createServer(this.app);
        this.io = this._setupSocketIO();
        this.whatsappService = whatsappService;
        this.port = process.env.PORT || 4000;

        this._setupMiddleware();
        this._setupRoutes();
    }

    _setupMiddleware() {
        this.app.use(express.json());
    }

    _setupSocketIO() {
        return new Server(this.server, {
            cors: {
                origin: "*",
                methods: ["GET", "POST"]
            }
        });
    }

    _setupRoutes() {
        const WhatsAppController = require('./controllers/WhatsappController');
        const controller = new WhatsAppController(this.whatsappService);
        const routes = new WhatsAppRoutes(controller);

        this.app.use('/', routes.getRouter());

        // Health check endpoint
        this.app.get('/health', (req, res) => {
            res.json({
                status: 'ok',
                uptime: process.uptime(),
                timestamp: new Date().toISOString()
            });
        });
    }

    async start() {
        // Connect socket.io to WhatsApp service
        this.whatsappService.setSocket(this.io);

        // Initialize WhatsApp service
        await this.whatsappService.initialize();

        // Start server
        this.server.listen(this.port, () => {
            console.log(`🚀 Server running on port ${this.port}`);
        });
    }

    async shutdown() {
        console.log('Shutting down gracefully...');

        await this.whatsappService.cleanup();

        this.server.close(() => {
            console.log('✓ Server closed');
            process.exit(0);
        });
    }

    getServer() {
        return this.server;
    }
}

module.exports = Application;
