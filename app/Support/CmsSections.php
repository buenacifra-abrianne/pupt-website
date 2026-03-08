<?php

namespace App\Support;

class CmsSections
{
    private const TAB_DEFINITIONS = [
        'home' => [
            'label' => 'Home',
            'request_type' => 'CMS_HOME_EDIT',
        ],
        'about' => [
            'label' => 'About',
            'request_type' => 'CMS_ABOUT_EDIT',
        ],
        'academics' => [
            'label' => 'Academics',
            'request_type' => 'CMS_ACADEMICS_EDIT',
        ],
        'students' => [
            'label' => 'Students',
            'request_type' => 'CMS_STUDENTS_EDIT',
        ],
        'research_extension' => [
            'label' => 'Research & Extension',
            'request_type' => 'CMS_RESEARCH_EXTENSION_EDIT',
        ],
        'events' => [
            'label' => 'Events',
            'request_type' => 'CMS_EVENTS_EDIT',
        ],
    ];

    private const ROLE_ACCESS = [
        'SUPERADMIN' => ['home', 'about', 'academics', 'students', 'research_extension', 'events'],
        'ADMIN' => ['home', 'about', 'academics', 'students', 'research_extension', 'events'],
        'REGISTRAR' => ['home', 'about', 'events'],
        'HAP' => ['home', 'academics', 'events'],
        'STUDENT_SERVICES' => ['home', 'students', 'events'],
        'RESEARCH_EXTENSION' => ['home', 'research_extension', 'events'],
        'FACULTY' => ['home', 'events'],
        'PUPT:FACULTY' => ['home', 'events'],
    ];

    public static function normalizeRole(string $role): string
    {
        $normalized = strtoupper(trim($role));

        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/', '_', $normalized) ?? $normalized;

        return match ($normalized) {
            'RESEARCH' => 'RESEARCH_EXTENSION',
            'RESEARCH_&_EXTENSION' => 'RESEARCH_EXTENSION',
            'RESEARCH_AND_EXTENSION' => 'RESEARCH_EXTENSION',
            'PUPT_FACULTY' => 'FACULTY',
            default => $normalized,
        };
    }

    public static function allTabKeys(): array
    {
        return array_keys(self::TAB_DEFINITIONS);
    }

    public static function tabsForRole(string $role): array
    {
        $normalized = self::normalizeRole($role);

        return self::ROLE_ACCESS[$normalized] ?? [];
    }

    public static function superadminTabs(): array
    {
        return self::tabsForRole('GLOBAL_SUPERADMIN');
    }

    public static function tabDefinitions(?array $tabKeys = null): array
    {
        if ($tabKeys === null) {
            return self::TAB_DEFINITIONS;
        }

        $out = [];
        foreach ($tabKeys as $tabKey) {
            if (!isset(self::TAB_DEFINITIONS[$tabKey])) {
                continue;
            }
            $out[$tabKey] = self::TAB_DEFINITIONS[$tabKey];
        }

        return $out;
    }

    public static function requestTypeForTab(string $tabKey): ?string
    {
        return self::TAB_DEFINITIONS[$tabKey]['request_type'] ?? null;
    }

    public static function tabForRequestType(string $type): ?string
    {
        $target = strtoupper(trim($type));
        foreach (self::TAB_DEFINITIONS as $tabKey => $definition) {
            if (($definition['request_type'] ?? '') === $target) {
                return $tabKey;
            }
        }

        return null;
    }

    public static function labelForTab(string $tabKey): string
    {
        return self::TAB_DEFINITIONS[$tabKey]['label'] ?? $tabKey;
    }

    public static function friendlyType(string $type): string
    {
        $tab = self::tabForRequestType($type);
        if ($tab === null) {
            return $type;
        }

        return 'Edit '.self::labelForTab($tab).' Content';
    }
}
