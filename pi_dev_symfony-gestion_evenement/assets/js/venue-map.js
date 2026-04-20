(function () {
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalize(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function createRedIcon(active) {
        return L.divIcon({
            className: '',
            html: `<span class="gm-map-pin${active ? ' is-active' : ''}"></span>`,
            iconSize: [22, 22],
            iconAnchor: [11, 22],
            popupAnchor: [0, -18],
        });
    }

    function createMap(mapId, center) {
        const map = L.map(mapId, {
            zoomControl: false,
            scrollWheelZoom: true,
        }).setView(center, 11);

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        return map;
    }

    function venuePopup(venue) {
        return `
            <div class="gm-venue-popup">
                <strong>${escapeHtml(venue.label)}</strong>
                <span>${escapeHtml(venue.category)} • ${escapeHtml(venue.city)}</span>
                <p>${escapeHtml(venue.address)}</p>
            </div>
        `;
    }

    async function resolveVenues(venues, onProgress) {
        const resolved = [];

        for (let index = 0; index < venues.length; index += 1) {
            const venue = venues[index];
            if (typeof venue.lat === 'number' && typeof venue.lng === 'number') {
                resolved.push(venue);
                if (typeof onProgress === 'function') {
                    onProgress(index + 1, venues.length, venue);
                }
            } else if (typeof onProgress === 'function') {
                onProgress(index + 1, venues.length, venue, new Error('Coordonnees manquantes'));
            }
        }

        return resolved;
    }

    async function initPicker(config) {
        const venues = Array.isArray(config.venues) ? config.venues : [];
        const select = document.getElementById(config.selectId);
        const search = document.getElementById(config.searchId);
        const input = document.getElementById(config.inputId);
        const status = document.getElementById(config.statusId);
        const resetButton = document.getElementById(config.resetId);
        const map = createMap(config.mapId, [36.8065, 10.1815]);
        const markers = [];
        let currentCircle = null;
        let currentSelection = input.value || config.selectedAddress || '';

        function updateStatus(message, muted) {
            if (!status) {
                return;
            }
            status.textContent = message;
            status.classList.toggle('is-muted', Boolean(muted));
        }

        function matchesQuery(venue, query) {
            const target = normalize([venue.label, venue.category, venue.city, venue.address].join(' '));
            return target.includes(normalize(query));
        }

        function renderOptions(query) {
            const previousValue = select.value;
            const filtered = venues.filter((venue) => !query || matchesQuery(venue, query));

            select.innerHTML = '<option value="">Choisir un lieu recommande</option>';
            filtered.forEach((venue) => {
                const option = document.createElement('option');
                option.value = venue.address;
                option.textContent = `${venue.label} • ${venue.city}`;
                select.appendChild(option);
            });

            if (Array.from(select.options).some((option) => option.value === previousValue)) {
                select.value = previousValue;
            } else if (Array.from(select.options).some((option) => option.value === currentSelection)) {
                select.value = currentSelection;
            }
        }

        function highlightSelection(lat, lng) {
            if (currentCircle) {
                map.removeLayer(currentCircle);
            }

            currentCircle = L.circleMarker([lat, lng], {
                radius: 18,
                color: '#1e5ba8',
                weight: 2,
                fillColor: '#6bbf59',
                fillOpacity: 0.2,
            }).addTo(map);
        }

        function selectVenue(venue, source) {
            currentSelection = venue.address;
            input.value = venue.address;
            select.value = venue.address;
            highlightSelection(venue.lat, venue.lng);
            map.flyTo([venue.lat, venue.lng], 15, { duration: 0.8 });
            updateStatus(`${venue.label} selectionne${source === 'marker' ? ' depuis la carte' : ''}.`, false);
            markers.forEach((entry) => {
                entry.marker.setIcon(createRedIcon(entry.venue.address === venue.address));
            });
        }

        renderOptions('');
        updateStatus('Chargement de la carte et des 10 adresses...', true);

        const resolvedVenues = await resolveVenues(venues, function (done, total, venue, error) {
            if (error) {
                updateStatus(`Adresse ignoree: ${venue.label}.`, true);
                return;
            }
            updateStatus(`${done}/${total} lieux charges...`, true);
        });

        if (resolvedVenues.length === 0) {
            updateStatus('Impossible de charger les adresses pour le moment.', false);
            return;
        }

        const bounds = [];
        resolvedVenues.forEach((venue) => {
            const marker = L.marker([venue.lat, venue.lng], {
                icon: createRedIcon(false),
            }).addTo(map);
            marker.bindPopup(venuePopup(venue));
            marker.on('click', () => selectVenue(venue, 'marker'));
            markers.push({ venue, marker });
            bounds.push([venue.lat, venue.lng]);
        });

        map.fitBounds(bounds, { padding: [28, 28] });

        if (currentSelection) {
            const matchingVenue = resolvedVenues.find((venue) => normalize(venue.address) === normalize(currentSelection));
            if (matchingVenue) {
                selectVenue(matchingVenue, 'initial');
            } else {
                updateStatus('Selectionnez un lieu recommande parmi les 10 adresses pour afficher la carte.', false);
            }
        } else {
            updateStatus('Choisissez un lieu dans la liste ou cliquez directement sur un marqueur.', false);
        }

        select.addEventListener('change', function () {
            const selected = resolvedVenues.find((venue) => venue.address === this.value);
            if (selected) {
                selectVenue(selected, 'select');
                const selectedMarker = markers.find((entry) => entry.venue.address === selected.address);
                if (selectedMarker) {
                    selectedMarker.marker.openPopup();
                }
            }
        });

        search.addEventListener('input', function () {
            renderOptions(this.value);
        });

        input.addEventListener('blur', function () {
            const exactMatch = resolvedVenues.find((venue) => normalize(venue.address) === normalize(this.value));
            if (exactMatch) {
                selectVenue(exactMatch, 'input');
                return;
            }

            if (!this.value.trim()) {
                updateStatus('Choisissez un lieu dans la liste ou cliquez directement sur un marqueur.', false);
                return;
            }

            updateStatus('Cette vue affiche uniquement les 10 adresses recommandees. Choisissez-en une dans la liste.', false);
        });

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                search.value = '';
                renderOptions('');
                select.value = '';
                input.value = '';
                currentSelection = '';
                if (currentCircle) {
                    map.removeLayer(currentCircle);
                    currentCircle = null;
                }
                markers.forEach((entry) => {
                    entry.marker.setIcon(createRedIcon(false));
                });
                map.fitBounds(bounds, { padding: [28, 28] });
                updateStatus('Selection reinitialisee.', false);
            });
        }

        setTimeout(() => map.invalidateSize(), 120);
        setTimeout(() => map.invalidateSize(), 500);
    }

    async function initShowcase(config) {
        const venues = Array.isArray(config.venues) ? config.venues : [];
        const status = document.getElementById(config.statusId);
        const title = document.getElementById(config.titleId);
        const directions = document.getElementById(config.directionsId);
        const map = createMap(config.mapId, [36.8065, 10.1815]);
        const currentAddress = String(config.currentAddress || '').trim();

        function updateStatus(message, muted) {
            if (!status) {
                return;
            }
            status.textContent = message;
            status.classList.toggle('is-muted', Boolean(muted));
        }

        updateStatus('Chargement des lieux sur la carte...', true);
        const resolvedVenues = await resolveVenues(venues, function (done, total) {
            updateStatus(`${done}/${total} lieux affiches...`, true);
        });

        const bounds = [];
        let activeVenue = null;

        resolvedVenues.forEach((venue) => {
            const marker = L.marker([venue.lat, venue.lng], {
                icon: createRedIcon(currentAddress && normalize(venue.address) === normalize(currentAddress)),
            }).addTo(map);
            marker.bindPopup(venuePopup(venue));
            bounds.push([venue.lat, venue.lng]);

            if (currentAddress && normalize(venue.address) === normalize(currentAddress)) {
                activeVenue = venue;
                marker.openPopup();
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [28, 28] });
        }

        if (!activeVenue && currentAddress) {
            updateStatus('Le lieu actuel ne fait pas partie des 10 adresses configurees.', false);
            return;
        }

        if (activeVenue) {
            L.circleMarker([activeVenue.lat, activeVenue.lng], {
                radius: 18,
                color: '#1e5ba8',
                weight: 2,
                fillColor: '#6bbf59',
                fillOpacity: 0.2,
            }).addTo(map);

            if (title) {
                title.textContent = activeVenue.label;
            }

            if (directions) {
                directions.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(activeVenue.address)}`;
            }

            updateStatus('Le lieu de cet evenement est mis en avant sur la carte.', false);
        } else {
            updateStatus('Carte chargee avec les lieux recommandes.', false);
        }

        setTimeout(() => map.invalidateSize(), 120);
        setTimeout(() => map.invalidateSize(), 500);
    }

    window.GrowMindVenueMaps = {
        initPicker,
        initShowcase,
    };
})();
