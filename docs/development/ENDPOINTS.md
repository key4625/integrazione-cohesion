# Integrazione Cohesion - URL Endpoints

## Endpoints Disponibili

Il plugin crea automaticamente i seguenti endpoint personalizzati:

### `/cohesion/login`
- **Funzione**: Avvia il processo di autenticazione Cohesion
- **Metodo**: GET
- **Parametri opzionali**:
  - `redirect_to`: URL di reindirizzamento dopo il login

### `/cohesion/logout` 
- **Funzione**: Gestisce il logout da Cohesion e WordPress
- **Metodo**: GET
- **Parametri opzionali**:
  - `redirect_to`: URL di reindirizzamento dopo il logout

### `/cohesion/callback`
- **Funzione**: Gestisce la risposta del sistema Cohesion dopo l'autenticazione
- **Metodo**: GET/POST
- **Uso**: Utilizzato internamente dal sistema Cohesion

## Implementazione Tecnica

Gli endpoint sono implementati tramite:

1. **Rewrite Rules**: Regole personalizzate di WordPress che mappano gli URL alle query vars
2. **Query Variables**: Variabile `cohesion_action` che identifica l'azione richiesta
3. **Template Redirect**: Hook che intercetta le richieste e le gestisce prima del caricamento del template

## Utilizzo negli Shortcode

```php
// Shortcode per il login
[cohesion_login button_text="Accedi con SPID" redirect="https://example.com/dashboard"]

// Shortcode per il logout  
[cohesion_logout button_text="Esci" redirect="https://example.com/homepage"]
```

## Note di Sicurezza

- Tutti gli URL di reindirizzamento vengono validati
- Le sessioni sono gestite in modo sicuro
- L'integrazione rispetta gli standard di sicurezza WordPress

## Troubleshooting

Se gli endpoint non funzionano:

1. Disattivare e riattivare il plugin (forza il flush delle rewrite rules)
2. Andare in Impostazioni > Permalink e salvare (flush manuale)
3. Verificare che non ci siano conflitti con altri plugin

## Libreria Cohesion2

- **Versione**: 2.2.0
- **Fonte**: Repository ufficiale Regione Marche
- **Installazione**: Tramite Composer dal repository GitHub ufficiale
