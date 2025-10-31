# 🏗️ System Architecture

Technical architecture overview of the Mijra omni-channel super app.

---

## 📐 **High-Level Architecture**

```
┌──────────────────────────────────────────────────────┐
│                   CLIENT LAYER                        │
│  ┌────────────┬────────────┬────────────────────┐   │
│  │  Web App   │ Mobile App │  External Systems  │   │
│  └─────┬──────┴─────┬──────┴──────┬─────────────┘   │
│        │            │             │                  │
│        └────────────┴─────────────┘                  │
│                     │                                │
└─────────────────────┼────────────────────────────────┘
                      │
┌─────────────────────┼────────────────────────────────┐
│                  API LAYER                            │
│  ┌───────────────────────────────────────────────┐  │
│  │  REST API (70+ Endpoints)                     │  │
│  │  - Tenant API (/api/{tenant}/...)             │  │
│  │  - Landlord API (/api/landlord/...)           │  │
│  │  - Webhook API (/api/webhooks/...)            │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────┼────────────────────────────────┘
                      │
┌─────────────────────┼────────────────────────────────┐
│              APPLICATION LAYER                        │
│  ┌──────────────────────────────────────────────┐   │
│  │           Controllers (15)                    │   │
│  │  ┌──────────────────────────────────────┐    │   │
│  │  │      Services (13)                    │    │   │
│  │  │  ┌─────────────────────────────────┐ │    │   │
│  │  │  │     Models (18)                 │ │    │   │
│  │  │  └─────────────────────────────────┘ │    │   │
│  │  └──────────────────────────────────────┘    │   │
│  └──────────────────────────────────────────────┘   │
└─────────────────────┼────────────────────────────────┘
                      │
┌─────────────────────┼────────────────────────────────┐
│              INTEGRATION LAYER                        │
│  ┌──────────────────────────────────────────────┐   │
│  │  Platform Services (10)                       │   │
│  │  - WhatsApp, Facebook, Instagram, TikTok     │   │
│  │  - GMB, Email, SMS                           │   │
│  │  - Shopify, Salla, WooCommerce               │   │
│  └──────────────────────────────────────────────┘   │
└─────────────────────┼────────────────────────────────┘
                      │
┌─────────────────────┼────────────────────────────────┐
│              INFRASTRUCTURE LAYER                     │
│  ┌──────────┬──────────┬──────────┬──────────────┐  │
│  │  MySQL   │  Redis   │  Queue   │  WebSocket   │  │
│  │(Multi-DB)│ (Cache)  │(Horizon) │  (Reverb)    │  │
│  └──────────┴──────────┴──────────┴──────────────┘  │
└───────────────────────────────────────────────────────┘
```

---

## 🎯 **Design Patterns**

### **1. Service-Oriented Architecture**
```
Controller → Service → Model → Database
         ↓
      Resource (API Response)
```

### **2. Event-Driven Design**
```
Action → Event → Broadcast → WebSocket → Frontend
```

### **3. Queue-Based Processing**
```
Webhook → Controller → Dispatch Job → Queue → Process
```

### **4. Multi-Tenant Pattern**
```
Request → Resolve Tenant → Switch Connection → Query Tenant DB
```

---

## 🗄️ **Database Architecture**

### **Multi-Tenant Design**
- **Landlord Database:** Central management (tenants, subscriptions, plans)
- **Tenant Databases:** One per tenant (conversations, messages, customers)

### **Connection Switching**
```php
DB::connection('landlord') // System tables
DB::connection('tenant')   // Current tenant's database
```

### **Automatic Scoping**
All tenant models automatically use tenant connection.

---

## 📡 **Message Flow**

### **Inbound Message**
```
Platform (WhatsApp) 
  → Webhook (/api/webhooks/whatsapp)
    → Verify Signature
      → Dispatch Job (ProcessWhatsAppMessageJob)
        → Find/Create Customer
          → Find/Create Conversation
            → Store Message
              → Auto-Assign (if enabled)
                → Auto-Reply (if matched)
                  → Broadcast Event (WebSocket)
                    → Frontend Updates
```

### **Outbound Message**
```
API Call (/conversations/{id}/messages)
  → Validate Request
    → Create Message (status: pending)
      → Dispatch Send Job
        → Platform API Call
          → Update Status (sent)
            → Receive Status Webhook
              → Update (delivered/read)
                → Broadcast Update
```

---

## ⚡ **Queue Architecture**

### **Job Types**
1. **Message Processing** - Process incoming messages
2. **Message Sending** - Send to platforms
3. **Status Updates** - Track delivery/read
4. **Campaign Execution** - Bulk messaging
5. **E-Commerce Sync** - Order/product sync
6. **Payment Processing** - Payment webhooks

### **Queue Configuration**
- **Default Queue:** General tasks
- **High Priority:** Real-time messages
- **Low Priority:** Bulk operations
- **Failed Jobs:** Automatic retry with backoff

---

## 🔄 **Real-Time Architecture**

### **Laravel Reverb (WebSocket)**
- Lightweight WebSocket server
- Channel-based subscriptions
- Private & public channels
- Event broadcasting

### **Channels**
```
conversations.{id}  - Conversation updates
users.{id}          - User notifications (private)
```

### **Events**
- NewMessageReceived
- MessageStatusUpdated
- ConversationAssigned
- ConversationStatusChanged
- ConversationTransferred

---

## 🔐 **Security Architecture**

### **API Security**
- Laravel Sanctum (token authentication)
- Rate limiting
- Request validation
- CSRF protection

### **Webhook Security**
- Signature verification (HMAC SHA-256)
- Token validation
- Replay attack prevention
- Request logging

### **Multi-Tenant Security**
- Database isolation
- Connection switching
- Tenant context middleware
- Access control (RBAC)

---

## ⚡ **Performance Optimizations**

### **Database**
- 30+ strategic indexes
- Eager loading relationships
- Query optimization
- Connection pooling

### **Caching**
- Redis for cache
- Config caching
- Route caching
- View caching

### **Queue**
- Async processing
- Job batching
- Rate limiting
- Auto-retry

### **Real-Time**
- Efficient broadcasting
- Channel subscriptions
- Event filtering

---

## 📊 **Scalability**

### **Horizontal Scaling**
- Stateless application design
- Load balancer ready
- Queue worker scaling
- WebSocket server scaling

### **Vertical Scaling**
- Optimized queries
- Indexed databases
- Cached responses
- Lazy loading

### **Multi-Tenant Scaling**
- Isolated databases per tenant
- Tenant-specific caching
- Queue job tenancy
- Independent scaling

---

## 🎯 **Technology Stack**

### **Backend**
- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis
- **Real-Time:** Laravel Reverb

### **Architecture**
- **Pattern:** Service-Oriented
- **Design:** Event-Driven
- **Processing:** Queue-Based
- **Tenancy:** Multi-Database

### **Key Packages**
- Laravel Sanctum (Auth)
- Spatie Multi-tenancy
- Laravel Horizon (Queue Monitoring)
- Spatie Permission (RBAC)
- Spatie Media Library
- Laravel Reverb (WebSocket)

---

## 🔄 **Data Flow Example**

### **Customer Messages on WhatsApp:**

1. Customer sends WhatsApp message
2. Meta servers receive message
3. Meta calls webhook: `POST /api/webhooks/whatsapp`
4. Webhook controller verifies signature
5. Dispatches `ProcessWhatsAppMessageJob`
6. Job runs asynchronously:
   - Finds/creates customer by phone
   - Finds/creates conversation
   - Downloads media if present
   - Stores message in database
   - Triggers auto-assignment
   - Checks automated reply rules
7. Message model fires `created` event
8. Event broadcasts via WebSocket
9. Frontend receives update instantly
10. Agent sees new message in inbox

**Total Time:** < 2 seconds from send to agent notification!

---

**For specific implementations:** See [Database Schema](./DATABASE_SCHEMA.md)

**Continue to:** [Multi-Tenancy](./MULTI_TENANCY.md)

