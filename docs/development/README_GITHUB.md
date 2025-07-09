# 🔐 Plugin WordPress - Integrazione Cohesion Regione Marche

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![SPID](https://img.shields.io/badge/SPID-Compatible-red.svg)](https://www.spid.gov.it/)
[![CIE](https://img.shields.io/badge/CIE-Compatible-blue.svg)](https://www.cartaidentita.interno.gov.it/)

Plugin WordPress professionale per l'integrazione del sistema di autenticazione **Cohesion della Regione Marche**. Supporta **SPID**, **CIE**, **eIDAS** e sistemi di autenticazione tradizionali.

## 🎯 Caratteristiche Principali

- ✅ **Autenticazione SPID** (Sistema Pubblico di Identità Digitale)
- ✅ **Autenticazione CIE** (Carta d'Identità Elettronica)  
- ✅ **Supporto eIDAS** (Identità digitali europee)
- ✅ **SPID Professionale** (PF, PG, LP)
- ✅ **Creazione automatica utenti WordPress**
- ✅ **Integrazione menu WordPress**
- ✅ **Shortcode personalizzabili**
- ✅ **Pannello amministrazione completo**
- ✅ **Log degli accessi**
- ✅ **Gestione profili utente**
- ✅ **Libreria locale** (no dipendenze Composer)

## 🚀 Installazione Rapida

### Prerequisiti
- WordPress 5.0 o superiore
- PHP 7.4 o superiore  
- Estensioni PHP: `openssl`, `dom`, `libxml`
- `allow_url_fopen = On` nel php.ini
- **ID Sito Cohesion** fornito dalla Regione Marche

### Installazione

1. **Scarica il plugin**
   ```bash
   git clone https://github.com/keysoluzioni/integrazione-cohesion-wordpress.git
   ```

2. **Carica nella cartella plugin**
   ```
   wp-content/plugins/integrazione-cohesion/
   ```

3. **Attiva il plugin** dal pannello WordPress

4. **Configura** in `Impostazioni > Cohesion`:
   - Inserisci l'**ID Sito** fornito dalla Regione Marche
   - Abilita i metodi di autenticazione desiderati (SPID, CIE, eIDAS)
   - Configura le impostazioni utente

## ⚙️ Configurazione

### ID Sito Cohesion
```php
// L'ID Sito viene fornito dalla Regione Marche
// Esempio: COMUNE001, ENTE002, etc.
// NON usare 'TEST' in produzione
```

### Shortcode Disponibili

#### Login
```php
[cohesion_login button_text="Accedi con SPID" redirect="/area-riservata"]
```

**Parametri:**
- `button_text`: Testo del pulsante (default: "Accedi con Cohesion")
- `redirect`: URL di destinazione dopo il login
- `show_spid`: Mostra info SPID (true/false)

#### Logout
```php
[cohesion_logout button_text="Esci" redirect="/"]
```

### URL Endpoint
- **Login**: `tuosito.it/cohesion/login`
- **Logout**: `tuosito.it/cohesion/logout` 
- **Callback**: `tuosito.it/cohesion/callback`

## 🛠️ Sviluppo

### Architettura
```
integrazione-cohesion/
├── integrazione-cohesion.php    # File principale
├── lib/
│   └── Cohesion2.php           # Libreria locale modificata
├── includes/
│   ├── class-cohesion-config.php
│   ├── class-cohesion-authentication.php
│   ├── class-cohesion-integration.php
│   ├── class-cohesion-user-manager.php
│   └── class-cohesion-admin.php
├── assets/
│   └── admin.css
└── languages/
    └── integrazione-cohesion.pot
```

### Debug
```php
// Abilita debug in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Log disponibili in wp-content/debug.log
```

### Script di Test
```bash
# Verifica configurazione
curl https://tuosito.com/wp-content/plugins/integrazione-cohesion/check-deployment.php

# Test login flow  
curl https://tuosito.com/wp-content/plugins/integrazione-cohesion/test-finale.php
```

## 📋 Risoluzione Problemi

### Errore "allow_url_fopen is disabled"
```ini
# Modifica php.ini
allow_url_fopen = On
```

### Errore "Class not found"
- Verifica che `lib/Cohesion2.php` esista
- Controlla i permessi file (644)
- Non usare Composer (libreria locale)

### Problemi di Redirect
- Verifica flush delle rewrite rules
- Controlla ID Sito configurato correttamente
- Verifica connessione HTTPS

### ID Sito "TEST"
⚠️ Con ID 'TEST' vedrai solo login/password tradizionale. Per SPID/CIE serve un ID reale abilitato dalla Regione Marche.

## 🔧 Tecnologie Utilizzate

- **WordPress API**: Hook, shortcode, rewrite rules
- **Libreria Cohesion2**: Versione locale modificata (no namespace)
- **SAML 2.0**: Per integrazione SPID/CIE
- **PHP Session**: Gestione stato autenticazione
- **WordPress User Management**: Creazione/aggiornamento utenti

## 📖 Documentazione Tecnica

### Flusso di Autenticazione
1. Utente clicca "Login Cohesion"
2. Redirect a portale Cohesion esterno
3. Autenticazione SPID/CIE/eIDAS
4. Callback al sito WordPress
5. Creazione/aggiornamento utente
6. Login automatico WordPress

### Hook Disponibili
```php
// Dopo creazione utente
do_action('cohesion_user_created', $user, $profile);

// Dopo aggiornamento utente  
do_action('cohesion_user_updated', $user, $profile);

// Log accesso
do_action('cohesion_user_login', $user, $auth_type);
```

## 🤝 Contributi

Contributi benvenuti! Segui questo processo:

1. Fork del repository
2. Crea branch feature (`git checkout -b feature/AmazingFeature`)
3. Commit modifiche (`git commit -m 'Add some AmazingFeature'`)
4. Push branch (`git push origin feature/AmazingFeature`)
5. Apri Pull Request

### Guidelines
- Segui standard WordPress Coding Standards
- Aggiungi test per nuove funzionalità
- Aggiorna documentazione
- Mantieni compatibilità PHP 7.4+

## 📄 Licenza

Questo progetto è licenziato sotto **MIT License** - vedi il file [LICENSE](LICENSE) per dettagli.

## 👥 Autori e Riconoscimenti

### 🤖 Sviluppo Iniziale
**GitHub Copilot AI Assistant**
- Architettura plugin WordPress
- Implementazione classi core
- Integrazione libreria Cohesion2
- Sistema shortcode e admin panel

### 👨‍💻 Sviluppo e Testing
**Ing. Michele Cappannari**  
*Key Soluzioni Informatiche*
- Debugging e risoluzione problemi critici
- Testing su ambiente di produzione
- Ottimizzazioni performance
- Documentazione tecnica
- Preparazione per pubblicazione

**Website**: [keysoluzioni.it](https://keysoluzioni.it)  
**Email**: info@keysoluzioni.it

### 🏛️ Sistema Cohesion
**Regione Marche**
- Sistema di autenticazione Cohesion
- Documentazione API
- Supporto integrazione enti

## 🆘 Supporto

### Supporto Tecnico
- **Issues GitHub**: [Apri un issue](https://github.com/keysoluzioni/integrazione-cohesion-wordpress/issues)
- **Email**: info@keysoluzioni.it
- **Documentazione**: Consulta file `TROUBLESHOOTING.md`

### Supporto Cohesion
- **Regione Marche**: cohesion@regione.marche.it
- **Documentazione**: http://cohesion.regione.marche.it/

## 📊 Versioni

- **v1.0.1** (2025-07-09): Fix redirect issue, gestione ID Sito reali
- **v1.0.0** (2025-07-08): Release iniziale con supporto SPID/CIE/eIDAS

Vedi [CHANGELOG.md](CHANGELOG.md) per dettagli completi.

## 🌟 Riconoscimenti Speciali

Questo plugin è stato sviluppato come esempio di collaborazione tra:
- **Intelligenza Artificiale** (GitHub Copilot)
- **Esperienza Umana** (Testing e debugging)
- **Settore Pubblico** (Sistema Cohesion Regione Marche)

Un ringraziamento particolare alla **Regione Marche** per aver reso disponibile il sistema Cohesion e alla community **WordPress italiana** per il supporto continuo.

---

**⭐ Se questo plugin ti è stato utile, lascia una stella su GitHub!**

**🔗 Link Utili:**
- [WordPress Plugin Directory](https://wordpress.org/plugins/)
- [SPID](https://www.spid.gov.it/)  
- [CIE](https://www.cartaidentita.interno.gov.it/)
- [Cohesion Regione Marche](http://cohesion.regione.marche.it/)

---
*Developed with ❤️ in Italy 🇮🇹*
