<?php
$files = [
    __DIR__ . '/resources/views/admin/notifications.blade.php',
    __DIR__ . '/resources/views/staff/notifications.blade.php',
    __DIR__ . '/resources/views/superadmin/notifications.blade.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $search = "if (item) item.classList.remove('unread');\n        btn.disabled = true;\n        showToast(response.message || \"Notification marked as read.\", 'success', 'Success');";
        
        $replace = "if (item) item.classList.remove('unread');\n        btn.disabled = true;\n        \n        document.querySelectorAll('.unread-notifications-badge').forEach(badge => {\n            let text = badge.innerText;\n            let current = text === '99+' ? 100 : parseInt(text, 10);\n            if (!isNaN(current)) {\n                current--;\n                if (current <= 0) {\n                    badge.style.display = 'none';\n                } else {\n                    badge.innerText = current > 99 ? '99+' : current;\n                }\n            }\n        });\n\n        showToast(response.message || \"Notification marked as read.\", 'success', 'Success');";
        
        $newContent = str_replace($search, $replace, $content);
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Updated: $file\n";
        }
    }
}
