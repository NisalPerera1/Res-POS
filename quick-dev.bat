@echo off
echo Quick POS Development Setup...

REM Just ensure manifest is in place
if not exist "public\build\manifest.json" (
    echo Copying manifest file...
    copy "public\build\.vite\manifest.json" "public\build\manifest.json"
)

REM Clear caches
php artisan cache:clear

REM Start server
echo Starting Laravel server on http://127.0.0.1:8000
php artisan serve
