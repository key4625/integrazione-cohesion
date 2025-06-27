<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cohesion User Manager
 * Handles user creation and management for Cohesion authenticated users
 */
class CohesionUserManager {
    
    /**
     * Get or create WordPress user from Cohesion profile
     */
    public function get_or_create_user($profile, $username) {
        // First, try to find existing user by Cohesion username
        $existing_user = $this->find_existing_user($profile, $username);
        
        if ($existing_user) {
            // Update existing user with latest profile data
            $this->update_user_profile($existing_user, $profile);
            return $existing_user;
        }
        
        // Check if auto-creation is enabled
        if (!get_option('cohesion_auto_create_users', true)) {
            return new WP_Error('user_creation_disabled', __('La creazione automatica degli utenti è disabilitata.', 'integrazione-cohesion'));
        }
        
        // Create new user
        return $this->create_new_user($profile, $username);
    }
    
    /**
     * Find existing user by various criteria
     */
    private function find_existing_user($profile, $username) {
        // First, try to find by Cohesion username
        $user = get_user_by('login', $username);
        if ($user) {
            return $user;
        }
        
        // Try to find by email
        $email = $this->extract_email($profile);
        if ($email) {
            $user = get_user_by('email', $email);
            if ($user) {
                return $user;
            }
        }
        
        // Try to find by fiscal code (stored in user meta)
        $fiscal_code = $this->extract_fiscal_code($profile);
        if ($fiscal_code) {
            $users = get_users(array(
                'meta_key' => 'cohesion_fiscal_code',
                'meta_value' => $fiscal_code,
                'number' => 1
            ));
            
            if (!empty($users)) {
                return $users[0];
            }
        }
        
        return null;
    }
    
    /**
     * Create new WordPress user from Cohesion profile
     */
    private function create_new_user($profile, $username) {
        // Extract user data from profile
        $user_data = $this->extract_user_data($profile, $username);
        
        // Validate required data
        if (empty($user_data['user_login']) || empty($user_data['user_email'])) {
            return new WP_Error('invalid_user_data', __('Dati utente insufficienti per la creazione dell\'account.', 'integrazione-cohesion'));
        }
        
        // Check if username already exists (shouldn't happen, but double-check)
        if (username_exists($user_data['user_login'])) {
            // Generate alternative username
            $user_data['user_login'] = $this->generate_unique_username($user_data['user_login']);
        }
        
        // Check if email already exists
        if (email_exists($user_data['user_email'])) {
            return new WP_Error('email_exists', __('Un utente con questa email esiste già.', 'integrazione-cohesion'));
        }
        
        // Create user
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Get the created user
        $user = get_user_by('id', $user_id);
        
        // Store Cohesion-specific metadata
        $this->store_cohesion_metadata($user, $profile);
        
        // Send notification email if enabled
        if (get_option('cohesion_send_welcome_email', false)) {
            wp_new_user_notification($user_id, null, 'user');
        }
        
        // Hook for other plugins
        do_action('cohesion_user_created', $user, $profile);
        
        return $user;
    }
    
    /**
     * Extract user data from Cohesion profile
     */
    private function extract_user_data($profile, $username) {
        $first_name = $this->extract_first_name($profile);
        $last_name = $this->extract_last_name($profile);
        $email = $this->extract_email($profile);
        
        // Generate display name
        $display_name = trim($first_name . ' ' . $last_name);
        if (empty($display_name)) {
            $display_name = $username;
        }
        
        // Generate email if not available
        if (empty($email)) {
            $email = $this->generate_email($username);
        }
        
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
            'user_pass' => wp_generate_password(32), // Random password (user won't use it)
            'role' => get_option('cohesion_default_role', 'subscriber'),
            'show_admin_bar_front' => false
        );
        
        return $user_data;
    }
    
    /**
     * Extract first name from profile
     */
    private function extract_first_name($profile) {
        // SAML 2.0 format
        if (isset($profile['name'])) {
            return sanitize_text_field($profile['name']);
        }
        
        // Traditional format
        if (isset($profile['nome'])) {
            return sanitize_text_field($profile['nome']);
        }
        
        return '';
    }
    
    /**
     * Extract last name from profile
     */
    private function extract_last_name($profile) {
        // SAML 2.0 format
        if (isset($profile['familyName'])) {
            return sanitize_text_field($profile['familyName']);
        }
        
        // Traditional format
        if (isset($profile['cognome'])) {
            return sanitize_text_field($profile['cognome']);
        }
        
        return '';
    }
    
    /**
     * Extract email from profile
     */
    private function extract_email($profile) {
        // SAML 2.0 format
        if (isset($profile['email']) && is_email($profile['email'])) {
            return sanitize_email($profile['email']);
        }
        
        // Certified email
        if (isset($profile['email_certificata']) && is_email($profile['email_certificata'])) {
            return sanitize_email($profile['email_certificata']);
        }
        
        // Digital address
        if (isset($profile['digitalAddress']) && is_email($profile['digitalAddress'])) {
            return sanitize_email($profile['digitalAddress']);
        }
        
        return '';
    }
    
    /**
     * Extract fiscal code from profile
     */
    private function extract_fiscal_code($profile) {
        // SAML 2.0 format (remove TINIT- prefix if present)
        if (isset($profile['fiscalNumber'])) {
            $fiscal_code = $profile['fiscalNumber'];
            if (strpos($fiscal_code, 'TINIT-') === 0) {
                $fiscal_code = substr($fiscal_code, 6);
            }
            return strtoupper(sanitize_text_field($fiscal_code));
        }
        
        // Traditional format
        if (isset($profile['codice_fiscale'])) {
            return strtoupper(sanitize_text_field($profile['codice_fiscale']));
        }
        
        return '';
    }
    
    /**
     * Generate unique username
     */
    private function generate_unique_username($base_username) {
        $username = $base_username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . '_' . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Generate email for user without email
     */
    private function generate_email($username) {
        $domain = get_option('cohesion_fallback_email_domain', parse_url(home_url(), PHP_URL_HOST));
        return $username . '@' . $domain;
    }
    
    /**
     * Store Cohesion-specific metadata
     */
    private function store_cohesion_metadata($user, $profile) {
        // Store full profile
        update_user_meta($user->ID, 'cohesion_profile', $profile);
        
        // Store specific fields for easy access
        $fiscal_code = $this->extract_fiscal_code($profile);
        if ($fiscal_code) {
            update_user_meta($user->ID, 'cohesion_fiscal_code', $fiscal_code);
        }
        
        // Store authentication type
        if (isset($profile['tipo_autenticazione'])) {
            update_user_meta($user->ID, 'cohesion_auth_type', $profile['tipo_autenticazione']);
        }
        
        // Store birth date
        if (isset($profile['data_nascita'])) {
            update_user_meta($user->ID, 'cohesion_birth_date', $profile['data_nascita']);
        }
        
        // Store birth place
        if (isset($profile['localita_nascita'])) {
            update_user_meta($user->ID, 'cohesion_birth_place', $profile['localita_nascita']);
        }
        
        // Store gender
        if (isset($profile['gender']) || isset($profile['sesso'])) {
            $gender = isset($profile['gender']) ? $profile['gender'] : $profile['sesso'];
            update_user_meta($user->ID, 'cohesion_gender', $gender);
        }
        
        // Store SPID code if available
        if (isset($profile['spidCode'])) {
            update_user_meta($user->ID, 'cohesion_spid_code', $profile['spidCode']);
        }
        
        // Mark as Cohesion user
        update_user_meta($user->ID, 'cohesion_user', true);
        update_user_meta($user->ID, 'cohesion_created_date', current_time('mysql'));
    }
    
    /**
     * Update existing user profile with latest data
     */
    private function update_user_profile($user, $profile) {
        $updated = false;
        
        // Update basic info
        $first_name = $this->extract_first_name($profile);
        $last_name = $this->extract_last_name($profile);
        
        if ($first_name && $user->first_name !== $first_name) {
            wp_update_user(array('ID' => $user->ID, 'first_name' => $first_name));
            $updated = true;
        }
        
        if ($last_name && $user->last_name !== $last_name) {
            wp_update_user(array('ID' => $user->ID, 'last_name' => $last_name));
            $updated = true;
        }
        
        // Update display name if both first and last names are available
        if ($first_name && $last_name) {
            $display_name = trim($first_name . ' ' . $last_name);
            if ($user->display_name !== $display_name) {
                wp_update_user(array('ID' => $user->ID, 'display_name' => $display_name));
                $updated = true;
            }
        }
        
        // Update email if not set or different
        $email = $this->extract_email($profile);
        if ($email && $user->user_email !== $email && !email_exists($email)) {
            wp_update_user(array('ID' => $user->ID, 'user_email' => $email));
            $updated = true;
        }
        
        // Always update the profile metadata
        $this->store_cohesion_metadata($user, $profile);
        
        if ($updated) {
            do_action('cohesion_user_updated', $user, $profile);
        }
    }
    
    /**
     * Get user's Cohesion profile
     */
    public function get_user_cohesion_profile($user_id) {
        return get_user_meta($user_id, 'cohesion_profile', true);
    }
    
    /**
     * Check if user was created via Cohesion
     */
    public function is_cohesion_user($user_id) {
        return (bool) get_user_meta($user_id, 'cohesion_user', true);
    }
    
    /**
     * Get user's fiscal code
     */
    public function get_user_fiscal_code($user_id) {
        return get_user_meta($user_id, 'cohesion_fiscal_code', true);
    }
}
