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
            
            // Includi la libreria Cohesion2 locale
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php';
            
            // Verifica che la classe Cohesion2 sia disponibile
            if (!class_exists('Cohesion2')) {
                throw new Exception('Classe Cohesion2 non trovata');
            }
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Configura i parametri
            $config = $this->config->get_all_settings();
            $id_sito = $config['cohesion_id_sito'] ?? 'TEST';
            
            // Configura l'ID Sito personalizzato (ora funziona!)
            $cohesion->setIdSito($id_sito);
            
            // Configura l'istanza di Cohesion2 per supportare SPID/CIE
            $this->configure_cohesion_for_spid_cie($cohesion, $id_sito);
            
            error_log("Cohesion Login: Config = " . print_r($config, true));
            
            // Ora possiamo usare direttamente auth() invece del metodo personalizzato
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
                    'library_path' => plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php',
                    'library_exists' => file_exists(plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php'),
                    'cohesion2_exists' => class_exists('Cohesion2', false),
                    'session_status' => session_status(),
                    'php_version' => PHP_VERSION,
                    'local_library' => 'Using local Cohesion2 library (no Composer required)'
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
            
            // Pulisci l'output buffer per evitare problemi
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Includi la libreria Cohesion2 locale
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php';
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Configura l'ID Sito anche nel callback
            $config = $this->config->get_all_settings();
            $id_sito = $config['cohesion_id_sito'] ?? 'TEST';
            $cohesion->setIdSito($id_sito);
            
            // Il metodo auth() gestisce automaticamente il callback quando riceve auth= parameter
            $cohesion->auth();
            
            // Verifica se l'utente è autenticato
            if ($cohesion->isAuth()) {
                error_log('Cohesion Callback: User authenticated successfully');
                
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
                throw new Exception('Autenticazione Cohesion non riuscita - utente non autenticato dopo callback');
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
            
            // Carica la libreria Cohesion2 locale
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php';
            
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
            
            // IMPORTANTE: Pulisci l'output buffer prima del redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Avvia la sessione se non già attiva
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Salva l'URL di redirect se fornito
            if ($redirect_to) {
                $_SESSION['cohesion_redirect'] = $redirect_to;
            }
            
            // Salva anche il redirect_to dalla query string
            if (isset($_GET['redirect_to'])) {
                $_SESSION['cohesion_redirect'] = $_GET['redirect_to'];
            }
            
            // Includi la libreria Cohesion2 locale
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php';
            
            // Verifica che la classe Cohesion2 sia disponibile
            if (!class_exists('Cohesion2')) {
                throw new Exception('Classe Cohesion2 non trovata');
            }
            
            // Inizializza Cohesion2
            $cohesion = new Cohesion2();
            
            // Configura i parametri
            $config = $this->config->get_all_settings();
            $id_sito = $config['cohesion_id_sito'] ?? 'TEST';
            
            // Configura l'ID Sito personalizzato
            $cohesion->setIdSito($id_sito);
            
            // Configura l'istanza di Cohesion2 per supportare SPID/CIE
            $this->configure_cohesion_for_spid_cie($cohesion, $id_sito);
            
            error_log("Cohesion Login: Starting auth with ID Sito: $id_sito");
            
            // IMPORTANTE: Controlla se l'utente è già autenticato PRIMA di fare il redirect
            if ($cohesion->isAuth()) {
                error_log('Cohesion Login: User already authenticated, processing user data...');
                
                // Recupera i dati dell'utente e crea/aggiorna l'account WordPress
                $user_data = $this->extract_user_data_from_cohesion($cohesion);
                
                if (!empty($user_data)) {
                    // Crea o aggiorna l'utente WordPress
                    $wp_user = $this->user_manager->create_or_update_user($user_data);
                    
                    if (!is_wp_error($wp_user)) {
                        // Effettua il login
                        wp_set_current_user($wp_user->ID);
                        wp_set_auth_cookie($wp_user->ID, true);
                        
                        // Redirect alla destinazione
                        $redirect_url = isset($_SESSION['cohesion_redirect']) ? $_SESSION['cohesion_redirect'] : admin_url();
                        unset($_SESSION['cohesion_redirect']);
                        
                        wp_redirect($redirect_url);
                        exit;
                    }
                }
            }
            
            // Se non è autenticato, usa auth() che farà il redirect a Cohesion
            // QUESTO DOVREBBE FARE IL REDIRECT AL PORTALE ESTERNO COHESION
            error_log('Cohesion Login: User not authenticated, redirecting to Cohesion...');
            $cohesion->auth();
            
            // Se arriviamo qui, c'è stato un problema nel redirect
            throw new Exception('Il redirect a Cohesion non è stato eseguito correttamente');
            
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
            
            // Carica la libreria Cohesion2 locale
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/Cohesion2.php';
            
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
    
    /**
     * Configura l'istanza di Cohesion2 per supportare SPID/CIE
     * 
     * @param Cohesion2 $cohesion Istanza di Cohesion2
     * @param string $id_sito ID Sito per Cohesion
     * @return void
     */
    private function configure_cohesion_for_spid_cie($cohesion, $id_sito = 'TEST') {
        $config = $this->config->get_all_settings();
        
        // Configura l'ID Sito (importante per SPID/CIE)
        if (method_exists($cohesion, 'setIdSito')) {
            $cohesion->setIdSito($id_sito);
        } else {
            // Se la libreria non supporta setIdSito, dobbiamo usare un workaround
            $this->force_cohesion_id_sito($cohesion, $id_sito);
        }
        
        // Configura la libreria Cohesion2 per SPID/CIE
        // Abilita SAML 2.0 per supporto SPID/CIE per impostazione predefinita
        $cohesion->useSAML20(true);
        
        // Configura certificati se forniti dalle impostazioni
        if (!empty($config["cohesion_certificate_path"]) && !empty($config["cohesion_key_path"])) {
            $cohesion->setCertificate($config["cohesion_certificate_path"], $config["cohesion_key_path"]);
        } else {
            // La libreria locale Cohesion2 ha i suoi certificati interni predefiniti
            // Non serve specificare certificati esterni per test base
            error_log("Cohesion: Usando certificati predefiniti della libreria locale");
        }
        
        // Configura SSO
        $cohesion->useSSO(true);
        
        // Configura restrizioni di autenticazione per SPID/CIE
        // 0 = tutti i metodi incluso SPID/CIE
        // Per forzare solo SPID/CIE, usa restrizioni specifiche
        $auth_restriction = $config["cohesion_auth_restriction"] ?? "0";
        $cohesion->setAuthRestriction($auth_restriction);
        
        error_log("Cohesion Login: ID Sito = " . $id_sito);
        error_log("Cohesion Login: SAML20 enabled, SSO enabled, Auth restriction: " . $auth_restriction);
    }
    
    /**
     * Forza l'ID Sito in Cohesion2 usando reflection (workaround)
     * 
     * @param Cohesion2 $cohesion Istanza di Cohesion2  
     * @param string $id_sito ID Sito per Cohesion
     * @return void
     */
    private function force_cohesion_id_sito($cohesion, $id_sito) {
        // Usa reflection per accedere al metodo check privato e modificare l'XML
        $this->cohesion_id_sito = $id_sito;
        
        // Registra un filter per modificare l'XML prima del redirect
        add_filter('cohesion_auth_xml', array($this, 'modify_cohesion_xml'), 10, 2);
        
        error_log("Cohesion: Forcing ID Sito to " . $id_sito);
    }
    
    /**
     * Modifica l'XML di autenticazione per includere l'ID Sito corretto
     * 
     * @param string $xml XML di autenticazione
     * @param string $id_sito ID Sito per Cohesion
     * @return string XML modificato
     */
    public function modify_cohesion_xml($xml, $id_sito) {
        // Sostituisci <id_sito>TEST</id_sito> con l'ID sito corretto
        $xml = str_replace('<id_sito>TEST</id_sito>', '<id_sito>' . $id_sito . '</id_sito>', $xml);
        return $xml;
    }
    
    /**
     * Metodo di autenticazione personalizzato che utilizza l'ID Sito corretto
     * 
     * @param Cohesion2 $cohesion Istanza di Cohesion2
     * @param string $id_sito ID Sito per Cohesion
     * @return void
     */
    private function custom_cohesion_auth($cohesion, $id_sito) {
        // Se l'utente non è già autenticato
        if (!$cohesion->isAuth()) {
            
            // Se c'è un parametro auth nella richiesta, verifica l'autenticazione
            if (!empty($_REQUEST['auth'])) {
                $cohesion->verify($_REQUEST['auth']);
            } else {
                // Altrimenti, invia alla pagina di login con ID Sito personalizzato
                $this->custom_cohesion_check($cohesion, $id_sito);
            }
        }
    }
    
    /**
     * Metodo check personalizzato per supportare ID Sito configurabile
     * 
     * @param Cohesion2 $cohesion Istanza di Cohesion2
     * @param string $id_sito ID Sito per Cohesion
     * @return void
     */
    private function custom_cohesion_check($cohesion, $id_sito) {
        // Determina il protocollo
        $protocol = ($_SERVER["SERVER_PORT"] == 443) ? 'https://' : 'http://';
        $urlPagina = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $urlPagina .= ($_SERVER['QUERY_STRING']) ? '&' : '?';
        $urlPagina .= 'cohesionCheck=1';
        
        // Usa reflection per ottenere le proprietà private
        $reflection = new ReflectionClass($cohesion);
        $authRestrictionProperty = $reflection->getProperty('authRestriction');
        $authRestrictionProperty->setAccessible(true);
        $authRestriction = $authRestrictionProperty->getValue($cohesion);
        
        $saml20Property = $reflection->getProperty('saml20');
        $saml20Property->setAccessible(true);
        $saml20 = $saml20Property->getValue($cohesion);
        
        $ssoProperty = $reflection->getProperty('sso');
        $ssoProperty->setAccessible(true);
        $sso = $ssoProperty->getValue($cohesion);
        
        // Crea l'XML di autenticazione con l'ID Sito personalizzato
        $xmlAuth = '<dsAuth xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/Auth.xsd">
            <auth>
                <user />
                <id_sa />
                <id_sito>' . $id_sito . '</id_sito>
                <esito_auth_sa />
                <id_sessione_sa />
                <id_sessione_aspnet_sa />
                <url_validate><![CDATA[' . $urlPagina . ']]></url_validate>
                <url_richiesta><![CDATA[' . $urlPagina . ']]></url_richiesta>
                <esito_auth_sso />
                <id_sessione_sso />
                <id_sessione_aspnet_sso />
                <stilesheet>AuthRestriction=' . $authRestriction . '</stilesheet>
                <AuthRestriction xmlns="">' . $authRestriction . '</AuthRestriction>
            </auth>
        </dsAuth>';
        
        error_log('Cohesion XML Auth: ' . $xmlAuth);
        
        $auth = urlencode(base64_encode($xmlAuth));
        
        // Determina l'URL di login basato sulla configurazione
        if ($saml20) {
            $urlLogin = 'https://cohesion2.regione.marche.it/SPManager/WAYF.aspx?auth=' . $auth;
        } else {
            $urlLogin = ($sso) ? 'https://cohesion2.regione.marche.it/sso/Check.aspx?auth=' . $auth : 'https://cohesion2.regione.marche.it/SA/AccediCohesion.aspx?auth=' . $auth;
        }
        
        error_log('Cohesion Login URL: ' . $urlLogin);
        
        // Effettua il redirect
        header("Location: $urlLogin");
        exit;
    }
}
