#!/bin/bash

# Script di verifica pre-deployment per Cohesion Integration
# Questo script verifica che tutti i file necessari siano presenti e configurati

echo "🔍 Verifica Pre-Deployment Cohesion Integration"
echo "=============================================="

# Verifica presenza file essenziali
echo "📁 Verifica presenza file essenziali..."

files=(
    "integrazione-cohesion.php"
    "composer.json"
    "composer.lock"
    "README.md"
    "LICENSE"
    "includes/class-cohesion-integration.php"
    "includes/class-cohesion-authentication.php"
    "includes/class-cohesion-user-manager.php"
    "includes/class-cohesion-admin.php"
    "includes/class-cohesion-config.php"
    "assets/admin.css"
    "languages/integrazione-cohesion.pot"
)

missing_files=()
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✓ $file"
    else
        echo "✗ $file - MANCANTE"
        missing_files+=("$file")
    fi
done

if [ ${#missing_files[@]} -gt 0 ]; then
    echo ""
    echo "❌ File mancanti trovati. Deployment non consigliato."
    echo "   File mancanti: ${missing_files[*]}"
    echo ""
else
    echo ""
    echo "✅ Tutti i file essenziali sono presenti."
    echo ""
fi

# Verifica composer.json
echo "🔧 Verifica composer.json..."
if [ -f "composer.json" ]; then
    if grep -q "andreaval/cohesion2-library" composer.json; then
        echo "✓ Dipendenza Cohesion2 presente"
    else
        echo "✗ Dipendenza Cohesion2 non trovata"
    fi
    
    if grep -q "autoload" composer.json; then
        echo "✓ Configurazione autoload presente"
    else
        echo "✗ Configurazione autoload non trovata"
    fi
else
    echo "✗ composer.json non trovato"
fi

# Verifica .gitignore
echo ""
echo "🚫 Verifica .gitignore..."
if [ -f ".gitignore" ]; then
    if grep -q "vendor/" .gitignore; then
        echo "✓ vendor/ escluso da Git"
    else
        echo "⚠ vendor/ non escluso da Git - aggiungere alla .gitignore"
    fi
    
    if grep -q "node_modules/" .gitignore; then
        echo "✓ node_modules/ escluso da Git"
    else
        echo "⚠ node_modules/ non escluso da Git"
    fi
else
    echo "⚠ .gitignore non trovato - creare per escludere file non necessari"
fi

# Verifica struttura directories
echo ""
echo "📂 Verifica struttura directories..."
dirs=("includes" "assets" "languages")
for dir in "${dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "✓ Directory $dir presente"
    else
        echo "✗ Directory $dir mancante"
    fi
done

# Verifica sintassi PHP
echo ""
echo "🔍 Verifica sintassi PHP..."
php_files=(
    "integrazione-cohesion.php"
    "includes/class-cohesion-integration.php"
    "includes/class-cohesion-authentication.php"
    "includes/class-cohesion-user-manager.php"
    "includes/class-cohesion-admin.php"
    "includes/class-cohesion-config.php"
)

syntax_errors=()
for file in "${php_files[@]}"; do
    if [ -f "$file" ]; then
        if php -l "$file" > /dev/null 2>&1; then
            echo "✓ $file - sintassi OK"
        else
            echo "✗ $file - errore di sintassi"
            syntax_errors+=("$file")
        fi
    fi
done

if [ ${#syntax_errors[@]} -gt 0 ]; then
    echo ""
    echo "❌ Errori di sintassi PHP trovati:"
    for file in "${syntax_errors[@]}"; do
        echo "   - $file"
        php -l "$file"
    done
    echo ""
else
    echo ""
    echo "✅ Tutti i file PHP hanno sintassi corretta."
    echo ""
fi

# Verifica presenza file di debug (da rimuovere)
echo "🔍 Verifica file di debug..."
debug_files=(
    "debug-cohesion.php"
    "quick-setup.php"
    "test-cohesion.php"
)

debug_found=()
for file in "${debug_files[@]}"; do
    if [ -f "$file" ]; then
        echo "⚠ $file - File di debug presente (rimuovere in produzione)"
        debug_found+=("$file")
    fi
done

if [ ${#debug_found[@]} -eq 0 ]; then
    echo "✓ Nessun file di debug trovato"
else
    echo ""
    echo "⚠ File di debug trovati. Rimuovere prima del deployment in produzione:"
    for file in "${debug_found[@]}"; do
        echo "   rm $file"
    done
fi

# Verifica vendor/ non presente (dovrebbe essere escluso)
echo ""
echo "📦 Verifica vendor directory..."
if [ -d "vendor" ]; then
    echo "⚠ Directory vendor/ presente"
    echo "   La directory vendor/ dovrebbe essere rigenerata sul server con 'composer install'"
    echo "   Assicurarsi che sia esclusa da Git (.gitignore)"
else
    echo "✓ Directory vendor/ non presente (corretto per deployment)"
fi

# Checklist finale
echo ""
echo "📋 Checklist Pre-Deployment"
echo "=========================="
echo "□ Tutti i file essenziali sono presenti"
echo "□ Sintassi PHP corretta"
echo "□ File di debug rimossi"
echo "□ .gitignore configurato"
echo "□ composer.json configurato"
echo "□ Documentazione aggiornata"
echo ""
echo "🚀 Comandi per il deployment:"
echo "1. Caricare tutti i file sul server (escludendo vendor/)"
echo "2. Eseguire 'composer install --no-dev --optimize-autoloader'"
echo "3. Attivare il plugin in WordPress"
echo "4. Configurare le impostazioni"
echo "5. Testare il login"
echo ""

# Genera package per deployment
echo "📦 Vuoi generare un package per il deployment? (y/n)"
read -r response
if [[ "$response" == "y" || "$response" == "Y" ]]; then
    echo "Generazione package..."
    
    # Crea directory temporanea
    temp_dir="/tmp/cohesion-deployment-$(date +%s)"
    mkdir -p "$temp_dir/integrazione-cohesion"
    
    # Copia file necessari
    cp -r . "$temp_dir/integrazione-cohesion/"
    
    # Rimuovi file non necessari
    cd "$temp_dir/integrazione-cohesion"
    rm -rf vendor/
    rm -rf node_modules/
    rm -f debug-cohesion.php
    rm -f quick-setup.php
    rm -f test-cohesion.php
    rm -f check-deployment.sh
    rm -rf .git/
    
    # Crea archivio
    cd "$temp_dir"
    zip -r "cohesion-integration-deployment.zip" integrazione-cohesion/
    
    echo "✅ Package creato: $temp_dir/cohesion-integration-deployment.zip"
    echo "   Caricare questo file sul server e estrarlo in wp-content/plugins/"
    
    # Pulizia
    echo "Vuoi pulire i file temporanei? (y/n)"
    read -r cleanup
    if [[ "$cleanup" == "y" || "$cleanup" == "Y" ]]; then
        rm -rf "$temp_dir"
        echo "✅ File temporanei rimossi"
    fi
fi

echo ""
echo "🎉 Verifica completata!"
echo "   Consulta DEPLOYMENT.md per istruzioni dettagliate"
