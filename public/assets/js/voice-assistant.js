(function () {
    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function setStatus(root, message, listening) {
        if (!root) {
            return;
        }

        const status = root.querySelector('[data-voice-status]');
        const button = root.querySelector('[data-voice-toggle]');
        if (status) {
            status.textContent = message;
        }
        if (button) {
            button.classList.toggle('is-listening', Boolean(listening));
            button.setAttribute('aria-pressed', listening ? 'true' : 'false');
        }
    }

    function parseOrdinal(transcript) {
        const text = normalize(transcript);
        if (text.includes('premier') || text.includes('premiere') || text.includes('1er') || text.includes('1ere') || text.includes(' 1 ') || text === '1' || text.includes('un')) return 0;
        if (text.includes('deuxieme') || text.includes(' 2 ') || text === '2' || text.includes('deux')) return 1;
        if (text.includes('troisieme') || text.includes(' 3 ') || text === '3' || text.includes('trois')) return 2;
        if (text.includes('quatrieme') || text.includes(' 4 ') || text === '4' || text.includes('quatre')) return 3;
        if (text.includes('cinquieme') || text.includes(' 5 ') || text === '5' || text.includes('cinq')) return 4;
        return null;
    }

    function includesAny(text, patterns) {
        return patterns.some((pattern) => text.includes(pattern));
    }

    function extractAfterKeywords(text, keywords) {
        const normalized = normalize(text);
        for (const keyword of keywords) {
            const index = normalized.indexOf(keyword);
            if (index !== -1) {
                return normalized.slice(index + keyword.length).trim();
            }
        }
        return '';
    }

    function triggerInput(element) {
        if (!element) {
            return;
        }

        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
        element.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true, key: 'Enter' }));
    }

    function extractSearchQuery(text) {
        const normalized = normalize(text);
        const prefixes = ['recherche ', 'chercher ', 'cherche ', 'rechercher '];
        for (const prefix of prefixes) {
            if (normalized.startsWith(prefix)) {
                return normalized.slice(prefix.length).trim();
            }
        }

        const quoted = normalized.match(/(?:cherche|chercher|recherche|rechercher)\s+["“]?(.+?)["”]?$/);
        if (quoted && quoted[1]) {
            return quoted[1].trim();
        }

        return '';
    }

    function createRecognition() {
        const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!Recognition) {
            return null;
        }

        const recognition = new Recognition();
        recognition.lang = 'fr-FR';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        return recognition;
    }

    function setupRecognitionLifecycle(root, recognition, runCommand, helpMessage, options) {
        const settings = Object.assign({ persistent: false }, options || {});
        const toggle = root.querySelector('[data-voice-toggle]');
        let listening = false;
        let armed = false;
        let restartTimer = null;
        let lastTranscript = '';
        let lastTranscriptAt = 0;

        recognition.continuous = Boolean(settings.persistent);

        function clearRestart() {
            if (restartTimer) {
                clearTimeout(restartTimer);
                restartTimer = null;
            }
        }

        function stopListening(message) {
            armed = false;
            clearRestart();
            if (listening) {
                try {
                    recognition.stop();
                } catch (error) {
                    // ignore browser stop race conditions
                }
            }
            listening = false;
            setStatus(root, message || 'Ecoute arretee.', false);
        }

        function startListening() {
            armed = true;
            clearRestart();
            try {
                recognition.start();
            } catch (error) {
                setStatus(root, 'Micro deja actif ou indisponible. Reessayez.', false);
            }
        }

        recognition.onstart = function () {
            listening = true;
            setStatus(root, helpMessage, true);
        };

        recognition.onresult = function (event) {
            const transcript = event.results[event.results.length - 1][0].transcript || '';
            const normalized = normalize(transcript);
            const now = Date.now();

            if (normalized && normalized === lastTranscript && now - lastTranscriptAt < 1200) {
                return;
            }

            lastTranscript = normalized;
            lastTranscriptAt = now;

            runCommand(transcript, {
                keepListening: settings.persistent,
                stopListening,
            });

            if (settings.persistent && armed) {
                setStatus(root, `Commande executee: ${transcript}. Je continue d ecouter...`, true);
            } else if (!settings.persistent) {
                listening = false;
            }
        };

        recognition.onerror = function (event) {
            listening = false;

            if (settings.persistent && armed && event.error !== 'not-allowed' && event.error !== 'service-not-allowed') {
                setStatus(root, 'Micro relance automatiquement...', true);
                clearRestart();
                restartTimer = window.setTimeout(() => {
                    if (armed) {
                        startListening();
                    }
                }, 700);
                return;
            }

            setStatus(root, 'Impossible d ecouter. Reessayez.', false);
        };

        recognition.onend = function () {
            listening = false;

            if (settings.persistent && armed) {
                clearRestart();
                restartTimer = window.setTimeout(() => {
                    if (armed) {
                        startListening();
                    }
                }, 450);
                return;
            }

            setStatus(root, armed ? 'Ecoute terminee.' : 'Ecoute arretee.', false);
        };

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (armed) {
                    stopListening('Ecoute arretee.');
                    return;
                }

                startListening();
            });
        }
    }

    function initListVoice(root) {
        const recognition = createRecognition();
        if (!recognition) {
            setStatus(root, 'Commande vocale non supportee sur ce navigateur.', false);
            return;
        }

        const form = document.getElementById('eventSearchForm');
        const input = form ? form.querySelector('input[name="search"]') : null;
        const eventLinks = Array.from(document.querySelectorAll('.event-btn'));
        const favorites = document.getElementById('gmFavoritesGrid');

        function runCommand(transcript) {
            const text = normalize(transcript);
            const searchQuery = extractSearchQuery(text);
            const calendar = document.getElementById('eventCalendar');

            if (searchQuery && input && form) {
                input.value = searchQuery;
                setStatus(root, `Recherche lancee: ${searchQuery}`, false);
                form.requestSubmit();
                return;
            }

            if (includesAny(text, ['efface recherche', 'supprime recherche', 'retire filtre', 'reinitialise'])) {
                window.location.href = window.location.pathname;
                return;
            }

            if (includesAny(text, ['ouvre calendrier', 'voir calendrier', 'descend calendrier'])) {
                if (calendar) {
                    calendar.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setStatus(root, 'Calendrier affiche.', false);
                }
                return;
            }

            if (text.includes('ouvre favori') || text.includes('mes favoris')) {
                if (favorites) {
                    favorites.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setStatus(root, 'Section favoris ouverte.', false);
                }
                return;
            }

            if (includesAny(text, ['ouvre recents', 'ouvre recent', 'historiques', 'recemment'])) {
                const recent = document.getElementById('gmRecentGrid');
                if (recent) {
                    recent.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setStatus(root, 'Section recemment consultes ouverte.', false);
                }
                return;
            }

            if (text.includes('ouvre') || text.includes('ouvrir')) {
                const index = parseOrdinal(text);
                if (index !== null && eventLinks[index]) {
                    setStatus(root, `Ouverture de l evenement ${index + 1}.`, false);
                    window.location.href = eventLinks[index].href;
                    return;
                }
            }

            if (includesAny(text, ['dernier evenement', 'ouvre dernier'])) {
                const lastLink = eventLinks[eventLinks.length - 1];
                if (lastLink) {
                    setStatus(root, 'Ouverture du dernier evenement.', false);
                    window.location.href = lastLink.href;
                }
                return;
            }

            setStatus(root, `Commande non comprise: ${transcript}`, false);
        }

        setupRecognitionLifecycle(
            root,
            recognition,
            function (transcript) { runCommand(transcript); },
            'J ecoute... Essayez: recherche yoga, ouvre premier evenement.',
            { persistent: false }
        );
    }

    function initDetailVoice(root) {
        const recognition = createRecognition();
        if (!recognition) {
            setStatus(root, 'Commande vocale non supportee sur ce navigateur.', false);
            return;
        }

        const reservationForm = document.getElementById('reservationForm');
        const copyButton = document.getElementById('gmCopyEventLink');
        const shareButton = document.getElementById('gmShareEvent');
        const notifyButton = document.getElementById('gmNotifyEvent');
        const calendarLink = document.querySelector('.gm-smart-actions a[href*="calendar.google.com"]');
        const backLink = document.querySelector('.event-back');

        function runCommand(transcript) {
            const text = normalize(transcript);
            const mapPanel = document.getElementById('gmEventShowFrame');
            const smartPanel = document.getElementById('gmCountdown');

            if (text.includes('reserver') || text.includes('reservation')) {
                if (reservationForm) {
                    reservationForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setStatus(root, 'Formulaire de reservation affiche.', false);
                }
                return;
            }

            if (includesAny(text, ['carte', 'map', 'localisation', 'lieu'])) {
                if (mapPanel) {
                    mapPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setStatus(root, 'Carte du lieu affichee.', false);
                }
                return;
            }

            if (includesAny(text, ['compte a rebours', 'countdown', 'temps restant'])) {
                if (smartPanel) {
                    smartPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setStatus(root, 'Compte a rebours affiche.', false);
                }
                return;
            }

            if (text.includes('copie') || text.includes('copier lien')) {
                if (copyButton) {
                    copyButton.click();
                    setStatus(root, 'Lien copie.', false);
                }
                return;
            }

            if (text.includes('partage') || text.includes('partager')) {
                if (shareButton && shareButton.style.display !== 'none') {
                    shareButton.click();
                    setStatus(root, 'Partage lance.', false);
                }
                return;
            }

            if (text.includes('rappel') || text.includes('notification')) {
                if (notifyButton) {
                    notifyButton.click();
                    setStatus(root, 'Rappel demande.', false);
                }
                return;
            }

            if (includesAny(text, ['telecharger ics', 'telecharger agenda', 'fichier ics'])) {
                const icsLink = document.querySelector('.gm-smart-actions a[href$=".ics"], .gm-smart-actions a[href*="/ical"]');
                if (icsLink) {
                    window.location.href = icsLink.href;
                    setStatus(root, 'Fichier agenda telecharge.', false);
                }
                return;
            }

            if (text.includes('google calendar') || text.includes('agenda')) {
                if (calendarLink) {
                    window.open(calendarLink.href, '_blank', 'noopener');
                    setStatus(root, 'Google Calendar ouvert.', false);
                }
                return;
            }

            if (includesAny(text, ['favori', 'ajoute favori', 'sauvegarde'])) {
                const favoriteButton = document.querySelector('[data-favorite-toggle]');
                if (favoriteButton) {
                    favoriteButton.click();
                    setStatus(root, 'Favori mis a jour.', false);
                }
                return;
            }

            if (includesAny(text, ['telephone', 'numero'])) {
                const telField = document.getElementById('telephone');
                if (telField) {
                    telField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    telField.focus();
                    setStatus(root, 'Champ telephone pret.', false);
                }
                return;
            }

            if (includesAny(text, ['email'])) {
                const emailField = document.getElementById('email');
                if (emailField) {
                    emailField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    emailField.focus();
                    setStatus(root, 'Champ email pret.', false);
                }
                return;
            }

            if (text.includes('retour')) {
                if (backLink) {
                    window.location.href = backLink.href;
                    setStatus(root, 'Retour a la liste.', false);
                }
                return;
            }

            setStatus(root, `Commande non comprise: ${transcript}`, false);
        }

        setupRecognitionLifecycle(
            root,
            recognition,
            function (transcript) { runCommand(transcript); },
            'J ecoute... Essayez: reserver, copier le lien, rappel, agenda.',
            { persistent: false }
        );
    }

    function initForumAdminVoice(root) {
        const recognition = createRecognition();
        if (!recognition) {
            setStatus(root, 'Commande vocale non supportee sur ce navigateur.', false);
            return;
        }

        const newLink = document.getElementById('forumVoiceNew');
        const frontLink = document.getElementById('forumVoiceFront');
        const searchInput = document.getElementById('forumSearchInput');
        const stateFilter = document.getElementById('forumStateFilter');

        function getVisibleRows() {
            return Array.from(document.querySelectorAll('.forum-row')).filter((row) => row.style.display !== 'none');
        }

        function getRowAction(rows, selector, index) {
            const row = rows[index];
            return row ? row.querySelector(selector) : null;
        }

        function runCommand(transcript, controls) {
            const text = normalize(transcript);
            const searchQuery = extractSearchQuery(text);
            const api = controls || {};

            if (includesAny(text, ['stop', 'arrete', 'arreter', 'pause micro', 'coupe micro', 'desactive micro'])) {
                if (api.stopListening) {
                    api.stopListening('Micro forum desactive.');
                }
                return;
            }

            if (includesAny(text, ['nouvelle discussion', 'creer discussion', 'ajouter discussion'])) {
                if (newLink) {
                    setStatus(root, 'Ouverture du formulaire de nouvelle discussion.', false);
                    window.location.href = newLink.href;
                }
                return;
            }

            if (includesAny(text, ['voir front', 'ouvrir front', 'ouvrir forum public', 'voir forum public'])) {
                if (frontLink) {
                    setStatus(root, 'Ouverture du forum public.', false);
                    window.location.href = frontLink.href;
                }
                return;
            }

            if (searchQuery && searchInput) {
                searchInput.value = searchQuery;
                triggerInput(searchInput);
                setStatus(root, `Recherche forum lancee: ${searchQuery}`, false);
                return;
            }

            if (includesAny(text, ['voir archivees', 'discussion archivee', 'filtre archive'])) {
                if (stateFilter) {
                    stateFilter.value = 'archive';
                    triggerInput(stateFilter);
                    setStatus(root, 'Filtre des discussions archivees applique.', false);
                }
                return;
            }

            if (includesAny(text, ['voir visibles', 'discussion visible', 'filtre visible'])) {
                if (stateFilter) {
                    stateFilter.value = 'visible';
                    triggerInput(stateFilter);
                    setStatus(root, 'Filtre des discussions visibles applique.', false);
                }
                return;
            }

            if (includesAny(text, ['tout afficher', 'reinitialise forum', 'efface filtre forum'])) {
                if (searchInput) {
                    searchInput.value = '';
                    triggerInput(searchInput);
                }
                if (stateFilter) {
                    stateFilter.value = 'all';
                    triggerInput(stateFilter);
                }
                setStatus(root, 'Filtres forum reinitialises.', false);
                return;
            }

            if (
                (includesAny(text, ['ouvrir', 'ouvre', 'voir']) && text.includes('discussion'))
                || includesAny(text, ['ouvrir discussion', 'voir discussion', 'ouvre discussion'])
            ) {
                const index = parseOrdinal(text);
                const showLink = index !== null ? getRowAction(getVisibleRows(), '[data-forum-show]', index) : null;
                if (showLink) {
                    setStatus(root, `Ouverture de la discussion ${index + 1}.`, false);
                    window.location.href = showLink.href;
                    return;
                }
            }

            if (
                (includesAny(text, ['modifier', 'editer', 'modifie']) && text.includes('discussion'))
                || includesAny(text, ['modifier discussion', 'editer discussion', 'modifie discussion'])
            ) {
                const index = parseOrdinal(text);
                const editLink = index !== null ? getRowAction(getVisibleRows(), '[data-forum-edit]', index) : null;
                if (editLink) {
                    setStatus(root, `Modification de la discussion ${index + 1}.`, false);
                    window.location.href = editLink.href;
                    return;
                }
            }

            if (
                (includesAny(text, ['archiver', 'archive']) && text.includes('discussion'))
                || includesAny(text, ['archiver discussion', 'archive discussion', 'changer statut'])
            ) {
                const index = parseOrdinal(text);
                const archiveButton = index !== null ? getRowAction(getVisibleRows(), '[data-forum-archive]', index) : null;
                if (archiveButton) {
                    setStatus(root, `Archivage de la discussion ${index + 1}.`, false);
                    archiveButton.click();
                    return;
                }
            }

            setStatus(root, `Commande non comprise: ${transcript}`, false);
        }

        setupRecognitionLifecycle(
            root,
            recognition,
            runCommand,
            'J ecoute en continu... Essayez: chercher stress, voir archivees, ouvrir premiere discussion, stop.',
            { persistent: true }
        );
    }

    function initEventAdminVoice(root) {
        const recognition = createRecognition();
        if (!recognition) {
            setStatus(root, 'Commande vocale non supportee sur ce navigateur.', false);
            return;
        }

        const newLink = document.getElementById('eventVoiceNew');
        const statsLink = document.getElementById('eventVoiceStats');
        const exportButton = document.getElementById('exportPdfBtn');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const sortFilter = document.getElementById('sortFilter');
        const frontLink = document.querySelector('.admin-footer-links a[href]');
        const showLinks = Array.from(document.querySelectorAll('[data-event-show]'));
        const editLinks = Array.from(document.querySelectorAll('[data-event-edit]'));
        const deleteButtons = Array.from(document.querySelectorAll('[data-event-delete]'));

        function applySort(value, label) {
            if (!sortFilter) {
                return false;
            }

            sortFilter.value = value;
            triggerInput(sortFilter);
            setStatus(root, label, false);
            return true;
        }

        function resetFilters() {
            if (searchInput) {
                searchInput.value = '';
                triggerInput(searchInput);
            }
            if (statusFilter) {
                statusFilter.value = 'all';
                triggerInput(statusFilter);
            }
            if (sortFilter) {
                sortFilter.value = 'date_asc';
                triggerInput(sortFilter);
            }
        }

        function runCommand(transcript, controls) {
            const text = normalize(transcript);
            const searchQuery = extractSearchQuery(text);
            const sortQuery = extractAfterKeywords(text, ['trier ', 'tri ', 'classer ', 'classe ']);
            const api = controls || {};

            if (includesAny(text, ['stop', 'arrete', 'arreter', 'pause micro', 'coupe micro', 'desactive micro'])) {
                if (api.stopListening) {
                    api.stopListening('Micro desactive.');
                }
                return;
            }

            if (includesAny(text, ['nouvel evenement', 'creer evenement', 'ajouter evenement'])) {
                if (newLink) {
                    setStatus(root, 'Ouverture du formulaire de nouvel evenement.', false);
                    window.location.href = newLink.href;
                }
                return;
            }

            if (includesAny(text, ['voir statistiques', 'ouvrir statistiques', 'statistiques'])) {
                if (statsLink) {
                    setStatus(root, 'Ouverture des statistiques.', false);
                    window.location.href = statsLink.href;
                }
                return;
            }

            if (includesAny(text, ['voir front', 'ouvrir front', 'retour au site', 'ouvrir site', 'voir le front'])) {
                if (frontLink) {
                    setStatus(root, 'Ouverture du front evenement.', false);
                    window.location.href = frontLink.href;
                }
                return;
            }

            if (includesAny(text, ['exporter pdf', 'export pdf', 'imprimer liste', 'fais pdf', 'faire pdf', 'extraire pdf', 'telecharger pdf'])) {
                if (exportButton) {
                    exportButton.click();
                    setStatus(root, 'Export PDF lance.', false);
                }
                return;
            }

            if (searchQuery && searchInput) {
                searchInput.value = searchQuery;
                triggerInput(searchInput);
                setStatus(root, `Recherche evenement lancee: ${searchQuery}`, false);
                return;
            }

            if (includesAny(text, ['reset', 'reinitialise', 'reinitialiser', 'efface recherche', 'vide recherche', 'annule filtre'])) {
                resetFilters();
                setStatus(root, 'Recherche et filtres reinitialises.', false);
                return;
            }

            if (includesAny(text, ['voir a venir', 'filtre a venir', 'evenements a venir'])) {
                if (statusFilter) {
                    statusFilter.value = 'À venir';
                    triggerInput(statusFilter);
                    setStatus(root, 'Filtre des evenements a venir applique.', false);
                }
                return;
            }

            if (includesAny(text, ['voir aujourd hui', 'filtre aujourd hui', 'evenements aujourd hui'])) {
                if (statusFilter) {
                    statusFilter.value = "Aujourd'hui";
                    triggerInput(statusFilter);
                    setStatus(root, 'Filtre des evenements du jour applique.', false);
                }
                return;
            }

            if (includesAny(text, ['voir passes', 'voir passe', 'evenements passes', 'filtre passe'])) {
                if (statusFilter) {
                    statusFilter.value = 'Passé';
                    triggerInput(statusFilter);
                    setStatus(root, 'Filtre des evenements passes applique.', false);
                }
                return;
            }

            if (includesAny(text, ['tout afficher', 'reinitialise evenement', 'efface filtre evenement'])) {
                resetFilters();
                setStatus(root, 'Filtres evenements reinitialises.', false);
                return;
            }

            if (
                includesAny(text, ['trier par popularite', 'tri popularite', 'plus populaire'])
                || sortQuery.includes('popularite')
                || sortQuery.includes('reservation')
            ) {
                applySort('reservations', 'Tri par popularite applique.');
                return;
            }

            if (
                includesAny(text, ['trier par titre', 'tri titre', 'titre a z'])
                || sortQuery.includes('titre')
            ) {
                if (includesAny(text, ['z a', 'descendant', 'inverse'])) {
                    applySort('titre_desc', 'Tri titre Z-A applique.');
                } else {
                    applySort('titre_asc', 'Tri titre A-Z applique.');
                }
                return;
            }

            if (
                includesAny(text, ['trier par date', 'tri date', 'plus recent', 'plus recents'])
                || sortQuery.includes('date')
            ) {
                if (includesAny(text, ['plus ancien', 'plus anciens', 'ancien', 'ancienne', 'ascendant'])) {
                    applySort('date_desc', 'Tri date plus ancien applique.');
                } else {
                    applySort('date_asc', 'Tri date plus recent applique.');
                }
                return;
            }

            if (includesAny(text, ['ouvrir evenement', 'voir evenement', 'ouvre evenement'])) {
                const index = parseOrdinal(text);
                if (index !== null && showLinks[index]) {
                    setStatus(root, `Ouverture de l evenement ${index + 1}.`, false);
                    window.location.href = showLinks[index].href;
                    return;
                }
            }

            if (includesAny(text, ['modifier evenement', 'editer evenement', 'modifie evenement'])) {
                const index = parseOrdinal(text);
                if (index !== null && editLinks[index]) {
                    setStatus(root, `Modification de l evenement ${index + 1}.`, false);
                    window.location.href = editLinks[index].href;
                    return;
                }
            }

            if (includesAny(text, ['supprimer evenement', 'effacer evenement', 'retirer evenement'])) {
                const index = parseOrdinal(text);
                if (index !== null && deleteButtons[index]) {
                    setStatus(root, `Suppression de l evenement ${index + 1}.`, false);
                    deleteButtons[index].click();
                    return;
                }
            }

            setStatus(root, `Commande non comprise: ${transcript}`, false);
        }

        setupRecognitionLifecycle(
            root,
            recognition,
            runCommand,
            'J ecoute en continu... Essayez: cherche yoga, reset, fais pdf, voir front, trier par popularite.',
            { persistent: true }
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const listVoice = document.querySelector('[data-voice-list]');
        if (listVoice) {
            initListVoice(listVoice);
        }

        const detailVoice = document.querySelector('[data-voice-detail]');
        if (detailVoice) {
            initDetailVoice(detailVoice);
        }

        const forumAdminVoice = document.querySelector('[data-voice-forum-admin]');
        if (forumAdminVoice) {
            initForumAdminVoice(forumAdminVoice);
        }

        const eventAdminVoice = document.querySelector('[data-voice-event-admin]');
        if (eventAdminVoice) {
            initEventAdminVoice(eventAdminVoice);
        }
    });
})();
