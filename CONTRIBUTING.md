# Contribuire al Plugin Integrazione Cohesion

Grazie per il tuo interesse nel contribuire al plugin! Questo documento fornisce le linee guida per contribuire al progetto.

## 🚀 Come contribuire

### Segnalazione di bug

1. Verifica che il bug non sia già stato segnalato nelle [Issues](https://github.com/your-username/integrazione-cohesion/issues)
2. Crea una nuova issue utilizzando il template "Bug Report"
3. Fornisci informazioni dettagliate per riprodurre il problema
4. Includi versioni di WordPress, PHP e plugin
5. Allega log di errore se disponibili

### Richieste di funzionalità

1. Verifica che la funzionalità non sia già stata richiesta
2. Crea una nuova issue utilizzando il template "Feature Request"
3. Descrivi chiaramente il caso d'uso e i benefici
4. Considera l'impatto sulla sicurezza e la compatibilità

### Contributi al codice

1. **Fork** il repository
2. Crea un **branch** per la tua modifica: `git checkout -b feature/nome-funzionalita`
3. Effettua le modifiche seguendo gli standard di codifica
4. **Testa** le modifiche localmente
5. **Commit** con messaggi descrittivi
6. **Push** al tuo fork: `git push origin feature/nome-funzionalita`
7. Crea una **Pull Request**

## 📋 Standard di codifica

### PHP

- Segui il [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Usa PSR-4 per l'autoloading delle classi
- Commenta il codice complesso
- Valida e sanitizza tutti gli input
- Usa nonce per le form submissions

```php
// Esempio di struttura classe
class CohesionExample {
    
    /**
     * Descrizione del metodo
     * 
     * @param string $param Descrizione parametro
     * @return bool
     */
    public function example_method($param) {
        // Validazione input
        if (empty($param)) {
            return false;
        }
        
        // Logica del metodo
        return true;
    }
}
```

### JavaScript

- Usa moderne sintassi ES6+ quando possibile
- Evita variabili globali
- Segui il [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)

### CSS

- Usa prefissi per le classi: `.cohesion-`
- Segui il [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- Supporta responsive design

## 🧪 Testing

### Test locali richiesti

1. **Funzionalità base**:
   - Login/logout Cohesion
   - Creazione automatica utenti
   - Shortcode funzionanti
   - Pannello di amministrazione

2. **Compatibilità**:
   - WordPress 5.0+
   - PHP 7.4+
   - Diversi temi WordPress
   - Modalità multisite (se applicabile)

3. **Sicurezza**:
   - Validazione input
   - Sanitizzazione output
   - Controllo permessi
   - Protezione CSRF

### Setup ambiente di test

```bash
# Clona il repository
git clone https://github.com/your-username/integrazione-cohesion.git
cd integrazione-cohesion

# Installa dipendenze
composer install

# Configura WordPress locale
# (usa il tuo metodo preferito: XAMPP, Local, Docker, etc.)

# Attiva il plugin e testa
```

## 🔒 Sicurezza

### Principi di sicurezza

1. **Validazione input**: Valida tutti i dati in ingresso
2. **Sanitizzazione output**: Sanitizza tutti i dati in uscita
3. **Controllo permessi**: Verifica i permessi utente
4. **Nonce**: Usa nonce per prevenire CSRF
5. **Escape**: Fai escape di tutti i dati prima dell'output

### Segnalazione vulnerabilità

Per segnalare vulnerabilità di sicurezza:
- **NON** creare issue pubbliche
- Invia email a: security@your-domain.com
- Includi dettagli della vulnerabilità
- Fornisci steps per riprodurla

## 📦 Struttura del progetto

```
integrazione-cohesion/
├── integrazione-cohesion.php     # File principale
├── composer.json                 # Dipendenze
├── README.md                     # Documentazione
├── CONTRIBUTING.md               # Questo file
├── includes/                     # Classi PHP
│   ├── class-cohesion-integration.php
│   ├── class-cohesion-authentication.php
│   ├── class-cohesion-user-manager.php
│   ├── class-cohesion-admin.php
│   └── class-cohesion-config.php
├── assets/                       # CSS, JS, immagini
│   └── admin.css
├── languages/                    # File di traduzione
│   └── integrazione-cohesion.pot
├── .github/                      # Configurazione GitHub
│   ├── workflows/
│   ├── ISSUE_TEMPLATE/
│   └── pull_request_template.md
└── vendor/                       # Dipendenze Composer
```

## 🌐 Internazionalizzazione

- Usa sempre le funzioni di traduzione WordPress: `__()`, `_e()`, `esc_html__()`
- Specifica il text domain: `'integrazione-cohesion'`
- Aggiorna il file `.pot` quando aggiungi nuove stringhe

```php
// Corretto
echo esc_html__('Testo da tradurre', 'integrazione-cohesion');

// Scorretto
echo 'Testo non traducibile';
```

## 📝 Documentazione

### Commenti nel codice

- Usa DocBlocks per classi e metodi
- Commenta logica complessa
- Spiega il "perché", non solo il "cosa"

### README e documentazione

- Aggiorna il README.md per nuove funzionalità
- Includi esempi di codice
- Documenta le configurazioni necessarie

## 🏷️ Versioning

Usiamo [Semantic Versioning](https://semver.org/):

- **MAJOR** (X.0.0): Breaking changes
- **MINOR** (0.X.0): Nuove funzionalità (backward compatible)
- **PATCH** (0.0.X): Bug fixes (backward compatible)

### Branch strategy

- `main`: Codice stabile di produzione
- `develop`: Sviluppo attivo
- `feature/nome`: Nuove funzionalità
- `hotfix/nome`: Fix urgenti per produzione

## 📞 Supporto

### Canali di comunicazione

- **Issues**: Per bug e richieste funzionalità
- **Discussions**: Per domande generali
- **Email**: integrazioneCohesion@regione.marche.it

### Prima di chiedere supporto

1. Leggi la documentazione
2. Cerca nelle issue esistenti
3. Testa con configurazione minima
4. Raccogli informazioni di debug

## 📄 Licenza

Contribuendo al progetto, accetti che i tuoi contributi siano rilasciati sotto la [Licenza MIT](LICENSE).

## 🙏 Riconoscimenti

Grazie a tutti i contributori che aiutano a migliorare questo plugin!

### Come essere aggiunti ai riconoscimenti

- Contributi significativi al codice
- Miglioramenti alla documentazione
- Segnalazione di bug critici
- Supporto alla community

---

**Grazie per contribuire al plugin Integrazione Cohesion! 🚀**
