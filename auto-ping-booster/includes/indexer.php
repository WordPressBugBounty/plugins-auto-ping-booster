<?php
if (!defined('ABSPATH')) exit;

function apb_send_to_indexers($url) {

    // INDEXNOW (modern system)
    $endpoint = "https://api.indexnow.org/indexnow";

    $data = array(
        "host" => parse_url(home_url(), PHP_URL_HOST),
        "key" => "your-key-here",
        "urlList" => array($url)
    );

    wp_remote_post($endpoint, array(
        'body' => json_encode($data),
        'headers' => array('Content-Type' => 'application/json')
    ));
}