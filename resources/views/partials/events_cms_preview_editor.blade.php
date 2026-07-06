@php
    $eventsDefaults = \App\Support\EventsCmsContent::defaults();
    $eventsEditorData = \App\Support\EventsCmsContent::fromInput($eventsEditorData ?? [], null);
    $pageEditor = $eventsEditorData['page'] ?? $eventsDefaults['page'];
    $cardsEditor = $eventsEditorData['cards'] ?? $eventsDefaults['cards'];
    $eventsToday = now()->toDateString();
    $cardsWithIndexes = collect($cardsEditor)
        ->filter(fn ($card) => is_array($card))
        ->map(fn ($card, $index) => array_merge($card, ['source_index' => $index]))
        ->values();
    $cardCollections = \App\Support\EventsCmsContent::displayCollections($cardsWithIndexes, $eventsToday);
    $activeCardsEditor = collect($cardCollections['active'] ?? []);
    $expiredCardsEditor = collect($cardCollections['expired'] ?? []);
    $categoryOptions = \App\Support\EventsCmsContent::categoryOptions();
    $formClass = $eventsEditorFormClass ?? 'cms-save-form';
    $submitRoute = $eventsEditorSubmitRoute;
    $submitMode = $eventsEditorSubmitMode ?? 'save';
    $requestId = (int) ($eventsEditorRequestId ?? 0);
    $status = strtolower((string) ($eventsEditorStatus ?? ''));
    $idPrefix = trim((string) ($eventsEditorIdPrefix ?? 'events-editor'));
    $submitLabel = static function (string $sectionLabel) use ($submitMode, $status): string {
        if ($submitMode === 'request') {
            return $status === 'pending'
                ? 'Update '.$sectionLabel.' Request'
                : 'Submit '.$sectionLabel.' for Approval';
        }

        return 'Save '.$sectionLabel;
    };
@endphp

<div class="events-cms-workspace">
    <div class="events-cms-preview-shell">
        <div class="events-cms-preview-head">
            <div>
                <span class="events-cms-eyebrow">Events CMS</span>
                <h3>Live website preview</h3>
                <p>Click the highlighted sections inside the preview to edit the contents of the Events page.</p>
            </div>
        </div>

        <div class="events-cms-preview-frame-shell">
            <div class="events-cms-preview-stage">
                <div class="events-cms-preview-canvas">
                    <iframe
                        title="Events page preview"
                        class="events-cms-preview-frame"
                        data-events-preview-frame
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="events-cms-modal" data-events-editor-modal hidden>
    <div class="events-cms-modal-backdrop" data-close-events-editor></div>

    <div class="events-cms-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}-modal-title">
        <button type="button" class="events-cms-modal-close" data-close-events-editor aria-label="Close editor">&times;</button>

        <div class="events-cms-modal-header">
            <span class="events-cms-side-kicker">Events Section</span>
            <h3 id="{{ $idPrefix }}-modal-title">Edit events section</h3>
            <p data-events-editor-description>Select a highlighted section from the preview to edit it.</p>
        </div>

        <div class="events-cms-modal-panels">
            <section class="events-cms-editor-panel" data-events-editor-panel="page" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}">
                    @csrf
                    <input type="hidden" name="tab_key" value="events">
                    <input type="hidden" name="section_key" value="page">
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="events-cms-form-grid">
                        <div class="form-group">
                            <label>Eyebrow</label>
                            <input type="text" name="events[page][eyebrow]" maxlength="120" value="{{ $pageEditor['eyebrow'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Page Title</label>
                            <input type="text" name="events[page][title]" maxlength="255" value="{{ $pageEditor['title'] ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        @include('partials.rich_text_editor', [
                            'name' => 'events[page][description]',
                            'value' => $pageEditor['description'] ?? '',
                            'placeholder' => 'Write the intro text shown above the events listing...',
                        ])
                    </div>

                    <div class="events-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Page Header') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="events-cms-editor-panel" data-events-editor-panel="cards" hidden>
                <form class="{{ $formClass }}" method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" data-events-cards-form>
                    @csrf
                    <input type="hidden" name="tab_key" value="events">
                    <input type="hidden" name="section_key" value="cards">
                    <input type="hidden" name="events_cards_version" value="0" data-events-cards-version>
                    <input type="hidden" name="events_active_card_index" value="" data-events-active-card-index>
                    @if($requestId > 0)
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                    @endif

                    <div class="events-cms-card-stack" data-events-card-stack data-events-today="{{ $eventsToday }}">
                        <section class="events-cms-card-group" data-events-card-group="active">
                            <div class="events-cms-card-group-head">
                                <div>
                                    <h4>Active Events</h4>
                                    <p>These are visible on the public Events page.</p>
                                </div>
                                <span class="events-cms-card-count" data-events-card-group-count="active">{{ $activeCardsEditor->count() }}</span>
                            </div>

                            <div class="events-cms-card-empty" data-events-card-empty="active" @if($activeCardsEditor->isNotEmpty()) hidden @endif>
                                No active events right now.
                            </div>

                            <div class="events-cms-card-group-list" data-events-card-group-list="active">
                                @foreach($activeCardsEditor as $card)
                                    @php
                                        $index = $card['source_index'] ?? 0;
                                        $cardInputId = $idPrefix.'-events-card-image-'.$index;
                                        $cardPreview = \App\Support\NewsImage::url($card['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                    @endphp
                                    <article class="events-cms-card-editor" data-events-card-editor data-events-card-index="{{ $index }}">
                                        <input type="hidden" name="events[cards][{{ $index }}][image]" value="{{ $card['image'] ?? '' }}" data-events-image-field>
                                        <textarea name="events[cards][{{ $index }}][summary]" hidden>{{ $card['summary'] ?? '' }}</textarea>

                                        <div class="events-cms-card-editor-head">
                                            <div>
                                                <strong>{{ trim((string) ($card['title'] ?? '')) !== '' ? $card['title'] : 'Untitled event' }}</strong>
                                                <p>Active event details</p>
                                            </div>
                                            <button type="button" class="btn events-cms-delete-card" data-delete-events-card>
                                                Delete Event
                                            </button>
                                        </div>

                                        <div class="form-group">
                                            <label>Event Image</label>
                                            <div class="events-cms-image-dropzone-shell">
                                                <div class="events-cms-image-dropzone" data-events-dropzone-for="{{ $cardInputId }}" role="button" tabindex="0" aria-label="Upload event image">
                                                    <span class="events-cms-image-dropzone-preview-column">
                                                        <span class="events-cms-image-dropzone-media">
                                                            <img
                                                                src="{{ $cardPreview }}"
                                                                alt="{{ trim((string) ($card['title'] ?? '')) !== '' ? $card['title'] : 'Event image preview' }}"
                                                                class="events-cms-image-dropzone-preview"
                                                                data-events-preview-for="{{ $cardInputId }}"
                                                                data-events-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                            >
                                                            <button type="button" class="events-cms-image-dropzone-edit" data-events-edit-image-for="{{ $cardInputId }}" aria-label="Edit image" title="Edit image">
                                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                            </button>
                                                    <button type="button" class="events-cms-image-dropzone-remove" data-events-clear-image-for="{{ $cardInputId }}" aria-label="Delete image" title="Delete image">
                                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                            </button>
                                                        </span>
                                                    </span>
                                                    <span class="events-cms-image-dropzone-upload">
                                                        <span class="events-cms-image-dropzone-icon">
                                                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="events-cms-image-dropzone-upload-title">Upload event image</span>
                                                        <span class="events-cms-image-dropzone-upload-copy">Preview supports saved local and S3 images.</span>
                                                        <span class="events-cms-image-dropzone-upload-button">Select image</span>
                                                        <span class="events-cms-image-dropzone-file" data-events-file-name-for="{{ $cardInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <input
                                                id="{{ $cardInputId }}"
                                                class="events-cms-image-dropzone-input"
                                                type="file"
                                                name="events[cards][{{ $index }}][image_file]"
                                                accept="image/*"
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label>Event Title</label>
                                            <input type="text" name="events[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Event Head</label>
                                            <input type="text" name="events[cards][{{ $index }}][event_head]" maxlength="255" value="{{ $card['event_head'] ?? '' }}">
                                        </div>

                                        <div class="events-cms-form-grid">
                                            <div class="form-group">
                                                <label>Category</label>
                                                <select name="events[cards][{{ $index }}][category]">
                                                    @foreach($categoryOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected(($card['category'] ?? 'events') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Location</label>
                                                <input type="text" name="events[cards][{{ $index }}][location]" maxlength="255" value="{{ $card['location'] ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Event Date</label>
                                                <input type="date" name="events[cards][{{ $index }}][event_date]" value="{{ $card['event_date'] ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Event Time</label>
                                                <div class="events-cms-time-range-field">
                                                    <input type="time" name="events[cards][{{ $index }}][start_time]" value="{{ $card['start_time'] ?? '' }}" aria-label="Start time">
                                                    <span class="events-cms-time-range-separator" aria-hidden="true">to</span>
                                                    <input type="time" name="events[cards][{{ $index }}][end_time]" value="{{ $card['end_time'] ?? '' }}" aria-label="End time">
                                                </div>
                                            </div>
                                        </div>

                                        <label class="events-cms-feature-check" data-events-feature-check>
                                            <span class="events-cms-feature-switch">
                                                <input class="events-cms-feature-toggle" type="checkbox" name="events[cards][{{ $index }}][featured]" value="1" @checked(!empty($card['featured']))>
                                                <span class="events-cms-feature-slider" aria-hidden="true"></span>
                                            </span>
                                            <span class="events-cms-feature-copy">
                                                <strong>Featured Event</strong>
                                                <small>Pin this card to the highlighted event section.</small>
                                                <small class="events-cms-feature-note" data-events-feature-note hidden>A featured event already exists. Only one featured event is allowed at a time.</small>
                                            </span>
                                        </label>

                                        <div class="form-group">
                                            <label>Event Details</label>
                                            @include('partials.rich_text_editor', [
                                                'name' => 'events[cards]['.$index.'][content]',
                                                'value' => $card['content'] ?? '',
                                                'placeholder' => 'Write the full event details shown in the public modal...',
                                            ])
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <section class="events-cms-card-group events-cms-card-group-expired" data-events-card-group="expired">
                            <div class="events-cms-card-group-head">
                                <div>
                                    <h4>Expired Events</h4>
                                    <p>These stay saved in CMS but are hidden from the public Events page.</p>
                                </div>
                                <span class="events-cms-card-count events-cms-card-count-expired" data-events-card-group-count="expired">{{ $expiredCardsEditor->count() }}</span>
                            </div>

                            <div class="events-cms-card-empty" data-events-card-empty="expired" @if($expiredCardsEditor->isNotEmpty()) hidden @endif>
                                No expired events to review.
                            </div>

                            <div class="events-cms-card-group-list" data-events-card-group-list="expired">
                                @foreach($expiredCardsEditor as $card)
                                    @php
                                        $index = $card['source_index'] ?? 0;
                                        $cardInputId = $idPrefix.'-events-card-image-'.$index;
                                        $cardPreview = \App\Support\NewsImage::url($card['image'] ?? null, 'assets/static_img/pupillar.jpeg');
                                    @endphp
                                    <article class="events-cms-card-editor" data-events-card-editor data-events-card-index="{{ $index }}">
                                        <input type="hidden" name="events[cards][{{ $index }}][image]" value="{{ $card['image'] ?? '' }}" data-events-image-field>
                                        <textarea name="events[cards][{{ $index }}][summary]" hidden>{{ $card['summary'] ?? '' }}</textarea>

                                        <div class="events-cms-card-editor-head">
                                            <div>
                                                <strong>{{ trim((string) ($card['title'] ?? '')) !== '' ? $card['title'] : 'Untitled event' }}</strong>
                                                <p>Expired event details</p>
                                            </div>
                                            <button type="button" class="btn events-cms-delete-card" data-delete-events-card>
                                                Delete Event
                                            </button>
                                        </div>

                                        <div class="form-group">
                                            <label>Event Image</label>
                                            <div class="events-cms-image-dropzone-shell">
                                                <div class="events-cms-image-dropzone" data-events-dropzone-for="{{ $cardInputId }}" role="button" tabindex="0" aria-label="Upload event image">
                                                    <span class="events-cms-image-dropzone-preview-column">
                                                        <span class="events-cms-image-dropzone-media">
                                                            <img
                                                                src="{{ $cardPreview }}"
                                                                alt="{{ trim((string) ($card['title'] ?? '')) !== '' ? $card['title'] : 'Event image preview' }}"
                                                                class="events-cms-image-dropzone-preview"
                                                                data-events-preview-for="{{ $cardInputId }}"
                                                                data-events-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                            >
                                                            <button type="button" class="events-cms-image-dropzone-edit" data-events-edit-image-for="{{ $cardInputId }}" aria-label="Edit image" title="Edit image">
                                                                <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                            </button>
                                                    <button type="button" class="events-cms-image-dropzone-remove" data-events-clear-image-for="{{ $cardInputId }}" aria-label="Delete image" title="Delete image">
                                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                            </button>
                                                        </span>
                                                    </span>
                                                    <span class="events-cms-image-dropzone-upload">
                                                        <span class="events-cms-image-dropzone-icon">
                                                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="events-cms-image-dropzone-upload-title">Upload event image</span>
                                                        <span class="events-cms-image-dropzone-upload-copy">Preview supports saved local and S3 images.</span>
                                                        <span class="events-cms-image-dropzone-upload-button">Select image</span>
                                                        <span class="events-cms-image-dropzone-file" data-events-file-name-for="{{ $cardInputId }}" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <input
                                                id="{{ $cardInputId }}"
                                                class="events-cms-image-dropzone-input"
                                                type="file"
                                                name="events[cards][{{ $index }}][image_file]"
                                                accept="image/*"
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label>Event Title</label>
                                            <input type="text" name="events[cards][{{ $index }}][title]" maxlength="255" value="{{ $card['title'] ?? '' }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Event Head</label>
                                            <input type="text" name="events[cards][{{ $index }}][event_head]" maxlength="255" value="{{ $card['event_head'] ?? '' }}">
                                        </div>

                                        <div class="events-cms-form-grid">
                                            <div class="form-group">
                                                <label>Category</label>
                                                <select name="events[cards][{{ $index }}][category]">
                                                    @foreach($categoryOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected(($card['category'] ?? 'events') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Location</label>
                                                <input type="text" name="events[cards][{{ $index }}][location]" maxlength="255" value="{{ $card['location'] ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Event Date</label>
                                                <input type="date" name="events[cards][{{ $index }}][event_date]" value="{{ $card['event_date'] ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Event Time</label>
                                                <div class="events-cms-time-range-field">
                                                    <input type="time" name="events[cards][{{ $index }}][start_time]" value="{{ $card['start_time'] ?? '' }}" aria-label="Start time">
                                                    <span class="events-cms-time-range-separator" aria-hidden="true">to</span>
                                                    <input type="time" name="events[cards][{{ $index }}][end_time]" value="{{ $card['end_time'] ?? '' }}" aria-label="End time">
                                                </div>
                                            </div>
                                        </div>

                                        <label class="events-cms-feature-check" data-events-feature-check>
                                            <span class="events-cms-feature-switch">
                                                <input class="events-cms-feature-toggle" type="checkbox" name="events[cards][{{ $index }}][featured]" value="1" @checked(!empty($card['featured']))>
                                                <span class="events-cms-feature-slider" aria-hidden="true"></span>
                                            </span>
                                            <span class="events-cms-feature-copy">
                                                <strong>Featured Event</strong>
                                                <small>Pin this card to the highlighted event section.</small>
                                                <small class="events-cms-feature-note" data-events-feature-note hidden>A featured event already exists. Only one featured event is allowed at a time.</small>
                                            </span>
                                        </label>

                                        <div class="form-group">
                                            <label>Event Details</label>
                                            @include('partials.rich_text_editor', [
                                                'name' => 'events[cards]['.$index.'][content]',
                                                'value' => $card['content'] ?? '',
                                                'placeholder' => 'Write the full event details shown in the public modal...',
                                            ])
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <template data-events-card-template>
                        <article class="events-cms-card-editor" data-events-card-editor data-events-card-index="__INDEX__" data-events-new-card="1">
                            <input type="hidden" name="events[cards][__INDEX__][image]" value="" data-events-image-field>
                            <textarea name="events[cards][__INDEX__][summary]" hidden></textarea>

                            <div class="events-cms-card-editor-head">
                                <div>
                                    <strong>New event</strong>
                                    <p>Add the core event details.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Event Image</label>
                                <div class="events-cms-image-dropzone-shell">
                                    <div class="events-cms-image-dropzone" data-events-dropzone-for="{{ $idPrefix }}-events-card-image-__INDEX__" role="button" tabindex="0" aria-label="Upload event image">
                                        <span class="events-cms-image-dropzone-preview-column">
                                            <span class="events-cms-image-dropzone-media">
                                                <img
                                                    src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                    alt="Event image preview"
                                                    class="events-cms-image-dropzone-preview"
                                                    data-events-preview-for="{{ $idPrefix }}-events-card-image-__INDEX__"
                                                    data-events-default-src="{{ asset('assets/static_img/pupillar.jpeg') }}"
                                                >
                                                <button type="button" class="events-cms-image-dropzone-edit" data-events-edit-image-for="{{ $idPrefix }}-events-card-image-__INDEX__" aria-label="Edit image" title="Edit image">
                                                    <i class="fas fa-crop-alt" aria-hidden="true"></i>
                                                </button>
                                                    <button type="button" class="events-cms-image-dropzone-remove" data-events-clear-image-for="{{ $idPrefix }}-events-card-image-__INDEX__" aria-label="Delete image" title="Delete image">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                        </span>
                                        <span class="events-cms-image-dropzone-upload">
                                            <span class="events-cms-image-dropzone-icon">
                                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="events-cms-image-dropzone-upload-title">Upload event image</span>
                                            <span class="events-cms-image-dropzone-upload-copy">Preview supports saved local and S3 images.</span>
                                            <span class="events-cms-image-dropzone-upload-button">Select image</span>
                                            <span class="events-cms-image-dropzone-file" data-events-file-name-for="{{ $idPrefix }}-events-card-image-__INDEX__" data-empty-text="Drop image here or click to replace">Drop image here or click to replace</span>
                                        </span>
                                    </div>
                                </div>
                                <input
                                    id="{{ $idPrefix }}-events-card-image-__INDEX__"
                                    class="events-cms-image-dropzone-input"
                                    type="file"
                                    name="events[cards][__INDEX__][image_file]"
                                    accept="image/*"
                                >
                            </div>

                            <div class="form-group">
                                <label>Event Title</label>
                                <input type="text" name="events[cards][__INDEX__][title]" maxlength="255" value="">
                            </div>

                            <div class="form-group">
                                <label>Event Head</label>
                                <input type="text" name="events[cards][__INDEX__][event_head]" maxlength="255" value="">
                            </div>

                            <div class="events-cms-form-grid">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="events[cards][__INDEX__][category]">
                                        @foreach($categoryOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($value === 'events')>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="events[cards][__INDEX__][location]" maxlength="255" value="">
                                </div>
                                <div class="form-group">
                                    <label>Event Date</label>
                                    <input type="date" name="events[cards][__INDEX__][event_date]" value="">
                                </div>
                                <div class="form-group">
                                    <label>Event Time</label>
                                    <div class="events-cms-time-range-field">
                                        <input type="time" name="events[cards][__INDEX__][start_time]" value="" aria-label="Start time">
                                        <span class="events-cms-time-range-separator" aria-hidden="true">to</span>
                                        <input type="time" name="events[cards][__INDEX__][end_time]" value="" aria-label="End time">
                                    </div>
                                </div>
                            </div>

                            <label class="events-cms-feature-check" data-events-feature-check>
                                <span class="events-cms-feature-switch">
                                    <input class="events-cms-feature-toggle" type="checkbox" name="events[cards][__INDEX__][featured]" value="1">
                                    <span class="events-cms-feature-slider" aria-hidden="true"></span>
                                </span>
                                <span class="events-cms-feature-copy">
                                    <strong>Featured Event</strong>
                                    <small>Pin this card to the highlighted event section.</small>
                                    <small class="events-cms-feature-note" data-events-feature-note hidden>A featured event already exists. Only one featured event is allowed at a time.</small>
                                </span>
                            </label>

                            <div class="form-group">
                                <label>Event Details</label>
                                @include('partials.rich_text_editor', [
                                    'name' => 'events[cards][__INDEX__][content]',
                                    'value' => '',
                                    'placeholder' => 'Write the full event details shown in the public modal...',
                                ])
                            </div>
                        </article>
                    </template>

                    <div class="events-cms-modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas {{ $submitMode === 'request' ? 'fa-paper-plane' : 'fa-save' }}"></i>
                            {{ $submitLabel('Event Listings') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script type="application/json" data-events-preview-json>
{!! json_encode($eventsPreviewHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

@include('partials.rich_text_editor_assets')

<style>
    .events-cms-workspace {
        --events-preview-width: 1520px;
        --events-preview-height: 1800px;
        --events-preview-scale: 1;
        --events-preview-scaled-width: calc(var(--events-preview-width) * var(--events-preview-scale));
        --events-preview-scaled-height: calc(var(--events-preview-height) * var(--events-preview-scale));
        display: block;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .events-cms-preview-shell {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .events-cms-preview-head {
        display: none;
    }

    .events-cms-preview-head h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.1rem;
    }

    .events-cms-preview-head p {
        margin: 8px 0 0;
        color: #6f625c;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .events-cms-preview-frame-shell {
        width: 100%;
        padding: 0;
        background: transparent;
        overflow: hidden;
    }

    .events-cms-preview-stage {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        width: 100%;
        overflow: hidden;
        padding: 0;
        box-sizing: border-box;
    }

    .events-cms-preview-canvas {
        position: relative;
        flex: 1 1 auto;
        width: var(--events-preview-scaled-width);
        max-width: 100%;
        height: var(--events-preview-scaled-height);
        min-height: 0;
        overflow: hidden;
        border: 1px solid #d8cbc4;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(92, 12, 6, 0.08);
    }

    .events-cms-preview-frame {
        display: block;
        width: var(--events-preview-width);
        min-width: var(--events-preview-width);
        height: var(--events-preview-height);
        min-height: 0;
        border: 0;
        background: #fff;
        transform: scale(var(--events-preview-scale));
        transform-origin: top left;
    }

    .events-cms-modal[hidden] {
        display: none;
    }

    .events-cms-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .events-cms-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(25, 16, 12, 0.54);
        backdrop-filter: blur(6px);
    }

    .events-cms-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(1080px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        margin: 0;
        overflow: auto;
        border-radius: 24px;
        background: #fffdfc;
        box-shadow: 0 28px 80px rgba(25, 16, 12, 0.28);
    }

    .events-cms-modal-close {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 46px;
        height: 46px;
        border: none;
        border-radius: 14px;
        background: #f7ede8;
        color: #5c0000;
        font-size: 1.6rem;
        cursor: pointer;
    }

    .events-cms-modal-header {
        padding: 24px 24px 12px;
        border-bottom: 1px solid #f1e9e4;
    }

    .events-cms-modal-header h3 {
        margin: 0;
        color: #5c0000;
        font-size: 1.4rem;
    }

    .events-cms-modal-header p {
        margin: 8px 0 0;
        color: #6f625c;
        line-height: 1.55;
    }

    .events-cms-eyebrow,
    .events-cms-side-kicker {
        display: inline-flex;
        margin-bottom: 8px;
        color: #9f6b00;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .events-cms-modal-panels {
        padding: 22px 24px 24px;
        background: linear-gradient(180deg, rgba(255, 251, 247, 0.92) 0%, #fffdfc 100%);
    }

    .events-cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        align-items: end;
    }

    .events-cms-form-grid .form-group {
        min-width: 0;
        margin: 0;
    }

    .events-cms-card-stack {
        display: grid;
        gap: 16px;
    }

    .events-cms-card-toolbar,
    .events-cms-card-group-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .events-cms-card-toolbar {
        margin-bottom: 18px;
        padding: 18px 20px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(127, 17, 19, 0.04) 0%, rgba(242, 201, 76, 0.08) 100%);
    }

    .events-cms-card-toolbar h4,
    .events-cms-card-group-head h4 {
        margin: 0;
        color: #5c0000;
        font-size: 1rem;
    }

    .events-cms-card-toolbar p,
    .events-cms-card-group-head p,
    .events-cms-card-editor-head p {
        margin: 6px 0 0;
        color: #7c6660;
        font-size: 0.88rem;
        line-height: 1.5;
    }

    .events-cms-add-card {
        flex-shrink: 0;
        min-height: 42px;
        padding: 0 18px;
        border-color: rgba(127, 17, 19, 0.14);
        color: #5c0000;
        background: rgba(255, 250, 244, 0.95);
    }

    .events-cms-card-group {
        padding: 18px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 22px;
        background: rgba(255, 253, 252, 0.92);
    }

    .events-cms-card-group-expired {
        border-color: rgba(92, 12, 6, 0.12);
        background: linear-gradient(180deg, rgba(250, 247, 244, 0.96) 0%, rgba(244, 238, 234, 0.96) 100%);
    }

    .events-cms-card-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(127, 17, 19, 0.1);
        color: #7f1113;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .events-cms-card-count-expired {
        background: rgba(92, 12, 6, 0.12);
        color: #5c0000;
    }

    .events-cms-card-empty {
        margin-top: 12px;
        padding: 14px 16px;
        border: 1px dashed rgba(127, 17, 19, 0.14);
        border-radius: 16px;
        background: rgba(255, 250, 244, 0.7);
        color: #7c6660;
        font-size: 0.9rem;
    }

    .events-cms-card-group-list {
        display: grid;
        gap: 16px;
        margin-top: 14px;
    }

    .events-cms-card-editor {
        padding: 18px;
        border: 1px solid rgba(127, 17, 19, 0.08);
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf6 100%);
        box-shadow: 0 8px 22px rgba(92, 12, 6, 0.05);
    }

    .events-cms-card-editor[hidden] {
        display: none !important;
    }

    .events-cms-card-editor.is-selected {
        border-color: rgba(127, 17, 19, 0.28);
        box-shadow: 0 18px 34px rgba(92, 12, 6, 0.1);
    }

    .events-cms-card-editor-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 10px;
    }

    .events-cms-card-editor-head strong {
        color: #5c0000;
        font-size: 1rem;
        line-height: 1.2;
    }

    .events-cms-delete-card {
        min-height: 38px;
        padding: 0 14px;
        border-color: rgba(127, 17, 19, 0.18);
        color: #7f1113;
        background: rgba(255, 250, 244, 0.92);
        box-shadow: none;
    }

    .events-cms-delete-card:hover {
        border-color: #7f1113;
        background: #7f1113;
        color: #fffaf4;
    }

    .events-cms-feature-check {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 16px 0 10px;
        padding: 14px 16px;
        border: 1px solid rgba(127, 17, 19, 0.1);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(127, 17, 19, 0.04) 0%, rgba(242, 201, 76, 0.08) 100%);
        color: #5c0000;
        cursor: pointer;
        user-select: none;
    }

    .events-cms-feature-check.is-locked {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .events-cms-feature-switch {
        position: relative;
        display: block;
        width: 4em;
        height: 2em;
        flex-shrink: 0;
    }

    .events-cms-feature-toggle {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }

    .events-cms-feature-slider,
    .events-cms-feature-slider::after,
    .events-cms-feature-slider::before {
        box-sizing: border-box;
    }

    .events-cms-feature-slider {
        position: relative;
        display: block;
        width: 4em;
        height: 2em;
        padding: 2px;
        border: 1px solid #e8eae9;
        border-radius: 2em;
        background: #fbfbfb;
        outline: 0;
        transition: all 0.4s ease;
        cursor: pointer;
        user-select: none;
    }

    .events-cms-feature-slider::after {
        position: relative;
        left: 0;
        display: block;
        content: "";
        width: 50%;
        height: 100%;
        border-radius: 2em;
        background: #fbfbfb;
        transition:
            left 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275),
            padding 0.3s ease,
            margin 0.3s ease;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1), 0 4px 0 rgba(0, 0, 0, 0.08);
    }

    .events-cms-feature-toggle:focus-visible + .events-cms-feature-slider {
        box-shadow: 0 0 0 3px rgba(127, 17, 19, 0.18);
    }

    .events-cms-feature-toggle:hover + .events-cms-feature-slider::after {
        will-change: padding;
    }

    .events-cms-feature-toggle:active + .events-cms-feature-slider {
        box-shadow: inset 0 0 0 2em #e8eae9;
    }

    .events-cms-feature-toggle:active + .events-cms-feature-slider::after {
        padding-right: 0.8em;
    }

    .events-cms-feature-toggle:checked + .events-cms-feature-slider {
        border-color: rgba(127, 17, 19, 0.18);
        background: linear-gradient(135deg, #8f1117 0%, #b52127 100%);
    }

    .events-cms-feature-toggle:checked + .events-cms-feature-slider::after {
        left: 50%;
    }

    .events-cms-feature-toggle:checked:active + .events-cms-feature-slider {
        box-shadow: none;
    }

    .events-cms-feature-toggle:checked:active + .events-cms-feature-slider::after {
        margin-left: -0.8em;
    }

    .events-cms-feature-copy {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .events-cms-feature-copy strong {
        color: #5c0000;
        font-size: 0.94rem;
        line-height: 1.2;
    }

    .events-cms-feature-copy small {
        color: #7c6660;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .events-cms-feature-copy .events-cms-feature-note {
        color: #8f1117;
    }

    .events-cms-image-dropzone-shell {
        width: 100%;
        margin: 0 auto;
    }

    .events-cms-image-dropzone {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
        width: 100%;
        padding: 14px;
        border: 1px dashed #d4af37;
        border-radius: 24px;
        background: linear-gradient(180deg, #fffdf8 0%, #fff8ee 100%);
        cursor: pointer;
        align-items: stretch;
    }

    .events-cms-image-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .events-cms-image-dropzone-preview-column {
        display: flex;
        min-width: 0;
        min-height: 220px;
    }

    .events-cms-image-dropzone-media {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
    }

    .events-cms-image-dropzone-preview {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 220px;
        object-fit: cover;
        border-radius: 18px;
        background: #f1e7dd;
        box-shadow: inset 0 0 0 1px rgba(127, 17, 19, 0.08);
    }

    .events-cms-image-dropzone-label {
        display: none;
        color: #7f1113;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
    }

    .events-cms-image-dropzone-upload {
        display: grid;
        justify-items: center;
        align-content: center;
        gap: 12px;
        min-width: 0;
        padding: 20px 18px;
        border-radius: 18px;
        background: radial-gradient(circle at top, rgba(151, 26, 33, 0.98), rgba(96, 12, 18, 0.98));
        color: #f8f4ef;
        text-align: center;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
        min-height: 100%;
    }

    .events-cms-image-dropzone-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        border-radius: 999px;
        background: rgba(73, 8, 13, 0.42);
        color: #f2f0ed;
        font-size: 1.8rem;
    }

    .events-cms-image-dropzone-upload-title {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .events-cms-image-dropzone-upload-copy {
        display: block;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .events-cms-image-dropzone-upload-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 18px;
        border-radius: 999px;
        background: #fff8f1;
        color: #1b1714;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .events-cms-image-dropzone-file {
        display: block;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.8rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .events-cms-image-dropzone-input {
        display: none;
    }

    
    

    

    

    


    .events-cms-image-dropzone-edit {
        position: absolute;
        top: 60px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 40px;
        height: 40px;
        padding: 0;
        border: 1px solid rgba(26, 115, 232, 0.14);
        border-radius: 999px;
        background: rgba(255, 253, 250, 0.94);
        color: #1a73e8;
        font: inherit;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(26, 115, 232, 0.12);
        backdrop-filter: blur(6px);
        z-index: 10;
        transition: opacity 0.15s, background-color 0.15s, color 0.15s;
    }

    

    .events-cms-image-dropzone-edit:hover {
        background: #1a73e8;
        color: #ffffff;
    }

    

    .events-cms-image-dropzone-edit[hidden] {
        display: none !important;
    }

.events-cms-image-dropzone-remove {
        position: absolute;
        top: 12px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        padding: 0;
        border: 1px solid rgba(127, 17, 19, 0.14);
        border-radius: 999px;
        background: rgba(255, 253, 250, 0.94);
        color: #7f1113;
        font: inherit;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(92, 12, 6, 0.12);
        backdrop-filter: blur(6px);
    }

    .events-cms-image-dropzone-remove:hover {
        background: #7f1113;
        color: #fff8f1;
    }

    @media (max-width: 460px) {
        .events-cms-image-dropzone {
            grid-template-columns: 1fr;
        }

        .events-cms-image-dropzone-upload {
            min-height: 280px;
        }
    }

    
    @media (max-width: 640px) {
        .events-cms-image-dropzone-edit {
            top: 54px;
            right: 12px;
            padding: 0;
            height: 36px;
        }

        
    }

@media (max-width: 640px) {
        
    

    

    

    

.events-cms-image-dropzone-remove {
            top: 12px;
            right: 12px;
        }
    }

    .events-cms-image-dropzone-remove[hidden] {
        display: none;
    }

    .events-cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .events-cms-time-range-field {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        width: 100%;
    }

    .events-cms-time-range-separator {
        color: #7f1113;
        font-size: 0.88rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .events-cms-modal.is-card-focus .events-cms-modal-header {
        display: none;
    }

    .events-cms-modal.is-card-focus {
        align-items: center !important;
    }

    .events-cms-modal.is-card-focus .events-cms-modal-dialog {
        width: min(980px, calc(100vw - 24px));
        max-width: min(980px, calc(100vw - 24px));
        border-radius: 30px;
        background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
        box-shadow: 0 30px 70px rgba(45, 8, 5, 0.2);
    }

    .events-cms-modal.is-card-focus .events-cms-modal-panels {
        padding: 18px;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf6 0%, #fffdfc 100%);
    }

    .events-cms-editor-panel.is-card-focus form {
        max-width: 900px;
        margin: 0 auto;
    }

    .events-cms-editor-panel.is-card-focus .events-cms-card-stack,
    .events-cms-editor-panel.is-card-focus .events-cms-card-group-list {
        gap: 0;
    }

    .events-cms-editor-panel.is-card-focus .events-cms-card-group {
        padding: 0;
        border: 0;
        background: transparent;
    }

    .events-cms-editor-panel.is-card-focus .events-cms-card-group-head,
    .events-cms-editor-panel.is-card-focus .events-cms-card-empty {
        display: none;
    }

    .events-cms-editor-panel.is-card-focus .events-cms-card-editor.is-selected {
        padding: 22px;
        border: 1px solid rgba(127, 17, 19, 0.12);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 250, 245, 0.98) 100%);
        box-shadow:
            0 16px 34px rgba(92, 12, 6, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .events-cms-editor-panel.is-card-focus .events-cms-card-editor.is-selected .form-group + .form-group,
    .events-cms-editor-panel.is-card-focus .events-cms-card-editor.is-selected .events-cms-form-grid + .form-group,
    .events-cms-editor-panel.is-card-focus .events-cms-card-editor.is-selected .form-group + .events-cms-form-grid {
        margin-top: 14px;
    }

    .events-cms-modal.is-card-focus .events-cms-modal-close {
        top: 14px;
        right: 14px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(127, 17, 19, 0.08);
        font-size: 1.35rem;
    }

    .events-cms-upload-hint,
    .academics-cms-upload-hint {
        display: block;
        margin-top: 6px;
        color: #8a7a73;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .events-cms-workspace {
            --events-preview-width: 1440px;
            --events-preview-height: 1760px;
            --events-preview-scale: 0.58;
        }

        .events-cms-form-grid {
            grid-template-columns: 1fr;
        }

        .events-cms-preview-head,
        .events-cms-card-toolbar,
        .events-cms-card-group-head,
        .events-cms-card-editor-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .events-cms-modal-dialog {
            width: min(100vw - 20px, 1080px);
            max-height: calc(100vh - 20px);
            margin: 10px auto;
        }

        .events-cms-modal-header,
        .events-cms-modal-panels {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

<script>
    (() => {
        if (window.__eventsCmsPreviewEditorReady) {
            return;
        }

        const EVENTS_PREVIEW_MIN_LOADING_MS = 800;
        let eventsPreviewFitFrame = null;

        function syncEditorsInScope(scope) {
            if (typeof window.syncRichTextEditors === 'function') {
                window.syncRichTextEditors(scope);
            }
        }

        function fitEventsPreview(frame) {
            const workspace = frame.closest('.events-cms-workspace');
            const shell = frame.closest('.events-cms-preview-frame-shell');

            if (!workspace || !shell) {
                return;
            }

            const shellStyles = window.getComputedStyle(shell);
            const shellPaddingLeft = Number.parseFloat(shellStyles.paddingLeft) || 0;
            const shellPaddingRight = Number.parseFloat(shellStyles.paddingRight) || 0;
            const availableWidth = Math.max(320, shell.clientWidth - shellPaddingLeft - shellPaddingRight);
            const fixedPreviewWidth = 1520;
            const scale = Math.min(1, availableWidth / fixedPreviewWidth);

            workspace.style.setProperty('--events-preview-width', `${fixedPreviewWidth}px`);
            workspace.style.setProperty('--events-preview-scale', `${scale}`);
        }

        function setEventsPreviewLoading(frame, isLoading) {
            const canvas = frame?.closest('.events-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            if (frame.__eventsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__eventsPreviewLoadingTimeout);
                frame.__eventsPreviewLoadingTimeout = null;
            }

            if (isLoading) {
                frame.__eventsPreviewLoadingSession = (frame.__eventsPreviewLoadingSession || 0) + 1;
                frame.__eventsPreviewLoadingStartedAt = Date.now();
            }

            frame.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent(isLoading ? 'cms:preview-loading' : 'cms:preview-loaded', {
                detail: {
                    sessionId: frame.__eventsPreviewLoadingSession || 0,
                },
            }));
        }

        function finishEventsPreviewLoading(frame) {
            const canvas = frame?.closest('.events-cms-preview-canvas');

            if (!canvas) {
                return;
            }

            const activeSession = frame.__eventsPreviewLoadingSession || 0;
            const startedAt = frame.__eventsPreviewLoadingStartedAt || Date.now();
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, EVENTS_PREVIEW_MIN_LOADING_MS - elapsed);

            if (frame.__eventsPreviewLoadingTimeout) {
                window.clearTimeout(frame.__eventsPreviewLoadingTimeout);
            }

            frame.__eventsPreviewLoadingTimeout = window.setTimeout(() => {
                if ((frame.__eventsPreviewLoadingSession || 0) !== activeSession) {
                    return;
                }

                frame.setAttribute('aria-busy', 'false');
                window.dispatchEvent(new CustomEvent('cms:preview-loaded', {
                    detail: {
                        sessionId: activeSession,
                    },
                }));
                frame.__eventsPreviewLoadingTimeout = null;
            }, remaining);
        }

        function getEventsPreviewElementBottom(element) {
            return element.offsetTop + element.offsetHeight;
        }

        function isEventsPreviewMeasuredElement(element) {
            if (!(element instanceof HTMLElement)) {
                return false;
            }

            const styles = window.getComputedStyle(element);
            return styles.display !== 'none'
                && styles.visibility !== 'hidden'
                && styles.position !== 'fixed';
        }

        function measureEventsPreviewHeight(frame) {
            const doc = frame.contentDocument;
            if (!doc) {
                return 0;
            }

            const main = doc.querySelector('.main-content');
            const scope = main instanceof HTMLElement ? main : doc.body;

            if (!(scope instanceof HTMLElement)) {
                return 0;
            }

            const visibleElements = Array.from(scope.children)
                .filter((element) => isEventsPreviewMeasuredElement(element));

            const contentBottom = visibleElements.reduce((maxBottom, element) => {
                return Math.max(maxBottom, getEventsPreviewElementBottom(element));
            }, 0);

            return Math.max(
                1,
                Math.ceil(contentBottom),
                Math.ceil(scope.scrollHeight || 0),
                Math.ceil(doc.documentElement?.scrollHeight || 0),
                Math.ceil(doc.body?.scrollHeight || 0)
            );
        }

        function syncEventsPreviewHeight(frame, nextHeight) {
            const workspace = frame.closest('.events-cms-workspace');
            const height = Math.max(1, Number(nextHeight) || 0);

            if (!workspace || !height) {
                return;
            }

            workspace.style.setProperty('--events-preview-height', `${height}px`);
            frame.style.height = `${height}px`;
            fitEventsPreview(frame);
        }

        function scheduleEventsPreviewSync(frame) {
            if (!frame) {
                return;
            }

            if (frame.__eventsPreviewSyncFrame !== undefined && frame.__eventsPreviewSyncFrame !== null) {
                window.cancelAnimationFrame(frame.__eventsPreviewSyncFrame);
            }

            frame.__eventsPreviewSyncFrame = window.requestAnimationFrame(() => {
                const measuredHeight = measureEventsPreviewHeight(frame);

                if (measuredHeight > 0) {
                    syncEventsPreviewHeight(frame, measuredHeight);
                } else {
                    fitEventsPreview(frame);
                }

                frame.__eventsPreviewSyncFrame = null;
            });
        }

        function queueEventsPreviewSettledSync(frame) {
            scheduleEventsPreviewSync(frame);
            [80, 220, 480, 900].forEach((delay) => {
                window.setTimeout(() => scheduleEventsPreviewSync(frame), delay);
            });
            finishEventsPreviewLoading(frame);
        }

        function bindEventsPreviewDocument(frame) {
            const doc = frame.contentDocument;
            const win = frame.contentWindow;

            if (!doc) {
                return;
            }

            if (typeof frame.__eventsPreviewCleanup === 'function') {
                frame.__eventsPreviewCleanup();
            }

            const cleanups = [];
            const schedule = () => queueEventsPreviewSettledSync(frame);
            const main = doc.querySelector('.main-content');

            if (typeof window.bindCmsPreviewScrollBridge === 'function') {
                window.bindCmsPreviewScrollBridge(frame, cleanups);
            }

            const bindPreviewImages = () => {
                doc.querySelectorAll('img').forEach((image) => {
                    if (image.dataset.cmsPreviewHeightBound === '1') {
                        return;
                    }

                    image.dataset.cmsPreviewHeightBound = '1';

                    if (image.complete) {
                        return;
                    }

                    const handleImageSettled = () => schedule();
                    image.addEventListener('load', handleImageSettled, { once: true });
                    image.addEventListener('error', handleImageSettled, { once: true });
                });
            };

            bindPreviewImages();

            if (typeof ResizeObserver !== 'undefined') {
                const observer = new ResizeObserver(() => schedule());
                if (doc.documentElement) observer.observe(doc.documentElement);
                if (doc.body) observer.observe(doc.body);
                if (main) observer.observe(main);
                cleanups.push(() => observer.disconnect());
            }

            if (typeof MutationObserver !== 'undefined') {
                const observer = new MutationObserver(() => {
                    bindPreviewImages();
                    schedule();
                });

                observer.observe(doc.body || doc.documentElement, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'style', 'src'],
                });

                cleanups.push(() => observer.disconnect());
            }

            if (doc.fonts?.ready) {
                doc.fonts.ready.then(() => schedule()).catch(() => {});
            }

            if (win) {
                const handleResize = () => schedule();
                win.addEventListener('resize', handleResize);
                cleanups.push(() => win.removeEventListener('resize', handleResize));
            }

            frame.__eventsPreviewCleanup = () => {
                cleanups.forEach((cleanup) => cleanup());
            };
        }

        function fitAllEventsPreviews() {
            document.querySelectorAll('[data-events-preview-frame]').forEach((frame) => {
                scheduleEventsPreviewSync(frame);
            });
        }

        window.__eventsPreviewCache = window.__eventsPreviewCache || {};

        function loadEventsPreview(frame, options = {}) {
            const explicitSessionId = options.sessionId;
            const targetKey = 'overview';

            if (!frame) {
                return;
            }

            if (Number.isFinite(Number(explicitSessionId))) {
                frame.__eventsPreviewLoadingSession = Number(explicitSessionId) - 1;
            }

            setEventsPreviewLoading(frame, true);

            const applyHtml = (html) => {
                if (typeof window.applyCmsPreviewFrameContent === 'function') {
                    window.applyCmsPreviewFrameContent(frame, html);
                } else {
                    frame.srcdoc = html;
                }
            };

            if (window.__eventsPreviewCache[targetKey]) {
                applyHtml(window.__eventsPreviewCache[targetKey]);
                return;
            }

            const prefix = window.location.pathname.startsWith('/superadmin') ? '/superadmin' : (window.location.pathname.startsWith('/admin') ? '/admin' : '/staff');
            const previewUrl = `${prefix}/content/preview/events/${targetKey}`;

            fetch(previewUrl)
                .then(response => response.text())
                .then(previewHtml => {
                    window.__eventsPreviewCache[targetKey] = previewHtml;
                    applyHtml(previewHtml);
                })
                .catch(error => {
                    applyHtml('<!DOCTYPE html><html><body><p>Preview could not be loaded.</p></body></html>');
                });
        }

        function scheduleFitAllEventsPreviews() {
            if (eventsPreviewFitFrame !== null) {
                window.cancelAnimationFrame(eventsPreviewFitFrame);
            }

            eventsPreviewFitFrame = window.requestAnimationFrame(() => {
                fitAllEventsPreviews();
                window.setTimeout(fitAllEventsPreviews, 140);
                eventsPreviewFitFrame = null;
            });
        }

        function setActiveEventsCardEditor(targetIndex = null) {
            const cardsPanel = document.querySelector('[data-events-editor-panel="cards"]');
            const stack = cardsPanel?.querySelector('[data-events-card-stack]');
            const activeIndexField = cardsPanel?.querySelector('[data-events-active-card-index]');
            const editors = Array.from(cardsPanel?.querySelectorAll('[data-events-card-editor]') || []);

            if (!editors.length) {
                refreshEventsCardGroups(cardsPanel);
                return null;
            }

            const normalizedIndex = targetIndex === null || targetIndex === undefined || targetIndex === ''
                ? null
                : String(targetIndex);

            let activeEditor = null;

            editors.forEach((editor) => {
                const isMatch = normalizedIndex !== null && editor.getAttribute('data-events-card-index') === normalizedIndex;
                editor.hidden = normalizedIndex !== null && !isMatch;
                editor.classList.toggle('is-selected', isMatch);

                if (isMatch) {
                    activeEditor = editor;
                }
            });

            if (stack instanceof HTMLElement) {
                const editorGroup = activeEditor?.closest('[data-events-card-group]')?.getAttribute('data-events-card-group') || '';
                stack.dataset.eventsVisibleGroup = editorGroup !== '' ? editorGroup : 'active';
            }

            if (activeIndexField instanceof HTMLInputElement) {
                activeIndexField.value = activeEditor?.getAttribute('data-events-card-index') || '';
            }

            refreshEventsCardGroups(cardsPanel);
            syncEventsFeaturedToggles(cardsPanel);

            return activeEditor;
        }

        function getNextEventsCardIndex(stack) {
            const indexes = Array.from(stack.querySelectorAll('[data-events-card-editor]'))
                .map((editor) => Number(editor.getAttribute('data-events-card-index')))
                .filter((value) => Number.isFinite(value));

            if (!indexes.length) {
                return 0;
            }

            return Math.max(...indexes) + 1;
        }

        function markEventsCardsChanged(form) {
            const marker = form?.querySelector('[data-events-cards-version]');
            if (!marker) {
                return;
            }

            const currentValue = Number(marker.value || '0');
            marker.value = String(Number.isFinite(currentValue) ? currentValue + 1 : 1);
        }

        function shouldTrackEventsCardField(target) {
            if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
                return false;
            }

            const type = (target.type || '').toLowerCase();
            return type !== 'file'
                && type !== 'hidden'
                && type !== 'submit'
                && type !== 'button'
                && type !== 'reset';
        }

        function bindEventsCardsDirtyTracking(form) {
            if (!form || form.dataset.eventsDirtyTrackingBound === '1') {
                return;
            }

            form.dataset.eventsDirtyTrackingBound = '1';

            const markDirty = (event) => {
                if (!shouldTrackEventsCardField(event.target)) {
                    return;
                }

                markEventsCardsChanged(form);
            };

            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
        }

        function syncEventsFeaturedToggles(scope) {
            const form = scope?.matches?.('[data-events-cards-form]')
                ? scope
                : scope?.querySelector?.('[data-events-cards-form]') || document.querySelector('[data-events-cards-form]');

            if (!(form instanceof HTMLElement)) {
                return;
            }

            const toggles = Array.from(form.querySelectorAll('.events-cms-feature-toggle'));
            const checkedToggles = toggles.filter((toggle) => toggle.checked);
            const activeToggle = checkedToggles[0] || null;

            checkedToggles.slice(1).forEach((toggle) => {
                toggle.checked = false;
            });

            toggles.forEach((toggle) => {
                const wrapper = toggle.closest('[data-events-feature-check]');
                const note = wrapper?.querySelector('[data-events-feature-note]');
                const shouldLock = activeToggle !== null && activeToggle !== toggle;

                toggle.disabled = shouldLock;
                toggle.setAttribute('aria-disabled', shouldLock ? 'true' : 'false');
                wrapper?.classList.toggle('is-locked', shouldLock);

                if (note instanceof HTMLElement) {
                    note.hidden = !shouldLock;
                }
            });
        }

        function getEventsTodayKey(stack) {
            const value = String(stack?.getAttribute('data-events-today') || '').trim();

            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return value;
            }

            return new Date().toISOString().slice(0, 10);
        }

        function isExpiredEventsDate(value, todayKey) {
            const normalized = String(value || '').trim();

            return /^\d{4}-\d{2}-\d{2}$/.test(normalized) && normalized < todayKey;
        }

        function getEventsCardGroupList(stack, groupKey) {
            return stack?.querySelector(`[data-events-card-group-list="${groupKey}"]`) || null;
        }

        function refreshEventsCardGroups(scope) {
            const stack = scope?.querySelector?.('[data-events-card-stack]') || scope;
            if (!(stack instanceof HTMLElement)) {
                return;
            }

            const hasSelection = Array.from(stack.querySelectorAll('[data-events-card-editor]'))
                .some((editor) => editor.classList.contains('is-selected'));
            const visibleGroup = String(stack.dataset.eventsVisibleGroup || '').trim().toLowerCase();

            ['active', 'expired'].forEach((groupKey) => {
                const group = stack.querySelector(`[data-events-card-group="${groupKey}"]`);
                const list = getEventsCardGroupList(stack, groupKey);
                const editors = Array.from(list?.querySelectorAll('[data-events-card-editor]') || []);
                const visibleEditors = editors.filter((editor) => !editor.hidden);
                const count = stack.querySelector(`[data-events-card-group-count="${groupKey}"]`);
                const empty = stack.querySelector(`[data-events-card-empty="${groupKey}"]`);

                if (count) {
                    count.textContent = String(editors.length);
                }

                if (empty) {
                    empty.hidden = editors.length !== 0;
                }

                if (group) {
                    const hiddenForVisibleGroup = visibleGroup !== '' && visibleGroup !== 'all' && visibleGroup !== groupKey;
                    group.hidden = hiddenForVisibleGroup || (hasSelection ? visibleEditors.length === 0 : false);
                }
            });
        }

        function moveEventsCardEditorToGroup(editor) {
            const stack = editor?.closest('[data-events-card-stack]');
            if (!editor || !stack) {
                return;
            }

            const dateInput = editor.querySelector('input[name*="[event_date]"]');
            const nextGroupKey = isExpiredEventsDate(dateInput?.value, getEventsTodayKey(stack)) ? 'expired' : 'active';
            const targetList = getEventsCardGroupList(stack, nextGroupKey);

            if (!targetList || targetList === editor.parentElement) {
                return;
            }

            targetList.appendChild(editor);
        }

        function bindEventsCardDateInput(editor) {
            const dateInput = editor?.querySelector('input[name*="[event_date]"]');
            if (!dateInput || dateInput.dataset.eventsDateBound === '1') {
                return;
            }

            dateInput.dataset.eventsDateBound = '1';

            const syncGroup = () => {
                moveEventsCardEditorToGroup(editor);
                const stack = editor.closest('[data-events-card-stack]');
                if (editor.classList.contains('is-selected') && stack instanceof HTMLElement) {
                    const editorGroup = editor.closest('[data-events-card-group]')?.getAttribute('data-events-card-group') || '';
                    if (editorGroup !== '') {
                        stack.dataset.eventsVisibleGroup = editorGroup;
                    }
                }
                refreshEventsCardGroups(editor.closest('[data-events-cards-form]'));
            };

            dateInput.addEventListener('change', syncGroup);
            dateInput.addEventListener('input', syncGroup);
        }

        function initEventsImageDropzones(scope = document) {
            scope.querySelectorAll('.events-cms-image-dropzone-input').forEach((input) => {
                if (input.dataset.eventsDropzoneBound === '1') {
                    return;
                }

                const label = scope.querySelector(`[data-events-dropzone-for="${input.id}"]`)
                    || document.querySelector(`[data-events-dropzone-for="${input.id}"]`);
                const fileNameEl = scope.querySelector(`[data-events-file-name-for="${input.id}"]`)
                    || document.querySelector(`[data-events-file-name-for="${input.id}"]`);
                const previewEl = scope.querySelector(`[data-events-preview-for="${input.id}"]`)
                    || document.querySelector(`[data-events-preview-for="${input.id}"]`);
                const removeButton = scope.querySelector(`[data-events-clear-image-for="${input.id}"]`)
                    || document.querySelector(`[data-events-clear-image-for="${input.id}"]`);
                const editButton = scope.querySelector(`[data-events-edit-image-for="${input.id}"]`)
                    || document.querySelector(`[data-events-edit-image-for="${input.id}"]`);
                const imageField = input.closest('[data-events-card-editor]')?.querySelector('[data-events-image-field]') || null;

                if (!label || !fileNameEl) {
                    return;
                }

                input.dataset.eventsDropzoneBound = '1';
                const emptyText = fileNameEl.dataset.emptyText || 'Drop image here or click to replace';
                const defaultSrc = previewEl?.dataset.eventsDefaultSrc || '';

                const syncRemoveState = () => {
                    if (!removeButton) {
                        return;
                    }

                    const hasImage = Boolean((imageField?.value || '').trim() !== '' || (input.files && input.files[0]));
                    removeButton.hidden = !hasImage;
                    if (typeof editButton !== 'undefined' && editButton) editButton.hidden = !hasImage;
                };

                const prepareImageFile = async (file) => {
                    if (!file || !window.CmsImageEditor) {
                        return file;
                    }

                    const editedFile = await window.CmsImageEditor.editFile(file, {
                        input,
                        previewElement: previewEl,
                    });

                    if (editedFile && editedFile !== file) {
                        window.CmsImageEditor.setInputFile(input, editedFile);
                    }

                    return editedFile;
                };
                if (typeof editButton !== 'undefined' && editButton) {
                    editButton.addEventListener('click', async (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        let file = input.files && input.files[0];
                        if (!file && previewEl && previewEl.src && previewEl.src !== defaultSrc) {
                            try {
                                const res = await fetch(previewEl.src);
                                const blob = await res.blob();
                                const ext = previewEl.src.split('.').pop().split(/#|\?/)[0] || 'jpg';
                                file = new File([blob], `image.${ext}`, { type: blob.type });
                            } catch(err) {
                                console.error("Could not fetch image to edit", err);
                            }
                        }
                        
                        if (file && window.CmsImageEditor) {
                            const editedFile = await window.CmsImageEditor.editFile(file, {
                                input,
                                previewElement: previewEl,
                            });
                            
                            if (editedFile && editedFile !== file) {
                                window.CmsImageEditor.setInputFile(input, editedFile);
                                if (typeof applyFile === 'function') {
                                    applyFile(editedFile);
                                }
                            }
                        }
                    });
                }


                const applyFile = (file) => {
                    if (!file) {
                        syncRemoveState();
                        return;
                    }

                    fileNameEl.textContent = `Selected: ${file.name}`;

                    if (previewEl) {
                        previewEl.src = URL.createObjectURL(file);
                    }

                    syncRemoveState();
                };

                input.addEventListener('change', async () => {
                    const file = await prepareImageFile(input.files && input.files[0] ? input.files[0] : null);
                    if (!file) {
                        input.value = '';
                    }
                    applyFile(file);
                });

                label.addEventListener('click', (event) => {
                    if (event.target.closest('[data-events-clear-image-for]')) {
                        return;
                    }

                    input.click();
                });

                label.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    input.click();
                });

                label.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    label.classList.add('dragover');
                });

                label.addEventListener('dragleave', () => {
                    label.classList.remove('dragover');
                });

                label.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    label.classList.remove('dragover');

                    const file = event.dataTransfer?.files?.[0] ?? null;
                    if (!file) {
                        return;
                    }

                    const editedFile = await prepareImageFile(file);
                    if (!editedFile) {
                        input.value = '';
                        applyFile(null);
                        return;
                    }

                    window.CmsImageEditor?.setInputFile(input, editedFile);
                    applyFile(editedFile);
                });

                removeButton?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    input.value = '';
                    if (imageField) {
                        imageField.value = '';
                    }
                    if (previewEl && defaultSrc) {
                        previewEl.src = defaultSrc;
                    }
                    fileNameEl.textContent = emptyText;
                    syncRemoveState();
                });

                syncRemoveState();
            });
        }

        function deleteEventsCardEditor(editor, options = {}) {
            const stack = editor?.closest('[data-events-card-stack]');

            if (!editor || !stack) {
                return false;
            }

            const wasSelected = editor.classList.contains('is-selected');
            editor.remove();

            const remainingEditors = Array.from(stack.querySelectorAll('[data-events-card-editor]'));

            if (!remainingEditors.length) {
                refreshEventsCardGroups(stack);
                return true;
            }

            if (wasSelected && options.keepFocus !== false) {
                const fallbackEditor = remainingEditors[0];
                const fallbackIndex = fallbackEditor.getAttribute('data-events-card-index');
                setActiveEventsCardEditor(fallbackIndex);

                const firstField = fallbackEditor.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                firstField?.focus();
            }

            refreshEventsCardGroups(stack);

            return true;
        }

        function openEventsEditor(sectionKey, label, options = {}) {
            const modal = document.querySelector('[data-events-editor-modal]');
            if (!modal) {
                return;
            }

            const title = modal.querySelector('#{{ $idPrefix }}-modal-title');
            const description = modal.querySelector('[data-events-editor-description]');

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('cms-editor-modal-open');

            modal.querySelectorAll('[data-events-editor-panel]').forEach((panel) => {
                const isActive = panel.getAttribute('data-events-editor-panel') === sectionKey;
                const isCardFocus = sectionKey === 'cards';
                panel.hidden = !isActive;
                panel.classList.toggle('is-card-focus', isActive && isCardFocus);

                if (isActive) {
                    modal.classList.toggle('is-card-focus', isCardFocus);
                    if (title) {
                        title.textContent = label || 'Edit events section';
                    }

                    if (description) {
                        description.textContent = sectionKey === 'cards'
                            ? 'Add, edit, or delete event cards individually and save to refresh the events page preview.'
                            : 'Update this section and save to refresh the events page preview.';
                    }

                    if (typeof window.initializeRichTextEditors === 'function') {
                        window.initializeRichTextEditors(panel);
                    }

                    const activeCardEditor = sectionKey === 'cards'
                        ? setActiveEventsCardEditor(options.cardIndex ?? null)
                        : null;
                    if (sectionKey === 'cards') {
                        const stack = panel.querySelector('[data-events-card-stack]');
                        if (stack instanceof HTMLElement && (!stack.dataset.eventsVisibleGroup || stack.dataset.eventsVisibleGroup === 'all')) {
                            stack.dataset.eventsVisibleGroup = activeCardEditor
                                ? (activeCardEditor.closest('[data-events-card-group]')?.getAttribute('data-events-card-group') || 'active')
                                : 'active';
                        }
                    }
                    const focusScope = activeCardEditor || panel;
                    if (sectionKey === 'cards') {
                        refreshEventsCardGroups(panel);
                    }
                    const firstField = focusScope.querySelector('input:not([type="hidden"]), textarea, select, .rich-editor-surface');
                    firstField?.focus();
                }
            });
        }

        function addEventsCard(options = {}) {
            const cardsPanel = document.querySelector('[data-events-editor-panel="cards"]');
            const form = cardsPanel?.querySelector('[data-events-cards-form]');
            const stack = form?.querySelector('[data-events-card-stack]');
            const template = form?.querySelector('[data-events-card-template]');

            if (!stack || !template) {
                return null;
            }

            const existingNewCard = stack.querySelector('[data-events-card-editor][data-events-new-card="1"]');
            if (existingNewCard) {
                setActiveEventsCardEditor(existingNewCard.getAttribute('data-events-card-index'));

                if (options.focus !== false) {
                    const existingField = existingNewCard.querySelector('input[type="text"], input[type="date"], textarea, select');
                    existingField?.focus();
                }

                return existingNewCard;
            }

            const nextIndex = getNextEventsCardIndex(stack);
            const html = template.innerHTML
                .replaceAll('__INDEX__', String(nextIndex))
                .replaceAll('__CARD_NUMBER__', String(stack.querySelectorAll('[data-events-card-editor]').length + 1));

            const activeList = getEventsCardGroupList(stack, 'active');
            if (!activeList) {
                return null;
            }

            activeList.insertAdjacentHTML('beforeend', html);

            const newCard = activeList.lastElementChild;
            if (newCard && typeof window.initializeRichTextEditors === 'function') {
                window.initializeRichTextEditors(newCard);
            }

            bindEventsCardDateInput(newCard);
            initEventsImageDropzones(newCard);
            moveEventsCardEditorToGroup(newCard);
            syncEventsFeaturedToggles(form);

            markEventsCardsChanged(form);

            setActiveEventsCardEditor(String(nextIndex));
            refreshEventsCardGroups(form);

            if (options.focus !== false) {
                const firstField = newCard?.querySelector('input[type="text"], input[type="date"], textarea, select');
                firstField?.focus();
            }

            return newCard;
        }

        function closeEventsEditor() {
            const modal = document.querySelector('[data-events-editor-modal]');
            if (!modal) {
                return;
            }

            modal.hidden = true;
            modal.classList.remove('is-card-focus');
            document.body.style.overflow = '';
            document.body.classList.remove('cms-editor-modal-open');
        }

        function bindAddEventsCard() {
            document.querySelectorAll('[data-add-events-card]').forEach((button) => {
                if (button.dataset.eventsCardBound === '1') {
                    return;
                }

                button.dataset.eventsCardBound = '1';

                button.addEventListener('click', () => {
                    addEventsCard();
                });
            });
        }

        function deleteEventsCard(trigger) {
            const editor = trigger.closest('[data-events-card-editor]');
            if (!editor) {
                return;
            }

            confirmEventsCardDelete(editor.getAttribute('data-events-card-index'));
        }

        function deleteEventsCardByIndex(cardIndex, options = {}) {
            const form = document.querySelector('[data-events-cards-form]');
            const editor = form?.querySelector(`[data-events-card-editor][data-events-card-index="${cardIndex}"]`);
            if (!editor) {
                return false;
            }

            const deleted = deleteEventsCardEditor(editor, options);
            if (!deleted) {
                return false;
            }

            markEventsCardsChanged(form);

            const frame = document.querySelector('[data-events-preview-frame]');
            frame?.contentWindow?.postMessage({
                type: 'cms-events-prune-card',
                cardIndex: Number(cardIndex),
            }, '*');

            return true;
        }

        function deleteEventsCardsByIndexes(cardIndexes, options = {}) {
            const normalizedIndexes = Array.from(new Set(
                (Array.isArray(cardIndexes) ? cardIndexes : [])
                    .map((value) => Number(value))
                    .filter((value) => Number.isFinite(value))
            ));

            if (!normalizedIndexes.length) {
                return 0;
            }

            let deletedCount = 0;

            normalizedIndexes.forEach((cardIndex) => {
                if (deleteEventsCardByIndex(cardIndex, {
                    keepFocus: false,
                    ...options,
                })) {
                    deletedCount += 1;
                }
            });

            return deletedCount;
        }

        function submitEventsCardsForm(form) {
            if (!form) {
                return;
            }

            form.dataset.eventsSkipValidation = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true,
            }));

            delete form.dataset.eventsSkipValidation;
        }

        async function confirmEventsCardDelete(cardIndex) {
            const form = document.querySelector('[data-events-cards-form]');
            const editor = form?.querySelector(`[data-events-card-editor][data-events-card-index="${cardIndex}"]`);
            if (!editor) {
                return;
            }

            const titleInput = editor.querySelector('input[name*="[title]"]');
            const cardTitle = String(titleInput?.value || '').trim();
            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: 'Delete Event',
                    message: cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this event card?',
                    confirmText: 'Delete',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(
                    cardTitle
                        ? `Do you want to delete "${cardTitle}"?`
                        : 'Do you want to delete this event card?'
                );
            }

            if (!confirmed) {
                return;
            }

            const deleted = deleteEventsCardByIndex(cardIndex);
            if (!deleted) {
                return;
            }

            submitEventsCardsForm(form);
        }

        async function confirmExpiredEventsDelete(cardIndexes) {
            const form = document.querySelector('[data-events-cards-form]');
            if (!form) {
                return;
            }

            const normalizedIndexes = Array.from(new Set(
                (Array.isArray(cardIndexes) ? cardIndexes : [])
                    .map((value) => Number(value))
                    .filter((value) => Number.isFinite(value))
            ));

            if (!normalizedIndexes.length) {
                return;
            }

            const titles = normalizedIndexes
                .map((cardIndex) => {
                    const editor = form.querySelector(`[data-events-card-editor][data-events-card-index="${cardIndex}"]`);
                    const titleInput = editor?.querySelector('input[name*="[title]"]');

                    return String(titleInput?.value || '').trim();
                })
                .filter((value) => value !== '');

            const totalCount = normalizedIndexes.length;
            const message = totalCount === 1
                ? (titles[0]
                    ? `Do you want to remove "${titles[0]}" from expired events?`
                    : 'Do you want to remove this expired event?')
                : `Do you want to remove ${totalCount} selected expired events?`;

            let confirmed = false;

            if (typeof window.confirmAction === 'function') {
                confirmed = await window.confirmAction({
                    title: totalCount === 1 ? 'Remove Expired Event' : 'Remove Selected Expired Events',
                    message,
                    confirmText: 'Remove',
                    tone: 'danger',
                });
            } else {
                confirmed = window.confirm(message);
            }

            if (!confirmed) {
                return;
            }

            const deletedCount = deleteEventsCardsByIndexes(normalizedIndexes, {
                keepFocus: false,
            });

            if (deletedCount === 0) {
                return;
            }

            submitEventsCardsForm(form);
        }

        window.openEventsCmsSection = openEventsEditor;

        window.addEventListener('message', (event) => {
            const data = event.data || {};
            if (!data || !data.type) {
                return;
            }

            if (data.type === 'cms-events-edit') {
                if ((data.section || '') === 'cards') {
                    return;
                }

                openEventsEditor(data.section || 'page', data.label || 'Edit events section');
                return;
            }

            if (data.type === 'cms-events-add-card') {
                openEventsEditor('cards', data.label || 'Add event card');
                window.setTimeout(() => addEventsCard(), 0);
                return;
            }

            if (data.type === 'cms-events-edit-card') {
                openEventsEditor('cards', data.label || 'Edit event card', {
                    cardIndex: data.cardIndex,
                });
                return;
            }

            if (data.type === 'cms-events-delete-card') {
                confirmEventsCardDelete(data.cardIndex);
                return;
            }

            if (data.type === 'cms-events-delete-expired-cards') {
                confirmExpiredEventsDelete(data.cardIndexes);
                return;
            }

            if (data.type === 'cms-events-preview-height') {
                const targetFrame = Array.from(document.querySelectorAll('[data-events-preview-frame]'))
                    .find((frame) => frame.contentWindow === event.source);

                if (!targetFrame) {
                    return;
                }

                syncEventsPreviewHeight(targetFrame, data.height);
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-close-events-editor]')) {
                event.preventDefault();
                closeEventsEditor();
                return;
            }

            const deleteTrigger = event.target.closest('[data-delete-events-card]');
            if (deleteTrigger) {
                event.preventDefault();
                deleteEventsCard(deleteTrigger);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeEventsEditor();
            }
        });

        function showEventsValidationToast(message) {
            if (typeof window.showToast === 'function') {
                window.showToast(message, 'warning', 'Missing Details');
                return;
            }

            window.alert(message);
        }

        function getEventCardField(editor, fieldName) {
            return editor?.querySelector(`[name*="[${fieldName}]"]`) || null;
        }

        function getEventCardFieldValue(editor, fieldName) {
            const field = getEventCardField(editor, fieldName);
            return String(field?.value || '').trim();
        }

        function validateEventsCardEditor(editor) {
            if (!(editor instanceof HTMLElement)) {
                return true;
            }

            const requiredFields = [
                ['title', 'Event Title'],
                ['category', 'Category'],
                ['event_date', 'Event Date'],
                ['location', 'Location'],
                ['start_time', 'Start Time'],
                ['end_time', 'End Time'],
                ['content', 'Event Details'],
            ];
            const missing = requiredFields.filter(([fieldName]) => getEventCardFieldValue(editor, fieldName) === '');

            if (missing.length === 0) {
                return true;
            }

            const firstMissingField = getEventCardField(editor, missing[0][0]);
            showEventsValidationToast('All fields are required. Please complete: ' + missing.map(([, label]) => label).join(', ') + '.');

            if (firstMissingField) {
                firstMissingField.focus();
            } else {
                editor.querySelector('.rich-editor-surface')?.focus();
            }

            return false;
        }

        function validateEventsCardsForm(form) {
            if (!form?.matches?.('[data-events-cards-form]')) {
                return true;
            }

            if (form.dataset.eventsSkipValidation === '1') {
                delete form.dataset.eventsSkipValidation;
                return true;
            }

            syncEditorsInScope(form);

            const editors = Array.from(form.querySelectorAll('[data-events-card-editor]'));
            const targetEditors = editors.filter((editor) => editor.classList.contains('is-selected'));
            const editorsToValidate = targetEditors.length > 0
                ? targetEditors
                : editors.filter((editor) => editor.hasAttribute('data-events-new-card') && !editor.hidden);

            if (editorsToValidate.length === 0) {
                return true;
            }

            return editorsToValidate.every((editor) => validateEventsCardEditor(editor));
        }

        document.querySelectorAll('.{{ $formClass }}').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!validateEventsCardsForm(form)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    event.stopPropagation();
                    return;
                }

                syncEditorsInScope(form);
            });
        });

        document.querySelectorAll('[data-events-preview-frame]').forEach((frame) => {
            loadEventsPreview(frame);

            frame.addEventListener('load', () => {
                bindEventsPreviewDocument(frame);
                queueEventsPreviewSettledSync(frame);
                scheduleFitAllEventsPreviews();
            });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const previewResizeObserver = new ResizeObserver(() => {
                scheduleFitAllEventsPreviews();
            });

            document.querySelectorAll('.events-cms-preview-frame-shell').forEach((shell) => {
                previewResizeObserver.observe(shell);
            });

            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                previewResizeObserver.observe(mainContent);
            }
        }

        const sidebar = document.getElementById('sidebar');
        if (sidebar && typeof MutationObserver !== 'undefined') {
            const sidebarObserver = new MutationObserver(() => {
                scheduleFitAllEventsPreviews();
            });

            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }

        window.addEventListener('resize', scheduleFitAllEventsPreviews);
        window.addEventListener('pageshow', scheduleFitAllEventsPreviews);
        window.addEventListener('load', scheduleFitAllEventsPreviews);
        window.addEventListener('cms:tab-activated', (event) => {
            const tabPanel = event.detail?.panel;
            const sessionId = Number(event.detail?.sessionId || 0) || undefined;

            document.querySelectorAll('[data-events-preview-frame]').forEach((frame) => {
                if (!tabPanel || !tabPanel.contains(frame)) {
                    return;
                }

                loadEventsPreview(frame, { sessionId });
                window.setTimeout(() => scheduleFitAllEventsPreviews(), 40);
                window.setTimeout(() => scheduleFitAllEventsPreviews(), 180);
            });
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                scheduleFitAllEventsPreviews();
            }
        });

        window.refreshEventsCmsPreview = (scope) => {
            const frames = scope
                ? Array.from(scope.querySelectorAll('[data-events-preview-frame]'))
                : Array.from(document.querySelectorAll('[data-events-preview-frame]'));

            frames.forEach((frame) => {
                loadEventsPreview(frame);
            });
        };

        bindAddEventsCard();
        document.querySelectorAll('[data-events-card-editor]').forEach((editor) => {
            bindEventsCardDateInput(editor);
            moveEventsCardEditorToGroup(editor);
        });
        const eventsCardsForm = document.querySelector('[data-events-cards-form]');
        initEventsImageDropzones(eventsCardsForm || document);
        bindEventsCardsDirtyTracking(eventsCardsForm);
        eventsCardsForm?.addEventListener('change', (event) => {
            if (event.target instanceof HTMLInputElement && event.target.classList.contains('events-cms-feature-toggle')) {
                syncEventsFeaturedToggles(eventsCardsForm);
            }
        });
        refreshEventsCardGroups(eventsCardsForm);
        syncEventsFeaturedToggles(eventsCardsForm);
        scheduleFitAllEventsPreviews();
        window.__eventsCmsPreviewEditorReady = true;
    })();
</script>
