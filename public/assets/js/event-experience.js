(function () {
    const FAVORITES_KEY = 'growmind-favorite-events-v1';
    const RECENT_KEY = 'growmind-recent-events-v1';

    function readStore(key) {
        try {
            const parsed = JSON.parse(window.localStorage.getItem(key) || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function writeStore(key, value) {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            // Ignore storage errors.
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getEventFromDataset(dataset) {
        return {
            id: String(dataset.eventId || ''),
            title: String(dataset.eventTitle || ''),
            date: String(dataset.eventDate || ''),
            location: String(dataset.eventLocation || ''),
            price: String(dataset.eventPrice || ''),
            url: String(dataset.eventUrl || ''),
            description: String(dataset.eventDescription || ''),
        };
    }

    function upsertItem(items, item, limit) {
        const filtered = items.filter((entry) => String(entry.id) !== String(item.id));
        filtered.unshift(item);
        return filtered.slice(0, limit);
    }

    function isFavorite(id) {
        return readStore(FAVORITES_KEY).some((item) => String(item.id) === String(id));
    }

    function toggleFavorite(item) {
        const favorites = readStore(FAVORITES_KEY);
        const exists = favorites.some((entry) => String(entry.id) === String(item.id));

        if (exists) {
            writeStore(FAVORITES_KEY, favorites.filter((entry) => String(entry.id) !== String(item.id)));
            return false;
        }

        writeStore(FAVORITES_KEY, upsertItem(favorites, item, 12));
        return true;
    }

    function pushRecent(item) {
        writeStore(RECENT_KEY, upsertItem(readStore(RECENT_KEY), item, 8));
    }

    function buttonLabel(active) {
        return active ? 'Retire des favoris' : 'Ajouter aux favoris';
    }

    function iconLabel(active) {
        return active ? '❤' : '♡';
    }

    function syncFavoriteButtons() {
        document.querySelectorAll('[data-favorite-toggle]').forEach((button) => {
            const active = isFavorite(button.dataset.eventId);
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.setAttribute('title', buttonLabel(active));
            const icon = button.querySelector('[data-favorite-icon]');
            const text = button.querySelector('[data-favorite-text]');
            if (icon) icon.textContent = iconLabel(active);
            if (text) text.textContent = active ? 'Sauvegarde' : 'Favori';
        });
    }

    function renderCards(container, items, emptyMessage) {
        if (!container) {
            return;
        }

        if (!items.length) {
            container.innerHTML = `<div class="gm-empty-note">${escapeHtml(emptyMessage)}</div>`;
            return;
        }

        container.innerHTML = items.map((item) => `
            <article class="gm-mini-card">
                <div class="gm-mini-card-top">
                    <span class="gm-mini-pill">${escapeHtml(item.date)}</span>
                    <button type="button" class="gm-mini-favorite ${isFavorite(item.id) ? 'is-active' : ''}" data-favorite-toggle data-event-id="${escapeHtml(item.id)}">
                        <span data-favorite-icon>${isFavorite(item.id) ? '❤' : '♡'}</span>
                        <span data-favorite-text>${isFavorite(item.id) ? 'Sauvegarde' : 'Favori'}</span>
                    </button>
                </div>
                <h3>${escapeHtml(item.title)}</h3>
                <p>${escapeHtml(item.location)}</p>
                <div class="gm-mini-meta">
                    <span>${escapeHtml(item.price)}</span>
                    <a href="${escapeHtml(item.url)}">Ouvrir</a>
                </div>
            </article>
        `).join('');
    }

    function bindFavoriteClicks(onChange) {
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-favorite-toggle]');
            if (!button) {
                return;
            }

            const source = button.closest('[data-event-card], [data-event-detail]');
            let item = null;

            if (source) {
                item = getEventFromDataset(source.dataset);
            } else {
                item = { id: button.dataset.eventId };
            }

            toggleFavorite(item);
            syncFavoriteButtons();
            if (typeof onChange === 'function') {
                onChange();
            }
        });
    }

    function initListPage() {
        const cards = Array.from(document.querySelectorAll('[data-event-card]'));
        if (!cards.length) {
            return;
        }

        const favoritesGrid = document.getElementById('gmFavoritesGrid');
        const recentGrid = document.getElementById('gmRecentGrid');

        function renderPanels() {
            renderCards(favoritesGrid, readStore(FAVORITES_KEY), 'Aucun favori pour le moment.');
            renderCards(recentGrid, readStore(RECENT_KEY), 'Aucun evenement consulte recemment.');
            syncFavoriteButtons();
        }

        bindFavoriteClicks(renderPanels);
        syncFavoriteButtons();
        renderPanels();

        const campaignCards = Array.from(document.querySelectorAll('[data-campaign-card]'));
        const campaignButtons = Array.from(document.querySelectorAll('[data-campaign-filter]'));
        const resetCampaignButton = document.querySelector('[data-campaign-reset]');

        function setCampaignState(key) {
            campaignCards.forEach((card) => {
                const active = !key || card.dataset.campaignKey === key;
                card.classList.toggle('is-featured', active);
            });

            cards.forEach((card) => {
                const campaigns = String(card.dataset.campaigns || '').split(',').filter(Boolean);
                const matches = !key || campaigns.includes(key);
                card.classList.toggle('is-highlighted', matches);
                card.classList.toggle('is-dimmed', !matches);
            });
        }

        campaignButtons.forEach((button) => {
            button.addEventListener('click', function () {
                setCampaignState(this.dataset.campaignFilter || '');
                const target = document.querySelector('.events-grid');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        if (resetCampaignButton) {
            resetCampaignButton.addEventListener('click', function () {
                setCampaignState('');
            });
        }

        if (campaignCards.length > 0) {
            setCampaignState(campaignCards[0].dataset.campaignKey || '');
        }

        if (campaignCards.length > 1) {
            let featuredIndex = 0;
            window.setInterval(function () {
                featuredIndex = (featuredIndex + 1) % campaignCards.length;
                const key = campaignCards[featuredIndex].dataset.campaignKey || '';
                setCampaignState(key);
            }, 5500);
        }
    }

    function initDetailPage() {
        const detail = document.querySelector('[data-event-detail]');
        if (!detail) {
            return;
        }

        const item = getEventFromDataset(detail.dataset);
        pushRecent(item);
        syncFavoriteButtons();

        const copyButton = document.getElementById('gmCopyEventLink');
        if (copyButton) {
            copyButton.addEventListener('click', async function () {
                const url = item.url || window.location.href;
                try {
                    await navigator.clipboard.writeText(url);
                    this.querySelector('[data-copy-label]').textContent = 'Lien copie';
                } catch (error) {
                    this.querySelector('[data-copy-label]').textContent = 'Copie impossible';
                }
            });
        }

        const shareButton = document.getElementById('gmShareEvent');
        if (shareButton) {
            if (navigator.share) {
                shareButton.addEventListener('click', async function () {
                    try {
                        await navigator.share({
                            title: item.title,
                            text: `${item.title} • ${item.location} • ${item.date}`,
                            url: item.url || window.location.href,
                        });
                    } catch (error) {
                        // Ignore cancelled shares.
                    }
                });
            } else {
                shareButton.style.display = 'none';
            }
        }

        const countdown = document.getElementById('gmCountdown');
        if (countdown) {
            const eventDate = countdown.dataset.eventDate;
            const dayEl = document.getElementById('gmCountdownDays');
            const hourEl = document.getElementById('gmCountdownHours');
            const minuteEl = document.getElementById('gmCountdownMinutes');
            const eventDateTime = new Date(`${eventDate}T12:00:00`);

            function updateCountdown() {
                const diff = eventDateTime.getTime() - Date.now();
                if (diff <= 0) {
                    if (dayEl) dayEl.textContent = '0';
                    if (hourEl) hourEl.textContent = '0';
                    if (minuteEl) minuteEl.textContent = '0';
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                const minutes = Math.floor((diff / (1000 * 60)) % 60);

                if (dayEl) dayEl.textContent = String(days);
                if (hourEl) hourEl.textContent = String(hours);
                if (minuteEl) minuteEl.textContent = String(minutes);
            }

            updateCountdown();
            window.setInterval(updateCountdown, 60000);
        }

        const notifyButton = document.getElementById('gmNotifyEvent');
        const notifyLabel = document.getElementById('gmNotifyLabel');
        if (notifyButton && notifyLabel) {
            notifyButton.addEventListener('click', async function () {
                if (!('Notification' in window)) {
                    notifyLabel.textContent = 'Rappels non supportes';
                    return;
                }

                if (Notification.permission === 'default') {
                    await Notification.requestPermission();
                }

                if (Notification.permission !== 'granted') {
                    notifyLabel.textContent = 'Autorisation refusee';
                    return;
                }

                notifyLabel.textContent = 'Rappel active';
                window.setTimeout(function () {
                    new Notification(item.title, {
                        body: `${item.date} • ${item.location}`,
                    });
                }, 3000);
            });
        }

        bindFavoriteClicks();
        syncFavoriteButtons();
    }

    document.addEventListener('DOMContentLoaded', function () {
        initListPage();
        initDetailPage();
    });
})();
