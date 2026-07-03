<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
foreach ($ite as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents($file);
        if (strpos($content, '<span>Notifications</span>') !== false && strpos($content, 'class="sidebar"') !== false) {
            $replacement = "<span>Notifications</span>\n                    @if((\$unreadNotificationCount ?? 0) > 0)\n                        <span class=\"unread-notifications-badge\" style=\"margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#f0c85a;color:#5c0000;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;\">{{ (\$unreadNotificationCount ?? 0) > 99 ? '99+' : \$unreadNotificationCount }}</span>\n                    @endif";
            $newContent = preg_replace('/<span>Notifications<\/span>/', $replacement, $content);
            if ($newContent !== $content) {
                file_put_contents($file, $newContent);
                echo "Updated: $file\n";
            }
        }
    }
}
