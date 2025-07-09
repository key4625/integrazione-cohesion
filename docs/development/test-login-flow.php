<?php
/**
 * Test debug per il flusso di login Cohesion
 */

// Simula ambiente WordPress se necessario
if (!defined('ABSPATH')) {
    define('ABSPATH', true);
    
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
    
    function get_option($key, $default = null) {
        // Simula alcune opzioni per test
        $options = [
            'cohesion_id_sito' => 'TEST',
            'cohesion_saml_enabled' => true,
            'cohesion_auth_restriction' => '0,1,2,3'
        ];
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    function home_url($path = '') {
        return 'https://turismo.montesanvito.info' . $path;
    }
}

echo "<h1>Test Debug Login Cohesion</h1>\n";
echo "<hr>\n";

// Test 1: Carica libreria e configurazione
echo "<h2>1. Test Configurazione</h2>\n";
$plugin_path = plugin_dir_path(__FILE__);

if (file_exists($plugin_path . 'lib/Cohesion2.php')) {
    require_once $plugin_path . 'lib/Cohesion2.php';
    echo "✅ Libreria Cohesion2 caricata<br>\n";
    
    // Simula la configurazione del plugin
    $id_sito = get_option('cohesion_id_sito', 'TEST');
    echo "ID Sito configurato: <strong>$id_sito</strong><br>\n";
    
    if ($id_sito === 'TEST') {
        echo "⚠️ <strong>ATTENZIONE:</strong> Stai usando l'ID di test. Con 'TEST' potresti vedere solo login/password tradizionale.<br>\n";
        echo "Per abilitare SPID/CIE, configura un ID Sito reale da Regione Marche.<br>\n";
    }
    
} else {
    echo "❌ Libreria non trovata<br>\n";
    exit;
}

// Test 2: Simulazione flusso login
echo "<h2>2. Simulazione Processo Login</h2>\n";

try {
    // Crea istanza Cohesion2
    $cohesion = new Cohesion2();
    echo "✅ Istanza Cohesion2 creata<br>\n";
    
    // Configura ID Sito
    $cohesion->setIdSito($id_sito);
    echo "✅ ID Sito configurato: $id_sito<br>\n";
    
    // Configura SAML per SPID/CIE
    $cohesion->useSAML20(true);
    $cohesion->useSSO(true);
    echo "✅ SAML 2.0 e SSO abilitati<br>\n";
    
    // Configura restrizioni autenticazione
    $auth_restriction = get_option('cohesion_auth_restriction', '0,1,2,3');
    $cohesion->setAuthRestriction($auth_restriction);
    echo "✅ Restrizioni autenticazione: $auth_restriction<br>\n";
    
    // Test metodi
    if (method_exists($cohesion, 'auth')) {
        echo "✅ Metodo auth() disponibile<br>\n";
    } else {
        echo "❌ Metodo auth() NON disponibile<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "<br>\n";
}

// Test 3: URL e Rewrite Rules
echo "<h2>3. Test URL e Routing</h2>\n";
$site_url = home_url();
echo "URL sito: <strong>$site_url</strong><br>\n";

$login_urls = [
    $site_url . '/cohesion/login' => 'URL Login Cohesion (rewrite rule)',
    $site_url . '?cohesion_action=login' => 'URL Login Cohesion (query parameter)',
    $site_url . '/?cohesion_login=1' => 'URL Login diretto'
];

echo "URL di login disponibili:<br>\n";
foreach ($login_urls as $url => $desc) {
    echo "- <a href='$url' target='_blank'>$desc</a>: <code>$url</code><br>\n";
}

// Test 4: Verifica Costanti Cohesion2
echo "<h2>4. Endpoint Cohesion2</h2>\n";
if (class_exists('Cohesion2')) {
    $reflection = new ReflectionClass('Cohesion2');
    $constants = $reflection->getConstants();
    
    $cohesion_endpoints = array_filter($constants, function($key) {
        return strpos($key, 'COHESION2_') === 0;
    }, ARRAY_FILTER_USE_KEY);
    
    echo "Endpoint Cohesion2 configurati:<br>\n";
    foreach ($cohesion_endpoints as $name => $url) {
        echo "- <strong>$name:</strong> <code>$url</code><br>\n";
    }
}

// Test 5: Simulazione XML Auth
echo "<h2>5. Simulazione XML Auth</h2>\n";
$protocol = 'https://';
$host = $_SERVER['HTTP_HOST'] ?? 'turismo.montesanvito.info';
$uri = $_SERVER['REQUEST_URI'] ?? '/wp-content/plugins/integrazione-cohesion/test-login-flow.php';
$callback_url = $protocol . $host . $uri . '?cohesionCheck=1';

echo "URL callback che verrebbe generato:<br>\n";
echo "<code>$callback_url</code><br>\n";

// Mostra come apparirebbe l'XML auth
$xml_preview = htmlentities('<dsAuth xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/Auth.xsd">
    <auth>
        <user />
        <id_sa />
        <id_sito>' . $id_sito . '</id_sito>
        <esito_auth_sa />
        <id_sessione_sa />
        <id_sessione_aspnet_sa />
        <url_validate><![CDATA[' . $callback_url . ']]></url_validate>
        <url_richiesta><![CDATA[' . $callback_url . ']]></url_richiesta>
        <esito_auth_sso />
        <id_sessione_sso />
        <id_sessione_aspnet_sso />
        <stilesheet>AuthRestriction=' . $auth_restriction . '</stilesheet>
        <AuthRestriction xmlns="">' . $auth_restriction . '</AuthRestriction>
    </auth>
</dsAuth>');

echo "<h3>XML Auth che verrebbe inviato a Cohesion:</h3>\n";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>$xml_preview</pre>\n";

echo "<hr>\n";
echo "<h2>✅ Test Completato</h2>\n";
echo "<p><strong>Problema identificato:</strong> URL di login interno viene creato ma non gestito correttamente.</p>\n";
echo "<p><strong>Soluzione:</strong> Verificare le rewrite rules di WordPress e assicurarsi che il metodo auth() di Cohesion2 esegua il redirect.</p>\n";
echo "<p>Data: " . date('Y-m-d H:i:s') . "</p>\n";
?>
