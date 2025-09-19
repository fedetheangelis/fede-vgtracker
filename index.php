<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize database
initializeDatabase();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1><i class="fas fa-gamepad"></i> Fede's Game Tracker</h1>
                <div class="auth-section">
                    <div id="user-info" style="display: none;">
                        <input type="file" id="tsv-file" accept=".tsv,.txt" style="display: none;">
                        <button class="btn-square btn-secondary admin-only" id="import-tsv-btn" onclick="document.getElementById('tsv-file').click()" title="Importa TSV">
                            <i class="fas fa-upload"></i>
                        </button>
                        <button class="btn-square btn-secondary admin-only" id="find-missing-covers-btn" onclick="findMissingCovers()" title="Trova Cover Mancanti">
                            <i class="fas fa-image"></i>
                        </button>
                        <button class="btn-square btn-secondary" onclick="logout()" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                    <button id="login-btn" class="btn-primary" onclick="openLoginModal()">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </div>
            <nav>
                <button class="nav-btn active" data-section="played">
                    <i class="fas fa-trophy"></i> Giochi Giocati
                </button>
                <button class="nav-btn" data-section="backlog">
                    <i class="fas fa-list"></i> Backlog
                </button>
                <button class="nav-btn" data-section="statistics">
                    <i class="fas fa-chart-bar"></i> Statistiche
                </button>
                <button class="nav-btn" data-section="info">
                    <i class="fas fa-info-circle"></i> Informazioni
                </button>
                <button class="nav-btn" data-section="changelog">
                    <i class="fas fa-history"></i> Changelog
                </button>
            </nav>
        </header>

        <main>
            <!-- Played Games Section -->
            <section id="played-section" class="game-section active">
                <div class="section-header">
                    <h2>Giochi Giocati</h2>
                    <div class="controls">
                        <div class="left-controls">
                            <div class="search-container">
                                <input type="text" id="search-played" placeholder="Cerca gioco..." onkeyup="filterGames('played')">
                                <button class="btn-primary btn-square" onclick="document.getElementById('search-played').focus()" title="Cerca">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <button class="btn-primary btn-square" onclick="openAddGameModal('played')" title="Aggiungi Gioco">
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="platform-filter">
                                <button class="btn-square btn-secondary" onclick="togglePlatformFilter()" title="Filtra per piattaforma">
                                    <i class="fas fa-gamepad"></i>
                                </button>
                                <div id="platform-filter-dropdown" class="platform-dropdown">
                                    <div class="platform-options">
                                        <label><input type="checkbox" name="platform" value="DIGITALE" onchange="filterByPlatform('played')"> DIGITALE</label>
                                        <label><input type="checkbox" name="platform" value="FISICO" onchange="filterByPlatform('played')"> FISICO</label>
                                        <label><input type="checkbox" name="platform" value="PS1" onchange="filterByPlatform('played')"> PS1</label>
                                        <label><input type="checkbox" name="platform" value="PS2" onchange="filterByPlatform('played')"> PS2</label>
                                        <label><input type="checkbox" name="platform" value="PS3" onchange="filterByPlatform('played')"> PS3</label>
                                        <label><input type="checkbox" name="platform" value="PS4" onchange="filterByPlatform('played')"> PS4</label>
                                        <label><input type="checkbox" name="platform" value="PS5" onchange="filterByPlatform('played')"> PS5</label>
                                        <label><input type="checkbox" name="platform" value="PC" onchange="filterByPlatform('played')"> PC</label>
                                        <label><input type="checkbox" name="platform" value="SWITCH" onchange="filterByPlatform('played')"> SWITCH</label>
                                        <label><input type="checkbox" name="platform" value="3DS" onchange="filterByPlatform('played')"> 3DS</label>
                                        <label><input type="checkbox" name="platform" value="GBA" onchange="filterByPlatform('played')"> GBA</label>
                                        <label><input type="checkbox" name="platform" value="WII" onchange="filterByPlatform('played')"> WII</label>
                                    </div>
                                </div>
                            </div>
                            <div class="status-filter">
                                <button class="btn-square btn-secondary" onclick="toggleStatusFilter()" title="Filtra per stato">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <div id="status-filter-dropdown" class="status-dropdown">
                                    <div class="status-options">
                                        <label><input type="checkbox" name="status" value="Masterato/Platinato" onchange="filterByStatus('played')"> Masterato/Platinato</label>
                                        <label><input type="checkbox" name="status" value="Completato (100%)" onchange="filterByStatus('played')"> Completato (100%)</label>
                                        <label><input type="checkbox" name="status" value="Finito" onchange="filterByStatus('played')"> Finito</label>
                                        <label><input type="checkbox" name="status" value="In Pausa" onchange="filterByStatus('played')"> In Pausa</label>
                                        <label><input type="checkbox" name="status" value="In Corso" onchange="filterByStatus('played')"> In Corso</label>
                                        <label><input type="checkbox" name="status" value="Droppato" onchange="filterByStatus('played')"> Droppato</label>
                                        <label><input type="checkbox" name="status" value="Archiviato" onchange="filterByStatus('played')"> Archiviato</label>
                                        <label><input type="checkbox" name="status" value="Online/Senza Fine" onchange="filterByStatus('played')"> Online/Senza Fine</label>
                                        <label><input type="checkbox" name="status" value="Da Recuperare" onchange="filterByStatus('played')"> Da Recuperare</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sort-controls">
                            <select id="sort-played">
                                <option value="title">Ordina per Titolo</option>
                                <option value="total_score">Voto Totale</option>
                                <option value="ost_score">Voto OST</option>
                                <option value="aesthetic_score">Voto Aesthetic</option>
                                <option value="playtime">Ore di Gioco</option>
                                <option value="difficulty">Difficoltà</option>
                                <option value="first_played">Prima volta giocato</option>
                                <option value="last_finished">Ultima volta finito</option>
                            </select>
                            <button class="btn-secondary" onclick="toggleSortOrder('played')">
                                <i class="fas fa-sort" id="sort-icon-played"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="played-games" class="games-grid"></div>
            </section>

            <!-- Backlog Section -->
            <section id="backlog-section" class="game-section">
                <div class="section-header">
                    <h2>Backlog</h2>
                    <div class="controls">
                        <div class="left-controls">
                            <div class="search-container">
                                <input type="text" id="search-backlog" placeholder="Cerca gioco..." onkeyup="filterGames('backlog')">
                                <button class="btn-primary btn-square" onclick="document.getElementById('search-backlog').focus()" title="Cerca">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <button class="btn-primary btn-square" onclick="openAddGameModal('backlog')" title="Aggiungi al Backlog">
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="platform-filter">
                                <button class="btn-square btn-secondary" onclick="togglePlatformFilter('backlog')" title="Filtra per piattaforma">
                                    <i class="fas fa-gamepad"></i>
                                </button>
                                <div id="platform-filter-dropdown-backlog" class="platform-dropdown">
                                    <div class="platform-options">
                                        <label><input type="checkbox" name="platform" value="DIGITALE" onchange="filterByPlatform('backlog')"> DIGITALE</label>
                                        <label><input type="checkbox" name="platform" value="FISICO" onchange="filterByPlatform('backlog')"> FISICO</label>
                                        <label><input type="checkbox" name="platform" value="PS1" onchange="filterByPlatform('backlog')"> PS1</label>
                                        <label><input type="checkbox" name="platform" value="PS2" onchange="filterByPlatform('backlog')"> PS2</label>
                                        <label><input type="checkbox" name="platform" value="PS3" onchange="filterByPlatform('backlog')"> PS3</label>
                                        <label><input type="checkbox" name="platform" value="PS4" onchange="filterByPlatform('backlog')"> PS4</label>
                                        <label><input type="checkbox" name="platform" value="PS5" onchange="filterByPlatform('backlog')"> PS5</label>
                                        <label><input type="checkbox" name="platform" value="PC" onchange="filterByPlatform('backlog')"> PC</label>
                                        <label><input type="checkbox" name="platform" value="SWITCH" onchange="filterByPlatform('backlog')"> SWITCH</label>
                                        <label><input type="checkbox" name="platform" value="3DS" onchange="filterByPlatform('backlog')"> 3DS</label>
                                        <label><input type="checkbox" name="platform" value="GBA" onchange="filterByPlatform('backlog')"> GBA</label>
                                        <label><input type="checkbox" name="platform" value="WII" onchange="filterByPlatform('backlog')"> WII</label>
                                    </div>
                                </div>
                            </div>
                            <div class="status-filter">
                                <button class="btn-square btn-secondary" onclick="toggleStatusFilter('backlog')" title="Filtra per stato">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <div id="status-filter-dropdown-backlog" class="status-dropdown">
                                    <div class="status-options">
                                        <label><input type="checkbox" name="status" value="Masterato/Platinato" onchange="filterByStatus('backlog')"> Masterato/Platinato</label>
                                        <label><input type="checkbox" name="status" value="Completato (100%)" onchange="filterByStatus('backlog')"> Completato (100%)</label>
                                        <label><input type="checkbox" name="status" value="Finito" onchange="filterByStatus('backlog')"> Finito</label>
                                        <label><input type="checkbox" name="status" value="In Pausa" onchange="filterByStatus('backlog')"> In Pausa</label>
                                        <label><input type="checkbox" name="status" value="In Corso" onchange="filterByStatus('backlog')"> In Corso</label>
                                        <label><input type="checkbox" name="status" value="Droppato" onchange="filterByStatus('backlog')"> Droppato</label>
                                        <label><input type="checkbox" name="status" value="Archiviato" onchange="filterByStatus('backlog')"> Archiviato</label>
                                        <label><input type="checkbox" name="status" value="Online/Senza Fine" onchange="filterByStatus('backlog')"> Online/Senza Fine</label>
                                        <label><input type="checkbox" name="status" value="Da Recuperare" onchange="filterByStatus('backlog')"> Da Recuperare</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sort-controls">
                            <select id="sort-backlog">
                                <option value="title">Ordina per Titolo</option>
                                <option value="platform">Piattaforma</option>
                                <option value="ost_score">Voto OST</option>
                                <option value="aesthetic_score">Voto Aesthetic</option>
                                <option value="added_date">Data Aggiunta</option>
                            </select>
                            <button class="btn-secondary" onclick="toggleSortOrder('backlog')">
                                <i class="fas fa-sort" id="sort-icon-backlog"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="backlog-games" class="games-grid"></div>
            </section>

            <!-- Statistics Section -->
            <section id="statistics-section" class="game-section">
                <div class="section-header">
                    <h2>Statistiche</h2>
                </div>
                <div id="statistics-content">
                    <div class="stats-grid">
                        <!-- Status Distribution -->
                        <div class="stat-card">
                            <h3>Distribuzione degli Stati</h3>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                            <div class="chart-table-container">
                                <div id="statusTable" class="chart-table"></div>
                            </div>
                        </div>

                        <!-- Platform Distribution -->
                        <div class="stat-card">
                            <h3>Distribuzione delle Piattaforme</h3>
                            <div class="chart-container">
                                <canvas id="platformChart"></canvas>
                            </div>
                            <div class="chart-table-container">
                                <div id="platformTable" class="chart-table"></div>
                            </div>
                        </div>

                        <!-- Difficulty Distribution -->
                        <div class="stat-card">
                            <h3>Distribuzione delle Difficoltà</h3>
                            <div class="chart-container">
                                <canvas id="difficultyChart"></canvas>
                            </div>
                            <div class="chart-table-container">
                                <div id="difficultyTable" class="chart-table"></div>
                            </div>
                        </div>
                        
                        <!-- Vote Distribution -->
                        <div class="stat-card">
                            <h3>Distribuzione Voti</h3>
                            <div class="chart-container">
                                <canvas id="voteDistributionChart"></canvas>
                            </div>
                            <div class="chart-table-container">
                                <div id="voteDistributionTable" class="chart-table"></div>
                            </div>
                        </div>

                        <!-- Played by Year -->
                        <div class="stat-card full-width">
                            <h3>Distribuzione degli Anni (prima volta giocato)</h3>
                            <div class="chart-container" style="height: 400px;">
                                <canvas id="playedByYearChart"></canvas>
                            </div>
                        </div>

                        <!-- Top Difficult Games -->
                        <div class="stat-card full-width">
                            <h3>Top 15 Giochi Più Difficili</h3>
                            <div class="chart-table-container">
                                <table id="difficultGamesTable">
                                    <thead>
                                        <tr>
                                            <th>Posizione</th>
                                            <th>Titolo</th>
                                            <th>Difficoltà</th>
                                            <th>Piattaforma</th>
                                            <th>Stato</th>
                                        </tr>
                                    </thead>
                                    <tbody id="difficultGamesBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Top Playtime Games -->
                        <div class="stat-card full-width">
                            <h3>Top 15 Giochi con più Ore di Gioco</h3>
                            <div class="chart-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Posizione</th>
                                            <th>Titolo</th>
                                            <th>Ore Totali</th>
                                            <th>Piattaforma</th>
                                            <th>Stato</th>
                                        </tr>
                                    </thead>
                                    <tbody id="playtimeTableBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Fastest Completions -->
                        <div class="stat-card full-width">
                            <h3>Top 15 Giochi Platinati/Masterati Più Velocemente</h3>
                            <div class="chart-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Posizione</th>
                                            <th>Titolo</th>
                                            <th>Tempo di Completamento</th>
                                            <th>Piattaforma</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fastestCompletionsBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Info Section -->
            <section id="info-section" class="game-section">
                <div class="section-header">
                    <h2>Informazioni sul sito</h2>
                    <?php
                    // Get the site metadata
                    $metadataFile = __DIR__ . '/site_metadata.json';
                    $metadata = [
                        'last_updated' => date('Y-m-d H:i:s'),
                        'version' => '1.0.0'
                    ];
                    
                    if (file_exists($metadataFile)) {
                        $fileContent = file_get_contents($metadataFile);
                        $fileMetadata = json_decode($fileContent, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $metadata = array_merge($metadata, $fileMetadata);
                        }
                    }
                    ?>
                    <div class="last-update">
                        <?php 
                        // Set the timezone to Rome for this display
                        date_default_timezone_set('Europe/Rome');
                        $date = new DateTime($metadata['last_updated']);
                        $date->setTimezone(new DateTimeZone('Europe/Rome'));
                        ?>
                        <small>Ultimo aggiornamento: <?php echo $date->format('d/m/Y H:i'); ?></small>
                    </div>
                </div>
                <div class="info-content">
                    <div class="info-section">
                        <h3>A cosa serve?</h3>
                        <p>Ho deciso di creare questa webapp per avere finalmente un posto dove tenere traccia e valutare tutti i giochi che ho giocato in questi anni.</p>
                        <p>L'idea mi è venuta considerando che non ho trovato siti simili ad Anilist dedicati a valutare e tenere traccia dei videogiochi. Conosco backloggd ma le metriche di valutazione erano troppo approssimative.</p>
                    </div>

                    <div class="info-section">
                        <h3>Cosa c'è nelle pagine?</h3>
                        <p>Una pagina contiene la lista dei giochi che ho già giocato/valutato, l'altra pagina (backlog) contiene tutti quei titoli che ho intenzione, prima o poi, di recuperare.</p>
                        <p>La pagina statistiche contiene grafici e tabelle varie che si aggiornano ogni volta che viene aggiunta una nuova entry nella lista o viene modificato un valore.</p>
                        <p>La pagina Changelog (fatta per il meme) contiene una cronologia generale dei cambiamenti più importanti che ho fatto al file e, in generale, il suo sviluppo.</p>
                    </div>

                    <div class="info-section">
                        <h3>Stato:</h3>
                        <ul class="status-list">
                            <li><strong>Masterato/Platinato</strong>: Include i giochi che ho platinato su PlayStation o masterato su RetroAchievements.org.</li>
                            <li><strong>Completato 100%</strong>: Include i giochi che ho finito al 100% ma non ho platinato/masterato.</li>
                            <li><strong>Finito</strong>: Include i giochi di cui ho completato solo le missioni principali, la modalità storia o simili.</li>
                            <li><strong>In pausa</strong>: Include i giochi che ho già iniziato e giocato, ma che prima o poi devo finire. A differenza del backlog, sono giochi già avviati.</li>
                            <li><strong>Droppato</strong>: Include i giochi che ho intenzionalmente abbandonato e non intendo finire. Tra i droppati possono apparire anche alcuni titoli online/senza fine, in quel caso significa che non ho più intenzione di riscaricare quel gioco.</li>
                            <li><strong>Archiviato</strong>: Include i giochi che ho droppato involontariamente per mancanza di tempo o di interesse e che forse (prima o poi) recupererò.</li>
                            <li><strong>Online/Senza Fine</strong>: Include i giochi multiplayer online che non ho completamente droppato o che in generale riscarico di tanto in tanto.</li>
                            <li><strong>DA RECUPERARE</strong>: Include i giochi che devo recuperare (Presente solo nel foglio di Backlog).</li>
                            <li><strong>In corso</strong>: Include i giochi che devo ancora finire e sto giocando. (Presente solo nel foglio di Backlog).</li>
                            <li><strong>Da Rigiocare</strong>: Include giochi già presenti nella lista completa ma che appaiono anche nel foglio di Backlog. Come dice il nome, sono giochi che ho intenzione di rigiocare.</li>
                        </ul>
                    </div>

                    <div class="info-section">
                        <h3>Tipi di voto:</h3>
                        <p>I tipi di voto sono 3, oltre quello generale ho deciso di aggiungere:</p>
                        <p><strong>Voto "Aesthetic":</strong><br>Utilizzato per valutare l'estetica di un gioco: paesaggi, mappe, ambientazioni, grafica</p>
                        <p><strong>Voto OST:</strong><br>Utilizzato per valutare le canzoni presenti nel gioco</p>
                    </div>

                    <div class="info-section">
                        <h3>Difficoltà:</h3>
                        <p>Abbastanza self-explainatory, varia da 0 a 10.</p>
                    </div>

                    <div class="info-section">
                        <h3>Replay completati:</h3>
                        <p>L'equivalente dei rewatch per un film o una serie TV. E' il numero di volte che ho rigiocato il gioco (arrivando alla fine/obiettivo principale).</p>
                    </div>

                    <div class="info-section">
                        <h3>Prima volta giocato:</h3>
                        <p>Una data approssimativa o in alcuni casi specifica del primo giorno in cui ho messo mano sul gioco.</p>
                    </div>

                    <div class="info-section">
                        <h3>Ultima volta finito:</h3>
                        <p>Il giorno/mese/anno in cui ho finito il gioco per l'ultima volta.</p>
                    </div>

                    <div class="info-section">
                        <h3>Platinato/Masterato in:</h3>
                        <p>Il tempo che ho impiegato per platinare il gioco (o masterarlo su <a href="https://retroachievements.org/user/Fedecrash02" target="_blank">retroachievements.org</a>)</p>
                    </div>

                    <div class="info-section">
                        <h3>Altre info varie:</h3>
                        <ul class="info-list">
                            <li>► Alcuni link immagine sono stati messi manualmente perché RAWG aveva problemi a differenziare i titoli (alcuni esempi sono i giochi di Ratchet & Clank).</li>
                            <li>► Alcuni link immagine non sono appartenenti a RAWG, in quel caso ho semplicemente preferito farlo perché non mi piaceva l'immagine che appariva sul sito.</li>
                            <li>► Se non vengono inseriti voti nelle sezioni soundtrack/aesthetic, significa che essi sono irrilevanti per il gioco. In altri casi può significare che non ho giocato abbastanza per fornire una valutazione.</li>
                            <li>► Il simbolo ~ (circa), viene utilizzato quando non ho date o info precise riguardo un videogioco.</li>
                            <li>► Nel foglio Backlog è presente un'ulteriore metrica in cui inserisco la priorità (da 0 a 10) con il quale ho intenzione di recuperare quel gioco.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Changelog Section -->
            <section id="changelog-section" class="game-section">
                <div class="section-header">
                    <h2>Changelog</h2>
                    <div class="last-update">
                        <?php 
                        // Set the timezone to Rome for this display
                        date_default_timezone_set('Europe/Rome');
                        $date = new DateTime($metadata['last_updated']);
                        $date->setTimezone(new DateTimeZone('Europe/Rome'));
                        ?>
                        <small>Ultimo aggiornamento: <?php echo $date->format('d/m/Y H:i'); ?></small>
                    </div>
                </div>
                <div class="changelog-content">
                    <?php 
                    $changelogContent = file_get_contents(__DIR__ . '/README.md');
                    // Convert markdown to HTML (we'll add a simple converter function)
                    $changelogContent = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $changelogContent);
                    $changelogContent = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $changelogContent);
                    $changelogContent = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $changelogContent);
                    $changelogContent = preg_replace('/^#### (.*$)/m', '<h4>$1</h4>', $changelogContent);
                    $changelogContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $changelogContent);
                    $changelogContent = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $changelogContent);
                    $changelogContent = nl2br($changelogContent);
                    echo $changelogContent;
                    ?>
                </div>
            </section>

            <!-- Import Section -->
            <section id="import-section" class="game-section">
                <div class="section-header">
                    <h2>Importa Giochi da TSV</h2>
                </div>
                <div class="import-container">
                    <div class="import-box">
                        <i class="fas fa-file-upload"></i>
                        <h3>Carica file TSV</h3>
                        <p>Seleziona il file TSV esportato da Google Sheets</p>
                        <input type="file" id="tsv-file" accept=".tsv,.txt" style="display: none;">
                        <button class="btn-primary" onclick="document.getElementById('tsv-file').click()">
                            <i class="fas fa-upload"></i> Scegli File
                        </button>
                        <div id="import-status" class="import-status"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Game Modal -->
    <div id="game-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Aggiungi Gioco</h3>
                <button class="modal-close" onclick="closeGameModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="game-form" onsubmit="event.preventDefault(); handleGameSubmit(event);" accept-charset="UTF-8">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="game-title">Titolo *</label>
                        <input type="text" id="game-title" name="title" required>
                        <button type="button" class="btn-secondary" onclick="searchGameCover()">
                            <i class="fas fa-search"></i> Cerca Cover
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="game-platform">Piattaforma</label>
                        <div class="platform-checkboxes">
                            <label><input type="checkbox" name="platform[]" value="DIGITALE"> DIGITALE</label>
                            <label><input type="checkbox" name="platform[]" value="FISICO"> FISICO</label>
                            <label><input type="checkbox" name="platform[]" value="PS1"> PS1</label>
                            <label><input type="checkbox" name="platform[]" value="PS2"> PS2</label>
                            <label><input type="checkbox" name="platform[]" value="PS3"> PS3</label>
                            <label><input type="checkbox" name="platform[]" value="PS4"> PS4</label>
                            <label><input type="checkbox" name="platform[]" value="PS5"> PS5</label>
                            <label><input type="checkbox" name="platform[]" value="PC"> PC</label>
                            <label><input type="checkbox" name="platform[]" value="SWITCH"> SWITCH</label>
                            <label><input type="checkbox" name="platform[]" value="3DS"> 3DS</label>
                            <label><input type="checkbox" name="platform[]" value="GBA"> GBA</label>
                            <label><input type="checkbox" name="platform[]" value="WII"> WII</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="game-playtime">Ore di Gioco</label>
                        <input type="text" id="game-playtime" name="playtime">
                    </div>

                    <div class="form-group">
                        <label for="game-total-score">Voto Totale (0-100)</label>
                        <input type="number" id="game-total-score" name="total_score" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label for="game-aesthetic-score">Voto Aesthetic (0-100)</label>
                        <input type="number" id="game-aesthetic-score" name="aesthetic_score" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label for="game-ost-score">Voto OST (0-100)</label>
                        <input type="number" id="game-ost-score" name="ost_score" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label for="game-difficulty">Difficoltà (0-10)</label>
                        <input type="number" id="game-difficulty" name="difficulty" min="0" max="10">
                    </div>

                    <div class="form-group">
                        <label for="game-status">Stato</label>
                        <select id="game-status" name="status">
                            <option value="">Seleziona stato</option>
                            <option value="Masterato/Platinato">Masterato/Platinato</option>
                            <option value="Completato (100%)">Completato (100%)</option>
                            <option value="Finito">Finito</option>
                            <option value="DA RIGIOCARE">DA RIGIOCARE</option>
                            <option value="In Pausa">In Pausa</option>
                            <option value="In Corso">In Corso</option>
                            <option value="Droppato">Droppato</option>
                            <option value="Archiviato">Archiviato</option>
                            <option value="Online/Senza Fine">Online/Senza Fine</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="game-trophy-percentage">% Trofei (0-100)</label>
                        <input type="number" id="game-trophy-percentage" name="trophy_percentage" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label for="game-platinum-date">Platino/Masterato in</label>
                        <input type="text" id="game-platinum-date" name="platinum_date">
                    </div>

                    <div class="form-group">
                        <label for="game-replays">Replay completati</label>
                        <input type="number" id="game-replays" name="replays" min="0">
                    </div>

                    <div class="form-group">
                        <label for="game-first-played">Prima volta giocato</label>
                        <input type="text" id="game-first-played" name="first_played">
                    </div>

                    <div class="form-group">
                        <label for="game-last-finished">Ultima volta finito</label>
                        <input type="text" id="game-last-finished" name="last_finished">
                    </div>

                    <div class="form-group full-width">
                        <label for="game-review">Recensione</label>
                        <textarea id="game-review" name="review" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="game-cover">URL Cover</label>
                        <input type="url" id="game-cover" name="cover_url">
                    </div>
                </div>

                <div class="cover-preview">
                    <img id="cover-preview" src="" alt="Cover preview" style="display: none;">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeGameModal()">Annulla</button>
                    <button type="submit" class="btn-primary">Salva Gioco</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Admin Login</h3>
                <button class="modal-close" onclick="closeLoginModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="login-form">
                <div class="form-group">
                    <label for="login-password">Password Admin</label>
                    <input type="password" id="login-password" name="password" required autofocus>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeLoginModal()">Annulla</button>
                    <button type="submit" class="btn-primary">Accedi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Game Details Modal (for non-admin users) -->
    <div id="game-details-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 id="game-details-title">Dettagli Gioco</h3>
                <button class="modal-close" onclick="closeGameDetailsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="game-details-content">
                <div class="game-details-grid">
                    <div class="game-cover-container">
                        <img id="game-details-cover" src="" alt="Game Cover" class="game-details-cover">
                    </div>
                    <div class="game-info-container">
                        <div class="game-meta">
                            <span id="game-details-platform" class="platform-badge"></span>
                            <span id="game-details-status" class="status-badge"></span>
                        </div>
                        <div id="game-details-scores" class="game-scores"></div>
                        <div id="game-details-dates" class="game-dates"></div>
                    </div>
                </div>
                <div class="game-review-container">
                    <h4>Recensione</h4>
                    <div id="game-details-review" class="game-review"></div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeGameDetailsModal()">Chiudi</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
