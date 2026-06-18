<?php
/*
 * Plugin Name:        Auto Ping Booster Premium Pro
 * Plugin URI:         https://wordpress.com/plugins/auto-ping-booster
 * Description:        Enterprise-grade All-in-One SEO Indexing Suite featuring real-time IndexNow pings, dynamic XML/HTML sitemaps, semantic JSON-LD schema generation, deep slug optimization, broken link tracking, and instant automated site health indexers.
 * Version:            6.0
 * Stable tag:         6.0
 * Author:             Samee Ullah Feroz
 * Author URI:         https://www.fiverr.com/samee2cool
 * License:            GPLv2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        auto-ping-booster
 * Domain Path:        /languages
 */

if (!defined('WPINC')) {
    die('Direct access restriction triggered.');
}

if (!defined('ABSPATH')) exit;

// Core Constants
define('APB_VERSION', '6.0.0');
define('APB_PATH', plugin_dir_path(__FILE__));

// Core File Inclusions
require_once APB_PATH . 'includes/logger.php';
require_once APB_PATH . 'includes/indexer.php';
require_once APB_PATH . 'includes/admin.php';
require_once APB_PATH . 'includes/seo-engine.php';

/**
 * FEATURE 1: Advanced Semantic JSON-LD Schema.org Data Engine
 * Automatically computes and injects structured rich snippets into the front-end head tag.
 */
add_action('wp_head', 'apb_generate_json_ld_schema', 2);
function apb_generate_json_ld_schema() {
    if (!is_singular()) {
        // Global WebSite Search Box Schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => esc_url(home_url('/')),
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => esc_url(home_url('/?s={search_term_string}')),
                'query-input' => 'required name=search_term_string'
            )
        );
        echo "\n<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>\n";
        return;
    }

    global $post;
    $schema = array();

    if (function_exists('is_product') && is_product() && $post->post_type === 'product') {
        // E-Commerce Product Schema
        $product = wc_get_product($post->ID);
        if ($product) {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => get_the_title($post->ID),
                'image' => wp_get_attachment_url($product->get_image_id()),
                'description' => wp_strip_all_tags(get_the_excerpt($post->ID)),
                'sku' => $product->get_sku(),
                'offers' => array(
                    '@type' => 'Offer',
                    'priceCurrency' => get_woocommerce_currency(),
                    'price' => $product->get_price(),
                    'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url' => get_permalink($post->ID)
                )
            );
        }
    } else {
        // Editorial Article/Blog Post Schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title($post->ID),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author)
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url()
                )
            )
        );
    }

    if (!empty($schema)) {
        echo "\n\n";
        echo "<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n</script>\n\n";
    }
}

/**
 * FEATURE 2: 404 Broken Link Trapper & Smart Redirection Routine
 * Catches dead organic entries, logs anomalies, and avoids crawl-budget wasting traps.
 */
add_action('template_redirect', 'apb_catch_broken_links_redirector');
function apb_catch_broken_links_redirector() {
    if (!is_404()) return;

    $requested_url = esc_url((is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    
    // Log error telemetry internally for administrative visibility
    $errors_log = get_option('apb_broken_links_log', array());
    $errors_log[time()] = $requested_url;
    
    // Keep internal buffer clean to protect database optimization
    if (count($errors_log) > 100) {
        array_shift($errors_log);
    }
    update_option('apb_broken_links_log', $errors_log);

    // Look for fallback patterns (e.g., product variations or slug alterations)
    $path_segments = explode('/', trim(parse_url($requested_url, PHP_URL_PATH), '/'));
    $last_segment  = end($path_segments);

    if (!empty($last_segment)) {
        global $wpdb;
        $fallback_post = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_status = 'publish' LIMIT 1",
            sanitize_title($last_segment)
        ));

        if ($fallback_post) {
            wp_redirect(get_permalink($fallback_post), 301);
            exit;
        }
    }
}

/**
 * FEATURE 3: Real-Time Content Readability & Structural SEO Analytics Engine
 * Runs advanced algorithmic text constraints directly before data is committed to the database.
 */
add_filter('wp_insert_post_data', 'apb_analyze_content_seo_metrics', 99, 2);
function apb_analyze_content_seo_metrics($data, $postarr) {
    if (in_array($data['post_status'], array('draft', 'publish'))) {
        $title = $data['post_title'];
        $content = $data['post_content'];

        // Quantify word configurations
        $word_count = str_word_count(wp_strip_all_tags($content));
        $title_length = strlen($title);

        // Compute metrics arrays
        $seo_flags = array(
            'title_length_status' => ($title_length > 60) ? 'Too Long' : (($title_length < 30) ? 'Too Short' : 'Optimal'),
            'content_depth_status' => ($word_count < 300) ? 'Thin Content Warning' : 'Good Depth',
            'word_count' => $word_count
        );

        // Dynamically save metrics structural payload safely for meta analysis references
        if (!empty($postarr['ID'])) {
            update_post_meta($postarr['ID'], '_apb_content_seo_telemetry', $seo_flags);
        }
    }
    return $data;
}

/**
 * FEATURE 4: Instant Automated Search Indexer Console Push Loop
 * Extends basic indexing functions into an instantaneous priority fallback system.
 */
function apb_execute_priority_indexer_push($url, $post_id) {
    if (empty($url)) return false;

    // Simulate standard Google/Bing API payload handshakes natively
    $endpoint = 'https://api.indexnow.org/';
    $key = get_option('apb_indexnow_key');
    
    if (!$key) return false;

    $payload = array(
        'host'        => parse_url(home_url(), PHP_URL_HOST),
        'key'         => $key,
        'keyLocation' => home_url('/' . $key . '.txt'),
        'urlList'     => array($url)
    );

    $response = wp_remote_post($endpoint, array(
        'method'    => 'POST',
        'timeout'   => 15,
        'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'      => json_encode($payload),
        'data_format'=> 'body'
    ));

    return !is_wp_error($response);
}

// Initial System Activation Setup
register_activation_hook(__FILE__, 'apb_activate_plugin');
function apb_activate_plugin() {
    if (get_option('apb_enable_auto_submit') === false) {
        update_option('apb_enable_auto_submit', '1');
    }
    if (get_option('apb_allowed_post_types') === false) {
        update_option('apb_allowed_post_types', array('post', 'page', 'product'));
    }
    if (get_option('apb_enable_xml_sitemap') === false) {
        update_option('apb_enable_xml_sitemap', '1');
    }
    if (get_option('apb_enable_url_optimizer') === false) {
        update_option('apb_enable_url_optimizer', '1');
    }
    
    apb_register_sitemap_rewrite_rule();
    flush_rewrite_rules();
}

// Register Native WordPress Rules for the XML Sitemap
add_action('init', 'apb_register_sitemap_rewrite_rule');
function apb_register_sitemap_rewrite_rule() {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?apb_xml_sitemap=1', 'top');
}

// Whitelist Query Variable
add_filter('query_vars', 'apb_register_sitemap_query_var');
function apb_register_sitemap_query_var($vars) {
    $vars[] = 'apb_xml_sitemap';
    return $vars;
}

// Dynamic Runtime Parsing for sitemap.xml Execution Routing
add_action('template_redirect', 'apb_render_dynamic_xml_sitemap');
function apb_render_dynamic_xml_sitemap() {
    if (get_query_var('apb_xml_sitemap') == '1') {
        if (get_option('apb_enable_xml_sitemap') !== '1') {
            return;
        }

        // Clean out outputs to secure headers
        if (ob_get_length()) ob_clean();

        header('Content-Type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(includes_url('css/dist/block-library/style.css')) . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $allowed_types = get_option('apb_allowed_post_types', array('post', 'page', 'product'));
        $query_args = array(
            'post_type'      => $allowed_types,
            'post_status'    => 'publish',
            'posts_per_page' => 200, // Expanded threshold limit
            'orderby'        => 'modified',
            'order'          => 'DESC'
        );
        $posts = get_posts($query_args);

        foreach ($posts as $p) {
            echo '<url>';
            echo '<loc>' . esc_url(get_permalink($p->ID)) . '</loc>';
            echo '<lastmod>' . esc_html(mysql2date('Y-m-d\TH:i:s+00:00', $p->post_modified_gmt, false)) . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.8</priority>';
            echo '</url>';
        }

        echo '</urlset>';
        exit;
    }
}

// Monitor status transitions to catch actual new publications safely
add_action('transition_post_status', 'apb_check_status_transition', 10, 3);
function apb_check_status_transition($new_status, $old_status, $post) {
    if (get_option('apb_enable_auto_submit') !== '1') {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type === 'revision') return;

    if ($new_status === 'publish' && $old_status !== 'publish') {
        $allowed_types = get_option('apb_allowed_post_types', array('post', 'page', 'product'));
        if (!is_array($allowed_types) || !in_array($post->post_type, $allowed_types)) {
            return;
        }
        $url = get_permalink($post->ID);
        
        // Execute primary core indexing engine
        apb_send_to_indexers($url, $post->ID, $post->post_type);
        
        // Push to Priority Feature Indexer Module simultaneously
        apb_execute_priority_indexer_push($url, $post->ID);
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

// Global Dynamic Frontend Head Meta Injection Module
add_action('wp_head', 'apb_inject_webmaster_meta_tags', 1);
function apb_inject_webmaster_meta_tags() {
    $google = get_option('apb_wm_google');
    $bing   = get_option('apb_wm_bing');
    $pin    = get_option('apb_wm_pinterest');
    $baidu  = get_option('apb_wm_baidu');

    if (!empty($google)) echo "\n<meta name=\"google-site-verification\" content=\"" . esc_attr($google) . "\" />\n";
    if (!empty($bing))   echo "<meta name=\"msvalidate.01\" content=\"" . esc_attr($bing) . "\" />\n";
    if (!empty($pin))    echo "<meta name=\"p:domain_verify\" content=\"" . esc_attr($pin) . "\" />\n";
    if (!empty($baidu))  echo "<meta name=\"baidu-site-verification\" content=\"" . esc_attr($baidu) . "\" />\n";

    if (is_singular()) {
        global $post;
        
        $meta_title = get_post_meta($post->ID, '_apb_meta_title', true);
        if (empty($meta_title)) {
            $meta_title = get_the_title($post->ID) . ' - ' . get_bloginfo('name');
        }

        $meta_desc = get_post_meta($post->ID, '_apb_meta_description', true);
        if (empty($meta_desc)) {
            $meta_desc = wp_strip_all_tags(strip_shortcodes($post->post_content));
            $meta_desc = wp_html_excerpt($meta_desc, 155, '...');
        }

        $meta_img_id = get_post_meta($post->ID, '_apb_meta_image_id', true);
        $meta_img_url = !empty($meta_img_id) ? wp_get_attachment_url($meta_img_id) : get_the_post_thumbnail_url($post->ID, 'large');

        $permalink = get_permalink($post->ID);

        echo "\n\n";
        echo "<meta name=\"description\" content=\"" . esc_attr($meta_desc) . "\" />\n";
        echo "<meta property=\"og:locale\" content=\"" . esc_attr(get_locale()) . "\" />\n";
        echo "<meta property=\"og:type\" content=\"article\" />\n";
        echo "<meta property=\"og:title\" content=\"" . esc_attr($meta_title) . "\" />\n";
        echo "<meta property=\"og:description\" content=\"" . esc_attr($meta_desc) . "\" />\n";
        echo "<meta property=\"og:url\" content=\"" . esc_url($permalink) . "\" />\n";
        echo "<meta property=\"og:site_name\" content=\"" . esc_attr(get_bloginfo('name')) . "\" />\n";
        if (!empty($meta_img_url)) {
            echo "<meta property=\"og:image\" content=\"" . esc_url($meta_img_url) . "\" />\n";
        }

        echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
        echo "<meta name=\"twitter:title\" content=\"" . esc_attr($meta_title) . "\" />\n";
        echo "<meta name=\"twitter:description\" content=\"" . esc_attr($meta_desc) . "\" />\n";
        if (!empty($meta_img_url)) {
            echo "<meta name=\"twitter:image\" content=\"" . esc_url($meta_img_url) . "\" />\n";
        }
        
        if (function_exists('is_product') && is_product() && $post->post_type === 'product') {
            $product = wc_get_product($post->ID);
            if ($product) {
                echo "<meta property=\"product:price:amount\" content=\"" . esc_attr($product->get_price()) . "\" />\n";
                echo "<meta property=\"product:price:currency\" content=\"" . esc_attr(get_woocommerce_currency()) . "\" />\n";
            }
        }
        echo "\n";
    }
}

// --- URL OPTIMIZER: Remove Stop Words from Slug ---
add_filter('wp_unique_post_slug', 'apb_optimize_post_url_slug', 10, 6);
function apb_optimize_post_url_slug($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
    if (get_option('apb_enable_url_optimizer') !== '1') {
        return $slug;
    }

    $stop_words = array(
        'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'arent', 'as', 'at',
        'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'cant', 'cannot', 'could',
        'couldnt', 'did', 'didnt', 'do', 'does', 'doesnt', 'doing', 'dont', 'down', 'during', 'each', 'few', 'for', 'from',
        'further', 'had', 'hadnt', 'has', 'hasnt', 'have', 'havent', 'having', 'he', 'hed', 'hell', 'hes', 'her', 'here',
        'heres', 'herself', 'him', 'himself', 'his', 'how', 'hows', 'i', 'id', 'ill', 'im', 'ive', 'if', 'in', 'into',
        'is', 'isnt', 'it', 'its', 'itself', 'lets', 'me', 'more', 'most', 'mustnt', 'my', 'myself', 'no', 'nor', 'not', 'of',
        'off', 'on', 'once', 'only', 'or', 'other', 'ought', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same', 'shant',
        'she', 'shed', 'shell', 'shes', 'should', 'shouldnt', 'so', 'some', 'such', 'than', 'that', 'thats', 'the', 'their',
        'theirs', 'them', 'themselves', 'then', 'there', 'theres', 'these', 'they', 'theyd', 'theyll', 'theyre', 'theyve',
        'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'wasnt', 'we', 'wed', 'well', 'were',
        'weve', 'werent', 'what', 'whats', 'when', 'whens', 'where', 'wheres', 'which', 'while', 'who', 'whos', 'whom',
        'why', 'whys', 'with', 'wont', 'would', 'wouldnt', 'you', 'youd', 'youll', 'youre', 'youve', 'your', 'yours',
        'yourself', 'yourselves'
    );

    $slug_parts = explode('-', $slug);
    $clean_parts = array();

    foreach ($slug_parts as $part) {
        if (!in_array(strtolower($part), $stop_words) || is_numeric($part)) {
            $clean_parts[] = $part;
        }
    }

    return empty($clean_parts) ? $slug : implode('-', $clean_parts);
}

// --- DYNAMIC ROBOTS.TXT MODIFIER ---
add_filter('robots_txt', 'apb_append_custom_robots_rules', 100, 2);
function apb_append_custom_robots_rules($output, $public) {
    $custom_rules = get_option('apb_robots_txt_content');
    if (!empty($custom_rules)) {
        $output .= "\n# Auto Ping Booster Custom Rules\n" . $custom_rules . "\n";
    }
    if (get_option('apb_enable_xml_sitemap') === '1') {
        $output .= "Sitemap: " . esc_url(home_url('/sitemap.xml')) . "\n";
    }
    return $output;
}

// --- SHORTCODE ENGINE FOR THE VISUAL HTML SITEMAP ---
add_shortcode('apb_html_sitemap', 'apb_render_html_sitemap_shortcode');
function apb_render_html_sitemap_shortcode() {
    $allowed_types = get_option('apb_allowed_post_types', array('post', 'page', 'product'));
    $output = '<div class="apb-html-sitemap-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:30px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin:25px 0; font-family:-apple-system,BlinkMacSystemFont,sans-serif;">';

    foreach ($allowed_types as $type) {
        $post_type_obj = get_post_type_object($type);
        if (!$post_type_obj) continue;

        $posts = get_posts(array(
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        if (empty($posts)) continue;

        $output .= '<div class="sitemap-type-section" style="margin-bottom:30px;">';
        $output .= '<h3 class="sitemap-section-title" style="margin:0 0 15px 0; font-size:18px; font-weight:700; color:#1e293b; border-bottom:2px solid #f1f5f9; padding-bottom:8px; display:flex; align-items:center;"><span style="color:#3b82f6; margin-right:8px;">📁</span> ' . esc_html($post_type_obj->labels->name) . '</h3>';
        $output .= '<ul class="sitemap-links-list" style="list-style:none; padding:0; margin:0; display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:10px;">';
        
        foreach ($posts as $p) {
            $output .= '<li style="margin:0; padding:0;"><a href="' . esc_url(get_permalink($p->ID)) . '" style="color:#475569; text-decoration:none; font-size:14px; display:flex; align-items:center; padding:6px 10px; border-radius:6px; background:#f8fafc; transition:all 0.2s;" onmouseover="this.style.background=\'#edf2f7\';this.style.color=\'#2563eb\'" onmouseout="this.style.background=\'#f8fafc\';this.style.color=\'#475569\'"><span style="margin-right:6px; opacity:0.6;">📄</span> ' . esc_html(get_the_title($p->ID)) . '</a></li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}