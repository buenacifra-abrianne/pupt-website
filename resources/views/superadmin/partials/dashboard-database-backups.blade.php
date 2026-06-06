<div class="page-header" style="margin-top: 5px;">
    <div>
        <h1 class="page-title"><i class="fas fa-database" style="color: #D4AF37;"></i> Database Backup</h1>
        <p class="page-subtitle">Create and manage database backups.</p>
    </div>
    <div class="backup-tab-actions">
        <form method="POST" action="{{ route('superadmin.database-backups.store') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Backup
            </button>
        </form>
        <a href="{{ route('superadmin.dashboard', ['tab' => 'database-backups']) }}" class="btn btn-outline btn-sm">
            <i class="fas fa-rotate-right"></i> Refresh List
        </a>
    </div>
</div>

<div class="stats-grid backup-stats-grid">
    <div class="stat-card">
        <div class="stat-icon maroon">
            <i class="fas fa-box-archive"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Backups</div>
            <div class="stat-value">{{ number_format((int) ($databaseBackupStats['total_backups'] ?? 0)) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Last Backup Date</div>
            <div class="backup-inline-value">
                {{ !empty($databaseBackupStats['last_backup_date']) ? \Carbon\Carbon::parse($databaseBackupStats['last_backup_date'])->format('M d, Y g:i A') : 'No backups yet' }}
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-hard-drive"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Storage Used</div>
            <div class="stat-value">{{ $databaseBackupStats['total_storage_used'] ?? '0 B' }}</div>
        </div>
    </div>
</div>

<div class="backup-layout">
    <div class="card backup-settings-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-gear"></i> Automatic Backups</h3>
        </div>

        <form method="POST" action="{{ route('superadmin.database-backups.settings') }}" class="backup-settings-form">
            @csrf
            @method('PATCH')

            <label class="backup-toggle-row">
                <span>
                    <span class="backup-field-label">Enable Automatic Backup</span>
                    <span class="backup-field-help">Run scheduled backups with Laravel Scheduler.</span>
                </span>
                <input type="checkbox" name="automatic_backups_enabled" value="1" id="automaticBackupsEnabled" {{ $databaseBackupSettings->automatic_backups_enabled ? 'checked' : '' }}>
            </label>

            <div class="backup-settings-grid">
                <div class="backup-field-group">
                    <label for="backupFrequency" class="backup-field-label">Frequency</label>
                    <select name="frequency" id="backupFrequency">
                        <option value="daily" {{ $databaseBackupSettings->frequency === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $databaseBackupSettings->frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $databaseBackupSettings->frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>

                <div class="backup-field-group">
                    <label for="backupRetention" class="backup-field-label">Retention</label>
                    <select name="retention_count" id="backupRetention">
                        <option value="7" {{ (int) $databaseBackupSettings->retention_count === 7 ? 'selected' : '' }}>Keep last 7 backups</option>
                        <option value="15" {{ (int) $databaseBackupSettings->retention_count === 15 ? 'selected' : '' }}>Keep last 15 backups</option>
                        <option value="30" {{ (int) $databaseBackupSettings->retention_count === 30 ? 'selected' : '' }}>Keep last 30 backups</option>
                    </select>
                </div>
            </div>

            @if ($errors->any())
                <div class="backup-form-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="backup-settings-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-floppy-disk"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    <div class="card backup-info-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-circle-info"></i> Storage Rules</h3>
        </div>
        <div class="backup-info-list">
            <div class="backup-info-item">
                <strong>Preferred Storage</strong>
                <span>AWS S3 folder: <code>backups/database/</code></span>
            </div>
            <div class="backup-info-item">
                <strong>Fallback Storage</strong>
                <span>Local private storage: <code>storage/app/backups</code></span>
            </div>
            <div class="backup-info-item">
                <strong>Access</strong>
                <span>Backup files are only available through secure Super Admin routes.</span>
            </div>
        </div>
    </div>
</div>

<div class="card backup-history-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table"></i> Backup History</h3>
    </div>

    <div class="backup-table-wrap">
        <table class="backup-table">
            <thead>
                <tr>
                    <th>Backup Name</th>
                    <th>File Size</th>
                    <th>Storage Location</th>
                    <th>Created By</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($databaseBackupRows as $backup)
                    <tr>
                        <td>{{ $backup['backup_name'] }}</td>
                        <td>{{ $backup['file_size_human'] }}</td>
                        <td>{{ $backup['storage_location'] }}</td>
                        <td>{{ $backup['created_by_name'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($backup['created_at'])->format('M d, Y g:i A') }}</td>
                        <td>
                            <div class="backup-row-actions">
                                <a href="{{ route('superadmin.database-backups.download', $backup['id']) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm backup-danger-btn"
                                    data-delete-action="{{ route('superadmin.database-backups.destroy', $backup['id']) }}"
                                    data-backup-name="{{ $backup['backup_name'] }}"
                                    onclick="openBackupDeleteModal(this)"
                                >
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="backup-empty-state">
                                <i class="fas fa-database"></i>
                                <p>No backups available yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="backupDeleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-triangle-exclamation"></i> Delete Backup</h2>
            <button class="close-btn" type="button" onclick="closeBackupDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="backupDeleteText">Are you sure you want to delete this backup? This action cannot be undone.</p>
        </div>
        <div class="modal-footer backup-modal-footer">
            <button class="btn btn-outline btn-sm" type="button" onclick="closeBackupDeleteModal()">Cancel</button>
            <form id="backupDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary btn-sm backup-danger-solid">
                    <i class="fas fa-trash"></i> Delete Backup
                </button>
            </form>
        </div>
    </div>
</div>
