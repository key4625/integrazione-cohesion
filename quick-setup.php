<?php
/**
 * Configurazione rapida per il deployment
 * 
 * Questo file contiene una procedura di configurazione rapida
 * per il deployment del plugin su server remoto.
 * 
 * IMPORTANTE: Rimuovere questo file dopo il deployment!
 */

// Previeni accesso diretto se non siamo in un ambiente WordPress
if (!defined('ABSPATH')) {
    // Carica WordPress se non è già caricato
    $wp_path = dirname(__FILE__) . '/../../../../wp-config.php';
    if (file_exists($wp_path)) {
        require_once $wp_path;
    } else {
        die('WordPress non trovato. Eseguire questo script dalla directory del plugin.');
    }
}

// Verifica che sia un amministratore
if (!current_user_can('administrator')) {
    die('Accesso negato. Solo gli amministratori possono eseguire questo script.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Configurazione Rapida Cohesion</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; max-width: 800px; }
        .step { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .code { background: #f0f0f0; padding: 10px; border-radius: 3px; font-family: monospace; }
        button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>Configurazione Rapida Cohesion Integration</h1>
    
    <?php
    // Verifica requisiti di sistema
    echo '<div class="step">';
    echo '<h2>1. Verifica Requisiti di Sistema</h2>';
    
    $requirements = array(
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4', '>='),
        'WordPress >= 5.0' => version_compare(get_bloginfo('version'), '5.0', '>='),
        'OpenSSL Extension' => extension_loaded('openssl'),
        'cURL Extension' => extension_loaded('curl'),
        'JSON Extension' => extension_loaded('json'),
        'Session Support' => extension_loaded('session'),
        'Composer Autoloader' => file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php'),
        'Cohesion2 Library' => file_exists(plugin_dir_path(__FILE__) . 'vendor/andreaval/cohesion2-library/cohesion2/Cohesion2.php')
    );
    
    $all_ok = true;
    foreach ($requirements as $requirement => $met) {
        $class = $met ? 'success' : 'error';
        $symbol = $met ? '✓' : '✗';
        echo "<p class='$class'>$symbol $requirement</p>";
        if (!$met) $all_ok = false;
    }
    
    if ($all_ok) {
        echo '<p class="success"><strong>✓ Tutti i requisiti sono soddisfatti!</strong></p>';
    } else {
        echo '<p class="error"><strong>✗ Alcuni requisiti non sono soddisfatti. Verificare la configurazione del server.</strong></p>';
    }
    echo '</div>';
    
    // Verifica plugin attivo
    echo '<div class="step">';
    echo '<h2>2. Stato Plugin</h2>';
    
    $plugin_file = 'integrazione-cohesion/integrazione-cohesion.php';
    $is_active = is_plugin_active($plugin_file);
    
    if ($is_active) {
        echo '<p class="success">✓ Plugin attivo</p>';
    } else {
        echo '<p class="warning">⚠ Plugin non attivo</p>';
        echo '<p><a href="' . admin_url('plugins.php') . '">Attivare il plugin</a></p>';
    }
    echo '</div>';
    
    // Configurazione di base
    echo '<div class="step">';
    echo '<h2>3. Configurazione di Base</h2>';
    
    $config_options = array(
        'cohesion_environment' => 'test',
        'cohesion_site_id' => 'TEST',
        'cohesion_auto_create_users' => true,
        'cohesion_default_role' => 'subscriber',
        'cohesion_send_welcome_email' => false
    );
    
    foreach ($config_options as $option => $default) {
        $current_value = get_option($option, $default);
        echo "<p><strong>$option:</strong> ";
        if (is_bool($current_value)) {
            echo $current_value ? 'Abilitato' : 'Disabilitato';
        } else {
            echo $current_value;
        }
        echo "</p>";
    }
    
    // Form per configurazione rapida
    if (isset($_POST['quick_config'])) {
        foreach ($config_options as $option => $default) {
            $value = $_POST[$option] ?? $default;
            if ($option === 'cohesion_auto_create_users' || $option === 'cohesion_send_welcome_email') {
                $value = isset($_POST[$option]) ? true : false;
            }
            update_option($option, $value);
        }
        echo '<p class="success">✓ Configurazione salvata!</p>';
    }
    
    echo '<form method="post">';
    echo '<h3>Configurazione Rapida</h3>';
    echo '<p><label>Ambiente: <select name="cohesion_environment">';
    echo '<option value="test"' . (get_option('cohesion_environment', 'test') === 'test' ? ' selected' : '') . '>Test</option>';
    echo '<option value="production"' . (get_option('cohesion_environment', 'test') === 'production' ? ' selected' : '') . '>Produzione</option>';
    echo '</select></label></p>';
    
    echo '<p><label>ID Sito: <input type="text" name="cohesion_site_id" value="' . esc_attr(get_option('cohesion_site_id', 'TEST')) . '" placeholder="Inserire ID sito fornito dalla Regione Marche"></label></p>';
    
    echo '<p><label>Ruolo predefinito: <select name="cohesion_default_role">';
    $roles = get_editable_roles();
    $current_role = get_option('cohesion_default_role', 'subscriber');
    foreach ($roles as $role => $details) {
        $selected = $role === $current_role ? ' selected' : '';
        echo "<option value='$role'$selected>{$details['name']}</option>";
    }
    echo '</select></label></p>';
    
    echo '<p><label><input type="checkbox" name="cohesion_auto_create_users"' . (get_option('cohesion_auto_create_users', true) ? ' checked' : '') . '> Creazione automatica utenti</label></p>';
    
    echo '<p><label><input type="checkbox" name="cohesion_send_welcome_email"' . (get_option('cohesion_send_welcome_email', false) ? ' checked' : '') . '> Invia email di benvenuto</label></p>';
    
    echo '<p><button type="submit" name="quick_config">Salva Configurazione</button></p>';
    echo '</form>';
    
    echo '</div>';
    
    // URL di test
    echo '<div class="step">';
    echo '<h2>4. URL di Test</h2>';
    
    $base_url = admin_url('admin-ajax.php');
    $test_urls = array(
        'Login' => $base_url . '?action=cohesion_login',
        'Callback' => $base_url . '?action=cohesion_callback',
        'Logout' => $base_url . '?action=cohesion_logout'
    );
    
    foreach ($test_urls as $name => $url) {
        echo "<p><strong>$name:</strong> <code>$url</code></p>";
    }
    
    echo '</div>';
    
    // Shortcode di esempio
    echo '<div class="step">';
    echo '<h2>5. Shortcode di Esempio</h2>';
    
    echo '<p>Utilizzare questo shortcode per aggiungere il pulsante di login alle pagine:</p>';
    echo '<div class="code">[cohesion_login]</div>';
    echo '<p>Oppure con testo personalizzato:</p>';
    echo '<div class="code">[cohesion_login text="Accedi con SPID/CIE"]</div>';
    
    echo '</div>';
    
    // Istruzioni finali
    echo '<div class="step">';
    echo '<h2>6. Prossimi Passi</h2>';
    
    echo '<ol>';
    echo '<li>Verificare che tutti i requisiti siano soddisfatti</li>';
    echo '<li>Attivare il plugin se non già attivo</li>';
    echo '<li>Configurare i parametri di base</li>';
    echo '<li>Testare il login con credenziali SPID/CIE</li>';
    echo '<li>Implementare gli shortcode nelle pagine necessarie</li>';
    echo '<li><strong>IMPORTANTE:</strong> Rimuovere questo file (quick-setup.php) dopo la configurazione!</li>';
    echo '</ol>';
    
    echo '</div>';
    
    // Test rapido
    echo '<div class="step">';
    echo '<h2>7. Test Rapido</h2>';
    
    if (isset($_POST['quick_test'])) {
        try {
            // Test autoloader
            require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
            echo '<p class="success">✓ Autoloader caricato</p>';
            
            // Test classe Cohesion2
            if (class_exists('andreaval\cohesion2\Cohesion2')) {
                echo '<p class="success">✓ Classe Cohesion2 disponibile</p>';
                
                // Test istanza
                $cohesion = new andreaval\cohesion2\Cohesion2();
                echo '<p class="success">✓ Istanza Cohesion2 creata</p>';
                
                // Test callback URL
                $callback_url = admin_url('admin-ajax.php?action=cohesion_callback');
                echo "<p class='success'>✓ Callback URL: $callback_url</p>";
                
            } else {
                echo '<p class="error">✗ Classe Cohesion2 non trovata</p>';
            }
            
        } catch (Exception $e) {
            echo '<p class="error">✗ Errore durante il test: ' . $e->getMessage() . '</p>';
        }
    }
    
    echo '<form method="post">';
    echo '<button type="submit" name="quick_test">Esegui Test Rapido</button>';
    echo '</form>';
    
    echo '</div>';
    ?>
    
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
        <h3>⚠ Importante per la Sicurezza</h3>
        <p>Questo file contiene funzioni di debug e configurazione che possono esporre informazioni sensibili. 
        <strong>Rimuoverlo immediatamente dopo aver completato la configurazione!</strong></p>
        <p>Per rimuovere il file, eseguire:</p>
        <div class="code">rm <?php echo __FILE__; ?></div>
    </div>
    
</body>
</html>
