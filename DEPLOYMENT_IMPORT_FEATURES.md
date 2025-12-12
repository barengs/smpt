# Deployment Guide for Import Features

## Error Fix: "Interface Maatwebsite\Excel\Concerns\ToModel not found"

This error occurs when deploying to production because the composer dependencies need to be installed on the server.

## 🚀 Deployment Steps for Production Server

### Step 1: Upload Files to Server

Upload the following files to your production server at `/home/umex1887/api-smp/`:

**New Files:**

-   `app/Imports/StudentsImport.php`
-   `app/Imports/ParentsImport.php`

**Modified Files:**

-   `app/Http/Controllers/Api/Main/StudentController.php`
-   `app/Http/Controllers/Api/Main/ParentController.php`
-   `routes/api.php`
-   `composer.json` (updated with maatwebsite/excel)
-   `composer.lock` (updated)

**Optional Documentation:**

-   `STUDENT_IMPORT_GUIDE.md`
-   `PARENT_IMPORT_GUIDE.md`
-   `IMPORT_FEATURES_SUMMARY.md`
-   `public/csv/student_import_sample.csv`
-   `public/csv/parent_import_sample.csv`

### Step 2: SSH to Production Server

```bash
ssh username@your-server-ip
cd /home/umex1887/api-smp
```

### Step 3: Install Composer Dependencies

```bash
# Install the Laravel Excel package
composer require maatwebsite/excel

# OR update all dependencies
composer update

# OR if composer.lock is uploaded, just install
composer install --no-dev --optimize-autoloader
```

### Step 4: Publish Excel Config (Optional)

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

### Step 5: Clear and Optimize Caches

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate optimized files
php artisan config:cache
php artisan route:cache
php artisan optimize

# Dump autoload
composer dump-autoload -o
```

### Step 6: Set Permissions

```bash
# Ensure storage and cache directories are writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or if using a different user
chown -R $USER:www-data storage bootstrap/cache
```

### Step 7: Verify Installation

```bash
# Check if maatwebsite/excel is installed
composer show | grep maatwebsite

# Should output:
# maatwebsite/excel   3.1.67  ...

# Test routes
php artisan route:list --path=import
```

### Step 8: Test the Import Feature

Try accessing the import endpoint:

```bash
curl -X GET https://your-domain.com/api/main/student/import/template \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🔧 Alternative: Manual Installation on Server

If you can't use composer on the server, you can:

### Option A: Install Locally, Upload Vendor

1. **On Local Machine:**

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

2. **Upload entire vendor directory to server**

    ```bash
    # Using rsync
    rsync -avz vendor/ user@server:/home/umex1887/api-smp/vendor/

    # Or using FTP/SFTP
    # Upload the entire vendor folder
    ```

3. **On Server:**
    ```bash
    cd /home/umex1887/api-smp
    php artisan optimize
    ```

### Option B: Use Deployment Tool

If using tools like Deployer, Laravel Forge, or Envoyer, add these commands to your deployment script:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan config:cache
php artisan route:cache
php artisan optimize
```

---

## 📋 Verification Checklist

After deployment, verify:

-   [ ] `vendor/maatwebsite/excel` directory exists
-   [ ] `composer.json` includes `"maatwebsite/excel": "^3.1"`
-   [ ] `config/excel.php` exists (optional)
-   [ ] Routes are registered: `php artisan route:list --path=import`
-   [ ] No errors in: `php artisan about`
-   [ ] Import endpoints respond correctly

---

## 🐛 Troubleshooting

### Error: "Interface ToModel not found"

**Solution:**

```bash
cd /home/umex1887/api-smp
composer require maatwebsite/excel
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

### Error: "Class Excel not found"

**Solution:**

```bash
# Clear config cache
php artisan config:clear

# Publish service provider
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"

# Re-cache
php artisan config:cache
```

### Error: "Permission denied"

**Solution:**

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Error: "Memory limit exceeded"

**Solution:**
Edit `php.ini` or `.htaccess`:

```ini
memory_limit = 512M
upload_max_filesize = 10M
post_max_size = 10M
```

---

## 🔄 Complete Deployment Script

Create a file `deploy-imports.sh`:

```bash
#!/bin/bash

# Navigate to project directory
cd /home/umex1887/api-smp

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan optimize

# Dump autoload
composer dump-autoload -o

# Set permissions
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment completed!"
```

Make it executable and run:

```bash
chmod +x deploy-imports.sh
./deploy-imports.sh
```

---

## 📝 Server Requirements

Ensure your server meets these requirements:

-   PHP >= 8.2
-   Composer installed
-   Extensions:
    -   php-zip
    -   php-xml
    -   php-gd
    -   php-mbstring
    -   php-fileinfo

Check with:

```bash
php -v
php -m | grep -E 'zip|xml|gd|mbstring|fileinfo'
composer --version
```

---

## 🚨 Important Notes

1. **Always backup before deployment:**

    ```bash
    mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
    ```

2. **Test on staging first** if available

3. **Monitor logs during deployment:**

    ```bash
    tail -f storage/logs/laravel.log
    ```

4. **Keep composer.lock in version control** to ensure consistent dependencies

---

## 📞 Support Commands

```bash
# Check Laravel version
php artisan --version

# Check installed packages
composer show

# Check specific package
composer show maatwebsite/excel

# Check autoload
composer dump-autoload -o

# Check environment
php artisan about

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

---

**After completing these steps, your import features should work correctly on the production server!** 🎉
