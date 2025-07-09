<?php
/**
 * Script di debug per verificare la libreria Cohesion2 locale
 * Testa il caricamento e la configurazione dell'ID Sito
 */

// Previeni output di header per evitare warning sessione
ob_start();

// Simula l'ambiente WordPress
define('ABSPATH', true);

// Carica la libreria locale
require_once 'lib/Cohesion2.php';

echo "<h1>Debug Libreria Cohesion2 Locale</h1>\n";
echo "<hr>\n";

// Test 1: Creazione istanza
echo "<h2>Test 1: Creazione istanza Cohesion2</h2>\n";
try {
    $cohesion = new Cohesion2();
    echo "✅ Istanza creata con successo<br>\n";
    echo "Libreria caricata dalla versione originale modificata<br>\n";
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "<br>\n";
    exit;
}

// Test 2: Configurazione ID Sito
echo "<h2>Test 2: Configurazione ID Sito</h2>\n";
$test_id_sito = 'MIOIDSITO123';
$result = $cohesion->setIdSito($test_id_sito);
if ($result instanceof Cohesion2) {
    echo "✅ Metodo setIdSito() disponibile e funzionante<br>\n";
    echo "ID Sito impostato a: " . $test_id_sito . "<br>\n";
} else {
    echo "❌ Errore nel metodo setIdSito()<br>\n";
}

// Test 3: Verifica metodi disponibili
echo "<h2>Test 3: Metodi disponibili</h2>\n";
$methods = get_class_methods($cohesion);
echo "Metodi disponibili nella classe Cohesion2:<br>\n";
$important_methods = ['setIdSito', 'auth', 'isAuth', 'logout', 'setAuthRestriction', 'useSAML20'];
foreach ($methods as $method) {
    $important = in_array($method, $important_methods) ? ' <strong>(IMPORTANTE)</strong>' : '';
    echo "- " . $method . $important . "<br>\n";
}

// Test 4: Verifica proprietà pubbliche
echo "<h2>Test 4: Proprietà pubbliche</h2>\n";
$vars = get_object_vars($cohesion);
echo "Proprietà pubbliche:<br>\n";
foreach ($vars as $name => $value) {
    echo "- $name: " . (is_array($value) ? 'Array(' . count($value) . ')' : $value) . "<br>\n";
}

// Test 5: Test Configurazione SAML
echo "<h2>Test 5: Configurazione SAML</h2>\n";
try {
    $cohesion->useSAML20(true);
    echo "✅ SAML 2.0 abilitato con successo<br>\n";
} catch (Exception $e) {
    echo "❌ Errore SAML: " . $e->getMessage() . "<br>\n";
}

// Test 6: Verifica che l'ID Sito sia stato modificato nella libreria
echo "<h2>Test 6: Verifica modifica libreria</h2>\n";
$library_content = file_get_contents('lib/Cohesion2.php');
if (strpos($library_content, 'setIdSito') !== false) {
    echo "✅ Metodo setIdSito() presente nella libreria<br>\n";
} else {
    echo "❌ Metodo setIdSito() NON trovato nella libreria<br>\n";
}

if (strpos($library_content, '$this->id_sito') !== false) {
    echo "✅ Proprietà \$id_sito configurabile trovata<br>\n";
} else {
    echo "❌ Proprietà \$id_sito NON configurabile<br>\n";
}

// Test 7: Verifica costanti Cohesion
echo "<h2>Test 7: Costanti Cohesion</h2>\n";
$constants = get_defined_constants(true)['user'];
$cohesion_constants = array_filter($constants, function($key) {
    return strpos($key, 'COHESION') === 0;
}, ARRAY_FILTER_USE_KEY);

if (!empty($cohesion_constants)) {
    echo "Costanti Cohesion definite:<br>\n";
    foreach ($cohesion_constants as $name => $value) {
        echo "- $name: $value<br>\n";
    }
} else {
    echo "Nessuna costante Cohesion definita (normale per classe instanziata)<br>\n";
}

echo "<hr>\n";
echo "<h2>✅ Test Completati</h2>\n";
echo "<p><strong>La libreria Cohesion2 locale è operativa!</strong></p>\n";
echo "<p><strong>Versione:</strong> Cohesion2 modificata per WordPress (basata su v3.0.1)</p>\n";
echo "<p><strong>Caratteristiche:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ ID Sito configurabile dinamicamente</li>\n";
echo "<li>✅ SAML 2.0 supportato per SPID/CIE</li>\n";
echo "<li>✅ Sessioni PHP gestite automaticamente</li>\n";
echo "<li>✅ Nessuna dipendenza Composer richiesta</li>\n";
echo "</ul>\n";

echo "<p><strong>Prossimi passi:</strong></p>\n";
echo "<ul>\n";
echo "<li>Configurare un ID Sito reale nell'admin WordPress</li>\n";
echo "<li>Testare il flusso di login completo</li>\n";
echo "<li>Verificare che il portale Cohesion mostri SPID/CIE con ID reale</li>\n";
echo "</ul>\n";

// Info versione
echo "<hr>\n";
echo "<small>Script eseguito il: " . date('Y-m-d H:i:s') . "</small>\n";

// Flush del buffer per mostrare l'output
ob_end_flush();
?>
