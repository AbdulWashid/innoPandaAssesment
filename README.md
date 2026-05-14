# WooCommerce Integration API - Laravel

This project is a Laravel-based WooCommerce Integration API developed as part of a technical assessment.

The application provides REST API endpoints to:

- Create WooCommerce products
- Update WooCommerce products
- Fetch WooCommerce products
- Delete WooCommerce products

The integration uses WooCommerce REST API with Laravel HTTP Client.

---

# Features

- REST API Integration with WooCommerce
- Product CRUD Operations
- Request Validation
- Structured JSON Responses
- Exception Handling
- Logging Support
- Search & Pagination Support
- Clean Service-Based Architecture
- Queue Ready Structure

---

# Requirements

- PHP >= 8.2
- Composer
- Laravel >= 11
- WooCommerce Store Access

---

# Installation

## 1. Clone Repository

```bash
git clone https://github.com/AbdulWashid/innoPandaAssesment
```

---

## 2. Move Into Project

```bash
cd PROJECT_FOLDER_NAME
```

---

## 3. Install Dependencies

```bash
composer install
```

---

## 4. Create Environment File

```bash
cp .env.example .env
```

---

## 5. Generate Application Key

```bash
php artisan key:generate
```

---

# WooCommerce Configuration

Add the following credentials inside `.env`

```env
WC_STORE_URL=https://yourstore.com/wp-json/wc/v3
WC_CONSUMER_KEY=ck_xxxxxxxxxxxxxxxxx
WC_CONSUMER_SECRET=cs_xxxxxxxxxxxxxxxxx
```

Example:

```env
WC_STORE_URL=https://b2b.xtraprotein.com/wp-json/wc/v3
WC_CONSUMER_KEY=your_consumer_key
WC_CONSUMER_SECRET=your_consumer_secret
```

---

# Run Application

```bash
php artisan serve
```

Application URL:

```txt
http://127.0.0.1:8000
```

---

# API Base URL

```txt
http://127.0.0.1:8000/api/woocommerce
```

---

# API Endpoints

## 1. Fetch Products

### Request

```http
GET /api/woocommerce/products
```

### Optional Query Params

| Parameter | Description     |
| --------- | --------------- |
| search    | Search product  |
| per_page  | Pagination size |

### Example

```http
GET /api/woocommerce/products?search=protein&per_page=10
```

---

## 2. Create Product

### Request

```http
POST /api/woocommerce/products
```

### Request Body

```json
{
    "name": "Sample Product",
    "sku": "SKU123",
    "price": 199.99,
    "description": "Detailed product description",
    "short_description": "Short product description",
    "quantity": 10,
    "weight": "0.5",
    "woocommerce_category_id": [15]
}
```

### Success Response

```json
{
    "status": "success",
    "woocommerce_product_id": 125,
    "message": "Product created successfully"
}
```

---

## 3. Update Product

### Request

```http
PUT /api/woocommerce/products/{id}
```

### Request Body

```json
{
    "price": 249,
    "description": "Updated description",
    "quantity": 15
}
```

### Success Response

```json
{
    "status": "success",
    "message": "Product updated successfully on WooCommerce"
}
```

---

## 4. Delete Product

### Request

```http
DELETE /api/woocommerce/products/{id}
```

### Success Response

```json
{
    "success": true,
    "message": "Product permanently deleted from WooCommerce."
}
```

---

# Validation

Request validation is implemented using Laravel Form Request Validation.

Validation includes:

- Required fields
- Data type validation
- Numeric validation
- Array validation
- Minimum value validation

---

# Error Handling

All API exceptions are handled gracefully.

Example Error Response:

```json
{
    "status": "error",
    "message": "Failed to connect to WooCommerce"
}
```

---

# Logging

All WooCommerce API actions are logged using Laravel logging system.

Log file location:

```txt
storage/logs/laravel.log
```

---

# Project Structure

```txt
app/
├── Http/
│   ├── Controllers/Api/
│   │   └── WooCommerceController.php
│   ├── Requests/
│   │   ├── StoreProductRequest.php
│   │   └── UpdateProductRequest.php
│
├── Services/
│   └── WooCommerceService.php
```

---

# Testing APIs

You can test APIs using:

- Postman
- Insomnia
- Thunder Client

---

# Useful Commands

## Clear Cache

```bash
php artisan optimize:clear
```

---

## View Routes

```bash
php artisan route:list
```

---

# Notes

- Database setup is optional for this assessment.
- WooCommerce API credentials are required.
- API responses are returned in JSON format.

---

# Author

Abdul Washid
Laravel Full Stack Developer
