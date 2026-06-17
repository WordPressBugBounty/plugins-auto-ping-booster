<?php
/*
Plugin Name: Auto Ping Booster Pro
Description: Enterprise SEO Indexing Tool using IndexNow, Google Indexing API, and AI Automation.
Version: 2.10
Author: Samee Ullah Feroz
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/indexer.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// Monitor status transitions to catch actual new publications
add_action('transition_post_status', 'apb_check_status_transition', 10, 3);

function apb_check_status_transition($new_status, $old_status, $post) {
    // Only trigger if auto-submit is enabled globally
    if (get_option('apb_enable_auto_submit') !== '1') {
        return;
    }

    // Bypass revisions and auto-saves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type === 'revision') return;

    // Trigger ONLY when content shifts from draft/pending/new to PUBLISHED
    if ($new_status === 'publish' && $old_status !== 'publish') {
        $url = get_permalink($post->ID);
        apb_send_to_indexers($url, $post->ID);
    }
}

// Serve the IndexNow Key Verification dynamically if requested by web crawlers
add_action('init', 'apb_serve_indexnow_verification_key');
function apb_serve_indexnow_verification_key() {
    $key = get_option('apb_indexnow_key');
    if (!$key) return;

    $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
    if ($request_uri === $key . '.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo esc_html($key);
        exit;
    }
}