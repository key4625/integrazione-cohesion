<?php
/**
 * Plugin Name: Integrazione Cohesion
 * Plugin URI: https://cohesion.regione.marche.it/
 * Description: Plugin per l'integrazione del sistema di autenticazione Cohesion della Regione Marche con WordPress. Supporta SPID, CIE, eIDAS e autenticazione tradizionale.
 * Version: 1.0.0
 * Author: Sistema Cohesion
 * License: MIT
 * Text Domain: integrazione-cohesion
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('COHESION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COHESION_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('COHESION_PLUGIN_VERSION', '1.0.0');

// Load Composer autoloader if available
if (file_exists(COHESION_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once COHESION_PLUGIN_PATH . 'vendor/autoload.php';
}

// Include required files
require_once COHESION_PLUGIN_PATH . 'includes/class-cohesion-config.php';
require_once COHESION_PLUGIN_PATH . 'includes/class-cohesion-integration.php';
require_once COHESION_PLUGIN_PATH . 'includes/class-cohesion-admin.php';
require_once COHESION_PLUGIN_PATH . 'includes/class-cohesion-authentication.php';
require_once COHESION_PLUGIN_PATH . 'includes/class-cohesion-user-manager.php';

/**
 * Main plugin class
 */
class CohesionIntegrationPlugin {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('integrazione-cohesion', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        new Cohesion_Integration();
        
        if (is_admin()) {
            new Cohesion_Admin();
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check system requirements
        $errors = CohesionConfig::checkRequirements();
        if (!empty($errors)) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                '<h1>' . __('Errore di attivazione plugin', 'integrazione-cohesion') . '</h1>' .
                '<p>' . __('Il plugin Integrazione Cohesion non può essere attivato per i seguenti motivi:', 'integrazione-cohesion') . '</p>' .
                '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>' .
                '<p><a href="' . admin_url('plugins.php') . '">' . __('Torna ai plugin', 'integrazione-cohesion') . '</a></p>'
            );
        }
        
        // Check if Cohesion library is available
        if (!CohesionConfig::isCohesionLibraryAvailable()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                '<h1>' . __('Libreria Cohesion mancante', 'integrazione-cohesion') . '</h1>' .
                '<p>' . __('La libreria Cohesion2 non è stata trovata. Installa le dipendenze con:', 'integrazione-cohesion') . '</p>' .
                '<code>composer install</code>' .
                '<p>' . __('Oppure scarica manualmente la libreria da GitHub e inseriscila nella cartella lib/cohesion2/', 'integrazione-cohesion') . '</p>' .
                '<p><a href="' . admin_url('plugins.php') . '">' . __('Torna ai plugin', 'integrazione-cohesion') . '</a></p>'
            );
        }
        
        // Initialize default settings
        CohesionConfig::initializeSettings();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize plugin
CohesionIntegrationPlugin::getInstance();
