<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cohesion Authentication Handler
 */
class CohesionAuthentication {
    
    private $cohesion;
    
    public function __construct() {
        // Load Cohesion library
        if (file_exists(COHESION_PLUGIN_PATH . 'vendor/autoload.php')) {
            require_once COHESION_PLUGIN_PATH . 'vendor/autoload.php';
        } else {
            // Fallback: include the library directly if composer is not available
            if (file_exists(COHESION_PLUGIN_PATH . 'lib/cohesion2/Cohesion2.php')) {
                require_once COHESION_PLUGIN_PATH . 'lib/cohesion2/Cohesion2.php';
            }
        }
    }
    
    /**
     * Initiate login process
     */
    public function initiate_login() {
        try {
            // Initialize Cohesion
            $this->cohesion = new \andreaval\Cohesion2\Cohesion2('wordpress_cohesion');
            
            // Configure based on plugin settings
            $this->configure_cohesion();
            
            // Store redirect URL in session
            $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : get_option('cohesion_redirect_after_login', home_url());
            $_SESSION['cohesion_redirect_after_login'] = $redirect_to;
            
            // Start authentication
            $this->cohesion->auth();
            
        } catch (\Exception $e) {
            $this->handle_error('Errore durante l\'inizializzazione del login: ' . $e->getMessage());
        }
    }
    
    /**
     * Configure Cohesion based on plugin settings
     */
    private function configure_cohesion() {
        // Set ID Sito
        $id_sito = get_option('cohesion_id_sito', 'TEST');
        // Note: The library doesn't have a public method to set ID_SITO, 
        // this would need to be configured in the library itself
        
        // Enable SAML 2.0 if configured
        if (get_option('cohesion_enable_saml20', true)) {
            $this->cohesion->useSAML20(true);
            
            // Enable eIDAS if configured
            if (get_option('cohesion_enable_eidas', false)) {
                $this->cohesion->enableEIDASLogin();
            }
            
            // Enable SPID Professional if configured
            if (get_option('cohesion_enable_spid_pro', false)) {
                $purposes = get_option('cohesion_spid_pro_purposes', array('PF'));
                $this->cohesion->enableSPIDProLogin($purposes);
            }
        }
        
        // Set authentication restrictions
        $auth_restriction = get_option('cohesion_auth_restriction', '0,1,2,3');
        if (!empty($auth_restriction)) {
            $this->cohesion->setAuthRestriction($auth_restriction);
        }
    }
    
    /**
     * Handle authentication callback
     */
    public function handle_callback() {
        try {
            // Check if we have an auth parameter (callback from Cohesion)
            if (isset($_GET['auth']) || isset($_POST['auth'])) {
                $this->process_authentication();
            } else {
                // No auth parameter, redirect to login
                wp_redirect(wp_login_url());
                exit;
            }
            
        } catch (\Exception $e) {
            $this->handle_error('Errore durante l\'autenticazione: ' . $e->getMessage());
        }
    }
    
    /**
     * Process authentication response
     */
    private function process_authentication() {
        // Initialize Cohesion
        $this->cohesion = new \andreaval\Cohesion2\Cohesion2('wordpress_cohesion');
        $this->configure_cohesion();
        
        // Process authentication
        $this->cohesion->auth();
        
        if ($this->cohesion->isAuth()) {
            // Authentication successful
            $this->handle_successful_authentication();
        } else {
            // Authentication failed
            $this->handle_error('Autenticazione fallita');
        }
    }
    
    /**
     * Handle successful authentication
     */
    private function handle_successful_authentication() {
        $profile = $this->cohesion->profile;
        $username = $this->cohesion->username;
        
        if (empty($profile) || empty($username)) {
            $this->handle_error('Profilo utente non disponibile');
            return;
        }
        
        // Get or create WordPress user
        $user_manager = new CohesionUserManager();
        $user = $user_manager->get_or_create_user($profile, $username);
        
        if (is_wp_error($user)) {
            $this->handle_error('Errore nella creazione dell\'utente: ' . $user->get_error_message());
            return;
        }
        
        // Log in the user
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        // Mark user as authenticated via Cohesion
        update_user_meta($user->ID, 'cohesion_authenticated', true);
        update_user_meta($user->ID, 'cohesion_last_login', current_time('mysql'));
        update_user_meta($user->ID, 'cohesion_profile', $profile);
        update_user_meta($user->ID, 'cohesion_sso_id', $this->cohesion->id_sso);
        
        // Log the login
        $this->log_user_login($user, $profile);
        
        // Redirect to intended page
        $redirect_to = isset($_SESSION['cohesion_redirect_after_login']) 
            ? $_SESSION['cohesion_redirect_after_login'] 
            : get_option('cohesion_redirect_after_login', home_url());
        
        unset($_SESSION['cohesion_redirect_after_login']);
        
        wp_redirect($redirect_to);
        exit;
    }
    
    /**
     * Initiate logout process
     */
    public function initiate_logout() {
        try {
            // Get current user info before logout
            $user_id = get_current_user_id();
            $cohesion_auth = get_user_meta($user_id, 'cohesion_authenticated', true);
            
            // Store redirect URL
            $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : get_option('cohesion_redirect_after_logout', home_url());
            
            if ($cohesion_auth && is_user_logged_in()) {
                // User was authenticated via Cohesion, perform Cohesion logout
                $this->cohesion = new \andreaval\Cohesion2\Cohesion2('wordpress_cohesion');
                $this->configure_cohesion();
                
                // Clear WordPress session
                wp_logout();
                
                // Clear Cohesion metadata
                delete_user_meta($user_id, 'cohesion_authenticated');
                
                // Perform Cohesion logout
                $this->cohesion->logout();
                
            } else {
                // Regular WordPress logout
                wp_logout();
            }
            
            // Redirect after logout
            wp_redirect($redirect_to);
            exit;
            
        } catch (\Exception $e) {
            // Fallback: regular WordPress logout
            wp_logout();
            wp_redirect(home_url());
            exit;
        }
    }
    
    /**
     * Log user login activity
     */
    private function log_user_login($user, $profile) {
        $log_data = array(
            'user_id' => $user->ID,
            'username' => $user->user_login,
            'login_time' => current_time('mysql'),
            'ip_address' => $this->get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'cohesion_profile' => $profile,
            'authentication_type' => isset($profile['tipo_autenticazione']) ? $profile['tipo_autenticazione'] : 'unknown'
        );
        
        // Store in WordPress option (you might want to use a custom table for better performance)
        $existing_logs = get_option('cohesion_login_logs', array());
        $existing_logs[] = $log_data;
        
        // Keep only last 100 logs
        if (count($existing_logs) > 100) {
            $existing_logs = array_slice($existing_logs, -100);
        }
        
        update_option('cohesion_login_logs', $existing_logs);
        
        // WordPress action for other plugins to hook into
        do_action('cohesion_user_login', $user, $profile, $log_data);
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    
    /**
     * Handle authentication errors
     */
    private function handle_error($message) {
        // Log error
        error_log('Cohesion Authentication Error: ' . $message);
        
        // Store error in session to display to user
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['cohesion_error'] = $message;
        
        // Redirect to login page with error
        $login_url = wp_login_url();
        $login_url = add_query_arg('cohesion_error', '1', $login_url);
        
        wp_redirect($login_url);
        exit;
    }
    
    /**
     * Display error messages on login page
     */
    public static function display_login_errors() {
        if (isset($_GET['cohesion_error']) && $_GET['cohesion_error'] == '1') {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['cohesion_error'])) {
                echo '<div id="login_error">' . esc_html($_SESSION['cohesion_error']) . '</div>';
                unset($_SESSION['cohesion_error']);
            }
        }
    }
}

// Add error display to login page
add_action('login_head', array('CohesionAuthentication', 'display_login_errors'));
