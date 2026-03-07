@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    (() => {
        if (window.CmsCalendar) return;

        function resolveInput(inputOrSelector) {
            if (!inputOrSelector) return null;
            if (typeof inputOrSelector === 'string') {
                return document.querySelector(inputOrSelector);
            }
            if (inputOrSelector instanceof HTMLElement) {
                return inputOrSelector;
            }
            return null;
        }

        function mountYearDropdown(instance, config) {
            if (!config.enableYearDropdown) return;
            const calendar = instance?.calendarContainer;
            if (!calendar) return;

            const currentMonthEl = calendar.querySelector('.flatpickr-current-month');
            if (!currentMonthEl) return;

            const yearClass = String(config.yearDropdownClass || 'cms-year-dropdown');
            let yearSelect = currentMonthEl.querySelector('.' + yearClass);
            if (!yearSelect) {
                yearSelect = document.createElement('select');
                yearSelect.className = yearClass;
                yearSelect.setAttribute('aria-label', 'Select year');
                yearSelect.addEventListener('change', () => {
                    const selectedYear = Number.parseInt(yearSelect.value, 10);
                    if (Number.isFinite(selectedYear)) {
                        instance.changeYear(selectedYear);
                    }
                });
                currentMonthEl.appendChild(yearSelect);
            }

            const selectedDate = instance.selectedDates?.[0] || null;
            const activeYear = selectedDate
                ? selectedDate.getFullYear()
                : Number(instance.currentYear || new Date().getFullYear());
            const nowYear = new Date().getFullYear();
            const minYear = Math.min(1970, activeYear - 80);
            const maxYear = Math.max(nowYear + 10, activeYear + 10);

            yearSelect.innerHTML = '';
            for (let y = maxYear; y >= minYear; y -= 1) {
                const opt = document.createElement('option');
                opt.value = String(y);
                opt.textContent = String(y);
                yearSelect.appendChild(opt);
            }
            yearSelect.value = String(activeYear);
        }

        function init(inputOrSelector, opts = {}) {
            const input = resolveInput(inputOrSelector);
            if (!input) return null;

            if (typeof window.flatpickr !== 'function') {
                input.type = 'date';
                return null;
            }

            const config = {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'M j, Y',
                allowInput: false,
                disableMobile: true,
                clickOpens: true,
                monthSelectorType: 'dropdown',
                enableYearDropdown: true,
                calendarClass: 'cms-flatpickr-theme',
                yearDropdownClass: 'cms-year-dropdown',
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                prevArrow: '<i class="fas fa-chevron-left"></i>',
                ...opts,
            };

            const userOnReady = config.onReady;
            const userOnOpen = config.onOpen;
            const userOnMonthChange = config.onMonthChange;
            const userOnYearChange = config.onYearChange;
            const userOnChange = config.onChange;

            const fp = window.flatpickr(input, {
                dateFormat: config.dateFormat,
                altInput: config.altInput,
                altFormat: config.altFormat,
                allowInput: config.allowInput,
                disableMobile: config.disableMobile,
                clickOpens: config.clickOpens,
                monthSelectorType: config.monthSelectorType,
                nextArrow: config.nextArrow,
                prevArrow: config.prevArrow,
                onChange: (...args) => {
                    if (typeof userOnChange === 'function') userOnChange(...args);
                },
                onReady: (...args) => {
                    const instance = args[2];
                    if (config.calendarClass && instance?.calendarContainer) {
                        instance.calendarContainer.classList.add(config.calendarClass);
                    }
                    mountYearDropdown(instance, config);
                    if (typeof userOnReady === 'function') userOnReady(...args);
                },
                onOpen: (...args) => {
                    mountYearDropdown(args[2], config);
                    if (typeof userOnOpen === 'function') userOnOpen(...args);
                },
                onMonthChange: (...args) => {
                    mountYearDropdown(args[2], config);
                    if (typeof userOnMonthChange === 'function') userOnMonthChange(...args);
                },
                onYearChange: (...args) => {
                    mountYearDropdown(args[2], config);
                    if (typeof userOnYearChange === 'function') userOnYearChange(...args);
                },
            });

            return fp;
        }

        window.CmsCalendar = { init };
    })();
    </script>
@endonce
