<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cohesion Configuration Helper
 * Handles the configuration of the ID Sito and other Cohesion-specific settings
 */
class CohesionConfig {
    
    /**
     * Get the configured ID Sito or fallback to TEST
     */
    public static function getIdSito() {
        $id_sito = get_option('cohesion_id_sito', 'TEST');
        
        // If still using TEST in production, show admin notice
        if ($id_sito === 'TEST' && !self::isTestEnvironment()) {
            add_action('admin_notices', array(__CLASS__, 'show_test_id_notice'));
        }
        
        return $id_sito;
    }
    
    /**
     * Check if we're in a test environment
     */
    public static function isTestEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        // Common test/dev indicators
        $test_indicators = array(
            'localhost',
            '127.0.0.1',
            '.local',
            '.dev',
            '.test',
            'staging.',
            'dev.',
            'test.'
        );
        
        foreach ($test_indicators as $indicator) {
            if (strpos($host, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Show admin notice for TEST ID in production
     */
    public static function show_test_id_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('Attenzione:', 'integrazione-cohesion'); ?></strong>
                <?php _e('Stai utilizzando l\'ID Sito "TEST" di Cohesion. Per utilizzare il plugin in produzione, devi richiedere un ID Sito ufficiale alla Regione Marche e configurarlo nelle impostazioni del plugin.', 'integrazione-cohesion'); ?>
                <a href="<?php echo admin_url('options-general.php?page=cohesion-settings'); ?>"><?php _e('Configura ora', 'integrazione-cohesion'); ?></a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get Cohesion URLs based on environment
     */
    public static function getCohesionUrls() {
        $id_sito = self::getIdSito();
        
        if ($id_sito === 'TEST' || self::isTestEnvironment()) {
            // Test environment URLs
            return array(
                'check' => 'https://cohesion2.regione.marche.it/sso/Check.aspx?auth=',
                'login' => 'https://cohesion2.regione.marche.it/SA/AccediCohesion.aspx?auth=',
                'web' => 'https://cohesion2.regione.marche.it/SSO/webCheckSessionSSO.aspx',
                'saml20_check' => 'https://cohesion2.regione.marche.it/SPManager/WAYF.aspx?auth=',
                'saml20_web' => 'https://cohesion2.regione.marche.it/SPManager/webCheckSessionSSO.aspx'
            );
        } else {
            // Production URLs (same as test for now, but this could change)
            return array(
                'check' => 'https://cohesion2.regione.marche.it/sso/Check.aspx?auth=',
                'login' => 'https://cohesion2.regione.marche.it/SA/AccediCohesion.aspx?auth=',
                'web' => 'https://cohesion2.regione.marche.it/SSO/webCheckSessionSSO.aspx',
                'saml20_check' => 'https://cohesion2.regione.marche.it/SPManager/WAYF.aspx?auth=',
                'saml20_web' => 'https://cohesion2.regione.marche.it/SPManager/webCheckSessionSSO.aspx'
            );
        }
    }
    
    /**
     * Check system requirements
     */
    public static function checkRequirements() {
        $errors = array();
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $errors[] = sprintf(__('PHP 7.4 o superiore richiesto. Versione attuale: %s', 'integrazione-cohesion'), PHP_VERSION);
        }
        
        // Check allow_url_fopen
        if (!ini_get('allow_url_fopen')) {
            $errors[] = __('La direttiva allow_url_fopen deve essere abilitata nel php.ini', 'integrazione-cohesion');
        }
        
        // Check required extensions
        $required_extensions = array('openssl', 'dom', 'libxml', 'curl');
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf(__('Estensione PHP richiesta: %s', 'integrazione-cohesion'), $ext);
            }
        }
        
        // Check if WordPress supports sessions
        if (session_status() === PHP_SESSION_DISABLED) {
            $errors[] = __('Le sessioni PHP sono disabilitate', 'integrazione-cohesion');
        }
        
        return $errors;
    }
    
    /**
     * Check if Cohesion library is available
     */
    public static function isCohesionLibraryAvailable() {
        // Check if autoloader is available
        if (file_exists(COHESION_PLUGIN_PATH . 'vendor/autoload.php')) {
            require_once COHESION_PLUGIN_PATH . 'vendor/autoload.php';
            return class_exists('Cohesion2');
        }
        
        // Check if manual installation is available
        if (file_exists(COHESION_PLUGIN_PATH . 'lib/cohesion2/Cohesion2.php')) {
            require_once COHESION_PLUGIN_PATH . 'lib/cohesion2/Cohesion2.php';
            return class_exists('Cohesion2');
        }
        
        return false;
    }
    
    /**
     * Get default plugin settings
     */
    public static function getDefaultSettings() {
        return array(
            'cohesion_id_sito' => 'TEST',
            'cohesion_enable_saml20' => true,
            'cohesion_enable_spid' => true,
            'cohesion_enable_cie' => true,
            'cohesion_enable_eidas' => false,
            'cohesion_enable_spid_pro' => false,
            'cohesion_spid_pro_purposes' => array('PF'),
            'cohesion_auth_restriction' => '0,1,2,3',
            'cohesion_redirect_after_login' => home_url(),
            'cohesion_redirect_after_logout' => home_url(),
            'cohesion_auto_create_users' => true,
            'cohesion_default_role' => 'subscriber',
            'cohesion_send_welcome_email' => false,
            'cohesion_fallback_email_domain' => parse_url(home_url(), PHP_URL_HOST)
        );
    }
    
    /**
     * Initialize default settings if they don't exist
     */
    public static function initializeSettings() {
        $defaults = self::getDefaultSettings();
        
        foreach ($defaults as $option_name => $default_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $default_value);
            }
        }
    }
    
    /**
     * Validate ID Sito format
     */
    public static function validateIdSito($id_sito) {
        // Basic validation - ID Sito should be alphanumeric
        if (empty($id_sito)) {
            return false;
        }
        
        // Allow TEST for testing
        if ($id_sito === 'TEST') {
            return true;
        }
        
        // Validate format (adjust based on actual ID Sito format requirements)
        return preg_match('/^[A-Za-z0-9_-]+$/', $id_sito);
    }
}
