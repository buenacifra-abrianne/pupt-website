@php
    $pageData = is_array($pageData ?? null) ? $pageData : [];
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $hero = is_array($pageData['hero'] ?? null) ? $pageData['hero'] : [];
    $info = is_array($pageData['info'] ?? null) ? $pageData['info'] : [];
    $calendar = is_array($pageData['calendar'] ?? null) ? $pageData['calendar'] : [];
    $calendarUrl = 'https://www.pup.edu.ph/about/calendar';
    $infoRows = array_values(is_array($info['items'] ?? null) ? $info['items'] : []);
@endphp

<div class="academic-shell page-shell">
    <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <span>&gt;</span>
        <a href="{{ route('public.academics') }}" @if($cmsPreview) data-academics-preview-nav="overview" @endif>Academics</a>
        <span>&gt;</span>
        <strong>University Calendar</strong>
    </nav>
</div>

<section
    class="iapply-hero uc-hero-b{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-hero"
        data-cms-section-label="University Calendar Hero"
    @endif
>
    <div data-cms-boundary class="cms-preview-boundary-full">
        <div class="uc-hero-b-top">
            <p class="iapply-hero-tag">{{ $hero['tag'] ?? '' }}</p>

            <h1>{{ $hero['title'] ?? '' }}</h1>
            <p class="uc-hero-b-desc">{{ $hero['body'] ?? '' }}</p>
        </div>
    </div>
</section>

@if(!empty($info))
    @php
        $ayTitle = '';
        $ayStartDate = trim((string) ($info['ay_start_date'] ?? '2024-08-01'));
        if ($ayStartDate !== '') {
            $startMonth = (int) date('n', strtotime($ayStartDate));
            $currentMonth = (int) date('n');
            $currentYear = (int) date('Y');
            
            if ($currentMonth >= $startMonth) {
                $ayTitle = 'Academic Year ' . $currentYear . '&ndash;' . ($currentYear + 1);
            } else {
                $ayTitle = 'Academic Year ' . ($currentYear - 1) . '&ndash;' . $currentYear;
            }
        }
        if (!function_exists('formatAcademicsSemesterDateRange')) {
            function formatAcademicsSemesterDateRange($start, $end) {
                if (!$start && !$end) return '';
                if ($start && !$end) return date('F Y', strtotime($start));
                if (!$start && $end) return date('F Y', strtotime($end));
                
                $startTime = strtotime($start);
                $endTime = strtotime($end);
                
                $startMonth = date('F', $startTime);
                $endMonth = date('F', $endTime);
                $startYear = date('Y', $startTime);
                $endYear = date('Y', $endTime);
                
                if ($startYear === $endYear) {
                    if ($startMonth === $endMonth) {
                        return $startMonth . ' ' . $startYear;
                    }
                    return $startMonth . ' &ndash; ' . $endMonth . ' ' . $startYear;
                }
                
                return $startMonth . ' ' . $startYear . ' &ndash; ' . $endMonth . ' ' . $endYear;
            }
        }
        
        $sem1 = formatAcademicsSemesterDateRange($info['sem1_start'] ?? '', $info['sem1_end'] ?? '');
        $sem2 = formatAcademicsSemesterDateRange($info['sem2_start'] ?? '', $info['sem2_end'] ?? '');
        $summer = formatAcademicsSemesterDateRange($info['summer_start'] ?? '', $info['summer_end'] ?? '');
        
        // Provide defaults if totally empty
        if ($sem1 === '') $sem1 = 'August &ndash; December 2024';
        if ($sem2 === '') $sem2 = 'January &ndash; May 2025';
        if ($summer === '') $summer = 'June &ndash; July 2025';
    @endphp
    <section
        class="iapply-schedule-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
        @if($cmsPreview)
            data-cms-section="university-calendar-info"
            data-cms-section-label="University Calendar Info"
        @endif
    >
        <div data-cms-boundary class="cms-preview-boundary-full">
            <div class="iapply-schedule-inner">
                @if($ayTitle !== '')
                    <div class="iapply-schedule-head reveal">
                        <h2>{!! $ayTitle !!}</h2>
                    </div>
                @endif
                <div class="uc-schedule-container reveal delay-100">
                    @if($sem1 !== '')
                        <article class="uc-schedule-cell">
                            <span class="iapply-schedule-box-label">1ST SEMESTER</span>
                            <span class="iapply-schedule-box-value">{{ $sem1 }}</span>
                        </article>
                    @endif
                    
                    @if($sem2 !== '')
                        <article class="uc-schedule-cell">
                            <span class="iapply-schedule-box-label">2ND SEMESTER</span>
                            <span class="iapply-schedule-box-value">{{ $sem2 }}</span>
                        </article>
                    @endif
                    
                    @if($summer !== '')
                        <article class="uc-schedule-cell">
                            <span class="iapply-schedule-box-label">SUMMER</span>
                            <span class="iapply-schedule-box-value">{{ $summer }}</span>
                        </article>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif

<section
    class="contents-strip dp-programs-strip{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
    @if($cmsPreview)
        data-cms-section="university-calendar-calendar"
        data-cms-section-label="University Calendar Content"
    @endif
>
    <div data-cms-boundary class="cms-preview-boundary-full">
        <div class="contents-strip-inner">
            <div class="contents-strip-head reveal">
                <span class="section-tag">{{ $calendar['tag'] ?? '' }}</span>
                <h2>{{ $calendar['title'] ?? '' }}</h2>
            </div>

            <div class="uc-calendar-frame reveal delay-100">
                <div class="uc-calendar-official-card">
                    <div class="uc-calendar-official-head">
                        <img
                            src="{{ asset('assets/static_img/logo.png') }}"
                            alt="PUP Seal"
                            class="uc-calendar-official-seal"
                        >
                        <div>
                            <p>Official Source</p>
                            <h3>Polytechnic University of the Philippines Calendar</h3>
                        </div>
                    </div>

                    <div class="uc-calendar-source-row">
                        <span>www.pup.edu.ph/about/calendar</span>
                    </div>

                    @if(trim((string) ($calendar['note'] ?? '')) !== '')
                        <div class="uc-calendar-note academic-rich-copy">
                            {!! \App\Support\RichText::sanitize($calendar['note'] ?? '') !!}
                        </div>
                    @endif

                    @if(!empty($calendar['actions'] ?? []))
                        <div class="uc-calendar-actions reveal delay-200">
                            @foreach(($calendar['actions'] ?? []) as $action)
                                @php
                                    $actionLabel = trim((string) ($action['label'] ?? ''));
                                    $actionHref = trim((string) ($action['href'] ?? ''));
                                @endphp
                                @if($actionHref !== '')
                                    <a
                                        href="{{ $actionHref }}"
                                        class="apply-now-btn"
                                        @unless($cmsPreview) target="_blank" rel="noopener" @endunless
                                    >
                                        {{ $actionLabel !== '' ? $actionLabel : 'Open Calendar' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
