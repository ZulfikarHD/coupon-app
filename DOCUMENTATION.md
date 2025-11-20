# Coupon Management System - Documentation

## Overview

The **Coupon Management System** is a modern, mobile-first web application designed for businesses to create, manage, validate, and track digital coupons. The system enables staff members to generate unique coupon codes with QR codes, share them with customers, and validate them when customers redeem their coupons. It includes comprehensive reporting and analytics features to track coupon performance and customer behavior.

### Key Purpose

This system solves the problem of managing promotional coupons in a digital format, replacing traditional paper-based systems with:
- **Digital coupon generation** with unique codes
- **QR code integration** for easy sharing and validation
- **Real-time validation** with password-protected staff actions
- **Customer tracking** through phone numbers and contact information
- **Comprehensive reporting** for business insights

---

## Business Flow

### 1. User Authentication & Access

**Flow:**
- Staff members and administrators log in through Laravel Fortify authentication
- Two roles exist: **Staff** (regular users) and **Admin** (with additional user management privileges)
- Two-factor authentication is supported for enhanced security

**Access Levels:**
- **Staff**: Can create, view, validate, and reverse coupons; access dashboard and reports
- **Admin**: All staff privileges + user management (create/edit/delete users)

---

### 2. Coupon Creation Flow

**Step-by-Step Process:**

1. **Staff navigates to "Create Coupon" page**
   - Accessible from main navigation menu

2. **Fill in Customer Information**
   - **Customer Name** (required): Name of the customer receiving the coupon
   - **Customer Phone** (required): Phone number (automatically normalized to Indonesian format: 628xx)
   - **Customer Email** (optional): Email address for contact
   - **Customer Social Media** (optional): Social media handle/account

3. **Fill in Coupon Details**
   - **Coupon Type** (required): e.g., "Gratis 1 Kopi", "Diskon 20%", "Buy 1 Get 1"
   - **Description** (required): Detailed description of the offer
   - **Expiration Date** (optional): When the coupon expires

4. **Submit Form**
   - System validates all required fields
   - Phone number is automatically normalized (e.g., `081234567890` → `6281234567890`)
   - A unique coupon code is generated in format: **ABC-1234-XYZ** (3 letters, 4 digits, 3 letters)
   - Code uniqueness is guaranteed through database checking
   - Coupon is created with status: **"active"**
   - Staff member who created it is recorded (`created_by`)

5. **Coupon Created Successfully**
   - Staff is redirected to coupon detail page
   - QR code is automatically generated linking to public coupon view
   - Staff can copy the public link to share with customer

---

### 3. Coupon Sharing Flow

**Public Coupon View (No Authentication Required):**

1. **Staff shares coupon link** with customer
   - Link format: `/coupon/{code}` (e.g., `/coupon/JSM-5199-DC2`)
   - Can be shared via WhatsApp, email, SMS, or any messaging platform

2. **Customer opens link**
   - No login required - public access
   - Mobile-optimized view displays:
     - Coupon code
     - Customer name
     - Coupon type and description
     - Expiration date (if set)
     - QR code (for easy scanning)
     - Current status (Active/Used/Expired)
     - Validation history (if already used)

3. **Customer can:**
   - View their coupon details
   - See QR code for easy redemption
   - Check if coupon is still valid

---

### 4. Coupon Validation Flow (Redeeming)

**QR Code Scanning Method:**

1. **Staff opens Scanner page**
   - Navigate to "Scan" from main menu
   - QR code scanner interface opens (using device camera)

2. **Scan customer's coupon QR code**
   - Scanner reads QR code from customer's phone screen
   - System extracts coupon code from QR code URL
   - System checks coupon status:
     - **Exists?** → If not found, shows error
     - **Can be validated?** → Checks if:
       - Status is "active"
       - Not expired (if expiration date is set)
       - Not already used

3. **Display coupon information**
   - Shows customer name, coupon type, description
   - Shows validation status (can validate or error message)

4. **Staff validates coupon**
   - Staff enters their **password** for security
   - System verifies password
   - If valid:
     - Coupon status changes from "active" → "used"
     - Validation record is created with:
       - Timestamp (`validated_at`)
       - Staff member who validated (`validated_by`)
       - Action: "used"
     - Success message displayed
   - If invalid:
     - Password incorrect → Error message
     - Coupon already used → Error message
     - Coupon expired → Error message

**Manual Code Entry Method:**

1. Staff can manually enter coupon code in scanner
2. Same validation process as QR scanning

---

### 5. Coupon Reversal Flow (Canceling Usage)

**When to Use:**
- Customer accidentally redeemed coupon
- Staff made a mistake during validation
- Need to reactivate a used coupon

**Process:**

1. **Staff navigates to coupon detail page**
   - Find the used coupon in the coupon list
   - Open coupon details

2. **Click "Reverse" button**
   - Only available for coupons with status "used"

3. **Enter reversal information**
   - **Password** (required): Staff password for security
   - **Reason** (required, min 10 characters): Why the reversal is happening

4. **Submit reversal**
   - System verifies password
   - System checks coupon status is "used"
   - If valid:
     - Coupon status changes from "used" → "active"
     - Reversal record is created with:
       - Timestamp
       - Staff member who reversed
       - Action: "reversed"
       - Notes: Reason for reversal
     - Success message displayed

---

### 6. Coupon Management Flow

**Viewing Coupons:**

1. **Navigate to "All Coupons" page**
   - Lists all coupons with pagination (20 per page)

2. **Filtering Options:**
   - **Status Filter**: Filter by Active, Used, Expired (can select multiple)
   - **Search**: Search by coupon code, customer name, phone number, or coupon type
   - **Advanced Filters**:
     - Customer name
     - Customer phone
     - Coupon type
     - Created date range
     - Expiration date range

3. **Sorting:**
   - Sort by: Code, Customer Name, Phone, Type, Status, Created Date, Expiration Date
   - Sort direction: Ascending or Descending

4. **Actions Available:**
   - View coupon details
   - Delete coupon (if needed)
   - Copy public link
   - Validate (if active)
   - Reverse (if used)

---

### 7. Dashboard & Analytics Flow

**Dashboard Overview:**

1. **Staff/Admin logs in**
   - Redirected to dashboard

2. **Dashboard displays:**
   - **Key Statistics:**
     - Active coupons count
     - Coupons used today
     - Coupons expiring this week
     - Total coupons count
   - **Recent Activity:**
     - Last 10 coupon validations
     - Shows: Customer name, coupon type, code, validator, time ago

**Reports & Analytics:**

1. **Navigate to Reports page**

2. **Select Date Range**
   - Default: Last 30 days
   - Customizable date range

3. **View Reports:**

   **a. Summary Statistics:**
   - Total coupons created in period
   - Total coupons used in period
   - Redemption rate (%)
   - Currently active coupons
   - Total expired coupons

   **b. Top Coupon Types:**
   - List of coupon types with:
     - Created count
     - Used count
     - Expired count
     - Usage rate (%)
   - Sortable and paginated

   **c. Daily Usage Chart:**
   - Line/bar chart showing daily coupon usage
   - Visual representation of redemption trends

   **d. Frequent Customers:**
   - List of customers (by phone number) with:
     - Total coupons received
     - Total coupons used
     - Usage rate (%)
     - Last coupon date
   - Sortable and paginated

4. **Export Data:**
   - Export coupons to Excel (.xlsx) or CSV format
   - Includes all coupon data for selected date range
   - Useful for external analysis or record keeping

---

### 8. User Management Flow (Admin Only)

**Admin Functions:**

1. **Navigate to User Management**
   - Only accessible to users with "admin" role

2. **View All Users**
   - List of all staff members and admins
   - Shows: Name, Email, Role, Created date

3. **Create New User**
   - Fill in: Name, Email, Password, Role (Staff/Admin)
   - User can then log in

4. **Edit User**
   - Update user information
   - Change role
   - Reset password

5. **Delete User**
   - Remove user from system
   - Note: Coupons created by user remain in system

---

## Data Flow & Status Transitions

### Coupon Status Lifecycle

```
[Created] → status: "active"
    ↓
[Validated] → status: "used" (creates validation record)
    ↓
[Reversed] → status: "active" (creates reversal record)
    ↓
[Can be validated again]
```

**Automatic Status Updates:**
- System can mark coupons as "expired" if expiration date passes (though this appears to be manual in current implementation)

### Phone Number Normalization

**Input Formats Accepted:**
- `081234567890` → Normalized to: `6281234567890`
- `+6281234567890` → Normalized to: `6281234567890`
- `6281234567890` → Kept as: `6281234567890`
- `81234567890` → Normalized to: `6281234567890`

**Display Format:**
- Stored as: `6281234567890`
- Displayed as: `0812-3456-7890`

---

## Key Business Rules

1. **Coupon Code Uniqueness**
   - Every coupon code is unique (format: ABC-1234-XYZ)
   - System checks database before assigning code

2. **Validation Requirements**
   - Only "active" coupons can be validated
   - Coupon must not be expired
   - Staff password required for validation

3. **Reversal Requirements**
   - Only "used" coupons can be reversed
   - Staff password required
   - Reason required (minimum 10 characters)

4. **Phone Number Standardization**
   - All phone numbers normalized to Indonesian format (628xx)
   - Enables consistent customer tracking

5. **Audit Trail**
   - All validations and reversals are recorded
   - Tracks who validated, when, and action taken
   - Notes field for reversals

6. **Access Control**
   - Public coupon view: No authentication required
   - All other features: Authentication required
   - User management: Admin only

---

## Technical Architecture

### Backend
- **Framework**: Laravel 12
- **Authentication**: Laravel Fortify (with 2FA support)
- **Database**: MySQL/PostgreSQL/SQLite
- **Queue System**: Laravel Queue (for coupon code generation)
- **Export**: Maatwebsite Excel (for reports)

### Frontend
- **Framework**: Vue 3 with TypeScript
- **Routing**: Inertia.js (SPA-like experience without API)
- **Styling**: Tailwind CSS v4
- **UI Components**: Reka UI (shadcn/ui style)
- **QR Code**: qrcode npm package
- **QR Scanner**: html5-qrcode library
- **Charts**: Chart.js with vue-chartjs

### Key Features
- **Mobile-First Design**: Optimized for mobile devices
- **Dark Mode Support**: User preference-based theme
- **Real-Time Updates**: Inertia.js provides seamless navigation
- **QR Code Generation**: Automatic QR code for each coupon
- **QR Code Scanning**: Camera-based scanning for validation

---

## Use Cases

### Use Case 1: Coffee Shop Promotional Campaign
**Scenario**: Coffee shop wants to run a "Free Coffee" promotion for new customers.

1. Staff creates coupon with customer information
2. Customer receives link via WhatsApp
3. Customer shows QR code at store
4. Staff scans QR code and validates
5. Customer receives free coffee
6. Manager reviews redemption rate in reports

### Use Case 2: Loyalty Program
**Scenario**: Restaurant tracks frequent customers and their coupon usage.

1. Staff creates multiple coupons for loyal customers
2. System tracks customer by phone number
3. Reports show frequent customers and their usage rates
4. Business can identify VIP customers

### Use Case 3: Mistake Correction
**Scenario**: Staff accidentally validated wrong coupon.

1. Staff finds the incorrectly validated coupon
2. Staff reverses the validation with reason
3. Coupon becomes active again
4. Customer can use coupon correctly
5. Audit trail shows the correction

---

## Security Features

1. **Password Protection**: All validation/reversal actions require staff password
2. **Authentication**: All staff actions require login
3. **Role-Based Access**: Admin-only features protected
4. **CSRF Protection**: Laravel CSRF tokens for form submissions
5. **Input Validation**: All user inputs validated server-side
6. **SQL Injection Prevention**: Parameterized queries and validation
7. **Two-Factor Authentication**: Optional 2FA for enhanced security

---

## Integration Points

1. **QR Code Generation**: Automatic generation on coupon creation
2. **QR Code Scanning**: Camera integration for mobile devices
3. **Excel Export**: Integration with Maatwebsite Excel for reports
4. **Email System**: Ready for email notifications (if configured)
5. **Queue System**: Background job processing for code generation

---

## Future Enhancement Possibilities

Based on the current architecture, potential enhancements:

1. **Email Notifications**: Send coupon links via email
2. **SMS Integration**: Send coupon codes via SMS
3. **Bulk Coupon Creation**: Create multiple coupons at once
4. **Coupon Templates**: Pre-defined coupon types
5. **Expiration Automation**: Automatic status update to "expired"
6. **Customer Portal**: Customer login to view all their coupons
7. **API for Mobile App**: RESTful API for native mobile apps
8. **Advanced Analytics**: More detailed charts and insights
9. **Coupon Scheduling**: Schedule coupon creation for future dates
10. **Multi-language Support**: Support for multiple languages

---

## Summary

The Coupon Management System is a comprehensive solution for businesses to digitize their coupon/promotion management. It provides:

- **Easy Creation**: Simple form-based coupon creation
- **Customer-Friendly**: Public links and QR codes for easy access
- **Staff-Friendly**: QR scanning and password-protected validation
- **Business Intelligence**: Dashboard and reports for insights
- **Audit Trail**: Complete history of all actions
- **Security**: Password protection and role-based access
- **Mobile-First**: Optimized for mobile device usage

The system streamlines the entire coupon lifecycle from creation to redemption, providing both operational efficiency and business insights.
