<?php
/*
 * Bộ icon SVG dùng chung: adminIcon() nhận tên hoặc ký hiệu cũ trong $aliases.
 * Thêm icon bằng khóa trong $paths; tên không tồn tại dùng icon box.
 * Giữ nguyên chuỗi SVG để không thay đổi hình vẽ; aria-hidden dành cho icon trang trí.
 */

/** Inline SVG: no icon font, CDN or operating-system emoji dependency. */
function adminIcon(string $name): string
{
    $aliases = [
        '▦' => 'dashboard',
        '□' => 'box',
        '▤' => 'category',
        '◇' => 'building',
        '⚒' => 'tools',
        '▧' => 'project',
        '≡' => 'article',
        '▣' => 'image'
    ];
    $name = $aliases[$name] ?? $name;
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'box' => '<path d="m12 3 9 5v8l-9 5-9-5V8zM3 8l9 5 9-5M12 13v8M7.5 5.5l9 5"/>',
        'category' => '<path d="M3 6h7l2 3h9v11H3z"/><path d="M3 6V4h7l2 2h9v3"/>',
        'building' => '<rect x="5" y="3" width="14" height="18" rx="1"/><path d="M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3"/>',
        'tools' => '<path d="m14 6 4 4 3-3a6 6 0 0 1-8 7L6 21l-3-3 7-7a6 6 0 0 1 7-8z"/>',
        'project' => '<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V3h8v4M3 12h18M10 12v3h4v-3"/>',
        'article' => '<path d="M6 3h9l4 4v14H6zM14 3v5h5M9 12h7M9 16h7"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 4 4 3-3 5 5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="m15 4 5 5M4 20l5-1L21 7l-5-5L4 14z"/>',
        'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>',
        'back' => '<path d="m10 5-7 7 7 7M3 12h18"/>',
        'arrow' => '<path d="m14 5 7 7-7 7M3 12h18"/>',
        'external' => '<path d="M14 3h7v7m0-7L10 14M10 3H3v18h18v-7"/>',
        'reset' => '<path d="M3 11a9 9 0 1 1 3 8M3 4v7h7"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'star' => '<path d="m12 3 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/>',
    ];
    return '<svg class="admin-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($paths[$name] ?? $paths['box']) . '</svg>';
}
