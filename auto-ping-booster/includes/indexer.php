<?php
if (!defined('ABSPATH')) exit;

function apb_send_to_indexers($url, $post_id = 0) {
    $key = get_option('apb_indexnow_key');
    
    if (empty($key)) {
        apb_log_action("Indexing failed: IndexNow API Key is missing.");
        return;
    }

    // --- 1. INDEXNOW ENGINE ---
    $endpoint = "https://api.indexnow.org/indexnow";
    $data = array(
        "host"        => parse_url(home_url(), PHP_URL_HOST),
        "key"         => $key,
        "keyLocation" => home_url('/' . $key . '.txt'),
        "urlList"     => array($url)
    );

    $response = wp_remote_post($endpoint, array(
        'body'    => json_encode($data),
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'timeout' => 15
    ));

    // Log the event output
    if (is_wp_error($response)) {
        apb_log_action("IndexNow Error for ID {$post_id}: " . $response->get_error_message());
    } else {
        $code = wp_remote_retrieve_response_code($response);
        apb_log_action("IndexNow Success for ID {$post_id}: HTTP Status Code {$code}");
    }

    // --- 2. FUTURE GOOGLE INDEXING API (PRO TIER) ---
    // If (get_option('apb_pro_google_json_key')) { ... }

    // --- 3. FUTURE AI SEO SCHEMA GENERATION (AI TIER) ---
    // If (get_option('apb_ai_tier_enabled')) { ... }
}

function apb_log_action($message) {
    if (get_option('apb_enable_logging') !== '1') return;

    $log_file = plugin_dir_path(__DIR__) . 'debug.log';
    $timestamp = date("Y-m-d H:i:s");
    error_log("[{$timestamp}] {$message}\n", 3, $log_file);
}