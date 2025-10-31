# ⚡ Quick Start Guide

Get your omni-channel super app running in 30 minutes!

---

## 🎯 **Prerequisites**

- PHP 8.2+
- MySQL 8.0
- Redis
- Composer
- Node.js & NPM
- GitHub Desktop (for pushing code)

---

## 🚀 **5-Step Quick Start**

### **Step 1: Push to GitHub** (5 minutes)

1. Open GitHub Desktop
2. Select `mijra` repository
3. Switch to `stage` branch
4. See 110+ changed files
5. Commit message: `feat: complete omni-channel super app`
6. Click "Commit to stage"
7. Click "Push origin"

✅ **Done!** Code is now on GitHub.

---

### **Step 2: Setup Database** (5 minutes)

```bash
# Navigate to project
cd D:\Cornerz\Mijra\Code

# Run migrations (creates 11 new tables)
php artisan migrate

# Verify tables created
php artisan db:show
```

✅ **Done!** Database ready.

---

### **Step 3: Start Services** (5 minutes)

Open 3 terminals:

**Terminal 1 - Application:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 3 - Real-Time Server:**
```bash
php artisan reverb:start
```

✅ **Done!** All services running.

---

### **Step 4: Configure WhatsApp** (10 minutes)

1. Go to https://developers.facebook.com
2. Create/select your app
3. Add WhatsApp Business product
4. Get credentials:
   - Phone Number ID
   - Business Account ID
   - Access Token
   - App Secret

5. Add to `.env`:
```env
WHATSAPP_PHONE_NUMBER_ID=your_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_id
WHATSAPP_ACCESS_TOKEN=your_token
WHATSAPP_APP_SECRET=your_secret
WHATSAPP_VERIFY_TOKEN=random_secret_123
```

6. Configure webhook in Meta Dashboard:
   - URL: `https://yourapp.com/api/webhooks/whatsapp`
   - Verify Token: Same as `WHATSAPP_VERIFY_TOKEN`
   - Subscribe to: `messages`, `message_status`

✅ **Done!** WhatsApp configured.

---

### **Step 5: Test First Conversation** (5 minutes)

1. Send WhatsApp message to your business number
2. Check conversations:
   ```bash
   GET http://localhost:8000/api/{tenant}/conversations
   ```
3. See your message in the inbox!
4. Reply via API:
   ```bash
   POST http://localhost:8000/api/{tenant}/conversations/1/messages
   {
     "content": "Hello! How can I help?"
   }
   ```

✅ **Done!** First conversation complete!

---

## 🎉 **You're Live!**

In 30 minutes you now have:
- ✅ WhatsApp messaging working
- ✅ Unified inbox operational
- ✅ Real-time updates active
- ✅ Queue system processing
- ✅ Auto-assignment ready
- ✅ Chatbot ready

---

## 📖 **Next Steps**

1. **Add more platforms:** [Platform Setup Guides](../platforms/)
2. **Create campaigns:** [Campaign Guide](../features/CAMPAIGNS_GUIDE.md)
3. **Configure automation:** [Automation Guide](../features/AUTOMATION.md)
4. **View analytics:** [Analytics Setup](../features/ANALYTICS_SETUP.md)

---

## 🆘 **Troubleshooting**

**Issue:** Migrations fail  
**Fix:** Check database connection in `.env`

**Issue:** Queue not processing  
**Fix:** Make sure Redis is running and `queue:work` is started

**Issue:** WhatsApp webhook fails  
**Fix:** Check webhook URL is publicly accessible with HTTPS

**Issue:** Messages not sending  
**Fix:** Check WhatsApp credentials in `.env` and queue worker is running

---

**Need more help?** See [Troubleshooting Guide](../guides/TROUBLESHOOTING.md)

---

**You're ready to revolutionize customer engagement!** 🚀

