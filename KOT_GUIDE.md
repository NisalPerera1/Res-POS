# KOT Printing Guide

## 🚀 **How to Print KOT in Kitchen**

### **Method 1: Automatic (Recommended)**
When you add items to an order and click **"Send to Kitchen"**:
1. ✅ KOT automatically prints in kitchen
2. ✅ Response is instant (under 1 second)
3. ✅ Queue handles printing in background

### **Method 2: Manual Print**
For reprinting or manual control:
```bash
# Print specific KOT round
POST /api/orders/{id}/print-kot
Body: { "kot_round": 1 }

# Print latest KOT round
POST /api/orders/{id}/print-kot
```

### **Method 3: Command Line Test**
```bash
# Test printer status
php artisan kot:status

# Test KOT print
php artisan printer:test --type=kot
```

## ⚡ **Performance Improvements**

### **Before (20+ seconds):**
- ❌ Synchronous printing
- ❌ Blocked user interface
- ❌ Poor user experience

### **After (Under 1 second):**
- ✅ Asynchronous queue printing
- ✅ Instant user feedback
- ✅ Background processing
- ✅ Retry on failure

## 🖨 **Printer Setup**

### **Configuration (.env):**
```env
KITCHEN_PRINTER_IP=192.168.1.100
KITCHEN_PRINTER_PORT=9100
KOT_AUTO_PRINT=true
```

### **Queue Worker:**
```bash
# Start queue worker for printing
php artisan queue:work --queue=printing --daemon
```

## 📋 **KOT Format**

```
KITCHEN ORDER TICKET
====================
Order #: ORD-123
KOT Round: 2
Time: 14:30:25
Date: 2026-03-25
Table: T5 (Main Section)
Customer: John Smith
Guests: 4
====================

DINE_IN

ITEMS TO PREPARE:
====================
1. Cheese Burger
   Qty: 2
   Notes: No onions
   + Extra Cheese
   + Bacon

2. Fries
   Qty: 1
   Notes: Extra salt

====================
SPECIAL INSTRUCTIONS:
Customer allergic to nuts
====================
PLEASE ACKNOWLEDGE
RECEIPT: ________
```

## 🔧 **Troubleshooting**

### **KOT Not Printing:**
1. Check queue worker: `php artisan queue:status`
2. Test printer: `php artisan kot:status`
3. Check logs: `tail -f storage/logs/laravel.log`

### **Slow Response:**
1. Ensure queue worker running
2. Check printer connection
3. Verify IP/Port settings

### **Print Quality:**
1. Check printer paper
2. Verify printer power
3. Test with `php artisan printer:test`

## 📞 **Quick Commands**

```bash
# Start everything
php artisan serve
php artisan queue:work --queue=printing --daemon

# Test printer
php artisan kot:status

# Check queue status
php artisan queue:status

# Clear queue if stuck
php artisan queue:clear
```

## 🎯 **Best Practices**

1. **Always use "Send to Kitchen"** for automatic printing
2. **Keep queue worker running** for async printing
3. **Test printer daily** with `php artisan kot:status`
4. **Check logs** if printing fails
5. **Use manual print** only for reprints

## 🚨 **Important Notes**

- **Queue worker must be running** for auto-print
- **Printer must be connected** to network
- **Paper must be loaded** in printer
- **IP address must be correct** in configuration

The KOT system is now **optimized for speed** and will print instantly when you send items to kitchen!
