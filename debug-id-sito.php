<?php
/**
 * Debug del plugin Cohesion - Test ID Sito
 */

// Carica WordPress
require_once('../../../wp-load.php');

// Carica il plugin
require_once('integrazione-cohesion.php');

echo "<h1>Debug Cohesion - ID Sito</h1>";

// Testa la configurazione
$config = new Cohesion_Config();
$settings = $config->get_all_settings();

echo "<h2>Configurazione Attuale</h2>";
echo "<pre>";
print_r($settings);
echo "</pre>";

// Testa l'ID Sito
$id_sito = $settings['cohesion_site_id'] ?? 'DEFAULT';
echo "<h2>ID Sito Configurato: <strong>" . $id_sito . "</strong></h2>";

// Testa se l'ID Sito viene passato correttamente
if ($id_sito === 'TEST') {
    echo "<div style='color: orange; font-weight: bold;'>⚠️ Stai usando l'ID Sito TEST. Per utilizzare SPID/CIE in produzione, devi configurare un ID Sito valido.</div>";
} else {
    echo "<div style='color: green; font-weight: bold;'>✅ ID Sito personalizzato configurato: " . $id_sito . "</div>";
}

// Testa la configurazione SAML 2.0
$saml20 = $settings['cohesion_use_saml20'] ?? false;
echo "<h2>SAML 2.0: " . ($saml20 ? "✅ Abilitato" : "❌ Disabilitato") . "</h2>";

// Testa le restrizioni di autenticazione
$auth_restriction = $settings['cohesion_auth_restriction'] ?? '0,1,2,3';
echo "<h2>Restrizioni Autenticazione: " . $auth_restriction . "</h2>";

if ($auth_restriction === '0' || strpos($auth_restriction, '0') !== false) {
    echo "<div style='color: green;'>✅ Supporto per tutti i metodi di autenticazione incluso SPID/CIE</div>";
} else {
    echo "<div style='color: orange;'>⚠️ Restrizioni configurate: " . $auth_restriction . "</div>";
}

// Testa la libreria Cohesion2
echo "<h2>Test Libreria Cohesion2</h2>";
try {
    require_once('lib/Cohesion2.php');
    
    if (class_exists('Cohesion2')) {
        echo "<div style='color: green;'>✅ Libreria Cohesion2 locale disponibile</div>";
        
        $cohesion = new Cohesion2();
        
        // Configura ID Sito se disponibile
        if (!empty($settings['cohesion_id_sito'])) {
            $cohesion->setIdSito($settings['cohesion_id_sito']);
            echo "<div style='color: green;'>✅ ID Sito configurato: " . $settings['cohesion_id_sito'] . "</div>";
        } else {
            echo "<div style='color: red;'>❌ ID Sito non configurato</div>";
        }
        
        // Test configurazione
        $cohesion->useSAML20(true);
        $cohesion->useSSO(true);
        $cohesion->setAuthRestriction($auth_restriction);
        
        echo "<div style='color: green;'>✅ Configurazione Cohesion2 completata</div>";
        
        // Test certificati configurati
        if (!empty($settings['cohesion_certificate_path']) && !empty($settings['cohesion_key_path'])) {
            if (file_exists($settings['cohesion_certificate_path']) && file_exists($settings['cohesion_key_path'])) {
                echo "<div style='color: green;'>✅ Certificati personalizzati configurati e trovati</div>";
            } else {
                echo "<div style='color: orange;'>⚠️ Certificati personalizzati configurati ma non trovati</div>";
            }
        } else {
            echo "<div style='color: blue;'>ℹ️ Usando certificati predefiniti della libreria Cohesion2</div>";
        }
    } else {
        echo "<div style='color: red;'>❌ Libreria Cohesion2 non disponibile</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Errore nella libreria Cohesion2: " . $e->getMessage() . "</div>";
}

// Testa l'URL di login che verrà generato
echo "<h2>Test URL di Login</h2>";
$protocol = 'https://';
$urlPagina = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$urlPagina .= '?cohesionCheck=1';

$xmlAuth = '<dsAuth xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/Auth.xsd">
    <auth>
        <user />
        <id_sa />
        <id_sito>' . $id_sito . '</id_sito>
        <esito_auth_sa />
        <id_sessione_sa />
        <id_sessione_aspnet_sa />
        <url_validate><![CDATA[' . $urlPagina . ']]></url_validate>
        <url_richiesta><![CDATA[' . $urlPagina . ']]></url_richiesta>
        <esito_auth_sso />
        <id_sessione_sso />
        <id_sessione_aspnet_sso />
        <stilesheet>AuthRestriction=' . $auth_restriction . '</stilesheet>
        <AuthRestriction xmlns="">' . $auth_restriction . '</AuthRestriction>
    </auth>
</dsAuth>';

echo "<h3>XML di Autenticazione (ID Sito: " . $id_sito . ")</h3>";
echo "<textarea readonly style='width: 100%; height: 200px;'>" . htmlspecialchars($xmlAuth) . "</textarea>";

$auth = urlencode(base64_encode($xmlAuth));
$urlLogin = 'https://cohesion2.regione.marche.it/SPManager/WAYF.aspx?auth=' . $auth;

echo "<h3>URL di Login SAML 2.0</h3>";
echo "<textarea readonly style='width: 100%; height: 100px;'>" . htmlspecialchars($urlLogin) . "</textarea>";
echo "<p><a href='" . $urlLogin . "' target='_blank'>⚠️ Test Login (aprirà la pagina di autenticazione Cohesion)</a></p>";

echo "<h2>Note</h2>";
echo "<ul>";
echo "<li>Per utilizzare SPID/CIE, l'ID Sito deve essere configurato correttamente</li>";
echo "<li>SAML 2.0 deve essere abilitato per supportare SPID/CIE</li>";
echo "<li>Le restrizioni di autenticazione dovrebbero includere '0' per tutti i metodi</li>";
echo "<li>Il portale Cohesion mostrerà le opzioni SPID/CIE solo se l'ID Sito è configurato correttamente</li>";
echo "</ul>";
?>
