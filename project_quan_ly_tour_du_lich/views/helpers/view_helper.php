<?php
/**
 * Helper function để chuyển đổi view sang layout mới
 * Sử dụng: require_once __DIR__ . '/helpers/view_helper.php';
 * Sau đó gọi renderView($pageTitle, $currentPage, $content);
 */

function renderAventuraView($pageTitle, $currentPage, $content) {
    ob_start();
    echo $content;
    $finalContent = ob_get_clean();
    
    require __DIR__ . '/../layouts/aventura.php';
}

/**
 * Tạo card với dark theme
 */
function aventuraCard($content, $classes = '') {
    return '<div class="card" style="background: rgba(45, 45, 45, 0.5); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 2px; padding: 30px; margin-bottom: 30px; backdrop-filter: blur(10px); ' . $classes . '">' . $content . '</div>';
}

/**
 * Tạo button với dark theme
 */
function aventuraButton($text, $href, $type = 'primary', $icon = '') {
    $styles = [
        'primary' => 'background: var(--accent-gold); color: var(--primary-dark);',
        'secondary' => 'background: rgba(255, 255, 255, 0.1); color: var(--text-light); border: 1px solid rgba(255, 255, 255, 0.2);'
    ];
    $style = $styles[$type] ?? $styles['primary'];
    $iconHtml = $icon ? $icon . ' ' : '';
    return '<a href="' . htmlspecialchars($href) . '" class="btn" style="' . $style . '">' . $iconHtml . htmlspecialchars($text) . '</a>';
}

