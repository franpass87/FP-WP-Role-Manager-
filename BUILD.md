# Build Workflow Documentation

## WordPress Plugin Build Workflow

Questo repository include un workflow GitHub Actions che genera automaticamente un archivio ZIP pronto per l'installazione su WordPress.

### Come funziona

Il workflow viene attivato nei seguenti casi:
- **Release**: Quando crei una nuova release su GitHub
- **Push su main**: Ad ogni push sul branch principale
- **Pull Request**: Su pull request verso il branch main
- **Manualmente**: Tramite il tab "Actions" su GitHub

### Struttura del plugin generato

Il ZIP contiene:
```
fp-wp-role-manager/
├── fp-wp-role-manager.php  # File principale del plugin
├── README.md               # Documentazione
└── assets/                 # Asset CSS e JavaScript
    ├── admin.css
    └── admin.js
```

### Come utilizzare

#### 1. Download automatico dalle Actions
1. Vai su **Actions** nel repository GitHub
2. Seleziona l'ultima build riuscita
3. Scarica l'artifact "fp-wp-role-manager-plugin"

#### 2. Download dalle Release
1. Crea una nuova release su GitHub
2. Il workflow genererà automaticamente il ZIP
3. Il file sarà allegato alla release come asset

#### 3. Installazione su WordPress
1. Scarica il file `fp-wp-role-manager.zip`
2. Vai su **Plugin > Aggiungi nuovo** nel tuo admin WordPress
3. Clicca **Carica plugin**
4. Seleziona il file ZIP scaricato
5. Clicca **Installa ora**
6. Attiva il plugin

### File esclusi dal package

Il workflow esclude automaticamente:
- Directory `.git/`
- File `.gitignore`
- Directory `examples/` (solo per sviluppo)
- File di build temporanei
- File di configurazione IDE

### Personalizzazione

Per modificare quali file includere nel package, edita il file:
`.github/workflows/build-plugin.yml`

Nella sezione "Copy plugin files" puoi aggiungere o rimuovere file/directory.