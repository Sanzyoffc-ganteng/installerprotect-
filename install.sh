#!/bin/bash

# SANZY PROTECT - Auto Installer
# Author: @SANZY_OFFICIAL

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}╔════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   🛡️  SANZY PROTECT INSTALLER  🛡️   ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════╝${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root (sudo bash install.sh)${NC}"
    exit 1
fi

PANEL_PATH="/var/www/pterodactyl"

# Check if panel exists
if [ ! -d "$PANEL_PATH" ]; then
    echo -e "${RED}Error: Pterodactyl panel not found at $PANEL_PATH${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 Installing SANZY PROTECT...${NC}"

# Create directories
mkdir -p $PANEL_PATH/app/Http/Middleware
mkdir -p $PANEL_PATH/app/Http/Controllers/Admin
mkdir -p $PANEL_PATH/app/Providers
mkdir -p $PANEL_PATH/resources/views/admin
mkdir -p $PANEL_PATH/routes
mkdir -p $PANEL_PATH/config

# Copy files
echo -e "${GREEN}✓${NC} Copying middleware..."
cp app/Http/Middleware/SanzyProtect.php $PANEL_PATH/app/Http/Middleware/

echo -e "${GREEN}✓${NC} Copying controller..."
cp app/Http/Controllers/Admin/SanzyProtectController.php $PANEL_PATH/app/Http/Controllers/Admin/

echo -e "${GREEN}✓${NC} Copying service provider..."
cp app/Providers/SanzyProtectServiceProvider.php $PANEL_PATH/app/Providers/

echo -e "${GREEN}✓${NC} Copying view..."
cp resources/views/admin/sanzy-protect.blade.php $PANEL_PATH/resources/views/admin/

echo -e "${GREEN}✓${NC} Copying routes..."
cp routes/sanzy-protect.php $PANEL_PATH/routes/

echo -e "${GREEN}✓${NC} Copying config..."
cp config/sanzy-protect.php $PANEL_PATH/config/

# Set permissions
echo -e "${YELLOW}🔧 Setting permissions...${NC}"
chown -R www-data:www-data $PANEL_PATH/app/Http/Middleware/SanzyProtect.php
chown -R www-data:www-data $PANEL_PATH/app/Http/Controllers/Admin/SanzyProtectController.php
chown -R www-data:www-data $PANEL_PATH/app/Providers/SanzyProtectServiceProvider.php
chown -R www-data:www-data $PANEL_PATH/resources/views/admin/sanzy-protect.blade.php
chown -R www-data:www-data $PANEL_PATH/routes/sanzy-protect.php
chown -R www-data:www-data $PANEL_PATH/config/sanzy-protect.php

# Register service provider
echo -e "${YELLOW}📝 Registering service provider...${NC}"
if ! grep -q "SanzyProtectServiceProvider" $PANEL_PATH/config/app.php; then
    sed -i "/App\\\Providers\\\RouteServiceProvider::class/a\        App\\\Providers\\\SanzyProtectServiceProvider::class," $PANEL_PATH/config/app.php
    echo -e "${GREEN}✓${NC} Service provider registered"
else
    echo -e "${YELLOW}!${NC} Service provider already registered"
fi

# Register middleware
if ! grep -q "SanzyProtect" $PANEL_PATH/app/Http/Kernel.php; then
    sed -i "/protected \$routeMiddleware = \[/a\        'sanzy-protect' => \\\\App\\\\Http\\\\Middleware\\\\SanzyProtect::class," $PANEL_PATH/app/Http/Kernel.php
    echo -e "${GREEN}✓${NC} Middleware registered"
else
    echo -e "${YELLOW}!${NC} Middleware already registered"
fi

# Create whitelist file
echo -e "${YELLOW}🔐 Creating whitelist file...${NC}"
echo '{"admins": [1]}' > $PANEL_PATH/storage/sanzy_whitelist.json
chown www-data:www-data $PANEL_PATH/storage/sanzy_whitelist.json
echo -e "${GREEN}✓${NC} Whitelist file created (Admin #1)"

# Clear cache
echo -e "${YELLOW}🗑️  Clearing cache...${NC}"
cd $PANEL_PATH
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo -e "${GREEN}✓${NC} Cache cleared"

echo ""
echo -e "${GREEN}╔════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅  INSTALLATION COMPLETE!   ✅   ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}🛡️  SANZY PROTECT has been installed!${NC}"
echo -e "${YELLOW}📌 Menu '🛡️ SANZY PROTECT' akan muncul di sidebar admin${NC}"
echo -e "${YELLOW}📌 Default protection: Level 9/9${NC}"
echo -e "${YELLOW}📌 Main admin protected: ID #1${NC}"
echo ""
echo -e "${YELLOW}To customize, edit:${NC}"
echo -e "  - Config: ${GREEN}$PANEL_PATH/config/sanzy-protect.php${NC}"
echo -e "  - Whitelist: ${GREEN}$PANEL_PATH/storage/sanzy_whitelist.json${NC}"
echo ""
