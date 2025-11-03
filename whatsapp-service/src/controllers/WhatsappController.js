const MessageDataPreparer = require("../utils/MessageDataPreparer");

class WhatsAppController {
    constructor(whatsappService) {
        this.whatsappService = whatsappService;
    }

    async initialize(req, res) {
        try {
            console.log('Initializing new WhatsApp client...');
            const {tenant_id, account_id} = req.body;

            const client = await this.whatsappService.getOrCreateClient(tenant_id, account_id);

            // Client already authenticated
            if (client.isReady) {
                return res.json({
                    success: true,
                    message: 'Client already connected',
                    status: 'ready'
                });
            }

            // QR code available
            if (client.qrCode) {
                return res.json({
                    success: true,
                    message: 'QR code generated',
                    qr: client.qrCode,
                    status: 'qr_ready'
                });
            }

            // Still initializing
            res.json({
                success: true,
                message: 'QR code generating, please wait...',
                status: 'initializing'
            });
        } catch (error) {
            console.error('Initialize error:', error);
            res.status(500).json({
                success: false,
                error: error.message
            });
        }
    }

    getStatus(req, res) {
        const {tenant_id, account_id} = req.params;
        const client = this.whatsappService.getClient(tenant_id, account_id);

        if (!client) {
            return res.json({
                success: true,
                connected: false,
                ready: false,
                message: 'Client not initialized'
            });
        }

        res.json({
            success: true,
            connected: true,
            ready: client.isReady,
            qrCode: client.qrCode,
            message: client.isReady ? 'Client is ready' : 'Client is connecting'
        });
    }

    async sendMessage(req, res) {
        try {
            const {tenant_id, account_id, to, body, message_id, media_path, reply_to_message_id} = req.body;

            const message = await this.whatsappService.sendMessage(
                tenant_id,
                account_id,
                to,
                body,
                media_path,
                reply_to_message_id
            );

            res.status(200).json({
                success: true,
                message_id,
                messageData: message,
            });
        } catch (error) {
            console.error('Send message error:', error);
            res.status(500).json({
                success: false,
                error: error.message
            });
        }
    }

    async syncChats(req, res) {
        try {

            const {tenant_id, account_id} = req.body;
            this.whatsappService.syncRecentChats(tenant_id, account_id).catch(err => console.error('Sync error:', err));
            ;
            res.status(200).json({success: true});
        } catch (error) {
            console.error('Send message error:', error);
            res.status(500).json({
                success: false,
                error: error.message
            });
        }
    }


    async sendReaction(req, res) {
        try {
            console.log(req.body)
            await this.whatsappService.sendReaction(req);
            res.status(200).json({success: true});
        } catch (error) {
            console.error('Send message error:', error);
            res.status(500).json({
                success: false,
                error: error.message
            });
        }

    }

    async logout(req, res) {
        try {
            const {tenant_id, account_id} = req.params;

            await this.whatsappService.logout(tenant_id, account_id);

            res.json({
                success: true,
                message: 'Client logged out successfully'
            });
        } catch (error) {
            console.error('Logout error:', error);
            res.status(500).json({
                success: false,
                error: error.message
            });
        }
    }
}

module.exports = WhatsAppController;
