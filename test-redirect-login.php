<?php
/**
 * Test debug specifico per il redirect del login Cohesion
 * Questo script testa il redirect diretto senza interferenze di WordPress
 */

echo "<h1>Test Redirect Login Cohesion</h1>\n";
echo "<hr>\n";

// Carica la libreria Cohesion2
$plugin_path = dirname(__FILE__) . '/';
if (file_exists($plugin_path . 'lib/Cohesion2.php')) {
    require_once $plugin_path . 'lib/Cohesion2.php';
    echo "✅ Libreria Cohesion2 caricata<br>\n";
} else {
    echo "❌ Libreria non trovata<br>\n";
    exit;
}

// Test con ID Sito reale
$id_sito = 'MONSAN0001'; // Usa l'ID reale invece di TEST
echo "<h2>Test con ID Sito Reale: $id_sito</h2>\n";

try {
    // Avvia la sessione
    session_start();
    
    // Crea istanza Cohesion2
    $cohesion = new Cohesion2();
    echo "✅ Istanza Cohesion2 creata<br>\n";
    
    // Configura con ID Sito reale
    $cohesion->setIdSito($id_sito);
    echo "✅ ID Sito configurato: $id_sito<br>\n";
    
    // Abilita SAML2.0 per SPID/CIE
    $cohesion->useSAML20(true);
    $cohesion->useSSO(true);
    echo "✅ SAML 2.0 e SSO abilitati<br>\n";
    
    // Configura restrizioni autenticazione
    $cohesion->setAuthRestriction('0,1,2,3');
    echo "✅ Restrizioni autenticazione configurate<br>\n";
    
    // Mostra l'URL che verrebbe generato
    echo "<h3>Preview URL che verrebbe generato:</h3>\n";
    
    // Simula la generazione dell'URL senza fare il redirect
    $protocol = 'https://';
    $urlPagina = $protocol . 'turismo.montesanvito.info/wp-content/plugins/integrazione-cohesion/test-redirect-login.php?cohesionCheck=1';
    
    $xmlAuth = '<dsAuth xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/Auth.xsd">
        <auth>
            <user />
            <id_sa />
            <id_sito>'.$id_sito.'</id_sito>
            <esito_auth_sa />
            <id_sessione_sa />
            <id_sessione_aspnet_sa />
            <url_validate><![CDATA['.$urlPagina.']]></url_validate>
            <url_richiesta><![CDATA['.$urlPagina.']]></url_richiesta>
            <esito_auth_sso />
            <id_sessione_sso />
            <id_sessione_aspnet_sso />
            <stilesheet>AuthRestriction=0,1,2,3</stilesheet>
            <AuthRestriction xmlns="">0,1,2,3</AuthRestriction>
        </auth>
    </dsAuth>';
    
    $auth = urlencode(base64_encode($xmlAuth));
    
    // URL SAML2.0 (per SPID/CIE)
    $urlLogin = 'https://cohesion2.regione.marche.it/SPManager/WAYF.aspx?auth=' . $auth;
    
    echo "<p><strong>URL SAML 2.0 (SPID/CIE):</strong></p>\n";
    echo "<div style='word-break: break-all; background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo "<a href='$urlLogin' target='_blank' style='color: blue;'>$urlLogin</a>";
    echo "</div><br>\n";
    
    // Test anche l'URL tradizionale
    $urlLoginTraditional = 'https://cohesion2.regione.marche.it/sso/Check.aspx?auth=' . $auth;
    echo "<p><strong>URL Tradizionale (SSO):</strong></p>\n";
    echo "<div style='word-break: break-all; background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo "<a href='$urlLoginTraditional' target='_blank' style='color: blue;'>$urlLoginTraditional</a>";
    echo "</div><br>\n";
    
    echo "<h3>Test Redirect Diretto</h3>\n";
    echo "<p>Se clicchi qui sotto, dovrebbe essere fatto il redirect automaticamente al portale Cohesion:</p>\n";
    echo "<p><strong style='color: red;'>ATTENZIONE: Il click seguente farà il redirect reale!</strong></p>\n";
    echo "<button onclick=\"testRedirect()\" style='background: red; color: white; padding: 10px; border: none; cursor: pointer;'>ESEGUI REDIRECT REALE</button><br><br>\n";
    
    echo "<script>
    function testRedirect() {
        if(confirm('Sei sicuro di voler testare il redirect? Verrai reindirizzato al portale Cohesion.')) {
            window.location.href = 'test-redirect-login.php?do_redirect=1';
        }
    }
    </script>\n";
    
    // Se è richiesto il redirect reale
    if (isset($_GET['do_redirect']) && $_GET['do_redirect'] == '1') {
        echo "<h3>🚀 Eseguendo redirect...</h3>\n";
        echo "<p>Se vedi questo messaggio, il redirect non è avvenuto correttamente.</p>\n";
        
        // Pulisci l'output buffer per evitare problemi
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Esegui il redirect della libreria
        $cohesion->auth();
        
        // Se arriviamo qui, il redirect non è funzionato
        echo "<p style='color: red;'>❌ Il redirect non è stato eseguito!</p>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "<br>\n";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>\n";
}

echo "<hr>\n";
echo "<h2>📋 Risultati Test</h2>\n";
echo "<ul>\n";
echo "<li><strong>ID Sito:</strong> $id_sito (reale, non TEST)</li>\n";
echo "<li><strong>SAML 2.0:</strong> Abilitato per SPID/CIE</li>\n";
echo "<li><strong>SSO:</strong> Abilitato</li>\n";
echo "<li><strong>Restrizioni:</strong> 0,1,2,3 (tutti i metodi)</li>\n";
echo "</ul>\n";
echo "<p><strong>Se l'ID Sito è corretto e abilitato da Regione Marche, dovresti vedere le opzioni SPID/CIE nel portale esterno.</strong></p>\n";
echo "<p>Data: " . date('Y-m-d H:i:s') . "</p>\n";
?>
