<?php
/**
 * Plugin Name: Integrazione Cohesion
 * Plugin URI: https://github.com/keysoluzioni/integrazione-cohesion-wordpress
 * Description: Plugin per l'integrazione del sistema di autenticazione Cohesion della Regione Marche con WordPress. Supporta SPID, CIE, eIDAS e autenticazione tradizionale. Realizzato con AI GitHub Copilot e perfezionato da Key Soluzioni Informatiche.
 * Version: 1.0.1
 * Author: GitHub Copilot & Ing. Michele Cappannari (Key Soluzioni Informatiche)
 * Author URI: https://keysoluzioni.it
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: integrazione-cohesion
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * 
 * Developed with: GitHub Copilot AI Assistant
 * Refined and tested by: Ing. Michele Cappannari - Key Soluzioni Informatiche
 * 
 * @package IntegrazioneCohesion
 * @version 1.0.1
 * @author GitHub Copilot & Michele Cappannari <michele.cappannari@keysoluzioni.it>
 * @copyright 2025 Key Soluzioni Informatiche
 * @license MIT License
 * @link https://github.com/keysoluzioni/integrazione-cohesion-wordpress
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('COHESION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COHESION_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('COHESION_PLUGIN_VERSION', '1.0.1');

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
        $errors = Cohesion_Config::checkRequirements();
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
        if (!Cohesion_Config::isCohesionLibraryAvailable()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                '<h1>' . __('Libreria Cohesion mancante', 'integrazione-cohesion') . '</h1>' .
                '<p>' . __('La libreria Cohesion2 locale non è stata trovata nel percorso lib/Cohesion2.php', 'integrazione-cohesion') . '</p>' .
                '<p>' . __('Assicurati che il file lib/Cohesion2.php sia presente nella cartella del plugin.', 'integrazione-cohesion') . '</p>' .
                '<p><a href="' . admin_url('plugins.php') . '">' . __('Torna ai plugin', 'integrazione-cohesion') . '</a></p>'
            );
        }
        
        // Initialize default settings
        Cohesion_Config::initializeSettings();
        
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
