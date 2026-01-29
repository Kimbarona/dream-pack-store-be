# Dream Pack E-Commerce API Documentation

## Overview
This document provides comprehensive API endpoints for the Dream Pack E-Commerce application, covering Orders, Products, Banners, and Categories management.

## Table of Contents
- [Orders API](#orders-api)
- [Products API](#products-api)
- [Banners API](#banners-api)
- [Categories API](#categories-api)
- [Image Upload](#image-upload)

---

## Orders API

### Base URL
```
/api/orders
```

### Endpoints

#### Get All Orders
```
GET /api/orders
```

**Parameters:**
- `page` (optional) - Page number for pagination (default: 1)
- `per_page` (optional) - Items per page (default: 15, max: 100)
- `status` (optional) - Filter by order status
- `user_id` (optional) - Filter by user ID

**Response:**
```json
{
  "data": [
    {
      "id": "uuid-string",
      "order_number": "ORD-123456789",
      "user_id": 3,
      "status": "pending_payment",
      "subtotal": 89.97,
      "tax_amount": 7.20,
      "shipping_amount": 10.00,
      "total": 107.17,
      "shipping_address": {
        "address_line_1": "123 Test Street",
        "city": "Test City",
        "state": "CA",
        "postal_code": "12345",
        "country": "US"
      },
      "notes": "Order notes",
      "created_at": "2026-01-28T19:45:00.000000Z",
      "updated_at": "2026-01-28T19:45:00.000000Z",
      "items": [
        {
          "id": 1,
          "order_id": "uuid-string",
          "product_id": 1,
          "product_title": "Classic Cotton T-Shirt",
          "product_sku": "TSH001",
          "quantity": 2,
          "unit_price": 29.99,
          "total_price": 59.98,
          "size": "M",
          "chosen_color": {"name": "Red", "value": "#FF0000"},
          "pieces_per_package": 1
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  }
}
```

#### Get Single Order
```
GET /api/orders/{id}
```

**Response:** Same structure as individual order object above

#### Create Order
```
POST /api/orders
```

**Request Body:**
```json
{
  "user_id": 3,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "size": "M",
      "chosen_color": "Red"
    }
  ],
  "shipping_address": {
    "address_line_1": "123 Test Street",
    "city": "Test City",
    "state": "CA",
    "postal_code": "12345",
    "country": "US"
  },
  "notes": "Order notes"
}
```

**Response:** Created order object with auto-generated order_number and calculated totals

#### Update Order
```
PUT /api/orders/{id}
```

**Request Body:** Same as create order

#### Delete Order
```
DELETE /api/orders/{id}
```

---

## Products API

### Base URL
```
/api/products
```

### Endpoints

#### Get All Products
```
GET /api/products
```

**Parameters:**
- `page` (optional) - Page number for pagination
- `per_page` (optional) - Items per page (default: 15)
- `category_id` (optional) - Filter by category ID
- `is_active` (optional) - Filter by active status (true/false)
- `search` (optional) - Search term for title/sku

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Classic Cotton T-Shirt",
      "slug": "classic-cotton-t-shirt",
      "description": "Comfortable cotton t-shirt perfect for everyday wear",
      "price": 29.99,
      "sale_price": 24.99,
      "sku": "TSH001",
      "stock_qty": 100,
      "track_inventory": true,
      "sort_order": 0,
      "is_active": true,
      "pieces_per_package": 1,
      "meta_title": "Classic Cotton T-Shirt | Dream Pack",
      "meta_description": "Shop our classic cotton t-shirt",
      "created_at": "2026-01-26T00:00:00.000000Z",
      "updated_at": "2026-01-28T19:45:00.000000Z",
      "images": [
        {
          "id": 1,
          "path": "products/tshirt-main.jpg",
          "alt_text": "Main product image",
          "sort_order": 0,
          "is_featured": true,
          "url": "https://example.com/storage/products/tshirt-main.jpg"
        }
      ],
      "categories": [
        {
          "id": 1,
          "name": "Clothing",
          "slug": "clothing"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 11,
    "last_page": 1
  }
}
```

#### Get Single Product
```
GET /api/products/{id}
```

#### Create Product
```
POST /api/products
```

**Request Body:**
```json
{
  "title": "New Product",
  "slug": "new-product",
  "description": "Product description",
  "price": 99.99,
  "sale_price": 79.99,
  "sku": "NEW-001",
  "stock_qty": 50,
  "track_inventory": true,
  "sort_order": 1,
  "is_active": true,
  "pieces_per_package": 1,
  "meta_title": "Meta title",
  "meta_description": "Meta description",
  "category_ids": [1, 2],
  "images": [
    {
      "path": "products/new-product.jpg",
      "alt_text": "Product image",
      "sort_order": 0,
      "is_featured": true
    }
  ]
}
```

#### Update Product
```
PUT /api/products/{id}
```

#### Delete Product
```
DELETE /api/products/{id}
```

---

## Banners API

### Base URL
```
/api/banners
```

### Endpoints

#### Get All Banners
```
GET /api/banners
```

**Parameters:**
- `page` (optional) - Page number for pagination
- `per_page` (optional) - Items per page
- `is_active` (optional) - Filter by active status
- `search` (optional) - Search term for name/title

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Homepage Hero",
      "title": "Welcome to Dream Pack",
      "subtitle": "Amazing products for amazing people",
      "link_url": "https://example.com/products",
      "image": "banners/hero-banner.jpg",
      "is_active": true,
      "sort_order": 1,
      "starts_at": "2026-01-28T00:00:00.000000Z",
      "ends_at": "2026-02-28T23:59:59.000000Z",
      "created_at": "2026-01-28T19:45:00.000000Z",
      "updated_at": "2026-01-28T19:45:00.000000Z",
      "images": [
        {
          "id": 1,
          "path": "banners/hero-banner.jpg",
          "disk": "public",
          "sort_order": 0,
          "is_mobile": false,
          "url": "https://example.com/storage/banners/hero-banner.jpg"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 5,
    "last_page": 1
  }
}
```

#### Get Single Banner
```
GET /api/banners/{id}
```

#### Create Banner
```
POST /api/banners
```

**Request Body:**
```json
{
  "name": "Homepage Hero",
  "title": "Welcome to Dream Pack",
  "subtitle": "Amazing products for amazing people",
  "link_url": "https://example.com/products",
  "image": "banners/hero-banner.jpg",
  "is_active": true,
  "sort_order": 1,
  "starts_at": "2026-01-28T00:00:00.000000Z",
  "ends_at": "2026-02-28T23:59:59.000000Z"
}
```

#### Update Banner
```
PUT /api/banners/{id}
```

#### Delete Banner
```
DELETE /api/banners/{id}
```

---

## Categories API

### Base URL
```
/api/categories
```

### Endpoints

#### Get All Categories
```
GET /api/categories
```

**Parameters:**
- `page` (optional) - Page number for pagination
- `per_page` (optional) - Items per page
- `is_active` (optional) - Filter by active status
- `search` (optional) - Search term for name/description

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Clothing",
      "slug": "clothing",
      "description": "Fashion and apparel",
      "sort_order": 1,
      "created_at": "2026-01-26T00:00:00.000000Z",
      "updated_at": "2026-01-28T19:45:00.000000Z",
      "products_count": 5
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 8,
    "last_page": 1
  }
}
```

#### Get Single Category
```
GET /api/categories/{id}
```

#### Create Category
```
POST /api/categories
```

**Request Body:**
```json
{
  "name": "New Category",
  "slug": "new-category",
  "description": "Category description",
  "sort_order": 1
}
```

#### Update Category
```
PUT /api/categories/{id}
```

#### Delete Category
```
DELETE /api/categories/{id}
```

---

## Image Upload

### Supported File Types
- `image/jpeg` - JPEG images
- `image/jpg` - JPG images  
- `image/png` - PNG images
- `image/webp` - WebP images

### File Size Limits
- **Banner Images**: Maximum 5MB (5120KB)
- **Product Images**: Maximum 10MB (10240KB)
- **Gallery Upload**: Maximum 20 files per upload

### Storage Directories
- **Banners**: `storage/app/public/banners/`
- **Products**: `storage/app/public/products/`
- **Upload URLs**: `https://example.com/storage/{directory}/{filename}`

### Upload Endpoints

#### Upload Banner Image
```
POST /api/banners/{id}/upload-image
Content-Type: multipart/form-data
```

**Request:**
```
image: [file] - Banner image file
```

**Response:**
```json
{
  "success": true,
  "message": "Image uploaded successfully",
  "data": {
    "path": "banners/updated-banner.jpg",
    "url": "https://example.com/storage/banners/updated-banner.jpg"
  }
}
```

#### Upload Product Images
```
POST /api/products/{id}/upload-images
Content-Type: multipart/form-data
```

**Request:**
```
images: [file[]] - Multiple product image files
featured: [integer] (optional) - Index of featured image
```

**Response:**
```json
{
  "success": true,
  "message": "Images uploaded successfully",
  "data": {
    "uploaded": [
      {
        "path": "products/product-image-1.jpg",
        "url": "https://example.com/storage/products/product-image-1.jpg",
        "is_featured": true
      },
      {
        "path": "products/product-image-2.jpg", 
        "url": "https://example.com/storage/products/product-image-2.jpg",
        "is_featured": false
      }
    ]
  }
}
```

---

## Error Responses

### Standard Error Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  },
  "status_code": 422
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created successfully
- `422` - Validation error
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not found
- `429` - Too many requests
- `500` - Internal server error

---

## Authentication

### API Authentication
All endpoints require authentication using one of the following methods:

#### Bearer Token (Recommended)
```
Authorization: Bearer {your-api-token}
```

#### Session Authentication
```
Cookie: laravel_session={session-id}
```

### Rate Limiting
- **Authenticated users**: 1000 requests per hour
- **Unauthenticated users**: 100 requests per hour

---

## Admin Panel Endpoints

### Admin Panel Access
```
http://your-domain.com/admin
```

### Authentication & Redirects
✅ **Automatic Redirect to Login**
- All admin panel routes automatically redirect to `/admin/login` if user is not authenticated
- Root URL `/` redirects to `/admin/login` for better user experience
- Implemented via `RedirectIfNotAdmin` middleware
- Middleware registered in web middleware group
- Seamless integration with Filament auth system

### 🧪 **Testing Verification**
✅ **Automatic Redirect**: Tested and confirmed working
  - `curl http://127.0.0.1:8000/` → `HTTP/1.1 302 Found` (redirects to login)
  - `Location: http://127.0.0.1:8000/admin/login` (correct login route)
✅ **Route Configuration**: All routes properly configured
✅ **Middleware Integration**: Seamlessly integrated with Filament auth
✅ **User Experience**: Improved with automatic admin authentication

### Available Resources
- **Orders**: `/admin/orders` - Full CRUD with search, filters, bulk actions
- **Products**: `/admin/products` - Complete product management with image uploads
- **Categories**: `/admin/categories` - Category management with sorting
- **Banners**: `/admin/banners` - Banner management with image upload
- **Users**: `/admin/users` - User management (admin users)
- **Settings**: `/admin/settings` - Site configuration

### Features Implemented
✅ **Orders Module**
- Customer search with name/email format
- Product search with title/SKU/price format
- Auto-populated unit price (readOnly field)
- Real-time total calculations
- Atomic transaction processing
- Order status management

✅ **Banners Module** 
- Image upload field added to main form
- Image gallery management through relation manager
- Scheduled banner display
- Active/inactive status management
- Sort order control

✅ **Products Module**
- Complete CRUD operations
- Image gallery management
- Category assignment
- Inventory tracking
- Sale price support

✅ **Categories Module**
- Hierarchical category structure
- Product counting
- Sort order management
- Slug-based URLs

✅ **General Features**
- Full search functionality
- Bulk operations
- Export capabilities
- Responsive design
- Real-time updates

---

## Testing Status

### Completed Tests ✅
- **Banner CRUD Operations**: All Create, Read, Update, Delete - PASSED
- **Product CRUD Operations**: All Create, Read, Update, Delete - PASSED  
- **Category CRUD Operations**: All Create, Read, Update, Delete - PASSED
- **Order CRUD Operations**: All Create, Read, Update, Delete - PASSED
- **User CRUD Operations**: All Create, Read, Update, Delete - PASSED
- **Image Upload Functionality**: Banner image upload, storage, URL generation - PASSED
- **Image Display**: Image column display in admin tables - PASSED

### Admin Panel Status 🎯
All admin tabs are fully functional with:
- Image upload capabilities
- Search and filter functionality
- Bulk operations
- Real-time form updates
- Proper validation
- Atomic transactions
- Error handling

---

## File Information

### Generated By
**Dream Pack E-Commerce API Documentation**
Generated on: January 28, 2026
Version: 1.0

### Contact
For technical support or questions regarding this API documentation, please contact the development team.

---

*This documentation covers all implemented features as of the latest testing cycle. Features and endpoints may be added or modified in future versions.*