<?php
/**
 * Test configurazione SAML 2.0 per SPID/CIE
 */

// Carica WordPress
require_once dirname(__FILE__) . '/../../../wp-config.php';

echo "=== Test Configurazione SAML 2.0 ===\n\n";

// Test autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Test configurazione
if (class_exists('Cohesion_Config')) {
    $config_instance = new Cohesion_Config();
    $config = $config_instance->get_all_settings();
    
    echo "1. Configurazione Plugin:\n";
    foreach ($config as $key => $value) {
        echo "   $key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    }
    echo "\n";
}

// Test libreria Cohesion2
echo "2. Test Libreria Cohesion2:\n";
try {
    $cohesion = new Cohesion2();
    echo "   ✓ Istanza creata\n";
    
    // Configura SAML 2.0
    $cohesion->useSAML20(true);
    echo "   ✓ SAML 2.0 abilitato\n";
    
    // Test certificati
    $cert_path = __DIR__ . '/vendor/andreaval/cohesion2-library/cohesion2/cert/';
    if (file_exists($cert_path . 'cohesion2.crt.pem') && file_exists($cert_path . 'cohesion2.key.pem')) {
        $cohesion->setCertificate($cert_path . 'cohesion2.crt.pem', $cert_path . 'cohesion2.key.pem');
        echo "   ✓ Certificati configurati\n";
        echo "   - Cert: " . $cert_path . "cohesion2.crt.pem\n";
        echo "   - Key: " . $cert_path . "cohesion2.key.pem\n";
    } else {
        echo "   ✗ Certificati non trovati\n";
    }
    
    // Test SSO
    $cohesion->useSSO(true);
    echo "   ✓ SSO abilitato\n";
    
    // Test restrizioni autenticazione
    $cohesion->setAuthRestriction('0');
    echo "   ✓ Auth restriction impostata a '0' (tutti i metodi)\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore: " . $e->getMessage() . "\n";
}

echo "\n3. Informazioni SAML 2.0:\n";
echo "   - Con SAML 2.0 abilitato, Cohesion dovrebbe mostrare SPID/CIE\n";
echo "   - Senza SAML 2.0, mostra il form tradizionale (user/pass/pin)\n";
echo "   - I certificati sono necessari per la produzione\n";
echo "   - Auth restriction '0' abilita tutti i metodi di autenticazione\n";

echo "\n=== Test Completato ===\n";
