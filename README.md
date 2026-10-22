# 🍨 Delivergate Backend Internship Assessment

> A secure, production-grade RESTful API for Point of Sale (POS), Inventory, and Customer Management built with **Laravel 13**, **Laravel Sanctum**, and **Role-Based Access Control (RBAC)**.

---

## 📑 Table of Contents

1. [Project Overview](#1-project-overview)
   - [Domain & Purpose](#domain--purpose)
   - [Key Architectural Features](#key-architectural-features)
   - [Technology Stack](#technology-stack)
2. [Setup Instructions](#2-setup-instructions)
   - [Prerequisites](#prerequisites)
   - [Step-by-Step Installation](#step-by-step-installation)
3. [Seed Data & Initial Records](#3-seed-data--initial-records)
   - [How to Seed the Database](#how-to-seed-the-database)
   - [Initial User Accounts & Credentials](#initial-user-accounts--credentials)
   - [Pre-Seeded Inventory Stock](#pre-seeded-inventory-stock)
   - [Pre-Seeded Customers](#pre-seeded-customers)
   - [Creating Custom Users via Tinker](#creating-custom-users-via-tinker)
4. [Role-Based Access Control (RBAC) Matrix](#4-role-based-access-control-rbac-matrix)
5. [API Specification & Endpoints](#5-api-specification--endpoints)
   - [Authentication Endpoints](#authentication-endpoints)
   - [Inventory Item Endpoints](#inventory-item-endpoints)
   - [Customer Management Endpoints](#customer-management-endpoints)
6. [API Testing & Verification Guide](#6-api-testing--verification-guide)
   - [Automated PHPUnit Feature Tests (Recommended)](#automated-phpunit-feature-tests)
   - [Postman Collection Testing](#postman-collection-testing)
   - [Manual cURL Verification](#manual-curl-verification)
7. [System Architecture & Engineering Standards](#7-system-architecture--engineering-standards)

---

## 1. Project Overview

### Domain & Purpose
This application was developed as part of the **Delivergate Backend Engineering Internship Assessment**. It serves as the core API backbone for **"Frozen World"**, an ice-cream and POS business requiring:
- High security and token-based client authentication.
- Strict multi-tiered access control (Owner, Manager, Cashier).
- Safe, non-destructive data auditing (soft deletes on stock and customer files).
- Decoupled, predictable RESTful JSON communication for frontend and mobile POS terminals.
- High test coverage and stability verified by comprehensive automated PHPUnit test suites.

### Key Architectural Features
- **Stateless Token Authentication**: Implemented via **Laravel Sanctum 4.0**, issuing lightweight, revocable personal access tokens.
- **Decoupled RBAC Policies**: Authorization rules are isolated inside dedicated policy classes (`app/Policies/InventoryItemPolicy.php` and `app/Policies/CustomerPolicy.php`) using `Gate::authorize(...)`.
- **Auditable Soft Deletes**: Both `InventoryItem` and `Customer` models utilize Laravel's `SoftDeletes` trait, preserving business records in the database while filtering them out of active queries.
- **Dedicated Request Validation**: Input validation and constraints are isolated in `FormRequest` classes (`StoreInventoryItemRequest`, `UpdateCustomerRequest`, etc.), keeping controllers slim.
- **Data Transformation Layer**: Custom API Resources (`InventoryItemResource`, `CustomerResource`) standardize outgoing JSON structures and format dates uniformly.
- **Unified JSON Exception Handling**: Configured in `bootstrap/app.php` to force all API routes (`/api/*`) to return structured JSON error responses rather than HTML stack traces.
- **Comprehensive Feature Test Suite**: Full end-to-end test coverage across authentication, inventory items, and customers with `RefreshDatabase`.

### Technology Stack
- **Framework**: Laravel 13 (`laravel/framework: ^13.17`)
- **Language**: PHP `^8.3` (Developed & verified on PHP `8.4.2`)
- **Authentication**: Laravel Sanctum (`laravel/sanctum: ^4.0`)
- **Database**: SQLite (Default, zero-configuration) / MySQL ready
- **ORM**: Eloquent with strict attribute casts (`decimal:2`, `integer`) and soft deletes
- **Test Framework**: PHPUnit 12 with Laravel Feature Testing tools

---

## 2. Setup Instructions

Follow these step-by-step instructions to get the backend running locally from a fresh clone.

### Prerequisites
Before running the project, verify that your environment has:
- **PHP** >= 8.3 with `pdo`, `sqlite3` (or `pdo_mysql`), `mbstring`, `openssl`, and `curl` extensions enabled.
- **Composer** >= 2.x
- **Git**

Verify using terminal:
```bash
php -v
composer -V
```

---

### Step-by-Step Installation

#### Step 1: Clone the Repository
```bash
git clone <repository_url>
cd delivergate-backend
```

#### Step 2: Install Composer Dependencies
```bash
composer install
```

#### Step 3: Environment Configuration
Create the `.env` file from the provided `.env.example`:

**Linux / macOS:**
```bash
cp .env.example .env
```

**Windows (PowerShell):**
```powershell
copy .env.example .env
```

> [!NOTE]  
> The default configuration uses **SQLite** for zero-setup local development (`DB_CONNECTION=sqlite`). The SQLite database file can be initialized as follows:
> - **Linux / macOS**: `touch database/database.sqlite`
> - **Windows (PowerShell)**: `New-Item -ItemType File -Path database/database.sqlite -Force`

#### Step 4: Generate Application Encryption Key
```bash
php artisan key:generate
```

#### Step 5: Run Migrations and Seed Initial Data
Run the database migrations along with the `--seed` flag to prepare all schema tables and populate initial records:
```bash
php artisan migrate:fresh --seed
```

#### Step 6: Start the Development Server
```bash
php artisan serve
```
The API is now running and ready to accept requests at:
```
http://127.0.0.1:8000
```

---

## 3. Seed Data & Initial Records

The application comes pre-packaged with a complete seeder (`database/seeders/DatabaseSeeder.php`) that populates the database with default role-based users, inventory catalog items, and customer records.

### How to Seed the Database
You can re-run migrations and seed the database anytime with:
```bash
php artisan migrate:fresh --seed
```
Or seed without dropping tables:
```bash
php artisan db:seed
```

---

### Initial User Accounts & Credentials
All initial accounts are configured with the default password: **`password`**.

| Role | Display Name | Email Address | Password | Permissions Summary |
|---|---|---|---|---|
| 👑 **Owner** | Shop Owner | `owner@frozenworld.com` | `password` | **Full Access**: Can create, read, update, and soft-delete all items and customers. |
| 👔 **Manager** | Shop Manager | `manager@frozenworld.com` | `password` | **Management**: Can create and edit inventory and customers. Deletions are forbidden (`403 Forbidden`). |
| 🧑‍💼 **Cashier** | Shop Cashier | `cashier@frozenworld.com` | `password` | **Sales / POS**: Can view items, adjust stock levels, and register/update customers. Cannot add new stock items or delete records (`403 Forbidden`). |

---

### Pre-Seeded Inventory Stock

| ID | Item Name | Category | Unit Price (LKR) | Stock Quantity |
|:---:|---|---|:---:|:---:|
| **1** | Vanilla Bean | Vanilla | `450.00` | 50 |
| **2** | Dark Chocolate | Chocolate | `550.00` | 30 |
| **3** | Strawberry Swirl | Strawberry | `480.00` | 40 |

---

### Pre-Seeded Customers

| ID | Customer Name | Email Address | Phone Number |
|:---:|---|---|---|
| **1** | Nimal Perera | `nimal@frozenworld.com` | `0771112223` |
| **2** | Sunil Silva | `sunil@frozenworld.com` | `0714445556` |
| **3** | Kamal Fernando | `kamal@frozenworld.com` | `0707778889` |

---

### Creating Custom Users via Tinker
If you wish to create additional custom users with specific roles without editing the seeder, use **Laravel Tinker**:

```bash
php artisan tinker
```
Then execute:
```php
App\Models\User::create([
    'name' => 'Jane Supervisor',
    'email' => 'jane@frozenworld.com',
    'password' => Illuminate\Support\Facades\Hash::make('password123'),
    'role' => 'manager', // Choose: 'owner', 'manager', or 'cashier'
]);
exit;
```

---

## 4. Role-Based Access Control (RBAC) Matrix

Authorization is governed strictly at the policy layer (`CustomerPolicy` and `InventoryItemPolicy`). If an authenticated user lacks permission for an action, the server immediately returns a `403 Forbidden` response.

| Domain | Action | Method & URI | Owner | Manager | Cashier | Enforcing Policy | Business Justification |
|:---|:---|:---|:---:|:---:|:---:|:---|:---|
| **Inventory** | View All Items | `GET /api/items` | ✅ | ✅ | ✅ | `InventoryItemPolicy@viewAny` | All roles need to view stock and prices (supports `?search=` filter) |
| **Inventory** | View Single Item | `GET /api/items/{id}` | ✅ | ✅ | ✅ | `InventoryItemPolicy@view` | All roles can inspect item details |
| **Inventory** | Add Catalog Item | `POST /api/items` | ✅ | ✅ | ❌ *(403)* | `InventoryItemPolicy@create` | Adding new product lines is restricted to Owner and Manager |
| **Inventory** | Update Item / Stock | `PUT/PATCH /api/items/{id}` | ✅ | ✅ | ✅ | `InventoryItemPolicy@update` | Cashiers can adjust stock during sales; Managers can modify pricing |
| **Inventory** | Delete Item (Soft) | `DELETE /api/items/{id}` | ✅ | ❌ *(403)* | ❌ *(403)* | `InventoryItemPolicy@delete` | **Strictly Owner only** to prevent accidental product catalog loss |
| **Customer** | View Directory | `GET /api/customers` | ✅ | ✅ | ✅ | `CustomerPolicy@viewAny` | All staff can look up registered customers |
| **Customer** | View Profile | `GET /api/customers/{id}` | ✅ | ✅ | ✅ | `CustomerPolicy@view` | All staff can view customer details |
| **Customer** | Register Customer | `POST /api/customers` | ✅ | ✅ | ✅ | `CustomerPolicy@create` | Cashiers can register loyalty customers directly at checkout |
| **Customer** | Update Customer | `PUT/PATCH /api/customers/{id}` | ✅ | ✅ | ✅ | `CustomerPolicy@update` | All staff can update customer phone numbers or emails |
| **Customer** | Delete Customer (Soft) | `DELETE /api/customers/{id}` | ✅ | ❌ *(403)* | ❌ *(403)* | `CustomerPolicy@delete` | **Strictly Owner only** |

---

## 5. API Specification & Endpoints

All protected endpoints require a Sanctum Bearer token passed in the `Authorization` header.

### Standard Request Headers
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <your_token_here>
```

> [!TIP]
> The primary REST resource route is `/api/items`. The alias `/api/inventory` is also fully mapped and available to support client and test interoperability.

---

### Authentication Endpoints

#### 1. Login & Token Issuance
- **Endpoint**: `POST /api/auth/login`
- **Access**: Public
- **Request Body**:
  ```json
  {
    "email": "owner@frozenworld.com",
    "password": "password"
  }
  ```
- **Validation Rules**:
  - `email`: `required|email`
  - `password`: `required`
- **Response `200 OK`**:
  ```json
  {
    "message": "Login successful",
    "user": {
      "id": 1,
      "name": "Shop Owner",
      "email": "owner@frozenworld.com",
      "role": "owner",
      "created_at": "2026-10-20T18:00:00.000000Z",
      "updated_at": "2026-10-20T18:00:00.000000Z"
    },
    "token": "1|qX8zK3aBCdefGhijklmnoPQRSTUVWXYZ12345678"
  }
  ```
- **Response `401 Unauthorized`** (Invalid credentials):
  ```json
  {
    "message": "The provided credentials are incorrect."
  }
  ```
- **Response `422 Unprocessable Content`** (Missing required fields):
  ```json
  {
    "message": "The email field is required. (and 1 more error)",
    "errors": {
      "email": ["The email field is required."],
      "password": ["The password field is required."]
    }
  }
  ```

#### 2. Logout & Token Revocation
- **Endpoint**: `POST /api/auth/logout`
- **Access**: Authenticated (`auth:sanctum`)
- **Headers**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "message": "Successfully logged out"
  }
  ```
- **Response `401 Unauthorized`** (If called without token):
  ```json
  {
    "message": "Unauthenticated."
  }
  ```

---

### Inventory Item Endpoints

#### 1. List / Search Items
- **Endpoint**: `GET /api/items` *(or `GET /api/inventory`)*
- **Access**: Owner, Manager, Cashier
- **Query Parameters**:
  - `search` *(optional string)*: Case-insensitive search on item `name`.
    - Example: `GET /api/items?search=Vanilla`
- **Response `200 OK`**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Vanilla Bean",
        "category": "Vanilla",
        "price": "450.00",
        "quantity": 50,
        "created_at": "2026-10-20 18:00:00"
      }
    ]
  }
  ```

#### 2. Create Inventory Item
- **Endpoint**: `POST /api/items` *(or `POST /api/inventory`)*
- **Access**: Owner, Manager *(Cashier receives `403 Forbidden`)*
- **Request Body**:
  ```json
  {
    "name": "Pistachio Gelato",
    "category": "Gelato",
    "price": 600.00,
    "quantity": 20
  }
  ```
- **Validation Rules**:
  - `name`: `required|string|max:255`
  - `category`: `required|string|max:255`
  - `price`: `required|numeric|min:0`
  - `quantity`: `required|integer|min:0`
- **Response `201 Created`**:
  ```json
  {
    "data": {
      "id": 4,
      "name": "Pistachio Gelato",
      "category": "Gelato",
      "price": "600.00",
      "quantity": 20,
      "created_at": "2026-10-20 23:30:00"
    }
  }
  ```
- **Response `422 Unprocessable Content`** (Missing mandatory fields or negative values):
  ```json
  {
    "message": "The name field is required. (and 2 more errors)",
    "errors": {
      "name": ["The name field is required."],
      "price": ["The price field must be at least 0."],
      "quantity": ["The quantity field must be at least 0."]
    }
  }
  ```

#### 3. View Single Item
- **Endpoint**: `GET /api/items/{id}` *(or `GET /api/inventory/{id}`)*
- **Access**: Owner, Manager, Cashier
- **Response `200 OK`**:
  ```json
  {
    "data": {
      "id": 1,
      "name": "Vanilla Bean",
      "category": "Vanilla",
      "price": "450.00",
      "quantity": 50,
      "created_at": "2026-10-20 18:00:00"
    }
  }
  ```
- **Response `404 Not Found`**:
  ```json
  {
    "message": "Record not found."
  }
  ```

#### 4. Update Inventory Item
- **Endpoint**: `PUT/PATCH /api/items/{id}` *(or `PUT/PATCH /api/inventory/{id}`)*
- **Access**: Owner, Manager, Cashier
- **Request Body** *(Supports partial updates)*:
  ```json
  {
    "price": 475.00,
    "quantity": 45
  }
  ```
- **Response `200 OK`**:
  ```json
  {
    "data": {
      "id": 1,
      "name": "Vanilla Bean",
      "category": "Vanilla",
      "price": "475.00",
      "quantity": 45,
      "created_at": "2026-10-20 18:00:00"
    }
  }
  ```

#### 5. Soft Delete Inventory Item
- **Endpoint**: `DELETE /api/items/{id}` *(or `DELETE /api/inventory/{id}`)*
- **Access**: **Owner only** *(Manager and Cashier receive `403 Forbidden`)*
- **Response `200 OK`**:
  ```json
  {
    "message": "Item deleted successfully"
  }
  ```

---

### Customer Management Endpoints

#### 1. List Customers
- **Endpoint**: `GET /api/customers`
- **Access**: Owner, Manager, Cashier
- **Response `200 OK`**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Nimal Perera",
        "email": "nimal@frozenworld.com",
        "phone": "0771112223",
        "created_at": "2026-10-20 18:00:00"
      }
    ]
  }
  ```

#### 2. Create Customer
- **Endpoint**: `POST /api/customers`
- **Access**: Owner, Manager, Cashier
- **Request Body**:
  ```json
  {
    "name": "Anura Senanayake",
    "email": "anura@example.com",
    "phone": "0771234567"
  }
  ```
- **Validation Rules**:
  - `name`: `required|string|max:255`
  - `email`: `nullable|email|unique:customers,email`
  - `phone`: `nullable|string|regex:/^\+?[0-9]{9,15}$/`
- **Response `201 Created`**:
  ```json
  {
    "data": {
      "id": 4,
      "name": "Anura Senanayake",
      "email": "anura@example.com",
      "phone": "0771234567",
      "created_at": "2026-10-20 23:35:00"
    }
  }
  ```
- **Response `422 Unprocessable Content`** (Duplicate email or invalid phone regex):
  ```json
  {
    "message": "The email has already been taken.",
    "errors": {
      "email": ["The email has already been taken."]
    }
  }
  ```

#### 3. View Single Customer
- **Endpoint**: `GET /api/customers/{id}`
- **Access**: Owner, Manager, Cashier
- **Response `200 OK`**:
  ```json
  {
    "data": {
      "id": 1,
      "name": "Nimal Perera",
      "email": "nimal@frozenworld.com",
      "phone": "0771112223",
      "created_at": "2026-10-20 18:00:00"
    }
  }
  ```

#### 4. Update Customer
- **Endpoint**: `PUT/PATCH /api/customers/{id}`
- **Access**: Owner, Manager, Cashier
- **Request Body**:
  ```json
  {
    "phone": "0779998888"
  }
  ```
- **Validation Rules**:
  - `name`: `sometimes|required|string|max:255`
  - `email`: `nullable|email|unique:customers,email,{id}` *(automatically ignores current customer ID)*
  - `phone`: `nullable|string|regex:/^\+?[0-9]{9,15}$/`
- **Response `200 OK`**:
  ```json
  {
    "data": {
      "id": 1,
      "name": "Nimal Perera",
      "email": "nimal@frozenworld.com",
      "phone": "0779998888",
      "created_at": "2026-10-20 18:00:00"
    }
  }
  ```

#### 5. Soft Delete Customer
- **Endpoint**: `DELETE /api/customers/{id}`
- **Access**: **Owner only** *(Manager and Cashier receive `403 Forbidden`)*
- **Response `200 OK`**:
  ```json
  {
    "message": "Customer deleted successfully"
  }
  ```

---

## 6. API Testing & Verification Guide

We provide three complete ways to test and verify the API:

1. **Automated PHPUnit Feature Test Suite** (Command Line)
2. **Postman Collection** (Visual / GUI with automated token propagation)
3. **Manual cURL commands** (Terminal)

---

### Automated PHPUnit Feature Tests

The application includes comprehensive feature test suites covering all authentication paths, RBAC policies, CRUD operations, soft deletions, and validation rules.

#### Run All Feature Tests:
```bash
php artisan test
```

Expected output:
```text
Pass: 36 passed (121 assertions)
```

#### Run Specific Test Classes:
- **Authentication Tests**:
  ```bash
  php artisan test --filter=AuthTest
  ```
  *Tests successful login with token issuance, 401 on invalid passwords / nonexistent emails, 422 on missing credentials, authenticated logout, and 401 unauthenticated logout.*

- **Inventory Item Tests**:
  ```bash
  php artisan test --filter=InventoryItemTest
  ```
  *Tests listing items, creating items, viewing single items, updating items, soft-deleting items, 401 unauthorized access, 422 validation errors (missing fields, negative values), and 404 responses.*

- **Customer Management Tests**:
  ```bash
  php artisan test --filter=CustomerTest
  ```
  *Tests customer listing, creation, single profile view, updating customer details, soft-deleting customers, 401 unauthorized access, 422 validation errors (missing name, invalid/duplicate email, phone regex), and 404 responses.*

---

### Postman Collection Testing

A pre-configured, production-ready Postman collection is included directly in the root directory of this repository:
```text
FrozenWorld - Chiran VIdanagamage.postman_collection.json
```

#### 1. Pre-configured Role Login Endpoints

| Request Name | Credentials | Target Role & Scope |
|---|---|---|
| **Auth - Login (Owner)** | `owner@frozenworld.com` / `password` | **Full Access**: Can perform all CRUD operations and soft-deletions on items and customers. |
| **Auth - Login (Manager)** | `manager@frozenworld.com` / `password` | **Management**: Can create and update items and customers; deletions are forbidden (`403 Forbidden`). |
| **Auth - Login (Cashier)** | `cashier@frozenworld.com` / `password` | **Sales / POS**: Can view items, adjust stock levels, and manage customers; creating stock items and deletions are forbidden (`403 Forbidden`). |

#### 2. Automated Token Extraction & Inheritance
No manual copying and pasting of Bearer tokens is required between requests:
- **Post-Response Scripting**: Each login request includes an automated Postman test script that parses the JSON response, dynamically extracts the newly issued Sanctum Bearer token, and saves it into the collection variable `bearer_token`:
  ```javascript
  const response = pm.response.json();
  if (response.token) {
      pm.collectionVariables.set("bearer_token", response.token);
  }
  ```
- **Automatic Authorization**: All subsequent CRUD endpoints (Inventory Items & Customer Management) are configured to inherit authentication from the parent collection (`Authorization: Bearer {{bearer_token}}`).
- **Seamless Role Switching**: Running any one of the login requests (e.g., *Login as Cashier*) immediately updates the active authorization context for all subsequent requests.

#### 3. Step-by-Step Testing Workflow
1. **Start the Local Development Server**:
   ```bash
   php artisan serve
   ```
   Ensure the server is running at `http://127.0.0.1:8000`.

2. **Import the Collection**:
   - Open Postman.
   - Click **Import** (top left).
   - Select or drag and drop `FrozenWorld - Chiran VIdanagamage.postman_collection.json`.

3. **Verify the Base URL**:
   - Confirm the collection variable `base_url` is set to `http://127.0.0.1:8000/api`.

4. **Verify RBAC in Action**:
   - **Step 1**: Send `Auth - Login (Cashier)`. The script automatically captures and sets the Cashier Bearer token.
   - **Step 2**: Attempt `POST /api/items` (Create Item) $\rightarrow$ Returns **`403 Forbidden`** (Policy violation verified).
   - **Step 3**: Send `Auth - Login (Owner)`. The active token is updated automatically.
   - **Step 4**: Re-send `POST /api/items` $\rightarrow$ Returns **`201 Created`** (Authorized).
   - **Step 5**: Send `DELETE /api/items/1` $\rightarrow$ Returns **`200 OK`** (Soft deletion verified).

---

### Manual cURL Verification

Test the system quickly using terminal `curl` commands:

#### Test Scenario A: Cashier Authentication & RBAC Check
```bash
# 1. Log in as Cashier
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"cashier@frozenworld.com","password":"password"}'

# 2. Attempt to add a new stock item (Should fail with 403 Forbidden)
curl -X POST http://127.0.0.1:8000/api/items \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <CASHIER_TOKEN>" \
  -d '{"name":"Coffee Fudge","category":"Special","price":500,"quantity":10}'

# 3. Register a new customer (Should succeed with 201 Created)
curl -X POST http://127.0.0.1:8000/api/customers \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <CASHIER_TOKEN>" \
  -d '{"name":"Sandun Bandara","phone":"0773334445"}'
```

#### Test Scenario B: Owner Privileges & Soft Deletion
```bash
# 1. Log in as Owner
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@frozenworld.com","password":"password"}'

# 2. Add new item (Should succeed with 201 Created)
curl -X POST http://127.0.0.1:8000/api/items \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <OWNER_TOKEN>" \
  -d '{"name":"Coffee Fudge","category":"Special","price":500,"quantity":10}'

# 3. Soft-delete item (Should succeed with 200 OK)
curl -X DELETE http://127.0.0.1:8000/api/items/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <OWNER_TOKEN>"
```

---

## 7. System Architecture & Engineering Standards

```text
delivergate-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php            # Login (401 on invalid) & token revocation
│   │   │   ├── CustomerController.php        # Customer CRUD with policy gates
│   │   │   └── InventoryItemController.php   # Inventory CRUD with search & policy gates
│   │   ├── Requests/                         # Request validation classes
│   │   │   ├── StoreCustomerRequest.php
│   │   │   ├── UpdateCustomerRequest.php
│   │   │   ├── StoreInventoryItemRequest.php
│   │   │   └── UpdateInventoryItemRequest.php
│   │   └── Resources/                        # API Resource data transformations
│   │       ├── CustomerResource.php
│   │       └── InventoryItemResource.php
│   ├── Models/
│   │   ├── Customer.php                      # SoftDeletes trait enabled
│   │   ├── InventoryItem.php                 # SoftDeletes & strict type casts
│   │   └── User.php                          # Sanctum tokens & modern PHP attributes
│   └── Policies/                             # RBAC Authorization rules
│       ├── CustomerPolicy.php
│       └── InventoryItemPolicy.php
├── bootstrap/app.php                         # Configured for forced JSON exception responses
├── database/
│   ├── migrations/                           # Migrations including soft deletes & search indices
│   └── seeders/DatabaseSeeder.php            # Seed data for users, items, and customers
├── routes/api.php                            # Public and Sanctum-protected route definitions
└── tests/
    └── Feature/
        ├── AuthTest.php                      # Feature tests for authentication
        ├── CustomerTest.php                  # Feature tests for customer CRUD & validation
        └── InventoryItemTest.php             # Feature tests for inventory CRUD & validation
```

### Key Engineering Patterns Applied
1. **Single Responsibility**: Controllers are kept clean by delegating validation to `FormRequest` classes and output serialization to `JsonResource` classes.
2. **Data Retention & Integrity**: Soft deletes prevent catastrophic accidental data loss. Past purchase receipts or inventory audits remain historically accurate.
3. **Database Performance**: An index on the `name` column in `inventory_items` ensures high-throughput search queries across large catalogs.
4. **Client Consistency**: Type casts on `price` (`decimal:2`) and `quantity` (`integer`) guarantee uniform JSON types for frontend consumers without numeric string ambiguities.
5. **Quality Assurance via Automated Testing**: 100% test pass rate across 36 comprehensive feature tests verifying authentication, policies, validation, and error states.

---

### Submitted for
**Delivergate Backend Engineering Internship Assessment**  
*Stack: Laravel 13 • Laravel Sanctum • SQLite / MySQL • PHP 8.4 • PHPUnit 12*
