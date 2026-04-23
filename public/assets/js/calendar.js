document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('eventCalendar');
    if (!calendarEl) {
        return;
    }

    const events = JSON.parse(calendarEl.dataset.events || '[]');
    const selectedDate = calendarEl.dataset.selectedDate || '';
    const filterUrl = calendarEl.dataset.filterUrl || '/evenement';
    const today = new Date();
    let currentDate = selectedDate
        ? new Date(selectedDate + 'T00:00:00')
        : new Date(today.getFullYear(), today.getMonth(), 1);
    let activeDate = selectedDate || formatDate(today);

    calendarEl.innerHTML = `
        <section class="gm-calendar">
            <div class="gm-calendar-topbar">
                <div>
                    <div class="gm-calendar-label">Agenda GrowMind</div>
                    <h3 id="gmCalendarTitle" class="gm-calendar-title"></h3>
                    <p class="gm-calendar-subtitle">Cliquez sur une date pour afficher les evenements du jour et filtrer la liste.</p>
                </div>
                <div class="gm-calendar-actions">
                    <button type="button" class="gm-calendar-today" id="gmCalendarToday">Aujourd'hui</button>
                    <button type="button" class="gm-calendar-nav" data-nav="-1" aria-label="Mois precedent">&#8249;</button>
                    <button type="button" class="gm-calendar-nav" data-nav="1" aria-label="Mois suivant">&#8250;</button>
                </div>
            </div>

            <div class="gm-calendar-layout">
                <div class="gm-calendar-board">
                    <div class="gm-calendar-grid" id="gmCalendarGrid"></div>
                </div>

                <aside class="gm-calendar-sidebar">
                    <div class="gm-sidebar-card">
                        <div class="gm-sidebar-kicker">Date selectionnee</div>
                        <h4 id="gmSelectedDateTitle" class="gm-sidebar-title"></h4>
                        <p id="gmSelectedDateMeta" class="gm-sidebar-meta"></p>
                        <div class="gm-sidebar-actions">
                            <button type="button" class="gm-filter-btn" id="gmApplyFilter">Afficher les evenements du jour</button>
                            <button type="button" class="gm-clear-btn" id="gmClearFilter">Voir tous les evenements</button>
                        </div>
                    </div>

                    <div class="gm-sidebar-card">
                        <div class="gm-sidebar-kicker">Programme du jour</div>
                        <div id="gmSelectedDateList" class="gm-selected-list"></div>
                    </div>
                </aside>
            </div>
        </section>
    `;

    const titleEl = document.getElementById('gmCalendarTitle');
    const gridEl = document.getElementById('gmCalendarGrid');
    const selectedDateTitleEl = document.getElementById('gmSelectedDateTitle');
    const selectedDateMetaEl = document.getElementById('gmSelectedDateMeta');
    const selectedDateListEl = document.getElementById('gmSelectedDateList');
    const applyFilterButton = document.getElementById('gmApplyFilter');
    const clearFilterButton = document.getElementById('gmClearFilter');
    const todayButton = document.getElementById('gmCalendarToday');

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatDisplayDate(dateString) {
        return new Date(dateString + 'T00:00:00').toLocaleDateString('fr-FR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    function isToday(dateString) {
        return dateString === formatDate(today);
    }

    function filterToDate(dateString) {
        const url = new URL(filterUrl, window.location.origin);
        url.searchParams.set('date', dateString);
        window.location.href = url.toString();
    }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const firstWeekday = (firstDay.getDay() + 6) % 7;

        titleEl.textContent = currentDate.toLocaleDateString('fr-FR', {
            month: 'long',
            year: 'numeric'
        });

        let html = `
            <div class="gm-weekday">Lun</div>
            <div class="gm-weekday">Mar</div>
            <div class="gm-weekday">Mer</div>
            <div class="gm-weekday">Jeu</div>
            <div class="gm-weekday">Ven</div>
            <div class="gm-weekday">Sam</div>
            <div class="gm-weekday">Dim</div>
        `;

        for (let index = 0; index < firstWeekday; index += 1) {
            html += '<div class="gm-day gm-day-empty"></div>';
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const cellDate = new Date(year, month, day);
            const dateString = formatDate(cellDate);
            const dayEvents = events.filter((event) => event.start === dateString);
            const badgeCount = dayEvents.length > 1 ? `<span class="gm-day-count">${dayEvents.length}</span>` : '';

            html += `
                <button
                    type="button"
                    class="gm-day ${dayEvents.length ? 'gm-day-has-event' : ''} ${activeDate === dateString ? 'gm-day-selected' : ''} ${isToday(dateString) ? 'gm-day-today' : ''}"
                    data-date="${dateString}"
                >
                    <span class="gm-day-number">${day}</span>
                    ${badgeCount}
                    ${dayEvents.length ? '<span class="gm-day-dot"></span>' : ''}
                </button>
            `;
        }

        gridEl.innerHTML = html;

        gridEl.querySelectorAll('.gm-day[data-date]').forEach((button) => {
            button.addEventListener('click', function () {
                activeDate = this.dataset.date;
                renderCalendar();
            });
        });

        renderSidebar();
    }

    function renderSidebar() {
        const dayEvents = events.filter((event) => event.start === activeDate);

        selectedDateTitleEl.textContent = formatDisplayDate(activeDate);
        selectedDateMetaEl.textContent = dayEvents.length
            ? `${dayEvents.length} evenement(s) programme(s) pour cette date`
            : 'Aucun evenement programme pour cette date';

        if (!dayEvents.length) {
            selectedDateListEl.innerHTML = `
                <div class="gm-empty-day">
                    <strong>Jour libre</strong>
                    <span>Choisissez une autre date ou revenez a la vue complete.</span>
                </div>
            `;
            return;
        }

        selectedDateListEl.innerHTML = dayEvents.map((event) => `
            <article class="gm-event-chip">
                <div class="gm-event-chip-top">
                    <span class="gm-event-dot"></span>
                    <strong>${event.title}</strong>
                </div>
                <p>${event.location || 'Lieu a confirmer'}</p>
                <div class="gm-event-chip-bottom">
                    <span>${event.badge || 'Disponible'}</span>
                    <a href="${event.url}">Ouvrir</a>
                </div>
            </article>
        `).join('');
    }

    calendarEl.querySelectorAll('[data-nav]').forEach((button) => {
        button.addEventListener('click', function () {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + Number(this.dataset.nav), 1);
            renderCalendar();
        });
    });

    todayButton.addEventListener('click', function () {
        currentDate = new Date(today.getFullYear(), today.getMonth(), 1);
        activeDate = formatDate(today);
        renderCalendar();
    });

    applyFilterButton.addEventListener('click', function () {
        filterToDate(activeDate);
    });

    clearFilterButton.addEventListener('click', function () {
        window.location.href = filterUrl;
    });

    const style = document.createElement('style');
    style.textContent = `
        .gm-calendar {
            display: grid;
            gap: 22px;
        }
        .gm-calendar-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }
        .gm-calendar-label {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eef8ea;
            color: #2f6b49;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .gm-calendar-title {
            margin: 0;
            color: #183153;
            font-weight: 800;
            font-size: 32px;
        }
        .gm-calendar-subtitle {
            margin: 8px 0 0;
            color: #5d6d7c;
            max-width: 720px;
        }
        .gm-calendar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gm-calendar-today {
            height: 42px;
            border: none;
            border-radius: 999px;
            background: #eef4fb;
            color: #1E5BA8;
            padding: 0 16px;
            font-weight: 800;
            cursor: pointer;
        }
        .gm-calendar-nav {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #6BBF59, #1E5BA8);
            color: #fff;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }
        .gm-calendar-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(280px, .95fr);
            gap: 22px;
        }
        .gm-calendar-board,
        .gm-sidebar-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
            border: 1px solid rgba(30, 91, 168, 0.08);
            border-radius: 24px;
            padding: 18px;
        }
        .gm-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }
        .gm-weekday {
            text-align: center;
            font-weight: 800;
            color: #44607c;
            padding: 8px 0;
        }
        .gm-day {
            min-height: 76px;
            border: none;
            background: #f8fbfd;
            border-radius: 18px;
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 12px;
            color: #183153;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            cursor: pointer;
        }
        .gm-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(30, 91, 168, 0.08);
        }
        .gm-day-has-event {
            background: #eef8ea;
            box-shadow: inset 0 0 0 1px #d8e8d0;
        }
        .gm-day-selected {
            box-shadow: inset 0 0 0 2px #1E5BA8;
            background: linear-gradient(180deg, #edf5ff 0%, #f4fbff 100%);
        }
        .gm-day-today {
            box-shadow: inset 0 0 0 2px rgba(107, 191, 89, .55);
        }
        .gm-day-empty {
            background: transparent;
            cursor: default;
            box-shadow: none;
        }
        .gm-day-number {
            font-size: 15px;
        }
        .gm-day-dot {
            position: absolute;
            bottom: 12px;
            left: 12px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6BBF59;
        }
        .gm-day-count {
            position: absolute;
            top: 10px;
            right: 10px;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: #1E5BA8;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gm-calendar-sidebar {
            display: grid;
            gap: 16px;
        }
        .gm-sidebar-kicker {
            color: #5d6d7c;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 8px;
        }
        .gm-sidebar-title {
            margin: 0;
            color: #183153;
            font-size: 22px;
            font-weight: 800;
        }
        .gm-sidebar-meta {
            margin: 10px 0 0;
            color: #5d6d7c;
        }
        .gm-sidebar-actions {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }
        .gm-filter-btn,
        .gm-clear-btn {
            width: 100%;
            min-height: 44px;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }
        .gm-filter-btn {
            background: linear-gradient(135deg, #1E5BA8, #6BBF59);
            color: #fff;
        }
        .gm-clear-btn {
            background: #eef4fb;
            color: #1E5BA8;
        }
        .gm-selected-list {
            display: grid;
            gap: 12px;
        }
        .gm-event-chip {
            background: #f5fbf2;
            border: 1px solid #d8e8d0;
            border-radius: 18px;
            padding: 14px;
        }
        .gm-event-chip-top,
        .gm-event-chip-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .gm-event-chip-top strong {
            color: #183153;
            font-size: 15px;
        }
        .gm-event-chip p {
            margin: 10px 0 12px;
            color: #5d6d7c;
            font-size: 14px;
        }
        .gm-event-chip-bottom span {
            color: #2f6b49;
            font-size: 12px;
            font-weight: 800;
        }
        .gm-event-chip-bottom a {
            color: #1E5BA8;
            text-decoration: none;
            font-weight: 800;
        }
        .gm-event-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6BBF59;
            flex: 0 0 auto;
        }
        .gm-empty-day {
            display: grid;
            gap: 6px;
            background: #f8fbfd;
            border: 1px dashed #c9d9ea;
            border-radius: 18px;
            padding: 18px;
            color: #5d6d7c;
        }
        @media (max-width: 920px) {
            .gm-calendar-topbar,
            .gm-calendar-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .gm-calendar-layout {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .gm-calendar-grid {
                gap: 6px;
            }
            .gm-day {
                min-height: 58px;
                padding: 10px;
                border-radius: 14px;
            }
            .gm-calendar-title {
                font-size: 26px;
            }
        }
    `;

    document.head.appendChild(style);
    renderCalendar();
});
