# 🗄️ Database Schema Documentation

Complete database schema for the omni-channel super app.

---

## 📊 **Schema Overview**

### **Total Tables**
- **Landlord Database:** 34 tables (system-wide)
- **Tenant Databases:** 52 tables each (per tenant)
- **New Tables:** 11 (conversation system)

---

## 🆕 **New Conversation Tables (11 Tables)**

### **1. conversations**
Central table for unified inbox.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| customer_id | bigint FK | Links to customers table |
| platform | varchar(50) | whatsapp, facebook, instagram, etc. |
| platform_conversation_id | varchar(255) | External platform ID |
| status | varchar(50) | new, open, pending, resolved, archived |
| assigned_to | bigint FK | User ID (nullable) |
| channel_type | varchar(50) | direct, broadcast, group |
| last_message_at | timestamp | Latest message time |
| first_response_at | timestamp | First agent response |
| resolved_at | timestamp | When resolved |
| message_count | int | Total messages |
| unread_count | int | Unread messages |
| metadata | json | Platform-specific data |
| sla_policy_id | bigint FK | SLA policy |
| sla_first_response_due_at | timestamp | SLA deadline |
| sla_resolution_due_at | timestamp | SLA deadline |
| sla_first_response_breached | boolean | SLA breach flag |
| sla_resolution_breached | boolean | SLA breach flag |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- customer_id, platform
- assigned_to, status
- last_message_at
- platform_conversation_id

---

### **2. messages**
All messages across platforms.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| conversation_id | bigint FK | Parent conversation |
| platform_message_id | varchar(255) | External message ID |
| direction | varchar(20) | inbound, outbound |
| type | varchar(50) | text, image, video, audio, etc. |
| content | text | Message content |
| user_id | bigint FK | Sender (for outbound) |
| sender_type | varchar(50) | customer, user, system |
| status | varchar(50) | pending, sent, delivered, read, failed |
| delivered_at | timestamp | Delivery time |
| read_at | timestamp | Read time |
| failed_at | timestamp | Failure time |
| error_message | text | Error details |
| metadata | json | Buttons, quick_replies, etc. |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- conversation_id, created_at
- platform_message_id
- status, created_at

---

### **3. message_attachments**
Media files for messages.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| message_id | bigint FK | Parent message |
| type | varchar(50) | image, video, audio, document |
| url | varchar(500) | Local storage URL |
| platform_url | varchar(500) | Original platform URL |
| mime_type | varchar(100) | File MIME type |
| filename | varchar(255) | Original filename |
| file_size | bigint | Size in bytes |
| width | int | For images/videos |
| height | int | For images/videos |
| duration | int | For audio/video (seconds) |
| thumbnail_url | varchar(500) | Thumbnail URL |
| metadata | json | Additional data |

---

### **4. conversation_notes**
Internal team notes.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| conversation_id | bigint FK | Parent conversation |
| user_id | bigint FK | Note author |
| content | text | Note content |
| is_pinned | boolean | Pin to top |

---

### **5. conversation_tags**
Tag definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| name | varchar(100) UNIQUE | Tag name |
| color | varchar(7) | Hex color |
| description | text | Tag description |

**Pivot Table:** `conversation_tag`
- conversation_id (FK)
- conversation_tag_id (FK)

---

### **6. conversation_assignments**
Assignment history.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| conversation_id | bigint FK | Parent conversation |
| assigned_to | bigint FK | User assigned to |
| assigned_by | bigint FK | User who assigned |
| assignment_type | varchar(50) | manual, auto_round_robin, etc. |
| assigned_at | timestamp | Assignment time |
| unassigned_at | timestamp | When unassigned |

---

### **7. message_status_updates**
Status change audit trail.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| message_id | bigint FK | Parent message |
| status | varchar(50) | sent, delivered, read, failed |
| status_at | timestamp | When status changed |
| metadata | json | Platform response |

---

### **8. campaign_messages**
Campaign message tracking.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| campaign_id | bigint FK | Parent campaign |
| customer_id | bigint FK | Recipient |
| message_id | bigint FK | Actual message sent |
| status | varchar(50) | pending, sent, delivered, read, failed |
| sent_at | timestamp | Send time |
| delivered_at | timestamp | Delivery time |
| read_at | timestamp | Read time |
| failed_at | timestamp | Failure time |
| error_message | text | Error details |

---

### **9. automated_replies**
Chatbot rules.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| name | varchar(255) | Rule name |
| trigger_type | varchar(50) | keyword, greeting, away |
| keywords | json | Array of keywords |
| reply_message | text | Auto-reply content |
| reply_type | varchar(50) | text, template, buttons |
| reply_metadata | json | Button config, etc. |
| is_active | boolean | Active flag |
| priority | int | Match priority |
| conditions | json | Platform, time conditions |

---

### **10. canned_responses**
Quick reply templates.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| title | varchar(255) | Response title |
| shortcut | varchar(50) | Keyboard shortcut |
| content | text | Response content |
| category | varchar(100) | greeting, faq, etc. |
| user_id | bigint FK | Owner (NULL = shared) |
| is_shared | boolean | Team-shared |
| platforms | json | Specific platforms |
| usage_count | int | How many times used |
| last_used_at | timestamp | Last usage |

---

### **11. conversation_transfers**
Transfer history.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| conversation_id | bigint FK | Conversation transferred |
| from_user_id | bigint FK | Previous owner |
| to_user_id | bigint FK | New owner |
| transferred_by | bigint FK | Who initiated |
| reason | text | Transfer reason |
| transferred_at | timestamp | Transfer time |

---

### **12. sla_policies**
SLA definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| name | varchar(255) | Policy name |
| description | text | Policy description |
| first_response_time_minutes | int | Response target |
| resolution_time_hours | int | Resolution target |
| conditions | json | When applies |
| is_active | boolean | Active flag |
| is_default | boolean | Default policy |

---

## 🔗 **Key Relationships**

### **Conversation Relationships**
```
conversations (1) → (N) messages
conversations (1) → (N) conversation_notes
conversations (N) → (N) conversation_tags (pivot)
conversations (1) → (N) conversation_assignments
conversations (N) → (1) customers
conversations (N) → (1) users (assigned_to)
conversations (N) → (1) sla_policies
```

### **Message Relationships**
```
messages (1) → (N) message_attachments
messages (1) → (N) message_status_updates
messages (N) → (1) conversations
messages (N) → (1) users (sender)
```

### **Campaign Relationships**
```
campaigns (1) → (N) campaign_messages
campaigns (N) → (N) customers (pivot)
campaigns (N) → (1) templates
```

---

## 📈 **Indexes & Performance**

### **Critical Indexes**
- conversations.last_message_at (DESC) - Inbox sorting
- conversations.assigned_to, status - Filtering
- messages.conversation_id, created_at - Message history
- messages.platform_message_id - Status updates
- message_attachments.message_id, type - Media queries

### **Composite Indexes**
- (customer_id, platform) - Find conversation
- (assigned_to, status) - Agent workload
- (conversation_id, created_at) - Message pagination

---

## 🎯 **Data Retention**

### **Recommendations**
- **Messages:** Keep all (audit trail)
- **Attachments:** Archive after 90 days
- **Resolved Conversations:** Archive after 6 months
- **Campaigns:** Keep all (analytics)
- **Logs:** Rotate weekly

---

**For multi-tenancy details:** [Multi-Tenancy Architecture](./MULTI_TENANCY.md)

**Continue to:** [Queue System](./QUEUE_SYSTEM.md)

