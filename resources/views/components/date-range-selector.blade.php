@props([
    'label' => 'Date Range:',
    'presetId' => 'analyticsPreset',
    'dropdownId' => 'analyticsPresetDropdown',
    'startId' => 'analyticsStart',
    'endId' => 'analyticsEnd',
    'defaultPreset' => '30',
    'presetName' => null,
    'emitInputs' => true,
    'includeAll' => false,
    'allLabel' => 'All Dates',
    'includeCustom' => false,
    'customLabel' => 'Custom Range',
    'customValue' => 'CUSTOM',
    'customStartId' => null,
    'customEndId' => null,
    'customWrapId' => null,
])

@php
    $defaultPreset = (string) $defaultPreset;
    $customStartId = $customStartId ?: ($presetId . '_custom_start');
    $customEndId = $customEndId ?: ($presetId . '_custom_end');
    $customWrapId = $customWrapId ?: ($presetId . '_custom_wrap');
    $options = [
        ['value' => '7', 'label' => 'Last 7 Days'],
        ['value' => '30', 'label' => 'Last 30 Days'],
        ['value' => '90', 'label' => 'Last 3 Months'],
        ['value' => '180', 'label' => 'Last 6 Months'],
        ['value' => '365', 'label' => 'Last Year'],
    ];
    if ($includeAll) {
        array_unshift($options, ['value' => 'ALL', 'label' => $allLabel]);
    }
    if ($includeCustom) {
        $options[] = ['value' => (string) $customValue, 'label' => $customLabel];
    }
    $activeOption = collect($options)->firstWhere('value', $defaultPreset)
        ?? collect($options)->firstWhere('value', '30')
        ?? $options[0];
@endphp

<div class="date-range-selector">
    <label>{{ $label }}</label>

    <div class="filter-field filter-select">
        <i class="fas fa-calendar-days"></i>
        <div class="cms-dropdown" id="{{ $dropdownId }}">
            <button type="button" class="cms-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">
                <span class="cms-dropdown-label">{{ $activeOption['label'] }}</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="cms-dropdown-menu" role="listbox">
                @foreach ($options as $option)
                    <button type="button" class="cms-dropdown-option {{ $option['value'] === (string) $activeOption['value'] ? 'active' : '' }}" data-value="{{ $option['value'] }}">{{ $option['label'] }}</button>
                @endforeach
            </div>
            <select id="{{ $presetId }}" @if($presetName) name="{{ $presetName }}" @endif tabindex="-1" aria-hidden="true" class="cms-native-select">
                @foreach ($options as $option)
                    <option value="{{ $option['value'] }}" {{ $option['value'] === (string) $activeOption['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($emitInputs)
        <input type="hidden" id="{{ $startId }}" value="">
        <input type="hidden" id="{{ $endId }}" value="">
    @endif

    @if ($includeCustom)
        <div class="date-range-custom-wrap" id="{{ $customWrapId }}" hidden>
            <div class="filter-field filter-date">
                <i class="fas fa-calendar-days"></i>
                <input type="month" id="{{ $customStartId }}" aria-label="Start month">
            </div>
            <span class="date-range-separator">to</span>
            <div class="filter-field filter-date">
                <i class="fas fa-calendar-days"></i>
                <input type="month" id="{{ $customEndId }}" aria-label="End month">
            </div>
        </div>
    @endif
</div>

@once
    <style>
    .date-range-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        position: relative;
        z-index: 30;
        overflow: visible;
    }

    .date-range-selector label {
        color: #800000;
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
    }

    .date-range-selector.has-custom-open {
        align-items: center;
    }

    .date-range-selector .filter-field {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 0 12px;
        border: 2px solid #e4e4e4;
        border-radius: 12px;
        background: linear-gradient(180deg, #fff 0%, #fcfcfc 100%);
        transition: border-color .2s ease, box-shadow .2s ease, transform .15s ease;
        position: relative;
        overflow: visible;
        z-index: 1;
    }

    .date-range-selector .filter-field i {
        color: #8a8a8a;
        font-size: 14px;
        width: 16px;
        text-align: center;
        flex-shrink: 0;
    }

    .date-range-selector .filter-field:focus-within {
        border-color: #d4af37;
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.18);
        transform: translateY(-1px);
    }

    .date-range-selector .filter-field:focus-within i {
        color: #800000;
    }

    .date-range-selector .cms-dropdown {
        position: relative;
        width: 100%;
        z-index: 2;
        overflow: visible;
    }

    .date-range-selector .cms-dropdown.open {
        z-index: 1600;
    }

    .date-range-selector .cms-dropdown-trigger {
        width: 100%;
        min-height: 40px;
        border: none;
        background: transparent;
        color: #333;
        font-size: 13px;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0;
        cursor: pointer;
    }

    .date-range-selector .cms-dropdown-trigger i {
        color: #8a8a8a;
        font-size: 12px;
        transition: transform .2s ease, color .2s ease;
    }

    .date-range-selector .cms-dropdown.open .cms-dropdown-trigger i {
        transform: rotate(180deg);
        color: #800000;
    }

    .date-range-selector .cms-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 100%;
        min-width: 220px;
        background: #fff;
        border: 1px solid #ead8a0;
        border-radius: 12px;
        box-shadow: 0 14px 30px rgba(22, 29, 37, 0.16);
        padding: 8px;
        z-index: 1650;
        display: none;
        max-height: 260px;
        overflow-y: auto;
    }

    .date-range-selector .cms-dropdown.open .cms-dropdown-menu {
        display: block;
    }

    .date-range-selector .cms-dropdown-option {
        width: 100%;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #3d3d3d;
        padding: 10px 12px;
        text-align: left;
        font-size: 13px;
        font-family: inherit;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease;
    }

    .date-range-selector .cms-dropdown-option:hover {
        background: rgba(128, 0, 0, 0.08);
        color: #800000;
    }

    .date-range-selector .cms-dropdown-option.active {
        background: linear-gradient(135deg, #800000, #5c0000);
        color: #fff;
        font-weight: 600;
    }

    .date-range-selector .date-range-custom-wrap {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 5;
        overflow: visible;
    }

    .date-range-selector .date-range-custom-wrap[hidden] {
        display: none !important;
    }

    .date-range-selector .date-range-custom-wrap .filter-field {
        min-width: 165px;
        flex: 1;
    }

    .date-range-selector .date-range-custom-wrap input[type="month"] {
        width: 100%;
        min-height: 38px;
        border: none;
        outline: none;
        background: transparent;
        color: #333;
        font-size: 13px;
        font-family: inherit;
    }

    .date-range-selector .date-range-separator {
        color: #666;
        font-size: 13px;
        white-space: nowrap;
    }
    </style>

    <script>
    (() => {
        if (window.CmsDateRange) return;

        function toIsoDateLocal(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        function computeRange(daysValue) {
            if (String(daysValue || '').toUpperCase() === 'ALL') {
                return { start: '1970-01-01', end: toIsoDateLocal(new Date()) };
            }

            const days = Number(daysValue || 30);
            if (!Number.isFinite(days) || days <= 0) {
                return { start: '', end: '' };
            }

            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - days);
            return {
                start: toIsoDateLocal(start),
                end: toIsoDateLocal(end),
            };
        }

        function toMonthValue(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            return `${y}-${m}`;
        }

        function computeMonthRange(startMonth, endMonth) {
            const sm = String(startMonth || '').trim();
            const em = String(endMonth || '').trim();
            const re = /^(\d{4})-(\d{2})$/;
            const smMatch = sm.match(re);
            const emMatch = em.match(re);
            if (!smMatch || !emMatch) return { start: '', end: '' };

            const startYear = Number(smMatch[1]);
            const startMonthNum = Number(smMatch[2]);
            const endYear = Number(emMatch[1]);
            const endMonthNum = Number(emMatch[2]);
            if (!Number.isFinite(startYear) || !Number.isFinite(startMonthNum) || !Number.isFinite(endYear) || !Number.isFinite(endMonthNum)) {
                return { start: '', end: '' };
            }

            const rawStart = new Date(startYear, startMonthNum - 1, 1);
            const rawEnd = new Date(endYear, endMonthNum, 0);
            const start = rawStart <= rawEnd ? rawStart : new Date(endYear, endMonthNum - 1, 1);
            const end = rawStart <= rawEnd ? rawEnd : new Date(startYear, startMonthNum, 0);

            return {
                start: toIsoDateLocal(start),
                end: toIsoDateLocal(end),
            };
        }

        function normalizeMonthOrder(startMonth, endMonth) {
            const sm = String(startMonth || '').trim();
            const em = String(endMonth || '').trim();
            if (!sm || !em) return { startMonth: sm, endMonth: em };
            if (sm <= em) return { startMonth: sm, endMonth: em };
            return { startMonth: em, endMonth: sm };
        }

        function clampMonthToCurrent(monthValue) {
            const v = String(monthValue || '').trim();
            if (!v) return '';
            const nowMonth = toMonthValue(new Date());
            return v > nowMonth ? nowMonth : v;
        }

        function setupDropdown(dropdownId, selectId, onChange) {
            const dropdown = document.getElementById(dropdownId);
            const select = document.getElementById(selectId);
            if (!dropdown || !select) return;

            const trigger = dropdown.querySelector('.cms-dropdown-trigger');
            const label = dropdown.querySelector('.cms-dropdown-label');
            const options = Array.from(dropdown.querySelectorAll('.cms-dropdown-option'));

            const syncFromValue = (value) => {
                let active = options.find((opt) => String(opt.dataset.value) === String(value));
                if (!active) active = options[0] || null;
                options.forEach((opt) => opt.classList.toggle('active', opt === active));
                if (label && active) label.textContent = active.textContent.trim();
            };

            const setValue = (value, emit = true) => {
                select.value = value;
                syncFromValue(select.value);
                if (emit && typeof onChange === 'function') onChange(select.value);
            };

            trigger?.addEventListener('click', (e) => {
                e.stopPropagation();
                const willOpen = !dropdown.classList.contains('open');
                document.querySelectorAll('.cms-dropdown.open').forEach((el) => el.classList.remove('open'));
                dropdown.classList.toggle('open', willOpen);
                trigger?.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            options.forEach((opt) => {
                opt.addEventListener('click', () => {
                    setValue(opt.dataset.value ?? '', true);
                    dropdown.classList.remove('open');
                    trigger?.setAttribute('aria-expanded', 'false');
                });
            });

            select.addEventListener('change', () => {
                syncFromValue(select.value);
                if (typeof onChange === 'function') onChange(select.value);
            });
            syncFromValue(select.value);
        }

        function init(config = {}) {
            const presetId = String(config.presetId || 'analyticsPreset');
            const startId = String(config.startId || 'analyticsStart');
            const endId = String(config.endId || 'analyticsEnd');
            const dropdownId = String(config.dropdownId || 'analyticsPresetDropdown');
            const defaultPreset = String(config.defaultPreset || '30');
            const customValue = String(config.customValue || 'CUSTOM');
            const customStartId = String(config.customStartId || `${presetId}_custom_start`);
            const customEndId = String(config.customEndId || `${presetId}_custom_end`);
            const customWrapId = String(config.customWrapId || `${presetId}_custom_wrap`);
            const onChange = typeof config.onChange === 'function' ? config.onChange : null;

            const presetSelect = document.getElementById(presetId);
            const startInput = document.getElementById(startId);
            const endInput = document.getElementById(endId);
            const customStartInput = document.getElementById(customStartId);
            const customEndInput = document.getElementById(customEndId);
            const customWrap = document.getElementById(customWrapId);
            if (!presetSelect) return;
            const hasRangeInputs = Boolean(startInput && endInput);
            const hasCustomInputs = Boolean(customStartInput && customEndInput && customWrap);
            const selectorRoot = presetSelect.closest('.date-range-selector');

            const applyPreset = (value, emit = true) => {
                const effective = String(value || defaultPreset);
                const usingCustom = hasCustomInputs && effective === customValue;
                let range = { start: '', end: '' };

                if (usingCustom) {
                    if (!customStartInput.value || !customEndInput.value) {
                        const now = new Date();
                        const startMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        customStartInput.value = customStartInput.value || toMonthValue(startMonth);
                        customEndInput.value = customEndInput.value || toMonthValue(now);
                    }
                    const ordered = normalizeMonthOrder(customStartInput.value, customEndInput.value);
                    if (ordered.startMonth && ordered.endMonth) {
                        let startMonth = clampMonthToCurrent(ordered.startMonth);
                        let endMonth = clampMonthToCurrent(ordered.endMonth);
                        const orderedAfterClamp = normalizeMonthOrder(startMonth, endMonth);
                        startMonth = orderedAfterClamp.startMonth;
                        endMonth = orderedAfterClamp.endMonth;
                        customStartInput.value = startMonth;
                        customEndInput.value = endMonth;
                    }
                    range = computeMonthRange(customStartInput.value, customEndInput.value);
                } else {
                    range = computeRange(effective);
                }

                if (customWrap) {
                    customWrap.hidden = !usingCustom;
                }
                if (selectorRoot) {
                    selectorRoot.classList.toggle('has-custom-open', usingCustom);
                }

                if (hasRangeInputs) {
                    startInput.value = range.start;
                    endInput.value = range.end;
                }
                if (emit && onChange) onChange({
                    preset: effective,
                    start: range.start,
                    end: range.end,
                });
            };

            if (!presetSelect.value) {
                presetSelect.value = defaultPreset;
            }

            setupDropdown(dropdownId, presetId, (value) => applyPreset(value, true));
            if (hasCustomInputs) {
                const nowMonth = toMonthValue(new Date());
                customStartInput.setAttribute('max', nowMonth);
                customEndInput.setAttribute('max', nowMonth);
                customStartInput.setAttribute('min', '1970-01');
                customEndInput.setAttribute('min', '1970-01');

                const handleCustomInput = () => {
                    if (String(presetSelect.value || '') === customValue) {
                        applyPreset(customValue, true);
                    }
                };
                customStartInput.addEventListener('change', handleCustomInput);
                customEndInput.addEventListener('change', handleCustomInput);
            }
            applyPreset(presetSelect.value, false);
            if (onChange) {
                onChange({
                    preset: String(presetSelect.value || defaultPreset),
                    start: hasRangeInputs ? startInput.value : '',
                    end: hasRangeInputs ? endInput.value : '',
                });
            }
        }

        window.CmsDateRange = { init, computeRange };
    })();
    </script>
@endonce
