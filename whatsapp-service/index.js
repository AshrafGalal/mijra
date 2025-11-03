require('dotenv').config();
const whatsappService = require('./src/services/WhatsappService');
const Application = require('./src/app');
const ErrorHandler = require('./src/utils/ErrorHandler');

// Setup global error handlers
process.on('uncaughtException', ErrorHandler.handleUncaughtException);
process.on('unhandledRejection', ErrorHandler.handleUnhandledRejection);

// Create and start application
const app = new Application(whatsappService);

app.start().catch(error => {
    console.error('Failed to start application:', error);
    process.exit(1);
});

// Graceful shutdown handlers
process.on('SIGTERM', () => app.shutdown());
process.on('SIGINT', () => app.shutdown());
