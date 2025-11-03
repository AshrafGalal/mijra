class ErrorHandler {
    static isNavigationError(error) {
        const navErrors = ['Navigation', 'Execution context', 'destroyed'];
        return error.message && navErrors.some(err => error.message.includes(err));
    }

    static handleUncaughtException(error) {
        if (ErrorHandler.isNavigationError(error)) {
            console.log('⚠️ Caught navigation error, but service continues running');
            console.log('   The WhatsApp client may still connect successfully');
        } else {
            console.error('❌ Uncaught Exception:', error);
            // For critical errors, you might want to exit
            // process.exit(1);
        }
    }

    static handleUnhandledRejection(reason, promise) {
        if (reason && ErrorHandler.isNavigationError(reason)) {
            console.log('⚠️ Caught unhandled navigation rejection, but service continues');
        } else {
            console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
        }
    }
}

module.exports = ErrorHandler;
