# Kitchen Printer Setup Guide

## Overview
This POS system automatically prints Kitchen Order Tickets (KOT) when items are sent to the kitchen. The system supports both network and USB printers using ESC/POS commands.

## Configuration

### 1. Environment Variables
Add these to your `.env` file:

```env
# Printer Configuration
KITCHEN_PRINTER_IP=192.168.1.100
KITCHEN_PRINTER_PORT=9100
RECEIPT_PRINTER_IP=192.168.1.101
RECEIPT_PRINTER_PORT=9100

# KOT Settings
KOT_AUTO_PRINT=true
KOT_PRINT_VOID_NOTICES=true
KOT_DUPLICATE_COPIES=1
```

### 2. Printer IP Configuration
- **Kitchen Printer IP**: The IP address of your kitchen thermal printer
- **Port**: Usually 9100 for network printers
- **Default**: 192.168.1.100:9100

## Supported Printers
- ESC/POS compatible thermal printers
- Network printers (recommended)
- USB printers (fallback)
- Most Epson, Star, and custom thermal printers

## KOT Print Content
When items are sent to kitchen, the following is printed:

### Header
- "KITCHEN ORDER TICKET"
- Order number and KOT round
- Time and date
- Table number and section
- Customer name and guest count

### Order Details
- Order type (DINE_IN, TAKEAWAY, etc.)
- List of items with quantities
- Item notes and modifiers
- Special instructions

### Footer
- "PLEASE ACKNOWLEDGE" with signature line
- Auto paper cut

## Automatic Printing Triggers

### 1. Send to Kitchen (KOT)
- **Trigger**: When clicking "Send to Kitchen" button
- **Prints**: Full KOT with all new items
- **Response**: Shows "sent to kitchen and printed" if successful

### 2. Item Void/Cancellation
- **Trigger**: When voiding an item already sent to kitchen
- **Prints**: Void notice with item details and reason
- **Only if**: Item has KOT round number

## Testing the Printer

### Command Line Test
```bash
# Test KOT printing
php artisan printer:test --type=kot

# Test void notice printing  
php artisan printer:test --type=void
```

### In-App Test
1. Create a new order
2. Add some items
3. Click "Send to Kitchen"
4. Check if KOT prints automatically

## Troubleshooting

### Printer Not Found
1. Check printer IP and port in `.env`
2. Ensure printer is connected to network
3. Test connectivity: `ping 192.168.1.100`
4. Check firewall settings

### Print Quality Issues
1. Check printer paper alignment
2. Verify printer driver settings
3. Test with different font sizes

### No Auto-Print
1. Check `KOT_AUTO_PRINT=true` in `.env`
2. Verify printer service is running
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

## Printer Settings (Recommended)
- **Characters per line**: 42
- **Font size**: Normal
- **Auto cut**: Enabled
- **Line spacing**: 30

## Network Setup
1. Connect printer to restaurant network
2. Set static IP (recommended)
3. Configure port 9100 (or as required)
4. Test with printer's utility software

## ESC/POS Package
The system uses `mike42/escpos-php` for printer communication:
- Supports ESC/POS commands
- Handles multiple printer types
- Automatic fallback mechanisms
- Error logging and recovery

## Logs
Printer activities are logged to:
- Laravel logs: `storage/logs/laravel.log`
- Look for "KOT printed successfully" messages
- Check errors for connectivity issues

## Security Notes
- Printer should be on internal network only
- Consider firewall rules for printer ports
- Regular printer status monitoring recommended
