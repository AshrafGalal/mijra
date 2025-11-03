class AuthMiddleware {
    static verify(req, res, next) {
        const authHeader = req.headers.authorization;
        const expectedToken = `Bearer ${process.env.API_SECRET_TOKEN}`;

        if (!authHeader || authHeader !== expectedToken) {
            return res.status(401).json({
                success: false,
                message: "Unauthorized access to WhatsApp service API endpoint - Invalid API token"
            });
        }

        next();
    }

    static validateInitialize(req, res, next) {
        const { tenant_id, account_id } = req.body;

        if (!tenant_id || !account_id) {
            return res.status(400).json({
                success: false,
                error: 'tenant_id and account_id are required'
            });
        }

        next();
    }

    static validateSendMessage(req, res, next) {
        const { tenant_id, account_id, to, body, message_id } = req.body;

        if (!tenant_id || !account_id || !to) {
            return res.status(400).json({
                success: false,
                error: 'tenant_id, account_id, and to are required'
            });
        }

        next();
    }
}

module.exports = AuthMiddleware;
