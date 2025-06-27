<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main integration class for Cohesion authentication
 */
class CohesionIntegration {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Add login/logout links to WordPress menu
        add_action('wp_nav_menu_items', array($this, 'add_login_logout_links'), 10, 2);
        
        // Handle Cohesion authentication callback
        add_action('template_redirect', array($this, 'handle_cohesion_callback'));
        
        // Add rewrite rules for Cohesion endpoints
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // Handle query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Add login/logout shortcodes
        add_shortcode('cohesion_login', array($this, 'login_shortcode'));
        add_shortcode('cohesion_logout', array($this, 'logout_shortcode'));
        
        // Customize login form
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_styles'));
        add_action('login_form', array($this, 'add_cohesion_login_button'));
        
        // Handle logout
        add_action('wp_logout', array($this, 'handle_wp_logout'));
    }
    
    /**
     * Add rewrite rules for Cohesion endpoints
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^cohesion/login/?$',
            'index.php?cohesion_action=login',
            'top'
        );
        
        add_rewrite_rule(
            '^cohesion/logout/?$',
            'index.php?cohesion_action=logout',
            'top'
        );
        
        add_rewrite_rule(
            '^cohesion/callback/?$',
            'index.php?cohesion_action=callback',
            'top'
        );
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'cohesion_action';
        return $vars;
    }
    
    /**
     * Handle Cohesion authentication callback
     */
    public function handle_cohesion_callback() {
        $action = get_query_var('cohesion_action');
        
        switch ($action) {
            case 'login':
                $this->handle_login();
                break;
                
            case 'logout':
                $this->handle_logout();
                break;
                
            case 'callback':
                $this->handle_authentication_callback();
                break;
        }
    }
    
    /**
     * Handle login request
     */
    private function handle_login() {
        $auth = new CohesionAuthentication();
        $auth->initiate_login();
    }
    
    /**
     * Handle logout request
     */
    private function handle_logout() {
        $auth = new CohesionAuthentication();
        $auth->initiate_logout();
    }
    
    /**
     * Handle authentication callback
     */
    private function handle_authentication_callback() {
        $auth = new CohesionAuthentication();
        $auth->handle_callback();
    }
    
    /**
     * Add login/logout links to menu
     */
    public function add_login_logout_links($items, $args) {
        // Only add to primary menu
        if ($args->theme_location !== 'primary') {
            return $items;
        }
        
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $logout_url = home_url('/cohesion/logout');
            
            $items .= '<li class="menu-item cohesion-menu-item">';
            $items .= '<a href="' . esc_url($logout_url) . '">';
            $items .= sprintf(__('Logout (%s)', 'integrazione-cohesion'), $current_user->display_name);
            $items .= '</a></li>';
        } else {
            $login_url = home_url('/cohesion/login');
            
            $items .= '<li class="menu-item cohesion-menu-item">';
            $items .= '<a href="' . esc_url($login_url) . '">';
            $items .= __('Login con Cohesion', 'integrazione-cohesion');
            $items .= '</a></li>';
        }
        
        return $items;
    }
    
    /**
     * Login shortcode
     */
    public function login_shortcode($atts) {
        $atts = shortcode_atts(array(
            'button_text' => __('Accedi con Cohesion', 'integrazione-cohesion'),
            'show_spid' => 'true',
            'show_cie' => 'true',
            'redirect' => ''
        ), $atts);
        
        if (is_user_logged_in()) {
            return '<p>' . __('Sei già autenticato.', 'integrazione-cohesion') . '</p>';
        }
        
        $login_url = home_url('/cohesion/login');
        if (!empty($atts['redirect'])) {
            $login_url = add_query_arg('redirect_to', urlencode($atts['redirect']), $login_url);
        }
        
        ob_start();
        ?>
        <div class="cohesion-login-widget">
            <a href="<?php echo esc_url($login_url); ?>" class="button cohesion-login-button">
                <?php echo esc_html($atts['button_text']); ?>
            </a>
            
            <?php if ($atts['show_spid'] === 'true' && get_option('cohesion_enable_spid', true)): ?>
                <p class="cohesion-spid-info">
                    <small><?php _e('Supporta SPID, CIE e altri sistemi di identità digitale', 'integrazione-cohesion'); ?></small>
                </p>
            <?php endif; ?>
        </div>
        
        <style>
        .cohesion-login-widget {
            text-align: center;
            margin: 20px 0;
        }
        .cohesion-login-button {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .cohesion-login-button:hover {
            background: #005a87;
            color: white;
        }
        .cohesion-spid-info {
            margin: 10px 0;
            color: #666;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Logout shortcode
     */
    public function logout_shortcode($atts) {
        $atts = shortcode_atts(array(
            'button_text' => __('Logout', 'integrazione-cohesion'),
            'redirect' => ''
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<p>' . __('Non sei autenticato.', 'integrazione-cohesion') . '</p>';
        }
        
        $logout_url = home_url('/cohesion/logout');
        if (!empty($atts['redirect'])) {
            $logout_url = add_query_arg('redirect_to', urlencode($atts['redirect']), $logout_url);
        }
        
        $current_user = wp_get_current_user();
        
        ob_start();
        ?>
        <div class="cohesion-logout-widget">
            <p><?php printf(__('Benvenuto, %s', 'integrazione-cohesion'), $current_user->display_name); ?></p>
            <a href="<?php echo esc_url($logout_url); ?>" class="button cohesion-logout-button">
                <?php echo esc_html($atts['button_text']); ?>
            </a>
        </div>
        
        <style>
        .cohesion-logout-widget {
            text-align: center;
            margin: 20px 0;
        }
        .cohesion-logout-button {
            background: #dc3232;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .cohesion-logout-button:hover {
            background: #a82727;
            color: white;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Enqueue login page styles
     */
    public function enqueue_login_styles() {
        ?>
        <style>
        .cohesion-login-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .cohesion-login-button {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .cohesion-login-button:hover {
            background: #005a87;
            color: white;
        }
        .cohesion-spid-logos {
            text-align: center;
            margin-top: 10px;
        }
        </style>
        <?php
    }
    
    /**
     * Add Cohesion login button to WordPress login form
     */
    public function add_cohesion_login_button() {
        $login_url = home_url('/cohesion/login');
        if (isset($_GET['redirect_to'])) {
            $login_url = add_query_arg('redirect_to', urlencode($_GET['redirect_to']), $login_url);
        }
        ?>
        
        <div class="cohesion-login-section">
            <h3><?php _e('Accesso con Identità Digitale', 'integrazione-cohesion'); ?></h3>
            
            <a href="<?php echo esc_url($login_url); ?>" class="cohesion-login-button">
                <?php _e('Accedi con Cohesion', 'integrazione-cohesion'); ?>
            </a>
            
            <p style="text-align: center; font-size: 12px; color: #666;">
                <?php _e('Supporta SPID, CIE, eIDAS e sistemi di autenticazione della Regione Marche', 'integrazione-cohesion'); ?>
            </p>
            
            <div class="cohesion-spid-logos">
                <small><?php _e('Sistema di autenticazione certificato', 'integrazione-cohesion'); ?></small>
            </div>
        </div>
        
        <p style="text-align: center; margin: 20px 0;">
            <strong><?php _e('OPPURE', 'integrazione-cohesion'); ?></strong>
        </p>
        <?php
    }
    
    /**
     * Handle WordPress logout
     */
    public function handle_wp_logout() {
        // If user was logged in via Cohesion, redirect to Cohesion logout
        $user_meta = get_user_meta(get_current_user_id(), 'cohesion_authenticated', true);
        if ($user_meta) {
            wp_redirect(home_url('/cohesion/logout'));
            exit;
        }
    }
}
