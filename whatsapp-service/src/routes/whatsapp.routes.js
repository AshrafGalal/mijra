const express = require('express');
const AuthMiddleware = require('../middleware/AuthMiddleware');

class WhatsAppRoutes {
    constructor(whatsappController) {
        this.router = express.Router();
        this.controller = whatsappController;
        this._setupRoutes();
    }

    _setupRoutes() {
        // Initialize WhatsApp client
        this.router.post(
            '/initialize',
            AuthMiddleware.verify,
            AuthMiddleware.validateInitialize,
            (req, res) => this.controller.initialize(req, res)
        );

// sync WhatsApp client
        this.router.post(
            '/chats/sync',
            AuthMiddleware.verify,
            AuthMiddleware.validateInitialize,
            (req, res) => this.controller.syncChats(req, res)
        );

        //send message react
        this.router.post(
            '/messages/react',
            AuthMiddleware.verify,
            AuthMiddleware.validateInitialize,
            (req, res) => this.controller.sendReaction(req, res)
        );

        // Send message
        this.router.post(
            '/messages/send',
            AuthMiddleware.verify,
            AuthMiddleware.validateSendMessage,
            (req, res) => this.controller.sendMessage(req, res)
        );

        // Logout
        this.router.post(
            '/logout/:tenant_id/:account_id',
            AuthMiddleware.verify,
            (req, res) => this.controller.logout(req, res)
        );
    }

    getRouter() {
        return this.router;
    }
}

module.exports = WhatsAppRoutes;
