@echo off
echo Setting up POS Development Environment...

REM Build assets once
echo Building assets...
npm run build

REM Clear Laravel caches
echo Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan view:clear

REM Copy manifest to correct location
echo Setting up manifest...
copy "public\build\.vite\manifest.json" "public\build\manifest.json"

REM Start Laravel server
echo Starting Laravel server...
php artisan serve

pause
