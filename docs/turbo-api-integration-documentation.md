# Turbo API Integration Documentation

## Laravel External Turbo API Integration

**Version:** 1.0\
**Framework:** Laravel\
**Integration Type:** External REST API\
**Base URL:** `https://domain.com` (temporary placeholder)

------------------------------------------------------------------------

## 1. Overview

هذا المستند يشرح بالكامل طريقة ربط تطبيق Laravel بنظام خارجي لإدارة
التذاكر.

التكامل يسمح لتطبيق Laravel بتنفيذ العمليات التالية:

-   جلب فئات التذاكر.
-   جلب حالات التذاكر.
-   جلب تعليمات التذاكر.
-   جلب جميع التذاكر.
-   التحقق من إمكانية إنشاء تذكرة جديدة.
-   إنشاء تذكرة جديدة.
-   إضافة رسالة إلى تذكرة موجودة.
-   إضافة مرفقات إلى التذاكر والرسائل.
-   تقييم التذكرة.
-   معرفة إجمالي عدد التذاكر المفتوحة.

الفكرة الأساسية هي أن تطبيق Laravel لا يتعامل مباشرة مع تفاصيل HTTP في
كل Controller، بل يتم وضع كل الاتصال بالنظام الخارجي داخل Service واحدة:

``` text
Laravel Application
        |
        v
Controller
        |
        v
TurboApiService
        |
        v
Laravel HTTP Client
        |
        v
External Turbo API
```

------------------------------------------------------------------------

# 2. Architecture

## 2.1 مسؤولية كل Layer

### Controller

مسؤوليته:

-   استقبال Request من المستخدم.
-   عمل Validation.
-   استدعاء Service.
-   إرجاع Response للمستخدم أو View.

لا يجب أن يحتوي الـ Controller على تفاصيل مثل:

``` php
Http::get(...)
Http::post(...)
```

ولا يجب أن يعرف:

-   Base URL.
-   Authentication Key.
-   طريقة بناء الـ Query Parameters.
-   طريقة رفع الملفات.
-   Retry.
-   Timeout.

------------------------------------------------------------------------

### TurboApiService

هي الطبقة المسؤولة عن الاتصال الكامل بالـ External API.

مسؤوليتها:

-   بناء HTTP Client.
-   إضافة Authentication.
-   إرسال Requests.
-   رفع الملفات.
-   التعامل مع Responses.
-   تسجيل الأخطاء.

------------------------------------------------------------------------

### External API

وهو النظام الخارجي الذي ينفذ العمليات الحقيقية الخاصة بالتذاكر.

------------------------------------------------------------------------

# 3. Environment Configuration

يتم وضع بيانات الاتصال داخل ملف:

``` text
.env
```

القيم:

``` env
TURBO_API_BASE_URL=https://domain.com

TURBO_MAIN_CLIENT_CODE=25847

TURBO_AUTHENTICATION_KEY=YOUR_AUTHENTICATION_KEY
```

## شرح القيم

### TURBO_API_BASE_URL

هو عنوان الـ API الأساسي.

مثال مؤقت:

``` env
TURBO_API_BASE_URL=https://domain.com
```

لو كان الـ API الحقيقي:

``` text
https://api.example.com
```

نغير فقط:

``` env
TURBO_API_BASE_URL=https://api.example.com
```

ولا نحتاج تعديل Service.

------------------------------------------------------------------------

### TURBO_MAIN_CLIENT_CODE

كود العميل الرئيسي.

مثال:

``` env
TURBO_MAIN_CLIENT_CODE=25847
```

------------------------------------------------------------------------

### TURBO_AUTHENTICATION_KEY

مفتاح المصادقة.

``` env
TURBO_AUTHENTICATION_KEY=YOUR_AUTHENTICATION_KEY
```

يجب عدم وضعه داخل الكود مباشرة.

خطأ:

``` php
$authenticationKey = 'secret-key';
```

صحيح:

``` php
config('services.TURBO_api.authentication_key');
```

------------------------------------------------------------------------

# 4. Configuration File

في:

``` text
config/services.php
```

نضيف:

``` php
'TURBO_api' => [

    'base_url' => env(
        'TURBO_API_BASE_URL',
        'https://domain.com'
    ),

    'main_client_code' => env(
        'TURBO_MAIN_CLIENT_CODE'
    ),

    'authentication_key' => env(
        'TURBO_AUTHENTICATION_KEY'
    ),

    'timeout' => 30,

    'connect_timeout' => 10,

    'retry_times' => 3,

    'retry_sleep' => 500,

],
```

بعد تعديل `.env` أو `config/services.php`:

``` bash
php artisan config:clear
```

وفي Production يمكن استخدام:

``` bash
php artisan config:cache
```

------------------------------------------------------------------------

# 5. Authentication

الـ API يستخدم:

``` text
main_client_code
authentication_key
```

ويتم إرسالهم مع الـ GET Requests كـ Query Parameters.

مثال:

``` http
GET /external-api/turbo/categories
```

يتحول إلى:

``` http
GET /external-api/turbo/categories?main_client_code=25847&authentication_key=YOUR_AUTHENTICATION_KEY
```

داخل الـ Service يتم ذلك تلقائيًا من خلال:

``` php
protected function authParams(
    array $params = []
): array {

    return array_merge([

        'main_client_code' => $this->mainClientCode,

        'authentication_key' => $this->authenticationKey,

    ], $params);
}
```

وبالتالي لا يحتاج كل Method إلى تكرار بيانات المصادقة.

------------------------------------------------------------------------

# 6. HTTP Client

يتم إنشاء HTTP Client مركزي:

``` php
protected function client(): PendingRequest
{
    return Http::baseUrl($this->baseUrl)
        ->acceptJson()
        ->timeout(30)
        ->connectTimeout(10)
        ->retry(3, 500, throw: false);
}
```

## Timeout

``` text
timeout = 30 seconds
```

إذا لم يرجع الـ API Response خلال 30 ثانية يتم اعتبار العملية فاشلة.

------------------------------------------------------------------------

## Connect Timeout

``` text
connect_timeout = 10 seconds
```

وهو الوقت المسموح لإنشاء الاتصال بالسيرفر الخارجي.

------------------------------------------------------------------------

## Retry

في حالة فشل مؤقت في الاتصال، يحاول Laravel إرسال Request مرة أخرى.

الإعداد:

``` text
3 attempts
500ms delay
```

الفلو:

``` text
Request 1
   |
   | Failed
   v
Wait 500ms
   |
   v
Request 2
   |
   | Failed
   v
Wait 500ms
   |
   v
Request 3
```

------------------------------------------------------------------------

# 7. Service Location

الملف:

``` text
app/Services/Turbo/TurboApiService.php
```

يتم إنشاءه بواسطة:

``` bash
php artisan make:class Services/Turbo/TurboApiService
```

------------------------------------------------------------------------

# 8. API Endpoints

## Endpoint Summary

  --------------------------------------------------------------------------------------------------
  العملية                 HTTP Method             Endpoint
  ----------------------- ----------------------- --------------------------------------------------
  جلب الفئات              GET                     `/external-api/turbo/categories`

  جلب الحالات             GET                     `/external-api/turbo/statuses`

  جلب التعليمات           GET                     `/external-api/turbo/instructions`

  جلب التذاكر             GET                     `/external-api/turbo`

  التحقق من إنشاء تذكرة   GET                     `/external-api/turbo/can-create`

  إنشاء تذكرة             POST                    `/external-api/turbo/create`

  إضافة رسالة             POST                    `/external-api/turbo/{turboId}/message-store`

  تقييم تذكرة             POST                    `/external-api/turbo/{turboId}/rate`

  إجمالي التذاكر المفتوحة GET                     `/external-api/turbo/total-open-turbo`
  --------------------------------------------------------------------------------------------------

------------------------------------------------------------------------

# 9. Endpoint: Get Categories

## الهدف

جلب كل فئات التذاكر التي يمكن للمستخدم اختيار واحدة منها عند إنشاء تذكرة
جديدة.

------------------------------------------------------------------------

## Request

``` http
GET /external-api/turbo/categories
```

Authentication:

``` text
main_client_code
authentication_key
```

Full Request:

``` http
GET https://domain.com/external-api/turbo/categories?main_client_code=25847&authentication_key=YOUR_AUTHENTICATION_KEY
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$categories = $turboApi->getCategories();
```

------------------------------------------------------------------------

## Service Method

``` php
public function getCategories(): array
{
    $response = $this->client()->get(
        '/external-api/turbo/categories',
        $this->authParams()
    );

    return $this->handleResponse($response);
}
```

------------------------------------------------------------------------

## Expected Response

الـ Response النهائي يعتمد على الـ External API.

مثال عام:

``` json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Technical Support"
        },
        {
            "id": 2,
            "name": "Orders"
        }
    ]
}
```

------------------------------------------------------------------------

## الاستخدام

يتم استخدام الفئات في Form إنشاء التذكرة:

``` text
GET Categories
       |
       v
عرض الفئات للمستخدم
       |
       v
المستخدم يختار Category
       |
       v
category_id
       |
       v
Create Turbo
```

------------------------------------------------------------------------

# 10. Endpoint: Get Statuses

## الهدف

جلب الحالات التي يمكن أن تكون عليها التذكرة.

مثال:

``` text
Open
Pending
In Progress
Closed
```

------------------------------------------------------------------------

## Request

``` http
GET /external-api/turbo/statuses
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$statuses = $turboApi->getStatuses();
```

------------------------------------------------------------------------

## Service Method

``` php
public function getStatuses(): array
{
    $response = $this->client()->get(
        '/external-api/turbo/statuses',
        $this->authParams()
    );

    return $this->handleResponse($response);
}
```

------------------------------------------------------------------------

# 11. Endpoint: Get Instructions

## الهدف

جلب تعليمات استخدام نظام التذاكر.

مثال:

``` text
- اكتب وصفًا واضحًا للمشكلة.
- أرفق صورة عند الحاجة.
- لا تنشئ أكثر من تذكرة لنفس المشكلة.
```

------------------------------------------------------------------------

## Request

``` http
GET /external-api/turbo/instructions
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$instructions = $turboApi->getInstructions();
```

------------------------------------------------------------------------

# 12. Endpoint: Get Turbo

## الهدف

جلب تذاكر العميل.

------------------------------------------------------------------------

## Request

``` http
GET /external-api/turbo
```

Authentication:

``` text
main_client_code
authentication_key
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$turbo = $turboApi->getTurbo();
```

------------------------------------------------------------------------

## With Filters

الـ Service تدعم تمرير Filters:

``` php
$turbo = $turboApi->getTurbo([
    'status_id' => 1,
    'category_id' => 2,
]);
```

ويتم دمجها مع Authentication:

``` text
main_client_code
authentication_key
status_id
category_id
```

------------------------------------------------------------------------

## Response Example

``` json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "category_id": 1,
            "content": "Order issue",
            "status": "Open",
            "created_at": "2026-07-20T18:00:00Z"
        }
    ]
}
```

------------------------------------------------------------------------

# 13. Endpoint: Can Create Turbo

## الهدف

التحقق من أن العميل مسموح له بإنشاء تذكرة جديدة.

قبل عرض Form إنشاء تذكرة، يمكن تنفيذ:

``` php
$permission = $turboApi->canCreateTurbo();
```

------------------------------------------------------------------------

## Request

``` http
GET /external-api/turbo/can-create
```

------------------------------------------------------------------------

## Flow

``` text
User opens Create Turbo page
              |
              v
canCreateTurbo()
              |
       +------+------+
       |             |
     Allowed       Not Allowed
       |             |
       v             v
Show Form      Show Message
```

------------------------------------------------------------------------

# 14. Endpoint: Create Turbo

## الهدف

إنشاء تذكرة جديدة.

------------------------------------------------------------------------

## Endpoint

``` http
POST /external-api/turbo/create
```

------------------------------------------------------------------------

## Required Fields

  Field         Type      Required
  ------------- --------- ----------
  category_id   Integer   Yes
  content       String    Yes
  code          String    No
  media         Files     No

------------------------------------------------------------------------

## Request Without Files

``` text
category_id = 1
content = Detailed description of the issue
code = ORDER-123
```

------------------------------------------------------------------------

## Request With Files

يتم استخدام:

``` text
multipart/form-data
```

مثال:

``` text
category_id: 1
content: Detailed description of the issue
code: ORDER-123
media[]: image.jpg
media[]: invoice.pdf
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$turbo = $this->turboApi->createTurbo(

    categoryId: 1,

    content: 'تفاصيل المشكلة',

    code: 'ORDER-123'

);
```

مع Files:

``` php
$turbo = $this->turboApi->createTurbo(

    categoryId: 1,

    content: 'تفاصيل المشكلة',

    code: 'ORDER-123',

    media: $request->file('media', [])

);
```

------------------------------------------------------------------------

## Complete Flow

``` text
User
 |
 | Submit Turbo Form
 v
Controller
 |
 | Validate
 v
TurboApiService
 |
 | Build Multipart Request
 v
External API
 |
 | Create Turbo
 v
Response
 |
 v
Controller
 |
 v
JSON Response
```

------------------------------------------------------------------------

# 15. Create Turbo Controller

``` php
public function store(Request $request)
{
    $validated = $request->validate([

        'category_id' => [
            'required',
            'integer',
        ],

        'content' => [
            'required',
            'string',
            'min:10',
        ],

        'code' => [
            'nullable',
            'string',
        ],

        'media.*' => [
            'nullable',
            'file',
            'max:10240',
        ],

    ]);

    $turbo = $this->turboApi->createTurbo(

        categoryId: $validated['category_id'],

        content: $validated['content'],

        code: $validated['code'] ?? null,

        media: $request->file('media', [])

    );

    return response()->json([

        'success' => true,

        'message' => 'Turbo created successfully',

        'data' => $turbo,

    ]);
}
```

------------------------------------------------------------------------

# 16. Endpoint: Add Message

## الهدف

إضافة رسالة أو تحديث إلى تذكرة موجودة.

------------------------------------------------------------------------

## Endpoint

``` http
POST /external-api/turbo/{turboId}/message-store
```

مثال:

``` http
POST /external-api/turbo/15/message-store
```

------------------------------------------------------------------------

## Fields

  Field       Type      Required
  ----------- --------- ----------
  turbo_id   Integer   Yes
  content     String    Yes
  media       Files     No

------------------------------------------------------------------------

## Laravel Usage

``` php
$message = $turboApi->addMessage(

    turboId: 15,

    content: 'تحديث جديد بخصوص المشكلة'

);
```

مع مرفقات:

``` php
$message = $turboApi->addMessage(

    turboId: 15,

    content: 'تم إرفاق صورة جديدة',

    media: $request->file('media', [])

);
```

------------------------------------------------------------------------

## Flow

``` text
Open Turbo
     |
     v
User writes message
     |
     v
Optional Attachments
     |
     v
Controller Validation
     |
     v
TurboApiService
     |
     v
External API
     |
     v
Message Created
```

------------------------------------------------------------------------

# 17. Endpoint: Rate Turbo

## الهدف

تقييم التذكرة بعد انتهاء المشكلة.

------------------------------------------------------------------------

## Endpoint

``` http
POST /external-api/turbo/{turboId}/rate
```

مثال:

``` http
POST /external-api/turbo/15/rate
```

------------------------------------------------------------------------

## Request

``` json
{
    "rate": 5
}
```

------------------------------------------------------------------------

## Validation

``` php
'rate' => [
    'required',
    'integer',
    'min:1',
    'max:5',
],
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$result = $turboApi->rateTurbo(

    turboId: 15,

    rate: 5

);
```

------------------------------------------------------------------------

# 18. Endpoint: Total Open Turbo

## الهدف

الحصول على عدد التذاكر المفتوحة.

------------------------------------------------------------------------

## Request

``` http
GET /external-api/turbo/total-open-turbo
```

Authentication:

``` text
main_client_code
authentication_key
```

------------------------------------------------------------------------

## Laravel Usage

``` php
$totalOpenTurbo =
    $turboApi->getTotalOpenTurbo();
```

------------------------------------------------------------------------

## Example Dashboard Flow

``` text
Dashboard
    |
    +-- Total Turbo
    |
    +-- Open Turbo
    |
    +-- Closed Turbo
    |
    +-- Pending Turbo
```

يمكن استخدام:

``` php
$totalOpenTurbo =
    $turboApi->getTotalOpenTurbo();
```

لعرض الرقم في Dashboard.

------------------------------------------------------------------------

# 19. Media Upload System

الـ API يدعم الملفات من خلال:

``` text
multipart/form-data
```

في Laravel:

``` php
$request = $this->client()->asMultipart();
```

ثم:

``` php
$request->attach(
    'media[]',
    fopen(
        $file->getRealPath(),
        'r'
    ),
    $file->getClientOriginalName()
);
```

------------------------------------------------------------------------

## Supported Input

الـ Service تستقبل:

``` php
array $media = []
```

والعناصر يجب أن تكون:

``` php
UploadedFile
```

مثال:

``` php
$request->file('media')
```

------------------------------------------------------------------------

## Multiple Files

``` html
<input
    type="file"
    name="media[]"
    multiple
>
```

ثم:

``` php
$request->file('media', [])
```

------------------------------------------------------------------------

# 20. Response Handling

كل Response يمر من:

``` php
protected function handleResponse($response): array
```

إذا كان Request ناجحًا:

``` php
return $response->json() ?? [];
```

إذا فشل:

``` php
if ($response->failed()) {

    Log::error(
        'Turbo API Error',
        [
            'status' => $response->status(),
            'body' => $response->body(),
        ]
    );

    throw new RuntimeException(
        'Turbo API request failed: '
        . $response->status()
    );
}
```

------------------------------------------------------------------------

# 21. Error Flow

``` text
Laravel
   |
   v
Send Request
   |
   v
External API
   |
   +-------------------+
   |                   |
 Success             Failed
   |                   |
   v                   v
Return JSON       Log Error
                       |
                       v
                 Throw Exception
```

------------------------------------------------------------------------

# 22. Controller Usage

``` php
class TurboController extends Controller
{
    public function __construct(
        protected TurboApiService $turboApi
    ) {}

    public function index()
    {
        $turbo =
            $this->turboApi->getTurbo();

        $categories =
            $this->turboApi->getCategories();

        $statuses =
            $this->turboApi->getStatuses();

        return view(
            'turbo.index',
            compact(
                'turbo',
                'categories',
                'statuses'
            )
        );
    }
}
```

------------------------------------------------------------------------

# 23. Routes

``` php
use App\Http\Controllers\TurboController;

Route::prefix('turbo')
    ->name('turbo.')
    ->group(function () {

        Route::get(
            '/',
            [
                TurboController::class,
                'index'
            ]
        )->name('index');

        Route::post(
            '/',
            [
                TurboController::class,
                'store'
            ]
        )->name('store');

        Route::post(
            '/{turbo}/messages',
            [
                TurboController::class,
                'addMessage'
            ]
        )->name('messages.store');

        Route::post(
            '/{turbo}/rate',
            [
                TurboController::class,
                'rate'
            ]
        )->name('rate');

    });
```

------------------------------------------------------------------------

# 24. Full Turbo Lifecycle

## Step 1: Load Metadata

عند فتح شاشة التذاكر:

``` text
Get Categories
Get Statuses
Get Instructions
```

------------------------------------------------------------------------

## Step 2: Check Permission

قبل إنشاء تذكرة:

``` text
canCreateTurbo()
```

------------------------------------------------------------------------

## Step 3: User Fills Form

``` text
Category
Description
Order Code
Attachments
```

------------------------------------------------------------------------

## Step 4: Validation

Laravel يتحقق من:

``` text
category_id
content
code
media
```

------------------------------------------------------------------------

## Step 5: Create Turbo

``` text
createTurbo()
```

------------------------------------------------------------------------

## Step 6: View Turbo

``` text
getTurbo()
```

------------------------------------------------------------------------

## Step 7: Add Follow-up Message

``` text
addMessage()
```

------------------------------------------------------------------------

## Step 8: Close Turbo

بعد حل المشكلة:

``` text
rateTurbo()
```

------------------------------------------------------------------------

# 25. Complete Business Flow

``` text
┌─────────────────────────────┐
│       User Opens Turbo    │
└──────────────┬──────────────┘
               |
               v
┌─────────────────────────────┐
│ Get Categories              │
│ Get Statuses                │
│ Get Instructions            │
└──────────────┬──────────────┘
               |
               v
┌─────────────────────────────┐
│ Get Existing Turbo        │
└──────────────┬──────────────┘
               |
               v
        User Wants New Turbo
               |
               v
┌─────────────────────────────┐
│ Can Create Turbo?          │
└──────────────┬──────────────┘
               |
               v
        Yes
               |
               v
┌─────────────────────────────┐
│ User Fills Turbo Form      │
└──────────────┬──────────────┘
               |
               v
┌─────────────────────────────┐
│ Laravel Validation          │
└──────────────┬──────────────┘
               |
               v
┌─────────────────────────────┐
│ Create Turbo               │
└──────────────┬──────────────┘
               |
               v
        Turbo Created
               |
               v
┌─────────────────────────────┐
│ Add Messages / Attachments  │
└──────────────┬──────────────┘
               |
               v
        Issue Resolved
               |
               v
┌─────────────────────────────┐
│ Rate Turbo                 │
└─────────────────────────────┘
```

------------------------------------------------------------------------

# 26. Calling the Service From Anywhere

يمكن استخدام Service من أي مكان:

``` php
$turboApi = app(
    \App\Services\Turbo\TurboApiService::class
);
```

ثم:

``` php
$categories =
    $turboApi->getCategories();
```

أو:

``` php
$turbo =
    $turboApi->getTurbo();
```

أو:

``` php
$turbo =
    $turboApi->createTurbo(
        categoryId: 1,
        content: 'تفاصيل المشكلة',
        code: 'ORDER-123'
    );
```

أو:

``` php
$message =
    $turboApi->addMessage(
        turboId: 15,
        content: 'تحديث جديد'
    );
```

------------------------------------------------------------------------

# 27. Recommended Frontend Data Flow

يفضل أن يكون تدفق البيانات كالآتي:

``` text
Frontend
   |
   v
Laravel Controller
   |
   v
Validation
   |
   v
TurboApiService
   |
   v
External API
   |
   v
Response
   |
   v
Laravel Controller
   |
   v
Frontend
```

لا يفضل:

``` text
Frontend
   |
   v
External API مباشرة
```

لأن ذلك قد يكشف:

-   authentication_key
-   main_client_code
-   API structure
-   External API URL

لذلك Laravel يجب أن يكون Proxy / Integration Layer.

------------------------------------------------------------------------

# 28. Security Rules

## لا تضع Authentication Key في Frontend

خطأ:

``` javascript
fetch(
    'https://domain.com/external-api/turbo'
);
```

مع:

``` javascript
authentication_key
```

هذا يكشف المفتاح للمستخدم.

------------------------------------------------------------------------

## الصحيح

``` text
Browser
   |
   v
Laravel
   |
   v
External API
```

والـ Secret يظل داخل:

``` text
.env
```

------------------------------------------------------------------------

# 29. Testing

يمكن اختبار كل Method يدويًا:

``` php
$service = app(
    TurboApiService::class
);

$categories =
    $service->getCategories();

dd($categories);
```

اختبار إنشاء تذكرة:

``` php
$turbo =
    $service->createTurbo(
        categoryId: 1,
        content: 'Test turbo',
        code: 'TEST-001'
    );

dd($turbo);
```

------------------------------------------------------------------------

# 30. Important Note About the Base URL

القيمة الحالية:

``` env
TURBO_API_BASE_URL=https://domain.com
```

هي Placeholder فقط.

عند معرفة الـ URL الحقيقي:

``` env
TURBO_API_BASE_URL=https://real-api-domain.com
```

ثم:

``` bash
php artisan config:clear
```

أو:

``` bash
php artisan config:cache
```

ولا يحتاج أي Endpoint أو Controller أو Service إلى التعديل.

------------------------------------------------------------------------

# 31. Final Summary

الـ Integration مبني على مبدأ:

``` text
Configuration
      |
      v
TurboApiService
      |
      v
External API
```

والـ Service توفر:

``` php
getCategories()
getStatuses()
getInstructions()
getTurbo()
canCreateTurbo()
createTurbo()
addMessage()
rateTurbo()
getTotalOpenTurbo()
```

كل العمليات يتم تنفيذها من خلال Service واحدة، مما يجعل النظام:

-   أسهل في الصيانة.
-   أكثر أمانًا.
-   قابلًا للتوسع.
-   قابلًا للاختبار.
-   غير مرتبط بالـ Controller.
-   سهل تغيير الـ Base URL.
-   سهل إضافة Endpoints جديدة مستقبلًا.

------------------------------------------------------------------------

## Endpoint Quick Reference

``` text
GET
/external-api/turbo/categories

GET
/external-api/turbo/statuses

GET
/external-api/turbo/instructions

GET
/external-api/turbo

GET
/external-api/turbo/can-create

POST
/external-api/turbo/create

POST
/external-api/turbo/{turboId}/message-store

POST
/external-api/turbo/{turboId}/rate

GET
/external-api/turbo/total-open-turbo
```

------------------------------------------------------------------------

**End of Documentation**
