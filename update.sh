#!/bin/bash
# shoppinglist gyorsfrissítő script VPS-re

echo "Frissítés Git-ről..."
git pull --rebase

echo "Függőségek telepítése..."
npm install

echo "Production build készítése..."
npm run build

echo "Backend újraindítása..."
sudo systemctl restart shoppinglist

echo "Frissítés kész! A shoppinglist fut."
