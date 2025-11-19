# Coupon Management API Documentation

## Overview

This document describes the API endpoints for the Coupon Management System.

## Authentication

Most endpoints require authentication via Laravel Fortify. Include authentication cookies in requests or use Bearer token authentication.

## Endpoints

### List Coupons

**GET** `/coupons`

Returns a paginated list of coupons with optional filtering.

**Query Parameters:**
- `status` (string, optional): Filter by status (`active`, `used`, `expired`, or `all`)
- `search` (string, optional): Search by code, customer name, or phone
- `date_from` (date, optional): Filter coupons created from this date
- `date_to` (date, optional): Filter coupons created until this date
- `page` (integer, optional): Page number for pagination

**Response:**
```json
{
  "coupons": {
    "data": [
      {
        "id": 1,
        "code": "ABC-1234-XYZ",
        "type": "Gratis 1 Kopi",
        "description": "Dapatkan 1 kopi gratis",
        "customer_name": "John Doe",
        "customer_phone": "6281234567890",
        "formatted_phone": "0812-3456-7890",
        "status": "active",
        "created_at": "2025-11-19T10:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  },
  "filters": {
    "status": "all",
    "search": null
  }
}
```

### Create Coupon

**POST** `/coupons`

Creates a new coupon with auto-generated code.

**Request Body:**
```json
{
  "type": "Gratis 1 Kopi",
  "description": "Dapatkan 1 kopi gratis",
  "customer_name": "John Doe",
  "customer_phone": "081234567890",
  "customer_email": "john@example.com",
  "customer_social_media": "@johndoe",
  "expires_at": "2025-12-19"
}
```

**Validation Rules:**
- `type`: required, string, max:255
- `description`: required, string
- `customer_name`: required, string, max:255
- `customer_phone`: required, string (will be normalized)
- `customer_email`: optional, email, max:255
- `customer_social_media`: optional, string, max:255
- `expires_at`: optional, date

**Response:** Redirects to coupon show page

### Show Coupon

**GET** `/coupons/{id}`

Returns detailed information about a specific coupon.

**Response:**
```json
{
  "coupon": {
    "id": 1,
    "code": "ABC-1234-XYZ",
    "type": "Gratis 1 Kopi",
    "description": "Dapatkan 1 kopi gratis",
    "customer_name": "John Doe",
    "customer_phone": "6281234567890",
    "formatted_phone": "0812-3456-7890",
    "customer_email": "john@example.com",
    "customer_social_media": "@johndoe",
    "status": "active",
    "expires_at": "2025-12-19",
    "created_at": "2025-11-19T10:00:00.000000Z",
    "user": {
      "name": "Staff Name"
    },
    "validations": []
  },
  "qrUrl": "http://localhost/coupon/ABC-1234-XYZ",
  "publicUrl": "http://localhost/coupon/ABC-1234-XYZ"
}
```

### Delete Coupon

**DELETE** `/coupons/{id}`

Deletes a coupon.

**Response:** Redirects to coupon index page

### Public Coupon View

**GET** `/coupon/{code}`

Public endpoint to view coupon (no authentication required).

**Response:**
```json
{
  "coupon": {
    "id": 1,
    "code": "ABC-1234-XYZ",
    "type": "Gratis 1 Kopi",
    "description": "Dapatkan 1 kopi gratis",
    "customer_name": "John Doe",
    "status": "active",
    "expires_at": "2025-12-19",
    "created_at": "2025-11-19T10:00:00.000000Z"
  }
}
```

## Models

### Coupon Model

**Attributes:**
- `id`: integer
- `code`: string (unique, format: ABC-1234-XYZ)
- `type`: string
- `description`: text
- `customer_name`: string
- `customer_phone`: string (normalized to 628xx format)
- `customer_email`: string|null
- `customer_social_media`: string|null
- `expires_at`: date|null
- `status`: enum ('active', 'used', 'expired')
- `created_by`: integer (user ID)
- `created_at`: datetime
- `updated_at`: datetime

**Accessors:**
- `formatted_phone`: Returns phone in display format (0812-3456-7890)

**Scopes:**
- `active()`: Returns only active coupons
- `used()`: Returns only used coupons
- `expired()`: Returns only expired coupons

**Relationships:**
- `user()`: BelongsTo User (creator)
- `validations()`: HasMany CouponValidation

**Methods:**
- `canBeValidated()`: Returns boolean if coupon can be validated

### CouponValidation Model

**Attributes:**
- `id`: integer
- `coupon_id`: integer
- `validated_by`: integer (user ID)
- `validated_at`: datetime
- `action`: enum ('used', 'reversed')
- `notes`: text|null
- `created_at`: datetime
- `updated_at`: datetime

**Relationships:**
- `coupon()`: BelongsTo Coupon
- `validator()`: BelongsTo User

## Error Responses

### Validation Errors (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_name": ["The customer name field is required."],
    "customer_phone": ["The customer phone field is required."]
  }
}
```

### Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Coupon] 123"
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```
