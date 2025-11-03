const {saveMedia} = require('../services/MediaService');
const loan = require("qrcode/lib/core/galois-field");

class MessageDataPreparer {
    /**
     * Normalize WhatsApp JID (e.g., 123@s.whatsapp.net → 123@c.us)
     */
    static normalizeJid(jid) {
        return jid?.replace('@s.whatsapp.net', '@c.us') || null;
    }

    /**
     * Get external chat identifier based on message type
     */
    static getExternalChatId(message) {
        if (message.isStatus) {
            return message.author; // story
        }

        if (message.from.endsWith('@g.us')) {
            return message.from; // group chat
        }

        // 1:1 chat - deterministic combination
        return [message.from, message.to].sort().join('_');
    }

    /**
     * Get contact identifier for different message types
     */
    static getContactIdentifierId(message, client) {
        if (message.isStatus) {
            return message.author; // Status/broadcast
        }

        if (message.from.endsWith('@g.us')) {
            return message.author; // Group chat
        }

        // 1:1 chat
        return message.from !== client.info.wid._serialized
            ? message.from
            : message.to;
    }

    /**
     * Determine chat type (individual or group)
     */
    static getChatType(message) {
        return message.id?.remote?.endsWith('@g.us') ? 2 : 1; // 1=individual, 2=group
    }

    /**
     * Get message direction
     */
    static getMessageDirection(message) {
        return message.fromMe ? 2 : 1; // 1=incoming, 2=outgoing
    }

    /**
     * Download and save message media
     */
    static async handleMedia(message, tenantId) {

        if (!message.hasMedia) {
            return null;
        }

        try {
            const media = await message.downloadMedia();
            return await saveMedia(media, tenantId);
        } catch (err) {
            console.error(`Error saving media for message ${message.id?._serialized}:`, err.message);
            return null;
        }
    }

    /**
     * Get quoted message ID if exists
     */
    static async getQuotedMessageId(message) {
        if (!message.hasQuotedMsg) {
            return null;
        }

        try {
            const quotedMsg = await message.getQuotedMessage();
            return quotedMsg.id._serialized;
        } catch (err) {
            console.error('Error getting quoted message:', err.message);
            return null;
        }
    }

    /**
     * Build message metadata
     */
    static buildMessageMetadata(message, quotedMsgId = null) {
        return {
            deviceType: message.deviceType,
            quotedMsgId,
            isForwarded: !!message.isForwarded,
        };
    }

    /**
     * Build base message data object
     */
    static buildMessageData(message, quotedMsgId, accountId) {
        return {
            external_message_id: message.id?._serialized,
            sender: this.normalizeJid(message.from),
            receiver: this.normalizeJid(message.to),
            body: message.body || null,
            caption: message.caption || null,
            sent_at: message.timestamp
                ? new Date(message.timestamp * 1000)
                : new Date(),
            has_media: !!message.hasMedia,
            is_forward: !!message.isForwarded,
            direction: this.getMessageDirection(message),
            platform_account_id: accountId,
            reply_to_message_id: quotedMsgId,
            status: message.ack,
            type: message.type,
            metadata: this.buildMessageMetadata(message, quotedMsgId),
        };
    }

    /**
     * Attach media info to message data
     */
    static attachMediaData(messageData, mediaInfo) {
        if (!mediaInfo) {
            return messageData;
        }

        messageData.mediaData = {
            local_path: mediaInfo.local_path,
            name: mediaInfo.name,
            mimetype: mediaInfo.mimetype,
            size: mediaInfo.size,
        };

        return messageData;
    }

    /**
     * Get contact title with fallbacks
     */
    static async getContactTitle(contact) {
        return contact.name || contact.pushname || contact.number;
    }

    /**
     * Prepare a single chat message (for batch sync)
     */
    static async prepareChatMessage(message, contact, tenantId, accountId) {
        const quotedMsgId = await this.getQuotedMessageId(message);
        const mediaInfo = await this.handleMedia(message, tenantId);

        let msgData = this.buildMessageData(message, quotedMsgId, accountId);
        msgData = this.attachMediaData(msgData, mediaInfo);

        return {
            chatId: message.fromMe ? message.to : message.from,
            contactId: contact.id?._serialized,
            msgData,
        };
    }

    /**
     * Prepare complete chat data with recent messages (for sync)
     */
    static async prepareChatData(chat, client, tenantId, accountId) {
        const messages = await chat.fetchMessages({limit: 5});

        const preparedMessages = [];

        const contact = await chat.getContact();

        for (const message of messages) {
            const {msgData} = await this.prepareChatMessage(message, contact, tenantId, accountId);
            preparedMessages.push(msgData);
        }


        const title = await this.getContactTitle(contact);

        const myWhatsappNumber = client.info.wid._serialized;

        const chatType = chat.id._serialized.endsWith('@g.us') ? 2 : 1;

        return {
            account_id: accountId,
            tenant_id: tenantId,
            external_identifier_id: chat.id._serialized,
            contact_identifier_id: contact.id?._serialized,
            contact_name: title,
            title,
            is_muted: chat.isMuted,
            unread_count: chat.unreadCount,
            type: chatType,
            is_story: false,
            platform: 'whatsapp',
            sent_at: new Date(chat.timestamp * 1000),
            whatsapp_account_number: myWhatsappNumber,
            messages: preparedMessages,
        };
    }

    /**
     * Prepare single message with complete chat context (for webhooks/events)
     */
    static async prepareSingleMessage(message, client, tenantId, accountId) {
        if (!message) {
            throw new Error('Message is required');
        }

        if (!tenantId || !accountId) {
            throw new Error('tenantId and accountId are required');
        }
        const chat = await message.getChat();
        // ✅ CORRECT: Use chat.id._serialized - This is ALWAYS the same for the conversation
        const chatId = chat.id._serialized;

        const quotedMsgId = await this.getQuotedMessageId(message);

        const mediaInfo = await this.handleMedia(message, tenantId);

        const chatType = this.getChatType(message);

        const isStory = message.isStatus;

        const myWhatsappNumber = client.info.wid._serialized;

        let contactId;
        if (isStory && message.author) {
            // ✅ Real sender for status messages
            contactId = message.author;
        } else {
            // ✅ Normal chat case
            contactId = message.from === myWhatsappNumber ? message.to : message.from;
        }

        const contact = await client.getContactById(contactId);

        const title = await this.getContactTitle(contact);

        let msgData = this.buildMessageData(message, quotedMsgId, accountId);

        msgData = this.attachMediaData(msgData, mediaInfo);

        return {
            account_id: accountId,
            tenant_id: tenantId,
            external_identifier_id: chatId,
            contact_identifier_id: contactId,
            contact_name: title,
            title: title,
            is_muted: chat.isMuted,
            unread_count: chat.unreadCount,
            type: chatType,
            is_story: isStory,
            platform: 'whatsapp',
            sent_at: message.timestamp
                ? new Date(message.timestamp * 1000)
                : new Date(),
            whatsapp_account_number: myWhatsappNumber,
            messages: [msgData],
        };
    }
}

module.exports = MessageDataPreparer;
