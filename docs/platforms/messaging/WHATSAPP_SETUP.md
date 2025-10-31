# 📱 WhatsApp Business API Setup

Complete guide to setting up WhatsApp Business API integration.

---

## 🎯 **Prerequisites**

1. Meta Business Account
2. Meta Developer Account
3. Verified Business (2-4 weeks process)
4. Dedicated phone number for WhatsApp Business

---

## 📋 **Step-by-Step Setup**

### **1. Create Meta App**
1. Go to https://developers.facebook.com/apps
2. Click "Create App"
3. Select "Business" type
4. Enter app name and email
5. Create App

### **2. Add WhatsApp Product**
1. In app dashboard, find "WhatsApp"
2. Click "Set Up"
3. Complete setup wizard

### **3. Get API Credentials**

**Phone Number ID:**
1. WhatsApp → API Setup
2. Copy "Phone number ID"
3. Add to `.env`: `WHATSAPP_PHONE_NUMBER_ID=123456789`

**Business Account ID:**
1. WhatsApp → Getting Started
2. Copy "WhatsApp Business Account ID"
3. Add to `.env`: `WHATSAPP_BUSINESS_ACCOUNT_ID=123456789`

**Access Token (Permanent):**
1. Business Settings → System Users
2. Create System User (Admin role)
3. Generate New Token
4. Select your app
5. Check permissions:
   - `whatsapp_business_messaging`
   - `whatsapp_business_management`
6. Generate and save token
7. Add to `.env`: `WHATSAPP_ACCESS_TOKEN=EAAxxxx`

**App Secret:**
1. App Dashboard → Settings → Basic
2. Copy "App Secret"
3. Add to `.env`: `WHATSAPP_APP_SECRET=xxxxxxxx`

**Verify Token (Create Your Own):**
1. Generate random string: `openssl rand -hex 32`
2. Add to `.env`: `WHATSAPP_VERIFY_TOKEN=your_random_string`

### **4. Configure Webhook**

1. WhatsApp → Configuration
2. Click "Edit" in Webhook section
3. **Callback URL:** `https://yourapp.com/api/webhooks/whatsapp`
4. **Verify Token:** Use same as `WHATSAPP_VERIFY_TOKEN`
5. Click "Verify and Save"
6. Subscribe to fields:
   - ✅ `messages`
   - ✅ `message_status`

---

## 🧪 **Testing**

### **Test Webhook Verification**
```bash
curl "https://yourapp.com/api/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=YOUR_TOKEN&hub.challenge=test123"

# Should return: test123
```

### **Send Test Message (via API)**
```bash
POST https://yourapp.com/api/{tenant}/conversations/1/messages
{
  "content": "Test message from CRM"
}
```

### **Send Test Message (via WhatsApp API directly)**
```bash
curl -X POST "https://graph.facebook.com/v21.0/PHONE_NUMBER_ID/messages" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "1234567890",
    "type": "text",
    "text": {"body": "Hello from Mijra!"}
  }'
```

---

## ✅ **Complete .env Configuration**

```env
WHATSAPP_API_VERSION=v21.0
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_ACCESS_TOKEN=your_permanent_access_token
WHATSAPP_APP_SECRET=your_app_secret
WHATSAPP_VERIFY_TOKEN=your_random_verify_token
WHATSAPP_BASE_URL=https://graph.facebook.com
```

---

## 🎯 **Features Enabled**

Once configured, you can:
- ✅ Receive WhatsApp messages
- ✅ Send text messages
- ✅ Send images, videos, documents
- ✅ Send template messages
- ✅ Send interactive buttons
- ✅ Send interactive lists
- ✅ Track delivery & read receipts
- ✅ Auto-create customers
- ✅ Auto-assign conversations

---

## 📊 **Rate Limits**

- **Tier 1:** 1,000 conversations/24hrs
- **Tier 2:** 10,000 conversations/24hrs
- **Tier 3:** 100,000 conversations/24hrs
- **Unlimited:** Based on quality rating

Tiers automatically upgrade based on usage and quality.

---

## 🆘 **Troubleshooting**

**Issue:** Webhook not receiving messages  
**Fix:** Ensure URL is HTTPS and publicly accessible

**Issue:** Messages not sending  
**Fix:** Verify access token and phone number ID

**Issue:** Template rejected  
**Fix:** Follow Meta's template guidelines strictly

---

**Next:** [Facebook Messenger Setup](./FACEBOOK_SETUP.md)

