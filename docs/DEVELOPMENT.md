# Development Guide

## Project Overview

This is a Coupon Management System built with Laravel 12 and Vue 3, designed for mobile-first use with Airbnb-style design and dark mode support.

## Architecture

### Backend (Laravel)

- **Framework**: Laravel 12
- **Authentication**: Laravel Fortify
- **API**: Inertia.js (SPA-like experience without API)
- **Database**: MySQL/PostgreSQL/SQLite
- **Queue**: Laravel Queue (for async coupon generation)

### Frontend (Vue.js)

- **Framework**: Vue 3 with Composition API
- **Build Tool**: Vite
- **Routing**: Inertia.js
- **Styling**: Tailwind CSS v4
- **UI Components**: Reka UI (shadcn/ui style)
- **Icons**: Lucide Vue Next
- **Type Safety**: TypeScript

## Code Structure

### Models

**Coupon Model** (`app/Models/Coupon.php`)
- Handles phone normalization
- Provides scopes for filtering
- Manages relationships
- Contains validation logic

**CouponValidation Model** (`app/Models/CouponValidation.php`)
- Tracks coupon usage history
- Records reversals with notes

### Controllers

**CouponController** (`app/Http/Controllers/CouponController.php`)
- `index()` - List coupons with filters
- `create()` - Show create form
- `store()` - Create new coupon
- `show()` - Show coupon details
- `destroy()` - Delete coupon

### Jobs

**GenerateCouponCode** (`app/Jobs/GenerateCouponCode.php`)
- Generates unique coupon codes
- Creates coupon records
- Format: ABC-1234-XYZ

### Helpers

**CouponHelper** (`app/Helpers/CouponHelper.php`)
- `generateCouponCode()` - Global helper function

## Design Principles

### Mobile-First

All components are designed mobile-first:
- Responsive breakpoints: `sm:`, `md:`, `lg:`
- Touch-friendly button sizes (min 44x44px)
- Stack layouts on mobile, side-by-side on desktop

### Airbnb-Style Design

- Clean, modern aesthetics
- Generous whitespace
- Rounded corners (`rounded-xl`, `rounded-2xl`)
- Subtle shadows and borders
- Gradient accents
- Smooth transitions

### Dark Mode

- Uses CSS variables for theming
- Automatic dark mode detection
- Consistent color scheme across components
- Proper contrast ratios

## Component Patterns

### Page Components

Located in `resources/js/pages/coupons/`:
- `Create.vue` - Form with validation
- `Index.vue` - List with filters
- `Show.vue` - Detail view
- `Public.vue` - Public customer view

### UI Components

Located in `resources/js/components/ui/`:
- Reusable shadcn/ui style components
- Consistent API across components
- TypeScript support

## State Management

- **Inertia.js**: Handles page state and navigation
- **Vue Composition API**: Local component state
- **Form Handling**: Inertia's `useForm()` composable

## Styling Guidelines

### Colors

- Primary: Used for main actions and accents
- Secondary: Used for secondary actions
- Destructive: Used for delete/danger actions
- Muted: Used for less important text

### Spacing

- Mobile: `p-4`, `gap-4`
- Desktop: `md:p-6`, `md:gap-6`

### Typography

- Headings: `text-2xl md:text-3xl` (mobile-first scaling)
- Body: `text-base md:text-sm` (smaller on desktop)
- Labels: `text-sm font-medium`

## Best Practices

### Vue Components

1. Use Composition API with `<script setup>`
2. Define TypeScript interfaces for props
3. Use computed properties for derived state
4. Extract reusable logic to composables

### Laravel

1. Use Eloquent relationships
2. Implement model scopes for queries
3. Use form requests for validation
4. Keep controllers thin

### Testing

1. Write tests for all features
2. Use factories for test data
3. Test edge cases
4. Keep tests fast and isolated

## Common Tasks

### Adding a New Field to Coupon

1. Update migration
2. Add to model's `$fillable`
3. Update factory
4. Update form validation
5. Update Vue components
6. Update tests

### Adding a New Filter

1. Add to controller's `index()` method
2. Update Vue component's filter form
3. Add test case
4. Update documentation

## Performance Considerations

- Database indexes on frequently queried columns
- Eager loading relationships to avoid N+1 queries
- Pagination for large datasets
- Caching for expensive queries (future)

## Security

- CSRF protection (automatic with Laravel)
- XSS protection (automatic with Vue)
- SQL injection protection (Eloquent ORM)
- Authentication required for admin routes
- Public routes are read-only

## Deployment

### Production Checklist

- [ ] Run migrations
- [ ] Build assets (`npm run build`)
- [ ] Set `APP_ENV=production`
- [ ] Configure queue worker
- [ ] Set up cron for expiration job
- [ ] Configure caching
- [ ] Set up monitoring

### Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
```

## Troubleshooting

### Tests Failing

1. Ensure migrations are run: `php artisan migrate`
2. Check factory definitions
3. Verify test database configuration

### QR Codes Not Generating

1. Check `qrcode` package is installed
2. Verify `@types/qrcode` is installed
3. Check browser console for errors

### Phone Normalization Issues

1. Verify mutator is working: Check database directly
2. Test with various input formats
3. Check model's `setCustomerPhoneAttribute()` method

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
