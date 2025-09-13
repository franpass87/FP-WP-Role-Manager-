# FP WordPress Role Manager

Un plugin WordPress per la gestione avanzata dei ruoli utente e del controllo della visibilità dei menu amministrativi.

## Descrizione

Questo plugin permette di gestire i ruoli utente di WordPress controllando quali sezioni dell'area amministrativa possono vedere specifici ruoli. È particolarmente utile per creare ruoli personalizzati con accesso limitato a determinate funzionalità.

## Esempio d'uso

Il plugin include un ruolo di esempio "Restaurant Manager" che può essere utilizzato per gestori di ristoranti che necessitano di accedere solo a specifiche sezioni dell'admin WordPress, come:

- Gestione post e contenuti
- Caricamento media
- Prenotazioni ristorante (se presente un plugin dedicato)
- Gestione ordini (WooCommerce)

## Funzionalità

- ✅ Creazione automatica del ruolo "Restaurant Manager"
- ✅ Controllo granulare dei menu amministrativi visibili per ogni ruolo
- ✅ Interfaccia di configurazione semplice e intuitiva
- ✅ Supporto per ruoli personalizzati
- ✅ Preservazione delle impostazioni durante disattivazione/riattivazione

## Installazione

1. Carica i file del plugin nella directory `/wp-content/plugins/fp-wp-role-manager/`
2. Attiva il plugin attraverso il menu 'Plugins' di WordPress
3. Vai su "Strumenti" > "Role Manager" per configurare i permessi

## Configurazione

### Restaurant Manager

Dopo l'attivazione, il plugin crea automaticamente il ruolo "Restaurant Manager" con le seguenti capacità:

- `read` - Lettura contenuti
- `edit_posts` - Modifica post
- `upload_files` - Caricamento file
- `manage_categories` - Gestione categorie

### Configurazione Menu

1. Vai su **Strumenti > Role Manager**
2. Seleziona i menu che il "Restaurant Manager" può vedere
3. Salva le impostazioni

### Menu disponibili per configurazione:

- Posts (Articoli)
- Media (File multimediali)
- Pages (Pagine)
- Comments (Commenti)
- Appearance (Aspetto)
- Plugins (Plugin)
- Users (Utenti)
- Tools (Strumenti)
- Settings (Impostazioni)
- Role Manager (Questo plugin)

## Sviluppo e Estensioni

### Aggiungere nuovi ruoli

Il plugin può essere facilmente esteso per supportare ruoli aggiuntivi modificando il file principale `fp-wp-role-manager.php`.

### Hook disponibili

Il plugin utilizza i seguenti hook WordPress:

- `admin_menu` - Per filtrare i menu amministrativi
- `admin_init` - Per inizializzare le impostazioni
- `init` - Per caricare le traduzioni

## Requisiti

- WordPress 5.0 o superiore
- PHP 7.4 o superiore

## Licenza

GPL v2 or later

## Supporto

Per problemi o domande, apri una issue su GitHub.

## Changelog

### 1.0.0
- Prima release
- Ruolo Restaurant Manager
- Controllo menu amministrativi
- Interfaccia di configurazione