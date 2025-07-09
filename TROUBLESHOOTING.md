# Troubleshooting - Plugin Integrazione Cohesion

## Architettura Aggiornata (Senza Composer)

### ✅ Cambiamenti Implementati

#### Rimozione Composer
- ❌ Rimosso `composer.json` e `composer.lock`
- ❌ Eliminata cartella `vendor/`
- ✅ Creata libreria locale in `lib/Cohesion2.php`

#### Libreria Locale Modificata
- ✅ Aggiunta proprietà `$id_sito` configurabile
- ✅ Aggiunto metodo `setIdSito($id_sito)`
- ✅ Rimosso hardcoded "TEST" nel metodo `check()`
- ✅ Tutti i riferimenti aggiornati per usare la libreria locale

## Problemi Comuni e Soluzioni

### 1. Errore "Class Cohesion2 not found"

**Problema**: La libreria Cohesion2 non viene trovata.

**Soluzioni**:
- ✅ Verifica che il file `lib/Cohesion2.php` esista nella cartella del plugin
- ✅ La libreria è ora inclusa localmente, NON serve più Composer
- Se il file manca, contatta lo sviluppatore del plugin

### 2. Errore "ID Sito non configurato"

**Problema**: L'ID Sito non è stato impostato o è vuoto.

**Soluzioni**:
- Vai in `Impostazioni > Cohesion` nell'admin WordPress
- Inserisci l'ID Sito fornito da Regione Marche
- Salva le impostazioni e riprova

### 3. Il portale Cohesion mostra solo username/password

**Problema**: Non compaiono le opzioni SPID/CIE sul portale.

**Possibili Cause e Soluzioni**:
- **ID Sito errato**: Verifica che sia quello fornito da Regione Marche
- **ID Sito non attivo**: Contatta Regione Marche per attivazione SPID/CIE
- **SAML non abilitato**: Verifica che SAML 2.0 sia attivo nelle impostazioni
- **Test con ID "TEST"**: L'ID "TEST" mostra solo user/pass, usa un ID reale

### 4. Errore "Internal Server Error" durante il login

**Problema**: Errore 500 durante l'autenticazione.

**Soluzioni**:
- Verifica i log di PHP per dettagli specifici (`/wp-content/debug.log`)
- Abilita il debug WP: `define('WP_DEBUG', true);` in `wp-config.php`
- Usa lo script `debug-libreria-locale.php` per test base

### 5. Problemi di sessione PHP

**Problema**: Errori relativi alla gestione sessioni.

**Soluzioni**:
- Verifica che `session_start()` sia chiamato correttamente
- Controlla le impostazioni di sessione PHP sul server
- Verifica che il server abbia permessi di scrittura nelle cartelle di sessione

## Script di Debug Disponibili

### `debug-libreria-locale.php`
Testa il caricamento e funzionalità di base della libreria locale.

### `debug-id-sito.php`
Verifica la configurazione dell'ID Sito e la connessione a Cohesion.

### `debug-cohesion.php`
Test completo del flusso di autenticazione.

## File Modificati nella Migrazione

### File Rimossi
- ❌ `vendor/` (cartella completa)
- ❌ `composer.json`
- ❌ `composer.lock`

### File Aggiornati
- ✅ `integrazione-cohesion.php` - Rimosso autoloader Composer
- ✅ `includes/class-cohesion-config.php` - Controllo libreria locale
- ✅ `includes/class-cohesion-authentication.php` - Caricamento libreria locale
- ✅ `lib/Cohesion2.php` - Libreria locale con ID Sito configurabile

### File Creati
- ✅ `debug-libreria-locale.php` - Test libreria locale

## Deployment Senza Composer

### Vantaggi
- ✅ Nessuna dipendenza esterna da installare
- ✅ Libreria modificabile direttamente
- ✅ Controllo completo su versione e funzionalità
- ✅ Installazione semplificata

### Procedura di Deploy
1. Carica tutti i file del plugin sul server
2. Attiva il plugin da WordPress admin
3. Configura l'ID Sito nelle impostazioni
4. Testa il login con un ID Sito reale

## Supporto

Se riscontri problemi non elencati qui:
1. Abilita il debug WordPress
2. Controlla i log di errore PHP
3. Usa gli script di debug forniti
4. Verifica la configurazione con Regione Marche
4. **Testare il login** con l'URL di debug

## Test di Verifica

Per verificare che tutto funzioni correttamente:

```bash
# Verifica sintassi
php -l integrazione-cohesion.php
php -l includes/class-cohesion-*.php

# Verifica autoloader
php -r "require 'vendor/autoload.php'; echo 'OK';"

# Verifica classe Cohesion2
php -r "require 'vendor/autoload.php'; var_dump(class_exists('andreaval\cohesion2\Cohesion2'));"
```

## URL di Test per Server Remoto

Una volta deployato, questi URL dovrebbero funzionare:
- Login: `https://tuosito.com/cohesion/login`
- Logout: `https://tuosito.com/cohesion/logout`
- Callback: `https://tuosito.com/cohesion/callback`
- Debug: `https://tuosito.com/wp-content/plugins/integrazione-cohesion/debug-cohesion.php`

## Problema: Il portale Cohesion mostra solo user/password invece di SPID/CIE

**Sintomi:**
- Il plugin redirige correttamente al portale Cohesion
- Il portale mostra solo i campi per username, password e PIN
- Non vengono mostrate le opzioni SPID, CIE o altri sistemi di identità digitale

**Cause principali:**

### 1. ID Sito non configurato correttamente
L'ID Sito è il parametro più importante per l'autenticazione SPID/CIE. Se l'ID Sito è impostato su "TEST" o non è configurato correttamente, il portale Cohesion mostrerà solo l'autenticazione tradizionale.

**Soluzione:**
1. Andare in **Impostazioni > Cohesion** nell'admin di WordPress
2. Configurare l'**ID Sito Cohesion** con il valore fornito dalla Regione Marche
3. Se non si dispone di un ID Sito ufficiale, contattare la Regione Marche

### 2. SAML 2.0 non abilitato
SPID e CIE richiedono il protocollo SAML 2.0 per funzionare.

**Soluzione:**
1. Verificare che **Abilita SAML 2.0** sia attivato nelle impostazioni
2. Salvare le impostazioni

### 3. Restrizioni di autenticazione troppo limitanti
Le restrizioni di autenticazione potrebbero impedire la visualizzazione di SPID/CIE.

**Soluzione:**
1. Impostare **Restrizioni Autenticazione** su `0` o `0,1,2,3` per tutti i metodi
2. Il valore `0` include tutti i metodi di autenticazione incluso SPID/CIE

### 4. Configurazione del portale Cohesion
Il portale Cohesion deve essere configurato lato server per supportare SPID/CIE per l'ID Sito specifico.

**Soluzione:**
1. Contattare l'amministratore del sistema Cohesion della Regione Marche
2. Verificare che l'ID Sito sia abilitato per SPID/CIE
3. Richiedere la configurazione degli Identity Provider SPID/CIE

### Test della configurazione

Per testare la configurazione, usare il file di debug:
```
https://tuosito.com/wp-content/plugins/integrazione-cohesion/debug-id-sito.php
```

**Note importanti:**
- Con ID Sito "TEST", potrebbero non essere disponibili tutte le opzioni di autenticazione
- Per produzione, è necessario un ID Sito ufficiale della Regione Marche
- La configurazione deve essere validata lato server Cohesion per abilitare SPID/CIE

## Backup delle Modifiche

Tutti i file sono stati corretti e testati. Il plugin è ora pronto per il deployment remoto senza l'errore "Class not found".
