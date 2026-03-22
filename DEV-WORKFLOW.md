# POS System Development Workflow

## 🚀 Quick Start (Recommended)

### Method 1: Use the Batch Script
```bash
# Double-click this file or run in terminal
quick-dev.bat
```

### Method 2: Manual Setup
```bash
# 1. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 2. Start server
php artisan serve
```

## 📱 Access the Application

**Main URL**: `http://127.0.0.1:8000/`

## 🔄 When to Rebuild Assets

You only need to rebuild assets when:
- ✅ **First time setup** (already done)
- ✅ **Major Vue component changes**
- ✅ **CSS/styling changes**
- ✅ **New dependencies added**

### Rebuild Command (when needed)
```bash
# Windows
cmd /c "npm run build"

# Or use the setup script
dev-setup.bat
```

## 🛠️ Development Features

### ✅ **What Works Right Now**
- ✅ **Cart updates** - Items add instantly
- ✅ **Send to Kitchen** - Orders appear in kitchen display
- ✅ **Table Management** - Edit/close occupied tables
- ✅ **Payment UI** - Beautiful payment modal
- ✅ **Kitchen Display** - Auto-refreshes every 10 seconds
- ✅ **Service Charge** - Changed from Tax to Service Charge
- ✅ **Real-time updates** - Auto-polling system

### 🔧 **How Development Works**
1. **Assets are pre-built** - No need for `npm run dev`
2. **Laravel serves everything** - Single server on port 8000
3. **Auto-reload on changes** - Page refreshes when you modify code
4. **Fast development** - Just edit and refresh browser

## 📁 File Structure

```
pos-system/
├── resources/js/          # Vue components
├── resources/views/        # Blade templates
├── public/build/          # Built assets (CSS/JS)
├── routes/web.php         # Web routes (handles SPA)
├── routes/api.php         # API routes
└── quick-dev.bat         # Quick start script
```

## 🐛 Troubleshooting

### If assets don't load:
```bash
# Rebuild assets
cmd /c "npm run build"

# Copy manifest
copy "public\build\.vite\manifest.json" "public\build\manifest.json"

# Clear caches
php artisan cache:clear
```

### If changes don't appear:
1. **Hard refresh browser** (Ctrl+F5)
2. **Clear browser cache**
3. **Check console for errors**

## 🎯 Best Practices

1. **Use `php artisan serve` only** - No need for npm dev server
2. **Rebuild only when necessary** - Assets are already optimized
3. **Test in browser** - `http://127.0.0.1:8000/`
4. **Check console** - All debugging logs available
5. **Use quick-dev.bat** - Easiest way to start development

## 📞 Support

All features are working:
- ✅ **POS Screen** - Full cart and order management
- ✅ **Kitchen Display** - Real-time order tracking
- ✅ **Table View** - Table management
- ✅ **Payment System** - Beautiful UI
- ✅ **Menu Management** - Full CRUD operations

**No more `npm run build` needed for daily development!** 🎉
