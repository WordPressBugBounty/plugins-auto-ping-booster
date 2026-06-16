<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'Auto Ping Booster Pro',
        'APB Pro',
        'manage_options',
        'apb-pro',
        'apb_admin_page'
    );
});

function apb_admin_page() {
    echo "<div class='wrap'>";
    echo "<h1>Auto Ping Booster Pro 2.0</h1>";
    echo "<p>Modern Indexing System Active</p>";
    echo "<p>Status: Running IndexNow API</p>";
    echo "</div>";
}