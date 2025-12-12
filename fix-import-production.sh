#!/bin/bash

################################################################################
# Quick Fix Script for Import Feature on Production Server
# Error: Interface "Maatwebsite\Excel\Concerns\ToModel" not found
################################################################################

echo "🔧 Fixing Import Feature on Production Server..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Navigate to project directory
PROJECT_DIR="/home/umex1887/api-smp"

if [ ! -d "$PROJECT_DIR" ]; then
    print_error "Project directory not found: $PROJECT_DIR"
    echo "Please update PROJECT_DIR in this script with the correct path"
    exit 1
fi

cd "$PROJECT_DIR" || exit 1
print_status "Changed to project directory: $PROJECT_DIR"

# Step 1: Check if composer is available
echo ""
echo "📦 Step 1: Checking Composer..."
if ! command -v composer &> /dev/null; then
    print_error "Composer not found. Please install composer first."
    exit 1
fi
print_status "Composer is available: $(composer --version | head -n 1)"

# Step 2: Install maatwebsite/excel package
echo ""
echo "📥 Step 2: Installing Laravel Excel package..."
composer require maatwebsite/excel --no-interaction
if [ $? -eq 0 ]; then
    print_status "Laravel Excel package installed successfully"
else
    print_error "Failed to install Laravel Excel package"
    exit 1
fi

# Step 3: Dump autoload
echo ""
echo "🔄 Step 3: Regenerating autoload files..."
composer dump-autoload -o
if [ $? -eq 0 ]; then
    print_status "Autoload files regenerated"
else
    print_warning "Autoload regeneration had warnings (may still work)"
fi

# Step 4: Publish Excel config (optional)
echo ""
echo "📝 Step 4: Publishing Excel configuration..."
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config --force
print_status "Excel configuration published"

# Step 5: Clear all caches
echo ""
echo "🧹 Step 5: Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_status "All caches cleared"

# Step 6: Optimize application
echo ""
echo "⚡ Step 6: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan optimize
print_status "Application optimized"

# Step 7: Verify installation
echo ""
echo "✅ Step 7: Verifying installation..."

# Check if package is installed
if composer show | grep -q "maatwebsite/excel"; then
    VERSION=$(composer show maatwebsite/excel | grep "versions" | awk '{print $3}')
    print_status "Package installed: maatwebsite/excel $VERSION"
else
    print_error "Package not found in composer show"
fi

# Check if import classes exist
if [ -f "app/Imports/StudentsImport.php" ]; then
    print_status "StudentsImport.php exists"
else
    print_warning "StudentsImport.php not found - please upload it"
fi

if [ -f "app/Imports/ParentsImport.php" ]; then
    print_status "ParentsImport.php exists"
else
    print_warning "ParentsImport.php not found - please upload it"
fi

# Check routes
echo ""
echo "🛣️  Checking import routes..."
php artisan route:list --path=import
if [ $? -eq 0 ]; then
    print_status "Import routes registered successfully"
else
    print_error "Failed to list routes"
fi

# Step 8: Set permissions (if needed)
echo ""
echo "🔐 Step 8: Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null
if [ $? -eq 0 ]; then
    print_status "Permissions set for storage and cache directories"
else
    print_warning "Could not set permissions (may need sudo)"
fi

# Final status
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✓ Fix completed!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Next steps:"
echo "1. Test the import endpoint:"
echo "   curl -X GET https://your-domain.com/api/main/student/import/template"
echo ""
echo "2. Monitor application logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "3. If you still see errors, try:"
echo "   php artisan optimize:clear"
echo "   composer dump-autoload -o"
echo ""

# Check for errors in log
if [ -f "storage/logs/laravel.log" ]; then
    echo "📋 Recent errors in laravel.log:"
    tail -n 20 storage/logs/laravel.log | grep -i "error\|exception" || echo "   No recent errors found"
fi

echo ""
print_status "Script completed successfully! 🎉"
