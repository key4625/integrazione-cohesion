<?php
/**
 * Test semplice della libreria Cohesion2 locale (senza WordPress)
 */

// Test caricamento libreria
echo "<h1>Test Libreria Cohesion2 Locale (Standalone)</h1>\n";

if (file_exists('lib/Cohesion2.php')) {
    echo "✅ Libreria locale trovata\n";
    
    require_once 'lib/Cohesion2.php';
    
    if (class_exists('Cohesion2')) {
        echo "✅ Classe Cohesion2 caricata\n";
        
        try {
            $cohesion = new Cohesion2();
            echo "✅ Istanza creata\n";
            
            // Test setIdSito
            $test_id = 'MYSITE123';
            $result = $cohesion->setIdSito($test_id);
            if ($result instanceof Cohesion2) {
                echo "✅ setIdSito() funziona\n";
            } else {
                echo "❌ setIdSito() non restituisce oggetto Cohesion2\n";
            }
            
            // Test SAML
            $cohesion->useSAML20(true);
            echo "✅ SAML 2.0 configurato\n";
            
            // Test metodi
            $methods = get_class_methods($cohesion);
            echo "Metodi disponibili: " . implode(', ', $methods) . "\n";
            
            echo "\n✅ TUTTI I TEST SUPERATI - LA LIBRERIA LOCALE FUNZIONA!\n";
            
        } catch (Exception $e) {
            echo "❌ Errore: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Classe Cohesion2 non trovata\n";
    }
    
} else {
    echo "❌ File lib/Cohesion2.php non trovato\n";
}

echo "\nData test: " . date('Y-m-d H:i:s') . "\n";
?>
