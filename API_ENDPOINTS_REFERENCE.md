# 📡 API Endpoints Reference - Omni-Channel Super App

## Base URL Structure

```
Tenant API: https://yourapp.com/api/{tenant}/...
Landlord API: https://yourapp.com/api/landlord/...
Webhooks: https://yourapp.com/api/webhooks/...
```

---

## 🔵 Conversation & Messaging Endpoints (11)

### **List Conversations**
```http
GET /api/{tenant}/conversations

Headers:
  Authorization: Bearer {token}

Query Parameters:
  ?status=open              // Filter by status (new, open, pending, resolved, archived)
  ?platform=whatsapp        // Filter by platform
  ?assigned_to=5            // Filter by assigned user ID
  ?assigned_to=unassigned   // Filter unassigned
  ?customer_id=10           // Filter by customer
  ?tag=2                    // Filter by tag ID
  ?unread=true              // Show only unread
  ?search=John              // Search customer name/phone/email
  ?date_from=2025-10-01     // Filter by date range
  ?date_to=2025-10-31
  ?sort=-last_message_at    // Sort (prefix - for desc, + for asc)
  ?limit=20                 // Results per page

Response:
{
  "data": [
    {
      "id": 1,
      "customer": {...},
      "platform": "whatsapp",
      "status": "open",
      "assigned_to": 5,
      "assigned_user": {...},
      "latest_message": {...},
      "tags": [...],
      "message_count": 15,
      "unread_count": 3,
      "last_message_at": "2025-10-30T10:30:00Z"
    }
  ],
  "meta": {...}
}
```

---

### **Get Conversation Details**
```http
GET /api/{tenant}/conversations/{id}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "customer": {
      "id": 10,
      "name": "John Doe",
      "phone": "+1234567890",
      "email": "john@example.com",
      "status": 2,
      "country": "USA",
      "city": "New York"
    },
    "platform": "whatsapp",
    "status": "open",
    "assigned_user": {...},
    "tags": [...],
    "notes": [...],
    "message_count": 15,
    "unread_count": 3
  }
}
```

---

### **Get Conversation Messages**
```http
GET /api/{tenant}/conversations/{id}/messages

Query Parameters:
  ?limit=50                 // Messages per page (default: 50)

Response:
{
  "data": [
    {
      "id": 100,
      "conversation_id": 1,
      "direction": "inbound",
      "type": "text",
      "content": "Hello, I need help",
      "status": "delivered",
      "attachments": [],
      "created_at": "2025-10-30T10:25:00Z"
    },
    {
      "id": 101,
      "direction": "outbound",
      "type": "text",
      "content": "Hi! How can I help you?",
      "user": {
        "id": 5,
        "name": "Support Agent"
      },
      "status": "read",
      "read_at": "2025-10-30T10:26:00Z"
    }
  ]
}
```

---

### **Send Message**
```http
POST /api/{tenant}/conversations/{id}/messages

Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "content": "Thank you for contacting us!",
  "type": "text",  // Optional: text, image, video, audio, document, template
  "metadata": {},  // Optional: platform-specific data
  "attachments": [ // Optional: for media messages
    {
      "type": "image",
      "url": "https://example.com/image.jpg",
      "filename": "product.jpg",
      "mime_type": "image/jpeg"
    }
  ]
}

Response:
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 102,
    "conversation_id": 1,
    "content": "Thank you for contacting us!",
    "status": "pending"  // Will be updated to sent/delivered/read
  }
}
```

---

### **Assign Conversation**
```http
POST /api/{tenant}/conversations/{id}/assign

Body:
{
  "user_id": 5
}

Response:
{
  "success": true,
  "message": "Conversation assigned successfully",
  "data": {...}
}
```

---

### **Unassign Conversation**
```http
POST /api/{tenant}/conversations/{id}/unassign

Response:
{
  "success": true,
  "message": "Conversation unassigned successfully"
}
```

---

### **Update Conversation Status**
```http
PATCH /api/{tenant}/conversations/{id}/status

Body:
{
  "status": "resolved"  // new, open, pending, resolved, archived
}

Response:
{
  "success": true,
  "message": "Conversation status updated successfully"
}
```

---

### **Mark as Read**
```http
POST /api/{tenant}/conversations/{id}/mark-read

Response:
{
  "success": true,
  "message": "Conversation marked as read"
}
```

---

### **Add Internal Note**
```http
POST /api/{tenant}/conversations/{id}/notes

Body:
{
  "content": "Customer mentioned they're interested in premium plan",
  "is_pinned": false  // Optional: pin important notes
}

Response:
{
  "success": true,
  "message": "Note added successfully"
}
```

---

### **Add Tags**
```http
POST /api/{tenant}/conversations/{id}/tags

Body:
{
  "tag_ids": [1, 2, 3]
}

Response:
{
  "success": true,
  "message": "Tags added successfully"
}
```

---

### **Remove Tags**
```http
DELETE /api/{tenant}/conversations/{id}/tags

Body:
{
  "tag_ids": [2]
}

Response:
{
  "success": true,
  "message": "Tags removed successfully"
}
```

---

### **Get Statistics**
```http
GET /api/{tenant}/conversations/statistics

Response:
{
  "success": true,
  "data": {
    "new": {
      "label": "New",
      "count": 25,
      "total_unread": 25,
      "avg_messages": 1.5
    },
    "open": {
      "label": "Open",
      "count": 50,
      "total_unread": 15,
      "avg_messages": 8.3
    },
    "pending": {...},
    "resolved": {...},
    "archived": {...},
    "total": 150,
    "unassigned": 30,
    "with_unread": 40
  }
}
```

---

## 🪝 Webhook Endpoints

### **WhatsApp Webhooks**

#### Verify Webhook
```http
GET /api/webhooks/whatsapp

Query Parameters (sent by Meta):
  ?hub.mode=subscribe
  ?hub.verify_token=YOUR_VERIFY_TOKEN
  ?hub.challenge=test123

Response: test123 (plain text)
```

#### Receive Messages
```http
POST /api/webhooks/whatsapp

Headers (sent by Meta):
  X-Hub-Signature-256: sha256={signature}

Body: WhatsApp webhook payload

Response:
{
  "status": "ok"
}
```

---

## 🔄 Real-Time Events (WebSocket)

### **Subscribe to Conversation**
```javascript
Echo.channel(`conversations.${conversationId}`)
    .listen('.message.received', (e) => {
        console.log('New message:', e.message);
    })
    .listen('.message.status.updated', (e) => {
        console.log('Status updated:', e.status);
    })
    .listen('.conversation.status.changed', (e) => {
        console.log('Conversation status:', e.new_status);
    })
    .listen('.conversation.assigned', (e) => {
        console.log('Assigned to:', e.assigned_to);
    });
```

### **Subscribe to User Channel** (Private)
```javascript
Echo.private(`users.${userId}`)
    .listen('.conversation.assigned', (e) => {
        console.log('New conversation assigned:', e.conversation_id);
    });
```

---

## 📊 Complete Endpoint List

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| **Conversations** |
| GET | `/conversations` | ✅ | List with filters |
| GET | `/conversations/{id}` | ✅ | Get details |
| GET | `/conversations/statistics` | ✅ | Get stats |
| GET | `/conversations/{id}/messages` | ✅ | Get messages |
| POST | `/conversations/{id}/messages` | ✅ | Send message |
| POST | `/conversations/{id}/assign` | ✅ | Assign to user |
| POST | `/conversations/{id}/unassign` | ✅ | Unassign |
| PATCH | `/conversations/{id}/status` | ✅ | Update status |
| POST | `/conversations/{id}/mark-read` | ✅ | Mark as read |
| POST | `/conversations/{id}/notes` | ✅ | Add note |
| POST | `/conversations/{id}/tags` | ✅ | Add tags |
| DELETE | `/conversations/{id}/tags` | ✅ | Remove tags |
| **Webhooks** |
| GET | `/webhooks/whatsapp` | ❌ | Verify webhook |
| POST | `/webhooks/whatsapp` | ❌ | Receive messages |

---

## 🎯 Status Codes

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

**All endpoints follow RESTful conventions with consistent response formats.**

