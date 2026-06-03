@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar.cms-flatpickr-theme {
            z-index: 2000;
            border: 1px solid rgba(128, 0, 0, 0.14);
            border-radius: 16px;
            background: #fffdf8;
            box-shadow: 0 18px 40px rgba(64, 24, 24, 0.18);
            color: #2d1f1f;
        }

        .flatpickr-calendar.cms-flatpickr-theme.arrowTop::before,
        .flatpickr-calendar.cms-flatpickr-theme.arrowTop::after,
        .flatpickr-calendar.cms-flatpickr-theme.arrowBottom::before,
        .flatpickr-calendar.cms-flatpickr-theme.arrowBottom::after {
            border-bottom-color: #fffdf8;
            border-top-color: #fffdf8;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-months {
            padding: 0.5rem 0.5rem 0;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-month {
            height: 60px;
            overflow: visible;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-current-month {
            left: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3rem;
            height: 60px;
            color: #4a2727;
            font-weight: 600;
            pointer-events: none;
            transform: none;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-current-month .cur-month,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-current-month .numInputWrapper {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
            overflow: hidden;
        }

        .flatpickr-calendar.cms-flatpickr-theme .cms-flatpickr-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex: 0 0 auto;
            position: relative;
            z-index: 5;
            pointer-events: auto;
        }

        .flatpickr-calendar.cms-flatpickr-theme .cms-month-dropdown,
        .flatpickr-calendar.cms-flatpickr-theme .cms-year-dropdown {
            appearance: none;
            border: 1px solid rgba(128, 0, 0, 0.18);
            border-radius: 10px;
            background: #ffffff;
            color: #4a2727;
            font: inherit;
            font-weight: 600;
            line-height: 1.2;
            min-height: 2.25rem;
            padding: 0.45rem 2rem 0.45rem 0.75rem;
        }

        .flatpickr-calendar.cms-flatpickr-theme .cms-month-dropdown {
            min-width: 7.5rem;
        }

        .flatpickr-calendar.cms-flatpickr-theme .cms-year-dropdown {
            min-width: 5.5rem;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-prev-month,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-next-month {
            color: #4a2727;
            fill: #4a2727;
            padding: 0.8rem;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-prev-month:hover,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-next-month:hover {
            color: #7b1113;
            fill: #7b1113;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-weekdays {
            background: transparent;
        }

        .flatpickr-calendar.cms-flatpickr-theme span.flatpickr-weekday {
            color: #6b5b5b;
            font-weight: 700;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-days {
            padding: 0 0 0.7rem;
        }

        .flatpickr-calendar.cms-flatpickr-theme .dayContainer {
            gap: 0;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day {
            color: #3f3131;
            border-radius: 999px;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.today {
            border-color: rgba(123, 17, 19, 0.35);
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day:hover,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day:focus {
            background: rgba(123, 17, 19, 0.08);
            border-color: rgba(123, 17, 19, 0.18);
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.selected,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.startRange,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.endRange {
            background: #7b1113;
            border-color: #7b1113;
            color: #ffffff;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.inRange {
            background: rgba(123, 17, 19, 0.12);
            border-color: transparent;
            box-shadow: none;
            color: #4a2727;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.flatpickr-disabled,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.flatpickr-disabled:hover,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.prevMonthDay,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-day.nextMonthDay {
            color: #c6bdbd;
        }

        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-innerContainer,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-rContainer,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-weekdays,
        .flatpickr-calendar.cms-flatpickr-theme .flatpickr-days,
        .flatpickr-calendar.cms-flatpickr-theme .dayContainer {
            box-sizing: border-box;
        }
    </style>
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

            const nativeMonthSelect = currentMonthEl.querySelector('.flatpickr-monthDropdown-months');
            const nativeYearWrapper = currentMonthEl.querySelector('.numInputWrapper');
            const yearClass = String(config.yearDropdownClass || 'cms-year-dropdown');
            let controls = currentMonthEl.querySelector('[data-cms-flatpickr-controls]');
            let monthSelect = controls?.querySelector('[data-cms-flatpickr-month]') || null;
            let yearSelect = controls?.querySelector('.' + yearClass) || null;

            if (!controls || !monthSelect || !yearSelect) {
                controls?.remove();
                controls = document.createElement('div');
                controls.className = 'cms-flatpickr-controls';
                controls.setAttribute('data-cms-flatpickr-controls', 'true');

                monthSelect = document.createElement('select');
                monthSelect.className = 'cms-month-dropdown';
                monthSelect.setAttribute('data-cms-flatpickr-month', 'true');
                monthSelect.setAttribute('aria-label', 'Select month');
                monthSelect.addEventListener('change', () => {
                    const selectedMonth = Number.parseInt(monthSelect.value, 10);
                    if (Number.isFinite(selectedMonth) && selectedMonth !== instance.currentMonth) {
                        instance.changeMonth(selectedMonth, false);
                        instance.jumpToDate(new Date(instance.currentYear, selectedMonth, 1));
                    }
                });

                yearSelect = document.createElement('select');
                yearSelect.className = yearClass;
                yearSelect.setAttribute('aria-label', 'Select year');
                yearSelect.addEventListener('change', () => {
                    const selectedYear = Number.parseInt(yearSelect.value, 10);
                    if (Number.isFinite(selectedYear)) {
                        instance.changeYear(selectedYear);
                        instance.jumpToDate(new Date(selectedYear, instance.currentMonth, 1));
                    }
                });

                [monthSelect, yearSelect].forEach((select) => {
                    ['mousedown', 'click', 'touchstart'].forEach((eventName) => {
                        select.addEventListener(eventName, (event) => {
                            event.stopPropagation();
                        });
                    });
                });

                controls.appendChild(monthSelect);
                controls.appendChild(yearSelect);
                currentMonthEl.appendChild(controls);
            }

            if (nativeMonthSelect) {
                nativeMonthSelect.setAttribute('tabindex', '-1');
                nativeMonthSelect.setAttribute('aria-hidden', 'true');
            }

            if (nativeYearWrapper) {
                nativeYearWrapper.setAttribute('aria-hidden', 'true');
            }

            const monthOptions = nativeMonthSelect
                ? Array.from(nativeMonthSelect.options).map((option, index) => ({
                    value: option.value !== '' ? option.value : String(index),
                    label: String(option.textContent || '').trim(),
                }))
                : instance.l10n.months.longhand.map((monthLabel, index) => ({
                    value: String(index),
                    label: monthLabel,
                }));

            monthSelect.innerHTML = '';
            monthOptions.forEach((option) => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.label;
                monthSelect.appendChild(opt);
            });
            monthSelect.value = String(instance.currentMonth);

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
                monthSelectorType: 'static',
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
