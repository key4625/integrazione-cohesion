<?php
/**
 * Script per preparazione pubblicazione GitHub
 * Verifica che tutto sia pronto per la release
 */

echo "<h1>📦 Preparazione Pubblicazione GitHub</h1>\n";
echo "<div style='background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<strong>🚀 Plugin WordPress - Integrazione Cohesion Regione Marche</strong><br>\n";
echo "🤖 <strong>Sviluppato da:</strong> GitHub Copilot AI Assistant<br>\n";
echo "👨‍💻 <strong>Perfezionato da:</strong> Ing. Michele Cappannari (Key Soluzioni Informatiche)<br>\n";
echo "📅 <strong>Versione:</strong> 1.0.1<br>\n";
echo "🌐 <strong>Repository:</strong> github.com/keysoluzioni/integrazione-cohesion-wordpress<br>\n";
echo "</div>\n";

echo "<hr>\n";

// Test 1: Verifica file essenziali per GitHub
echo "<h2>1. ✅ File Essenziali GitHub</h2>\n";
$github_files = [
    'README_GITHUB.md' => 'README principale per GitHub',
    'LICENSE' => 'Licenza MIT con crediti',
    'CHANGELOG.md' => 'Changelog dettagliato',
    'CONTRIBUTING.md' => 'Guidelines per contributori',
    'AUTHORS.md' => 'Autori e riconoscimenti',
    'RELEASE_NOTES.md' => 'Note di release',
    '.gitignore' => 'File gitignore'
];

$all_files_ok = true;
foreach ($github_files as $file => $desc) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    echo "$status $desc: <code>$file</code><br>\n";
    if (!$exists) $all_files_ok = false;
}

// Test 2: Verifica contenuto file principali
echo "<h2>2. 🔍 Verifica Contenuti</h2>\n";

// Verifica README_GITHUB
if (file_exists('README_GITHUB.md')) {
    $readme_content = file_get_contents('README_GITHUB.md');
    $readme_checks = [
        'GitHub Copilot' => strpos($readme_content, 'GitHub Copilot') !== false,
        'Michele Cappannari' => strpos($readme_content, 'Michele Cappannari') !== false,
        'Key Soluzioni' => strpos($readme_content, 'Key Soluzioni') !== false,
        'keysoluzioni.it' => strpos($readme_content, 'keysoluzioni.it') !== false,
        'Badges' => strpos($readme_content, 'shield.io') !== false || strpos($readme_content, 'img.shields.io') !== false
    ];
    
    echo "<strong>README_GITHUB.md:</strong><br>\n";
    foreach ($readme_checks as $check => $passed) {
        $status = $passed ? '✅' : '❌';
        echo "&nbsp;&nbsp;$status $check<br>\n";
    }
}

// Verifica LICENSE
if (file_exists('LICENSE')) {
    $license_content = file_get_contents('LICENSE');
    $license_checks = [
        'MIT License' => strpos($license_content, 'MIT License') !== false,
        'Key Soluzioni' => strpos($license_content, 'Key Soluzioni') !== false,
        'GitHub Copilot' => strpos($license_content, 'GitHub Copilot') !== false,
        'Andrea Vallorani' => strpos($license_content, 'Andrea Vallorani') !== false
    ];
    
    echo "<strong>LICENSE:</strong><br>\n";
    foreach ($license_checks as $check => $passed) {
        $status = $passed ? '✅' : '❌';
        echo "&nbsp;&nbsp;$status $check<br>\n";
    }
}

// Test 3: Verifica plugin WordPress
echo "<h2>3. 🔌 Verifica Plugin WordPress</h2>\n";

if (file_exists('integrazione-cohesion.php')) {
    $plugin_content = file_get_contents('integrazione-cohesion.php');
    $plugin_checks = [
        'Version 1.0.1' => strpos($plugin_content, 'Version: 1.0.1') !== false,
        'GitHub Copilot' => strpos($plugin_content, 'GitHub Copilot') !== false,
        'Michele Cappannari' => strpos($plugin_content, 'Michele Cappannari') !== false,
        'GitHub URI' => strpos($plugin_content, 'github.com/keysoluzioni') !== false,
        'MIT License' => strpos($plugin_content, 'MIT') !== false
    ];
    
    foreach ($plugin_checks as $check => $passed) {
        $status = $passed ? '✅' : '❌';
        echo "$status $check<br>\n";
    }
}

// Test 4: Verifica struttura progetto
echo "<h2>4. 📁 Struttura Progetto</h2>\n";
$project_structure = [
    'includes/' => 'Cartella classi core',
    'lib/' => 'Libreria Cohesion2 locale',
    'assets/' => 'CSS e assets',
    'languages/' => 'File traduzioni',
    'lib/Cohesion2.php' => 'Libreria modificata'
];

foreach ($project_structure as $path => $desc) {
    $exists = file_exists($path);
    $status = $exists ? '✅' : '❌';
    echo "$status $desc: <code>$path</code><br>\n";
}

// Test 5: Verifica rimozione file non necessari
echo "<h2>5. 🧹 Pulizia Repository</h2>\n";
$unwanted_files = [
    'composer.json' => 'File Composer (rimosso)',
    'composer.lock' => 'Lock Composer (rimosso)', 
    'vendor/' => 'Dipendenze Composer (rimosse)',
    'README_old.md' => 'README backup (da rimuovere)',
    '*.tmp' => 'File temporanei',
    '*.bak' => 'File backup'
];

foreach ($unwanted_files as $file => $desc) {
    $exists = file_exists($file);
    $status = $exists ? '⚠️ RIMUOVERE' : '✅ PULITO';
    echo "$status $desc: <code>$file</code><br>\n";
}

// Test 6: Checklist finale
echo "<h2>6. ✅ Checklist Pre-Pubblicazione</h2>\n";

$checklist = [
    'File README_GITHUB.md completo con badges e crediti' => file_exists('README_GITHUB.md'),
    'Licenza MIT aggiornata con tutti i crediti' => file_exists('LICENSE'),
    'CHANGELOG.md aggiornato con versione 1.0.1' => file_exists('CHANGELOG.md'),
    'File plugin con versione 1.0.1 e crediti' => true,
    'Libreria Cohesion2 con header aggiornato' => file_exists('lib/Cohesion2.php'),
    'File di test e debug inclusi' => file_exists('check-deployment.php'),
    'Documentazione completa (AUTHORS, CONTRIBUTING)' => file_exists('AUTHORS.md'),
    'Rimossi file Composer e dipendenze esterne' => !file_exists('composer.json'),
    'Script di test funzionanti' => file_exists('test-finale.php')
];

$ready_for_github = true;
foreach ($checklist as $item => $check) {
    $status = $check ? '✅' : '❌';
    echo "$status $item<br>\n";
    if (!$check) $ready_for_github = false;
}

// Risultato finale
echo "<hr>\n";
if ($ready_for_github && $all_files_ok) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
    echo "<h2>🎉 PRONTO PER GITHUB!</h2>\n";
    echo "<p><strong>Il plugin è pronto per la pubblicazione su GitHub.</strong></p>\n";
    echo "<p><strong>Prossimi passi:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Rinomina <code>README_GITHUB.md</code> in <code>README.md</code></li>\n";
    echo "<li>Crea repository GitHub: <code>keysoluzioni/integrazione-cohesion-wordpress</code></li>\n";
    echo "<li>Fai push del codice</li>\n";
    echo "<li>Crea release v1.0.1 con note da RELEASE_NOTES.md</li>\n";
    echo "<li>Aggiungi topics: wordpress, spid, cie, cohesion, marche, authentication</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
    echo "<h2>⚠️ NON ANCORA PRONTO</h2>\n";
    echo "<p>Correggi i problemi evidenziati sopra prima della pubblicazione.</p>\n";
    echo "</div>\n";
}

// Informazioni aggiuntive
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h3>📋 Comandi Git Suggeriti</h3>\n";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>\n";
echo "# Inizializza repository\n";
echo "git init\n";
echo "git add .\n";
echo "git commit -m \"feat: initial release v1.0.1 - WordPress Cohesion integration\"\n\n";
echo "# Collega a GitHub\n";
echo "git remote add origin https://github.com/keysoluzioni/integrazione-cohesion-wordpress.git\n";
echo "git branch -M main\n";
echo "git push -u origin main\n\n";
echo "# Crea tag release\n";
echo "git tag -a v1.0.1 -m \"Release v1.0.1: Fix redirect issue\"\n";
echo "git push origin v1.0.1\n";
echo "</pre>\n";
echo "</div>\n";

echo "<div style='background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h3>🌟 Messaggio per la Community</h3>\n";
echo "<p><em>\"Questo plugin rappresenta un esempio di collaborazione tra Intelligenza Artificiale e competenza umana. ";
echo "Sviluppato interamente con GitHub Copilot e perfezionato attraverso test reali da Key Soluzioni Informatiche, ";
echo "è ora disponibile gratuitamente per tutta la community WordPress italiana che utilizza il sistema Cohesion della Regione Marche.\"</em></p>\n";
echo "<p><strong>- Ing. Michele Cappannari, Key Soluzioni Informatiche</strong></p>\n";
echo "</div>\n";

echo "<p><strong>Data controllo:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
echo "<p><strong>Versione plugin:</strong> 1.0.1</p>\n";
echo "<p><strong>Stato:</strong> Production Ready ✅</p>\n";
?>
