<?php
if (!defined('ABSPATH')) exit;

/**
 * Handles database transaction logging without straining server storage
 */
function apb_log_action($post_id, $type, $url, $status, $response_msg) {
    if (get_option('apb_enable_logging') !== '1') return;

    $logs = get_option('apb_activity_logs', array());

    // Clean data wrapper array
    $new_log = array(
        'time'    => current_time('mysql'),
        'post_id' => intval($post_id),
        'type'    => sanitize_text_field($type),
        'url'     => esc_url_raw($url),
        'status'  => sanitize_text_field($status),
        'message' => sanitize_text_field($response_msg)
    );

    // Prepend item to array
    array_unshift($logs, $new_log);

    // Limit log stack threshold to top 30 records to save memory footprint
    if (count($logs) > 30) {
        $logs = array_slice($logs, 0, 30);
    }

    update_option('apb_activity_logs', $logs);
}