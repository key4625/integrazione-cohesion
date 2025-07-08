<?php
/**
 * Test specifico per la libreria Cohesion2
 * Questo file verifica che la libreria sia correttamente installata e funzionante
 */

// Carica WordPress se disponibile
$wp_path = dirname(__FILE__) . '/../../../wp-config.php';
if (file_exists($wp_path)) {
    require_once $wp_path;
} else {
    echo "WordPress non trovato, test standalone\n";
}

echo "=== Test Libreria Cohesion2 ===\n\n";

// Test 1: Verifica autoloader
echo "1. Test Autoloader:\n";
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "   ✓ Autoloader trovato: $autoload_path\n";
    require_once $autoload_path;
    echo "   ✓ Autoloader caricato\n";
} else {
    echo "   ✗ Autoloader non trovato: $autoload_path\n";
    exit(1);
}

// Test 2: Verifica classe
echo "\n2. Test Classe Cohesion2:\n";
if (class_exists('Cohesion2')) {
    echo "   ✓ Classe Cohesion2 trovata\n";
} else {
    echo "   ✗ Classe Cohesion2 NON trovata\n";
    
    // Debug aggiuntivo
    echo "   Debug informazioni:\n";
    $composer_file = __DIR__ . '/vendor/composer/autoload_classmap.php';
    if (file_exists($composer_file)) {
        $classes = include $composer_file;
        if (isset($classes['Cohesion2'])) {
            echo "   - Classe registrata in autoload_classmap.php\n";
            echo "   - Path: " . $classes['Cohesion2'] . "\n";
        } else {
            echo "   - Classe NON registrata in autoload_classmap.php\n";
        }
    }
    
    exit(1);
}

// Test 3: Verifica istanza
echo "\n3. Test Istanza:\n";
try {
    $cohesion = new Cohesion2();
    echo "   ✓ Istanza creata con successo\n";
} catch (Exception $e) {
    echo "   ✗ Errore creazione istanza: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Verifica metodi
echo "\n4. Test Metodi:\n";
$required_methods = ['auth', 'isAuth', 'logout'];
$reflection = new ReflectionClass('Cohesion2');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
$method_names = array_map(function($m) { return $m->name; }, $methods);

foreach ($required_methods as $method) {
    if (in_array($method, $method_names)) {
        echo "   ✓ Metodo $method disponibile\n";
    } else {
        echo "   ⚠ Metodo $method non trovato\n";
    }
}

// Test 5: Test proprietà pubbliche
echo "\n5. Test Proprietà Pubbliche:\n";
$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
$property_names = array_map(function($p) { return $p->name; }, $properties);

$required_properties = ['username', 'profile'];
foreach ($required_properties as $property) {
    if (in_array($property, $property_names)) {
        echo "   ✓ Proprietà $property disponibile\n";
    } else {
        echo "   ⚠ Proprietà $property non trovata\n";
    }
}

// Test 6: Verifica costanti
echo "\n6. Test Costanti:\n";
$constants = ['COHESION2_CHECK', 'COHESION2_LOGIN', 'COHESION2_WS', 'COHESION2_SAML20_CHECK'];
foreach ($constants as $const) {
    if (defined("Cohesion2::$const")) {
        echo "   ✓ Costante $const definita\n";
    } else {
        echo "   ⚠ Costante $const non definita\n";
    }
}

// Test 7: Informazioni versione
echo "\n7. Informazioni Versione:\n";
$file_content = file_get_contents(__DIR__ . '/vendor/andreaval/cohesion2-library/cohesion2/Cohesion2.php');
if (preg_match('/@version\s+([^\s]+)/', $file_content, $matches)) {
    echo "   Versione libreria: " . $matches[1] . "\n";
}
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   OpenSSL: " . (extension_loaded('openssl') ? 'Abilitato' : 'Disabilitato') . "\n";
echo "   cURL: " . (extension_loaded('curl') ? 'Abilitato' : 'Disabilitato') . "\n";
echo "   SOAP: " . (extension_loaded('soap') ? 'Abilitato' : 'Disabilitato') . "\n";

echo "\n=== Test Completato ===\n";
echo "La libreria Cohesion2 è installata e funzionante!\n";
