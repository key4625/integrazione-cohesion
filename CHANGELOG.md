# Changelog

Tutte le modifiche notevoli a questo progetto saranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e questo progetto aderisce al [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-07-09

### 🔧 Fixed
- **REDIRECT ISSUE RISOLTO**: Corretto problema che mostrava URL interno `/cohesion/login` invece di redirigere al portale Cohesion esterno
- **Output Buffering**: Aggiunta pulizia output buffer prima dei redirect per evitare conflitti con WordPress  
- **Gestione redirect_to**: Migliorata gestione del parametro redirect_to nelle sessioni
- **Callback robusto**: Potenziata gestione del callback di ritorno da Cohesion con controlli aggiuntivi
- **ID Sito configurabile**: Verificato supporto completo per ID Sito reali (non solo TEST)

### 🚀 Improved
- Aggiunti log dettagliati per debug del flusso di autenticazione
- Migliorata configurazione SAML 2.0 per SPID/CIE con ID Sito reali
- Gestione errori più robusta nel flusso di login/callback
- Verifica presenza parametri callback prima dell'elaborazione

### 📋 Technical Changes
- `initiate_login()`: Aggiunta pulizia output buffer e gestione redirect_to
- `handle_callback()`: Migliorata gestione callback con controlli aggiuntivi  
- `handle_login()`: Supporto per parametro redirect_to dalla query string
- Configurazione Cohesion2: Verificata compatibilità con ID Sito reali

### 🧪 Testing
- Creati script di test per verifica redirect (`test-finale.php`, `test-redirect-login.php`)
- Script di aggiornamento configurazione (`update-config.php`)
- Verifica deployment aggiornata (`check-deployment.php`)

## [Unreleased]

### Pianificato
- Supporto per autenticazione multi-fattore
- Dashboard analytics per gli accessi
- Export dei dati utenti in CSV
- Integrazione con WooCommerce
- Widget per la sidebar

## [1.1.0] - 2025-07-09 - Migrazione Libreria Locale

### 🚀 Miglioramenti Principali
- **BREAKING CHANGE**: Rimossa dipendenza da Composer
- Creata libreria Cohesion2 locale in `lib/Cohesion2.php`
- ID Sito ora completamente configurabile tramite `setIdSito()`
- Rimozione dell'hardcoded "TEST" che impediva SPID/CIE

### ✅ Modifiche Tecniche
- Rimossi `composer.json`, `composer.lock` e cartella `vendor/`
- Aggiornato caricamento libreria in tutte le classi
- Modificata classe `Cohesion_Config` per controllo libreria locale
- Aggiornati messaggi di errore per riflettere nuova architettura

### 📚 Documentazione
- Aggiornato `TROUBLESHOOTING.md` con nuove procedure
- Aggiornato `STATUS.md` con stato attuale
- Creato script `debug-libreria-locale.php` per test

### 🎯 Benefici
- Installazione semplificata (nessun comando Composer)
- Libreria modificabile direttamente se necessario
- Controllo completo su versione e funzionalità
- Deploy immediato senza dipendenze esterne

### ⚠️ Note di Migrazione
- Non serve più eseguire `composer install`
- La libreria è ora autocontenuta nel plugin
- L'ID Sito "TEST" mostrava solo user/pass, ora configurabile per SPID/CIE

## [1.0.1] - 2025-07-08

### Fixed
- Risolto errore PHP Fatal "Class CohesionAuthentication not found"
- Corretti nomi delle classi per consistenza (underscore vs camelCase)
- Aggiunti metodi mancanti nella classe Cohesion_Authentication
- Migliorata gestione di login/logout tramite URL diretti

### Changed
- Nomenclatura classi standardizzata con underscore
- Metodi di autenticazione separati per AJAX e URL diretti

## [1.0.0] - 2025-06-27

### Aggiunto
- Integrazione completa con sistema Cohesion della Regione Marche
- Supporto per autenticazione SPID (Sistema Pubblico di Identità Digitale)
- Supporto per autenticazione CIE (Carta d'Identità Elettronica)  
- Supporto per autenticazione eIDAS (identità digitali europee)
- Supporto per SPID Professionale (PF, PG, LP)
- Modalità SAML 2.0 per tutti i sistemi di identità digitale
- Creazione automatica utenti WordPress da profili Cohesion
- Mappatura automatica dati anagrafici (nome, cognome, codice fiscale, etc.)
- Pannello di amministrazione completo con configurazione avanzata
- Shortcode `[cohesion_login]` per pulsanti di login personalizzabili
- Shortcode `[cohesion_logout]` per pulsanti di logout
- Integrazione automatica con menu di navigazione WordPress
- Sistema di log degli accessi con tracciamento IP e user agent
- Gestione intelligente degli errori con messaggi utente
- Restrizioni configurabili sui metodi di autenticazione
- Redirect personalizzabili post-login e post-logout
- Controlli di sicurezza e validazione requisiti di sistema
- Supporto per ambienti di test e produzione
- Gestione fallback per email utenti mancanti
- Metadati utente estesi con dati Cohesion
- Hook e filter per sviluppatori
- Interfaccia di amministrazione con tab organizzate
- Pagina di test completa per verificare l'integrazione
- Supporto per ruoli utente personalizzabili
- Email di benvenuto opzionali per nuovi utenti
- Configurazione ID Sito con validazione
- Sistema di cache per migliorare le prestazioni

### Sicurezza
- Validazione rigorosa di tutti gli input utente
- Sanitizzazione di tutti gli output
- Protezione CSRF con nonce WordPress
- Controllo permessi per tutte le operazioni admin
- Gestione sicura delle sessioni PHP
- Protezione contro attacchi di tipo injection
- Logging sicuro delle attività sensibili

### Tecnico
- Architettura modulare con classi separate per ogni funzionalità
- Compatibilità con WordPress 5.0+
- Compatibilità con PHP 7.4+
- Utilizzo di Composer per la gestione delle dipendenze
- Libreria Cohesion2 v3.0.1 integrata
- Standard di codifica WordPress rispettati
- Documentazione completa in italiano
- Template per traduzioni (.pot file)
- File di configurazione per GitHub Actions
- Template per issue e pull request GitHub

### Supportato
- Autenticazione tradizionale Cohesion (username/password/PIN)
- Smart Card e autenticazione di dominio
- Tutti i livelli di sicurezza Cohesion (0,1,2,3)
- Modalità Single Sign-On (SSO)
- Logout completo da tutti i sistemi
- Gestione profili utente complessi
- Codici fiscali italiani e europei
- Indirizzi email certificati (PEC)
- Multisite WordPress (limitato)

### Note di rilascio
Questa è la prima versione stabile del plugin. È stata testata con:
- WordPress 5.0 - 6.4
- PHP 7.4 - 8.2  
- Ambiente di test Cohesion della Regione Marche
- Vari temi WordPress popolari
- Configurazioni hosting comuni

### Migrazione
Non applicabile - primo rilascio

### Problemi noti
- La libreria Cohesion richiede `allow_url_fopen = On` nel php.ini
- Alcuni hosting condivisi potrebbero limitare le chiamate HTTP esterne
- Il logout potrebbe richiedere due click in alcune configurazioni
- La creazione automatica utenti dipende dalla qualità dei dati Cohesion

### Ringraziamenti
- Regione Marche per il sistema Cohesion
- Andrea Vallorani per la libreria PHP Cohesion2
- Community WordPress per gli standard di sviluppo
- Beta tester per il feedback prezioso

---

### Legenda
- `Aggiunto` per nuove funzionalità
- `Cambiato` per cambiamenti in funzionalità esistenti
- `Deprecato` per funzionalità che saranno rimosse nelle versioni future
- `Rimosso` per funzionalità rimosse in questa versione
- `Corretto` per correzioni di bug
- `Sicurezza` per vulnerabilità corrette

### Link utili
- [Documentazione Cohesion](https://cohesion.regione.marche.it/CohesionDocs/)
- [Repository GitHub](https://github.com/your-username/integrazione-cohesion)
- [Issues e Bug Report](https://github.com/your-username/integrazione-cohesion/issues)
- [WordPress Plugin Guidelines](https://developer.wordpress.org/plugins/)
