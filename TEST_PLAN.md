# POS System Test Plan

## 1. Login System Tests

### 1.1 Keyboard Login
- [ ] Select user profile
- [ ] Type PIN using keyboard (0-9)
- [ ] Test Backspace/Delete keys
- [ ] Test Enter to submit
- [ ] Test Escape to clear selection
- [ ] Verify visual keyboard hint appears
- [ ] Test wrong PIN error handling

### 1.2 Virtual Number Pad
- [ ] Click number buttons
- [ ] Test backspace button (⌫)
- [ ] Test enter button (↵)
- [ ] Verify PIN dots fill correctly
- [ ] Test auto-submit after 4 digits

## 2. Menu Manager Tests

### 2.1 Image Upload
- [ ] Add new menu item with image
- [ ] Verify image preview (110x90px)
- [ ] Test image file validation
- [ ] Edit existing item image
- [ ] Verify image appears in item list
- [ ] Test upload error handling

### 2.2 Item Management
- [ ] Create new item
- [ ] Edit item details
- [ ] Delete item
- [ ] Test category assignment
- [ ] Verify price formatting (Rs.)

## 3. POS Screen Tests

### 3.1 Item Display
- [ ] Verify 110x90px images
- [ ] Test 12px border radius
- [ ] Check fallback placeholder (🍽️)
- [ ] Verify item names and prices
- [ ] Test category filtering

### 3.2 Cart Operations
- [ ] Add item to cart
- [ ] Verify currency format (Rs.)
- [ ] Test quantity adjustment
- [ ] Remove item from cart
- [ ] Calculate total correctly

## 4. Add to Cart Modal Tests

### 4.1 Modifier Selection
- [ ] Open modifier modal
- [ ] Select required modifiers
- [ ] Test optional modifiers
- [ ] Verify modifier prices (Rs.)
- [ ] Test max selection limits
- [ ] Test min selection requirements

### 4.2 Price Display
- [ ] Verify base price: Rs.XX.XX
- [ ] Check modifier prices: +Rs.XX.XX
- [ ] Verify total price: Rs.XX.XX
- [ ] Test quantity multiplication
- [ ] Verify add button text

## 5. Direct Order Tests

### 5.1 Order Creation
- [ ] Create new order
- [ ] Select customer
- [ ] Add items with modifiers
- [ ] Verify order total
- [ ] Test order save
- [ ] Verify order history

### 5.2 Kitchen Display
- [ ] Check order appears in kitchen
- [ ] Verify order details
- [ ] Test order status updates
- [ ] Verify KOT generation

## 6. Payment System Tests

### 6.1 Payment Processing
- [ ] Test cash payment
- [ ] Test card payment
- [ ] Verify service charge
- [ ] Test discount application
- [ ] Generate receipt

### 6.2 Currency Display
- [ ] Verify all prices show Rs.
- [ ] Check totals format
- [ ] Test decimal places
- [ ] Verify receipt format

## 7. Image Display Tests

### 7.1 Menu Manager
- [ ] Images show 110x90px
- [ ] 12px border radius applied
- [ ] Placeholder appears when no image
- [ ] Image preview in form

### 7.2 POS Screens
- [ ] POSScreen shows images correctly
- [ ] DirectOrderSimple shows images
- [ ] Consistent sizing across components
- [ ] Fallback placeholders work

## 8. Docker Deployment Tests

### 8.1 Container Setup
- [ ] Build Docker containers
- [ ] Start all services
- [ ] Verify database connection
- [ ] Test API endpoints
- [ ] Check web interface

### 8.2 Service Health
- [ ] Nginx serves frontend
- [ ] PHP-FPM processes requests
- [ ] MySQL database accessible
- [ ] Redis cache working
- [ ] phpMyAdmin accessible

## Test Execution Order

1. **Environment Setup**
   - Start Docker containers
   - Verify all services running
   - Check database seeded

2. **Authentication Flow**
   - Test keyboard login
   - Test virtual pad login
   - Verify user roles

3. **Menu Management**
   - Test image upload
   - Test item CRUD
   - Verify currency format

4. **POS Operations**
   - Test item display
   - Test cart operations
   - Test modifier selection

5. **Order Processing**
   - Test order creation
   - Test payment processing
   - Test kitchen display

6. **Final Verification**
   - Check all currency displays
   - Verify image consistency
   - Test error handling

## Expected Results

- All currency displays show "Rs." prefix
- Images display at 110x90px with 12px border radius
- Keyboard login works seamlessly
- Add to cart shows correct pricing
- Docker deployment functions properly
