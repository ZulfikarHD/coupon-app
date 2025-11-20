# Design Consistency Analysis Report
## Vue Frontend Pages Review

### Overview
This report identifies design inconsistencies across Vue pages in the `/resources/js/pages` folder to help maintain a cohesive user experience.

---

## 1. Header Patterns

### ✅ Consistent Pattern (Most Pages)
```vue
<div class="space-y-1">
    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
        Page Title
    </h1>
    <p class="text-sm text-muted-foreground md:text-base">
        Description
    </p>
</div>
```
**Used in:** Dashboard, Coupons/Index, Coupons/Create, Users/Index, Users/Create, Users/Edit, Reports, Scan

### ❌ Inconsistent Patterns

**Settings Pages** use `HeadingSmall` component:
- `settings/Profile.vue`
- `settings/Password.vue`
- `settings/TwoFactor.vue`
- `settings/Appearance.vue`

**Public.vue** uses different styling:
```vue
<h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent md:text-4xl">
```

**Recommendation:** Standardize all pages to use the consistent header pattern, or create a reusable component.

---

## 2. Layout Structure

### ✅ Consistent Pattern
```vue
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
        <!-- Content -->
    </div>
</AppLayout>
```

### ❌ Inconsistencies

**Settings Pages** wrap content differently:
```vue
<AppLayout :breadcrumbs="breadcrumbItems">
    <SettingsLayout>
        <div class="space-y-6"> <!-- or flex flex-col space-y-6 -->
```

**Public.vue** doesn't use AppLayout at all:
```vue
<div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-accent/5">
```

**Recommendation:** Ensure all authenticated pages use `AppLayout` consistently. Settings pages can keep `SettingsLayout` wrapper but should maintain consistent inner spacing.

---

## 3. Card Component Usage

### ✅ Consistent Pattern
```vue
<Card class="border-2">
    <CardHeader class="pb-4"> <!-- Some use pb-4, some don't -->
        <div class="flex items-center gap-2">
            <Icon class="h-5 w-5 text-primary" />
            <CardTitle class="text-lg md:text-xl">Title</CardTitle>
        </div>
        <CardDescription class="text-sm">Description</CardDescription>
    </CardHeader>
    <CardContent class="space-y-4"> <!-- or space-y-6 -->
```

### ❌ Inconsistencies

**CardHeader padding:**
- Some use `class="pb-4"` (Coupons/Create, Coupons/Show)
- Some use `class="pb-4"` with different spacing (Reports)
- Some don't specify padding (Users/Index, Dashboard)

**CardTitle sizing:**
- Most use `text-lg md:text-xl`
- Some use just `text-lg` (Users/Index)
- Some don't specify size (Settings pages)

**Icon usage:**
- Most pages include icons in CardHeader
- Some pages don't use icons (Settings pages)
- Icon size varies: `h-5 w-5` vs `h-6 w-6`

**CardContent spacing:**
- Most use `space-y-4` or `space-y-6`
- Some use `class="pt-6"` (Users/Index filters)
- Some use `class="p-0"` for tables (Coupons/Index)

**Recommendation:** 
- Standardize CardHeader to always include `pb-4`
- Standardize CardTitle to `text-lg md:text-xl`
- Standardize icon size to `h-5 w-5`
- Use `space-y-4` for CardContent unless more spacing is needed

---

## 4. Form Patterns

### ✅ Consistent Elements
- Most forms use `space-y-6` or `space-y-4`
- Most use `InputError` component for errors
- Most use `Label` component

### ❌ Inconsistencies

**Input Heights:**
- Coupons/Create: `h-11 text-base md:h-10 md:text-sm`
- Users/Create: Default height (no explicit height)
- Users/Edit: Default height
- Settings/Profile: Default height
- Settings/Password: Default height

**Form Spacing:**
- Most use `space-y-6` (Coupons/Create, Users/Create, Users/Edit)
- Settings use `space-y-6` but wrapped differently
- Some use `space-y-4` (Coupons/Index filters)

**Error Display:**
- Most use `<InputError :message="form.errors.field" />`
- Coupons/Create uses inline `<p>` tags:
  ```vue
  <p v-if="form.errors.field" class="text-sm text-destructive">
      {{ form.errors.field }}
  </p>
  ```

**Button Placement:**
- Coupons/Create: `flex flex-col-reverse gap-3 sm:flex-row sm:justify-end`
- Users/Create: `flex items-center gap-4`
- Users/Edit: `flex items-center gap-4`
- Settings: `flex items-center gap-4`

**Button Sizes:**
- Some use `size="lg"` (Users/Create, Users/Edit)
- Some use `class="h-11"` (Coupons/Create)
- Some use default size (Settings)

**Loading States:**
- Some show loading text: `{{ form.processing ? 'Menyimpan...' : 'Simpan' }}`
- Some don't show loading state explicitly
- Some use Spinner component, some don't

**Recommendation:**
- Standardize input height to `h-11 text-base md:h-10 md:text-sm` for mobile-first approach
- Use `InputError` component consistently
- Standardize button placement to `flex flex-col-reverse gap-3 sm:flex-row sm:justify-end` for mobile-first
- Always show loading states with Spinner component
- Use `size="lg"` for primary action buttons consistently

---

## 5. Button Patterns

### ✅ Consistent Elements
- Most use `Button` component from `@/components/ui/button`
- Most use `as-child` prop with `Link` for navigation

### ❌ Inconsistencies

**Button Sizes:**
- Dashboard: `size="lg"` and `h-12`
- Coupons/Index: `h-11` and default
- Coupons/Create: `h-11`
- Users/Index: `size="lg"`
- Users/Create: `size="lg"`
- Users/Edit: `size="lg"`
- Settings: Default size

**Button Variants:**
- Most use `variant="outline"` for secondary actions
- Some use `variant="ghost"` for icon buttons
- Some use `variant="destructive"` for delete actions
- Inconsistent usage of variants

**Icon Usage:**
- Some buttons include icons: `<Plus class="h-4 w-4" />`
- Some buttons don't include icons
- Icon sizes vary: `h-4 w-4`, `h-5 w-5`

**Recommendation:**
- Use `size="lg"` for primary action buttons
- Use `variant="outline"` for secondary actions
- Use `variant="ghost"` for icon-only buttons
- Standardize icon size to `h-4 w-4` for buttons

---

## 6. Badge Colors & Status Indicators

### ❌ Major Inconsistencies

**Status Colors Definition:**
- Dashboard: Uses inline color classes in statCards
- Coupons/Index: `statusColors` object with consistent pattern
- Coupons/Show: Similar `statusColors` object
- Users/Index: `roleColors` object (different pattern)
- Public.vue: Uses gradient badges: `bg-gradient-to-r from-green-500/20 to-emerald-500/20`

**Status Badge Patterns:**
```vue
// Most pages use:
statusColors = {
    active: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
    used: 'bg-gray-500/10 text-gray-700 dark:text-gray-400 border-gray-500/20',
    expired: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
}

// Public.vue uses gradients:
statusColors = {
    active: 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 dark:text-green-300 border-green-400/30 shadow-green-500/10',
    // ...
}
```

**Recommendation:**
- Create a shared composable or constants file for status colors
- Use consistent badge styling across all pages
- Public.vue can have enhanced styling, but should maintain semantic consistency

---

## 7. Empty States

### ❌ Inconsistencies

**Empty State Patterns:**

**Dashboard:**
```vue
<div v-if="recentActivity.length === 0" class="py-8 text-center">
    <Activity class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
    <p class="text-sm text-muted-foreground">
        Belum ada aktivitas validasi
    </p>
</div>
```

**Coupons/Index:**
```vue
<div v-if="coupons.data.length === 0" class="p-12 text-center">
    <p class="text-lg font-medium text-muted-foreground">
        Tidak ada kupon ditemukan
    </p>
    <p class="mt-2 text-sm text-muted-foreground">
        Coba ubah filter atau buat kupon baru
    </p>
</div>
```

**Users/Index:**
```vue
<div v-if="props.users.data.length === 0" class="py-12 text-center">
    <UserIcon class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
    <p class="text-sm text-muted-foreground">
        Tidak ada user ditemukan
    </p>
</div>
```

**Reports:**
```vue
<div v-if="topTypes.length === 0" class="py-8 text-center">
    <Ticket class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
    <p class="text-sm text-muted-foreground">
        Tidak ada data kupon dalam periode yang dipilih
    </p>
</div>
```

**Recommendation:**
- Create a reusable `EmptyState` component
- Standardize to: Icon (h-12 w-12), Title (text-lg font-medium), Description (text-sm)
- Use consistent padding: `py-12 text-center`

---

## 8. Table Patterns

### ❌ Inconsistencies

**Table Styling:**

**Coupons/Index:**
```vue
<thead class="border-b bg-muted/50">
    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
```

**Users/Index:**
```vue
<thead>
    <tr class="border-b">
        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
```

**Reports:**
```vue
<thead>
    <tr class="border-b border-border">
        <th class="text-left p-3 text-sm font-semibold text-muted-foreground">
```

**Differences:**
- Padding: `px-6 py-3` vs `px-4 py-3` vs `p-3`
- Font size: `text-xs` vs `text-sm`
- Font weight: `font-medium` vs `font-semibold`
- Background: `bg-muted/50` vs none
- Uppercase: Some use `uppercase tracking-wider`, some don't

**Recommendation:**
- Standardize table header to: `px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground`
- Use `bg-muted/50` for thead background
- Standardize table cell padding to `px-6 py-4`

---

## 9. Pagination Patterns

### ❌ Inconsistencies

**Coupons/Index:**
```vue
<div v-if="coupons.last_page > 1" class="border-t p-4">
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <p class="text-sm text-muted-foreground">
            Menampilkan {{ coupons.data.length }} dari {{ coupons.total }} kupon
        </p>
        <div class="flex gap-2">
            <Button variant="outline" size="sm">Sebelumnya</Button>
            <Button variant="outline" size="sm">Selanjutnya</Button>
        </div>
    </div>
</div>
```

**Users/Index:**
```vue
<div v-if="props.users.last_page > 1" class="mt-6 flex items-center justify-between">
    <div class="text-sm text-muted-foreground">
        Menampilkan {{ (props.users.current_page - 1) * props.users.per_page + 1 }} sampai
        {{ Math.min(props.users.current_page * props.users.per_page, props.users.total) }} dari
        {{ props.users.total }} user
    </div>
    <div class="flex gap-2">
        <Button variant="outline" size="sm">Sebelumnya</Button>
        <Button variant="outline" size="sm">Selanjutnya</Button>
    </div>
</div>
```

**Differences:**
- Container: `border-t p-4` vs `mt-6`
- Layout: `flex-col sm:flex-row` vs `flex items-center justify-between`
- Text format: Different calculation methods

**Recommendation:**
- Create a reusable `Pagination` component
- Standardize text format
- Use consistent spacing and layout

---

## 10. Filter/Search Patterns

### ❌ Inconsistencies

**Coupons/Index:**
- Uses Collapsible for advanced search
- Has filter badges with remove functionality
- Complex filter UI with multiple inputs

**Users/Index:**
- Simple search input + dropdown
- No advanced search
- No filter badges

**Reports:**
- Date range filters only
- Simple form layout

**Recommendation:**
- Create reusable filter components
- Standardize filter UI patterns
- Use consistent filter badge patterns

---

## 11. Responsive Design

### ✅ Generally Consistent
- Most pages use mobile-first approach
- Most use `sm:`, `md:`, `lg:` breakpoints consistently

### ❌ Minor Issues
- Some pages have inconsistent responsive spacing
- Some buttons don't adapt well on mobile
- Table responsive patterns vary (some hide on mobile, some show cards)

**Recommendation:**
- Document responsive breakpoint strategy
- Ensure all interactive elements are touch-friendly on mobile
- Standardize table responsive patterns

---

## 12. Typography

### ✅ Consistent Elements
- Most pages use consistent heading sizes
- Most use `text-muted-foreground` for secondary text

### ❌ Inconsistencies
- Some pages use `text-base` for descriptions, some use `text-sm`
- Font weights vary: `font-medium`, `font-semibold`, `font-bold`
- Line heights not consistently specified

**Recommendation:**
- Document typography scale
- Use consistent font weights
- Specify line heights where needed

---

## Summary of Recommendations

### High Priority
1. **Standardize header pattern** - Create reusable component or enforce consistent pattern
2. **Standardize form inputs** - Use consistent height and spacing
3. **Create shared status colors** - Move to composable/constants file
4. **Create EmptyState component** - Reusable empty state pattern
5. **Standardize button patterns** - Consistent sizes and variants

### Medium Priority
1. **Create Pagination component** - Reusable pagination
2. **Standardize table patterns** - Consistent styling
3. **Create filter components** - Reusable filter UI
4. **Standardize Card usage** - Consistent CardHeader/CardContent patterns

### Low Priority
1. **Document design system** - Typography, spacing, colors
2. **Review responsive patterns** - Ensure consistency
3. **Standardize icon usage** - Consistent sizes and placement

---

## Files Requiring Updates

### High Priority
- `pages/settings/Profile.vue` - Use consistent header pattern
- `pages/settings/Password.vue` - Use consistent header pattern
- `pages/settings/TwoFactor.vue` - Use consistent header pattern
- `pages/coupons/Create.vue` - Standardize form inputs and error display
- `pages/users/Create.vue` - Standardize form inputs
- `pages/users/Edit.vue` - Standardize form inputs

### Medium Priority
- `pages/coupons/Index.vue` - Standardize table and pagination
- `pages/users/Index.vue` - Standardize table and pagination
- `pages/reports/Index.vue` - Standardize table patterns
- `pages/Dashboard.vue` - Standardize empty states

### Low Priority
- `pages/coupons/Public.vue` - Review if enhanced styling is intentional
- All pages - Review responsive patterns

---

## Suggested Component Library Structure

```
components/
  ui/                    # Existing shadcn components
  EmptyState.vue         # Reusable empty state
  Pagination.vue         # Reusable pagination
  StatusBadge.vue        # Status badge with consistent colors
  PageHeader.vue         # Consistent page header
  FilterBar.vue          # Reusable filter UI
  DataTable.vue          # Standardized table component
```

---

## Next Steps

1. Create shared composables/constants for:
   - Status colors
   - Badge colors
   - Table configurations
   
2. Create reusable components:
   - EmptyState
   - Pagination
   - PageHeader
   - StatusBadge

3. Update pages incrementally:
   - Start with high-priority pages
   - Test after each update
   - Document patterns as you go

4. Create design system documentation:
   - Typography scale
   - Spacing system
   - Color palette
   - Component patterns
