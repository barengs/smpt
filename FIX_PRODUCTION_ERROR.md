# Fix: Interface "Maatwebsite\Excel\Concerns\ToModel" not found

## 🔴 Error Message:

```
Error when analyzing route 'POST api/main/student/import'
(App\Http\Controllers\Api\Main\StudentController@import):
Interface "Maatwebsite\Excel\Concerns\ToModel" not found
– /home/umex1887/api-smp/app/Imports/StudentsImport.php on line 20
```

## 📋 Root Cause:

The Laravel Excel package (`maatwebsite/excel`) is not installed on your production server at `/home/umex1887/api-smp/`

## ✅ Quick Fix (Recommended)

### Option 1: Using the Fix Script

1. **Upload the fix script to your server:**

    ```bash
    # Upload fix-import-production.sh to /home/umex1887/api-smp/
    ```

2. **SSH to your server:**

    ```bash
    ssh your-username@your-server
    cd /home/umex1887/api-smp
    ```

3. **Make the script executable and run it:**

    ```bash
    chmod +x fix-import-production.sh
    ./fix-import-production.sh
    ```

4. **Done!** The script will automatically:
    - Install Laravel Excel package
    - Regenerate autoload files
    - Clear all caches
    - Optimize the application
    - Verify the installation

---

### Option 2: Manual Fix (Step by Step)

SSH to your production server and run these commands:

```bash
# 1. Navigate to project directory
cd /home/umex1887/api-smp

# 2. Install Laravel Excel package
composer require maatwebsite/excel

# 3. Regenerate autoload
composer dump-autoload -o

# 4. Publish config (optional)
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

# 5. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Optimize application
php artisan config:cache
php artisan route:cache
php artisan optimize

# 7. Verify installation
composer show | grep maatwebsite
php artisan route:list --path=import
```

---

### Option 3: Upload Vendor Directory

If you can't run composer on the server:

1. **On your local machine:**

    ```bash
    cd /Users/ROFI/Develop/proyek/smp
    composer install --no-dev --optimize-autoloader
    ```

2. **Upload these files to the server:**

    - `composer.json`
    - `composer.lock`
    - `vendor/` (entire directory)
    - `config/excel.php`

3. **On the server:**
    ```bash
    cd /home/umex1887/api-smp
    php artisan optimize
    php artisan config:cache
    php artisan route:cache
    ```

---

## ✅ Verification

After fixing, verify the installation:

### 1. Check Package Installation:

```bash
cd /home/umex1887/api-smp
composer show maatwebsite/excel
```

Expected output:

```
name     : maatwebsite/excel
descrip. : Supercharged Excel exports and imports in Laravel
versions : * 3.1.67
```

### 2. Check Routes:

```bash
php artisan route:list --path=import
```

Expected output:

```
POST       api/main/parent/import
GET|HEAD   api/main/parent/import/template
POST       api/main/student/import
GET|HEAD   api/main/student/import/template
```

### 3. Test Import Endpoint:

```bash
curl -X GET http://your-domain.com/api/main/student/import/template \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Should download a CSV template file.

---

## 🔍 Troubleshooting

### Still getting the error?

1. **Clear OPcache (if enabled):**

    ```bash
    # Add to your web server or run via PHP
    php -r "if(function_exists('opcache_reset')) opcache_reset();"
    ```

2. **Restart PHP-FPM or Web Server:**

    ```bash
    # For PHP-FPM
    sudo systemctl restart php8.2-fpm

    # For Apache
    sudo systemctl restart apache2

    # For Nginx
    sudo systemctl restart nginx
    ```

3. **Check PHP Extensions:**

    ```bash
    php -m | grep -E 'zip|xml|gd|mbstring|fileinfo'
    ```

    All should be present.

4. **Check Autoload:**

    ```bash
    cat vendor/composer/autoload_psr4.php | grep Maatwebsite
    ```

    Should show: `'Maatwebsite\\Excel\\' => ...`

5. **Full Cache Clear:**
    ```bash
    php artisan optimize:clear
    composer dump-autoload -o
    php artisan optimize
    ```

---

## 📁 Required Files on Server

Make sure these files exist on your server:

```
/home/umex1887/api-smp/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── Main/
│   │               ├── StudentController.php (updated)
│   │               └── ParentController.php (updated)
│   └── Imports/
│       ├── StudentsImport.php (NEW)
│       └── ParentsImport.php (NEW)
├── routes/
│   └── api.php (updated)
├── vendor/
│   └── maatwebsite/
│       └── excel/ (installed via composer)
├── config/
│   └── excel.php (optional)
├── composer.json (updated)
└── composer.lock (updated)
```

---

## 🚀 Production Deployment Checklist

-   [ ] Upload new files (StudentsImport.php, ParentsImport.php)
-   [ ] Upload updated files (Controllers, routes)
-   [ ] Run `composer require maatwebsite/excel` on server
-   [ ] Run `composer dump-autoload -o`
-   [ ] Run `php artisan optimize`
-   [ ] Clear all caches
-   [ ] Restart web server/PHP-FPM
-   [ ] Test import routes
-   [ ] Verify in browser/API client
-   [ ] Monitor error logs

---

## 📞 Emergency Rollback

If something goes wrong:

```bash
cd /home/umex1887/api-smp

# Restore from backup
cp -r backup/app/Http/Controllers app/Http/
cp backup/routes/api.php routes/

# Remove import classes
rm -f app/Imports/StudentsImport.php
rm -f app/Imports/ParentsImport.php

# Clear caches
php artisan optimize:clear
php artisan optimize
```

---

## ✨ Success Indicators

You'll know it's working when:

1. ✅ No errors when accessing any route
2. ✅ `composer show maatwebsite/excel` shows the package
3. ✅ `php artisan route:list --path=import` shows 4 routes
4. ✅ Template download works
5. ✅ Import endpoint accepts files without errors

---

**Need more help?** Check the detailed deployment guide: `DEPLOYMENT_IMPORT_FEATURES.md`
