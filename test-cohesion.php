<?php
/**
 * Test script per verificare che la libreria Cohesion2 sia caricata correttamente
 */

// Include autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader caricato con successo\n";
} else {
    echo "❌ Autoloader non trovato\n";
    exit(1);
}

// Test class existence
if (class_exists('Cohesion2')) {
    echo "✅ Classe Cohesion2 trovata\n";
    
    // Test instantiation
    try {
        $cohesion = new Cohesion2('test');
        echo "✅ Istanza Cohesion2 creata con successo\n";
        echo "📋 Versione libreria: " . (defined('Cohesion2::VERSION') ? Cohesion2::VERSION : 'non specificata') . "\n";
    } catch (Exception $e) {
        echo "❌ Errore nell'istanziazione: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Classe Cohesion2 non trovata\n";
}

echo "\n🏁 Test completato\n";
?>
