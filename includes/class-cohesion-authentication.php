<?php
/**
 * Gestione dell'autenticazione Cohesion
 *
 * @package IntegrazioneCohesion
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class Cohesion_Authentication {
    
    private $config;
    private $user_manager;
    
    public function __construct() {
        $this->config = new Cohesion_Config();
        $this->user_manager = new Cohesion_User_Manager();
        
        // Hook per l'inizializzazione AJAX
        add_action('wp_ajax_cohesion_login', array($this, 'handle_ajax_login'));
        add_action('wp_ajax_nopriv_cohesion_login', array($this, 'handle_ajax_login'));
        
        // Hook per il callback di ritorno da Cohesion
        add_action('wp_ajax_cohesion_callback', array($this, 'handle_callback'));
        add_action('wp_ajax_nopriv_cohesion_callback', array($this, 'handle_callback'));
        
        // Hook per il logout
        add_action('wp_ajax_cohesion_logout', array($this, 'handle_logout'));
        add_action('wp_ajax_nopriv_cohesion_logout', array($this, 'handle_logout'));
    }
    
    /**
     * Gestisce la richiesta AJAX di login
     */
    public function handle_ajax_login() {
        try {
            // Verifica nonce per sicurezza
            if (!wp_verify_nonce($_POST['nonce'], 'cohesion_login_nonce')) {
                throw new Exception('Nonce verification failed');
            }
            
            // Log per debug
            error_log('Cohesion Login: Starting login process');
            
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Includi l'autoloader di Composer
            $autoload_path = plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            if (!file_exists($autoload_path)) {
                throw new Exception('Autoloader Composer non trovato: ' . $autoload_path);
            }
            require_once $autoload_path;
            
            // Verifica che la classe Cohesion2 sia disponibile
            if (!class_exists('Cohesion2')) {
                throw new Exception('Classe Cohesion2 non trovata');
            }
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Configura i parametri
            $config = $this->config->get_all_settings();
            
            // Configura la libreria Cohesion2
            if (!empty($config['cohesion_certificate_path'])) {
                $cohesion->setCertificate($config['cohesion_certificate_path'], $config['cohesion_key_path']);
            }
            
            // Configura SSO e SAML2.0
            $cohesion->useSSO(true);
            if ($config['cohesion_use_saml20']) {
                $cohesion->useSAML20(true);
            }
            
            // Configura restrizioni di autenticazione
            if (!empty($config['cohesion_auth_restriction'])) {
                $cohesion->setAuthRestriction($config['cohesion_auth_restriction']);
            }
            
            error_log('Cohesion Login: Config = ' . print_r($config, true));
            
            // La libreria Cohesion2 gestisce il redirect automaticamente
            // Non restituisce un URL ma fa il redirect direttamente
            $cohesion->auth();
            
            // Se arriviamo qui, significa che l'utente è già autenticato
            error_log('Cohesion Login: User already authenticated');
            
            wp_send_json_success(array(
                'message' => 'Utente già autenticato',
                'redirect_url' => admin_url()
            ));
            
        } catch (Exception $e) {
            error_log('Cohesion Login Error: ' . $e->getMessage());
            error_log('Cohesion Login Stack Trace: ' . $e->getTraceAsString());
            
            wp_send_json_error(array(
                'message' => 'Errore durante l\'inizializzazione del login: ' . $e->getMessage(),
                'debug_info' => array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'autoload_path' => $autoload_path ?? 'N/A',
                    'autoload_exists' => file_exists(plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php'),
                    'cohesion2_exists' => class_exists('Cohesion2', false),
                    'cohesion2_file' => plugin_dir_path(dirname(__FILE__)) . 'vendor/andreaval/cohesion2-library/cohesion2/Cohesion2.php',
                    'cohesion2_file_exists' => file_exists(plugin_dir_path(dirname(__FILE__)) . 'vendor/andreaval/cohesion2-library/cohesion2/Cohesion2.php'),
                    'session_status' => session_status(),
                    'php_version' => PHP_VERSION,
                    'composer_autoload' => file_exists(plugin_dir_path(dirname(__FILE__)) . 'vendor/composer/autoload_classmap.php') ? 'Present' : 'Missing'
                )
            ));
        }
        
        wp_die();
    }
    
    /**
     * Gestisce il callback di ritorno da Cohesion
     * Con la libreria Cohesion2, il callback viene gestito automaticamente dal metodo auth()
     */
    public function handle_callback() {
        try {
            error_log('Cohesion Callback: Starting callback handling');
            error_log('Cohesion Callback: GET data = ' . print_r($_GET, true));
            error_log('Cohesion Callback: POST data = ' . print_r($_POST, true));
            
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Includi l'autoloader di Composer
            require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Il metodo auth() gestisce automaticamente il callback
            $cohesion->auth();
            
            // Verifica se l'utente è autenticato
            if ($cohesion->isAuth()) {
                error_log('Cohesion Callback: User authenticated');
                
                // Recupera i dati dell'utente dalla sessione Cohesion
                $user_data = $this->extract_user_data_from_cohesion($cohesion);
                
                if (empty($user_data)) {
                    throw new Exception('Nessun dato utente disponibile dalla sessione Cohesion');
                }
                
                error_log('Cohesion Callback: User data = ' . print_r($user_data, true));
                
                // Crea o aggiorna l'utente WordPress
                $wp_user = $this->user_manager->create_or_update_user($user_data);
                
                if (is_wp_error($wp_user)) {
                    throw new Exception('Errore nella creazione utente: ' . $wp_user->get_error_message());
                }
                
                // Effettua il login
                wp_set_current_user($wp_user->ID);
                wp_set_auth_cookie($wp_user->ID, true);
                
                // Redirect alla dashboard o pagina specificata
                $redirect_url = isset($_SESSION['cohesion_redirect']) ? $_SESSION['cohesion_redirect'] : admin_url();
                unset($_SESSION['cohesion_redirect']);
                
                error_log('Cohesion Callback: Login successful, redirecting to ' . $redirect_url);
                
                wp_redirect($redirect_url);
                exit;
            } else {
                throw new Exception('Autenticazione Cohesion non riuscita');
            }
            
        } catch (Exception $e) {
            error_log('Cohesion Callback Error: ' . $e->getMessage());
            error_log('Cohesion Callback Stack Trace: ' . $e->getTraceAsString());
            
            // Redirect con messaggio di errore
            $error_url = home_url('?cohesion_error=' . urlencode($e->getMessage()));
            wp_redirect($error_url);
            exit;
        }
    }
    
    /**
     * Estrae i dati dell'utente dalla sessione Cohesion
     */
    private function extract_user_data_from_cohesion($cohesion) {
        $user_data = array();
        
        // La libreria Cohesion2 espone i dati utente attraverso le proprietà pubbliche
        if ($cohesion->username && $cohesion->profile) {
            $profile = $cohesion->profile;
            
            // Mappa i dati dal profilo Cohesion alla struttura attesa dal user manager
            $user_data = array(
                'username' => $cohesion->username,
                'user_login' => $cohesion->username,
                'nome' => $profile['nome'] ?? '',
                'cognome' => $profile['cognome'] ?? '',
                'email' => $profile['email'] ?? $profile['email_certificata'] ?? '',
                'codice_fiscale' => $profile['codice_fiscale'] ?? '',
                'spid_code' => $profile['spid_code'] ?? '',
                'tipo_autenticazione' => $profile['tipo_autenticazione'] ?? '',
                'data_nascita' => $profile['data_nascita'] ?? '',
                'localita_nascita' => $profile['localita_nascita'] ?? '',
                'sesso' => $profile['sesso'] ?? '',
                'telefono' => $profile['telefono'] ?? '',
                'indirizzo_residenza' => $profile['indirizzo_residenza'] ?? '',
                'localita_residenza' => $profile['localita_residenza'] ?? '',
                'provincia_residenza' => $profile['provincia_residenza'] ?? '',
                'cap_residenza' => $profile['cap_residenza'] ?? '',
                'regione_residenza' => $profile['regione_residenza'] ?? '',
                'nazione_residenza' => $profile['nazione_residenza'] ?? '',
                'professione' => $profile['professione'] ?? '',
                'settore_azienda' => $profile['settore_azienda'] ?? '',
                'profilo_familiare' => $profile['profilo_familiare'] ?? '',
                'full_profile' => $profile // Salva il profilo completo per reference
            );
        }
        
        return $user_data;
    }
    
    /**
     * Gestisce il logout da Cohesion
     */
    public function handle_logout() {
        try {
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Includi l'autoloader di Composer
            require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Effettua il logout da WordPress
            wp_logout();
            
            // Effettua il logout da Cohesion (se supportato dalla libreria)
            if (method_exists($cohesion, 'logout')) {
                $logout_url = $cohesion->logout(home_url());
                wp_send_json_success(array('redirect_url' => $logout_url));
            } else {
                wp_send_json_success(array('redirect_url' => home_url()));
            }
            
        } catch (Exception $e) {
            error_log('Cohesion Logout Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Errore durante il logout: ' . $e->getMessage()));
        }
        
        wp_die();
    }
    
    /**
     * Inizia il processo di login (per chiamate dirette URL)
     */
    public function initiate_login($redirect_to = null) {
        try {
            // Log per debug
            error_log('Cohesion Login: Starting login process (direct URL)');
            
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Salva l'URL di redirect se fornito
            if ($redirect_to) {
                $_SESSION['cohesion_redirect'] = $redirect_to;
            }
            
            // Includi l'autoloader di Composer
            $autoload_path = plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            if (!file_exists($autoload_path)) {
                throw new Exception('Autoloader Composer non trovato: ' . $autoload_path);
            }
            require_once $autoload_path;
            
            // Verifica che la classe Cohesion2 sia disponibile
            if (!class_exists('Cohesion2')) {
                throw new Exception('Classe Cohesion2 non trovata');
            }
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Configura i parametri
            $config = $this->config->get_all_settings();
            
            // Configura la libreria Cohesion2
            if (!empty($config['cohesion_certificate_path'])) {
                $cohesion->setCertificate($config['cohesion_certificate_path'], $config['cohesion_key_path']);
            }
            
            // Configura SSO e SAML2.0
            $cohesion->useSSO(true);
            if ($config['cohesion_use_saml20']) {
                $cohesion->useSAML20(true);
            }
            
            // Configura restrizioni di autenticazione
            if (!empty($config['cohesion_auth_restriction'])) {
                $cohesion->setAuthRestriction($config['cohesion_auth_restriction']);
            }
            
            error_log('Cohesion Login: Config = ' . print_r($config, true));
            
            // La libreria Cohesion2 gestisce il redirect automaticamente
            // Non restituisce un URL ma fa il redirect direttamente
            $cohesion->auth();
            
            // Se arriviamo qui, significa che l'utente è già autenticato
            // Recupera i dati dell'utente e crea/aggiorna l'account WordPress
            if ($cohesion->isAuth()) {
                error_log('Cohesion Login: User authenticated, processing...');
                
                // Qui dovremmo recuperare i dati dell'utente e creare/aggiornare l'account
                // La libreria Cohesion2 dovrebbe fornire i metodi per ottenere i dati dell'utente
                
                // Per ora, redirect alla home
                wp_redirect(home_url());
                exit;
            } else {
                throw new Exception('Autenticazione Cohesion fallita');
            }
            
        } catch (Exception $e) {
            error_log('Cohesion Login Error: ' . $e->getMessage());
            error_log('Cohesion Login Stack Trace: ' . $e->getTraceAsString());
            
            // Redirect con messaggio di errore
            $error_url = home_url('?cohesion_error=' . urlencode($e->getMessage()));
            wp_redirect($error_url);
            exit;
        }
    }
    
    /**
     * Inizia il processo di logout (per chiamate dirette URL)
     */
    public function initiate_logout() {
        try {
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Includi l'autoloader di Composer
            require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Effettua il logout da WordPress
            wp_logout();
            
            // Effettua il logout da Cohesion (se supportato dalla libreria)
            if (method_exists($cohesion, 'logout')) {
                $logout_url = $cohesion->logout(home_url());
                wp_redirect($logout_url);
            } else {
                wp_redirect(home_url());
            }
            exit;
            
        } catch (Exception $e) {
            error_log('Cohesion Logout Error: ' . $e->getMessage());
            wp_redirect(home_url());
            exit;
        }
    }

    /**
     * Verifica se un utente è autenticato tramite Cohesion
     */
    public function is_cohesion_user($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return get_user_meta($user_id, 'cohesion_authenticated', true) === 'yes';
    }
    
    /**
     * Ottieni informazioni sull'utente Cohesion
     */
    public function get_cohesion_user_info($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return array(
            'is_cohesion_user' => $this->is_cohesion_user($user_id),
            'cohesion_id' => get_user_meta($user_id, 'cohesion_user_id', true),
            'fiscal_code' => get_user_meta($user_id, 'cohesion_fiscal_code', true),
            'spid_code' => get_user_meta($user_id, 'cohesion_spid_code', true),
            'last_login' => get_user_meta($user_id, 'cohesion_last_login', true)
        );
    }
}
