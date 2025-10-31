# 👨‍💻 Guide for Developers

Technical guide for developers working on the Mijra platform.

---

## 🎯 **Getting Started**

### **1. Understand the Architecture**
Read these in order:
1. [System Architecture](../architecture/SYSTEM_ARCHITECTURE.md)
2. [Database Schema](../architecture/DATABASE_SCHEMA.md)
3. [Multi-Tenancy](../architecture/MULTI_TENANCY.md)
4. [Queue System](../architecture/QUEUE_SYSTEM.md)

### **2. Code Structure**
```
app/
├── Models/Tenant/          # Tenant-specific models
├── Models/Landlord/        # System-wide models
├── Services/               # Business logic
│   ├── Tenant/            # Tenant services
│   └── Platforms/         # Platform integrations
├── Jobs/                   # Queue jobs
├── Events/                 # Broadcasting events
├── Http/
│   ├── Controllers/       # API endpoints
│   ├── Resources/         # API responses
│   └── Requests/          # Validation
└── Enum/                   # Type-safe enums
```

---

## 🛠️ **Development Setup**

### **1. Clone Repository**
```bash
git clone https://github.com/AshrafGalal/mijra.git
cd mijra
git checkout stage
```

### **2. Install Dependencies**
```bash
composer install
npm install
```

### **3. Environment**
```bash
cp .env.example .env
php artisan key:generate
```

### **4. Database**
```bash
php artisan migrate
php artisan db:seed
```

### **5. Start Services**
```bash
# Terminal 1
php artisan serve

# Terminal 2
php artisan queue:work

# Terminal 3
php artisan reverb:start

# Terminal 4 (optional)
php artisan horizon
```

---

## 📝 **Coding Standards**

### **Follow Laravel Best Practices**
- Service-oriented architecture
- Type hints everywhere
- PHPDoc comments
- Eloquent ORM (no raw queries)
- Request validation
- Resource transformers

### **Example Service Pattern**
```php
class ExampleService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return ExampleFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Example::query();
    }

    public function create(ExampleDTO $dto): Example
    {
        return DB::connection('tenant')->transaction(function () use ($dto) {
            return $this->baseQuery()->create($dto->toArray());
        });
    }
}
```

---

## 🎯 **Adding New Platform**

### **Steps:**

**1. Create Service** (`app/Services/Platforms/`)
```php
class NewPlatformService
{
    public function sendTextMessage(Conversation $conv, Message $msg): array
    {
        // Platform API call
    }
}
```

**2. Create Webhook Controller** (`app/Http/Controllers/Api/Webhooks/`)
```php
class NewPlatformWebhookController extends Controller
{
    public function handle(Request $request) {
        // Verify signature
        // Dispatch job
    }
}
```

**3. Create Jobs**
- `ProcessNewPlatformMessageJob.php`
- `SendNewPlatformMessageJob.php`

**4. Add Route** (`routes/webhooks.php`)
```php
Route::post('/newplatform', [NewPlatformWebhookController::class, 'handle']);
```

**5. Add to Dispatcher** (`ConversationController.php`)
```php
'newplatform' => dispatch(new SendNewPlatformMessageJob($message)),
```

**6. Add Enum**
Update `ExternalPlatformEnum.php`:
```php
case NEWPLATFORM = 'newplatform';
```

---

## 🧪 **Testing**

### **Run Tests**
```bash
php artisan test
```

### **Test Specific Feature**
```bash
php artisan test --filter ConversationTest
```

### **Manual Testing**
Use Postman collection (to be created) or:
```bash
# Test conversation creation
curl -X POST "http://localhost:8000/api/test-tenant/conversations/1/messages" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"content":"Test"}'
```

---

## 🔍 **Debugging**

### **Enable Debug Mode**
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### **Check Logs**
```bash
tail -f storage/logs/laravel.log
```

### **Queue Debugging**
```bash
# Monitor queue
php artisan queue:monitor

# Failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all
```

### **Database Queries**
```bash
# Enable query log
DB::enableQueryLog();
// ... your code
dd(DB::getQueryLog());
```

---

## 🎨 **Code Examples**

### **Create a Conversation**
```php
use App\Services\Tenant\ConversationService;

$conversationService = app(ConversationService::class);

$conversation = $conversationService->findOrCreate(
    customerId: 1,
    platform: 'whatsapp',
    platformConversationId: '+1234567890'
);
```

### **Send a Message**
```php
use App\Services\Tenant\MessageService;

$messageService = app(MessageService::class);

$message = $messageService->createOutboundMessage(
    conversationId: 1,
    content: 'Hello!',
    userId: auth()->id(),
    type: 'text'
);

// Dispatch to platform
dispatch(new SendWhatsAppMessageJob($message));
```

### **Broadcast Event**
```php
use App\Events\NewMessageReceived;

broadcast(new NewMessageReceived($message))->toOthers();
```

---

## 📚 **Useful Artisan Commands**

```bash
# Create model
php artisan make:model Tenant/Example

# Create migration
php artisan make:migration create_examples_table

# Create controller
php artisan make:controller Api/Tenant/ExampleController

# Create job
php artisan make:job ProcessExampleJob

# Create event
php artisan make:event ExampleEvent

# Clear caches
php artisan optimize:clear

# View routes
php artisan route:list

# Tinker (REPL)
php artisan tinker
```

---

## 🔐 **Security Checklist**

When adding new features:
- [ ] Validate all inputs
- [ ] Use parameterized queries (Eloquent)
- [ ] Verify webhook signatures
- [ ] Check user permissions
- [ ] Log sensitive actions
- [ ] Handle errors gracefully
- [ ] Test edge cases

---

## 📖 **Resources**

- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Laravel Reverb](https://laravel.com/docs/reverb)
- [Laravel Horizon](https://laravel.com/docs/horizon)
- [Spatie Multi-tenancy](https://spatie.be/docs/laravel-multitenancy)

---

## 🎯 **Best Practices**

1. **Always use services** - Keep controllers thin
2. **Type hints** - Use strict typing
3. **DTOs** - For data transfer
4. **Events** - For side effects
5. **Jobs** - For async work
6. **Resources** - For API responses
7. **Requests** - For validation
8. **Enums** - For constants

---

**Need help?** Review existing code in `app/Services/` for patterns.

**Continue to:** [Adding New Platforms](../development/ADDING_PLATFORMS.md)

