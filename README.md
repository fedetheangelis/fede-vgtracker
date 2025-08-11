## [2025-08-10] - Versione 1.9.0

### Miglioramenti
- **Modal Dettagli Gioco**: Rivista completamente la modale dei dettagli per gli utenti non amministratori
  - Aggiunti tutti i voti disponibili (Voto, Aesthetic, OST, Difficoltà)
  - Inclusi i metadati completi (Ore di gioco, Percentuale trofei, Replay completati)
  - Aggiunte le date importanti (Prima volta giocato, Ultima volta finito, Platinato/Masterato in)
  - Interfaccia coerente con badge stilizzati e icone intuitive
  - Immagine di copertina con rapporto d'aspetto 16:9
  - Design responsive che si adatta a tutti i dispositivi

## [2025-08-07] - Versione 1.8.1

### Miglioramenti
- **Persistenza Sezione**: La pagina ora ricorda l'ultima sezione visitata (Giochi Giocati, Backlog, Statistiche, ecc.) e la ripristina al ricaricamento
- **Esperienza Utente**: Navigazione più fluida con il mantenimento dello stato tra i ricarichi della pagina

## [2025-08-07] - Versione 1.8.0

### Aggiunte
- **Tabella Completamenti Veloce**: Aggiunta tabella "Top 15 Giochi Completati Più Velocemente" nella sezione Statistiche
  - Supporto per formati di tempo multipli (ore, giorni, mesi, anni)
  - Gestione speciale per giochi con più voci (es: trilogie)
  - Indicazione specifica per giochi multipli (es: "(CB2)" per Crash Bandicoot N.Sane Trilogy)
  - Ordinamento automatico per tempo di completamento più veloce

### Miglioramenti
- **Parser Tempi di Completamento**:
  - Supporto per formati complessi (es: "X giorni, Y ore")
  - Gestione di più voci di tempo per lo stesso gioco
  - Migliorata la gestione degli errori per formati non standard

### Correzioni di Bug
- Risolto problema di visualizzazione per giochi con stati composti (es: "Masterato/Platinato")
- Corretto l'ordinamento dei tempi di completamento
- Migliorata la gestione dei valori mancanti o non validi

## [2025-07-28] - Versione 1.7.2

### Aggiunte
- **Pagina Changelog**: Aggiunta nuova sezione "Changelog" accessibile dal menu di navigazione
- **Pagina Informazioni**: Aggiunta nuova sezione "Informazioni sul sito" accessibile dal menu di navigazione
  - Dettagliate spiegazioni su stati, voti, difficoltà e altre metriche
  - Sezione dedicata alla descrizione dello scopo del sito
  - Informazioni su come vengono utilizzati i diversi tipi di voto
  - Spiegazione dettagliata di tutti gli stati dei giochi
- **Timestamp Aggiornamento**: Aggiunto sistema di tracciamento dell'ultimo aggiornamento
  - Visualizzazione dell'ultima modifica nella pagina Informazioni
  - Aggiornamento automatico del timestamp a ogni modifica dei dati
  - Versione del software sempre visibile

## [2025-07-26] - Versione 1.6.0

### Aggiunte
- **Tabella Ore di Gioco**: Aggiunta tabella "Top 15 Giochi con più Ore di Gioco" nella sezione Statistiche
  - Calcolo automatico del tempo di gioco totale da stringhe multiple (es: "412 PS + 7,9 PC + 2" = 422 ore)
  - Supporto per numeri decimali con virgola o punto
  - Ignoro di caratteri non numerici e simboli come "~"
  - Ordinamento decrescente per ore totali

## [2025-07-26] - Versione 1.5.0

### Aggiunte
- **Filtro Piattaforme**: Aggiunto menu a tendina con filtri per piattaforme (DIGITALE, FISICO, PS1, PS2, PS3, PS4, PS5, PC, SWITCH, 3DS, GBA, WII)
- **Filtro Stato**: Aggiunto menu a tendina con filtri per stato di completamento (Masterato/Platinato, Completato, Finito, In Pausa, In Corso, Droppato, Archiviato, Online/Senza Fine, Da Recuperare)
- **Pulsante di Ricerca**: Aggiunto pulsante dedicato per la ricerca dei giochi
- **Nuovi Stati per Backlog**: Aggiunti stati "da recuperare" e "da rigiocare" con colori personalizzati
- **Monitoraggio API**: Implementato sistema di tracciamento per le chiamate API con limitazione di 10 chiamate al minuto

### Modifiche
- **Interfaccia Utente**:
  - Aggiunto filtro per stato accanto al filtro piattaforme
  - Spostati i pulsanti "Importa TSV" e "Trova Cover Mancanti" nell'header
  - Rimosso il nome utente accanto al pulsante di logout, lasciando solo l'icona
  - Convertiti i pulsanti principali in pulsanti con solo icone per un design più pulito
  - Modificato il colore del pulsante "Trova Cover Mancanti" da arancione a grigio scuro (#333333)
  - Centrate le icone dei pulsanti nell'header per una migliore estetica
- **Backend**:
  - Aggiunti attributi data-platform e data-status alle card dei giochi per il filtraggio
  - Migliorata la gestione degli stati dei giochi nel backlog
  - Corretto il funzionamento del filtro per stato

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
