# Plugin WordPress - Integrazione Cohesion

Questo plugin WordPress permette l'integrazione con il sistema di autenticazione Cohesion della Regione Marche, supportando SPID, CIE, eIDAS e altri sistemi di identità digitale.

## Caratteristiche

- ✅ Autenticazione tramite sistema Cohesion
- ✅ Supporto SPID (Sistema Pubblico di Identità Digitale)
- ✅ Supporto CIE (Carta d'Identità Elettronica)
- ✅ Supporto eIDAS (identità digitali europee)
- ✅ Supporto SPID Professionale
- ✅ Creazione automatica utenti WordPress
- ✅ Integrazione con i menu di WordPress
- ✅ Shortcode per login/logout
- ✅ Pannello di amministrazione completo
- ✅ Log degli accessi
- ✅ Gestione profili utente

## Requisiti

- WordPress 5.0 o superiore
- PHP 7.4 o superiore
- Estensioni PHP: openssl, dom, libxml
- `allow_url_fopen = On` nel php.ini
- ID Sito Cohesion fornito dalla Regione Marche

## Installazione

### 1. Installazione delle dipendenze

Il plugin utilizza la libreria PHP `andreaval/cohesion2-library`. Puoi installarla in due modi:

#### Opzione A: Con Composer (Raccomandato)

```bash
cd wp-content/plugins/integrazione-cohesion
composer install
```

#### Opzione B: Installazione manuale

Se non puoi usare Composer, scarica la libreria manualmente:

1. Scarica la libreria da: https://github.com/andreaval/Cohesion2PHPLibrary
2. Estrai i file nella cartella `lib/cohesion2/` del plugin
3. Assicurati che il file `lib/cohesion2/Cohesion2.php` sia presente

### 2. Attivazione del plugin

1. Carica la cartella del plugin in `wp-content/plugins/`
2. Attiva il plugin dal pannello WordPress
3. Vai in "Impostazioni > Cohesion" per configurare

## Configurazione

### 1. Ottenere l'ID Sito

Prima di utilizzare il plugin in produzione, devi:

1. Fare richiesta di integrazione a Cohesion tramite: https://procedimenti.regione.marche.it/Pratiche/Avvia/3049
2. Ottenere l'ID Sito univoco dalla Regione Marche
3. Inserire l'ID Sito nella configurazione del plugin

Per i test, puoi utilizzare l'ID Sito "TEST".

### 2. Configurazione del plugin

Vai in "Impostazioni > Cohesion" e configura:

- **ID Sito Cohesion**: Inserisci l'ID fornito dalla Regione Marche
- **Metodi di autenticazione**: Abilita SPID, CIE, eIDAS secondo le tue necessità
- **Gestione utenti**: Configura la creazione automatica degli utenti
- **Redirect**: Imposta le pagine di destinazione dopo login/logout

### 3. Test dell'integrazione

Utilizza i link di test nel pannello di amministrazione per verificare che tutto funzioni correttamente.

## Utilizzo

### Shortcode

#### Login
```php
[cohesion_login button_text="Accedi con SPID" redirect="/area-riservata"]
```

Parametri disponibili:
- `button_text`: Testo del pulsante (default: "Accedi con Cohesion")
- `redirect`: URL di destinazione dopo il login
- `show_spid`: Mostra informazioni SPID (true/false)

#### Logout
```php
[cohesion_logout button_text="Esci" redirect="/"]
```

Parametri disponibili:
- `button_text`: Testo del pulsante (default: "Logout")
- `redirect`: URL di destinazione dopo il logout

### URL Endpoint

Il plugin crea automaticamente questi endpoint:

- **Login**: `tuosito.it/cohesion/login`
- **Logout**: `tuosito.it/cohesion/logout`
- **Callback**: `tuosito.it/cohesion/callback` (utilizzato da Cohesion)

### Integrazione con i menu

Il plugin aggiunge automaticamente i link di login/logout al menu principale di WordPress.

### Template personalizzati

Puoi personalizzare l'aspetto dei pulsanti aggiungendo CSS personalizzato:

```css
.cohesion-login-button {
    background: #your-color !important;
    /* altre personalizzazioni */
}
```

## Hook per sviluppatori

Il plugin fornisce diversi hook per personalizzazioni avanzate:

```php
// Dopo la creazione di un nuovo utente
add_action('cohesion_user_created', function($user, $profile) {
    // Il tuo codice
}, 10, 2);

// Dopo l'aggiornamento di un utente esistente
add_action('cohesion_user_updated', function($user, $profile) {
    // Il tuo codice
}, 10, 2);

// Dopo ogni login Cohesion
add_action('cohesion_user_login', function($user, $profile, $log_data) {
    // Il tuo codice
}, 10, 3);
```

## Gestione utenti

### Creazione automatica

Quando abilitata, la creazione automatica degli utenti:

1. Cerca utenti esistenti per username, email o codice fiscale
2. Se non trovato, crea un nuovo utente WordPress
3. Memorizza il profilo Cohesion nei metadati dell'utente
4. Assegna il ruolo predefinito configurato

### Dati memorizzati

Per ogni utente Cohesion vengono memorizzati:

- Profilo completo ricevuto da Cohesion
- Codice fiscale
- Tipo di autenticazione utilizzata
- Date di login
- Dati anagrafici (nome, cognome, data di nascita, ecc.)

## Sicurezza

### Buone pratiche

1. Utilizza sempre HTTPS in produzione
2. Configura correttamente l'ID Sito fornito dalla Regione Marche
3. Monitora i log degli accessi
4. Mantieni aggiornati WordPress e il plugin
5. Limita i metodi di autenticazione secondo le tue necessità

### Gestione errori

Il plugin gestisce automaticamente:

- Errori di comunicazione con Cohesion
- Utenti senza email o dati mancanti
- Timeout delle sessioni
- Errori di validazione

## Risoluzione problemi

### Errore "allow_url_fopen is disabled"

Modifica il file `php.ini`:
```ini
allow_url_fopen = On
```

### Errore "User creation failed"

Verifica che:
1. L'utente non esista già con la stessa email
2. I permessi di WordPress permettano la creazione di utenti
3. Il ruolo predefinito sia valido

### Errore di autenticazione

Verifica che:
1. L'ID Sito sia corretto
2. La connessione HTTPS funzioni
3. I server di Cohesion siano raggiungibili

### Debug

Per abilitare il debug, aggiungi in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

I log saranno salvati in `wp-content/debug.log`.

## Supporto

### Regione Marche
- Email: integrazioneCohesion@regione.marche.it
- Documentazione: https://cohesion.regione.marche.it/CohesionDocs/

### Plugin
- Per problemi tecnici del plugin, controlla i log di WordPress
- Verifica la configurazione nel pannello di amministrazione
- Testa l'integrazione con gli strumenti forniti

## Licenza

Questo plugin è rilasciato sotto licenza MIT. La libreria Cohesion2 è anch'essa sotto licenza MIT.

## Changelog

### 1.0.0
- Rilascio iniziale
- Integrazione completa con Cohesion
- Supporto SPID, CIE, eIDAS
- Pannello di amministrazione
- Shortcode e hook per sviluppatori
