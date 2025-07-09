<?php
/**
 * Verifica veloce configurazione plugin per server remoto
 */

// Controlla se siamo in ambiente WordPress
if (!defined('ABSPATH')) {
    // Simuliamo WordPress per test locale
    define('ABSPATH', true);
    
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
    
    $plugin_path = plugin_dir_path(__FILE__);
} else {
    // Ambiente WordPress reale
    $plugin_path = plugin_dir_path(__FILE__);
}

echo "<h1>🔍 Verifica Configurazione Plugin Cohesion</h1>\n";
echo "<div style='background: #e8f4fd; border: 1px solid #bee5eb; padding: 10px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<strong>📋 Informazioni Plugin</strong><br>\n";
echo "🤖 <strong>Sviluppato da:</strong> GitHub Copilot AI Assistant<br>\n";
echo "👨‍💻 <strong>Testato e perfezionato da:</strong> Ing. Michele Cappannari (Key Soluzioni Informatiche)<br>\n";
echo "🌐 <strong>Website:</strong> <a href='https://keysoluzioni.it' target='_blank'>keysoluzioni.it</a><br>\n";
echo "📧 <strong>Supporto:</strong> info@keysoluzioni.it<br>\n";
echo "📅 <strong>Versione:</strong> 1.0.1 (9 Luglio 2025)\n";
echo "</div>\n";
echo "<hr>\n";

// Test 1: Verifica file principali
echo "<h2>1. File Principali</h2>\n";
$main_files = [
    'integrazione-cohesion.php' => 'File principale plugin',
    'lib/Cohesion2.php' => 'Libreria Cohesion2 locale',
    'includes/class-cohesion-config.php' => 'Classe configurazione',
    'includes/class-cohesion-authentication.php' => 'Classe autenticazione'
];

foreach ($main_files as $file => $desc) {
    $exists = file_exists($plugin_path . $file);
    $status = $exists ? '✅' : '❌';
    echo "$status $desc: $file<br>\n";
}

// Test 2: Verifica che vendor sia stato rimosso
echo "<h2>2. Verifica Rimozione Composer</h2>\n";
$composer_files = [
    'composer.json' => 'File configurazione Composer',
    'composer.lock' => 'Lock file Composer', 
    'vendor/' => 'Cartella dipendenze Composer'
];

foreach ($composer_files as $file => $desc) {
    $exists = file_exists($plugin_path . $file);
    $status = $exists ? '❌ TROVATO' : '✅ RIMOSSO';
    echo "$status $desc: $file<br>\n";
}

// Test 3: Carica e testa libreria locale
echo "<h2>3. Test Libreria Locale</h2>\n";
$lib_path = $plugin_path . 'lib/Cohesion2.php';
if (file_exists($lib_path)) {
    echo "✅ Caricamento libreria locale...<br>\n";
    
    try {
        require_once $lib_path;
        
        if (class_exists('Cohesion2')) {
            echo "✅ Classe Cohesion2 disponibile<br>\n";
            
            $cohesion = new Cohesion2();
            echo "✅ Istanza creata<br>\n";
            
            // Test metodo setIdSito
            if (method_exists($cohesion, 'setIdSito')) {
                echo "✅ Metodo setIdSito() disponibile<br>\n";
                $cohesion->setIdSito('TEST_' . time());
                echo "✅ ID Sito configurato dinamicamente<br>\n";
            } else {
                echo "❌ Metodo setIdSito() NON disponibile<br>\n";
            }
            
        } else {
            echo "❌ Classe Cohesion2 non trovata<br>\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Errore: " . $e->getMessage() . "<br>\n";
    }
    
} else {
    echo "❌ Libreria locale non trovata<br>\n";
}

// Test 4: Verifica namespace e conflitti
echo "<h2>4. Verifica Namespace</h2>\n";
$lib_content = file_exists($lib_path) ? file_get_contents($lib_path) : '';
if ($lib_content) {
    if (strpos($lib_content, 'namespace') !== false) {
        echo "⚠️ ATTENZIONE: Libreria contiene namespace (potrebbe causare conflitti)<br>\n";
    } else {
        echo "✅ Libreria senza namespace (corretto per versione locale)<br>\n";
    }
    
    if (strpos($lib_content, 'setIdSito') !== false) {
        echo "✅ Metodo setIdSito presente nella libreria<br>\n";
    } else {
        echo "❌ Metodo setIdSito NON presente nella libreria<br>\n";
    }
}

echo "<hr>\n";
echo "<p><strong>Controllo completato!</strong></p>\n";
echo "<p>Data: " . date('Y-m-d H:i:s') . "</p>\n";
?>
