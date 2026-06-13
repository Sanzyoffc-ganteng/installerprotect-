#!/bin/bash

# Warna Terminal biar keren
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m'

clear
echo -e "${CYAN}===============================================${NC}"
echo -e "${CYAN}      INSTALLER ULTIMATE VISUAL SANZY PROTECT  ${NC}"
echo -e "${CYAN}===============================================${NC}"
sleep 1

# Lokasi File Penting
SIDEBAR="/var/www/pterodactyl/resources/views/layouts/admin.blade.php"
DASHBOARD="/var/www/pterodactyl/resources/views/admin/index.blade.php"

# --- 1. PASANG BADGE MERAH DI SIDEBAR (ATAS MENU) ---
echo -e "${GREEN}[*] Memasang Badge Merah di Sidebar...${NC}"
BANNER_HTML='<li style=\"margin: 10px 15px; list-style: none;\"><div style=\"background-color: #ff0000; color: #ffffff; padding: 10px; text-align: center; border-radius: 6px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border: 2px solid #b30000; box-shadow: 0px 2px 5px rgba(0,0,0,0.2);\"><i class=\"fa fa-shield\"></i> SANZY PROTECT<\/div><\/li>'

# Masukin badge tepat setelah menu sidebar dimulai
sed -i "/<ul class=\"sidebar-menu\" data-widget=\"tree\">/a $BANNER_HTML" $SIDEBAR

# --- 2. PASANG TOMBOL MERAH DI DASHBOARD (LOCKED) ---
echo -e "${GREEN}[*] Memasang Tombol Utama di Dashboard...${NC}"
sed -i '/Support the Project/a <div class="row"><div class="col-xs-12"><button class="btn btn-danger disabled" style="width:100%; margin-top: 15px; font-weight:bold; background-color: #d9534f; border-color: #d43f3a; cursor: not-allowed;"><i class="fa fa-shield"></i> SANZY PROTECT ACTIVE (LOCKED)</button></div></div>' $DASHBOARD

# --- 3. PASANG GEMBOK MERAH & HARD LOCK (MATIKAN KLIK) ---
echo -e "${GREEN}[*] Mengunci Menu & Memasang Gembok Merah...${NC}"
menus=("Settings" "API Configuration" "Databases" "Locations" "Nodes" "Servers" "Users" "Mounts" "Nests")

for menu in "${menus[@]}"; do
    sed -i "s|<span>$menu<\/span>|<span>$menu <i class=\"fa fa-lock\" style=\"font-size: 10px; color: #ff0000; margin-left: 4px;\"><\/i><\/span>|g" $SIDEBAR
done

# Injeksi CSS ke Sidebar biar gak bisa diklik (Hard Lock)
sed -i '/<\/head>/i <style>.sidebar-menu li a { position: relative; } .sidebar-menu li a:has(i.fa-lock) { pointer-events: none !important; opacity: 0.8; cursor: not-allowed !important; }</style>' $SIDEBAR

# --- 4. FINISHING: REFRESH TAMPILAN ---
echo -e "${GREEN}[*] Menyinkronkan Semua Fitur...${NC}"
cd /var/www/pterodactyl && php artisan view:clear

echo -e "${RED}===============================================${NC}"
echo -e "${RED}      SANZY PROTECT LEVEL ULTIMATE AKTIF!      ${NC}"
echo -e "${RED}    DASHBOARD, SIDEBAR, & MENU TELAH DI-LOCK   ${NC}"
echo -e "${RED}===============================================${NC}"
