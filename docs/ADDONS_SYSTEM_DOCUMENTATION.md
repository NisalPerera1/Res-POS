# Add-ons System Documentation

## 🎯 Overview

The add-ons system allows restaurant staff to add extra items, ingredients, or modifications to existing order items. This system works seamlessly across both table orders and direct orders, with proper pricing calculations and display throughout the entire POS workflow.

## 🔧 Core Components

### 1. Database Structure (order_item_addons table)

The add-ons are stored in a dedicated database table with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `order_item_id` | bigint | Links the add-on to a specific order item |
| `addon_name` | string | The name of the add-on (e.g., "Extra Cheese", "Bullseye Egg") |
| `quantity` | decimal | The amount of the add-on (numeric value) |
| `unit` | string | The unit of measurement (grams, kg, pieces, units) |
| `unit_price` | decimal | Price per unit of the add-on |
| `total_price` | decimal | Total price for the add-on (quantity × unit_price) |
| `notes` | text | Additional notes or specifications for the add-on |
| `is_custom_price` | boolean | Boolean flag for market-price items |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### 2. Frontend Modal Interface

The add-ons modal provides an intuitive interface for staff to add extras:

#### Fields and Functions:
- **Item Name Input**: Text field for entering the add-on name
- **Quantity Input**: Numeric field for specifying the amount
- **Unit Selection**: Dropdown with options (grams, kg, pieces, units)
- **Total Amount**: Auto-calculated based on quantity and unit price
- **Notes Field**: Optional text area for special instructions
- **Add Button**: Submits the add-on to the current order item

### 3. Backend API Endpoints

#### Table Orders:
- `POST /api/orders/{id}/items/{itemId}/addons` - Add add-on to table order item
- `PUT /api/orders/{id}/items/{itemId}/addons/{addonId}` - Update add-on
- `DELETE /api/orders/{id}/items/{itemId}/addons/{addonId}` - Remove add-on

#### Direct Orders:
- `POST /api/direct-orders/{id}/items/{itemId}/addons` - Add add-on to direct order item
- `PUT /api/direct-orders/{id}/items/{itemId}/addons/{addonId}` - Update add-on
- `DELETE /api/direct-orders/{id}/items/{itemId}/addons/{addonId}` - Remove add-on

## 📱 User Interface Workflow

### Step 1: Accessing Add-ons
- **Table Orders**: Click the "+Extra" button next to any cart item in POSScreen.vue
- **Direct Orders**: Click the "+Extra" button next to any cart item in DirectOrderImproved.vue

### Step 2: Adding an Add-on
1. **Item Name**: Enter the add-on name (e.g., "Extra Cheese", "Special Sauce")
2. **Quantity**: Specify the amount (e.g., 500, 2, 1)
3. **Unit**: Select the appropriate unit:
   - `grams`: For weight-based items (cheese, spices)
   - `kg`: For heavier items (large portions)
   - `pieces`: For countable items (eggs, bread slices)
   - `units`: For packaged items (bottles, containers)
4. **Total Amount**: Enter the total price (system calculates unit price automatically)
5. **Notes**: Add any special instructions (e.g., "Add on side", "Extra spicy")

### Step 3: Processing
1. Click "Add Extra" button
2. System validates the input
3. Add-on is saved to database
4. Cart updates immediately with new pricing
5. Success message appears

## 💰 Pricing Integration

### Cart Display
- Add-ons appear as small badges under the main item
- Format: `+{addon_name} ({quantity}{unit})`
- Example: `+Extra Cheese (500g)`

### Payment Modal
- Add-ons listed with clear brackets showing they're extras
- Format: `Item Name (+Add-on details)`
- Example: `Rice (+Extra Cheese 500g - Rs.150)`

### Kitchen Display
- Add-ons appear with full details for kitchen staff
- Shows quantity, unit, and any special notes
- Integrated into KOT printing

## 🔄 System Integration

### Real-time Updates
- Add-ons immediately update cart totals
- Payment calculations include add-on prices
- Kitchen display refreshes automatically

### Data Flow
1. **Frontend**: User enters add-on details
2. **API**: Data sent to backend controller
3. **Database**: Add-on saved with proper relationships
4. **Frontend**: Cart refreshes with updated pricing
5. **Kitchen**: Display shows add-on details

### Error Handling
- Validation for required fields
- Error messages for failed operations
- Automatic retry mechanisms
- User-friendly feedback

## 🎯 Key Features

### Weight/Units Support
- Flexible unit system for different item types
- Accurate pricing based on weight or quantity
- Support for both packaged and loose items

### Cross-Platform Compatibility
- Works on both table orders and direct orders
- Consistent interface across all components
- Mobile-responsive design

### Pricing Accuracy
- Automatic unit price calculation
- Real-time total updates
- Integration with payment processing

### Kitchen Integration
- Clear display for kitchen staff
- Proper formatting on KOT tickets
- Notes and special instructions included

## 📊 Business Benefits

### Revenue Generation
- Easy upselling opportunities
- Customizable pricing for extras
- Accurate tracking of add-on sales

### Customer Satisfaction
- Personalized order modifications
- Clear pricing breakdown
- Special dietary accommodations

### Operational Efficiency
- Streamlined add-on process
- Reduced order errors
- Better kitchen communication

## 🛠 Technical Implementation

### Models and Relationships

#### OrderItemAddon Model
```php
class OrderItemAddon extends Model
{
    protected $fillable = [
        'order_item_id', 'addon_name', 'quantity', 'unit',
        'unit_price', 'total_price', 'notes', 'is_custom_price'
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function getFormattedUnitAttribute(): string
    {
        $unitMap = [
            'grams' => 'g',
            'kg' => 'kg',
            'pieces' => 'pcs',
            'units' => 'units',
        ];
        return $unitMap[$this->unit] ?? $this->unit;
    }
}
```

#### OrderItem Model (Updated)
```php
public function addons(): HasMany
{
    return $this->hasMany(OrderItemAddon::class);
}

public function getTotalWithAddonsAttribute(): float
{
    $addonsTotal = $this->addons->sum('total_price');
    return (float) $this->total_price + $addonsTotal;
}

public function getAddonsDisplayAttribute(): string
{
    if ($this->addons->isEmpty()) {
        return '';
    }

    return $this->addons->map(function ($addon) {
        return "+{$addon->addon_name} ({$addon->quantity}{$addon->formatted_unit})";
    })->implode(', ');
}
```

### API Controller Methods

#### Add Add-on Endpoint
```php
public function addAddon(Request $request, $orderId, $orderItemId)
{
    $request->validate([
        'addon_name' => 'required|string|max:100',
        'quantity' => 'required|numeric|min:0.01',
        'unit' => 'required|in:grams,kg,pieces,units',
        'total_price' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:255',
        'is_custom_price' => 'nullable|boolean',
    ]);

    $orderItem = OrderItem::where('order_id', $orderId)
        ->findOrFail($orderItemId);

    $unitPrice = $request->total_price / $request->quantity;

    $addon = OrderItemAddon::create([
        'order_item_id' => $orderItem->id,
        'addon_name' => $request->addon_name,
        'quantity' => $request->quantity,
        'unit' => $request->unit,
        'unit_price' => $unitPrice,
        'total_price' => $request->total_price,
        'notes' => $request->notes,
        'is_custom_price' => $request->boolean('is_custom_price', false),
    ]);

    $this->updateOrderTotals($orderId);
    return response()->json($addon->load('orderItem'), 201);
}
```

### Frontend Components

#### AddonsModal.vue
- Handles add-on creation and management
- Form validation and user feedback
- Real-time price calculations
- Integration with both order types

#### POSScreen.vue Integration
- "+Extra" button in unsent item controls
- Modal integration for table orders
- Real-time cart updates

#### DirectOrderImproved.vue Integration
- "+Extra" button in cart controls
- Modal integration for direct orders
- Consistent UI/UX with table orders

## 🔍 Database Migration

```php
Schema::create('order_item_addons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
    $table->string('addon_name'); // e.g., "Extra Cheese", "Bullseye Egg"
    $table->decimal('quantity', 10, 2); // amount of the add-on
    $table->string('unit'); // grams, kg, pieces, units
    $table->decimal('unit_price', 10, 2); // price per unit
    $table->decimal('total_price', 10, 2); // quantity × unit_price
    $table->text('notes')->nullable(); // additional specifications
    $table->boolean('is_custom_price')->default(false); // for market-price items
    $table->timestamps();
    
    // Indexes for performance
    $table->index('order_item_id');
    $table->index('addon_name');
});
```

## 📋 Testing and Validation

### Unit Tests
- Model relationships and accessors
- API endpoint validation
- Price calculation accuracy
- Data integrity constraints

### Integration Tests
- End-to-end add-on workflow
- Cart total calculations
- Payment processing integration
- Kitchen display updates

### User Acceptance Testing
- Staff training and ease of use
- Mobile responsiveness
- Performance under load
- Error handling scenarios

## 🚀 Deployment Instructions

1. **Database Migration**: Run the migration to create the `order_item_addons` table
   ```bash
   php artisan migrate
   ```

2. **Clear Caches**: Clear application caches to ensure new models are loaded
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. **Frontend Build**: Rebuild frontend assets if needed
   ```bash
   npm run build
   ```

4. **Training**: Train staff on the new add-on functionality
   - Demonstrate the +Extra button workflow
   - Explain unit selection and pricing
   - Show how add-ons appear in different views

## 🔮 Future Enhancements

### Potential Improvements
- **Predefined Add-ons**: Create a menu of common add-ons for quick selection
- **Add-on Categories**: Group add-ons by type (sauces, proteins, toppings)
- **Bulk Pricing**: Special pricing for multiple add-ons
- **Add-on Analytics**: Reporting on add-on sales and popularity
- **Kitchen Preparation**: Add-on-specific preparation instructions

### Integration Opportunities
- **Inventory Management**: Track add-on stock levels
- **Supplier Integration**: Automatic ordering for popular add-ons
- **Customer Preferences**: Remember frequent add-on choices
- **Promotional Features**: Special pricing on add-on combinations

---

This comprehensive add-ons system provides restaurants with the flexibility to customize orders while maintaining accurate pricing and clear communication between front-of-house and kitchen staff.
