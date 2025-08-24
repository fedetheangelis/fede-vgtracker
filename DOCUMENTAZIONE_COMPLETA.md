# Documentazione Completa Game Tracker

## Indice
1. [Panoramica](#panoramica)
2. [Struttura del Progetto](#struttura-del-progetto)
3. [Funzionalità Dettagliate](#funzionalità-dettagliate)
4. [Design e Stile](#design-e-stile)
5. [Specifiche Tecniche](#specifiche-tecniche)
6. [Database](#database)
7. [API Endpoints](#api-endpoints)
8. [Sicurezza](#sicurezza)
9. [Performance](#performance)
10. [Installazione](#installazione)
11. [Manutenzione](#manutenzione)

## Panoramica
Game Tracker è un'applicazione web completa per la gestione della propria collezione di videogiochi, progettata per giocatori appassionati che vogliono tenere traccia dei propri progressi, backlog e statistiche di gioco.

## Struttura del Progetto
```
game-tracker/
├── assets/
│   ├── css/
│   │   └── style.css         # Stili principali
│   ├── js/
│   │   └── app.js           # Logica principale dell'applicazione
│   └── images/              # Immagini e icone
├── api/
│   ├── auth.php            # Gestione autenticazione
│   ├── games.php           # Gestione giochi
│   └── statistics.php      # Dati statistici
├── config/                 # File di configurazione
├── docs/                   # Documentazione
└── index.php              # Punto di ingresso principale
```

## Funzionalità Dettagliate

### 1. Gestione Giochi
- **Aggiunta Giochi**: 
  - Form multi-step con validazione in tempo reale
  - Autocompletamento campi basato su giochi esistenti
  - Supporto per piattaforme multiple con tag personalizzati
  - Caricamento copertine con anteprima istantanea
  - Calcolo automatico del punteggio complessivo
  
- **Modifica/Rimozione**:
  - Modale di modifica contestuale
  - Cronologia delle modifiche
  - Conferma eliminazione con possibilità di annullamento
  - Batch operations per operazioni multiple
  
- **Import/Export**:
  - Supporto per CSV, TSV, JSON
  - Mappatura personalizzata dei campi
  - Anteprima importazione con verifica errori
  - Esportazione personalizzabile con filtri
  
- **Ricerca Avanzata**:
  - Ricerca full-text su titolo, piattaforma, recensioni
  - Filtri combinati (AND/OR)
  - Salvataggio ricerche frequenti
  - Ricerca per intervallo di date

### 2. Sezioni Principali
1. **Giochi Giocati**
   - Visualizzazione a griglia/card con copertine
   - Dettaglio espandibile per ogni gioco
   - Filtri avanzati per:
     - Piattaforma (multi-selezione)
     - Stato di completamento
     - Valutazione (range 1-10)
     - Anno di rilascio/completamento
     - Tag personalizzati
   - Ordinamento per:
     - Data di aggiunta
     - Data di completamento
     - Titolo (A-Z/Z-A)
     - Valutazione
     - Ore di gioco
   - Filtri per piattaforma, stato, valutazione
   - Ordinamento personalizzabile
   - Statistiche rapide per i giochi visualizzati
   - Modalità compatto/esteso per la visualizzazione
   - Azioni rapide su hover (modifica, elimina, sposta in giocati)
   - Ricerca istantanea con suggerimenti

2. **Backlog**
   - **Gestione Priorità**:
     - Sistema di priorità da 0 (bassa) a 10 (massima)
     - Ordinamento automatico per priorità e data di aggiunta
     - Filtri rapidi per livello di priorità
     
   - **Stime e Pianificazione**:
     - Campo per stima ore di gioco necessarie
     - Calcolo automatico del tempo totale stimato
     - Pianificazione per periodo (settimanale/mensile)
     - Tracciamento progresso con barra di avanzamento
     
   - **Stati Avanzati**:
     - Da Giocare (default)
     - In Corso (con data di inizio)
     - In Pausa (con motivo opzionale)
     - Abbandonato (con motivazione)
     - Da Recuperare (per DLC/espansioni)
     - Da Rigiocare (per replay)
     
   - **Funzionalità Aggiuntive**:
     - Tag personalizzati per organizzazione
     - Note dettagliate per ogni gioco
     - Collegamenti a guide/trophy roadmap
     - Promemoria personalizzabili
     - Esportazione lista condivisibile

3. **Statistiche**
   - **Dashboard Interattiva**:
     - Panoramica completa con KPI principali
     - Widget personalizzabili
     - Intervalli temporali personalizzabili
     - Confronti tra periodi
     
   - **Analisi Giochi**:
     - Ore totali di gioco
     - Media ore per gioco
     - Distribuzione per piattaforma
     - Andamento completamenti nel tempo
     
   - **Classifiche**:
     - Top 15 giochi per ore di gioco
     - Top 15 completamenti più veloci
     - Giochi più difficili (per voto difficoltà)
     - Giochi migliori per voto complessivo
     
   - **Visualizzazioni**:
     - Grafici a torta per distribuzione
     - Istogrammi per confronti
     - Mappe termiche per attività
     - Timeline interattiva
     
   - **Esportazione Dati**:
     - Immagini ad alta risoluzione
     - Dati grezzi in CSV/JSON
     - Report personalizzabili
     - Pianificazione esportazioni automatiche

4. **Informazioni**
   - **Guida all'Uso**
     - Tutorial interattivo per nuovi utenti
     - Glossario dei termini
     - Domande frequenti (FAQ)
     - Video dimostrativi
     
   - **Metriche di Valutazione**
     - Spiegazione dettagliata del sistema di valutazione
     - Linee guida per l'assegnazione dei punteggi
     - Esempi pratici di valutazioni
     
   - **Informazioni di Sistema**
     - Versione attuale dell'applicazione
     - Data/ora ultimo aggiornamento
     - Stato dei servizi
     - Note sulla privacy e termini d'uso
     
   - **Supporto**
     - Contatti per assistenza
     - Segnalazione bug
     - Richieste funzionalità
     - Forum della community

5. **Changelog**
   - **Versioni**
     - Numero di versione con data di rilascio
     - Note di rilascio dettagliate
     - Link alle discussioni relative
     
   - **Modifiche**
     - Nuove funzionalità
     - Miglioramenti
     - Correzioni di bug
     - Modifiche alla sicurezza
     
   - **Storico**
     - Cronologia completa delle modifiche
     - Note di compatibilità
     - Istruzioni di aggiornamento
     - Note di deprecazione

## Design e Stile

### Tema
- **Colori Principali**:
  - Sfondo: #1a1a1a
  - Testo: #e0e0e0
  - Primario: #4a6fa5
  - Secondario: #6c757d
  - Successo: #28a745
  - Pericolo: #dc3545

### Tipografia
- **Font Principale**: 'Segoe UI', system-ui, sans-serif
- **Dimensione Base**: 16px
- **Scala Tipografica**: 1.2 (Major Third)

### Componenti UI
1. **Card Gioco**
   - Copertina gioco con fallback
   - Badge stato colorati
   - Pulsanti azione contestuali
   - Tooltip informativi

2. **Modali**
   - Animazioni fluide
   - Chiusura con ESC o click esterno
   - Form con validazione

3. **Filtri**
   - Dropdown con ricerca
   - Multi-selezione
   - Reset rapido

## Specifiche Tecniche

### Requisiti di Sistema
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- Estensione PDO abilitata
- mod_rewrite abilitato (per URL puliti)

### Struttura Dati
```json
{
  "game": {
    "id": "int",
    "title": "string",
    "platforms": "string[]",
    "status": "string",
    "scores": {
      "total": "number",
      "aesthetic": "number",
      "ost": "number",
      "difficulty": "number"
    },
    "playtime": "string",
    "dates": {
      "added": "datetime",
      "started": "date",
      "finished": "date",
      "last_played": "datetime"
    },
    "metadata": {
      "cover_url": "string",
      "review": "string",
      "notes": "string",
      "replay_count": "int"
    }
  }
}
```

## Database

### Schema Principale
```sql
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    platforms JSON NOT NULL,
    status VARCHAR(50) NOT NULL,
    total_score DECIMAL(3,1),
    aesthetic_score DECIMAL(3,1),
    ost_score DECIMAL(3,1),
    difficulty_score DECIMAL(3,1),
    playtime VARCHAR(100),
    first_played DATE,
    last_finished DATE,
    completion_date DATE,
    platinum_date DATE,
    cover_url TEXT,
    review TEXT,
    notes TEXT,
    replay_count INT DEFAULT 0,
    priority TINYINT,
    section ENUM('played', 'backlog') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT(title, platforms, review, notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## API Endpoints

### Autenticazione
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/status`

### Giochi
- `GET /api/games` - Lista giochi (filtrabile)
- `GET /api/games/:id` - Dettaglio gioco
- `POST /api/games` - Crea gioco
- `PUT /api/games/:id` - Aggiorna gioco
- `DELETE /api/games/:id` - Elimina gioco
- `POST /api/games/import` - Importa giochi
- `GET /api/games/export` - Esporta giochi

### Statistiche
- `GET /api/statistics/summary` - Riepilogo generale
- `GET /api/statistics/platforms` - Statistiche per piattaforma
- `GET /api/statistics/timeline` - Timeline completamenti
- `GET /api/statistics/top` - Top giochi (per vari criteri)

## Sicurezza

### Misure Implementate
1. **Autenticazione**
   - Hash password con bcrypt
   - Token JWT per le sessioni
   - Scadenza sessione configurable

2. **Protezione Dati**
   - Prepared statements per tutte le query
   - Sanitizzazione input/output
   - Validazione lato client e server

3. **Sicurezza HTTP**
   - Headers di sicurezza (CSP, HSTS, XSS)
   - Protezione CSRF
   - Rate limiting

## Performance

### Ottimizzazioni Frontend
- Lazy loading immagini
- Code splitting JavaScript
- Caching risorse statiche
- Minimizzazione asset

### Ottimizzazioni Backend
- Query ottimizzate
- Cache layer (APCu/Redis)
- Compressione GZIP
- Paginazione risultati

## Installazione

### Prerequisiti
- Server web (Apache/Nginx)
- PHP 7.4+
- MySQL/MariaDB
- Composer (per le dipendenze PHP)
- Node.js (per lo sviluppo)

### Procedura
1. Clonare il repository
2. Installare le dipendenze:
   ```bash
   composer install
   npm install
   ```
3. Configurare il database:
   ```bash
   mysql -u utente -p database < database/schema.sql
   ```
4. Configurare l'ambiente:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Configurare il server web per puntare alla cartella `public/`

## Manutenzione

### Aggiornamenti
1. Backup del database
2. `git pull` per aggiornare i file
3. `composer install` per aggiornare le dipendenze
4. `php artisan migrate` per aggiornare il database

### Monitoraggio
- Log PHP in `storage/logs/`
- Monitoraggio errori con Sentry
- Metriche prestazioni con New Relic

### Backup
- Backup automatico giornaliero del database
- Backup incrementali dei file di configurazione
- Archiviazione esterna crittografata

---
*Documentazione aggiornata all'11/08/2025*
