// Global variables
let currentSection = 'played';
let currentSort = { played: 'title', backlog: 'title' };
let currentOrder = { played: 'ASC', backlog: 'ASC' };
let editingGameId = null;
let imageLoadQueue = [];
let isLoadingImages = false;
const IMAGES_PER_BATCH = 20;
const BATCH_DELAY = 5000; // 5 seconds between batches

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin-only elements are hidden by default
    document.body.classList.remove('admin-logged-in');
    
    // Initialize core functionality
    checkAuthStatus();
    loadGames('played');
    setupEventListeners();
    
    // Set up TSV file upload
    const tsvFileInput = document.getElementById('tsv-file');
    if (tsvFileInput) {
        tsvFileInput.addEventListener('change', handleTSVUpload);
    }
    
    // Restore last visited section from localStorage
    const lastSection = localStorage.getItem('lastVisitedSection');
    if (lastSection && lastSection !== 'played') {
        // Small delay to ensure all elements are loaded
        setTimeout(() => {
            const sectionBtn = document.querySelector(`.nav-btn[data-section="${lastSection}"]`);
            if (sectionBtn) {
                sectionBtn.click();
            }
        }, 100);
    }
});

async function checkAuthStatus() {
    try {
        const response = await fetch('api/auth.php?action=status');
        const data = await response.json();
        
        if (data.success && data.user) {
            updateUIForAuthStatus(data.user);
        } else {
            updateUIForAuthStatus({ is_admin: false });
        }
    } catch (error) {
        console.error('Error checking auth status:', error);
        updateUIForAuthStatus({ is_admin: false });
    }
}

function updateUIForAuthStatus(user) {
    const loginBtn = document.getElementById('login-btn');
    const userInfo = document.getElementById('user-info');
    const addGameButtons = document.querySelectorAll('button[onclick*="openAddGameModal"]');
    
    // Update the global admin flag
    window.isAdminLoggedIn = user.is_admin || false;
    
    if (user.is_admin) {
        // Add admin class to body to show admin-only elements via CSS
        document.body.classList.add('admin-logged-in');
        
        // Show admin UI
        loginBtn.style.display = 'none';
        if (userInfo) userInfo.style.display = 'flex';
        
        // Show add game buttons
        addGameButtons.forEach(btn => {
            btn.style.display = 'inline-flex';
        });
        
        // Enable game card actions
        enableGameCardActions(true);
        
        // Set global admin status
        window.isAdminLoggedIn = true;
    } else {
        // Remove admin class from body to hide admin-only elements via CSS
        document.body.classList.remove('admin-logged-in');
        
        // Show guest UI
        loginBtn.style.display = 'inline-flex';
        if (userInfo) userInfo.style.display = 'none';
        
        // Hide add game buttons
        addGameButtons.forEach(btn => {
            btn.style.display = 'none';
        });
        
        // Disable game card actions
        enableGameCardActions(false);
        
        // Set global admin status
        window.isAdminLoggedIn = false;
    }
}

// Function to enable/disable game card actions based on admin status
function enableGameCardActions(enabled) {
    window.isAdminLoggedIn = enabled;
}

function setupEventListeners() {
    // Navigation buttons
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const section = this.dataset.section;
            switchSection(section);
        });
    });
    
    // Sort controls
    const sortPlayed = document.getElementById('sort-played');
    const sortBacklog = document.getElementById('sort-backlog');
    
    if (sortPlayed) {
        sortPlayed.addEventListener('change', function() {
            currentSort.played = this.value;
            loadGames('played');
        });
    }
    
    if (sortBacklog) {
        sortBacklog.addEventListener('change', function() {
            currentSort.backlog = this.value;
            loadGames('backlog');
        });
    }
    
    // Game form submission
    const gameForm = document.getElementById('game-form');
    if (gameForm) {
        gameForm.addEventListener('submit', handleGameSubmit);
    }
    
    // Cover URL input change
    const coverInput = document.getElementById('game-cover');
    if (coverInput) {
        coverInput.addEventListener('input', function() {
            updateCoverPreview(this.value);
        });
    }
    
    // Modal close on outside click
    const gameModal = document.getElementById('game-modal');
    if (gameModal) {
        gameModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGameModal();
            }
        });
    }
}

function switchSection(section) {
    // Hide all sections
    document.querySelectorAll('.game-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show the selected section
    document.getElementById(`${section}-section`).classList.add('active');
    
    // Update active button
    document.querySelectorAll('.nav-btn').forEach(btn => {
        if (btn.getAttribute('data-section') === section) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Update current section
    currentSection = section;
    
    // Save the current section to localStorage
    localStorage.setItem('lastVisitedSection', section);
    
    // Load games for this section if needed
    if (section === 'played' || section === 'backlog') {
        loadGames(section);
    } else if (section === 'statistics') {
        loadStatistics();
    }
}

async function loadGames(section, showLoading = true) {
    const container = document.getElementById(`${section}-games`);
    
    // Only show loading spinner if explicitly requested (initial load)
    if (showLoading) {
        container.innerHTML = '<div class="loading"></div>';
    }
    
    try {
        const response = await fetch(`api/games.php?action=list&section=${section}&sort=${currentSort[section]}&order=${currentOrder[section]}`);
        const data = await response.json();
        
        if (data.success) {
            displayGames(data.games, section);
        } else {
            container.innerHTML = '<p>Errore nel caricamento dei giochi</p>';
        }
    } catch (error) {
        console.error('Error loading games:', error);
        container.innerHTML = '<p>Errore di connessione</p>';
    }
}

function displayGames(games, section) {
    const container = document.getElementById(`${section}-games`);
    
    if (games.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-gamepad" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>Nessun gioco trovato in questa sezione</p>
            </div>
        `;
        return;
    }
    
    // Create new content
    const newContent = games.map(game => createGameCard(game, section)).join('');
    
    // If container has existing content, use smooth transition
    if (container.children.length > 0 && !container.querySelector('.loading')) {
        // Add fade-out class
        container.style.opacity = '0.5';
        container.style.transition = 'opacity 0.15s ease';
        
        // Update content after brief delay
        setTimeout(() => {
            container.innerHTML = newContent;
            container.style.opacity = '1';
            // Remove transition after animation
            setTimeout(() => {
                container.style.transition = '';
            }, 150);
        }, 75);
    } else {
        // Direct update for initial load or after loading spinner
        container.innerHTML = newContent;
    }
}

// Open game details modal with game information
function openGameDetailsModal(game) {
    const modal = document.getElementById('game-details-modal');
    const title = document.getElementById('game-details-title');
    const cover = document.getElementById('game-details-cover');
    const platform = document.getElementById('game-details-platform');
    const status = document.getElementById('game-details-status');
    const scores = document.getElementById('game-details-scores');
    const dates = document.getElementById('game-details-dates');
    const review = document.getElementById('game-details-review');

    // Set basic info
    title.textContent = game.title;
    cover.src = game.cover_url || 'assets/images/placeholder.jpg';
    cover.onerror = function() {
        this.src = 'assets/images/placeholder.jpg';
    };

    // Set platform and status badges
    platform.textContent = game.platform;
    platform.className = 'platform-badge';
    
    status.textContent = game.status;
    status.className = 'status-badge ' + getStatusClass(game.status);

    // Set scores
    scores.innerHTML = '';
    if (game.total_score) {
        const scoreBadge = document.createElement('div');
        scoreBadge.className = 'score-badge';
        scoreBadge.innerHTML = `<i class="fas fa-star"></i> Voto: ${game.total_score}/10`;
        scores.appendChild(scoreBadge);
    }

    if (game.difficulty) {
        const diffBadge = document.createElement('div');
        diffBadge.className = 'score-badge';
        diffBadge.innerHTML = `<i class="fas fa-tachometer-alt"></i> Difficoltà: ${game.difficulty}/10`;
        scores.appendChild(diffBadge);
    }

    // Set dates
    dates.innerHTML = '';
    if (game.completion_date) {
        const dateElement = document.createElement('div');
        dateElement.className = 'game-date';
        dateElement.innerHTML = `<i class="far fa-calendar-check"></i> Completato il: ${game.completion_date}`;
        dates.appendChild(dateElement);
    }

    if (game.completion_time) {
        const timeElement = document.createElement('div');
        timeElement.className = 'game-date';
        timeElement.innerHTML = `<i class="far fa-clock"></i> Tempo di completamento: ${game.completion_time}`;
        dates.appendChild(timeElement);
    }

    // Set review
    review.textContent = game.review || 'Nessuna recensione disponibile.';
    review.style.fontStyle = game.review ? 'normal' : 'italic';

    // Show the modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close game details modal
function closeGameDetailsModal() {
    const modal = document.getElementById('game-details-modal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal when clicking outside content
document.addEventListener('click', function(event) {
    const modal = document.getElementById('game-details-modal');
    if (event.target === modal) {
        closeGameDetailsModal();
    }
});

function createGameCard(game, section) {
    // Process platforms to handle DIGITALE/FISICO and other platforms
    const platforms = game.platform ? game.platform.split(',').map(p => p.trim()) : [];
    const status = game.status || '';
    
    // Separate DIGITALE/FISICO from other platforms
    const specialPlatforms = [];
    const otherPlatforms = [];
    
    platforms.forEach(platform => {
        const upperPlatform = platform.toUpperCase();
        if (upperPlatform === 'DIGITALE' || upperPlatform === 'FISICO') {
            specialPlatforms.push(platform.toUpperCase());
        } else if (platform.trim() !== '') {
            otherPlatforms.push(platform);
        }
    });
    
    // Sort special platforms: FISICO comes before DIGITALE if both exist
    specialPlatforms.sort((a, b) => {
        if (a === 'FISICO') return -1;
        if (b === 'FISICO') return 1;
        return 0;
    });
    
    // Create platform badges HTML
    let platformBadges = '';
    
    // Add special platforms (FISICO/DIGITALE) first
    if (specialPlatforms.length > 0) {
        platformBadges += `<span class="platform-badge special">${specialPlatforms.join(' + ')}</span>`;
    }
    
    // Add other platforms if they exist
    if (otherPlatforms.length > 0) {
        // Add semicolon separator if we have both special and other platforms
        if (specialPlatforms.length > 0) {
            platformBadges += '<span class="separator semicolon">; </span>';
        }
        
        // Add other platforms with commas
        platformBadges += otherPlatforms
            .map(platform => `<span class="platform-badge">${platform}</span>`)
            .join('<span class="separator">, </span>');
    }
    
    const statusClass = getStatusClass(game.status);
    
    // Handle cover image with simple loading
    let coverElement;
    if (game.cover_url && game.cover_url.trim() !== '' && isValidUrl(game.cover_url.trim())) {
        coverElement = `<img src="${game.cover_url.trim()}" alt="${game.title}" class="game-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                       <div class="game-cover no-cover" style="display:none;"><i class="fas fa-gamepad"></i><span>Immagine non presente</span></div>`;
    } else {
        coverElement = `<div class="game-cover no-cover"><i class="fas fa-gamepad"></i><span>Immagine non presente</span></div>`;
    }
    
    // Prepare review tooltip
    const reviewTooltip = game.review && game.review.trim() !== '' 
        ? `title="${escapeHtml(game.review).replace(/"/g, '&quot;').replace(/\n/g, ' ')}"`
        : '';
    
    // Check if admin is logged in to show/hide admin actions
    const isAdmin = window.isAdminLoggedIn || false;
    
    // For admins, use the existing openEditGameModal function with just the game ID
    // For non-admins, use openGameDetailsModal with the full game object
    const cardClickHandler = isAdmin 
        ? `onclick="event.stopPropagation(); openEditGameModal(${game.id})"` 
        : `onclick="openGameDetailsModal(${JSON.stringify(game).replace(/"/g, '&quot;')})"`;
        
    const cardCursor = 'cursor: pointer;';
    
    // Add priority display for backlog games
    const priorityDisplay = section === 'backlog' ? `
        <div class="game-priority" onclick="event.stopPropagation();">
            <span class="priority-label">Priorità:</span>
            ${isAdmin ? `
            <select class="priority-select" onchange="updateGamePriority(${game.id}, this.value)">
                ${Array.from({length: 11}, (_, i) => 
                    `<option value="${i}" ${game.priority == i ? 'selected' : ''}>${i}</option>`
                ).join('')}
            </select>` : 
            `<span class="priority-value">${game.priority !== null ? game.priority : 'N/A'}</span>`}
        </div>` : '';

    return `
        <div class="game-card" data-platform="${game.platform || ''}" data-status="${game.status || ''}" ${cardClickHandler} ${reviewTooltip} style="${cardCursor}">
            ${isAdmin ? `
            <div class="game-actions">
                <button class="action-btn" onclick="event.stopPropagation(); moveGame(${game.id}, '${section === 'played' ? 'backlog' : 'played'}')" title="${section === 'played' ? 'Sposta in Backlog' : 'Sposta in Giocati'}">
                    <i class="fas fa-${section === 'played' ? 'list' : 'trophy'}"></i>
                </button>
                <button class="action-btn delete-btn" onclick="event.stopPropagation(); deleteGame(${game.id})" title="Elimina">
                    <i class="fas fa-trash"></i>
                </button>
            </div>` : ''}
            ${coverElement}
            <div class="game-info">
                <h3 class="game-title">${escapeHtml(game.title)}</h3>
                <div class="game-details">
                    ${platforms.length > 0 ? `
                    <div class="game-detail platforms">
                        <strong>💻 Piattaforma:</strong>
                        <div class="platforms-container">${platformBadges}</div>
                    </div>` : ''}
                    
                    <!-- Scores and playtime row with emojis -->
                    ${(game.total_score || game.aesthetic_score || game.ost_score || game.playtime || game.difficulty || game.trophy_percentage) ? `
                    <div class="game-detail scores-row">
                        ${game.total_score ? `<span class="score-item score-colored" style="background-color: ${getScoreColor(game.total_score)}">⭐ ${game.total_score}/100</span>` : ''}
                        ${game.aesthetic_score ? `<span class="score-item score-colored" style="background-color: ${getScoreColor(game.aesthetic_score)}">🌌 ${game.aesthetic_score}/100</span>` : ''}
                        ${game.ost_score ? `<span class="score-item score-colored" style="background-color: ${getScoreColor(game.ost_score)}">🎶 ${game.ost_score}/100</span>` : ''}
                        ${game.playtime ? `<span class="score-item">⏳ ${escapeHtml(game.playtime)}h</span>` : ''}
                        ${game.difficulty ? `<span class="score-item score-colored" style="background-color: ${getDifficultyColor(game.difficulty)}">🔥 ${game.difficulty}/10</span>` : ''}
                        ${game.trophy_percentage ? `<span class="score-item">🏆 ${game.trophy_percentage}%</span>` : ''}
                    </div>` : ''}

                    ${game.status ? `<div class="game-detail"><strong>Stato:</strong> <span class="status-badge ${statusClass}">${escapeHtml(game.status)}</span></div>` : ''}

                    ${game.replays ? `<div class="game-detail"><strong>🔁 Replay:</strong> ${game.replays}</div>` : ''}
                    ${game.first_played ? `<div class="game-detail"><strong>🚀 Prima volta giocato:</strong> ${escapeHtml(game.first_played)}</div>` : ''}
                    ${game.last_finished ? `<div class="game-detail"><strong>🏁 Ultima volta finito:</strong> ${escapeHtml(game.last_finished)}</div>` : ''}
                    ${game.platinum_date ? `<div class="game-detail"><strong>🏅 Platinato/Masterato in:</strong> ${escapeHtml(game.platinum_date)}</div>` : ''}
                    ${priorityDisplay}
                </div>
            </div>
        </div>
    `;
}

function getScoreColor(score) {
    if (!score || score < 0) return 'var(--bg-tertiary)';
    
    // Clamp score between 0 and 100
    const clampedScore = Math.min(100, Math.max(0, score));
    
    // Calculate color based on score
    // 0-50: Red to Yellow gradient
    // 50-100: Yellow to Green gradient
    if (clampedScore <= 50) {
        // Red to Yellow (0-50)
        const ratio = clampedScore / 50;
        const red = 255;
        const green = Math.round(255 * ratio);
        const blue = 0;
        return `rgba(${red}, ${green}, ${blue}, 0.3)`;
    } else {
        // Yellow to Green (50-100)
        const ratio = (clampedScore - 50) / 50;
        const red = Math.round(255 * (1 - ratio));
        const green = 255;
        const blue = 0;
        return `rgba(${red}, ${green}, ${blue}, 0.3)`;
    }
}

function getDifficultyColor(difficulty) {
    if (!difficulty || difficulty < 0) return 'var(--bg-tertiary)';
    
    // Clamp difficulty between 0 and 10
    const clampedDifficulty = Math.min(10, Math.max(0, difficulty));
    
    // Convert to 0-1 scale for color calculation
    const ratio = clampedDifficulty / 10;
    
    // Purple gradient: Light purple (easy) to Dark purple (hard)
    // Light purple: #f1e3ff (241, 227, 255)
    // Dark purple: #8a2be2 (138, 43, 226)
    const lightPurple = { r: 241, g: 227, b: 255 };
    const darkPurple = { r: 138, g: 43, b: 226 };
    
    // Interpolate between light and dark purple
    const red = Math.round(lightPurple.r + (darkPurple.r - lightPurple.r) * ratio);
    const green = Math.round(lightPurple.g + (darkPurple.g - lightPurple.g) * ratio);
    const blue = Math.round(lightPurple.b + (darkPurple.b - lightPurple.b) * ratio);
    
    return `rgba(${red}, ${green}, ${blue}, 0.3)`;
}

function getStatusClass(status) {
    if (!status) return '';
    
    const statusLower = status.toLowerCase();
    
    // Map status to the appropriate CSS class
    if (statusLower.includes('masterato') || statusLower.includes('platinato')) {
        return 'status-masterato';
    } else if (statusLower.includes('archiviato')) {
        return 'status-archiviato';
    } else if (statusLower.includes('finito')) {
        return 'status-finito';
    } else if (statusLower.includes('online') && (statusLower.includes('senza') || statusLower.includes('fine'))) {
        return 'status-online-senza-fine';
    } else if (statusLower.includes('online')) {
        return 'status-online';
    } else if (statusLower.includes('droppato')) {
        return 'status-droppato';
    } else if (statusLower.includes('completato') || statusLower === '100%') {
        return 'status-completato-100';
    } else if (statusLower.includes('pausa') || statusLower === 'in pausa') {
        return 'status-in-pausa';
    } else if (statusLower.includes('corso') || statusLower.includes('in corso')) {
        return 'status-in-corso';
    } else if (statusLower.includes('recuperare')) {
        return 'status-da-recuperare';
    } else if (statusLower.includes('rigiocare')) {
        return 'status-da-rigiocare';
    }
    
    return '';
}

// Toggle platform filter dropdown
function togglePlatformFilter(section = 'played') {
    const dropdown = document.getElementById(`platform-filter-dropdown${section === 'played' ? '' : '-' + section}`);
    dropdown.classList.toggle('show');
    
    // Close other dropdowns
    document.querySelectorAll('.platform-dropdown').forEach(dd => {
        if (dd !== dropdown) {
            dd.classList.remove('show');
        }
    });
}

// Close platform dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.platform-filter')) {
        document.querySelectorAll('.platform-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
    if (!event.target.closest('.status-filter')) {
        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});

// Toggle status filter dropdown
function toggleStatusFilter(section = 'played') {
    const dropdown = document.getElementById(`status-filter-dropdown${section === 'played' ? '' : '-' + section}`);
    dropdown.classList.toggle('show');
    
    // Close other dropdowns
    document.querySelectorAll('.status-dropdown').forEach(dd => {
        if (dd !== dropdown) {
            dd.classList.remove('show');
        }
    });
}

// Filter games by selected platforms
function filterByPlatform(section) {
    const selectedPlatforms = Array.from(
        document.querySelectorAll(`#${section === 'played' ? 'platform-filter-dropdown' : 'platform-filter-dropdown-backlog'} input[type="checkbox"]:checked`)
    ).map(checkbox => checkbox.value);
    
    const selectedStatuses = Array.from(
        document.querySelectorAll(`#${section === 'played' ? 'status-filter-dropdown' : 'status-filter-dropdown-backlog'} input[type="checkbox"]:checked`)
    ).map(checkbox => checkbox.value);
    
    const games = document.querySelectorAll(`#${section}-games .game-card`);
    
    games.forEach(game => {
        const gamePlatform = game.getAttribute('data-platform') || '';
        const gameStatus = game.getAttribute('data-status') || '';
        
        const platformMatch = selectedPlatforms.length === 0 || 
                           selectedPlatforms.some(platform => gamePlatform.includes(platform));
                           
        const statusMatch = selectedStatuses.length === 0 || 
                          selectedStatuses.some(status => gameStatus === status);
        
        if (platformMatch && statusMatch) {
            game.style.display = 'block';
        } else {
            game.style.display = 'none';
        }
    });
    
    // Also apply search filter if there's a search term
    const searchTerm = document.getElementById(`search-${section}`).value.toLowerCase();
    if (searchTerm) {
        filterGames(section);
    }
}

// Filter games by selected statuses
function filterByStatus(section) {
    filterByPlatform(section);
}

function filterGames(section) {
    const searchTerm = document.getElementById(`search-${section}`).value.toLowerCase();
    const games = document.querySelectorAll(`#${section}-games .game-card`);
    
    games.forEach(game => {
        const title = game.querySelector('.game-title').textContent.toLowerCase();
        if (title.includes(searchTerm)) {
            game.style.display = 'block';
        } else {
            game.style.display = 'none';
        }
    });
}

function toggleSortOrder(section) {
    currentOrder[section] = currentOrder[section] === 'ASC' ? 'DESC' : 'ASC';
    const icon = document.getElementById(`sort-icon-${section}`);
    icon.className = currentOrder[section] === 'ASC' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    loadGames(section, false);
}

function openAddGameModal(section) {
    editingGameId = null;
    document.getElementById('modal-title').textContent = 'Aggiungi Gioco';
    document.getElementById('game-form').reset();
    document.getElementById('cover-preview').style.display = 'none';
    
    // Set section in a hidden field or remember it
    document.getElementById('game-form').dataset.section = section;
    
    // Update status dropdown based on section
    const statusSelect = document.getElementById('game-status');
    const currentStatus = statusSelect.value;
    
    // Clear existing options except the first one
    while (statusSelect.options.length > 1) {
        statusSelect.remove(1);
    }
    
    // Common status options
    const commonStatuses = [
        'Masterato/Platinato',
        'Completato (100%)',
        'Finito',
        'In Pausa',
        'In Corso',
        'Droppato',
        'Archiviato',
        'Online/Senza Fine'
    ];
    
    // Add backlog-specific statuses
    if (section === 'backlog') {
        commonStatuses.push('Da Recuperare', 'Da Rigiocare');
    }
    
    // Add all status options
    commonStatuses.forEach(status => {
        const option = document.createElement('option');
        option.value = status;
        option.textContent = status;
        statusSelect.appendChild(option);
    });
    
    // Restore previous selection if it still exists
    if (currentStatus && Array.from(statusSelect.options).some(opt => opt.value === currentStatus)) {
        statusSelect.value = currentStatus;
    } else {
        statusSelect.selectedIndex = 0;
    }
    
    document.getElementById('game-modal').classList.add('active');
}

async function openEditGameModal(gameId) {
    editingGameId = gameId;
    document.getElementById('modal-title').textContent = 'Modifica Gioco';
    
    try {
        const response = await fetch(`api/games.php?action=get&id=${gameId}`);
        const data = await response.json();
        
        if (data.success) {
            // Set the section before populating the form
            const section = data.game.section || 'played'; // Default to 'played' if section is not set
            document.getElementById('game-form').dataset.section = section;
            
            // Populate the form with game data
            populateGameForm(data.game);
            
            // Now update the status dropdown based on the section
            const statusSelect = document.getElementById('game-status');
            const currentStatus = statusSelect.value;
            
            // Clear existing options except the first one
            while (statusSelect.options.length > 1) {
                statusSelect.remove(1);
            }
            
            // Common status options
            const commonStatuses = [
                'Masterato/Platinato',
                'Completato (100%)',
                'Finito',
                'In Pausa',
                'In Corso',
                'Droppato',
                'Archiviato',
                'Online/Senza Fine'
            ];
            
            // Add backlog-specific statuses
            if (section === 'backlog') {
                commonStatuses.push('Da Recuperare', 'Da Rigiocare');
            }
            
            // Add all status options
            commonStatuses.forEach(status => {
                const option = document.createElement('option');
                option.value = status;
                option.textContent = status;
                statusSelect.appendChild(option);
            });
            
            // Set the current status if it exists in the new options
            if (currentStatus) {
                statusSelect.value = currentStatus;
            }
            
            document.getElementById('game-modal').classList.add('active');
        } else {
            alert('Errore nel caricamento del gioco');
        }
    } catch (error) {
        console.error('Error loading game:', error);
        alert('Errore di connessione');
    }
}

function populateGameForm(game) {
    document.getElementById('game-title').value = game.title || '';
    document.getElementById('game-playtime').value = game.playtime || '';
    document.getElementById('game-total-score').value = game.total_score || '';
    document.getElementById('game-aesthetic-score').value = game.aesthetic_score || '';
    document.getElementById('game-ost-score').value = game.ost_score || '';
    document.getElementById('game-difficulty').value = game.difficulty || '';
    document.getElementById('game-status').value = game.status || '';
    document.getElementById('game-trophy-percentage').value = game.trophy_percentage || '';
    document.getElementById('game-platinum-date').value = game.platinum_date || '';
    document.getElementById('game-replays').value = game.replays || '';
    document.getElementById('game-first-played').value = game.first_played || '';
    document.getElementById('game-last-finished').value = game.last_finished || '';
    document.getElementById('game-review').value = game.review || '';
    document.getElementById('game-cover').value = game.cover_url || '';
    
    // Set platforms
    const platforms = game.platform ? game.platform.split(', ') : [];
    document.querySelectorAll('input[name="platform[]"]').forEach(checkbox => {
        checkbox.checked = platforms.includes(checkbox.value);
    });
    
    // Update cover preview
    if (game.cover_url) {
        updateCoverPreview(game.cover_url);
    }
    
    // Remember the section
    document.getElementById('game-form').dataset.section = game.section;
}

function closeGameModal() {
    document.getElementById('game-modal').classList.remove('active');
    editingGameId = null;
}

async function handleGameSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const gameData = {};
    
    // Get all form fields
    for (let [key, value] of formData.entries()) {
        // Skip file inputs and empty values
        if (value instanceof File || value === '') continue;
        
        if (key === 'platform[]') {
            if (!gameData.platform) gameData.platform = [];
            gameData.platform.push(value);
        } else if (key.endsWith('[]')) {
            // Handle other array fields if any
            const fieldName = key.slice(0, -2);
            if (!gameData[fieldName]) gameData[fieldName] = [];
            gameData[fieldName].push(value);
        } else {
            gameData[key] = value;
        }
    }
    
    // Set section
    gameData.section = e.target.dataset.section || currentSection;
    
    // Convert empty strings to null for optional fields
    Object.keys(gameData).forEach(key => {
        if (gameData[key] === '') {
            gameData[key] = null;
        }
    });
    
    try {
        const url = 'api/games.php';
        const method = editingGameId ? 'PUT' : 'POST';
        const requestData = {
            action: editingGameId ? 'update' : 'add',
            game: gameData
        };

        if (editingGameId) {
            requestData.id = editingGameId;
        }

        console.log('Sending request to:', url);
        console.log('Method:', method);
        console.log('Data:', requestData);

        const response = await fetch(url, {
            method: method,
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server responded with error:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
        }

        let result;
        try {
            const responseText = await response.text();
            console.log('Response text:', responseText);
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parsing JSON response:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        if (result && result.success) {
            closeGameModal();
            loadGames(gameData.section);
            showNotification(result.message || 'Operazione completata con successo', 'success');
        } else {
            const errorMsg = result && result.error ? result.error : 
                           (result && result.message ? result.message : 'Errore sconosciuto');
            console.error('Server error:', errorMsg);
            showNotification(`Errore: ${errorMsg}`, 'error');
        }
    } catch (error) {
        console.error('Error in handleGameSubmit:', error);
        const errorMessage = error.message || 'Si è verificato un errore durante il salvataggio';
        showNotification(`Errore: ${errorMessage}`, 'error');
    }
}

async function searchGameCover() {
    const title = document.getElementById('game-title').value.trim();
    if (!title) {
        alert('Inserisci prima il titolo del gioco');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cercando...';
    button.disabled = true;
    
    try {
        const response = await fetch(`api/games.php?action=search_cover&title=${encodeURIComponent(title)}`);
        const data = await response.json();
        
        if (data.success && data.cover_url) {
            document.getElementById('game-cover').value = data.cover_url;
            updateCoverPreview(data.cover_url);
            showNotification('Cover trovata!', 'success');
        } else {
            showNotification('Nessuna cover trovata per questo titolo', 'warning');
        }
    } catch (error) {
        console.error('Error searching cover:', error);
        showNotification('Errore nella ricerca della cover', 'error');
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

function updateCoverPreview(url) {
    const preview = document.getElementById('cover-preview');
    if (url) {
        preview.src = url;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

async function moveGame(gameId, newSection) {
    try {
        const response = await fetch('api/games.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'move',
                id: gameId,
                section: newSection
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadGames(currentSection, false);
            showNotification(`Gioco spostato in ${newSection === 'played' ? 'Giocati' : 'Backlog'}`, 'success');
        } else {
            alert('Errore: ' + result.error);
        }
    } catch (error) {
        console.error('Error moving game:', error);
        alert('Errore di connessione');
    }
}

async function deleteGame(gameId) {
    if (!confirm('Sei sicuro di voler eliminare questo gioco?')) {
        return;
    }
    
    try {
        const response = await fetch('api/games.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: gameId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadGames(currentSection, false);
            showNotification('Gioco eliminato', 'success');
        } else {
            alert('Errore: ' + result.error);
        }
    } catch (error) {
        console.error('Error deleting game:', error);
        alert('Errore di connessione');
    }
}

async function searchCoverForGame(gameId, gameTitle, section) {
    try {
        // Show loading state on the cover
        const coverContainer = document.getElementById(`cover-${gameId}`);
        if (coverContainer) {
            coverContainer.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Cercando cover...</span>`;
            coverContainer.className = 'game-cover loading-cover';
        }
        
        // Call the search cover API
        const response = await fetch('api/games.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'search_cover',
                id: gameId,
                title: gameTitle
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.cover_url) {
            // Clear cache for this game to force reload
            const cache = JSON.parse(localStorage.getItem('gameImageCache') || '{}');
            Object.keys(cache).forEach(key => {
                if (key.startsWith(`${gameId}_`)) {
                    delete cache[key];
                }
            });
            localStorage.setItem('gameImageCache', JSON.stringify(cache));
            
            // Load the new image directly without throttling since it's a single image
            if (coverContainer) {
                const img = new Image();
                
                img.onload = function() {
                    coverContainer.innerHTML = `<img src="${result.cover_url}" alt="${gameTitle}" class="game-cover">`;
                    coverContainer.className = 'game-cover';
                    setCachedImageStatus(gameId, result.cover_url, 'success');
                };
                
                img.onerror = function() {
                    coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Immagine non presente</span>`;
                    coverContainer.className = 'game-cover no-cover';
                    setCachedImageStatus(gameId, result.cover_url, 'error');
                };
                
                // Set a timeout to avoid hanging
                setTimeout(() => {
                    if (!img.complete) {
                        img.src = '';
                        coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Timeout caricamento</span>`;
                        coverContainer.className = 'game-cover no-cover';
                        setCachedImageStatus(gameId, result.cover_url, 'timeout');
                    }
                }, 10000);
                
                img.src = result.cover_url;
            }
            
            showNotification('Cover trovata e aggiornata!', 'success');
        } else {
            // No cover found
            if (coverContainer) {
                coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Nessuna cover trovata</span>`;
                coverContainer.className = 'game-cover no-cover';
            }
            showNotification('Nessuna cover trovata per questo gioco', 'info');
        }
    } catch (error) {
        console.error('Error searching cover:', error);
        
        // Reset cover to no-cover state
        const coverContainer = document.getElementById(`cover-${gameId}`);
        if (coverContainer) {
            coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Errore ricerca</span>`;
            coverContainer.className = 'game-cover no-cover';
        }
        
        showNotification('Errore durante la ricerca della cover', 'error');
    }
}

async function handleTSVUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const statusDiv = document.getElementById('import-status');
    statusDiv.innerHTML = '<div class="loading"></div> Importazione in corso...';
    statusDiv.className = 'import-status';
    statusDiv.style.display = 'block';
    
    const formData = new FormData();
    formData.append('tsv_file', file);
    
    try {
        const response = await fetch('api/import.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            statusDiv.className = 'import-status success';
            statusDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <strong>${result.message}</strong>
                ${result.errors.length > 0 ? `<br><small>Errori: ${result.errors.join(', ')}</small>` : ''}
            `;
            
            // Refresh the games list
            loadGames('played', false);
        } else {
            statusDiv.className = 'import-status error';
            statusDiv.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <strong>Errore nell'importazione</strong>
                <br><small>${result.error}</small>
                ${result.errors ? `<br><small>${result.errors.join(', ')}</small>` : ''}
            `;
        }
    } catch (error) {
        console.error('Error importing TSV:', error);
        statusDiv.className = 'import-status error';
        statusDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <strong>Errore di connessione</strong>
        `;
    }
    
    // Clear the file input
    e.target.value = '';
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
        ${message}
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 15px 20px;
        border-radius: 8px;
        border-left: 4px solid var(--${type === 'success' ? 'success' : type === 'error' ? 'error' : 'accent'}-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Image loading throttling functions
function isValidUrl(string) {
    try {
        new URL(string);
        return string.startsWith('http://') || string.startsWith('https://');
    } catch (_) {
        return false;
    }
}

// Image cache functions
function getCachedImageStatus(gameId, imageUrl) {
    try {
        const cache = JSON.parse(localStorage.getItem('gameImageCache') || '{}');
        const cacheKey = `${gameId}_${btoa(imageUrl).substring(0, 20)}`; // Use base64 hash of URL
        return cache[cacheKey] || null;
    } catch (e) {
        return null;
    }
}

function setCachedImageStatus(gameId, imageUrl, status, actualUrl = null) {
    try {
        const cache = JSON.parse(localStorage.getItem('gameImageCache') || '{}');
        const cacheKey = `${gameId}_${btoa(imageUrl).substring(0, 20)}`;
        cache[cacheKey] = {
            status: status, // 'success', 'error', 'timeout'
            url: actualUrl || imageUrl,
            timestamp: Date.now()
        };
        
        // Clean old cache entries (older than 7 days)
        const weekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
        Object.keys(cache).forEach(key => {
            if (cache[key].timestamp < weekAgo) {
                delete cache[key];
            }
        });
        
        localStorage.setItem('gameImageCache', JSON.stringify(cache));
    } catch (e) {
        // localStorage might be full or disabled
        console.warn('Could not save image cache:', e);
    }
}

function queueImageLoad(gameId, imageUrl, title) {
    // Check cache first
    const cached = getCachedImageStatus(gameId, imageUrl);
    if (cached) {
        // Use cached result immediately
        const coverContainer = document.getElementById(`cover-${gameId}`);
        if (coverContainer) {
            if (cached.status === 'success') {
                coverContainer.innerHTML = `<img src="${cached.url}" alt="${title}" class="game-cover">`;
                coverContainer.className = 'game-cover';
            } else {
                coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Immagine non presente</span>`;
                coverContainer.className = 'game-cover no-cover';
            }
        }
        return; // Don't queue if we have cached result
    }
    
    // Add to queue only if not cached
    imageLoadQueue.push({ gameId, imageUrl, title });
    
    if (!isLoadingImages) {
        processImageQueue();
    }
}

async function processImageQueue() {
    if (imageLoadQueue.length === 0) {
        isLoadingImages = false;
        return;
    }
    
    isLoadingImages = true;
    
    // Process images in batches
    const batch = imageLoadQueue.splice(0, IMAGES_PER_BATCH);
    
    // Load images in current batch with small delays between each
    for (let i = 0; i < batch.length; i++) {
        const { gameId, imageUrl, title } = batch[i];
        
        // Small delay between individual images (200ms)
        setTimeout(() => {
            loadSingleImage(gameId, imageUrl, title);
        }, i * 200);
    }
    
    // Wait for batch delay before processing next batch
    if (imageLoadQueue.length > 0) {
        setTimeout(() => {
            processImageQueue();
        }, BATCH_DELAY);
    } else {
        isLoadingImages = false;
    }
}

function loadSingleImage(gameId, imageUrl, title) {
    const coverContainer = document.getElementById(`cover-${gameId}`);
    if (!coverContainer) return;
    
    const img = new Image();
    
    img.onload = function() {
        coverContainer.innerHTML = `<img src="${imageUrl}" alt="${title}" class="game-cover">`;
        coverContainer.className = 'game-cover';
        setCachedImageStatus(gameId, imageUrl, 'success');
    };
    
    img.onerror = function() {
        coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Immagine non presente</span>`;
        coverContainer.className = 'game-cover no-cover';
        setCachedImageStatus(gameId, imageUrl, 'error');
    };
    
    // Set a timeout to avoid hanging on slow images
    setTimeout(() => {
        if (!img.complete) {
            img.src = ''; // Cancel loading
            coverContainer.innerHTML = `<i class="fas fa-gamepad"></i><span>Timeout caricamento</span>`;
            coverContainer.className = 'game-cover no-cover';
            setCachedImageStatus(gameId, imageUrl, 'timeout');
        }
    }, 10000); // 10 second timeout
    
}

// TSV Import functionality is handled directly by handleTSVUpload

async function handleTSVFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Check file extension
    const fileName = file.name.toLowerCase();
    const fileExtension = fileName.split('.').pop();
    const validExtensions = ['tsv', 'txt'];
    
    if (!validExtensions.includes(fileExtension)) {
        showNotification('Errore: Seleziona un file TSV valido (.tsv o .txt)', 'error');
        // Reset the file input
        event.target.value = '';
        return;
    }
    
    // Show loading state
    showNotification('Importazione del file in corso...', 'info');
    
    try {
        await uploadTSVFile(file);
    } catch (error) {
        console.error('Error in TSV import:', error);
        showNotification('Errore durante l\'importazione del file', 'error');
    }
}

async function uploadTSVFile(file) {
    const importStatus = document.getElementById('import-status');
    
    try {
        const formData = new FormData();
        formData.append('tsv_file', file);
        
        importStatus.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Importazione in corso...</div>';
        
        const response = await fetch('api/import.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            importStatus.innerHTML = `
                <div class="success">
                    <i class="fas fa-check-circle"></i> 
                    ${data.message}
                    ${data.errors && data.errors.length > 0 ? 
                        `<div class="warnings">
                            <strong>Avvisi:</strong>
                            <ul>${data.errors.map(error => `<li>${error}</li>`).join('')}</ul>
                        </div>` : ''
                    }
                </div>
            `;
            
            // Reload games to show imported games
            loadGames(currentSection);
            
            // Clear file input
            document.getElementById('tsv-file').value = '';
            
            // Show success notification
            showNotification(`Importati ${data.imported_count} giochi con successo!`, 'success');
            
        } else {
            importStatus.innerHTML = `
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> 
                    ${data.error || 'Errore durante l\'importazione'}
                    ${data.errors && data.errors.length > 0 ? 
                        `<div class="error-details">
                            <strong>Dettagli errori:</strong>
                            <ul>${data.errors.map(error => `<li>${error}</li>`).join('')}</ul>
                        </div>` : ''
                    }
                </div>
            `;
            
            showNotification('Errore durante l\'importazione TSV', 'error');
        }
        
    } catch (error) {
        console.error('TSV Import error:', error);
        importStatus.innerHTML = `
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> 
                Errore di connessione durante l'importazione
            </div>
        `;
        showNotification('Errore di connessione durante l\'importazione', 'error');
    }
}

// Admin function to find and update missing covers
async function findMissingCovers() {
    if (!confirm('Vuoi cercare le cover mancanti? Questo processo potrebbe richiedere qualche minuto.')) {
        return;
    }
    
    const button = document.getElementById('find-missing-covers-btn');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ricerca in corso...';
    
    try {
        const response = await fetch('api/games.php?action=find_missing_covers');
        const result = await response.json();
        
        if (result.success) {
            const message = `Processo completato!\n` +
                          `- Giochi aggiornati: ${result.stats.updated}\n` +
                          `- Falliti: ${result.stats.failed}\n` +
                          `- Totale elaborati: ${result.stats.total}`;
            
            showNotification(message, 'success');
            
            // Reload the current section to show updated covers
            loadGames(currentSection);
        } else {
            throw new Error(result.error || 'Errore durante la ricerca delle cover');
        }
    } catch (error) {
        console.error('Error finding missing covers:', error);
        showNotification('Errore durante la ricerca delle cover: ' + error.message, 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// Authentication functions
function openLoginModal() {
    const modal = document.getElementById('login-modal');
    modal.classList.add('active');
    document.getElementById('login-username').focus();
}

function closeLoginModal() {
    const modal = document.getElementById('login-modal');
    modal.classList.remove('active');
    document.getElementById('login-form').reset();
}

async function login(username, password) {
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                username: username,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Login effettuato con successo!', 'success');
            closeLoginModal();
            updateUIForAuthStatus(data.user);
            // Reload games to update the UI with admin actions
            loadGames(currentSection);
            return true;
        } else {
            showNotification(data.error || 'Errore durante il login', 'error');
            return false;
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('Errore di connessione durante il login', 'error');
        return false;
    }
}

async function logout() {
    try {
        const response = await fetch('api/auth.php?action=logout');
        const data = await response.json();
        
        if (data.success) {
            showNotification('Logout effettuato con successo!', 'success');
            updateUIForAuthStatus({ is_admin: false });
            // Force reload games to update the UI without admin actions
            loadGames(currentSection);
        }
    } catch (error) {
        console.error('Logout error:', error);
        showNotification('Errore durante il logout', 'error');
    }
}

// Parse playtime string and calculate total hours
function parsePlaytime(playtimeStr) {
    if (!playtimeStr) return 0;
    
    let totalHours = 0;
    
    // Handle formats like "412 PS + 7,9 PC + 2"
    const parts = playtimeStr.split('+').map(part => part.trim());
    
    for (const part of parts) {
        // Extract numeric value (handles both . and , as decimal separator)
        const match = part.match(/^([\d,.]*\d+)/);
        if (match) {
            // Replace comma with dot for proper float parsing
            const value = parseFloat(match[1].replace(',', '.'));
            if (!isNaN(value)) {
                totalHours += value;
            }
        }
    }
    
    return parseFloat(totalHours.toFixed(1));
}

// Helper function to convert time components to total minutes
function timeToMinutes(years = 0, months = 0, days = 0, hours = 0, minutes = 0) {
    // Approximate conversions (since months and years aren't fixed)
    const daysFromYears = years * 365;
    const daysFromMonths = months * 30; // Approximation
    const totalDays = daysFromYears + daysFromMonths + days;
    return (totalDays * 24 * 60) + (hours * 60) + minutes;
}

// Parse completion time string (e.g., "2 ORE, 30 MINUTI" or "1 GIORNO, 3 ORE")
function parseCompletionTime(completionStr) {
    if (!completionStr || completionStr.trim() === '') return null;
    
    console.log('Parsing completion time:', completionStr);
    
    // Special handling for Crash Bandicoot N.Sane Trilogy format
    if (completionStr.includes('(CB2)')) {
        console.log('=== DEBUG: Processing Crash Bandicoot N.Sane Trilogy ===');
        console.log('Original string:', completionStr);
        
        // Extract just the CB2 time (second entry)
        // First get the text between (CB1) and (CB3)
        const cb2Section = completionStr.match(/\(CB1\)(.*?)\(CB3\)/is);
        let cb2Match = null;
        
        if (cb2Section && cb2Section[1]) {
            // Now extract days and hours from the CB2 section
            cb2Match = cb2Section[1].match(/(\d+)\s*GIORNI?[,\s]*(\d+)?\s*ORE?/i);
        }
        console.log('CB2 match result:', cb2Match);
        
        if (cb2Match) {
            const days = parseInt(cb2Match[1]) || 0;
            const hours = cb2Match[2] ? parseInt(cb2Match[2]) : 0;
            const totalMinutes = timeToMinutes(0, 0, days, hours, 0);
            const result = {
                days: days,
                hours: hours,
                totalMinutes: totalMinutes,
                display: `${days} ${days === 1 ? 'GIORNO' : 'GIORNI'}${hours ? `, ${hours} ORE` : ''} (CB2)`,
                original: completionStr
            };
            console.log('Successfully processed CB2 time:', result);
            return result;
        } else {
            console.log('WARNING: Could not parse CB2 time from:', completionStr);
            
            // Fallback: Try to extract any time format for CB2
            const fallbackMatch = completionStr.match(/\(CB2\)\s*([^)]+)/i);
            if (fallbackMatch) {
                console.log('Attempting fallback parsing for:', fallbackMatch[1]);
                const fallbackParsed = parseCompletionTime(fallbackMatch[1].trim());
                if (fallbackParsed) {
                    console.log('Fallback parsing successful:', fallbackParsed);
                    return {
                        ...fallbackParsed,
                        display: `${fallbackParsed.display} (CB2)`
                    };
                }
            }
        }
        console.log('=== END DEBUG ===');
    }
    
    // Normalize the string - remove any non-essential text
    const normalized = completionStr
        .toUpperCase()
        .replace(/^PLATINATO\/MASTERATO IN:/i, '') // Remove label if present
        .replace(/\([^)]+\)/g, '') // Remove any parenthetical notes like (PS4)
        .replace(/\s+/g, ' ') // Normalize spaces
        .trim();
    
    console.log('Normalized time string:', normalized);
    
    // Try to match "X ORE, YY MINUTI" format
    const timeMatch = normalized.match(/(\d+)\s*ORE\D*(\d+)?\s*MINUTI?/);
    if (timeMatch) {
        const hours = parseInt(timeMatch[1]) || 0;
        const minutes = timeMatch[2] ? parseInt(timeMatch[2]) : 0;
        const totalMinutes = timeToMinutes(0, 0, 0, hours, minutes);
        const result = {
            hours: hours,
            minutes: minutes,
            totalMinutes: totalMinutes,
            display: `${hours} ORE${minutes ? `, ${minutes} MINUTI` : ''}`,
            original: completionStr
        };
        console.log('Matched time format:', result);
        return result;
    }
    
    // Try to match "X GIORNO, YY MINUTI" format (e.g., "1 giorno, 37 minuti")
    const giornoMinutiMatch = normalized.match(/(\d+)\s*GIORNO\D*(\d+)?\s*MINUTI?/);
    if (giornoMinutiMatch) {
        const days = parseInt(giornoMinutiMatch[1]) || 0;
        const minutes = giornoMinutiMatch[2] ? parseInt(giornoMinutiMatch[2]) : 0;
        const totalMinutes = timeToMinutes(0, 0, days, 0, minutes);
        const result = {
            days: days,
            minutes: minutes,
            totalMinutes: totalMinutes,
            display: `${days} ${days === 1 ? 'GIORNO' : 'GIORNI'}${minutes ? `, ${minutes} MINUTI` : ''}`,
            original: completionStr
        };
        console.log('Matched giorno/minuti format:', result);
        return result;
    }
    
    // Try to match "X GIORNI, YY ORE" format
    const dayMatch = normalized.match(/(\d+)\s*GIORNI?\D*(\d+)?\s*ORE?/);
    if (dayMatch) {
        const days = parseInt(dayMatch[1]) || 0;
        const hours = dayMatch[2] ? parseInt(dayMatch[2]) : 0;
        const totalMinutes = timeToMinutes(0, 0, days, hours, 0);
        const result = {
            days: days,
            hours: hours,
            totalMinutes: totalMinutes,
            display: `${days} ${days === 1 ? 'GIORNO' : 'GIORNI'}${hours ? `, ${hours} ORE` : ''}`,
            original: completionStr
        };
        console.log('Matched days format:', result);
        return result;
    }
    
    // Try to match "X ANNI, Y MESI, Z GIORNI" format
    const anniMesiGiorniMatch = normalized.match(/(\d+)\s*ANNI?\D*(\d+)?\s*MESI?\D*(\d+)?\s*GIORNI?/i);
    if (anniMesiGiorniMatch) {
        const years = parseInt(anniMesiGiorniMatch[1]) || 0;
        const months = anniMesiGiorniMatch[2] ? parseInt(anniMesiGiorniMatch[2]) : 0;
        const days = anniMesiGiorniMatch[3] ? parseInt(anniMesiGiorniMatch[3]) : 0;
        const totalMinutes = timeToMinutes(years, months, days, 0, 0);
        const result = {
            years: years,
            months: months,
            days: days,
            totalMinutes: totalMinutes,
            display: [
                years > 0 ? `${years} ${years === 1 ? 'ANNO' : 'ANNI'}` : '',
                months > 0 ? `${months} ${months === 1 ? 'MESE' : 'MESI'}` : '',
                days > 0 ? `${days} ${days === 1 ? 'GIORNO' : 'GIORNI'}` : ''
            ].filter(Boolean).join(', '),
            original: completionStr
        };
        console.log('Matched anni/mesi/giorni format:', result);
        return result;
    }
    
    // Try to match "X MESI, Y GIORNI" format
    const mesiGiorniMatch = normalized.match(/(\d+)\s*MESI?\D*(\d+)?\s*GIORNI?/i);
    if (mesiGiorniMatch) {
        const months = parseInt(mesiGiorniMatch[1]) || 0;
        const days = mesiGiorniMatch[2] ? parseInt(mesiGiorniMatch[2]) : 0;
        const totalMinutes = timeToMinutes(0, months, days, 0, 0);
        const result = {
            months: months,
            days: days,
            totalMinutes: totalMinutes,
            display: [
                months > 0 ? `${months} ${months === 1 ? 'MESE' : 'MESI'}` : '',
                days > 0 ? `${days} ${days === 1 ? 'GIORNO' : 'GIORNI'}` : ''
            ].filter(Boolean).join(', '),
            original: completionStr
        };
        console.log('Matched mesi/giorni format:', result);
        return result;
    }
    
    // Try to match "X ANNI, Y MESI" format
    const anniMesiMatch = normalized.match(/(\d+)\s*ANNI?\D*(\d+)?\s*MESI?/i);
    if (anniMesiMatch) {
        const years = parseInt(anniMesiMatch[1]) || 0;
        const months = anniMesiMatch[2] ? parseInt(anniMesiMatch[2]) : 0;
        const totalMinutes = timeToMinutes(years, months, 0, 0, 0);
        const result = {
            years: years,
            months: months,
            totalMinutes: totalMinutes,
            display: [
                years > 0 ? `${years} ${years === 1 ? 'ANNO' : 'ANNI'}` : '',
                months > 0 ? `${months} ${months === 1 ? 'MESE' : 'MESI'}` : ''
            ].filter(Boolean).join(', '),
            original: completionStr
        };
        console.log('Matched anni/mesi format:', result);
        return result;
    }
    
    // Try to match simple days format (e.g., "163 giorni")
    const giorniMatch = normalized.match(/(\d+)\s*GIORNI?/i);
    if (giorniMatch) {
        const days = parseInt(giorniMatch[1]) || 0;
        const totalMinutes = timeToMinutes(0, 0, days, 0, 0);
        const result = {
            days: days,
            totalMinutes: totalMinutes,
            display: `${days} ${days === 1 ? 'GIORNO' : 'GIORNI'}`,
            original: completionStr
        };
        console.log('Matched giorni format:', result);
        return result;
    }
    
    console.log('No valid time format found for:', completionStr);
    return null; // No valid format found
}

// Render fastest completions table
function renderFastestCompletionsTable(games) {
    const tbody = document.getElementById('fastestCompletionsBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    console.log('=== DEBUG: Starting renderFastestCompletionsTable ===');
    console.log('Total games to process:', games.length);
    
    // Log all games with their status and platinum_date for debugging
    console.log('All games with status and platinum_date:');
    games.forEach(game => {
        // Log all games for debugging
        console.log(`Game: ${game.title}, Status: ${game.status}, Platinum Date: ${game.platinum_date}`);
        
        // Special debug for crash games
        if (game.title && game.title.toLowerCase().includes('crash')) {
            console.log('CRASH GAME FOUND:', {
                title: game.title,
                status: game.status,
                platinum_date: game.platinum_date,
                hasPlatinum: game.status && (game.status.includes('Platinato') || game.status.includes('Masterato'))
            });
        }
    });

    // Filter games with valid completion times and parse them
    const gamesWithCompletion = games
        .filter(game => {
            const hasPlatinumDate = !!game.platinum_date;
            const hasValidStatus = game.status && (game.status.includes('Platinato') || game.status.includes('Masterato') || game.status === 'Masterato/Platinato');
            
            if (game.title && game.title.toLowerCase().includes('crash')) {
                console.log('=== CRASH GAME FILTERING ===');
                console.log('Title:', game.title);
                console.log('Status:', game.status);
                console.log('Platinum Date:', game.platinum_date);
                console.log('Has Platinum Date:', hasPlatinumDate);
                console.log('Has Valid Status:', hasValidStatus);
                console.log('============================');
            }
            
            return hasPlatinumDate && hasValidStatus;
        })
        .map(game => {
            const completionText = game.platinum_date || '';
            
            if (game.title && game.title.toLowerCase().includes('crash')) {
                console.log('=== PARSING CRASH GAME ===');
                console.log('Title:', game.title);
                console.log('Platinum Date:', completionText);
            }
            
            const parsed = parseCompletionTime(completionText);
            
            if (game.title && game.title.toLowerCase().includes('crash')) {
                console.log('Parsed Result:', parsed);
                console.log('=========================');
            }
            
            return {
                ...game,
                parsedCompletion: parsed,
                sortKey: parsed ? parsed.totalMinutes : Infinity
            };
        })
        .filter(game => {
            const hasCompletion = !!game.parsedCompletion;

            if (game.title && game.title.toLowerCase().includes('crash')) {
                console.log('=== FINAL FILTER ===');
                console.log('Title:', game.title);
                console.log('Has Completion:', hasCompletion);
                if (!hasCompletion) {
                    console.log('Reason for exclusion: Could not parse completion time from:', game.platinum_date);
                }
                console.log('====================');
            }

            return hasCompletion;
        })
        .sort((a, b) => a.sortKey - b.sortKey) // Sort by total minutes (ascending)
        .slice(0, 15); // Get top 15 fastest
    
    console.log('Filtered and sorted games with completion times:', gamesWithCompletion);
    
    // Populate the table
    gamesWithCompletion.forEach((game, index) => {
        const tr = document.createElement('tr');
        const statusClass = getStatusClass(game.status);
                // Add indicators to specific game titles
            let displayTitle = escapeHtml(game.title);
            if (game.title.includes('Spyro Reignited Trilogy')) {
                displayTitle += ' (SP1)';
            } else if (game.title.includes('Crash Bandicoot N.Sane Trilogy')) {
                displayTitle += ' (CB2)';
            }
            
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${displayTitle}</td>
                <td>${game.parsedCompletion.display}</td>
                <td>${escapeHtml(game.platform || 'N/A')}</td>
            `;
        
        tbody.appendChild(tr);
    });
}

// Status color mapping
const statusColors = {
    'Masterato/Platinato': '#8DB3E2',
    'Completato (100%)': '#FFC000',
    'Finito': '#92D050',
    'IN PAUSA': '#afaffa',
    'In Corso': '#D4EDBC',
    'Droppato': '#FF7C80',
    'Archiviato': '#FFF1B3',
    'Online/Senza Fine': '#D9D9D9',
    'Da Rigiocare': '#7EF1FF',
    'DA RECUPERARE': '#E6E6E6'
};

// Chart default configuration
Chart.defaults.color = '#ffffff';
Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
Chart.defaults.font.family = '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
Chart.defaults.plugins.legend.labels.boxWidth = 16;
Chart.defaults.plugins.legend.labels.padding = 12;
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.pointStyle = 'circle';

// Impostazioni assi per i grafici a barre
Chart.defaults.scale.grid.color = 'rgba(255, 255, 255, 0.1)';
Chart.defaults.scale.ticks.color = '#ffffff';
Chart.defaults.scale.title.color = '#ffffff';

// Chart instances
let statusChart = null;
let platformChart = null;
let difficultyChart = null;
async function loadStatistics() {
    try {
        console.log('Loading statistics...');
        const loadingElement = document.getElementById('loading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        
        // First load the played games to get completion times
        const gamesResponse = await fetch('api/games.php?action=list&section=played');
        if (!gamesResponse.ok) {
            throw new Error(`HTTP error! status: ${gamesResponse.status}`);
        }
        const gamesData = await gamesResponse.json();
        
        // Debug: Log the first game to see its structure
        if (gamesData.games && gamesData.games.length > 0) {
            console.log('First played game structure:', gamesData.games[0]);
            // Log all available fields in the game object
            console.log('Available fields in game object:', Object.keys(gamesData.games[0]));
        }
        
        // Then load the statistics
        const response = await fetch('api/statistics.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Received statistics data:', data);
        
        if (data.success && gamesData.success) {
            // Add the played games to the data object for the completion time table
            data.playedGames = gamesData.games;
            
            // Render all charts and tables
            if (data.data.status) renderStatusChart(data.data.status);
            if (data.data.platform) renderPlatformChart(data.data.platform);
            if (data.data.difficulty) renderDifficultyChart(data.data.difficulty);
            if (data.data.topDifficultGames) renderDifficultGamesTable(data.data.topDifficultGames);
            if (data.data.topPlaytimeGames) renderPlaytimeTable(data.data.topPlaytimeGames);
            if (data.data.playedByYear) renderPlayedByYearChart(data.data.playedByYear);
            if (data.data.voteDistribution) {
                renderVoteDistributionChart(data.data.voteDistribution);
                renderVoteDistributionTable(data.data.voteDistribution);
            } else {
                console.warn('No voteDistribution data in response');
            }
            // Also render the data tables
            if (data.data) {
                renderStatusTable(data.data.status);
                renderPlatformTable(data.data.platform);
                renderDifficultyTable(data.data.difficulty);
                
                // Render fastest completions table if we have played games
                if (data.playedGames) {
                    renderFastestCompletionsTable(data.playedGames);
                }
            }
        } else {
            console.error('Failed to load statistics:', data.error);
            showNotification('Errore nel caricamento delle statistiche', 'error');
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
        showNotification('Errore di connessione', 'error');
    }
}

// Render status distribution chart
function renderStatusChart(statusData) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (statusChart) {
        statusChart.destroy();
    }
    
    // Debug log to see the raw status data
    console.log('Raw status data:', statusData);
    
    const labels = statusData.map(item => item.status || 'Nessuno');
    const data = statusData.map(item => item.count);
    
    // Map each status to its color, using the status value as it appears in the data
    const backgroundColors = statusData.map(item => {
        // Try to find the exact status in statusColors, case-insensitive
        const statusKey = Object.keys(statusColors).find(
            key => key.toLowerCase() === (item.status || '').toLowerCase()
        );
        
        // Debug log for each status
        console.log(`Status: "${item.status}", Found key: "${statusKey}", Color: ${statusKey ? statusColors[statusKey] : 'default'}`);
        
        return statusKey ? statusColors[statusKey] : '#666666';
    });
    
    // Debug log the final mapping
    console.log('Final color mapping:', labels.map((label, i) => `${label}: ${backgroundColors[i]}`));
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderColor: 'rgba(255, 255, 255, 0.5)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(22, 24, 28, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#e0e0e0',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%',
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
}

// Render platform distribution chart
function renderPlatformChart(platformData) {
    const ctx = document.getElementById('platformChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (platformChart) {
        platformChart.destroy();
    }
    
    // Define the exact order of platforms we want to display
    const platformOrder = [
        'DIGITALE', 'FISICO', 'PS1', 'PS2', 'PS3', 'PS4', 'PS5', 
        'PC', 'SWITCH', '3DS', 'GBA', 'WII'
    ];
    
    // Create a map of platform counts for easy lookup
    const platformMap = {};
    platformData.forEach(item => {
        platformMap[item.platform] = item.count;
    });
    
    // Create labels and data in the specified order, including all platforms
    const labels = [];
    const data = [];
    const backgroundColors = [];
    const baseColor = '32, 205, 50'; // RGB of var(--accent-primary)
    
    platformOrder.forEach((platform, index) => {
        labels.push(platform);
        data.push(platformMap[platform] || 0);
        
        // Apply different opacity based on position for visual distinction
        const opacity = 0.8 - (index * 0.05);
        backgroundColors.push(`rgba(${baseColor}, ${opacity > 0.3 ? opacity : 0.3})`);
    });
    
    platformChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Numero di giochi',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: 'rgba(50, 205, 50, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#ffffff',
                        stepSize: 1
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: function(context) {
                            // Make DIGITALE and FISICO bold
                            const label = context.tick && context.tick.label;
                            if (label === 'DIGITALE' || label === 'FISICO') {
                                return '#FFFFFF';
                            }
                            return '#E0E0E0';
                        },
                        font: function(context) {
                            // Make DIGITALE and FISICO bold
                            const label = context.tick && context.tick.label;
                            if (label === 'DIGITALE' || label === 'FISICO') {
                                return { weight: 'bold' };
                            }
                            return { weight: 'normal' };
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(22, 24, 28, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#e0e0e0',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Giochi: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

// Render difficulty distribution chart
function renderDifficultyChart(difficultyData) {
    const ctx = document.getElementById('difficultyChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (difficultyChart) {
        difficultyChart.destroy();
    }
    
    // Sort difficulties numerically
    const sortedDifficulties = [...difficultyData].sort((a, b) => a.difficulty - b.difficulty);
    
    const labels = sortedDifficulties.map(item => item.difficulty || '0');
    const data = sortedDifficulties.map(item => item.count);
    
    // Generate colors based on difficulty (red to green gradient)
    const difficultyColors = labels.map(difficulty => {
        const value = parseInt(difficulty) / 10; // Normalize to 0-1
        const r = Math.round(255 * (1 - value));
        const g = Math.round(255 * value);
        return `rgba(${r}, ${g}, 0, 0.7)`;
    });
    
    difficultyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: difficultyColors,
                borderColor: difficultyColors.map(c => c.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'x',
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#ffffff'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#ffffff',
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(22, 24, 28, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#e0e0e0',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false
                }
            }
        }
    });
}

// Render playtime table
function renderPlaytimeTable(games) {
    const tbody = document.getElementById('playtimeTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    games.forEach((game, index) => {
        const tr = document.createElement('tr');
        const statusClass = game.status ? game.status.toLowerCase().replace(/[\s/]+/g, '-') : '';
        const totalHours = game.total_playtime || parsePlaytime(game.playtime);
        
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${escapeHtml(game.title || 'N/A')}</td>
            <td>${totalHours} ore</td>
            <td>${escapeHtml(game.platform || 'N/A')}</td>
            <td class="status-${statusClass}">${escapeHtml(game.status || 'N/A')}</td>
        `;
        
        tbody.appendChild(tr);
    });
}

// Render difficult games table
function renderDifficultGamesTable(games) {
    const tbody = document.getElementById('difficultGamesBody');
    tbody.innerHTML = '';
    
    games.forEach((game, index) => {
        const tr = document.createElement('tr');
        const statusClass = game.status ? game.status.toLowerCase().replace(/[\s/]+/g, '-') : '';
        
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${escapeHtml(game.title || 'N/A')}</td>
            <td>${game.difficulty || '0'}/10</td>
            <td>${escapeHtml(game.platform || 'N/A')}</td>
            <td class="status-${statusClass}">${escapeHtml(game.status || 'N/A')}</td>
        `;
        
        tbody.appendChild(tr);
    });
}

// Render status distribution table
function renderStatusTable(statusData) {
    const container = document.getElementById('statusTable');
    let html = '<table><tr><th>Stato</th><th>Conteggio</th><th>Percentuale</th></tr>';
    
    const total = statusData.reduce((sum, item) => sum + parseInt(item.count), 0);
    
    statusData.forEach(item => {
        const status = item.status || 'Nessuno';
        const count = parseInt(item.count);
        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
        const statusClass = status.toLowerCase().replace(/[\s/]+/g, '-');
        
        html += `
            <tr>
                <td class="status-${statusClass}">${escapeHtml(status)}</td>
                <td>${count}</td>
                <td>${percentage}%</td>
            </tr>
        `;
    });
    
    html += `
        <tr class="total-row">
            <td><strong>Totale</strong></td>
            <td><strong>${total}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
    </table>`;
    
    container.innerHTML = html;
}

// Render platform distribution table
function renderPlatformTable(platformData) {
    const container = document.getElementById('platformTable');
    let html = '<table><tr><th>Piattaforma</th><th>Conteggio</th><th>Percentuale</th></tr>';
    
    const total = platformData.reduce((sum, item) => sum + parseInt(item.count), 0);
    
    platformData.forEach(item => {
        const platform = item.platform || 'Nessuna';
        const count = parseInt(item.count);
        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
        
        html += `
            <tr>
                <td>${escapeHtml(platform)}</td>
                <td>${count}</td>
                <td>${percentage}%</td>
            </tr>
        `;
    });
    
    html += `
        <tr class="total-row">
            <td><strong>Totale</strong></td>
            <td><strong>${total}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
    </table>`;
    
    container.innerHTML = html;
}

// Render vote distribution table
function renderVoteDistributionTable(voteData) {
    const container = document.getElementById('voteDistributionTable');
    if (!container) {
        console.error('Vote distribution table container not found');
        return;
    }

    let html = '<table><tr><th>Range Voti</th><th>Conteggio</th><th>Percentuale</th></tr>';
    
    const total = voteData.reduce((sum, item) => sum + parseInt(item.count), 0);
    
    voteData.forEach(item => {
        const range = item.range || 'N/D';
        const count = parseInt(item.count);
        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
        
        html += `
            <tr>
                <td>${escapeHtml(range)}</td>
                <td>${count}</td>
                <td>${percentage}%</td>
            </tr>
        `;
    });
    
    html += `
        <tr class="total-row">
            <td><strong>Totale</strong></td>
            <td><strong>${total}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
    </table>`;
    
    container.innerHTML = html;
}

// Render difficulty distribution table
function renderDifficultyTable(difficultyData) {
    const container = document.getElementById('difficultyTable');
    let html = '<table><tr><th>Difficoltà</th><th>Conteggio</th><th>Percentuale</th></tr>';
    
    const total = difficultyData.reduce((sum, item) => sum + parseInt(item.count), 0);
    
    // Sort difficulties numerically
    const sortedDifficulties = [...difficultyData].sort((a, b) => a.difficulty - b.difficulty);
    
    sortedDifficulties.forEach(item => {
        const difficulty = item.difficulty || '0';
        const count = parseInt(item.count);
        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
        
        html += `
            <tr>
                <td>${difficulty}/10</td>
                <td>${count}</td>
                <td>${percentage}%</td>
            </tr>
        `;
    });
    
    html += `
        <tr class="total-row">
            <td><strong>Totale</strong></td>
            <td><strong>${total}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
    </table>`;
    
    container.innerHTML = html;
}

// Render vote distribution chart
function renderVoteDistributionChart(voteData) {
    console.log('Rendering vote distribution chart with data:', voteData);
    
    const canvas = document.getElementById('voteDistributionChart');
    if (!canvas) {
        console.error('Canvas element for voteDistributionChart not found');
        return;
    }
    
    // Check if we have valid data
    if (!Array.isArray(voteData) || voteData.length === 0) {
        console.warn('No valid data provided for vote distribution chart');
        // Hide the chart container if no data
        const container = canvas.closest('.stat-card');
        if (container) {
            container.style.display = 'none';
        }
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.voteDistributionChart && typeof window.voteDistributionChart.destroy === 'function') {
        console.log('Destroying existing voteDistributionChart');
        window.voteDistributionChart.destroy();
    }
    
    // Prepare data for the chart
    const labels = voteData.map(item => item.range);
    const counts = voteData.map(item => item.count);
    
    // Create gradient for the bars
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(78, 115, 223, 0.8)');
    gradient.addColorStop(1, 'rgba(28, 200, 138, 0.2)');
    
    // Create the chart
    window.voteDistributionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Numero di giochi',
                data: counts,
                backgroundColor: gradient,
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.8,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Giochi: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#fff',
                        maxRotation: 0,
                        autoSkip: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#fff',
                        precision: 0
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Render played by year chart
function renderPlayedByYearChart(playedByYearData) {
    console.log('Rendering played by year chart with data:', playedByYearData);
    
    const canvas = document.getElementById('playedByYearChart');
    if (!canvas) {
        console.error('Canvas element for playedByYearChart not found');
        return;
    }
    
    // Check if we have valid data
    if (!playedByYearData || typeof playedByYearData !== 'object') {
        console.warn('No valid data provided for played by year chart');
        // Hide the chart container if no data
        const container = canvas.closest('.chart-container');
        if (container) {
            container.style.display = 'none';
        }
        return;
    }
    
    // Convert the tabular format to an array of objects
    const years = [];
    const counts = [];
    
    // Sort years in ascending order
    const sortedYears = Object.keys(playedByYearData).sort((a, b) => parseInt(a) - parseInt(b));
    
    sortedYears.forEach(year => {
        years.push(year);
        counts.push(playedByYearData[year]);
    });
    
    console.log('Processed years data:', { years, counts });
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.playedByYearChart && typeof window.playedByYearChart.destroy === 'function') {
        console.log('Destroying existing playedByYearChart');
        window.playedByYearChart.destroy();
    }
    
    // We already processed the data at the beginning of the function
    // Use the years and counts we already prepared
    
    // Create gradient for the line
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(50, 205, 50, 0.6)');
    gradient.addColorStop(1, 'rgba(50, 205, 50, 0.1)');
    
    window.playedByYearChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: years,
            datasets: [{
                label: 'Giochi giocati',
                data: counts,
                borderColor: '#32cd32',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#32cd32',
                pointBorderColor: '#ffffff',
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#32cd32',
                pointHoverBorderColor: '#ffffff',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'nearest',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#e0e0e0',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Giochi: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        },
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Function to update game priority
async function updateGamePriority(gameId, priority) {
    try {
        const response = await fetch('api/games.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_priority',
                id: gameId,
                priority: priority
            })
        });
        
        const data = await response.json();
        if (data.success) {
            // Reload backlog to update the order
            loadGames('backlog');
            showNotification('Priorità aggiornata con successo!', 'success');
        } else {
            throw new Error('Errore durante l\'aggiornamento della priorità');
        }
    } catch (error) {
        console.error('Error updating priority:', error);
        showNotification('Errore durante l\'aggiornamento della priorità', 'error');
    }
}

// Setup login form event listener
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = document.getElementById('login-username')?.value;
            const password = document.getElementById('login-password')?.value;
            if (username && password) {
                await login(username, password);
            }
        });
    }
});

// Add CSS for notifications and review tooltips
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    /* Review tooltip styles */
    .game-card[data-review] {
        position: relative;
    }
    
    .game-card[data-review]:hover::after {
        content: attr(data-review);
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.95);
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.4;
        max-width: 300px;
        width: max-content;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        white-space: pre-wrap;
        word-wrap: break-word;
        animation: fadeInTooltip 0.2s ease;
        margin-top: 8px;
    }
    
    .game-card[data-review]:hover::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 8px solid rgba(0, 0, 0, 0.95);
        z-index: 1001;
        margin-top: 0px;
    }
    
    @keyframes fadeInTooltip {
        from { opacity: 0; transform: translateX(-50%) translateY(-5px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
    
    /* Ensure tooltip doesn't get cut off */
    .game-card[data-review]:hover {
        z-index: 10;
    }
`;
document.head.appendChild(notificationStyles);
