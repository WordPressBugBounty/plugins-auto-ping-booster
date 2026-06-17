<?php
if (!defined('ABSPATH')) exit;

/**
 * Automatically inject post/product titles into missing or empty image ALT tags
 * Hooks into standard post content and WooCommerce loop pipelines.
 */
add_filter('the_content', 'apb_fallback_empty_image_alts', 100);
add_filter('post_thumbnail_html', 'apb_fallback_thumbnail_alts', 100, 3);

// For WooCommerce specific product loops, galleries, and variations
add_filter('woocommerce_single_product_image_thumbnail_html', 'apb_fallback_wc_gallery_alts', 100, 2);
add_filter('woocommerce_product_get_image', 'apb_fallback_wc_loop_alts', 100, 5);

/**
 * 1. Post/Product Body Content XML Scanner
 */
function apb_fallback_empty_image_alts($content) {
    if (empty($content)) return $content;

    $global_title = get_the_title();
    if (empty($global_title)) return $content;

    return preg_replace_callback('/<img([^>]+)>/i', function($matches) use ($global_title) {
        $img_meta_attributes = $matches[1];

        if (stripos($img_meta_attributes, 'alt=') === false) {
            return sprintf('<img alt="%s" %s>', esc_attr($global_title), $img_meta_attributes);
        }

        if (preg_match('/alt=([\'"])\s*\1/i', $img_meta_attributes)) {
            $img_meta_attributes = preg_replace('/alt=([\'"])\s*\1/i', 'alt="' . esc_attr($global_title) . '"', $img_meta_attributes);
            return '<img' . $img_meta_attributes . '>';
        }

        return $matches[0];
    }, $content);
}

/**
 * 2. Core Post Thumbnail / Featured Image Filter Injection
 */
function apb_fallback_thumbnail_alts($html, $post_id, $post_thumbnail_id) {
    if (empty($html)) return $html;
    
    $fallback_title = get_the_title($post_id);
    if (empty($fallback_title)) return $html;

    if (stripos($html, 'alt=""') !== false || stripos($html, "alt=''") !== false || stripos($html, 'alt=') === false) {
        if (stripos($html, 'alt=') === false) {
            $html = str_replace('<img', '<img alt="' . esc_attr($fallback_title) . '"', $html);
        } else {
            $html = preg_replace('/alt=([\'"])\s*\1/i', 'alt="' . esc_attr($fallback_title) . '"', $html);
        }
    }
    return $html;
}

/**
 * 3. WooCommerce Single Product Gallery Injection
 */
function apb_fallback_wc_gallery_alts($html, $attachment_id) {
    if (empty($html)) return $html;
    
    $product_title = get_the_title(); 
    if (empty($product_title)) return $html;

    if (stripos($html, 'alt=""') !== false || stripos($html, 'alt=') === false) {
        if (stripos($html, 'alt=') === false) {
            $html = str_replace('<img', '<img alt="' . esc_attr($product_title) . '"', $html);
        } else {
            $html = preg_replace('/alt=([\'"])\s*\1/i', 'alt="' . esc_attr($product_title) . '"', $html);
        }
    }
    return $html;
}

/**
 * 4. WooCommerce Catalog Grid Loop Images Injection
 */
function apb_fallback_wc_loop_alts($html, $product, $size, $attr, $placeholder) {
    if (empty($html)) return $html;
    
    $product_title = $product->get_name();
    if (empty($product_title)) return $html;

    if (stripos($html, 'alt=""') !== false || stripos($html, 'alt=') === false) {
        if (stripos($html, 'alt=') === false) {
            $html = str_replace('<img', '<img alt="' . esc_attr($product_title) . '"', $html);
        } else {
            $html = preg_replace('/alt=([\'"])\s*\1/i', 'alt="' . esc_attr($product_title) . '"', $html);
        }
    }
    return $html;
}

/**
 * =========================================================================
 * MINIMALIST SCHEMA BACKSTOP ENGINE
 * =========================================================================
 * Dynamically hooks into wp_head to output standard metadata formats.
 * Safely adapts between standard Articles and WooCommerce Product catalogs.
 */
add_action('wp_head', 'apb_inject_minimalist_schema_backstop', 15);

function apb_inject_minimalist_schema_backstop() {
    // Only output schema structures on single item screens (posts, pages, products)
    if (!is_singular()) return;

    $post_id = get_the_ID();
    if (!$post_id) return;

    $schema_payload = [];
    $site_name      = get_bloginfo('name');
    $permalink      = get_permalink($post_id);
    $title          = get_the_title($post_id);
    $excerpt        = get_the_excerpt($post_id);
    $featured_img   = get_the_post_thumbnail_url($post_id, 'full');

    // Default image fallback if no featured image is defined
    if (!$featured_img) {
        $featured_img = esc_url(get_site_icon_url(512));
    }

    // CASE 1: WooCommerce Product Page Matrix
    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product($post_id);
        if ($product) {
            $schema_payload = [
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => esc_html($title),
                'url'         => esc_url($permalink),
                'description' => esc_html(wp_strip_all_tags($excerpt ? $excerpt : $title)),
                'sku'         => esc_html($product->get_sku() ? $product->get_sku() : 'SKU-' . $post_id),
                'offers'      => [
                    '@type'         => 'Offer',
                    'price'         => esc_html($product->get_price() ? $product->get_price() : '0.00'),
                    'priceCurrency' => esc_html(get_woocommerce_currency()),
                    'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url'           => esc_url($permalink)
                ]
            ];
            
            if ($featured_img) {
                $schema_payload['image'] = esc_url($featured_img);
            }
        }
    } 
    // CASE 2: Standard Post / Article Matrix
    elseif (is_single()) {
        $schema_payload = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => esc_html($title),
            'url'              => esc_url($permalink),
            'description'      => esc_html(wp_strip_all_tags($excerpt ? $excerpt : $title)),
            'datePublished'    => esc_html(get_the_date('c', $post_id)),
            'dateModified'     => esc_html(get_the_modified_date('c', $post_id)),
            'author'           => [
                '@type' => 'Person',
                'name'  => esc_html(get_the_author_meta('display_name', get_post_field('post_author', $post_id)))
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => esc_html($site_name),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => esc_url(get_site_icon_url(512))
                ]
            ]
        ];

        if ($featured_img) {
            $schema_payload['image'] = esc_url($featured_img);
        }
    }

    // Render the structured payload cleanly into the site head if data exists
    if (!empty($schema_payload)) {
        echo "\n<!-- Auto Ping Booster Minimalist Schema Backstop -->\n";
        echo '<script type="application/ld+json">' . json_encode($schema_payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n\n";
    }
}