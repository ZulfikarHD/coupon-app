# Coding Guide - Coupon Management System

**Last Updated:** November 20, 2025  
**Version:** 1.0  
**Technology Stack:** Laravel 11 + Inertia.js + Vue 3 + TypeScript + shadcn/ui

---

## 📋 Table of Contents

1. [Project Structure](#project-structure)
2. [Technology Stack](#technology-stack)
3. [Coding Standards](#coding-standards)
4. [Design System](#design-system)
5. [Component Guidelines](#component-guidelines)
6. [Backend Guidelines](#backend-guidelines)
7. [Database Conventions](#database-conventions)
8. [Testing Guidelines](#testing-guidelines)
9. [Git Workflow](#git-workflow)
10. [Common Patterns](#common-patterns)

---

## 📁 Project Structure

```
coupon-management-system/
│
├── app/
│   ├── Actions/
│   │   └── Fortify/          # Laravel Fortify actions
│   ├── Helpers/               # Helper classes (utility functions)
│   │   └── PhoneHelper.php
│   ├── Http/
│   │   ├── Controllers/       # Route controllers (thin, HTTP-focused)
│   │   │   ├── Controller.php
│   │   │   ├── CouponController.php
│   │   │   ├── DashboardController.php
│   │   │   └── Settings/      # Settings controllers
│   │   ├── Middleware/        # Custom middleware
│   │   └── Requests/          # Form request validation
│   ├── Jobs/                  # Queue jobs
│   │   └── GenerateCouponCode.php
│   ├── Models/                # Eloquent models (data layer)
│   │   ├── Coupon.php
│   │   ├── CouponValidation.php
│   │   └── User.php
│   ├── Services/              # Business logic services
│   │   ├── CouponService.php
│   │   ├── DashboardService.php
│   │   └── ReportService.php
│   └── Providers/             # Service providers
│
├── bootstrap/                 # Laravel bootstrap files
├── config/                    # Configuration files
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
│
├── public/                    # Public assets
│
├── resources/
│   ├── css/
│   │   └── app.css            # Tailwind CSS + custom styles
│   ├── js/
│   │   ├── app.ts             # Main Vue app entry
│   │   ├── ssr.ts             # SSR entry (if using SSR)
│   │   ├── components/        # Vue components
│   │   │   └── ui/            # shadcn/ui components
│   │   ├── composables/       # Vue composables
│   │   ├── layouts/           # Page layouts
│   │   │   ├── AppLayout.vue
│   │   │   └── GuestLayout.vue
│   │   ├── lib/               # Utility libraries
│   │   ├── pages/             # Inertia pages
│   │   │   ├── Dashboard.vue
│   │   │   ├── coupons/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   ├── Show.vue
│   │   │   │   └── Public.vue
│   │   │   └── scan/
│   │   │       └── Index.vue
│   │   └── types/             # TypeScript types
│   └── views/
│       └── app.blade.php      # Root Blade template
│
├── routes/
│   ├── api.php                # API routes
│   ├── console.php            # Artisan commands
│   ├── settings.php           # Settings routes
│   └── web.php                # Web routes
│
├── storage/                   # Storage directory
├── tests/                     # Tests
│   ├── Feature/               # Feature tests
│   └── Unit/                  # Unit tests
│
├── .env                       # Environment variables
├── composer.json              # PHP dependencies
├── package.json               # Node dependencies
├── phpunit.xml                # PHPUnit configuration
├── tsconfig.json              # TypeScript configuration
├── vite.config.ts             # Vite build configuration
└── tailwind.config.js         # Tailwind CSS configuration

```

---

## 🛠 Technology Stack

### **Backend**

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.2+ | Server-side language |
| Laravel | 11.x | PHP framework |
| Laravel Fortify | Latest | Authentication |
| Laravel Sanctum | Latest | API authentication |
| MySQL/PostgreSQL | 8.0+ / 14+ | Database |

### **Frontend**

| Technology | Version | Purpose |
|------------|---------|---------|
| Node.js | 18+ | JavaScript runtime |
| Vue 3 | 3.x | Frontend framework |
| TypeScript | 5.x | Type safety |
| Inertia.js | 1.x | SPA adapter for Laravel |
| Vite | 5.x | Build tool |
| Tailwind CSS | 3.x | CSS framework |
| shadcn/ui | Latest | UI component library |

### **Key Libraries**

| Library | Purpose |
|---------|---------|
| `html5-qrcode` | QR code scanning |
| `qrcode` | QR code generation |
| `lucide-vue-next` | Icons |
| `@inertiajs/vue3` | Inertia Vue adapter |
| `class-variance-authority` | Component variants |
| `clsx` | Class name utility |
| `tailwind-merge` | Tailwind class merging |

---

## 📝 Coding Standards

### **PHP (Laravel)**

#### **Naming Conventions**

```php
// Classes: PascalCase
class CouponController extends Controller {}

// Methods: camelCase
public function store(Request $request) {}

// Variables: camelCase
$couponCode = 'ABC-1234-XYZ';

// Constants: UPPERCASE_SNAKE_CASE
public const STATUS_ACTIVE = 'active';

// Database tables: plural_snake_case
Schema::create('coupon_validations', function (Blueprint $table) {});

// Database columns: snake_case
$table->string('customer_phone');

// Routes: kebab-case
Route::get('/scan-coupon', [Controller::class, 'scan']);
```

#### **Code Style**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons.
     */
    public function index(Request $request): Response
    {
        // Group related logic
        $query = Coupon::with('user')
            ->orderBy('created_at', 'desc');

        // Single responsibility per method
        $this->applyFilters($query, $request);

        // Return Inertia response
        return Inertia::render('coupons/Index', [
            'coupons' => $query->paginate(20),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
    }
}
```

#### **Model Best Practices**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Coupon extends Model
{
    // Constants at top
    public const STATUS_ACTIVE = 'active';
    public const STATUS_USED = 'used';
    public const STATUS_EXPIRED = 'expired';

    // Mass assignment protection
    protected $fillable = [
        'code',
        'type',
        'customer_name',
        'customer_phone',
        // ...
    ];

    // Type casting
    protected $casts = [
        'expires_at' => 'date',
    ];

    // Mutators (setters)
    public function setCustomerPhoneAttribute($value): void
    {
        $this->attributes['customer_phone'] = $this->normalizePhone($value);
    }

    // Accessors (getters)
    public function getFormattedPhoneAttribute(): string
    {
        return $this->formatPhoneForDisplay($this->customer_phone);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(CouponValidation::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Helper methods
    public function canBeValidated(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    // Private utility methods
    private function normalizePhone(string $phone): string
    {
        // Implementation
    }
}
```

---

### **TypeScript (Vue)**

#### **Naming Conventions**

```typescript
// Components: PascalCase
export default defineComponent({
  name: 'CouponCard'
});

// Interfaces: PascalCase with descriptive name
interface CouponData {
  id: number;
  code: string;
}

// Variables/functions: camelCase
const couponCode = ref('');
const handleSubmit = () => {};

// Constants: UPPERCASE_SNAKE_CASE
const MAX_RETRIES = 3;

// Files: PascalCase for components, kebab-case for others
// ✅ CouponCard.vue
// ✅ useCouponFilters.ts
// ✅ coupon-service.ts
```

#### **Component Structure**

```vue
<script setup lang="ts">
// 1. Imports (grouped by category)
// - Vue core
import { ref, computed, onMounted } from 'vue';
// - Inertia
import { Head, router } from '@inertiajs/vue3';
// - UI components
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
// - Icons
import { Plus, Edit } from 'lucide-vue-next';
// - Types
import type { Coupon } from '@/types';

// 2. Props interface (if needed)
interface Props {
  coupon: Coupon;
  readonly?: boolean;
}

// 3. Props definition
const props = defineProps<Props>();

// 4. Emits (if needed)
const emit = defineEmits<{
  save: [coupon: Coupon];
  cancel: [];
}>();

// 5. Reactive state
const isEditing = ref(false);
const form = ref({
  name: props.coupon.name,
  type: props.coupon.type,
});

// 6. Computed properties
const isValid = computed(() => {
  return form.value.name && form.value.type;
});

// 7. Methods
const handleSave = () => {
  if (!isValid.value) return;
  
  emit('save', {
    ...props.coupon,
    ...form.value,
  });
};

const handleCancel = () => {
  form.value = {
    name: props.coupon.name,
    type: props.coupon.type,
  };
  emit('cancel');
};

// 8. Lifecycle hooks
onMounted(() => {
  // Initialization
});
</script>

<template>
  <!-- Template content -->
  <div class="space-y-4">
    <Card>
      <CardHeader>
        <CardTitle>{{ coupon.code }}</CardTitle>
      </CardHeader>
      <CardContent>
        <!-- Content -->
      </CardContent>
    </Card>
  </div>
</template>
```

#### **Type Safety**

```typescript
// Define interfaces for all data structures
interface Coupon {
  id: number;
  code: string;
  type: string;
  status: 'active' | 'used' | 'expired'; // Use union types
  expires_at: string | null;
}

// Use type annotations for function parameters and return types
function formatCouponCode(coupon: Coupon): string {
  return coupon.code.toUpperCase();
}

// Use readonly for props that shouldn't be modified
interface Props {
  readonly coupon: Coupon;
  readonly isEditable: boolean;
}

// Use generics for reusable types
interface ApiResponse<T> {
  data: T;
  message: string;
  success: boolean;
}
```

---

## 🎨 Design System

### **Color Palette**

Based on Tailwind CSS and shadcn/ui theming:

```css
/* Primary Colors */
--primary: #3B82F6;           /* Blue - Primary actions */
--primary-foreground: #FFFFFF;

/* Status Colors */
--success: #10B981;            /* Green - Active, success */
--warning: #F59E0B;            /* Orange - Warning, expiring */
--danger: #EF4444;             /* Red - Error, expired */
--info: #3B82F6;               /* Blue - Information */

/* Neutral Colors */
--background: #FFFFFF;         /* Light mode background */
--foreground: #0A0A0A;         /* Light mode text */
--muted: #F4F4F5;              /* Muted backgrounds */
--muted-foreground: #71717A;   /* Muted text */

/* Dark Mode */
--dark-background: #0A0A0A;
--dark-foreground: #FAFAFA;
--dark-muted: #27272A;
--dark-muted-foreground: #A1A1AA;
```

### **Typography**

```css
/* Font Sizes */
--text-xs: 0.75rem;      /* 12px */
--text-sm: 0.875rem;     /* 14px */
--text-base: 1rem;       /* 16px */
--text-lg: 1.125rem;     /* 18px */
--text-xl: 1.25rem;      /* 20px */
--text-2xl: 1.5rem;      /* 24px */
--text-3xl: 1.875rem;    /* 30px */

/* Font Weights */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;

/* Line Heights */
--leading-none: 1;
--leading-tight: 1.25;
--leading-normal: 1.5;
--leading-relaxed: 1.75;
```

### **Spacing Scale**

```css
/* Tailwind spacing scale */
0   = 0px
1   = 0.25rem  (4px)
2   = 0.5rem   (8px)
3   = 0.75rem  (12px)
4   = 1rem     (16px)
5   = 1.25rem  (20px)
6   = 1.5rem   (24px)
8   = 2rem     (32px)
10  = 2.5rem   (40px)
12  = 3rem     (48px)
```

### **Border Radius**

```css
--radius-sm: 0.375rem;   /* 6px - Small elements */
--radius-md: 0.5rem;     /* 8px - Cards, inputs */
--radius-lg: 0.75rem;    /* 12px - Modals */
--radius-xl: 1rem;       /* 16px - Large cards */
--radius-full: 9999px;   /* Full rounded */
```

### **Shadows**

```css
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
--shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
```

---

### **Status Badge Styles**

```typescript
// Consistent badge styling across the app
const statusColors = {
  active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
  used: 'bg-gray-500/10 text-gray-700 dark:text-gray-400 border-gray-500/20',
  expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
};

const statusLabels = {
  active: 'Aktif',
  used: 'Terpakai',
  expired: 'Kedaluwarsa',
};
```

### **Button Variants**

```typescript
// shadcn/ui button variants
<Button variant="default">    {/* Primary action - blue */}
<Button variant="destructive"> {/* Delete/danger - red */}
<Button variant="outline">     {/* Secondary - outlined */}
<Button variant="ghost">       {/* Tertiary - no background */}
<Button variant="link">        {/* Link style */}

// Button sizes
<Button size="sm">    {/* Small */}
<Button size="default"> {/* Default */}
<Button size="lg">    {/* Large */}
<Button size="icon">  {/* Icon only */}
```

---

### **Responsive Design**

```css
/* Mobile-first approach */
/* Base styles = mobile (< 640px) */
.container {
  padding: 1rem; /* 16px on mobile */
}

/* Tablet (sm: 640px+) */
@media (min-width: 640px) {
  .container {
    padding: 1.5rem; /* 24px on tablet */
  }
}

/* Desktop (md: 768px+) */
@media (min-width: 768px) {
  .container {
    padding: 2rem; /* 32px on desktop */
  }
}

/* Large desktop (lg: 1024px+) */
@media (min-width: 1024px) {
  .container {
    padding: 3rem; /* 48px on large screens */
  }
}
```

**Tailwind Responsive Classes:**
```html
<!-- Mobile: stack vertically, Desktop: side by side -->
<div class="flex flex-col md:flex-row gap-4">
  <div class="w-full md:w-1/2">Column 1</div>
  <div class="w-full md:w-1/2">Column 2</div>
</div>

<!-- Mobile: hidden, Desktop: visible -->
<span class="hidden md:inline">Desktop only</span>

<!-- Mobile: visible, Desktop: hidden -->
<span class="md:hidden">Mobile only</span>

<!-- Responsive text sizes -->
<h1 class="text-2xl md:text-3xl lg:text-4xl">Responsive Heading</h1>

<!-- Responsive padding -->
<div class="p-4 md:p-6 lg:p-8">Content</div>
```

---

## 🧩 Component Guidelines

### **shadcn/ui Components**

Located in `/resources/js/components/ui/`

#### **Available Components**

- **Layout:** `Card`, `CardHeader`, `CardContent`, `CardFooter`
- **Forms:** `Input`, `Label`, `Textarea`, `Select`
- **Buttons:** `Button`
- **Feedback:** `Badge`, `Alert`, `Toast`
- **Overlays:** `Dialog`, `Popover`, `DropdownMenu`
- **Navigation:** `Breadcrumb`, `Tabs`
- **Disclosure:** `Collapsible`, `Accordion`

#### **Usage Example**

```vue
<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
</script>

<template>
  <Card class="border-2">
    <CardHeader>
      <CardTitle>Coupon Information</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        <Badge variant="outline">Active</Badge>
        <Button>Action</Button>
      </div>
    </CardContent>
  </Card>
</template>
```

### **Icon System**

Using `lucide-vue-next` for all icons:

```vue
<script setup lang="ts">
import { 
  Plus,          // Add/Create actions
  Edit,          // Edit actions
  Trash2,        // Delete actions
  Eye,           // View actions
  Search,        // Search functionality
  Filter,        // Filter functionality
  QrCode,        // QR related
  ScanLine,      // Scanning
  CheckCircle2,  // Success states
  XCircle,       // Error states
  AlertCircle,   // Warning states
  Clock,         // Time-related
  User,          // User-related
  Phone,         // Contact info
  Mail,          // Email
} from 'lucide-vue-next';
</script>

<template>
  <Button class="gap-2">
    <Plus class="h-4 w-4" />
    Add Coupon
  </Button>
</template>
```

**Icon Sizing:**
- `h-3 w-3` (12px) - Small, inline with text
- `h-4 w-4` (16px) - Standard button icons
- `h-5 w-5` (20px) - Card headers, larger buttons
- `h-6 w-6` (24px) - Page headers
- `h-8 w-8` (32px) - Feature icons

---

### **Page Layout Structure**

```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Coupons', href: '/coupons' },
];
</script>

<template>
  <Head title="Page Title" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
      <!-- Page Header -->
      <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
          Page Title
        </h1>
        <p class="text-sm text-muted-foreground md:text-base">
          Page description
        </p>
      </div>

      <!-- Main Content -->
      <Card class="border-2">
        <CardContent>
          <!-- Content here -->
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
```

---

## 🗄 Backend Guidelines

### **Controller Pattern**

Controllers should be thin and focused on HTTP concerns:
- Handle request/response formatting
- Validate input
- Delegate business logic to Services
- Return appropriate responses (Inertia pages, JSON, redirects)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Display a listing (Inertia page).
     */
    public function index(Request $request): Response
    {
        $query = Coupon::with('user')
            ->orderBy('created_at', 'desc');

        // Delegate filtering logic to service
        $this->couponService->applyFilters($query, $request);

        return Inertia::render('coupons/Index', [
            'coupons' => $query->paginate(20),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show create form (Inertia page).
     */
    public function create(): Response
    {
        return Inertia::render('coupons/Create');
    }

    /**
     * Store new resource (redirect).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string'],
            // ...
        ]);

        // Delegate business logic to service
        $coupon = $this->couponService->create($validated);

        return redirect()
            ->route('coupons.show', $coupon)
            ->with('success', 'Coupon created successfully!');
    }

    /**
     * API endpoint (JSON response).
     */
    public function check(string $code)
    {
        $result = $this->couponService->check($code);

        return response()->json(
            \Illuminate\Support\Arr::except($result, 'status_code'),
            $result['status_code']
        );
    }
}
```

### **Service Pattern**

Services contain business logic and complex operations that don't belong in controllers or models.

**When to create a Service:**
- Complex business logic (validation, calculations, transformations)
- Operations involving multiple models
- Reusable logic across multiple controllers
- Complex query building/filtering
- External API integrations
- Heavy data processing

**Service Structure:**

```php
<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponService
{
    /**
     * Apply filters to coupon query
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        // Complex filtering logic here
        if ($request->filled('status')) {
            $query->whereIn('status', $this->parseStatusFilter($request));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%");
            });
        }

        return $query;
    }

    /**
     * Create a new coupon with business logic
     */
    public function create(array $data): Coupon
    {
        // Normalize data
        $data['customer_phone'] = PhoneHelper::normalize($data['customer_phone']);
        $data['status'] = Coupon::STATUS_ACTIVE;
        $data['created_by'] = auth()->id();

        // Use Job for code generation
        GenerateCouponCode::dispatchSync($data);

        // Retrieve created coupon
        return Coupon::where('created_by', auth()->id())
            ->latest('id')
            ->firstOrFail();
    }

    /**
     * Validate a coupon (business logic)
     */
    public function validate(string $code, string $password): array
    {
        // Verify password
        if (!Hash::check($password, auth()->user()->password)) {
            return [
                'success' => false,
                'message' => 'Password salah',
                'status_code' => 401,
            ];
        }

        $coupon = Coupon::where('code', $code)->firstOrFail();

        // Business validation rules
        if (!$coupon->canBeValidated()) {
            return [
                'success' => false,
                'message' => $this->getValidationErrorMessage($coupon),
                'status_code' => 422,
            ];
        }

        // Update status and create validation record
        $coupon->status = Coupon::STATUS_USED;
        $coupon->save();

        CouponValidation::create([
            'coupon_id' => $coupon->id,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'action' => 'used',
        ]);

        return [
            'success' => true,
            'message' => 'Kupon berhasil divalidasi',
            'status_code' => 200,
        ];
    }

    /**
     * Private helper methods
     */
    protected function parseStatusFilter(Request $request): array
    {
        // Complex parsing logic
        return [];
    }

    protected function getValidationErrorMessage(Coupon $coupon): string
    {
        // Error message logic
        return 'Kupon tidak dapat divalidasi';
    }
}
```

**Service Best Practices:**

1. **Single Responsibility**: Each service should handle one domain (CouponService, ReportService, DashboardService)
2. **Dependency Injection**: Inject services into controllers via constructor
3. **Return Structured Data**: Return arrays or DTOs, not HTTP responses
4. **Testable**: Services should be easily unit testable without HTTP layer
5. **Reusable**: Services can be used by controllers, jobs, commands, etc.
6. **Helper Classes**: Use helper classes for utility functions (PhoneHelper, etc.)

**Helper Classes:**

For simple, stateless utility functions:

```php
<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Normalize phone number to international format
     */
    public static function normalize(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Format phone for display
     */
    public static function formatForDisplay(string $phone): string
    {
        // Formatting logic
        return $phone;
    }
}
```

### **Validation**

```php
// Option 1: Inline validation
$validated = $request->validate([
    'customer_name' => ['required', 'string', 'max:255'],
    'customer_phone' => ['required', 'string'],
    'customer_email' => ['nullable', 'email', 'max:255'],
    'expires_at' => ['nullable', 'date', 'after:today'],
]);

// Option 2: Form Request class (for complex validation)
php artisan make:request StoreCouponRequest

// app/Http/Requests/StoreCouponRequest.php
public function rules(): array
{
    return [
        'customer_name' => ['required', 'string', 'max:255'],
        'customer_phone' => ['required', 'string'],
        // ...
    ];
}

// In controller
public function store(StoreCouponRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

### **Query Optimization**

```php
// ❌ Bad: N+1 query problem
$coupons = Coupon::all();
foreach ($coupons as $coupon) {
    echo $coupon->user->name; // Triggers separate query for each coupon
}

// ✅ Good: Eager loading
$coupons = Coupon::with('user')->get();
foreach ($coupons as $coupon) {
    echo $coupon->user->name; // Uses single JOIN query
}

// ✅ Better: Select only needed columns
$coupons = Coupon::select(['id', 'code', 'type', 'created_by'])
    ->with('user:id,name')
    ->get();

// ✅ Best: Paginate large datasets
$coupons = Coupon::with('user')
    ->paginate(20);
```

---

## 🗃 Database Conventions

### **Migration Structure**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys (with constraints)
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            // Regular columns (ordered logically)
            $table->string('code')->unique();
            $table->string('type');
            $table->text('description');
            $table->string('customer_name');
            $table->string('customer_phone')->index(); // Add index for searches
            $table->string('customer_email')->nullable();
            $table->string('customer_social_media')->nullable();
            
            // Dates
            $table->date('expires_at')->nullable()->index();
            
            // Enums
            $table->enum('status', ['active', 'used', 'expired'])
                ->default('active')
                ->index();

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
```

### **Naming Conventions**

| Type | Convention | Example |
|------|------------|---------|
| Table | plural_snake_case | `coupons`, `coupon_validations` |
| Column | snake_case | `customer_phone`, `expires_at` |
| Primary Key | `id` | Always `id` |
| Foreign Key | `{model}_id` | `user_id`, `coupon_id` |
| Pivot Table | `{model1}_{model2}` | `coupon_user` (alphabetical) |
| Index | `{table}_{column}_index` | `coupons_code_index` |
| Unique | `{table}_{column}_unique` | `coupons_code_unique` |

---

## 🧪 Testing Guidelines

### **Feature Test Example**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_coupon(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/coupons', [
            'type' => 'Gratis 1 Kopi',
            'description' => 'Test coupon',
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coupons', [
            'type' => 'Gratis 1 Kopi',
            'customer_name' => 'John Doe',
        ]);
    }

    public function test_guest_cannot_create_coupon(): void
    {
        $response = $this->post('/coupons', [
            'type' => 'Gratis 1 Kopi',
        ]);

        $response->assertRedirect('/login');
    }
}
```

### **Unit Test Example**

```php
<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Tests\TestCase;

class CouponModelTest extends TestCase
{
    public function test_phone_normalization(): void
    {
        $coupon = new Coupon();
        $coupon->customer_phone = '08123456789';

        $this->assertEquals('628123456789', $coupon->customer_phone);
    }

    public function test_can_be_validated_returns_false_for_used_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
        ]);

        $this->assertFalse($coupon->canBeValidated());
    }
}
```

---

## 📚 Common Patterns

### **Inertia Page Rendering**

```php
// Controller
return Inertia::render('PageName', [
    'data' => $data,
    'filters' => $request->only(['search', 'status']),
]);

// Vue Component
interface Props {
  data: DataType;
  filters: {
    search?: string;
    status?: string;
  };
}

const props = defineProps<Props>();
```

### **Form Handling**

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
});

const submit = () => {
  form.post('/endpoint', {
    preserveScroll: true,
    onSuccess: () => {
      // Success handling
    },
    onError: () => {
      // Error handling
    },
  });
};
</script>

<template>
  <form @submit.prevent="submit">
    <Input
      v-model="form.name"
      :class="{ 'border-destructive': form.errors.name }"
    />
    <p v-if="form.errors.name" class="text-sm text-destructive">
      {{ form.errors.name }}
    </p>
    
    <Button type="submit" :disabled="form.processing">
      {{ form.processing ? 'Saving...' : 'Save' }}
    </Button>
  </form>
</template>
```

### **Loading States**

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { Loader2 } from 'lucide-vue-next';

const isLoading = ref(false);

const handleAction = async () => {
  isLoading.value = true;
  try {
    await someAsyncOperation();
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Button :disabled="isLoading">
    <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
    {{ isLoading ? 'Loading...' : 'Submit' }}
  </Button>
</template>
```

### **Error Handling**

```typescript
// API calls
const checkCoupon = async (code: string) => {
  try {
    const response = await fetch(`/api/coupons/${code}/check`);
    
    if (!response.ok) {
      throw new Error('Request failed');
    }
    
    const data = await response.json();
    return { success: true, data };
  } catch (error) {
    console.error('Error checking coupon:', error);
    return {
      success: false,
      error: 'Failed to check coupon. Please try again.',
    };
  }
};
```

---

## 🔄 Git Workflow

### **Branch Naming**

```
feature/   - New features (feature/add-qr-scanner)
fix/       - Bug fixes (fix/phone-validation)
refactor/  - Code refactoring (refactor/coupon-model)
docs/      - Documentation (docs/update-readme)
test/      - Tests (test/coupon-validation)
```

### **Commit Messages**

```
feat: add QR code scanner functionality
fix: resolve phone normalization issue
refactor: improve coupon validation logic
docs: update coding guide with new patterns
test: add tests for coupon creation
style: format code with prettier
chore: update dependencies
```

### **Conventional Commits Format**

```
<type>(<scope>): <subject>

<body>

<footer>
```

Example:
```
feat(coupons): add barcode scanning support

- Implement html5-qrcode integration
- Add manual code input fallback
- Handle camera permission errors

Closes #123
```

---

## 📖 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [shadcn/ui Documentation](https://ui.shadcn.com/)
- [TypeScript Documentation](https://www.typescriptlang.org/)

---

## 💡 Tips & Best Practices

### **Performance**
- ✅ Use eager loading for relationships
- ✅ Implement pagination for large datasets
- ✅ Add database indexes on frequently queried columns
- ✅ Cache expensive queries when appropriate

### **Security**
- ✅ Always validate user input
- ✅ Use CSRF protection (automatic with Laravel)
- ✅ Sanitize output to prevent XSS
- ✅ Use parameterized queries (Eloquent does this)
- ✅ Verify user permissions before sensitive operations

### **Code Quality**
- ✅ Write descriptive variable and function names
- ✅ Keep functions small and focused
- ✅ Add comments for complex logic
- ✅ Use TypeScript for type safety
- ✅ Follow DRY principle (Don't Repeat Yourself)
- ✅ Extract business logic to Services
- ✅ Keep controllers thin (HTTP concerns only)
- ✅ Use Helper classes for reusable utility functions

### **User Experience**
- ✅ Provide loading states for async operations
- ✅ Show clear error messages
- ✅ Implement proper form validation
- ✅ Make UI responsive (mobile-first)
- ✅ Add confirmation dialogs for destructive actions

---

**Last Updated:** November 20, 2025  
**Maintainer:** Development Team  
**Version:** 1.0
