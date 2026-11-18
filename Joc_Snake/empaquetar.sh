#!/bin/sh
# Genera dist/snake_classic.zip per pujar-lo a "Gestió jocs" (només el que es serveix:
# sense tests, docs ni SQL). index.html queda a l'arrel del ZIP.
set -e
cd "$(dirname "$0")"
mkdir -p dist
rm -f dist/snake_classic.zip
zip -q dist/snake_classic.zip index.html index.css conf.js main.js motor.js snake-ui.js api-jowron.js
echo "Creat dist/snake_classic.zip"
