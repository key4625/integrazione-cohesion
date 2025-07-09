<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wrapper per Cohesion2 che supporta ID Sito configurabile
 * 
 * Questa classe estende Cohesion2 per supportare l'ID Sito configurabile
 * necessario per il funzionamento corretto con SPID/CIE.
 */
class Cohesion_Wrapper extends Cohesion2 {
    
    private $custom_id_sito = 'TEST';
    
    /**
     * Imposta l'ID Sito personalizzato
     * 
     * @param string $id_sito ID Sito per Cohesion
     * @return Cohesion_Wrapper
     */
    public function setIdSito($id_sito) {
        $this->custom_id_sito = $id_sito;
        return $this;
    }
    
    /**
     * Override del metodo check per supportare ID Sito personalizzato
     * 
     * Questo metodo è una copia del metodo check() originale con la modifica
     * per supportare l'ID Sito personalizzato.
     */
    public function check() {
        // Determina il protocollo
        $protocol = ($_SERVER["SERVER_PORT"] == 443) ? 'https://' : 'http://';
        $urlPagina = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $urlPagina .= ($_SERVER['QUERY_STRING']) ? '&' : '?';
        $urlPagina .= 'cohesionCheck=1';
        
        // Ottiene l'authRestriction usando il metodo parent
        $authRestriction = $this->__get('authRestriction');
        $saml20 = $this->__get('saml20');
        $sso = $this->__get('sso');
        
        // Crea l'XML di autenticazione con l'ID Sito personalizzato
        $xmlAuth = '<dsAuth xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/Auth.xsd">
            <auth>
                <user />
                <id_sa />
                <id_sito>' . $this->custom_id_sito . '</id_sito>
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
        
        error_log('Cohesion XML: ' . $xmlAuth);
        
        $auth = urlencode(base64_encode($xmlAuth));
        
        // Determina l'URL di login basato sulla configurazione
        if ($saml20) {
            $urlLogin = self::COHESION2_SAML20_CHECK . $auth;
        } else {
            $urlLogin = ($sso) ? self::COHESION2_CHECK . $auth : self::COHESION2_LOGIN . $auth;
        }
        
        error_log('Cohesion Login URL: ' . $urlLogin);
        
        // Effettua il redirect
        header("Location: $urlLogin");
        exit;
    }
    
    /**
     * Override del metodo auth per utilizzare il check personalizzato
     */
    public function auth() {
        if (!$this->isAuth()) {
            if (!empty($_REQUEST['auth'])) {
                $this->verify($_REQUEST['auth']);
            } else {
                $this->check();
            }
        }
    }
    
    /**
     * Accesso alle proprietà protette per compatibilità
     */
    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }
    
    /**
     * Modifica delle proprietà protette per compatibilità
     */
    public function __set($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
}
