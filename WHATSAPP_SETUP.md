# WhatsApp Business API Setup Guide

## Prerequisites

1. **Meta Business Account** - Create at https://business.facebook.com
2. **Meta Developer Account** - Create at https://developers.facebook.com
3. **Verified Business** - Your business must be verified by Meta (takes 2-4 weeks)
4. **Phone Number** - A dedicated phone number for WhatsApp Business

## Step-by-Step Setup

### 1. Create Meta App

1. Go to https://developers.facebook.com/apps
2. Click "Create App"
3. Select "Business" as app type
4. Fill in app name and contact email
5. Click "Create App"

### 2. Add WhatsApp Product

1. In your app dashboard, find "WhatsApp" in the product list
2. Click "Set Up"
3. Complete the WhatsApp Business setup wizard

### 3. Get API Credentials

#### Phone Number ID
1. Go to WhatsApp > API Setup
2. Copy the "Phone number ID" (looks like: `123456789012345`)
3. Add to `.env`: `WHATSAPP_PHONE_NUMBER_ID=123456789012345`

#### Business Account ID
1. Go to WhatsApp > Getting Started
2. Copy "WhatsApp Business Account ID"
3. Add to `.env`: `WHATSAPP_BUSINESS_ACCOUNT_ID=123456789012345`

#### Access Token (Permanent)
1. Go to Business Settings > System Users
2. Create a new System User with "Admin" role
3. Click "Generate New Token"
4. Select your app
5. Check permissions:
   - `whatsapp_business_messaging`
   - `whatsapp_business_management`
6. Generate token (save it securely - shown only once!)
7. Add to `.env`: `WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxx`

#### App Secret
1. Go to App Dashboard > Settings > Basic
2. Copy "App Secret"
3. Add to `.env`: `WHATSAPP_APP_SECRET=xxxxxxxxxxxxxxxx`

#### Verify Token (You Create This)
1. Generate a random secure string (e.g., use: `openssl rand -hex 32`)
2. Add to `.env`: `WHATSAPP_VERIFY_TOKEN=your_random_string_here`

### 4. Configure Webhooks

1. Go to WhatsApp > Configuration
2. Click "Edit" in the Webhook section
3. **Callback URL:** `https://yourdomain.com/api/webhooks/whatsapp`
4. **Verify Token:** Use the same token from `WHATSAPP_VERIFY_TOKEN`
5. Click "Verify and Save"

6. Subscribe to webhook fields:
   - ✅ `messages` - For incoming messages
   - ✅ `message_status` - For delivery receipts

### 5. Test Configuration

#### Test Webhook Verification
```bash
curl -X GET "https://yourdomain.com/api/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=YOUR_TOKEN&hub.challenge=test123"
```

Should return: `test123`

#### Send Test Message
```bash
curl -X POST "https://graph.facebook.com/v21.0/PHONE_NUMBER_ID/messages" \
-H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "messaging_product": "whatsapp",
  "to": "1234567890",
  "type": "text",
  "text": {
    "body": "Hello from your CRM!"
  }
}'
```

## Environment Variables

Add these to your `.env` file:

```env
# WhatsApp Business API
WHATSAPP_API_VERSION=v21.0
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_ACCESS_TOKEN=your_permanent_access_token
WHATSAPP_APP_SECRET=your_app_secret
WHATSAPP_VERIFY_TOKEN=your_random_verify_token
WHATSAPP_BASE_URL=https://graph.facebook.com
```

## Webhook URL Structure

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/webhooks/whatsapp` | GET | Webhook verification by Meta |
| `/api/webhooks/whatsapp` | POST | Incoming messages and status updates |

## Security Features

✅ **Signature Verification** - All webhooks are verified using HMAC SHA-256  
✅ **Token Authentication** - Verify token prevents unauthorized webhook setup  
✅ **HTTPS Required** - Meta only sends webhooks to HTTPS endpoints  
✅ **IP Whitelisting** - Consider whitelisting Meta's webhook IPs

## Meta Webhook IPs (Optional Whitelist)
```
173.252.88.0/24
173.252.96.0/24
69.63.176.0/20
66.220.144.0/20
```

## Message Types Supported

- ✅ Text messages
- ✅ Image messages
- ✅ Video messages
- ✅ Audio messages
- ✅ Document messages
- ✅ Location messages
- ✅ Contact messages
- ✅ Interactive messages (buttons, lists)
- ✅ Template messages

## Status Updates

- `sent` - Message sent to WhatsApp servers
- `delivered` - Message delivered to recipient's phone
- `read` - Message read by recipient
- `failed` - Message failed to send

## Rate Limits

- **Standard Tier:** 1,000 conversations per 24 hours
- **Tier 1:** 10,000 conversations per 24 hours
- **Tier 2:** 100,000 conversations per 24 hours
- **Tier 3:** Unlimited conversations

Tiers increase automatically based on quality rating.

## Message Templates

All marketing/promotional messages must use approved templates:

1. Go to WhatsApp > Message Templates
2. Create template with variables
3. Submit for approval (takes 24-48 hours)
4. Use template in campaigns

## Troubleshooting

### Webhook Not Receiving Messages
1. Check webhook URL is publicly accessible
2. Verify SSL certificate is valid
3. Check webhook subscriptions are active
4. Review webhook logs in Meta Dashboard

### Authentication Errors
1. Verify access token hasn't expired
2. Check token has correct permissions
3. Regenerate token if needed

### Message Send Failures
1. Verify phone number format (E.164: +1234567890)
2. Check recipient opted in to receive messages
3. Verify template is approved (for templates)
4. Check rate limits not exceeded

## Support

- **Meta Documentation:** https://developers.facebook.com/docs/whatsapp
- **API Reference:** https://developers.facebook.com/docs/whatsapp/cloud-api/reference
- **Business Support:** https://business.whatsapp.com/support

## Next Steps

After setup is complete:
1. ✅ Webhook verification working
2. ✅ Test sending a message
3. ✅ Test receiving a message
4. 🔜 Implement message handlers
5. 🔜 Implement send message service
6. 🔜 Test end-to-end flow



