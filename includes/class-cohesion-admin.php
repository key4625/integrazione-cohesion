<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cohesion Admin Interface
 */
class Cohesion_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_filter('plugin_action_links_' . plugin_basename(COHESION_PLUGIN_PATH . 'integrazione-cohesion.php'), array($this, 'add_plugin_action_links'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Integrazione Cohesion', 'integrazione-cohesion'),
            __('Cohesion', 'integrazione-cohesion'),
            'manage_options',
            'cohesion-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('cohesion_settings', 'cohesion_id_sito');
        register_setting('cohesion_settings', 'cohesion_enable_saml20');
        register_setting('cohesion_settings', 'cohesion_enable_spid');
        register_setting('cohesion_settings', 'cohesion_enable_cie');
        register_setting('cohesion_settings', 'cohesion_enable_eidas');
        register_setting('cohesion_settings', 'cohesion_enable_spid_pro');
        register_setting('cohesion_settings', 'cohesion_spid_pro_purposes');
        register_setting('cohesion_settings', 'cohesion_auth_restriction');
        register_setting('cohesion_settings', 'cohesion_redirect_after_login');
        register_setting('cohesion_settings', 'cohesion_redirect_after_logout');
        register_setting('cohesion_settings', 'cohesion_auto_create_users');
        register_setting('cohesion_settings', 'cohesion_default_role');
        register_setting('cohesion_settings', 'cohesion_send_welcome_email');
        register_setting('cohesion_settings', 'cohesion_fallback_email_domain');
        
        // Add settings sections
        add_settings_section(
            'cohesion_basic_settings',
            __('Configurazione Base', 'integrazione-cohesion'),
            array($this, 'basic_settings_callback'),
            'cohesion_settings'
        );
        
        add_settings_section(
            'cohesion_authentication_settings',
            __('Metodi di Autenticazione', 'integrazione-cohesion'),
            array($this, 'auth_settings_callback'),
            'cohesion_settings'
        );
        
        add_settings_section(
            'cohesion_user_settings',
            __('Gestione Utenti', 'integrazione-cohesion'),
            array($this, 'user_settings_callback'),
            'cohesion_settings'
        );
        
        // Add settings fields
        $this->add_settings_fields();
    }
    
    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        // Basic settings
        add_settings_field(
            'cohesion_id_sito',
            __('ID Sito Cohesion', 'integrazione-cohesion'),
            array($this, 'id_sito_field'),
            'cohesion_settings',
            'cohesion_basic_settings'
        );
        
        add_settings_field(
            'cohesion_enable_saml20',
            __('Abilita SAML 2.0', 'integrazione-cohesion'),
            array($this, 'enable_saml20_field'),
            'cohesion_settings',
            'cohesion_basic_settings'
        );
        
        add_settings_field(
            'cohesion_auth_restriction',
            __('Restrizioni Autenticazione', 'integrazione-cohesion'),
            array($this, 'auth_restriction_field'),
            'cohesion_settings',
            'cohesion_basic_settings'
        );
        
        // Authentication methods
        add_settings_field(
            'cohesion_enable_spid',
            __('Abilita SPID', 'integrazione-cohesion'),
            array($this, 'enable_spid_field'),
            'cohesion_settings',
            'cohesion_authentication_settings'
        );
        
        add_settings_field(
            'cohesion_enable_cie',
            __('Abilita CIE', 'integrazione-cohesion'),
            array($this, 'enable_cie_field'),
            'cohesion_settings',
            'cohesion_authentication_settings'
        );
        
        add_settings_field(
            'cohesion_enable_eidas',
            __('Abilita eIDAS', 'integrazione-cohesion'),
            array($this, 'enable_eidas_field'),
            'cohesion_settings',
            'cohesion_authentication_settings'
        );
        
        add_settings_field(
            'cohesion_enable_spid_pro',
            __('Abilita SPID Professionale', 'integrazione-cohesion'),
            array($this, 'enable_spid_pro_field'),
            'cohesion_settings',
            'cohesion_authentication_settings'
        );
        
        add_settings_field(
            'cohesion_spid_pro_purposes',
            __('SPID Pro Purposes', 'integrazione-cohesion'),
            array($this, 'spid_pro_purposes_field'),
            'cohesion_settings',
            'cohesion_authentication_settings'
        );
        
        // User management
        add_settings_field(
            'cohesion_auto_create_users',
            __('Creazione Automatica Utenti', 'integrazione-cohesion'),
            array($this, 'auto_create_users_field'),
            'cohesion_settings',
            'cohesion_user_settings'
        );
        
        add_settings_field(
            'cohesion_default_role',
            __('Ruolo Predefinito', 'integrazione-cohesion'),
            array($this, 'default_role_field'),
            'cohesion_settings',
            'cohesion_user_settings'
        );
        
        add_settings_field(
            'cohesion_send_welcome_email',
            __('Invia Email di Benvenuto', 'integrazione-cohesion'),
            array($this, 'send_welcome_email_field'),
            'cohesion_settings',
            'cohesion_user_settings'
        );
        
        add_settings_field(
            'cohesion_fallback_email_domain',
            __('Dominio Email Fallback', 'integrazione-cohesion'),
            array($this, 'fallback_email_domain_field'),
            'cohesion_settings',
            'cohesion_user_settings'
        );
        
        add_settings_field(
            'cohesion_redirect_after_login',
            __('Redirect dopo Login', 'integrazione-cohesion'),
            array($this, 'redirect_after_login_field'),
            'cohesion_settings',
            'cohesion_user_settings'
        );
        
        add_settings_field(
            'cohesion_redirect_after_logout',
            __('Redirect dopo Logout', 'integrazione-cohesion'),
            array($this, 'redirect_after_logout_field'),
            'cohesion_settings',
            'cohesion_user_settings'
        );
    }
    
    /**
     * Section callbacks
     */
    public function basic_settings_callback() {
        echo '<p>' . __('Configurazione di base per l\'integrazione con Cohesion.', 'integrazione-cohesion') . '</p>';
    }
    
    public function auth_settings_callback() {
        echo '<p>' . __('Configura i metodi di autenticazione disponibili.', 'integrazione-cohesion') . '</p>';
    }
    
    public function user_settings_callback() {
        echo '<p>' . __('Configura come vengono gestiti gli utenti autenticati tramite Cohesion.', 'integrazione-cohesion') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function id_sito_field() {
        $value = get_option('cohesion_id_sito', 'TEST');
        echo '<input type="text" name="cohesion_id_sito" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('ID sito fornito dalla Regione Marche. Utilizzare "TEST" per l\'ambiente di test.', 'integrazione-cohesion') . '</p>';
    }
    
    public function enable_saml20_field() {
        $value = get_option('cohesion_enable_saml20', true);
        echo '<input type="checkbox" name="cohesion_enable_saml20" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Abilita SAML 2.0 (richiesto per SPID, CIE, eIDAS)', 'integrazione-cohesion') . '</label>';
    }
    
    public function auth_restriction_field() {
        $value = get_option('cohesion_auth_restriction', '0,1,2,3');
        echo '<input type="text" name="cohesion_auth_restriction" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Restrizioni sui metodi di autenticazione (0=User/Pass, 1=User/Pass/PIN, 2=Smart Card, 3=Dominio). Separare con virgole.', 'integrazione-cohesion') . '</p>';
    }
    
    public function enable_spid_field() {
        $value = get_option('cohesion_enable_spid', true);
        echo '<input type="checkbox" name="cohesion_enable_spid" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Abilita autenticazione SPID', 'integrazione-cohesion') . '</label>';
    }
    
    public function enable_cie_field() {
        $value = get_option('cohesion_enable_cie', true);
        echo '<input type="checkbox" name="cohesion_enable_cie" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Abilita autenticazione CIE (Carta d\'Identità Elettronica)', 'integrazione-cohesion') . '</label>';
    }
    
    public function enable_eidas_field() {
        $value = get_option('cohesion_enable_eidas', false);
        echo '<input type="checkbox" name="cohesion_enable_eidas" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Abilita autenticazione eIDAS (identità digitali europee)', 'integrazione-cohesion') . '</label>';
    }
    
    public function enable_spid_pro_field() {
        $value = get_option('cohesion_enable_spid_pro', false);
        echo '<input type="checkbox" name="cohesion_enable_spid_pro" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Abilita SPID Professionale', 'integrazione-cohesion') . '</label>';
    }
    
    public function spid_pro_purposes_field() {
        $value = get_option('cohesion_spid_pro_purposes', array('PF'));
        $purposes = array(
            'PF' => 'SPID per Persone Fisiche ad Uso Professionale',
            'PG' => 'SPID per Persone Giuridiche',
            'LP' => 'SPID per Rappresentanti Legali',
            'PX' => 'SPID per Altri Usi Professionali'
        );
        
        echo '<fieldset>';
        foreach ($purposes as $key => $label) {
            $checked = in_array($key, $value) ? 'checked' : '';
            echo '<label><input type="checkbox" name="cohesion_spid_pro_purposes[]" value="' . esc_attr($key) . '" ' . $checked . ' /> ' . esc_html($label) . '</label><br>';
        }
        echo '</fieldset>';
    }
    
    public function auto_create_users_field() {
        $value = get_option('cohesion_auto_create_users', true);
        echo '<input type="checkbox" name="cohesion_auto_create_users" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Crea automaticamente gli utenti WordPress per i nuovi utenti Cohesion', 'integrazione-cohesion') . '</label>';
    }
    
    public function default_role_field() {
        $value = get_option('cohesion_default_role', 'subscriber');
        $roles = get_editable_roles();
        
        echo '<select name="cohesion_default_role">';
        foreach ($roles as $role_key => $role) {
            $selected = selected($role_key, $value, false);
            echo '<option value="' . esc_attr($role_key) . '" ' . $selected . '>' . esc_html($role['name']) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Ruolo assegnato ai nuovi utenti creati automaticamente.', 'integrazione-cohesion') . '</p>';
    }
    
    public function send_welcome_email_field() {
        $value = get_option('cohesion_send_welcome_email', false);
        echo '<input type="checkbox" name="cohesion_send_welcome_email" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Invia email di benvenuto ai nuovi utenti', 'integrazione-cohesion') . '</label>';
    }
    
    public function fallback_email_domain_field() {
        $value = get_option('cohesion_fallback_email_domain', parse_url(home_url(), PHP_URL_HOST));
        echo '<input type="text" name="cohesion_fallback_email_domain" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Dominio utilizzato per generare email temporanee per utenti senza email.', 'integrazione-cohesion') . '</p>';
    }
    
    public function redirect_after_login_field() {
        $value = get_option('cohesion_redirect_after_login', home_url());
        echo '<input type="url" name="cohesion_redirect_after_login" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('URL di destinazione dopo il login (lasciare vuoto per homepage).', 'integrazione-cohesion') . '</p>';
    }
    
    public function redirect_after_logout_field() {
        $value = get_option('cohesion_redirect_after_logout', home_url());
        echo '<input type="url" name="cohesion_redirect_after_logout" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('URL di destinazione dopo il logout (lasciare vuoto per homepage).', 'integrazione-cohesion') . '</p>';
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        if (isset($_GET['tab'])) {
            $active_tab = $_GET['tab'];
        } else {
            $active_tab = 'settings';
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Integrazione Cohesion', 'integrazione-cohesion'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=cohesion-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Impostazioni', 'integrazione-cohesion'); ?>
                </a>
                <a href="?page=cohesion-settings&tab=users" class="nav-tab <?php echo $active_tab == 'users' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Utenti Cohesion', 'integrazione-cohesion'); ?>
                </a>
                <a href="?page=cohesion-settings&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Log Accessi', 'integrazione-cohesion'); ?>
                </a>
                <a href="?page=cohesion-settings&tab=help" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Aiuto', 'integrazione-cohesion'); ?>
                </a>
            </h2>
            
            <?php
            switch ($active_tab) {
                case 'settings':
                    $this->settings_tab();
                    break;
                case 'users':
                    $this->users_tab();
                    break;
                case 'logs':
                    $this->logs_tab();
                    break;
                case 'help':
                    $this->help_tab();
                    break;
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Settings tab
     */
    private function settings_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('cohesion_settings');
            do_settings_sections('cohesion_settings');
            submit_button();
            ?>
        </form>
        
        <div class="cohesion-test-section">
            <h3><?php _e('Test Integrazione', 'integrazione-cohesion'); ?></h3>
            <p><?php _e('Utilizza questi link per testare l\'integrazione:', 'integrazione-cohesion'); ?></p>
            <p>
                <a href="<?php echo home_url('/cohesion/login'); ?>" class="button" target="_blank">
                    <?php _e('Test Login', 'integrazione-cohesion'); ?>
                </a>
                <a href="<?php echo home_url('/cohesion/logout'); ?>" class="button" target="_blank">
                    <?php _e('Test Logout', 'integrazione-cohesion'); ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Users tab
     */
    private function users_tab() {
        $cohesion_users = get_users(array(
            'meta_key' => 'cohesion_user',
            'meta_value' => true
        ));
        ?>
        <h3><?php _e('Utenti autenticati tramite Cohesion', 'integrazione-cohesion'); ?></h3>
        
        <?php if (empty($cohesion_users)): ?>
            <p><?php _e('Nessun utente autenticato tramite Cohesion trovato.', 'integrazione-cohesion'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Utente', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Email', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Codice Fiscale', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Ultimo Login', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Tipo Auth', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Azioni', 'integrazione-cohesion'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cohesion_users as $user): ?>
                        <?php
                        $fiscal_code = get_user_meta($user->ID, 'cohesion_fiscal_code', true);
                        $last_login = get_user_meta($user->ID, 'cohesion_last_login', true);
                        $auth_type = get_user_meta($user->ID, 'cohesion_auth_type', true);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <small><?php echo esc_html($user->user_login); ?></small>
                            </td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html($fiscal_code); ?></td>
                            <td><?php echo $last_login ? esc_html($last_login) : '-'; ?></td>
                            <td><?php echo esc_html($auth_type); ?></td>
                            <td>
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>" class="button-small">
                                    <?php _e('Modifica', 'integrazione-cohesion'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Logs tab
     */
    private function logs_tab() {
        $logs = get_option('cohesion_login_logs', array());
        $logs = array_reverse($logs); // Show newest first
        ?>
        <h3><?php _e('Log degli accessi Cohesion', 'integrazione-cohesion'); ?></h3>
        
        <?php if (empty($logs)): ?>
            <p><?php _e('Nessun log di accesso disponibile.', 'integrazione-cohesion'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Data/Ora', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Utente', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('IP', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('Tipo Auth', 'integrazione-cohesion'); ?></th>
                        <th><?php _e('User Agent', 'integrazione-cohesion'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($logs, 0, 50) as $log): // Show only last 50 logs ?>
                        <tr>
                            <td><?php echo esc_html($log['login_time']); ?></td>
                            <td><?php echo esc_html($log['username']); ?></td>
                            <td><?php echo esc_html($log['ip_address']); ?></td>
                            <td><?php echo esc_html($log['authentication_type']); ?></td>
                            <td style="max-width: 200px; word-wrap: break-word;">
                                <small><?php echo esc_html(substr($log['user_agent'], 0, 100)); ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p>
                <button type="button" class="button" onclick="if(confirm('<?php _e('Sei sicuro di voler cancellare tutti i log?', 'integrazione-cohesion'); ?>')) { window.location.href='<?php echo wp_nonce_url(admin_url('options-general.php?page=cohesion-settings&tab=logs&action=clear_logs'), 'clear_cohesion_logs'); ?>'; }">
                    <?php _e('Cancella tutti i log', 'integrazione-cohesion'); ?>
                </button>
            </p>
        <?php endif; ?>
        
        <?php
        // Handle clear logs action
        if (isset($_GET['action']) && $_GET['action'] === 'clear_logs' && wp_verify_nonce($_GET['_wpnonce'], 'clear_cohesion_logs')) {
            delete_option('cohesion_login_logs');
            echo '<div class="notice notice-success"><p>' . __('Log cancellati con successo.', 'integrazione-cohesion') . '</p></div>';
        }
    }
    
    /**
     * Help tab
     */
    private function help_tab() {
        ?>
        <h3><?php _e('Aiuto e Documentazione', 'integrazione-cohesion'); ?></h3>
        
        <div class="cohesion-help-section">
            <h4><?php _e('Configurazione Iniziale', 'integrazione-cohesion'); ?></h4>
            <ol>
                <li><?php _e('Richiedere l\'integrazione a Cohesion tramite il portale della Regione Marche', 'integrazione-cohesion'); ?></li>
                <li><?php _e('Ottenere l\'ID Sito univoco dalla Regione Marche', 'integrazione-cohesion'); ?></li>
                <li><?php _e('Inserire l\'ID Sito nella configurazione del plugin', 'integrazione-cohesion'); ?></li>
                <li><?php _e('Configurare i metodi di autenticazione desiderati', 'integrazione-cohesion'); ?></li>
                <li><?php _e('Testare l\'integrazione utilizzando i link di test', 'integrazione-cohesion'); ?></li>
            </ol>
        </div>
        
        <div class="cohesion-help-section">
            <h4><?php _e('Shortcode Disponibili', 'integrazione-cohesion'); ?></h4>
            <p><strong>[cohesion_login]</strong> - <?php _e('Mostra il pulsante di login Cohesion', 'integrazione-cohesion'); ?></p>
            <p><strong>[cohesion_logout]</strong> - <?php _e('Mostra il pulsante di logout per utenti autenticati', 'integrazione-cohesion'); ?></p>
            
            <h5><?php _e('Parametri shortcode login:', 'integrazione-cohesion'); ?></h5>
            <ul>
                <li><code>button_text</code> - <?php _e('Testo del pulsante', 'integrazione-cohesion'); ?></li>
                <li><code>redirect</code> - <?php _e('URL di destinazione dopo il login', 'integrazione-cohesion'); ?></li>
                <li><code>show_spid</code> - <?php _e('Mostra informazioni SPID (true/false)', 'integrazione-cohesion'); ?></li>
            </ul>
        </div>
        
        <div class="cohesion-help-section">
            <h4><?php _e('URL Endpoint', 'integrazione-cohesion'); ?></h4>
            <ul>
                <li><strong>Login:</strong> <code><?php echo home_url('/cohesion/login'); ?></code></li>
                <li><strong>Logout:</strong> <code><?php echo home_url('/cohesion/logout'); ?></code></li>
                <li><strong>Callback:</strong> <code><?php echo home_url('/cohesion/callback'); ?></code></li>
            </ul>
        </div>
        
        <div class="cohesion-help-section">
            <h4><?php _e('Supporto', 'integrazione-cohesion'); ?></h4>
            <p><?php _e('Per supporto tecnico sull\'integrazione Cohesion:', 'integrazione-cohesion'); ?></p>
            <ul>
                <li><?php _e('Email:', 'integrazione-cohesion'); ?> <a href="mailto:integrazioneCohesion@regione.marche.it">integrazioneCohesion@regione.marche.it</a></li>
                <li><?php _e('Documentazione:', 'integrazione-cohesion'); ?> <a href="https://cohesion.regione.marche.it/CohesionDocs/" target="_blank">https://cohesion.regione.marche.it/CohesionDocs/</a></li>
            </ul>
        </div>
        
        <div class="cohesion-help-section">
            <h4><?php _e('Requisiti Sistema', 'integrazione-cohesion'); ?></h4>
            <ul>
                <li>PHP 7.4+</li>
                <li>WordPress 5.0+</li>
                <li>allow_url_fopen = On nel php.ini</li>
                <li>Estensioni PHP: openssl, dom, libxml</li>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_cohesion-settings') {
            return;
        }
        
        wp_enqueue_style('cohesion-admin', COHESION_PLUGIN_URL . 'assets/admin.css', array(), COHESION_PLUGIN_VERSION);
    }
    
    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=cohesion-settings') . '">' . __('Impostazioni', 'integrazione-cohesion') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
