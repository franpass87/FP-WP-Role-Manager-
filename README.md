# FP WordPress Role Manager

Un plugin WordPress per la gestione avanzata dei ruoli utente e del controllo granulare della visibilità dei menu amministrativi e dell'accesso ai plugin.

## Descrizione

Questo plugin permette di gestire qualsiasi ruolo utente di WordPress controllando con precisione quali sezioni dell'area amministrativa e quali plugin possono utilizzare specifici ruoli. È particolarmente utile per creare configurazioni personalizzate con accesso limitato a determinate funzionalità.

## Caratteristiche Principali

Il plugin offre un approccio flessibile e generico per la gestione dei permessi:

- **Configurazione per Qualsiasi Ruolo**: Gestisci editor, autori, contributor o ruoli personalizzati
- **Controllo Menu Amministrativi**: Decidi quali sezioni dell'admin WordPress sono visibili
- **Gestione Plugin**: Controlla l'accesso ai menu dei plugin installati
- **Interfaccia Pulita**: Inizia con configurazione vuota, senza preset predefiniti

## Funzionalità

- ✅ **Creazione di nuovi ruoli personalizzati** - Crea ruoli con nomi personalizzati
- ✅ Configurazione di qualsiasi ruolo WordPress esistente
- ✅ Controllo granulare dei menu amministrativi visibili per ogni ruolo
- ✅ Gestione dell'accesso ai plugin installati
- ✅ **Eliminazione ruoli personalizzati** - Rimuovi i ruoli creati (non quelli predefiniti)
- ✅ Interfaccia di configurazione semplice e intuitiva
- ✅ Nessuna configurazione predefinita - inizi da zero
- ✅ Preservazione delle impostazioni durante disattivazione/riattivazione
- ✅ **Validazione avanzata** - Controllo formato nome ruolo e sicurezza

## Installazione

### Download automatico (consigliato)

Il plugin è disponibile come archivio ZIP pronto per WordPress:

1. **Dalle Release**: Vai alla sezione [Releases](../../releases) e scarica l'ultimo `fp-wp-role-manager.zip`
2. **Dalle Actions**: Vai su [Actions](../../actions) e scarica l'artifact dall'ultima build

### Installazione su WordPress

1. Nel tuo admin WordPress, vai su **Plugin > Aggiungi nuovo**
2. Clicca **Carica plugin**
3. Seleziona il file `fp-wp-role-manager.zip` scaricato
4. Clicca **Installa ora** e poi **Attiva**
5. Vai su "Strumenti" > "Role Manager" per iniziare la configurazione

### Installazione manuale

1. Carica i file del plugin nella directory `/wp-content/plugins/fp-wp-role-manager/`
2. Attiva il plugin attraverso il menu 'Plugins' di WordPress
3. Vai su "Strumenti" > "Role Manager" per iniziare la configurazione

> 📋 Per maggiori dettagli sul processo di build, consulta [BUILD.md](BUILD.md)

## Configurazione

### Configurazione Iniziale

Dopo l'attivazione, il plugin inizia con una configurazione vuota. Hai due opzioni:

#### **Opzione 1: Crea un Nuovo Ruolo Personalizzato**

1. Vai su **Strumenti > Role Manager**
2. Nella sezione "Crea Nuovo Ruolo":
   - Inserisci il **Nome Ruolo (chiave)** (es: `content_manager`)
     - Solo lettere minuscole, numeri e underscore
     - Minimo 3 caratteri
   - Inserisci opzionalmente il **Nome Visualizzato** (es: "Content Manager")
3. Clicca **"Crea Nuovo Ruolo"**
4. Verrai automaticamente reindirizzato alla configurazione del nuovo ruolo
5. Scegli i menu amministrativi che il ruolo può vedere
6. Seleziona i plugin che il ruolo può utilizzare
7. Salva le impostazioni

#### **Opzione 2: Configura un Ruolo Esistente**

1. Vai su **Strumenti > Role Manager**
2. Nella sezione "Configura Ruolo Esistente":
   - Seleziona il ruolo che vuoi configurare dal menu a tendina
3. Scegli i menu amministrativi che il ruolo può vedere
4. Seleziona i plugin che il ruolo può utilizzare
5. Salva le impostazioni

### Esempi di Configurazione

#### Editor Limitato
Per un editor che può gestire solo contenuti:
- **Menu consentiti**: Posts, Media, Pages
- **Plugin consentiti**: Eventuali plugin di editor di testo

#### Gestore E-commerce
Per un utente che gestisce solo un negozio online:
- **Menu consentiti**: Posts, Media
- **Plugin consentiti**: WooCommerce, plugin di pagamento

#### Content Manager
Per chi gestisce solo contenuti specifici:
- **Menu consentiti**: Posts, Media, Comments
- **Plugin consentiti**: Plugin SEO, plugin di backup

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

### Plugin supportati:

Il plugin rileva automaticamente tutti i plugin attivi e permette di configurare l'accesso ai loro menu amministrativi per ciascun ruolo.

### Gestione Ruoli Personalizzati

Il plugin permette la gestione completa dei ruoli personalizzati:

- **Creazione**: Crea nuovi ruoli con nomi personalizzati
- **Configurazione**: Configura permessi di accesso per menu e plugin
- **Eliminazione**: Rimuovi ruoli personalizzati (i ruoli predefiniti di WordPress sono protetti)

#### Sicurezza e Validazione

- Validazione del formato del nome ruolo (solo lettere minuscole, numeri, underscore)
- Controllo unicità del nome ruolo
- Protezione CSRF tramite nonces
- Controllo capabilities per tutte le operazioni

#### Note Importanti

- I ruoli predefiniti di WordPress (Administrator, Editor, Author, Contributor, Subscriber) non possono essere eliminati
- I ruoli personalizzati creati possono essere eliminati solo attraverso questo plugin
- L'eliminazione di un ruolo rimuove anche la sua configurazione dalle impostazioni del plugin

## Sviluppo e Estensioni

### Aggiungere nuovi ruoli

Il plugin funziona con qualsiasi ruolo WordPress esistente. Per ruoli personalizzati, utilizza plugin specializzati nella creazione di ruoli e poi configura l'accesso con questo plugin.

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
- Interfaccia generica per configurazione ruoli
- Controllo menu amministrativi per qualsiasi ruolo
- Gestione accesso plugin per ruolo
- Configurazione vuota iniziale (no preset)