<?php
/*
Plugin Name: Auto Ping Booster Pro
Description: Modern SEO Indexing Tool using IndexNow (no outdated ping services)
Version: 2.0
Author: Samee Ullah Feroz
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/indexer.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

add_action('publish_post', 'apb_trigger_indexing');

function apb_trigger_indexing($post_ID) {
    $url = get_permalink($post_ID);
    apb_send_to_indexers($url);
}