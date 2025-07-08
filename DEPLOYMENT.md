# Istruzioni per il Deployment del Plugin Cohesion su Server Remoto

## 1. Preparazione del Package

### File da caricare sul server remoto:
```
integrazione-cohesion/
├── integrazione-cohesion.php
├── composer.json
├── composer.lock
├── README.md
├── LICENSE
├── CHANGELOG.md
├── assets/
│   └── admin.css
├── includes/
│   ├── class-cohesion-admin.php
│   ├── class-cohesion-authentication.php
│   ├── class-cohesion-config.php
│   ├── class-cohesion-integration.php
│   └── class-cohesion-user-manager.php
├── languages/
│   └── integrazione-cohesion.pot
└── debug-cohesion.php (solo per debug, rimuovere in produzione)
```

### File da NON caricare (esclusi dal .gitignore):
- `vendor/` (verrà rigenerato)
- `node_modules/`
- `.env`
- File di test e debug

## 2. Installazione sul Server

### Step 1: Caricare i file
1. Caricare tutti i file del plugin nella directory:
   `/wp-content/plugins/integrazione-cohesion/`

### Step 2: Installare le dipendenze
```bash
cd /wp-content/plugins/integrazione-cohesion/
composer install --no-dev --optimize-autoloader
```

### Step 3: Attivare il plugin
1. Accedere al pannello di amministrazione WordPress
2. Andare in Plugin → Plugin installati
3. Attivare "Integrazione Cohesion"

## 3. Configurazione

### Step 1: Configurazione di base
1. Andare in **Impostazioni → Cohesion Integration**
2. Configurare:
   - **Ambiente**: Produzione o Test
   - **ID Sito**: Fornito dalla Regione Marche
   - **Certificato**: Certificato del sito
   - **Chiave Privata**: Chiave privata del certificato
   - **URL di Callback**: Sarà generato automaticamente

### Step 2: Configurazione utenti
1. **Ruolo predefinito**: Subscriber (consigliato)
2. **Creazione automatica utenti**: Abilitata
3. **Invio email di benvenuto**: Secondo necessità

### Step 3: Test della configurazione
1. Visitare `https://iltuosito.com/wp-content/plugins/integrazione-cohesion/debug-cohesion.php`
2. Verificare che tutti i componenti siano caricati correttamente
3. **IMPORTANTE**: Rimuovere il file debug-cohesion.php dopo il test!

## 4. Debug e Troubleshooting

### Attivare il debug WordPress
In `wp-config.php` aggiungere:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Verificare i log
I log si trovano in:
- `/wp-content/debug.log` (WordPress)
- Log del server (varia secondo la configurazione)

### Errori comuni e soluzioni

#### "Internal Server Error" durante il login
**Possibili cause:**
1. Autoloader Composer non installato
2. Configurazione PHP insufficiente
3. Certificati non validi
4. Sessioni PHP non configurate

**Soluzioni:**
1. Verificare che `composer install` sia stato eseguito
2. Verificare versione PHP >= 7.4
3. Verificare che le sessioni PHP siano abilitate
4. Controllare i log per errori specifici

#### "Classe Cohesion2 non trovata"
**Causa:** Libreria non installata correttamente
**Soluzione:** Reinstallare le dipendenze con `composer install`

#### "Nonce verification failed"
**Causa:** Problema di sicurezza WordPress
**Soluzione:** Verificare che i nonce siano generati correttamente

### Comandi utili per debug
```bash
# Verificare installazione Composer
composer show

# Verificare autoloader
php -r "require 'vendor/autoload.php'; echo 'Autoloader OK';"

# Verificare classe Cohesion2
php -r "require 'vendor/autoload.php'; var_dump(class_exists('andreaval\cohesion2\Cohesion2'));"

# Verificare configurazione PHP
php -m | grep -E "(session|openssl|curl|json)"
```

## 5. Test del Login

### URL di test
- Login: `https://iltuosito.com/wp-admin/admin-ajax.php?action=cohesion_login`
- Callback: `https://iltuosito.com/wp-admin/admin-ajax.php?action=cohesion_callback`

### Procedura di test
1. Implementare il shortcode `[cohesion_login]` in una pagina
2. Testare il login con credenziali SPID/CIE
3. Verificare che l'utente venga creato correttamente
4. Testare il logout

## 6. Sicurezza

### Protezioni implementate
- Verifica nonce per tutte le richieste AJAX
- Sanitizzazione di tutti i dati utente
- Validazione certificati SSL
- Protezione da accesso diretto ai file

### Raccomandazioni aggiuntive
1. Utilizzare HTTPS obbligatorio
2. Configurare firewall per proteggere gli endpoint
3. Monitorare i log per attività sospette
4. Aggiornare regolarmente il plugin e le dipendenze

## 7. Monitoraggio

### Metriche da monitorare
- Numero di login riusciti/falliti
- Tempo di risposta del sistema Cohesion
- Errori nei log

### Alert da configurare
- Errori "Internal Server Error" frequenti
- Timeout durante l'autenticazione
- Certificati in scadenza

## 8. Supporto

### Informazioni da fornire in caso di problemi
1. Versione WordPress
2. Versione PHP
3. Log completi degli errori
4. Configurazione del plugin
5. Passi per riprodurre il problema

### Contatti
- Documentazione Cohesion: [URL della documentazione]
- Repository GitHub: [URL del repository]
- Issues: [URL per segnalazioni]

---

**Nota importante:** Questo plugin gestisce dati sensibili. Assicurarsi di rispettare tutte le normative GDPR e di sicurezza applicabili.
