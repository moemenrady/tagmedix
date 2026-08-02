# Inventory Management API

## 1. System Architecture

The inventory module follows a service-oriented backend structure for Laravel 12:

- Controllers remain thin and delegate to services.
- Item and inventory operations are handled through dedicated services.
- Validation is enforced with Form Requests.
- Responses are returned through API Resources and a consistent JSON envelope.
- All write operations run inside database transactions.

## 2. Database Schema

### item_categories
- id
- name
- description
- is_active
- timestamps

### items
- id
- name
- code
- barcode
- category_id
- unit
- description
- minimum_quantity
- current_quantity
- average_cost
- last_purchase_price
- total_inventory_cost
- is_active
- timestamps
- soft deletes

### inventory_transactions
- id
- transaction_number
- item_id
- type
- quantity
- unit_cost
- total_cost
- balance_before
- balance_after
- reference_type
- reference_id
- purpose
- notes
- performed_by
- approved_by
- transaction_date
- timestamps

## 3. Relationships

- ItemCategory has many Items
- Item belongs to ItemCategory
- Item has many InventoryTransaction rows
- InventoryTransaction belongs to Item
- InventoryTransaction belongs to User for performed/approved by

## 4. Business Rules

- Negative quantities are not allowed.
- Issuing stock more than available is rejected.
- Inventory quantities are modified only through InventoryService.
- Every movement writes an inventory transaction.
- Total inventory cost and average cost are recalculated automatically.
- Transaction numbers are generated as TRX-00000001 style values.

## 5. API Endpoints

### Categories
- GET /api/v1/categories
- GET /api/v1/categories/{id}
- POST /api/v1/categories
- PUT /api/v1/categories/{id}
- DELETE /api/v1/categories/{id}

### Items
- GET /api/v1/items
- GET /api/v1/items/{id}
- POST /api/v1/items
- PUT /api/v1/items/{id}
- DELETE /api/v1/items/{id}
- PATCH /api/v1/items/{id}/restore
- PATCH /api/v1/items/{id}/toggle-status
- GET /api/v1/items/statistics
- GET /api/v1/items/low-stock

### Inventory
- POST /api/v1/inventory/opening-balance
- POST /api/v1/inventory/add
- POST /api/v1/inventory/issue
- POST /api/v1/inventory/return
- POST /api/v1/inventory/adjust
- POST /api/v1/inventory/count
- POST /api/v1/inventory/rollback/{transactionId}
- GET /api/v1/inventory/transactions
- GET /api/v1/inventory/items/{id}/transactions
- GET /api/v1/inventory/items/{id}/movement-summary
- GET /api/v1/inventory/value

## 6. Usage Guide

1. Create a category.
2. Create an item under that category.
3. Record opening balance or stock additions.
4. Issue stock for production.
5. Review transactions and inventory valuation.

## 7. Request Parameters

### Item create/update
- name
- code
- barcode
- category_id
- unit
- description
- minimum_quantity
- current_quantity
- is_active

### Inventory movement
- item_id
- quantity
- unit_cost
- notes
- reference_type
- reference_id
- purpose

## 8. Query Parameters

### Item listing
- search
- category
- sku
- barcode
- unit
- status
- available
- low_stock
- out_of_stock
- active
- inactive
- quantity_from
- quantity_to
- minimum_quantity
- created_from
- created_to
- sorting
- pagination

## 9. Validation Rules

- Item names and codes are required.
- Category references must exist.
- Quantities must be numeric and non-negative.
- Inventory movement quantities must be greater than zero.

## 10. Request Examples

### Create item
```http
POST /api/v1/items
Content-Type: application/json

{
  "name": "Plain Cotton Fabric",
  "code": "SKU-001",
  "barcode": "BAR-001",
  "category_id": 1,
  "unit": "Meter",
  "minimum_quantity": 20,
  "current_quantity": 0,
  "is_active": true
}
```

### Add stock
```http
POST /api/v1/inventory/add
Content-Type: application/json

{
  "item_id": 1,
  "quantity": 100,
  "unit_cost": 85,
  "notes": "Purchase received"
}
```

## 11. Success Response Examples

```json
{
  "success": true,
  "message": "Item created successfully.",
  "data": {
    "id": 1,
    "name": "Plain Cotton Fabric",
    "category": "Fabric",
    "unit": "Meter",
    "current_quantity": 100,
    "minimum_quantity": 20,
    "last_purchase_price": 85,
    "inventory_value": 8500,
    "status": "available"
  }
}
```

## 12. Error Response Examples

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "quantity": ["The quantity field must be greater than 0."]
  }
}
```

## 13. HTTP Status Codes

- 200 OK
- 201 Created
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 422 Unprocessable Entity
- 500 Internal Server Error

## 14. Inventory Transaction Types

- purchase
- opening_balance
- production_issue
- production_return
- adjustment_add
- adjustment_minus
- inventory_count
- damaged
- returned_supplier
- customer_return

## 15. Weighted Average Cost Calculation

The system uses a weighted average cost formula:

$$
\text{Average Cost} = \frac{\text{Existing Value} + \text{New Value}}{\text{Existing Quantity} + \text{New Quantity}}
$$

## 16. Real Workflow Examples

### Purchase
1. Create item
2. POST to /api/v1/inventory/add
3. System records transaction and updates inventory balance

### Issue
1. POST to /api/v1/inventory/issue
2. System validates stock availability and records a deduction

### Return
1. POST to /api/v1/inventory/return
2. System increases stock and records a return transaction

### Adjustment
1. POST to /api/v1/inventory/adjust
2. System updates balance and captures manual change

## 17. Service Execution Flow

- Controller receives request
- Form Request validates payload
- Service performs business rules and database transaction
- Model updates are persisted
- Resource transforms output into API response

## 18. Generated Project Structure

- app/Http/Controllers/Api
- app/Http/Requests
- app/Http/Resources
- app/Models
- app/Services
- database/migrations
- routes/api.php
- docs/inventory-api.md

## 19. Design Decisions & Assumptions

- The system uses a single warehouse assumption and does not create warehouse tables.
- The module is designed to be extended for purchasing, production, sales, and reporting.
- Soft deletes are enabled for items for audit and restore support.
