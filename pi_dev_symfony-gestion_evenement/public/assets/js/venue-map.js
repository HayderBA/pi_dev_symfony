(function () {
    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function mapUrl(address) {
        const query = encodeURIComponent(address && String(address).trim() ? address : 'Tunisie');
        return `https://www.google.com/maps?q=${query}&output=embed`;
    }

    function matchesQuery(venue, query) {
        const target = normalize([venue.label, venue.category, venue.city, venue.address].join(' '));
        return target.includes(normalize(query));
    }

    function renderOptions(select, venues, query, currentSelection) {
        const filtered = venues.filter((venue) => !query || matchesQuery(venue, query));
        select.innerHTML = '<option value="">Choisir un lieu recommande</option>';

        filtered.forEach((venue) => {
            const option = document.createElement('option');
            option.value = venue.address;
            option.textContent = `${venue.label} • ${venue.city}`;
            select.appendChild(option);
        });

        if (currentSelection && Array.from(select.options).some((option) => option.value === currentSelection)) {
            select.value = currentSelection;
        }
    }

    function updateStatus(element, message, muted) {
        if (!element) {
            return;
        }

        element.textContent = message;
        element.classList.toggle('is-muted', Boolean(muted));
    }

    function findVenue(venues, address) {
        return venues.find((venue) => normalize(venue.address) === normalize(address));
    }

    function setFrameLocation(frame, address) {
        if (!frame) {
            return;
        }

        frame.src = mapUrl(address);
    }

    function initPicker(config) {
        const venues = Array.isArray(config.venues) ? config.venues : [];
        const frame = document.getElementById(config.frameId);
        const select = document.getElementById(config.selectId);
        const search = document.getElementById(config.searchId);
        const input = document.getElementById(config.inputId);
        const status = document.getElementById(config.statusId);
        const resetButton = document.getElementById(config.resetId);
        let currentSelection = input && input.value ? input.value : (config.selectedAddress || '');

        if (!frame || !select || !search || !input) {
            return;
        }

        renderOptions(select, venues, '', currentSelection);
        setFrameLocation(frame, currentSelection || 'Tunisie');

        if (currentSelection) {
            const initialVenue = findVenue(venues, currentSelection);
            if (initialVenue) {
                updateStatus(status, `${initialVenue.label} affiche sur la carte.`, false);
            } else {
                updateStatus(status, 'Adresse actuelle affichee sur la carte.', false);
            }
        } else {
            updateStatus(status, 'Choisissez un lieu pour afficher la carte reelle.', true);
        }

        search.addEventListener('input', function () {
            renderOptions(select, venues, this.value, currentSelection);
        });

        select.addEventListener('change', function () {
            const selectedVenue = venues.find((venue) => venue.address === this.value);
            if (!selectedVenue) {
                return;
            }

            currentSelection = selectedVenue.address;
            input.value = selectedVenue.address;
            setFrameLocation(frame, selectedVenue.address);
            updateStatus(status, `${selectedVenue.label} affiche sur la carte.`, false);
        });

        input.addEventListener('change', function () {
            currentSelection = this.value.trim();
            if (!currentSelection) {
                setFrameLocation(frame, 'Tunisie');
                updateStatus(status, 'Choisissez un lieu pour afficher la carte reelle.', true);
                return;
            }

            setFrameLocation(frame, currentSelection);
            const selectedVenue = findVenue(venues, currentSelection);
            updateStatus(
                status,
                selectedVenue ? `${selectedVenue.label} affiche sur la carte.` : 'Adresse choisie affichee sur la carte.',
                false
            );
        });

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                currentSelection = '';
                search.value = '';
                select.value = '';
                input.value = '';
                renderOptions(select, venues, '', '');
                setFrameLocation(frame, 'Tunisie');
                updateStatus(status, 'Selection reinitialisee.', false);
            });
        }
    }

    function initShowcase(config) {
        const venues = Array.isArray(config.venues) ? config.venues : [];
        const frame = document.getElementById(config.frameId);
        const status = document.getElementById(config.statusId);
        const title = document.getElementById(config.titleId);
        const directions = document.getElementById(config.directionsId);
        const currentAddress = String(config.currentAddress || '').trim();

        if (!frame) {
            return;
        }

        setFrameLocation(frame, currentAddress || 'Tunisie');

        const activeVenue = findVenue(venues, currentAddress);

        if (title && activeVenue) {
            title.textContent = activeVenue.label;
        }

        if (directions && currentAddress) {
            directions.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(currentAddress)}`;
        }

        if (currentAddress) {
            updateStatus(status, activeVenue ? `${activeVenue.label} est affiche sur la carte.` : 'Le lieu de cet evenement est affiche sur la carte.', false);
        } else {
            updateStatus(status, 'Aucune localisation n est definie pour cet evenement.', true);
        }
    }

    window.GrowMindVenueMaps = {
        initPicker,
        initShowcase,
    };
})();
