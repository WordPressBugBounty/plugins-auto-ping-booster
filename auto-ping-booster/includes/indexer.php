<?php
if (!defined('ABSPATH')) exit;

function apb_send_to_indexers($url, $post_id = 0, $post_type = 'post') {
    $key = get_option('apb_indexnow_key');
    
    if (empty($key)) {
        apb_log_action($post_id, $post_type, $url, 'Failed', 'IndexNow API Key configuration missing.');
        return;
    }

    // --- INDEXNOW PROTOCOL IMPLEMENTATION ---
    $endpoint = "https://api.indexnow.org/indexnow";
    $data = array(
        "host"        => parse_url(home_url(), PHP_URL_HOST),
        "key"         => $key,
        "keyLocation" => home_url('/' . $key . '.txt'),
        "urlList"     => array($url)
    );

    $response = wp_remote_post($endpoint, array(
        'body'        => json_encode($data),
        'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
        'timeout'     => 15,
        'data_format' => 'body'
    ));

    if (is_wp_error($response)) {
        apb_log_action($post_id, $post_type, $url, 'Error', $response->get_error_message());
    } else {
        $code = wp_remote_retrieve_response_code($response);
        if ($code === 200) {
            apb_log_action($post_id, $post_type, $url, 'Success', 'HTTP Status Code 200: Key paired successfully.');
        } else {
            apb_log_action($post_id, $post_type, $url, 'Warning', 'HTTP Error Response Code: ' . $code);
        }
    }

    // --- FUTURE GOOGLE INDEXING API (PRO TIER) ---
    // if (get_option('apb_pro_google_json_key')) { ... }

    // --- FUTURE AI SEO SCHEMA GENERATION (AI TIER) ---
    // if (get_option('apb_ai_tier_enabled')) { ... }
}