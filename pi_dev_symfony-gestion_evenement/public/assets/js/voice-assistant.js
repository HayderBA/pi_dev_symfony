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
        if (text.includes('premier') || text.includes('1') || text.includes('un')) return 0;
        if (text.includes('deuxieme') || text.includes('2') || text.includes('deux')) return 1;
        if (text.includes('troisieme') || text.includes('3') || text.includes('trois')) return 2;
        if (text.includes('quatrieme') || text.includes('4') || text.includes('quatre')) return 3;
        return null;
    }

    function includesAny(text, patterns) {
        return patterns.some((pattern) => text.includes(pattern));
    }

    function extractSearchQuery(text) {
        const normalized = normalize(text);
        const prefixes = ['recherche ', 'chercher ', 'cherche ', 'rechercher '];
        for (const prefix of prefixes) {
            if (normalized.startsWith(prefix)) {
                return normalized.slice(prefix.length).trim();
            }
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
        let listening = false;

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

        recognition.onstart = function () {
            listening = true;
            setStatus(root, 'J ecoute... Essayez: recherche yoga, ouvre premier evenement.', true);
        };

        recognition.onresult = function (event) {
            const transcript = event.results[0][0].transcript || '';
            listening = false;
            runCommand(transcript);
        };

        recognition.onerror = function () {
            listening = false;
            setStatus(root, 'Impossible d ecouter. Reessayez.', false);
        };

        recognition.onend = function () {
            if (listening) {
                listening = false;
                setStatus(root, 'Ecoute terminee.', false);
            }
        };

        root.querySelector('[data-voice-toggle]').addEventListener('click', function () {
            if (listening) {
                recognition.stop();
                listening = false;
                setStatus(root, 'Ecoute arretee.', false);
                return;
            }
            recognition.start();
        });
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
        let listening = false;

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
                const favoriteButton = detail.querySelector('[data-favorite-toggle]');
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

        recognition.onstart = function () {
            listening = true;
            setStatus(root, 'J ecoute... Essayez: reserver, copier le lien, rappel, agenda.', true);
        };

        recognition.onresult = function (event) {
            const transcript = event.results[0][0].transcript || '';
            listening = false;
            runCommand(transcript);
        };

        recognition.onerror = function () {
            listening = false;
            setStatus(root, 'Impossible d ecouter. Reessayez.', false);
        };

        recognition.onend = function () {
            if (listening) {
                listening = false;
                setStatus(root, 'Ecoute terminee.', false);
            }
        };

        root.querySelector('[data-voice-toggle]').addEventListener('click', function () {
            if (listening) {
                recognition.stop();
                listening = false;
                setStatus(root, 'Ecoute arretee.', false);
                return;
            }
            recognition.start();
        });
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
    });
})();
