<?php
/**
 * Debug helper per Cohesion Integration
 * 
 * Questo file può essere utilizzato per testare l'integrazione Cohesion
 * e diagnosticare eventuali problemi sul server remoto.
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    // Se non è WordPress, include manualmente le costanti base
    define('ABSPATH', dirname(__FILE__) . '/../../../../');
    
    // Carica WordPress
    require_once ABSPATH . 'wp-config.php';
    require_once ABSPATH . 'wp-load.php';
}

// Attiva il debug
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Funzione per output debug
function debug_output($title, $data) {
    echo "<h3>$title</h3>";
    echo "<pre>";
    if (is_array($data) || is_object($data)) {
        print_r($data);
    } else {
        echo $data;
    }
    echo "</pre>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Cohesion Integration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h3 { color: #666; border-bottom: 1px solid #ccc; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Debug Cohesion Integration</h1>
    
    <?php
    // Test 1: Verifica ambiente WordPress
    debug_output("1. Ambiente WordPress", array(
        'WordPress Version' => get_bloginfo('version'),
        'PHP Version' => PHP_VERSION,
        'Plugin Active' => is_plugin_active('integrazione-cohesion/integrazione-cohesion.php'),
        'Current User' => wp_get_current_user()->user_login ?: 'Nessun utente loggato'
    ));
    
    // Test 2: Verifica file e percorsi
    $plugin_dir = plugin_dir_path(__FILE__);
    $autoload_path = $plugin_dir . 'vendor/autoload.php';
    
    debug_output("2. File e Percorsi", array(
        'Plugin Directory' => $plugin_dir,
        'Autoload Path' => $autoload_path,
        'Autoload Exists' => file_exists($autoload_path) ? 'SÌ' : 'NO',
        'Vendor Directory' => is_dir($plugin_dir . 'vendor') ? 'SÌ' : 'NO',
        'Cohesion2 Library' => is_dir($plugin_dir . 'vendor/andreaval/cohesion2-library') ? 'SÌ' : 'NO'
    ));
    
    // Test 3: Verifica autoloader e classe
    echo "<h3>3. Test Autoloader e Classe</h3>";
    
    if (file_exists($autoload_path)) {
        echo "<p class='success'>✓ Autoloader trovato</p>";
        
        try {
            require_once $autoload_path;
            echo "<p class='success'>✓ Autoloader caricato</p>";
            
            if (class_exists('Cohesion2')) {
                echo "<p class='success'>✓ Classe Cohesion2 disponibile</p>";
                
                try {
                    $cohesion = new Cohesion2();
                    echo "<p class='success'>✓ Istanza Cohesion2 creata</p>";
                    
                    // Verifica metodi disponibili
                    $methods = get_class_methods($cohesion);
                    debug_output("Metodi disponibili", $methods);
                    
                } catch (Exception $e) {
                    echo "<p class='error'>✗ Errore creazione istanza: " . $e->getMessage() . "</p>";
                }
                
            } else {
                echo "<p class='error'>✗ Classe Cohesion2 non trovata</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>✗ Errore caricamento autoloader: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Autoloader non trovato</p>";
    }
    
    // Test 4: Verifica configurazione plugin
    echo "<h3>4. Configurazione Plugin</h3>";
    
    if (class_exists('Cohesion_Config')) {
        $config = new Cohesion_Config();
        $settings = $config->get_all_settings();
        debug_output("Impostazioni Plugin", $settings);
    } else {
        echo "<p class='error'>✗ Classe Cohesion_Config non trovata</p>";
    }
    
    // Test 5: Verifica sessione
    echo "<h3>5. Sessione PHP</h3>";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    debug_output("Informazioni Sessione", array(
        'Session Status' => session_status(),
        'Session ID' => session_id(),
        'Session Data' => $_SESSION ?? array()
    ));
    
    // Test 6: Verifica AJAX endpoints
    echo "<h3>6. AJAX Endpoints</h3>";
    
    $ajax_endpoints = array(
        'Login' => admin_url('admin-ajax.php?action=cohesion_login'),
        'Callback' => admin_url('admin-ajax.php?action=cohesion_callback'),
        'Logout' => admin_url('admin-ajax.php?action=cohesion_logout')
    );
    
    debug_output("Endpoints AJAX", $ajax_endpoints);
    
    // Test 7: Test libreria Cohesion2 (se disponibile)
    echo "<h3>7. Test Libreria Cohesion2</h3>";
    
    if (class_exists('Cohesion2')) {
        try {
            $cohesion = new Cohesion2();
            
            // Test callback URL
            $callback_url = admin_url('admin-ajax.php?action=cohesion_callback');
            echo "<p>Callback URL: <code>$callback_url</code></p>";
            
            // Prova a testare il metodo auth (NON chiamarlo se non necessario)
            try {
                echo "<p>Callback URL: <code>$callback_url</code></p>";
                echo "<p>Metodo auth disponibile: " . (method_exists($cohesion, 'auth') ? 'SÌ' : 'NO') . "</p>";
                echo "<p>Metodo isAuth disponibile: " . (method_exists($cohesion, 'isAuth') ? 'SÌ' : 'NO') . "</p>";
                echo "<p>Utente autenticato: " . ($cohesion->isAuth() ? 'SÌ' : 'NO') . "</p>";
                
                if ($cohesion->isAuth()) {
                    echo "<p>Username: " . ($cohesion->username ?? 'N/A') . "</p>";
                    echo "<p>Profilo disponibile: " . (isset($cohesion->profile) ? 'SÌ' : 'NO') . "</p>";
                }
            } catch (Exception $e) {
                echo "<p class='warning'>⚠ Errore test libreria: " . $e->getMessage() . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>✗ Errore test libreria: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Libreria Cohesion2 non disponibile</p>";
    }
    
    // Test 8: Log degli errori
    echo "<h3>8. Log Errori Recenti</h3>";
    
    $log_file = ini_get('error_log');
    if ($log_file && file_exists($log_file)) {
        echo "<p>File log: <code>$log_file</code></p>";
        
        // Mostra le ultime 20 righe del log
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -20);
        
        echo "<pre>";
        foreach ($recent_lines as $line) {
            if (strpos($line, 'Cohesion') !== false) {
                echo "<span style='background: yellow;'>$line</span>";
            } else {
                echo $line;
            }
        }
        echo "</pre>";
    } else {
        echo "<p>File log non trovato o non accessibile</p>";
    }
    
    ?>
    
    <hr>
    <p><strong>Nota:</strong> Questo file dovrebbe essere rimosso in produzione per motivi di sicurezza.</p>
    
</body>
</html>
