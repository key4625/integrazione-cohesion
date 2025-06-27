# Security Policy

## Versioni supportate

| Versione | Supportata          |
| -------- | ------------------- |
| 1.0.x    | :white_check_mark: |
| < 1.0    | :x:                |

## Segnalazione di vulnerabilità di sicurezza

La sicurezza del plugin Integrazione Cohesion è una priorità assoluta. Apprezziamo gli sforzi della community per identificare e segnalare responsabilmente le vulnerabilità di sicurezza.

### Come segnalare una vulnerabilità

**🚨 NON segnalare vulnerabilità di sicurezza tramite issue pubbliche GitHub.**

Per segnalare una vulnerabilità di sicurezza:

1. **Email privata**: Invia i dettagli a `security@your-domain.com`
2. **Oggetto**: "SECURITY - Integrazione Cohesion Plugin"
3. **Includi**:
   - Descrizione dettagliata della vulnerabilità
   - Passaggi per riprodurre il problema
   - Versioni interessate
   - Impatto potenziale
   - Eventuali patch o workaround suggeriti

### Cosa aspettarsi

- **Conferma**: Riceverai una conferma di ricezione entro 24 ore
- **Valutazione iniziale**: Risposta iniziale entro 72 ore
- **Analisi completa**: Valutazione approfondita entro 7 giorni
- **Risoluzione**: Patch di sicurezza entro 30 giorni (a seconda della gravità)

### Responsible Disclosure

Seguiamo i principi del responsible disclosure:

1. **Non divulgare** pubblicamente la vulnerabilità fino alla risoluzione
2. **Collaborazione**: Lavoriamo insieme per una risoluzione efficace
3. **Crediti**: Riconoscimento pubblico per la segnalazione (se desiderato)
4. **Timeline**: Coordinate disclosure dopo la risoluzione

## Scope della sicurezza

### Aree in scope

- ✅ Autenticazione e autorizzazione
- ✅ Gestione delle sessioni
- ✅ Validazione e sanitizzazione input
- ✅ Protezione CSRF
- ✅ Gestione dei dati sensibili
- ✅ Comunicazione con servizi Cohesion
- ✅ Gestione degli errori e logging
- ✅ Permessi e controlli di accesso

### Aree fuori scope

- ❌ Vulnerabilità in WordPress core
- ❌ Vulnerabilità in plugin di terze parti
- ❌ Problemi di hosting/server
- ❌ Attacchi di social engineering
- ❌ Vulnerabilità che richiedono accesso fisico

## Buone pratiche di sicurezza

### Per gli amministratori

1. **Aggiornamenti**: Mantieni sempre aggiornato il plugin
2. **Configurazione sicura**: Usa HTTPS in produzione
3. **ID Sito**: Proteggi l'ID Sito Cohesion
4. **Backup**: Effettua backup regolari
5. **Monitoring**: Monitora i log degli accessi
6. **Permessi**: Limita i permessi amministrativi

### Per gli sviluppatori

1. **Input validation**: Valida tutti gli input
2. **Output escaping**: Fai escape di tutti gli output
3. **Nonce**: Usa nonce per tutte le form
4. **Permissions**: Controlla i permessi utente
5. **Error handling**: Gestisci gli errori senza esporre informazioni sensibili

## Configurazione sicura

### Requisiti minimi di sicurezza

```php
// wp-config.php - Configurazione raccomandata
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('FORCE_SSL_ADMIN', true);
define('DISALLOW_FILE_EDIT', true);
```

### Server configuration

```apache
# .htaccess - Protezioni aggiuntive
<Files "composer.json">
    Order Allow,Deny
    Deny from all
</Files>

<Files "composer.lock">
    Order Allow,Deny
    Deny from all
</Files>

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

## Audit di sicurezza

### Controlli automatici

Il plugin include controlli automatici per:

- Validazione ID Sito
- Verifica requisiti PHP
- Controllo estensioni necessarie
- Validazione certificati SSL
- Controllo permessi file

### Audit manuali

Si raccomandano audit di sicurezza regolari che includano:

- Review del codice personalizzato
- Test di penetrazione
- Analisi dei log di accesso
- Verifica delle configurazioni

## Incident Response

### In caso di compromissione

1. **Isolamento**: Disattiva immediatamente il plugin
2. **Valutazione**: Determina l'estensione del danno
3. **Contenimento**: Implementa misure di contenimento
4. **Eradicazione**: Rimuovi la causa della compromissione
5. **Ripristino**: Ripristina da backup puliti
6. **Monitoraggio**: Monitora per attività sospette

### Contatti di emergenza

- **Sicurezza plugin**: security@your-domain.com
- **Supporto Cohesion**: integrazioneCohesion@regione.marche.it
- **Emergenze critiche**: +39-XXX-XXXXXXX

## Compliance e standard

### Standard seguiti

- OWASP Web Application Security Project
- WordPress Security Best Practices
- GDPR (per la gestione dei dati personali)
- AgID Guidelines (per l'identità digitale italiana)

### Certificazioni

- Testato secondo OWASP Top 10
- Compatibile con WordPress VIP Security Review
- Conforme alle linee guida AgID per SPID

## Security Headers

Il plugin raccomanda l'uso di questi security headers:

```
Content-Security-Policy: default-src 'self'
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000
Referrer-Policy: strict-origin-when-cross-origin
```

## Changelog di sicurezza

### v1.0.0 (2025-06-27)
- Implementazione controlli di sicurezza iniziali
- Validazione e sanitizzazione completa
- Protezione CSRF
- Gestione sicura delle sessioni

---

**Ricorda**: La sicurezza è una responsabilità condivisa. Seguendo queste linee guida e segnalando responsabilmente le vulnerabilità, aiuti a mantenere sicuro l'ecosistema WordPress per tutti.
