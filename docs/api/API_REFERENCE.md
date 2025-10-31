# 📡 Complete API Reference

Full API documentation for all 70+ endpoints.

---

## 🔑 **Authentication**

All tenant API endpoints require authentication via Laravel Sanctum.

### **Get Access Token**
```http
POST /api/landlord/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "data": {
    "token": "1|xxxxxxxxxxxxx",
    "user": {...}
  }
}
```

### **Use Token**
```http
GET /api/{tenant}/conversations
Authorization: Bearer 1|xxxxxxxxxxxxx
```

---

## 💬 **Conversation Endpoints**

### **List Conversations**
```http
GET /api/{tenant}/conversations

Query Parameters:
  ?status=open
  ?platform=whatsapp
  ?assigned_to=5
  ?unread=true
  ?search=John
  ?limit=20

Response: Paginated list of conversations
```

### **Get Conversation**
```http
GET /api/{tenant}/conversations/{id}

Response: Full conversation details with customer, tags, notes
```

### **Get Messages**
```http
GET /api/{tenant}/conversations/{id}/messages
?limit=50

Response: Paginated message history
```

### **Send Message**
```http
POST /api/{tenant}/conversations/{id}/messages

{
  "content": "Hello!",
  "type": "text",
  "metadata": {},
  "attachments": []
}

Response: Created message
```

### **Assign Conversation**
```http
POST /api/{tenant}/conversations/{id}/assign

{
  "user_id": 5
}
```

### **Transfer Conversation**
```http
POST /api/{tenant}/conversations/{id}/transfer

{
  "to_user_id": 10,
  "reason": "Customer needs technical support"
}
```

### **Update Status**
```http
PATCH /api/{tenant}/conversations/{id}/status

{
  "status": "resolved"
}
```

### **Add Note**
```http
POST /api/{tenant}/conversations/{id}/notes

{
  "content": "Customer is VIP, handle with priority",
  "is_pinned": true
}
```

### **Add Tags**
```http
POST /api/{tenant}/conversations/{id}/tags

{
  "tag_ids": [1, 2, 3]
}
```

### **Statistics**
```http
GET /api/{tenant}/conversations/statistics

Response: Dashboard statistics
```

---

## 📢 **Campaign Endpoints**

### **List Campaigns**
```http
GET /api/{tenant}/campaigns
?status=active
?channel=whatsapp
```

### **Create Campaign**
```http
POST /api/{tenant}/campaigns

{
  "title": "Flash Sale",
  "content": "Hi {{customer_name}}! 50% off today!",
  "channel": "whatsapp",
  "target": 1,
  "scheduled_at": "2025-11-01 10:00:00",
  "customer_ids": [1, 2, 3]
}
```

### **Start Campaign**
```http
POST /api/{tenant}/campaigns/{id}/start
```

### **Pause Campaign**
```http
POST /api/{tenant}/campaigns/{id}/pause
```

### **Campaign Analytics**
```http
GET /api/{tenant}/campaigns/{id}/analytics

Response: Delivery rates, read rates, engagement
```

---

## 📊 **Analytics Endpoints**

### **Dashboard**
```http
GET /api/{tenant}/analytics/dashboard
?date_from=2025-10-01
?date_to=2025-10-31

Response: Complete analytics overview
```

### **Time Series**
```http
GET /api/{tenant}/analytics/time-series
?metric=conversations
?group_by=day
```

### **Customer Lifecycle**
```http
GET /api/{tenant}/analytics/customer-lifecycle
?customer_id=50

Response: Complete customer journey
```

---

## ⚡ **Canned Responses**

### **List**
```http
GET /api/{tenant}/canned-responses
?category=greeting
?platform=whatsapp
?search=hello
```

### **Create**
```http
POST /api/{tenant}/canned-responses

{
  "title": "Greeting",
  "shortcut": "/hi",
  "content": "Hello {{customer_name}}!",
  "category": "greeting",
  "is_shared": true
}
```

### **Use Response**
```http
POST /api/{tenant}/canned-responses/{id}/use

{
  "variables": {
    "customer_name": "John"
  }
}

Response: Content with variables replaced
```

---

## 🔄 **Bulk Actions**

### **Bulk Assign**
```http
POST /api/{tenant}/conversations/bulk-assign

{
  "conversation_ids": [1, 2, 3, 4, 5],
  "user_id": 10
}
```

### **Bulk Update Status**
```http
POST /api/{tenant}/conversations/bulk-status

{
  "conversation_ids": [1, 2, 3],
  "status": "resolved"
}
```

### **Export to CSV**
```http
POST /api/{tenant}/conversations/export

{
  "conversation_ids": [1, 2, 3]
}

Response: CSV data array
```

---

## 👤 **Customer 360 Profile**

### **Get Complete Profile**
```http
GET /api/{tenant}/customers/{id}/profile

Response:
{
  "customer": {...},
  "summary": {...},
  "conversations": [...],
  "opportunities": [...],
  "tasks": [...],
  "timeline": [...],
  "statistics": {...}
}
```

### **Engagement Score**
```http
GET /api/{tenant}/customers/{id}/engagement-score

Response:
{
  "score": 85,
  "level": "Very High",
  "metrics": {...}
}
```

---

## 🔔 **WebSocket Events**

Subscribe to real-time updates:

```javascript
// Subscribe to conversation
Echo.channel(`conversations.${conversationId}`)
    .listen('.message.received', (e) => {
        console.log('New message:', e.message);
    })
    .listen('.message.status.updated', (e) => {
        console.log('Status:', e.status);
    });

// Subscribe to user notifications
Echo.private(`users.${userId}`)
    .listen('.conversation.assigned', (e) => {
        console.log('New conversation assigned');
    });
```

---

## 📝 **Response Format**

All endpoints return consistent format:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": {...}
}
```

---

## 🎯 **Status Codes**

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

**For complete endpoint details:** See individual API guides

**Continue to:** [Webhook Reference](./WEBHOOKS.md)

