# 🛒 Salla Integration Setup (Saudi Arabia)

Complete guide to integrating Salla, Saudi Arabia's leading e-commerce platform.

---

## 🇸🇦 **About Salla**

Salla is the #1 e-commerce platform in Saudi Arabia, serving 50,000+ merchants.

**Perfect for:**
- Saudi Arabian businesses
- Arabic-speaking customers
- SAR currency transactions
- Local payment methods (Mada, STC Pay)

---

## 📋 **Prerequisites**

1. Salla merchant account
2. Salla Partner account (for API access)
3. Your Mijra app deployed with HTTPS

---

## 🔧 **Step-by-Step Setup**

### **1. Create Salla App**

1. Go to https://salla.partners
2. Register as a partner
3. Create new app
4. Get credentials:
   - Client ID
   - Client Secret

### **2. Configure OAuth**

Add to `.env`:
```env
SALLA_CLIENT_ID=your_client_id
SALLA_CLIENT_SECRET=your_client_secret
SALLA_REDIRECT_URI=https://yourapp.com/api/landlord/auth/salla/callback
SALLA_WEBHOOK_SECRET=your_webhook_secret
SALLA_BASE_URL=https://api.salla.sa
```

### **3. Connect Merchant Store**

**Option A: Via API**
```http
GET /api/landlord/auth/salla/

Response:
{
  "authorization_url": "https://accounts.salla.sa/oauth2/auth?..."
}
```

**Option B: Direct URL**
Visit the authorization URL and grant permissions.

**After Authorization:**
- Merchant authorizes your app
- Redirected to callback URL
- Access token stored automatically
- Ready to receive webhooks

### **4. Configure Webhooks**

1. In Salla Partner Dashboard
2. Go to Webhooks section
3. Add webhook URL: `https://yourapp.com/api/webhooks/salla`
4. Set secret: Same as `SALLA_WEBHOOK_SECRET`
5. Subscribe to events:
   - ✅ order.created
   - ✅ order.updated
   - ✅ order.cancelled
   - ✅ customer.created
   - ✅ customer.updated
   - ✅ product.created
   - ✅ product.updated

---

## ✨ **Features**

Once configured:
- ✅ Orders automatically sync to Opportunities
- ✅ Customers automatically sync to CRM
- ✅ Products sync to catalog
- ✅ Order updates tracked in real-time
- ✅ Customer data enriched
- ✅ SAR currency support
- ✅ Arabic language ready

---

## 🧪 **Testing**

### **Test OAuth Flow**
1. Call OAuth endpoint
2. Authorize in Salla
3. Verify token stored in `tenant_platforms` table

### **Test Webhooks**
1. Create test order in Salla
2. Check logs: Should see "Salla webhook received"
3. Verify customer created in CRM
4. Verify opportunity created

---

## 🔐 **Security**

- ✅ HMAC SHA-256 signature verification
- ✅ OAuth 2.0 authentication
- ✅ Secure token storage
- ✅ Request logging

---

## 📊 **Data Mapping**

| Salla | Mijra |
|-------|-------|
| Order | Opportunity |
| Customer | Customer |
| Product | Product |
| Order Status | Opportunity Status |
| Total Amount | Stored in notes |

---

## 🌟 **Salla-Specific Features**

- Arabic customer names
- SAR currency
- Saudi phone numbers (+966)
- Mada payments
- STC Pay
- Local delivery options

---

## 🆘 **Troubleshooting**

**Issue:** OAuth redirect fails  
**Fix:** Check redirect URI matches exactly

**Issue:** Webhook not receiving  
**Fix:** Verify webhook secret matches

**Issue:** Customer not syncing  
**Fix:** Check email/phone format

---

## 🎯 **Next Steps**

1. ✅ Configure OAuth
2. ✅ Connect merchant store
3. ✅ Test order sync
4. ✅ Enable WhatsApp for order notifications
5. ✅ Create abandoned cart campaigns

---

**Perfect for Saudi market!** 🇸🇦

**Next:** [Moyasar Payment Setup](../payments/MOYASAR_SETUP.md)

