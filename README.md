# FEDE'S VG TRACKER - RELEASE 1.0.0

### Novità in questa Versione
- **Release Ufficiale**: Primo release stabile pubblicato su InfinityFree
- **Miglioramenti alla Stabilità**: Sistema più affidabile e reattivo
- **Ottimizzazioni**: Migliorate le prestazioni generali dell'applicazione

## [2025-09-15] - Versione 0.9.8
### Correzioni di Bug
- **Gestione Piattaforme**:
  - **Problema**: Le piattaforme non venivano salvate correttamente durante la creazione di un nuovo gioco
  - **Soluzione**:
    1. Allineata la gestione delle piattaforme tra creazione e modifica
    2. Migliorata la sincronizzazione tra frontend e backend
    3. Aggiunto logging dettagliato per il debug

## [2025-09-15] - Versione 0.9.7
### Miglioramenti
- **Gestione Caratteri Speciali**:
  - **Problema**: I caratteri speciali (è, ò, à, ù) non venivano visualizzati correttamente nei nuovi giochi aggiunti
  - **Soluzione**:
    1. Aggiornata la connessione al database per forzare l'uso di UTF-8
    2. Aggiunto supporto esplicito per utf8mb4 in tutte le connessioni
    3. Migliorata la gestione della codifica nei dati in entrata e in uscita
  - **Miglioramenti Aggiuntivi**:
    - Aggiunta validazione della codifica in fase di inserimento
    - Migliorata la gestione degli errori di codifica
    - Aggiunti controlli di consistenza per i caratteri speciali

## [2025-09-14] - Versione 0.9.6
### Miglioramenti
- Ripristinata opzione "DA RIGIOCARE" nel menù a tendina per la modifica di un gioco
- **Ricerca Avanzata Cover**:
  - **Problema**: La ricerca delle cover non riusciva a trovare le immagini nonostante fossero presenti nell'API RAWG
  - **Tentativi Falliti**:
    1. Utilizzo di un solo campo per la ricerca dell'URL dell'immagine
    2. Mancata gestione della struttura nidificata delle risposte API
    3. Assenza di meccanismi di fallback per titoli complessi
  - **Soluzione**:
    1. Implementata ricerca in profondità nei campi `cover_url` e `background_image`
    2. Aggiunto supporto per la struttura nidificata delle risposte API
    3. Inserito automaticamente il parametro `crop/600/400/` negli URL delle immagini
    4. Aggiunto sistema di fallback per titoli con parentesi o trattini
  - **Miglioramenti Aggiuntivi**:
    - Log dettagliato per il debug
    - Gestione degli errori migliorata
    - Feedback utente più chiaro durante la ricerca

## [2025-09-14] - Versione 0.9.5
### Problemi Risolti
- **Gestione Piattaforme nel Form di Modifica**:
  - **Problema**: Le piattaforme non venivano salvate correttamente durante la modifica di un gioco, mostrando "Array" invece delle piattaforme selezionate
  - **Tentativi Falliti**:
    1. Modifica del solo lato client per la gestione delle checkbox
    2. Tentativo di forzare il formato dei dati senza aggiornare il backend
  - **Soluzione**:
    1. Aggiornato il frontend per inviare l'array delle piattaforme con il nome `platforms`
    2. Aggiunta logica nel backend per convertire l'array in una stringa separata da virgole
    3. Migliorata la gestione dei valori nulli per i campi opzionali
    4. Aggiunto logging dettagliato per il debug
  - **Miglioramenti Aggiuntivi**:
    - Migliorata la gestione degli errori durante l'aggiornamento
    - Aggiunta validazione per i dati delle piattaforme
    - Migliorata la documentazione del codice

## [2025-09-14] - Versione 0.9.4
### Problemi Risolti
- **Modifica Dati Gioco**:
  - **Problema**: Il form di modifica non mostrava i dati esistenti del gioco
  - **Tentativi Falliti**:
    1. Utilizzo di ID dei campi non corrispondenti tra HTML e JavaScript
    2. Tentativo di gestire la struttura annidata della risposta API in modo complesso
  - **Soluzione**:
    1. Semplificata la logica di estrazione dei dati dalla risposta API
    2. Corretti gli ID dei campi del form per corrispondere esattamente agli attributi `name`
    3. Implementata gestione dei valori nulli/undefined
    4. Aggiunto supporto per i formati di data
    5. Migliorato il logging per il debug

## [2025-09-14] - Versione 0.9.3
### Problemi Risolti
- **Ordinamento Priorità Backlog**:
  - **Problema**: L'ordinamento per priorità nel backlog non funzionava correttamente, mostrando i giochi in ordine alfabetico invece che per priorità
  - **Tentativi Falliti**:
    1. Modifica del solo lato client senza forzare l'ordinamento
    2. Tentativo di gestire l'ordinamento solo lato server
    3. Utilizzo di `currentSort` e `currentOrder` senza forzarne i valori
  - **Soluzione**:
    1. Modificata la logica di ordinamento per forzare l'ordinamento per priorità (decrescente) e titolo (crescente) per il backlog
    2. Aggiunto salvataggio della posizione di scorrimento durante l'aggiornamento delle priorità
    3. Implementato refresh automatico della lista dopo l'aggiornamento di una priorità
    4. Aggiunto `data-game-id` agli elementi del gioco per un targeting affidabile
  - **Miglioramenti Aggiuntivi**:
    - Log dettagliato per il debug
    - Gestione dello stato di caricamento migliorata
    - Ripristino della posizione di scorrimento dopo l'aggiornamento

## [2025-09-14] - Versione 0.9.2
### Problemi Risolti
- **Salvataggio Dati Gioco**:
  - **Problema**: Il form di aggiunta/modifica giochi non salvava correttamente tutti i campi (punteggi, tempo di gioco, recensioni, ecc.)
  - **Tentativi Falliti**:
    1. Modifica del solo lato client per la raccolta dei dati dal form
    2. Tentativo di forzare il tipo di dati nel form HTML
  - **Soluzione**:
    1. Aggiornato il gestore delle richieste POST in `games_improved.php` per processare correttamente tutti i campi del form
    2. Aggiunta conversione esplicita dei tipi per i campi numerici
    3. Gestione corretta delle piattaforme multiple
    4. Conversione dei valori vuoti in NULL per i campi opzionali
  - **Miglioramenti Aggiuntivi**:
    - Migliore gestione degli errori
    - Validazione più robusta dei dati in input
    - Supporto per valori numerici opzionali


## [2025-09-14] - Versione 0.9.1
### Miglioramenti
- **Semplificazione Autenticazione**: 
  - Rimosso il campo username, ora è richiesta solo la password per l'accesso admin
  - Interfaccia di login semplificata con focus automatico sul campo password
  - Messaggi di feedback migliorati per l'utente
  - Aggiunto autofocus al campo password per un accesso più rapido

- **Accesso Admin**: 
  - Corretto il problema che impediva il login dopo la rimozione del campo username
  - Migliorata la gestione degli errori durante il login
  - Aggiunto feedback visivo durante il tentativo di accesso

### Problemi Risolti
- **Eliminazione Giochi**:
  - **Problema**: L'eliminazione dei giochi falliva con l'errore "Missing required field: title"
  - **Tentativi Falliti**:
    1. Aggiunta di un titolo fittizio nella richiesta di eliminazione
    2. Modifica della validazione lato server per saltare i controlli durante l'eliminazione
    3. Conversione della richiesta in formato form-data invece di JSON
  - **Soluzione Funzionante**:
    1. Spostamento della gestione dell'eliminazione all'inizio del gestore delle richieste POST
    2. Gestione esplicita dei parametri di eliminazione sia da GET che da POST
    3. Reindirizzamento alla pagina precedente dopo l'eliminazione invece di restituire JSON
    4. Aggiunta di controlli specifici per l'azione di eliminazione
  - **Miglioramenti Aggiuntivi**:
    - Feedback visivo durante l'eliminazione
    - Gestione degli errori più robusta
    - Migliore esperienza utente con reindirizzamento automatico

- **Problemi di Visualizzazione Dopo Migrazione**:
  - **Sintomo**: Dopo la migrazione a InfinityFree, l'elenco giochi e le statistiche non venivano visualizzati
  - **Cause e Soluzioni**:
    1. **Connessione al Database**
       - **Problema**: Le credenziali del database su InfinityFree erano diverse da quelle in locale
       - **Soluzione**: Aggiornato il file `config/database.php` con le credenziali corrette di InfinityFree

    2. **Percorsi Assoluti**
       - **Problema**: I percorsi assoluti non funzionavano correttamente su InfinityFree
       - **Soluzione**: Convertiti tutti i percorsi in percorsi relativi e verificata la struttura delle directory

    3. **Configurazione CORS**
       - **Problema**: Le richieste API venivano bloccate a causa delle policy CORS
       - **Soluzione**: Aggiunte le intestazioni CORS corrette nel file `api/auth.php`

    4. **Permessi File**
       - **Problema**: Impossibile accedere ai file di configurazione
       - **Soluzione**: Impostati i permessi corretti (755 per le cartelle, 644 per i file)

    5. **Sessioni PHP**
       - **Problema**: La sessione non veniva mantenuta tra le richieste
       - **Soluzione**: Aggiunto `session_start()` all'inizio di ogni file PHP e verificata la configurazione del percorso delle sessioni

  - **Note Aggiuntive**:
    - Verificata la configurazione PHP su InfinityFree
    - Aggiunto controllo degli errori dettagliato per semplificare il debug

## [2025-08-10] - Versione 0.9.0
### Miglioramenti
- **Modal Dettagli Gioco**: Rivista completamente la modale dei dettagli per gli utenti non amministratori
  - Aggiunti tutti i voti disponibili (Voto, Aesthetic, OST, Difficoltà)
  - Inclusi i metadati completi (Ore di gioco, Percentuale trofei, Replay completati)
  - Aggiunte le date importanti (Prima volta giocato, Ultima volta finito, Platinato/Masterato in)
  - Interfaccia coerente con badge stilizzati e icone intuitive
  - Immagine di copertina con rapporto d'aspetto 16:9
  - Design responsive che si adatta a tutti i dispositivi

### Problemi Riscontrati e Soluzioni
- **Errore di visualizzazione su dispositivi mobili**: Le card dei giochi non si adattavano correttamente agli schermi piccoli. Risolto aggiungendo media query specifiche e migliorando il layout flessibile.
- **Problemi di prestazioni con molte immagini**: Implementato un sistema di lazy loading per le immagini e ottimizzato il caricamento delle copertine.
- **Incoerenza nei formati data/ora**: Standardizzato il formato delle date in tutta l'applicazione per evitare discrepanze di visualizzazione.

## [2025-08-07] - Versione 0.8.1
### Miglioramenti
- **Persistenza Sezione**: La pagina ora ricorda l'ultima sezione visitata (Giochi Giocati, Backlog, Statistiche, ecc.) e la ripristina al ricaricamento
- **Esperienza Utente**: Navigazione più fluida con il mantenimento dello stato tra i ricarichi della pagina

### Problemi Riscontrati e Soluzioni
- **Perdita dello stato al refresh**: Implementato il salvataggio in localStorage per mantenere lo stato della navigazione tra i ricarichi della pagina.
- **Problemi di sincronizzazione tra schede**: Aggiunto un sistema di notifica per avvisare l'utente quando i dati vengono modificati da un'altra scheda.

## [2025-08-07] - Versione 0.8.0
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

### Problemi Riscontrati e Soluzioni
- **Errore di parsing per formati di tempo non standard**: Implementato un parser più robusto per gestire diversi formati di tempo (ore, giorni, mesi, anni).
- **Ordinamento non corretto dei tempi di completamento**: Corretto l'algoritmo di ordinamento per considerare correttamente le unità di tempo.
- **Visualizzazione errata per giochi con stati composti**: Aggiunta logica per gestire correttamente stati come "Masterato/Platinato".

## [2025-07-28] - Versione 0.7.2
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

### Problemi Riscontrati e Soluzioni
- **Mancanza di tracciamento delle modifiche**: Implementato un sistema di timestamp per tenere traccia degli aggiornamenti.
- **Incoerenza nella visualizzazione delle versioni**: Standardizzato il formato di visualizzazione della versione in tutta l'applicazione.
- **Problemi di caching**: Aggiunti header di cache appropriati per assicurare che gli utenti ricevano sempre l'ultima versione del software.

## [2025-07-26] - Versione 0.6.0
### Aggiunte
- **Tabella Ore di Gioco**: Aggiunta tabella "Top 15 Giochi con più Ore di Gioco" nella sezione Statistiche
  - Calcolo automatico del tempo di gioco totale da stringhe multiple (es: "412 PS + 7,9 PC + 2" = 422 ore)
  - Supporto per numeri decimali con virgola o punto
  - Ignoro di caratteri non numerici e simboli come "~"
  - Ordinamento decrescente per ore totali

### Problemi Riscontrati e Soluzioni
- **Calcolo errato delle ore di gioco**: Implementato un parser per sommare correttamente i tempi di gioco da stringhe complesse (es: "412 PS + 7,9 PC + 2").
- **Gestione di formati numerici diversi**: Aggiunto supporto per numeri con virgola e punto come separatore decimale.
- **Filtri non funzionanti**: Corretta la logica di filtraggio per gestire correttamente i casi limite.

## [2025-07-26] - Versione 0.5.0
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

### Problemi Riscontrati e Soluzioni
- **Problemi di usabilità su mobile**: Migliorata la risposta tattile dei menu a tendina su dispositivi touch.
- **Incoerenza nei colori degli stati**: Standardizzati i colori per gli stati in tutta l'applicazione.
- **Problemi di accessibilità**: Migliorato il contrasto e la navigazione da tastiera per i filtri.
- **Errore nell'ordinamento della priorità**: Risolto un problema che causava l'ordinamento errato dei giochi in base alla priorità.

## [2025-07-24] - Versione 0.4.0
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

## Migrazione a InfinityFree

### Problemi Riscontrati e Soluzioni
1. **Problemi di Connessione al Database**
   - **Sintomo**: Le pagine non mostravano i giochi né le statistiche
   - **Causa**: Le credenziali del database su InfinityFree erano diverse da quelle in locale
   - **Soluzione**: 
     - Aggiornato il file `config/database.php` con le credenziali corrette di InfinityFree
     - Verificato che il nome del database corrispondesse a quello fornito da InfinityFree
     - Controllato che l'utente del database avesse i permessi corretti

2. **Problemi di Percorsi**
   - **Sintomo**: Errori 404 per file CSS, JS e immagini
   - **Causa**: I percorsi assoluti non funzionavano correttamente su InfinityFree
   - **Soluzione**: 
     - Convertiti tutti i percorsi in percorsi relativi
     - Verificato che la struttura delle directory corrispondesse a quella del server
     - Aggiunto file `.htaccess` per la gestione dei reindirizzamenti

3. **Problemi di Permessi**
   - **Sintomo**: Impossibile scrivere file o aggiornare il database
   - **Causa**: I permessi delle directory su InfinityFree erano troppo restrittivi
   - **Soluzione**:
     - Impostati i permessi corretti (755 per le cartelle, 644 per i file)
     - Verificato che la cartella di upload avesse i permessi di scrittura

4. **Problemi di Configurazione PHP**
   - **Sintomo**: Errori PHP o funzionalità mancanti
   - **Causa**: Configurazione PHP diversa tra ambiente locale e InfinityFree
   - **Soluzione**:
     - Verificato che tutte le estensioni PHP necessarie fossero abilitate
     - Aggiornato il codice per utilizzare solo funzionalità supportate
     - Aggiunto controllo delle estensioni richieste all'avvio

5. **Problemi di Timeout**
   - **Sintomo**: Le operazioni lunghe (come l'importazione) andavano in timeout
   - **Causa**: Limiti di esecuzione su InfinityFree
   - **Soluzione**:
     - Implementata l'elaborazione in batch per le operazioni lunghe
     - Aggiunto feedback all'utente sullo stato delle operazioni
     - Ottimizzate le query per ridurre i tempi di esecuzione

### Note Importanti
- **Backup**: Prima di ogni aggiornamento, assicurarsi di fare un backup completo del database e dei file
- **Test**: Dopo ogni modifica, testare accuratamente tutte le funzionalità
- **Log**: Controllare regolarmente i log degli errori di PHP e del server web

### Problemi Riscontrati e Soluzioni
- **Errore durante l'aggiornamento della priorità**: Risolto un problema che causava errori di parsing JSON durante l'aggiornamento della priorità.
- **Perdita della priorità durante lo spostamento**: Corretto un bug che faceva perdere la priorità quando un gioco veniva spostato tra le sezioni.
- **Valori di default mancanti**: Aggiunti valori di default per evitare errori con le priorità non impostate.

## [2025-07-24] - Versione 0.3.0
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

### Problemi Riscontrati e Soluzioni
- **Lentezza nel caricamento delle statistiche**: Ottimizzate le query SQL e implementata la memorizzazione nella cache dei risultati.
- **Errore di visualizzazione degli anni mancanti**: Corretta la logica di estrazione degli anni dal campo "prima_volta_giocato".
- **Problemi di layout su schermi piccoli**: Migliorata la responsività dei grafici e delle tabelle statistiche.
- **Gestione dei dati mancanti**: Aggiunta logica per gestire correttamente i casi in cui i dati statistici sono parzialmente mancanti.

## [2025-07-22] - Versione 0.1.0
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

### Problemi Riscontrati e Soluzioni
- **Problemi di caricamento delle immagini**: Implementato un sistema di fallback per le copertine mancanti.
- **Allungamento delle card**: Corretto il layout delle card per mantenere un'altezza uniforme.
- **Formattazione incoerente delle piattaforme**: Standardizzato il formato di visualizzazione delle piattaforme.
- **Problemi di accessibilità**: Migliorato il contrasto e la leggibilità dei testi.
- **Gestione degli errori API**: Migliorata la gestione degli errori durante le chiamate API esterne.

## [2025-07-21] - Versione 0.0.1
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

### Requisiti di Sistema
- PHP 7.4+
- MySQL 5.7+ o MariaDB 10.3+
- Server web (Apache/Nginx)
- Browser moderno (Chrome, Firefox, Safari, Edge)