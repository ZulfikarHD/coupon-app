# Coupon Management System

A modern, mobile-first coupon management system built with Laravel and Vue.js, featuring QR code generation, customer tracking, and validation workflows.

## Features

### SPRINT 1: Core Coupon System ✅

- **Coupon CRUD Operations**
  - Create coupons with customer information
  - View all coupons with advanced filtering
  - View individual coupon details with QR code
  - Delete coupons

- **Customer Information Management**
  - Customer data embedded directly in coupons
  - Phone number normalization (08xx → 628xx)
  - Support for email and social media

- **Search & Filtering**
  - Search by coupon code, customer name, or phone
  - Filter by status (Active, Used, Expired)
  - Date range filtering
  - Pagination (20 per page)

- **QR Code Generation**
  - Automatic QR code generation for each coupon
  - Public coupon view (no authentication required)
  - Mobile-optimized display

- **Design**
  - Mobile-first responsive design
  - Airbnb-style modern UI
  - Dark mode support
  - Tailwind CSS styling

## Technology Stack

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js + TypeScript
- **Styling**: Tailwind CSS v4
- **UI Components**: Reka UI (shadcn/ui style)
- **Authentication**: Laravel Fortify
- **QR Codes**: qrcode npm package

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ and npm/yarn
- MySQL/PostgreSQL/SQLite

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd coupon-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Update `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=coupon_app
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start development server**
   ```bash
   php artisan serve
   npm run dev
   ```

## Database Schema

### Coupons Table

```sql
- id (bigint, primary key)
- code (string, unique, indexed) - Format: ABC-1234-XYZ
- type (string) - e.g., "Gratis 1 Kopi", "Diskon 20%"
- description (text)
- customer_name (string)
- customer_phone (string, indexed) - Normalized to 628xx format
- customer_email (string, nullable)
- customer_social_media (string, nullable)
- expires_at (date, nullable, indexed)
- status (enum: 'active', 'used', 'expired', indexed)
- created_by (foreign key → users.id)
- created_at, updated_at (timestamps)
```

### Coupon Validations Table

```sql
- id (bigint, primary key)
- coupon_id (foreign key → coupons.id)
- validated_by (foreign key → users.id)
- validated_at (datetime)
- action (enum: 'used', 'reversed')
- notes (text, nullable)
- created_at, updated_at (timestamps)
```

## API Routes

### Protected Routes (Requires Authentication)

- `GET /coupons` - List all coupons
- `GET /coupons/create` - Show create form
- `POST /coupons` - Store new coupon
- `GET /coupons/{id}` - Show coupon details
- `DELETE /coupons/{id}` - Delete coupon

### Public Routes (No Authentication)

- `GET /coupon/{code}` - Public coupon view (for customers)

## Usage

### Creating a Coupon

1. Navigate to **Kupon → Buat Kupon Baru**
2. Fill in customer information:
   - Name (required)
   - Phone (required) - Will be normalized automatically
   - Email (optional)
   - Social Media (optional)
3. Fill in coupon details:
   - Type (required) - e.g., "Gratis 1 Kopi"
   - Description (required)
   - Expiration Date (optional)
4. Click **Buat Kupon**
5. The system will automatically generate a unique code (ABC-1234-XYZ format)
6. You'll be redirected to the coupon detail page

### Viewing Coupons

- **All Coupons**: Navigate to **Kupon → Semua Kupon**
- **Filtering**: Use the filter section to filter by status, search, or date range
- **Search**: Search by coupon code, customer name, or phone number
- **Pagination**: Results are paginated (20 per page)

### Sharing Coupons

1. Open the coupon detail page
2. Click **Salin Link** button
3. Share the link with the customer
4. Customer can view the coupon without logging in

## Phone Number Normalization

The system automatically normalizes phone numbers to Indonesian format (628xx):

- `081234567890` → `6281234567890`
- `+6281234567890` → `6281234567890`
- `6281234567890` → `6281234567890`
- `81234567890` → `6281234567890`

Display format: `0812-3456-7890`

## Coupon Code Format

All coupon codes follow the format: **ABC-1234-XYZ**

- First part: 3 uppercase letters (ABC)
- Second part: 4 digits (1234)
- Third part: 3 uppercase letters (XYZ)

Example: `JSM-5199-DC2`

## Testing

### Run All Tests

```bash
php artisan test
```

### Run Feature Tests Only

```bash
php artisan test --testsuite=Feature
```

### Run Unit Tests Only

```bash
php artisan test --testsuite=Unit
```

### Run Specific Test File

```bash
php artisan test tests/Feature/CouponTest.php
```

### Test Coverage

The test suite includes:

**Feature Tests:**
- Coupon CRUD operations
- Authentication requirements
- Filtering and search functionality
- Public coupon access
- Pagination

**Unit Tests:**
- Phone number normalization
- Coupon code generation format
- Model scopes (active, used, expired)
- Model relationships
- Validation logic

## Development

### Code Style

The project uses Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

### Frontend Development

```bash
npm run dev
```

### Building for Production

```bash
npm run build
```

## Project Structure

```
app/
├── Http/Controllers/
│   └── CouponController.php      # Coupon CRUD operations
├── Jobs/
│   └── GenerateCouponCode.php    # Coupon code generation job
├── Models/
│   ├── Coupon.php                 # Coupon model with normalization
│   └── CouponValidation.php       # Validation history model
└── Helpers/
    └── CouponHelper.php           # Helper functions

resources/js/
├── pages/coupons/
│   ├── Create.vue                 # Create coupon form
│   ├── Index.vue                  # Coupon list with filters
│   ├── Show.vue                   # Coupon detail (staff view)
│   └── Public.vue                 # Public coupon view (customer)
└── components/
    └── ui/                        # Reusable UI components

database/
├── migrations/
│   ├── create_coupons_table.php
│   └── create_coupon_validations_table.php
└── factories/
    ├── CouponFactory.php
    └── CouponValidationFactory.php

tests/
├── Feature/
│   └── CouponTest.php             # Feature tests
└── Unit/
    ├── CouponModelTest.php        # Model unit tests
    └── CouponCodeGenerationTest.php # Code generation tests
```

## Contributing

1. Create a feature branch
2. Make your changes
3. Write tests for new features
4. Ensure all tests pass
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
