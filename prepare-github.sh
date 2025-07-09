#!/bin/bash

# ================================================================
# Script di Preparazione per Pubblicazione GitHub
# Plugin WordPress - Integrazione Cohesion
# 
# Sviluppato con GitHub Copilot AI Assistant
# Perfezionato da Ing. Michele Cappannari - Key Soluzioni Informatiche
# ================================================================

echo "🚀 Preparazione Plugin WordPress Integrazione Cohesion per GitHub"
echo "================================================================"
echo ""

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Directory del plugin
PLUGIN_DIR="$(pwd)"
BACKUP_DIR="$PLUGIN_DIR/backup_$(date +%Y%m%d_%H%M%S)"

echo -e "${BLUE}📂 Directory del plugin: $PLUGIN_DIR${NC}"
echo ""

# Funzione per verificare file
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✅ $1${NC}"
        return 0
    else
        echo -e "${RED}❌ $1${NC}"
        return 1
    fi
}

# Funzione per verificare directory
check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✅ $1/${NC}"
        return 0
    else
        echo -e "${RED}❌ $1/${NC}"
        return 1
    fi
}

echo "🔍 Verifica File Essenziali"
echo "================================"

# File principali
FILES_OK=true
check_file "integrazione-cohesion.php" || FILES_OK=false
check_file "LICENSE" || FILES_OK=false
check_file "README_GITHUB.md" || FILES_OK=false
check_file "CHANGELOG.md" || FILES_OK=false
check_file "CONTRIBUTING.md" || FILES_OK=false
check_file "AUTHORS.md" || FILES_OK=false
check_file "SECURITY.md" || FILES_OK=false
check_file ".gitignore" || FILES_OK=false

# Directory
check_dir "includes" || FILES_OK=false
check_dir "lib" || FILES_OK=false
check_dir "assets" || FILES_OK=false
check_dir "languages" || FILES_OK=false

# File specifici
check_file "lib/Cohesion2.php" || FILES_OK=false
check_file "includes/class-cohesion-config.php" || FILES_OK=false
check_file "includes/class-cohesion-authentication.php" || FILES_OK=false

echo ""

if [ "$FILES_OK" = true ]; then
    echo -e "${GREEN}✅ Tutti i file essenziali sono presenti!${NC}"
else
    echo -e "${RED}❌ Alcuni file essenziali mancano. Verifica la struttura.${NC}"
    exit 1
fi

echo ""
echo "📋 Controllo Crediti nei File"
echo "================================"

# Verifica crediti nel file principale
if grep -q "GitHub Copilot" integrazione-cohesion.php && grep -q "Michele Cappannari" integrazione-cohesion.php; then
    echo -e "${GREEN}✅ Crediti presenti in integrazione-cohesion.php${NC}"
else
    echo -e "${YELLOW}⚠️  Crediti da verificare in integrazione-cohesion.php${NC}"
fi

# Verifica crediti nella libreria
if grep -q "GitHub Copilot" lib/Cohesion2.php && grep -q "Michele Cappannari" lib/Cohesion2.php; then
    echo -e "${GREEN}✅ Crediti presenti in lib/Cohesion2.php${NC}"
else
    echo -e "${YELLOW}⚠️  Crediti da verificare in lib/Cohesion2.php${NC}"
fi

echo ""
echo "🔧 Preparazione per GitHub"
echo "================================"

# Creare backup
echo -e "${BLUE}📦 Creazione backup in $BACKUP_DIR${NC}"
mkdir -p "$BACKUP_DIR"
if [ -f "README.md" ]; then
    cp README.md "$BACKUP_DIR/README.md.backup"
    echo -e "${GREEN}✅ Backup README.md esistente${NC}"
fi

# Preparare README per GitHub
if [ -f "README_GITHUB.md" ]; then
    echo -e "${BLUE}📝 Preparazione README.md per GitHub${NC}"
    
    # Se esiste già README.md, fare backup
    if [ -f "README.md" ]; then
        echo -e "${YELLOW}ℹ️  README.md esistente salvato in backup${NC}"
    fi
    
    # Copia il README GitHub come README.md
    cp README_GITHUB.md README.md
    echo -e "${GREEN}✅ README.md preparato per GitHub${NC}"
else
    echo -e "${RED}❌ README_GITHUB.md non trovato${NC}"
    exit 1
fi

echo ""
echo "🧪 Test Configurazione"
echo "================================"

# Eseguire test se disponibili
if [ -f "check-deployment.php" ]; then
    echo -e "${BLUE}🔍 Esecuzione check-deployment.php${NC}"
    php check-deployment.php
fi

echo ""
echo "📊 Statistiche Plugin"
echo "================================"

# Conteggio file
PHP_FILES=$(find . -name "*.php" | wc -l)
MD_FILES=$(find . -name "*.md" | wc -l)
CSS_FILES=$(find . -name "*.css" | wc -l)

echo -e "${BLUE}📂 File PHP: $PHP_FILES${NC}"
echo -e "${BLUE}📄 File Markdown: $MD_FILES${NC}"
echo -e "${BLUE}🎨 File CSS: $CSS_FILES${NC}"

# Dimensione totale
TOTAL_SIZE=$(du -sh . | cut -f1)
echo -e "${BLUE}📏 Dimensione totale: $TOTAL_SIZE${NC}"

echo ""
echo "🔗 Comandi Git Suggeriti"
echo "================================"

echo -e "${YELLOW}# Inizializzare repository (se necessario)${NC}"
echo "git init"
echo ""

echo -e "${YELLOW}# Aggiungere tutti i file${NC}"
echo "git add ."
echo ""

echo -e "${YELLOW}# Commit iniziale${NC}"
echo 'git commit -m "🎉 Initial release v1.0.1 - WordPress Cohesion Integration Plugin

Developed with GitHub Copilot AI Assistant
Refined and tested by Ing. Michele Cappannari - Key Soluzioni Informatiche

Features:
- SPID, CIE, eIDAS authentication via Cohesion
- Configurable Site ID  
- Local Cohesion2.php library (no Composer)
- Complete admin interface
- Comprehensive testing suite
- Full documentation"'
echo ""

echo -e "${YELLOW}# Aggiungere remote GitHub${NC}"
echo "git remote add origin https://github.com/keysoluzioni/integrazione-cohesion-wordpress.git"
echo ""

echo -e "${YELLOW}# Push su GitHub${NC}"
echo "git branch -M main"
echo "git push -u origin main"
echo ""

echo -e "${YELLOW}# Creare tag per release${NC}"
echo 'git tag -a v1.0.1 -m "Release v1.0.1 - Stable WordPress Cohesion Integration"'
echo "git push origin v1.0.1"

echo ""
echo "🎯 Topic Suggeriti per GitHub"
echo "================================"
echo "wordpress, spid, cie, eidas, cohesion, regione-marche, authentication, php, saml"

echo ""
echo -e "${GREEN}🚀 Plugin pronto per la pubblicazione su GitHub!${NC}"
echo ""
echo -e "${BLUE}📋 Checklist finale:${NC}"
echo "1. ✅ Verificare che tutti i file siano corretti"
echo "2. ✅ Eseguire i comandi Git sopra indicati"  
echo "3. ✅ Configurare repository su GitHub con topic"
echo "4. ✅ Abilitare Issues per supporto community"
echo "5. ✅ Creare release notes su GitHub"
echo "6. ✅ Promuovere nella community WordPress"
echo ""
echo -e "${GREEN}Developed with ❤️ by GitHub Copilot AI Assistant & Ing. Michele Cappannari${NC}"
echo -e "${GREEN}Key Soluzioni Informatiche - https://keysoluzioni.it${NC}"
