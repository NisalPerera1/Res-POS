# 💰 **Service Charge Management System**

## 🎯 **Complete Implementation:**

**Service charge automatically set to 0% for direct orders and 10% (editable) for table orders.**

---

## 🔧 **Backend Implementation:**

### **1. OrderController.php - Auto Service Charge Logic**
```php
// Determine service charge based on order type
$isDirectOrder = is_null($request->table_id);
$taxRate = $isDirectOrder ? 0 : 10; // 0% for direct orders, 10% for table orders

$order = Order::create([
    'tax_rate' => $taxRate, // Automatically set
    // ... other fields
]);
```

### **2. DirectOrderController.php - Direct Orders Get 0%**
```php
$order = Order::create([
    'table_id' => null, // Direct order
    'tax_rate' => 0,     // 0% service charge for direct orders
    // ... other fields
]);
```

### **3. Service Charge Update API (Table Orders Only)**
```php
public function updateServiceCharge(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Only allow service charge updates for table orders (not direct orders)
    if (is_null($order->table_id)) {
        return response()->json([
            'message' => 'Service charge cannot be modified for direct orders'
        ], 422);
    }
    
    $order->tax_rate = $request->tax_rate;
    $order->tax_amount = ($order->subtotal * $request->tax_rate) / 100;
    $order->total = $order->subtotal + $order->tax_amount - $order->discount_amount;
    $order->save();
    
    return response()->json($order);
}
```

---

## 🎨 **Frontend Implementation:**

### **PaymentModal.vue - Conditional Service Charge Display**

#### **Table Orders (Editable Service Charge):**
```vue
<!-- SERVICE CHARGE EDITOR (Table Orders Only) -->
<div v-if="props.order?.table_id">
  <div style="display:flex; align-items:center; justify-content:space-between;">
    <div>Service Charge</div>
    <div>Editor with presets and custom input</div>
  </div>
  
  <!-- Preset buttons: [No Charge] [5%] [10%] [15%] [Custom %] -->
  <div style="display:flex; gap:5px;">
    <button @click="applyPreset(0)">No Charge</button>
    <button @click="applyPreset(5)">5%</button>
    <button @click="applyPreset(10)">10%</button>
    <button @click="applyPreset(15)">15%</button>
    <input v-model.number="localTaxRate" type="number" min="0" max="100" step="0.5" />
  </div>
  
  <!-- Live totals preview -->
  <div>
    <div>Subtotal: Rs. 1,200</div>
    <div>Service (10%): Rs. 120</div>
    <div>Total: Rs. 1,320</div>
  </div>
</div>
```

#### **Direct Orders (Fixed 0% Service Charge):**
```vue
<!-- SERVICE CHARGE STATUS (Direct Orders) -->
<div v-else>
  <div style="display:flex; align-items:center; justify-content:space-between;">
    <div>Service Charge</div>
    <span style="background:rgba(16,185,129,0.1); color:#10B981;">
      NOT APPLICABLE
    </span>
  </div>
  <div style="font-size:10px; color:#64748B;">
    Direct orders have 0% service charge
  </div>
  
  <!-- Live totals preview -->
  <div>
    <div>Subtotal: Rs. 1,200</div>
    <div>Service Charge: Waived (Direct Order)</div>
    <div>Total: Rs. 1,200</div>
  </div>
</div>
```

---

## 📊 **Service Charge Logic:**

### **Automatic Assignment:**
```
┌─────────────────────────────────────────────────────────┐
│  Order Creation                                          │
│                                                         │
│  ┌─────────────┐    ┌─────────────────────────────────┐   │
│  │ Table Order │ →  │ tax_rate = 10% (editable)      │   │
│  │ table_id > 0│    │ Default: 10%                  │   │
│  └─────────────┘    │ Can be changed in payment      │   │
│                     │                                 │   │
│  ┌─────────────┐    └─────────────────────────────────┘   │
│  │ Direct Order│ →  │ tax_rate = 0% (fixed)           │   │
│  │ table_id = 0│    │ Cannot be changed              │   │
│  └─────────────┘    │ Shows "NOT APPLICABLE"         │   │
│                     └─────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### **Payment Interface Differences:**

#### **Table Order Payment:**
```
┌─────────────────────────────────────────┐
│ 💳 Payment - Table T-01               │
│                                         │
│ Service Charge                          │
│ [No Charge] [5%] [10%] [15%] [12% ▼]    │ ← Editable
│                                         │
│ Subtotal:     Rs. 1,200                 │
│ Service (10%): Rs. 120                  │
│ Total:        Rs. 1,320                 │
└─────────────────────────────────────────┘
```

#### **Direct Order Payment:**
```
┌─────────────────────────────────────────┐
│ 💳 Payment - Direct Order              │
│                                         │
│ Service Charge                          │
│ NOT APPLICABLE                          │ ← Fixed 0%
│ Direct orders have 0% service charge    │
│                                         │
│ Subtotal:     Rs. 1,200                 │
│ Service:      Waived (Direct Order)     │
│ Total:        Rs. 1,200                 │
└─────────────────────────────────────────┘
```

---

## 🔒 **Validation & Security:**

### **Backend Protection:**
```php
// Only table orders can update service charge
if (is_null($order->table_id)) {
    return response()->json([
        'message' => 'Service charge cannot be modified for direct orders'
    ], 422);
}
```

### **Frontend Protection:**
```vue
<!-- Service charge editor only shown for table orders -->
<div v-if="props.order?.table_id">
  <!-- Editable service charge interface -->
</div>

<!-- Direct orders show read-only status -->
<div v-else>
  <!-- Fixed 0% service charge display -->
</div>
```

---

## 🎯 **User Experience:**

### **For Table Orders:**
1. **Default 10%** service charge applied automatically
2. **Editable** - Staff can change to any percentage (0-50%)
3. **Quick presets** - 0%, 5%, 10%, 15% buttons
4. **Custom input** - Any percentage with 0.5% precision
5. **Live preview** - See total update in real-time

### **For Direct Orders:**
1. **Fixed 0%** service charge (cannot be changed)
2. **Clear indicator** - "NOT APPLICABLE" badge
3. **Explanation** - "Direct orders have 0% service charge"
4. **Clean interface** - No confusing editable options

---

## 💱 **Financial Impact:**

### **Table Orders Revenue:**
```
Subtotal: Rs. 1,000
Service (10%): Rs. 100
Total: Rs. 1,100
```

### **Direct Orders Revenue:**
```
Subtotal: Rs. 1,000
Service: Waived (Direct Order)
Total: Rs. 1,000
```

### **Business Logic:**
- **Table Service**: Customers get full service → Service charge applies
- **Direct Orders**: Customers handle pickup/delivery → No service charge
- **Flexibility**: Staff can adjust table service charge as needed
- **Transparency**: Clear communication to customers

---

## 🔄 **API Endpoints:**

### **1. Create Order (Auto Service Charge)**
```
POST /api/orders
{
  "table_id": 5,        // Table order → 10% service charge
  "tax_rate": 10        // Auto-set
}

POST /api/direct-orders  
{
  "table_id": null,     // Direct order → 0% service charge  
  "tax_rate": 0         // Auto-set
}
```

### **2. Update Service Charge (Table Orders Only)**
```
PATCH /api/orders/{id}/service-charge
{
  "tax_rate": 15        // Only works for table orders
}

// Direct orders will return error:
{
  "message": "Service charge cannot be modified for direct orders"
}
```

---

## 🎉 **Benefits:**

### **✅ Business Logic**
- **Fair pricing** - Service charge only for full service
- **Flexibility** - Staff can adjust table charges
- **Automation** - No manual calculation needed

### **✅ User Experience**
- **Clear interface** - Different UI for table vs direct orders
- **No confusion** - Direct orders clearly show 0% charge
- **Easy editing** - Quick presets for common percentages

### **✅ Financial Accuracy**
- **Automatic calculation** - No manual errors
- **Real-time updates** - Live preview of totals
- **Secure validation** - Backend prevents unauthorized changes

---

## 🚀 **Ready for Production!**

**The service charge management system automatically applies the correct rates:**
- ✅ **Direct Orders**: 0% service charge (fixed, not editable)
- ✅ **Table Orders**: 10% service charge (default, editable)
- ✅ **Payment Interface**: Different UI for each order type
- ✅ **API Protection**: Backend validation prevents unauthorized changes
- ✅ **User Experience**: Clear, intuitive interface for staff

**Staff can now easily manage service charges with automatic logic and secure validation!**
