# Changelog

## [2025-07-26] - Versione 1.5.0

### Aggiunte
- **Filtro Piattaforme**: Aggiunto menu a tendina con filtri per piattaforme (DIGITALE, FISICO, PS1, PS2, PS3, PS4, PS5, PC, SWITCH, 3DS, GBA, WII)
- **Pulsante di Ricerca**: Aggiunto pulsante dedicato per la ricerca dei giochi
- **Nuovi Stati per Backlog**: Aggiunti stati "da recuperare" e "da rigiocare" con colori personalizzati
- **Monitoraggio API**: Implementato sistema di tracciamento per le chiamate API con limitazione di 10 chiamate al minuto

### Modifiche
- **Interfaccia Utente**:
  - Spostati i pulsanti "Importa TSV" e "Trova Cover Mancanti" nell'header
  - Rimosso il nome utente accanto al pulsante di logout, lasciando solo l'icona
  - Convertiti i pulsanti principali in pulsanti con solo icone per un design più pulito
  - Modificato il colore del pulsante "Trova Cover Mancanti" da arancione a grigio scuro (#333333)
  - Centrate le icone dei pulsanti nell'header per una migliore estetica
- **Backend**:
  - Aggiunto attributo data-platform alle card dei giochi per il filtraggio
  - Migliorata la gestione degli stati dei giochi nel backlog

### Correzioni di Bug
- Risolto problema di visualizzazione dei giochi nel filtro piattaforme
- Corretta la gestione degli errori di autenticazione
- Risolto problema di caricamento delle copertine mancanti
- Corretta la visualizzazione dei menu a tendina su dispositivi mobili

## [2025-07-24] - Versione 1.4.0

### Aggiunte
- **Sistema di Priorità per Backlog**: Dopo 4 ore buttate e 2 rollback,Implementata la possibilità di impostare priorità per i giochi nel backlog
- **Ordinamento Automatico**: I giochi nel backlog vengono ora ordinati automaticamente per priorità (dal più alto al più basso) e poi per titolo
- **Interfaccia Utente**: Aggiunto selettore di priorità nella pagina del backlog
- **API**: Aggiunto endpoint per l'aggiornamento della priorità

### Modifiche
- **Database**: Aggiunta colonna `priority` alla tabella `games`
- **Backend**: Aggiornate le funzioni di gestione giochi per supportare la priorità
- **Frontend**: Migliorata la gestione degli aggiornamenti in tempo reale delle priorità

### Correzioni di Bug
- Risolto problema di aggiornamento priorità che causava errori di parsing JSON
- Corretta la gestione della priorità durante lo spostamento dei giochi tra le sezioni
- Migliorata la gestione dei valori di default per la priorità

### Note Tecniche
- La priorità è un numero intero che va da 0 (bassa) a 10 (alta)
- I giochi con priorità più alta appaiono in cima alla lista del backlog
- La priorità viene mantenuta solo per i giochi nella sezione backlog

## [2025-07-24] - Versione 1.3.0

### Aggiunte
- **Istogramma Voti**: Aggiunto grafico a barre verticali per la distribuzione dei voti totali (raggruppati per intervalli di 10)
- **Tabella Dettagliata Voti**: Inclusa tabella con conteggio e percentuale per ogni intervallo di voti
- **Grafico a Linee**: Implementato grafico per la distribuzione degli anni di prima giocata
- **Grafici a Torta**: Aggiunti grafici per la distribuzione di stati, piattaforme e difficoltà
- **Tabelle Dettagliate**: Incluso il dettaglio delle statistiche in formato tabellare

### Modifiche
- **Interfaccia Utente**:
  - Aggiunto un nuovo tab "Statistiche" nel menu principale
  - Migliorata la responsività dei grafici per dispositivi mobili
  - Ottimizzato il layout della pagina delle statistiche
  - Aggiunto stile personalizzato per le tabelle delle statistiche

- **Grafici**:
  - Implementato tema scuro per i grafici per coerenza con il design del sito
  - Aggiunti tooltip informativi per una migliore leggibilità
  - Ottimizzata la visualizzazione delle etichette sugli assi

### Correzioni di Bug
- Risolto problema di sovrapposizione del menu a scorrimento nella pagina delle statistiche
- Corretta la gestione degli errori durante il caricamento delle statistiche
- Risolto problema con la visualizzazione degli anni nel grafico di distribuzione
- Migliorata la gestione dei dati mancanti nei grafici

### Note Tecniche
- Aggiunto endpoint `/api/statistics.php` per il recupero dei dati statistici
- Implementata la logica per l'estrazione degli anni dal campo "prima_volta_giocato"
- Aggiunto supporto per la visualizzazione di dati in formato tabellare
- Ottimizzate le query SQL per il calcolo delle statistiche

## [2025-07-22] - Versione 1.1.0

### Aggiunte
- **Funzionalità Admin**: Aggiunto pulsante "Trova Cover Mancanti" accessibile solo agli amministratori
- **Ricerca Copertine**: Implementata API per la ricerca e aggiornamento automatico delle copertine mancanti
- **Rate Limiting**: Aggiunto sistema di rate limiting (2 richieste/secondo) per le chiamate API esterne
- **Notifiche**: Aggiunto sistema di notifiche per feedback all'utente durante le operazioni

### Modifiche
- **Interfaccia Utente**:
  - Rimosso lo sfondo dai badge delle piattaforme per un aspetto più pulito
  - Ottimizzato lo spazio tra i badge delle piattaforme
  - Aggiunto stile speciale per le piattaforme DIGITALE/FISICO
  - Corretto lo stile dei badge di stato con i nuovi colori specificati
  - Modificato il titolo in "Fede's Game Tracker"

- **Formattazione Piattaforme**:
  - Le piattaforme DIGITALE/FISICO vengono ora mostrate per prime
  - FISICO viene mostrato prima di DIGITALE quando entrambi sono presenti
  - Aggiunto separatore ";" tra piattaforme speciali e altre piattaforme
  - Le altre piattaforme sono separate da virgole

- **Ottimizzazioni CSS**:
  - Corretto lo stile delle card per evitare allungamenti indesiderati
  - Migliorata la gestione dello spazio vuoto nelle card
  - Ottimizzato il layout per una migliore visualizzazione su dispositivi mobili
  - Aggiunte regole per garantire altezze consistenti

### Correzioni di Bug
- Risolto un problema che causava l'allungamento verticale delle card quando alcuni campi erano mancanti
- Corretta la formattazione dei badge delle piattaforme per evitare spaziatura eccessiva
- Risolto un problema con i selettori CSS non standard
- Corretta la visualizzazione dei badge di stato con i nuovi colori

### Note Tecniche
- Aggiunto endpoint API `/api/games.php?action=find_missing_covers` per la ricerca delle copertine mancanti
- Implementata funzione `findAndUpdateMissingCovers()` per la gestione della ricerca delle copertine
- Aggiornato il file JavaScript per supportare la nuova funzionalità
- Migliorata la gestione degli errori e il feedback all'utente

## [2025-07-21] - Versione 1.0.0

### Funzionalità Principali
- **Gestione Giochi**:
  - Aggiunta, modifica ed eliminazione di giochi
  - Organizzazione in sezioni (Giocati, Backlog)
  - Ricerca e filtraggio dei giochi
  - Ordinamento personalizzabile

- **Sistema di Autenticazione**:
  - Login/Logout utente
  - Area amministrativa protetta
  - Sistema di autenticazione sicuro

- **Gestione Copertine**:
  - Upload manuale delle copertine
  - Ricerca automatica delle copertine
  - Visualizzazione delle anteprime

- **Importazione Dati**:
  - Importazione da file TSV
  - Validazione dei dati importati
  - Feedback sull'importazione

- **Interfaccia Utente**:
  - Design responsive
  - Tema scuro personalizzabile
  - Card interattive per ogni gioco
  - Animazioni e transizioni fluide

### Caratteristiche Tecniche
- **Backend**:
  - Architettura modulare
  - API RESTful
  - Gestione dello stato con sessioni
  - Validazione degli input

- **Frontend**:
  - JavaScript vanilla per l'interattività
  - CSS moderno con variabili personalizzate
  - Gestione asincrona delle richieste
  - Feedback utente in tempo reale

- **Sicurezza**:
  - Protezione contro SQL injection
  - Validazione lato server e client
  - Gestione sicura delle sessioni
  - Controlli di accesso

### Installazione
1. Clonare il repository
2. Configurare il database in `config/database.php`
3. Importare lo schema del database
4. Avviare il server web
5. Accedere all'applicazione tramite browser

### Requisiti di Sistema
- PHP 7.4+
- MySQL 5.7+ o MariaDB 10.3+
- Server web (Apache/Nginx)
- Browser moderno (Chrome, Firefox, Safari, Edge)
