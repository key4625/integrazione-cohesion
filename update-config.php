<?php
/**
 * Script per aggiornare la configurazione di Cohesion con l'ID Sito reale
 */

// Determina se siamo in ambiente WordPress o in test standalone
if (!defined('ABSPATH')) {
    echo "<h1>Aggiornamento Configurazione Cohesion</h1>\n";
    echo "<p>⚠️ <strong>ATTENZIONE:</strong> Questo script deve essere eseguito in ambiente WordPress per aggiornare la configurazione.</p>\n";
    echo "<p>Per aggiornare manualmente:</p>\n";
    echo "<ol>\n";
    echo "<li>Vai nel pannello WordPress Admin</li>\n";
    echo "<li>Vai in <strong>Impostazioni > Cohesion</strong></li>\n";
    echo "<li>Cambia l'ID Sito da 'TEST' a '<strong>MONSAN0001</strong>' (o l'ID reale fornito dalla Regione Marche)</li>\n";
    echo "<li>Salva le impostazioni</li>\n";
    echo "</ol>\n";
    echo "<p>Data: " . date('Y-m-d H:i:s') . "</p>\n";
    exit;
}

// Siamo in ambiente WordPress
$current_id = get_option('cohesion_id_sito', 'TEST');
$real_id = 'MONSAN0001'; // Sostituisci con l'ID reale fornito dalla Regione Marche

echo "<h1>Aggiornamento ID Sito Cohesion</h1>\n";
echo "<hr>\n";

echo "<p><strong>ID Sito Attuale:</strong> $current_id</p>\n";
echo "<p><strong>Nuovo ID Sito:</strong> $real_id</p>\n";

if (isset($_GET['update']) && $_GET['update'] === '1') {
    // Aggiorna l'ID Sito
    update_option('cohesion_id_sito', $real_id);
    
    // Verifica l'aggiornamento
    $updated_id = get_option('cohesion_id_sito');
    
    if ($updated_id === $real_id) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>\n";
        echo "✅ <strong>ID Sito aggiornato con successo!</strong><br>\n";
        echo "Nuovo ID: <strong>$updated_id</strong>\n";
        echo "</div>\n";
        
        echo "<h3>Prossimi passi:</h3>\n";
        echo "<ol>\n";
        echo "<li>Verifica che l'ID Sito sia stato abilitato dalla Regione Marche</li>\n";
        echo "<li>Testa il login: <a href='" . home_url('/cohesion/login') . "' target='_blank'>" . home_url('/cohesion/login') . "</a></li>\n";
        echo "<li>Dovresti vedere le opzioni SPID/CIE nel portale esterno</li>\n";
        echo "</ol>\n";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>\n";
        echo "❌ <strong>Errore nell'aggiornamento dell'ID Sito</strong>\n";
        echo "</div>\n";
    }
    
} else {
    echo "<p><strong>Per aggiornare l'ID Sito clicca qui:</strong></p>\n";
    echo "<a href='?update=1' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aggiorna ID Sito</a><br><br>\n";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px;'>\n";
    echo "⚠️ <strong>IMPORTANTE:</strong><br>\n";
    echo "- Assicurati che l'ID Sito sia corretto e abilitato dalla Regione Marche<br>\n";
    echo "- Con 'TEST' vedrai solo login/password tradizionale<br>\n";
    echo "- Con un ID reale abilitato vedrai SPID, CIE ed eIDAS\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<h2>Stato Configurazione Attuale</h2>\n";

$config = array(
    'cohesion_id_sito' => get_option('cohesion_id_sito', 'TEST'),
    'cohesion_enable_saml20' => get_option('cohesion_enable_saml20', true),
    'cohesion_enable_spid' => get_option('cohesion_enable_spid', true),
    'cohesion_enable_cie' => get_option('cohesion_enable_cie', true),
    'cohesion_auth_restriction' => get_option('cohesion_auth_restriction', '0,1,2,3'),
);

echo "<table style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr style='background: #f5f5f5;'><th style='border: 1px solid #ddd; padding: 8px;'>Opzione</th><th style='border: 1px solid #ddd; padding: 8px;'>Valore</th></tr>\n";

foreach ($config as $key => $value) {
    $value_display = is_bool($value) ? ($value ? 'Abilitato' : 'Disabilitato') : $value;
    echo "<tr><td style='border: 1px solid #ddd; padding: 8px;'>$key</td><td style='border: 1px solid #ddd; padding: 8px;'>$value_display</td></tr>\n";
}

echo "</table>\n";

echo "<h3>Link di Test</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . home_url('/cohesion/login') . "' target='_blank'>Test Login Cohesion</a></li>\n";
echo "<li><a href='" . admin_url('options-general.php?page=cohesion-settings') . "' target='_blank'>Impostazioni Plugin</a></li>\n";
echo "<li><a href='check-deployment.php' target='_blank'>Check Deployment</a></li>\n";
echo "</ul>\n";

echo "<p>Data: " . date('Y-m-d H:i:s') . "</p>\n";
?>
