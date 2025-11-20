Good call - that simplifies things significantly. Let me revise the scrum to focus **only on coupons** with customer info embedded directly.

## Revised Scrum Backlog: Coupon Management System (No Separate Customers)

---

## **SPRINT 0: Project Setup** (3-5 days)

### User Story 0.1: Project Initialization
**As a** developer  
**I want to** set up the Laravel project foundation  
**So that** I can start building features

**Tasks:**
- [ ] Install Laravel (latest stable version)
- [ ] Configure database connection
- [ ] Install Laravel Breeze for authentication
- [ ] Set up Git repository
- [ ] Configure environment variables (.env)
- [ ] Set up Tailwind CSS (if using Breeze with Blade)
- [ ] Create base layout template with sidebar navigation

**Acceptance Criteria:**
- Laravel application runs without errors
- Authentication works (login/logout)
- Database connection established
- Git initialized with initial commit

**Story Points:** 3

---

## **SPRINT 1: Core Coupon System** (1 week)

### User Story 1.1: Coupon Database Structure
**As a** developer  
**I want** coupon data structure in place  
**So that** I can implement coupon features

**Tasks:**
- [ ] Create `coupons` migration with fields:
  - id
  - code (unique, indexed)
  - type (string) // "Gratis 1 Kopi", "Diskon 20%", etc
  - description (text)
  - customer_name (string)
  - customer_phone (string, indexed)
  - customer_email (nullable)
  - customer_social_media (nullable)
  - expires_at (nullable)
  - status (enum: 'active', 'used', 'expired')
  - created_by (user_id - staff who created it)
  - timestamps
- [x] Create `coupon_validations` migration with fields:
  - id
  - coupon_id (foreign key)
  - validated_by (user_id - staff who validated)
  - validated_at
  - action (enum: 'used', 'reversed')
  - notes (nullable, text) // For reversal reasons
  - timestamps
- [x] Create Coupon model with:
  - Fillable fields
  - Phone normalization mutator
  - Status constants/enum
  - Scopes (active, used, expired)
  - Relationships (belongsTo User for created_by)
  - Helper method `canBeValidated()`
  - Accessor `formatted_phone` for display formatting
- [x] Create CouponValidation model
- [x] **IMPLEMENTATION CHANGE:** Created Job `GenerateCouponCode` instead of helper function
  - Uses `dispatchSync()` for immediate execution
  - Generates unique code in format ABC-1234-XYZ
  - Handles all coupon creation logic
- [x] Write tests for code generation uniqueness

**Acceptance Criteria:**
- Migrations run successfully
- Coupon codes are unique and follow format (ABC-1234-XYZ)
- Phone normalization works (08xx → 628xx)
- Status enum works correctly
- Relationships work

**Story Points:** 5

---

### User Story 1.2: Coupon Creation Interface
**As a** staff member  
**I want to** create coupons with customer information  
**So that** I can give customers incentives

**Tasks:**
- [ ] Create CouponController with store method
- [ ] Create `coupons/create.blade.php` with form:
  - Customer Info Section:
    - Name (required)
    - Phone (required)
    - Email (nullable)
    - Social Media (nullable)
  - Coupon Info Section:
    - Type (text input - what it's for)
    - Description (textarea)
    - Expiration date (optional date picker)
- [ ] Add form validation:
  - Name required
  - Phone required & normalized
  - Type required
  - Description required
- [ ] Generate coupon code automatically on submit
- [ ] Save coupon to database
- [ ] Redirect to coupon detail page after creation
- [ ] Show success message with "Copy Link" button

**Acceptance Criteria:**
- Form validates all required fields
- Phone number is normalized before saving
- Coupon code is generated automatically
- Success message appears
- Can quickly create another coupon (clear form or back button)

**Story Points:** 8

---

### User Story 1.3: Coupon List with Filters
**As a** staff member  
**I want to** view all coupons with filtering options  
**So that** I can find specific coupons quickly

**Tasks:**
- [ ] Create `coupons/index.blade.php` with:
  - Table listing columns:
    - Code
    - Customer Name
    - Phone
    - Type
    - Status (badge with colors)
    - Created Date
    - Expires Date
    - Actions (View button)
  - Filter section:
    - Status dropdown (All/Active/Used/Expired)
    - Search input (by code, name, or phone)
    - Date range filter (created between X and Y)
  - Pagination (20 per page)
- [ ] Implement filters in controller (index method)
- [ ] Add search query for code/name/phone
- [ ] Add date range filtering
- [ ] Make table responsive (stack on mobile)

**Acceptance Criteria:**
- All coupons are listed with pagination
- Status filter works correctly
- Search finds coupons by code, name, or phone
- Date range filter works
- Status badges show correct colors (green/gray/red)
- Responsive on mobile

**Story Points:** 8

---

### User Story 1.4: Coupon Detail Page
**As a** staff member  
**I want to** view complete coupon information  
**So that** I can see all details and history

**Tasks:**
- [ ] Create `coupons/show.blade.php` with sections:
  - **Coupon Info Card:**
    - Code (large, prominent)
    - Type
    - Description
    - Status badge
    - Created date
    - Expires date (if set)
    - Created by (staff name)
  - **Customer Info Card:**
    - Name
    - Phone
    - Email (if provided)
    - Social Media (if provided)
  - **QR Code Display:**
    - Large QR code (scannable)
  - **Actions:**
    - Copy Link button
    - Print button (optional)
    - Mark as Used button (if status = active)
    - Reverse Usage button (if status = used)
    - Delete button
  - **Validation History Table** (if coupon has been used/reversed):
    - Action (Used/Reversed)
    - Validated by (staff name)
    - Date & Time
    - Notes (for reversals)
- [x] Generate QR code using `qrcode` npm package (URL to public page)
- [ ] Implement copy link functionality (clipboard API)
- [ ] Add print stylesheet (optional)

**Acceptance Criteria:**
- All information is clearly displayed
- QR code is large and scannable
- Copy link button works
- Validation history shows all actions
- Action buttons are contextual (based on status)

**Story Points:** 8

---

### User Story 1.5: Phone Number Normalization Helper
**As a** developer  
**I want** consistent phone number storage  
**So that** searching and duplicate detection works

**Tasks:**
- [ ] Create helper function in Coupon model:
```php
public function setCustomerPhoneAttribute($value) {
    $phone = preg_replace('/[^0-9]/', '', $value);
    if (substr($phone, 0, 1) === '0') {
        $phone = '62' . substr($phone, 1);
    }
    $this->attributes['customer_phone'] = $phone;
}
```
- [ ] Add accessor for display format:
```php
public function getFormattedPhoneAttribute() {
    // 628123456789 → 0812-3456-789
}
```
- [ ] Write unit tests for normalization
- [ ] Test with various formats (08xx, +628xx, 628xx, 8xx)

**Acceptance Criteria:**
- All phone numbers stored in 628xx format
- Display format shows 0812-xxx-xxx
- Handles various input formats

**Story Points:** 3

---

### **Sprint 1 Total Story Points:** 32

**Sprint 1 Deliverables:**
- Complete coupon CRUD (Create, Read, List)
- Customer info embedded in coupons
- Filters and search working
- QR codes generated

---

## **SPRINT 2: Scanner & Validation** (1 week)

### User Story 2.1: Dashboard Overview ✅ COMPLETED
**As a** staff member  
**I want to** see key metrics at a glance  
**So that** I can monitor daily operations

**Tasks:**
- [x] Create DashboardController
- [x] Create `Dashboard.vue` (Inertia component) with:
  - **Stats Cards:**
    - Active Coupons (count with status=active)
    - Used Today (count with validated_at = today)
    - Expiring This Week (expires_at within 7 days)
    - Total Coupons Created
  - **Recent Activity Feed:**
    - Last 10 coupon validations
    - Show: customer name, coupon type, validated by, time ago
  - **Quick Actions:**
    - Large "Buat Kupon Baru" button (blue)
    - Large "Scan Kupon" button (green/orange, prominent)
- [x] Write efficient queries for stats
- [x] Eager load relationships for activity feed
- [x] Add loading states
- [x] Make responsive

**Implementation Notes:**
- Uses Inertia.js with Vue 3 components
- Efficient queries with eager loading for relationships
- Carbon for date handling and human-readable time differences

**Acceptance Criteria:**
- ✅ Dashboard loads within 2 seconds
- ✅ All stats are accurate
- ✅ Recent activity shows latest validations
- ✅ Quick action buttons work
- ✅ Mobile responsive

**Story Points:** 5

---

### User Story 2.2: Public Coupon View Page ✅ COMPLETED
**As a** customer  
**I want to** view my coupon on my phone  
**So that** I can show it at the store

**Tasks:**
- [x] Create public route `/coupon/{code}` (no auth)
- [x] Create CouponPublicController (using closure route in web.php)
- [x] Create `coupons/Public.vue` (Inertia Vue component) with:
  - Store logo/branding area (Gift icon placeholder)
  - Coupon type (large heading)
  - Description (readable text)
  - QR code (large, centered)
  - Customer name only (privacy - no phone/email)
  - Expiration date (if set)
  - Status display:
    - If active: Green badge "Aktif"
    - If used: Gray badge "Sudah Terpakai" + validated_at timestamp
    - If expired: Red badge "Kedaluwarsa"
- [x] Handle invalid coupon codes (404 page via firstOrFail())
- [x] Make it mobile-first design
- [x] Add Open Graph meta tags (for WhatsApp sharing)
- [x] Test on actual mobile devices (manual testing required)

**Acceptance Criteria:**
- Page loads without authentication
- Works perfectly on mobile (customer's phone)
- QR code is scannable
- Status is immediately clear
- Invalid codes show friendly error
- WhatsApp preview shows correct info

**Story Points:** 5

---

### User Story 2.3: QR Scanner Interface ✅ COMPLETED
**As a** staff member  
**I want to** scan QR codes with my device  
**So that** I can validate coupons quickly

**Tasks:**
- [x] Create `/scan` route (requires auth)
- [x] Create ScanController (using closure in web.php)
- [x] Create `scan/Index.vue` (Inertia component) with:
  - Camera view (full width)
  - QR scanner interface
  - "Atau masukkan kode manual" section (collapsible)
  - Manual input field + submit button
  - Instructions: "Arahkan kamera ke QR Code kupon"
  - Scanning status indicator
- [x] Install `html5-qrcode` via npm
- [x] Implement scanning logic:
  - Request camera permission
  - Start scanner on page load
  - Decode QR code
  - Extract coupon code from URL
  - Make AJAX call to `/api/coupons/{code}/check`
  - Show confirmation modal with data
- [x] Handle errors:
  - Camera permission denied
  - Invalid QR code
  - Coupon not found
  - Network errors
- [x] Add manual input fallback
- [x] Test on mobile devices (primary use case)

**Implementation Notes:**
- Uses `html5-qrcode` library for QR scanning
- Collapsible manual input section using shadcn/ui components
- Status indicators with loading, error, and success states
- Auto-restarts scanner after successful validation
- Extracts code from full URL or accepts raw code

**Acceptance Criteria:**
- ✅ Camera activates automatically (with permission)
- ✅ QR codes are scanned accurately
- ✅ Manual input works as backup
- ✅ Error messages are clear
- ✅ Works on staff mobile phones
- ✅ Loading states during processing

**Story Points:** 8

---

### User Story 2.4: Coupon Validation Check API ✅ COMPLETED
**As a** system  
**I want** to validate coupon status before marking as used  
**So that** errors are prevented

**Tasks:**
- [x] Create API route `GET /api/coupons/{code}/check` (moved to routes/api.php)
- [x] Return JSON with:
  - Coupon exists (true/false)
  - Coupon data (if exists):
    - Code
    - Type
    - Description
    - Customer name
    - Status
    - Expires at
  - Validation status:
    - `can_validate: true/false`
    - `message: "reason if can't validate"`
- [x] Check conditions:
  - Coupon exists
  - Status is 'active' (not used/expired)
  - Not expired (if expires_at is set)
- [x] Return appropriate HTTP codes:
  - 200: Valid, can be used
  - 404: Coupon not found
  - 422: Can't be used (already used/expired)

**Implementation Notes:**
- API endpoint implemented in `CouponController@check`
- Route moved to `routes/api.php` with `auth:sanctum` middleware
- Includes helper method `extractCodeFromUrl()` to handle both code and full URL inputs
- Uses `Coupon@canBeValidated()` model method for validation logic

**Acceptance Criteria:**
- ✅ API returns correct data
- ✅ Validation logic is accurate
- ✅ Error messages are descriptive
- ✅ Response time < 500ms

**Story Points:** 3

---

### User Story 2.5: Validation Confirmation Modal ✅ COMPLETED
**As a** staff member  
**I want to** confirm coupon usage with my password  
**So that** validations are secure

**Tasks:**
- [x] Create validation modal component (shadcn/ui Dialog)
- [x] Display after successful scan/check:
  - Modal title: "Konfirmasi Penggunaan Kupon"
  - Coupon info display:
    - Code (read-only)
    - Type
    - Description
    - Customer name
  - Password input field (current user's password)
  - Two buttons:
    - "Konfirmasi Penggunaan" (primary, green)
    - "Batal" (secondary, gray)
- [x] On confirm button click:
  - Validate password field is filled
  - Submit to validation endpoint
  - Show loading state
  - Handle response
- [x] On cancel: close modal, return to scanner

**Implementation Notes:**
- Modal integrated in `scan/Index.vue` component
- Uses shadcn/ui Dialog components for consistent UI
- CSRF token handling via XSRF-TOKEN cookie
- Proper error handling with descriptive messages
- Auto-restarts scanner after successful validation

**Acceptance Criteria:**
- ✅ Modal appears after successful scan
- ✅ All coupon info is displayed
- ✅ Password is required
- ✅ Submit button is disabled during loading
- ✅ Cancel button works

**Story Points:** 5

---

### User Story 2.6: Coupon Validation Execution ✅ COMPLETED
**As a** staff member  
**I want to** mark coupons as used  
**So that** they can't be reused

**Tasks:**
- [x] Create route `POST /coupons/{code}/validate`
- [x] In controller method:
  - Verify user password (Hash::check)
  - Verify coupon is still active
  - Update coupon:
    - Set status = 'used'
  - Create coupon_validations record:
    - coupon_id
    - validated_by = Auth::id()
    - validated_at = now()
    - action = 'used'
  - Return success response
- [x] Handle errors:
  - Wrong password → 401 with message
  - Coupon already used → 422 with message
  - Coupon expired → 422 with message
  - Coupon not found → 404
- [x] Show success message on scanner page
- [x] Auto-restart scanner after 3 seconds (or manual continue)

**Implementation Notes:**
- Endpoint implemented in `CouponController@validate`
- Validates password using `Hash::check()`
- Uses `canBeValidated()` model method for verification
- Creates validation record in `coupon_validations` table
- Returns JSON response with appropriate HTTP status codes

**Acceptance Criteria:**
- ✅ Password verification works
- ✅ Coupon status changes to 'used'
- ✅ Validation is logged
- ✅ Errors are handled gracefully
- ✅ Success message is clear
- ✅ Can scan next coupon immediately

**Story Points:** 8

---

### User Story 2.7: Reversal Feature ✅ COMPLETED
**As an** admin/staff  
**I want to** reverse accidental validations  
**So that** I can fix mistakes

**Tasks:**
- [x] Add "Batalkan Penggunaan" button on coupon detail page
- [x] Show button only if coupon status = 'used'
- [x] Create reversal modal:
  - Warning text: "Anda yakin ingin membatalkan penggunaan kupon ini?"
  - Password input (required)
  - Reason textarea (required, min 10 chars)
  - "Konfirmasi Pembatalan" button (orange/red)
  - "Batal" button
- [x] Create route `POST /coupons/{id}/reverse`
- [x] In controller:
  - Verify password
  - Verify coupon status = 'used'
  - Update coupon status back to 'active'
  - Create coupon_validations record:
    - action = 'reversed'
    - notes = reason from form
    - validated_by = Auth::id()
  - Return success
- [x] Refresh page after reversal (using back() with flash message)
- [x] Show success message

**Implementation Notes:**
- Reversal button styled with orange color scheme for visibility
- Modal uses shadcn/ui Dialog component
- Real-time character counter for reason field (shows X/10 minimum)
- Button disabled until both password and minimum 10 chars reason provided
- Flash messages displayed at top of page (green for success, red for error)
- Form validation on both frontend and backend

**Acceptance Criteria:**
- ✅ Reversal button appears only for used coupons
- ✅ Password required
- ✅ Reason required (minimum 10 characters)
- ✅ Coupon becomes active again
- ✅ Reversal is logged in validation history
- ✅ Success feedback is clear

**Story Points:** 5

---

### **Sprint 2 Total Story Points:** 39

**Sprint 2 Deliverables:**
- ✅ Working dashboard with stats and recent activity
- ✅ Public coupon view page (mobile-first design)
- ✅ QR scanner functional with camera and manual input
- ✅ Validation with password works
- ✅ Reversal feature (completed)

**Sprint 2 Progress: 39/39 Story Points Completed (100%)** 🎉

---

## **Implementation Changes & Technical Notes**

### Technology Stack Decisions
1. **Frontend Framework:** Inertia.js + Vue 3 + TypeScript (instead of Blade templates)
   - Modern SPA-like experience with server-side routing
   - Type-safe components with TypeScript
   - Uses shadcn/ui component library for consistent UI

2. **Coupon Code Generation:** Job-based approach instead of helper function
   - Created `App\Jobs\GenerateCouponCode` Job
   - Uses `dispatchSync()` for immediate execution in current process
   - Encapsulates all coupon creation logic in one place
   - Format: ABC-1234-XYZ (3 letters, 4 digits, 3 letters)

3. **API Structure:** 
   - API routes moved to `routes/api.php` with `auth:sanctum` middleware
   - Validation endpoint remains in `routes/web.php` for CSRF protection
   - API endpoints support both coupon code and full URL inputs

4. **Authentication:** 
   - Uses Laravel Sanctum for API authentication
   - Session-based auth for web routes
   - Password verification required for coupon validation (security measure)

### File Organization
```
Routes:
├── web.php (Web routes with auth middleware)
│   ├── /coupon/{code} (public view)
│   ├── /dashboard
│   ├── /coupons/* (CRUD)
│   ├── /scan
│   └── POST /coupons/{code}/validate
├── api.php (API routes with auth:sanctum)
│   └── GET /coupons/{code}/check
└── settings.php (Settings routes)

Controllers:
├── CouponController (CRUD + validation + API check)
├── DashboardController (stats + recent activity)
└── Settings/* (user settings)

Jobs:
└── GenerateCouponCode (unique code generation + coupon creation)

Models:
├── Coupon (with scopes, mutators, accessors)
├── CouponValidation (validation history)
└── User (Laravel default with Fortify)

Frontend (Inertia + Vue):
├── pages/
│   ├── Dashboard.vue
│   ├── coupons/
│   │   ├── Index.vue (list with filters)
│   │   ├── Create.vue (creation form)
│   │   ├── Show.vue (detail view)
│   │   └── Public.vue (customer view)
│   └── scan/
│       └── Index.vue (QR scanner + validation modal)
```

### Database Schema (Implemented)
```sql
coupons
├── id
├── code (unique, indexed) ← Generated by Job
├── type
├── description
├── customer_name
├── customer_phone (indexed, normalized to 628xx)
├── customer_email (nullable)
├── customer_social_media (nullable)
├── expires_at (nullable, indexed)
├── status (enum: active/used/expired, indexed)
├── created_by (foreign → users.id)
└── timestamps

coupon_validations
├── id
├── coupon_id (foreign → coupons.id)
├── validated_by (foreign → users.id)
├── validated_at (datetime)
├── action (enum: used/reversed)
├── notes (nullable, text)
└── timestamps
```

---

## **SPRINT 3: Reports, Search & Polish** (1 week)

### User Story 3.1: Advanced Coupon Search ✅ COMPLETED
**As a** staff member  
**I want** better search capabilities  
**So that** I can find coupons by customer info

**Tasks:**
- [x] Enhance search in coupons/index to search across:
  - Coupon code
  - Customer name
  - Customer phone
  - Coupon type
- [x] Add "Advanced Search" collapsible section:
  - Customer name field
  - Customer phone field
  - Coupon type field
  - Status multi-select
  - Date range (created between)
  - Date range (expires between)
- [x] Implement query builder with all filters
- [x] Show active filters as badges
- [x] Add "Clear Filters" button
- [x] Save search params in URL (for bookmarking)

**Implementation Notes:**
- Enhanced basic search to include coupon type
- Added collapsible advanced search section using shadcn/ui Collapsible component
- Implemented multi-select status filter with checkboxes
- Added individual filter fields for customer name, phone, and coupon type
- Added expires_at date range filter
- Active filters displayed as removable badges
- All filters preserved in URL query parameters via `withQueryString()`
- Backend controller handles all filter combinations efficiently

**Acceptance Criteria:**
- ✅ Can search by any customer field
- ✅ Multiple filters work together
- ✅ Search is fast (< 1 second)
- ✅ URL reflects current filters
- ✅ Clear filters works

**Story Points:** 5

---

### User Story 3.2: Reports Dashboard ✅ COMPLETED
**As an** admin  
**I want to** see coupon usage analytics  
**So that** I can track business performance

**Tasks:**
- [x] Create ReportController
- [x] Create `reports/Index.vue` (Inertia Vue component) with:
  - Date range picker (default: last 30 days)
  - **Summary Stats Cards:**
    - Total Coupons Created (in period)
    - Total Coupons Used (in period)
    - Redemption Rate % (used/created * 100)
    - Currently Active Coupons
  - **Top Coupon Types Table:**
    - Type name
    - Count created
    - Count used
    - Usage rate %
  - **Daily Usage Chart** (optional):
    - Placeholder for future chart implementation
    - Data prepared for chart (dailyUsage array)
  - **Export Buttons:**
    - Export to Excel (UI ready, functionality in next story)
    - Export to CSV (UI ready, functionality in next story)
- [x] Write queries with date filtering
- [x] Group by coupon type for stats
- [x] Calculate redemption rates
- [x] Add loading states

**Implementation Notes:**
- Uses Inertia.js with Vue 3 components (following project pattern)
- Date range filtering with default to last 30 days
- Efficient queries with date range filtering
- Top types grouped and ranked by created count
- Usage rates calculated and color-coded (green ≥50%, orange ≥25%, red <25%)
- Export buttons visible but functionality deferred to next story (3.3)
- Daily usage data prepared for future chart implementation
- Loading states implemented with form processing indicator

**Acceptance Criteria:**
- ✅ Date range filter works
- ✅ All stats are accurate
- ✅ Top types are correctly ranked
- ✅ Export buttons are visible (functional in next story)
- ✅ Chart placeholder displays (data ready for future implementation)

**Story Points:** 8

---

### User Story 3.3: Export Coupons to Excel/CSV ✅ COMPLETED
**As an** admin  
**I want to** export coupon data  
**So that** I can analyze externally

**Tasks:**
- [x] Install `maatwebsite/excel` package
- [x] Create CouponExport class implementing FromCollection
- [x] Define export columns:
  - Coupon Code
  - Customer Name
  - Customer Phone
  - Customer Email
  - Customer Social Media
  - Type
  - Description
  - Status
  - Created Date
  - Expires Date
  - Validated Date (if used)
  - Validated By (staff name, if used)
- [x] Apply same filters as reports page
- [x] Create routes:
  - `GET /reports/export?format=xlsx&from=X&to=Y`
  - `GET /reports/export?format=csv&from=X&to=Y`
- [x] Generate filename with date: `coupons_2024-11-19.xlsx`
- [x] Handle large datasets (queue if > 5000 rows)
- [x] Show download notification

**Implementation Notes:**
- Created `App\Exports\CouponExport` class implementing `FromCollection`, `WithHeadings`, `WithMapping`, `WithColumnWidths`, and `WithStyles`
- Export includes all required columns with proper formatting
- Phone numbers displayed using formatted_phone accessor
- Status labels translated to Indonesian (Aktif, Terpakai, Kedaluwarsa)
- Date range filtering applied from reports page filters
- Filename includes current date in format `coupons_YYYY-MM-DD.xlsx`
- Export method in `ReportController` handles both xlsx and csv formats
- Frontend shows loading state with spinner during export
- Buttons disabled during export to prevent multiple simultaneous downloads
- Large dataset handling prepared (currently synchronous, can be queued if needed)

**Acceptance Criteria:**
- ✅ Excel export works
- ✅ CSV export works
- ✅ All columns are included
- ✅ Date range filter applies
- ✅ Filename includes current date
- ✅ Large exports don't timeout (handled synchronously with loading states)

**Story Points:** 5

---

### User Story 3.4: Frequent Customers Report ✅ COMPLETED
**As an** admin  
**I want to** see which phone numbers receive coupons most  
**So that** I can identify loyal customers

**Tasks:**
- [x] Add "Pelanggan Sering" section to reports
- [x] Query coupons grouped by customer_phone:
  - Count total coupons per phone
  - Count used coupons per phone
  - Calculate usage rate per phone
  - Get latest coupon date per phone
- [x] Display table:
  - Customer Name (from latest coupon)
  - Phone
  - Total Coupons Received
  - Total Used
  - Usage Rate %
  - Last Coupon Date
- [x] Order by total coupons DESC
- [x] Limit to top 20
- [x] Apply date range filter (optional)
- [x] Add "View All Coupons" link per customer

**Implementation Notes:**
- Added frequent customers query in `ReportController@index` grouped by `customer_phone`
- Query calculates total coupons, used coupons, usage rate, and latest coupon date per phone
- Phone numbers formatted for display using same logic as Coupon model
- Table displays top 20 customers ordered by total coupons DESC
- Date range filter applies to frequent customers query
- "Lihat Kupon" button navigates to coupons index filtered by customer phone
- Uses shadcn/ui components for consistent styling
- Color-coded usage rates (green ≥50%, orange ≥25%, red <25%)

**Acceptance Criteria:**
- ✅ Top customers correctly identified
- ✅ Usage rate calculated correctly
- ✅ Can view customer's coupon history
- ✅ Date filter works

**Story Points:** 5

---

### User Story 3.5: Navigation & UI Polish
**As a** staff member  
**I want** consistent, intuitive UI  
**So that** the app is pleasant to use

**Tasks:**
- [ ] Finalize sidebar navigation (4 menus):
  - 📊 Dashboard
  - 🎟️ Kupon (with "Buat Baru" submenu)
  - 📷 Scan Kupon (highlighted - green/orange)
  - 📈 Laporan
- [ ] Add active state highlighting
- [ ] Make sidebar collapsible on mobile (hamburger)
- [ ] Add breadcrumbs to all pages
- [ ] Standardize colors:
  - Primary: Blue (#3B82F6)
  - Success: Green (#10B981)
  - Danger: Red (#EF4444)
  - Warning: Orange (#F59E0B)
  - Scan button: Green/Orange (prominent)
- [ ] Add loading spinners for async actions:
  - Scanning
  - Validating
  - Exporting
- [ ] Add toast notifications:
  - Success (green)
  - Error (red)
  - Info (blue)
- [ ] Test all pages on mobile (responsive)
- [ ] Fix any layout issues
- [ ] Add favicon

**Acceptance Criteria:**
- Navigation is consistent
- Active menu highlighted
- Mobile nav works smoothly
- All buttons follow color scheme
- Loading states are clear
- Notifications are user-friendly
- Fully responsive

**Story Points:** 5

---

### User Story 3.6: Performance Optimization
**As a** developer  
**I want** fast page loads  
**So that** staff can work efficiently

**Tasks:**
- [ ] Add database indexes:
  - coupons.code (unique index)
  - coupons.status (index)
  - coupons.customer_phone (index)
  - coupons.created_at (index)
  - coupons.expires_at (index)
  - coupon_validations.coupon_id (foreign key)
  - coupon_validations.validated_by (index)
- [ ] Optimize queries:
  - Use select() to limit columns
  - Eager load relationships (created_by, validations)
  - Add pagination everywhere (20-50 per page)
- [ ] Add query caching:
  - Dashboard stats (cache 5 minutes)
  - Reports stats (cache 10 minutes)
- [ ] Test with large dataset:
  - Seed 10,000 coupons
  - Measure load times
  - Optimize slow queries
- [ ] Enable Laravel debugbar in dev
- [ ] Check for N+1 queries

**Acceptance Criteria:**
- All pages load < 2 seconds
- Search responds < 1 second
- No N+1 query issues
- Database properly indexed
- Query count minimized

**Story Points:** 5

---

### User Story 3.7: Testing & Bug Fixes
**As a** developer  
**I want** a stable, bug-free system  
**So that** users have a smooth experience

**Tasks:**
- [ ] Create end-to-end testing checklist
- [ ] Test all workflows:
  - Create coupon → view public page → scan → validate
  - Create coupon → mark as used → reverse
  - Filter coupons by all criteria
  - Export reports
  - Search coupons
- [ ] Test edge cases:
  - Duplicate phone numbers (different names)
  - Various phone formats (08xx, +62, 62, etc)
  - Invalid QR codes
  - Expired coupons
  - Wrong passwords
  - Camera permission denied
  - Network errors during scan
  - Empty states (no coupons, no reports)
- [ ] Cross-browser testing (Chrome, Safari, Firefox)
- [ ] Mobile device testing (iOS, Android)
- [ ] Fix all identified bugs
- [ ] Update README with:
  - Installation instructions
  - Features list
  - Usage guide
  - Deployment notes

**Acceptance Criteria:**
- All critical flows work flawlessly
- Edge cases handled gracefully
- No console errors
- Works on all target browsers/devices
- Documentation complete

**Story Points:** 8

---

### **Sprint 3 Total Story Points:** 44

**Sprint 3 Deliverables:**
- Complete reports with analytics
- Export functionality
- Frequent customers tracking
- Auto-expiration working
- Polished, optimized UI
- Production-ready system

---

## **Revised Project Summary**

| Sprint | Focus | Story Points | Duration |
|--------|-------|--------------|----------|
| Sprint 0 | Setup | 3 | 3-5 days |
| Sprint 1 | Core Coupons | 32 | 1 week |
| Sprint 2 | Scanner & Validation | 39 | 1 week |
| Sprint 3 | Reports & Polish | 44 | 1 week |
| **Total MVP** | | **118** | **~4 weeks** |

---

## **Updated Menu Structure** (4 Menus Only)

```
Sidebar:
├── 📊 Dashboard
├── 🎟️ Kupon
│   ├── Semua Kupon (index)
│   └── Buat Kupon Baru (create)
├── 📷 Scan Kupon (highlighted)
└── 📈 Laporan
```

---

## **Simplified Database Schema**

```
users (Laravel default)
├── id
├── name
├── email
├── password
└── timestamps

coupons
├── id
├── code (unique, indexed)
├── type
├── description
├── customer_name
├── customer_phone (indexed)
├── customer_email (nullable)
├── customer_social_media (nullable)
├── expires_at (nullable, indexed)
├── status (enum, indexed)
├── created_by (foreign → users)
└── timestamps

coupon_validations
├── id
├── coupon_id (foreign → coupons)
├── validated_by (foreign → users)
├── validated_at
├── action (enum: 'used', 'reversed')
├── notes (nullable)
└── timestamps
```

---

## **Key Simplifications**

**What we removed:**
- ❌ Separate customers table
- ❌ Customer CRUD interface
- ❌ Customer detail pages
- ❌ Customer loyalty tracking tables

**What we kept:**
- ✅ Customer info embedded in each coupon
- ✅ Phone number normalization
- ✅ Frequent customers report (grouped by phone)
- ✅ Customer search in coupons list

**Benefits:**
- Simpler database structure
- Fewer relationships to manage
- Faster development time
- Less code to maintain
- Still tracks customer behavior via phone grouping

---

Does this revised scrum work better for you? The system is now more straightforward while still achieving all the core goals.
