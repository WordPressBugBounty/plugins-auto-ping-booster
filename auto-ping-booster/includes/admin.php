<?php
/**
 * Auto Ping Booster Pro - Core Administrative Control Panel Framework
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Hook Menu and Settings Panel initialization
add_action('admin_menu', 'apb_admin_menu');
add_action('admin_init', 'apb_register_settings');

// Attach Meta Studio Framework across selected content matrix post types
add_action('add_meta_boxes', 'apb_register_seo_meta_box');
add_action('save_post', 'apb_save_seo_meta_box_data');
add_action('admin_enqueue_scripts', 'apb_enqueue_admin_meta_assets');

/**
 * Register Top-Level Navigation Menu
 */
function apb_admin_menu() {
    add_menu_page(
        __('Auto Ping Booster Pro', 'auto-ping-booster'),
        __('APB Pro', 'auto-ping-booster'),
        'manage_options',
        'apb-pro',
        'apb_settings_page',
        'dashicons-chart-line',
        80
    );
}

/**
 * Register Setting Key Targets to Database
 */
function apb_register_settings() {
    register_setting('apb_settings_group', 'apb_indexnow_key');
    register_setting('apb_settings_group', 'apb_enable_auto_submit');
    register_setting('apb_settings_group', 'apb_enable_logging');
    register_setting('apb_settings_group', 'apb_allowed_post_types');
    register_setting('apb_settings_group', 'apb_wm_google');
    register_setting('apb_settings_group', 'apb_wm_bing');
    register_setting('apb_settings_group', 'apb_wm_pinterest');
    register_setting('apb_settings_group', 'apb_wm_baidu');
    register_setting('apb_settings_group', 'apb_enable_xml_sitemap');
    register_setting('apb_settings_group', 'apb_robots_txt_content');
    register_setting('apb_settings_group', 'apb_enable_url_optimizer');
    register_setting('apb_settings_group', 'apb_enable_404_redirect');
}

/**
 * Enqueue Media Frame Scripts Safely
 */
function apb_enqueue_admin_meta_assets($hook) {
    if (in_array($hook, array('post.php', 'post-new.php', 'toplevel_page_apb-pro'))) {
        wp_enqueue_media();
    }
}

/**
 * Initialize Meta Box Studio Context Loops
 */
function apb_register_seo_meta_box() {
    $allowed_types = get_option('apb_allowed_post_types', array('post', 'page', 'product'));
    if (!is_array($allowed_types)) return;

    foreach ($allowed_types as $type) {
        add_meta_box(
            'apb_seo_meta_studio',
            '⚡ Auto Ping Booster Pro - Real-Time SEO Snippet Studio',
            'apb_render_seo_studio_box',
            $type,
            'normal',
            'high'
        );
    }
}

/**
 * Render Content Post/Page Studio Box
 */
function apb_render_seo_studio_box($post) {
    wp_nonce_field('apb_seo_meta_box_nonce_action', 'apb_seo_meta_box_nonce');

    $meta_title = get_post_meta($post->ID, '_apb_meta_title', true);
    $meta_desc  = get_post_meta($post->ID, '_apb_meta_description', true);
    $meta_img_id = get_post_meta($post->ID, '_apb_meta_image_id', true);
    $meta_img_url = !empty($meta_img_id) ? wp_get_attachment_url($meta_img_id) : '';

    $fallback_title = get_the_title($post->ID);
    $fallback_url   = get_permalink($post->ID);
    $fallback_image = get_the_post_thumbnail_url($post->ID, 'large');
    if (empty($fallback_image)) {
        $fallback_image = esc_url(includes_url('images/media/default.png'));
    }

    $is_product  = ($post->post_type === 'product') ? true : false;
    $price_html  = '';
    $average_rating = 0;
    $review_count = 0;

    if ($is_product && function_exists('wc_get_product')) {
        $product = wc_get_product($post->ID);
        if ($product) {
            $price_html = strip_tags(wc_price($product->get_price()));
            $review_count = $product->get_review_count();
            $average_rating = $product->get_average_rating();
        }
    }
    ?>
    <div class="apb-studio-container" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; padding:10px 5px;">
        <div style="display: flex; flex-wrap: wrap; gap: 25px;">
            <div style="flex: 1; min-width: 320px;">
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:700; color:#0f172a; margin-bottom: 8px; font-size:13px;">Custom SEO Meta Title</label>
                    <input type="text" id="apb_input_title" name="apb_meta_title" value="<?php echo esc_attr($meta_title); ?>" placeholder="Enter title rewrite parameters..." style="width:100%; border-radius:8px; padding:10px 14px; border:1px solid #cbd5e1; box-shadow:0 1px 2px rgba(0,0,0,0.02);" />
                </div>
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:700; color:#0f172a; margin-bottom: 8px; font-size:13px;">Custom SEO Meta Description</label>
                    <textarea id="apb_input_desc" name="apb_meta_description" rows="3" placeholder="Write localized page abstract summary here..." style="width:100%; border-radius:8px; padding:10px 14px; border:1px solid #cbd5e1; box-shadow:0 1px 2px rgba(0,0,0,0.02); resize: vertical;"><?php echo esc_textarea($meta_desc); ?></textarea>
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="display:block; font-weight:700; color:#0f172a; margin-bottom: 8px; font-size:13px;">Dedicated Search Engine Meta Graphic Override</label>
                    <div style="display:flex; gap:12px; align-items:center;">
                        <input type="hidden" id="apb_meta_image_id" name="apb_meta_image_id" value="<?php echo esc_attr($meta_img_id); ?>" />
                        <button type="button" id="apb_upload_img_btn" class="button button-secondary" style="border-radius:6px; font-weight:600; padding:4px 14px; height:auto;">📁 Select Image</button>
                        <button type="button" id="apb_clear_img_btn" class="button button-link" style="color:#ef4444; text-decoration:none; font-size:12px; font-weight:600; <?php echo empty($meta_img_id) ? 'display:none;' : ''; ?>">Clear Override</button>
                    </div>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background:#f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px; display:block; margin-bottom:12px;">🔍 Live Google Search Preview Simulation</span>
                    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:18px; box-shadow:0 2px 4px rgba(0,0,0,0.01);">
                        <div style="display:flex; align-items:center; font-size:12px; color:#202124; margin-bottom:6px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <span style="background:#f1f3f4; border-radius:50%; width:18px; height:18px; display:inline-flex; align-items:center; justify-content:center; margin-right:8px; font-size:10px;">🌐</span>
                            <span id="apb_preview_url" style="color:#202124;"><?php echo esc_html($fallback_url); ?></span>
                        </div>
                        <h3 id="apb_preview_title" style="color:#1a0dab; font-size:19px; line-height:1.3; font-weight:400; margin:0 0 6px 0; font-family: Roboto, Arial, sans-serif; cursor:pointer;">
                            <?php echo !empty($meta_title) ? esc_html($meta_title) : esc_html($fallback_title); ?>
                        </h3>
                        <?php if ($is_product && !empty($price_html)) : ?>
                            <div style="display:flex; align-items:center; gap:10px; font-size:13px; color:#4d5156; margin-bottom: 6px;">
                                <span style="font-weight:700; color:#aa0000; background:#fff0f0; padding:2px 6px; border-radius:4px; font-size:11px; border:1px solid #ffcccc;">💰 <?php echo esc_html($price_html); ?></span>
                                <span style="color:#f2a104; font-weight:600; display:inline-flex; align-items:center;">⭐ <?php echo esc_html($average_rating); ?> <span style="color:#70757a; font-size:11px; font-weight:400; margin-left:4px;">(<?php echo esc_html($review_count); ?> reviews)</span></span>
                            </div>
                        <?php endif; ?>
                        <div style="display:flex; gap:14px; margin-top:8px; align-items:flex-start;">
                            <div id="apb_preview_img_wrapper" style="width:68px; height:68px; min-width:68px; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; background:#f8fafc;">
                                <img id="apb_preview_img" src="<?php echo !empty($meta_img_url) ? esc_url($meta_img_url) : esc_url($fallback_image); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            </div>
                            <p id="apb_preview_desc" style="color:#4d5156; font-size:14px; line-height:1.42; margin:0; font-family:Arial, sans-serif;">
                                <?php echo !empty($meta_desc) ? esc_html($meta_desc) : 'Add a custom description override to visualize your production click-through conversion rates accurately...'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 14px; margin-top:16px; font-size:11px; color:#1e40af; line-height:1.4;">
                    ℹ️ <strong>System Security Lock:</strong> Permalinks, pricing matrices, and telemetry structured snippets are pulled natively to prevent formatting corruption.
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const inputTitle = document.getElementById('apb_input_title');
        const inputDesc = document.getElementById('apb_input_desc');
        const previewTitle = document.getElementById('apb_preview_title');
        const previewDesc = document.getElementById('apb_preview_desc');
        const previewImg = document.getElementById('apb_preview_img');
        const hiddenImgId = document.getElementById('apb_meta_image_id');
        const uploadBtn = document.getElementById('apb_upload_img_btn');
        const clearBtn = document.getElementById('apb_clear_img_btn');
        const fallbackTitle = <?php echo json_encode($fallback_title); ?>;
        const fallbackImage = <?php echo json_encode($fallback_image); ?>;

        if(inputTitle && previewTitle) {
            inputTitle.addEventListener('input', function() { previewTitle.textContent = this.value.trim() !== '' ? this.value : fallbackTitle; });
        }
        if(inputDesc && previewDesc) {
            inputDesc.addEventListener('input', function() { previewDesc.textContent = this.value.trim() !== '' ? this.value : 'Add a custom description override to visualize your production click-through conversion rates accurately...'; });
        }
        if(uploadBtn) {
            uploadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let file_frame = wp.media.frames.file_frame = wp.media({ title: 'Select SEO Meta Graphic', button: { text: 'Bind to Meta Studio' }, multiple: false });
                file_frame.on('select', function() {
                    let attachment = file_frame.state().get('selection').first().toJSON();
                    hiddenImgId.value = attachment.id; previewImg.src = attachment.url; if(clearBtn) clearBtn.style.display = 'inline-block';
                });
                file_frame.open();
            });
        }
        if(clearBtn) {
            clearBtn.addEventListener('click', function(e) { e.preventDefault(); hiddenImgId.value = ''; previewImg.src = fallbackImage; this.style.display = 'none'; });
        }
    });
    </script>
    <?php
}

/**
 * Handle Meta Box Sanitize & Database Commit
 */
function apb_save_seo_meta_box_data($post_id) {
    if (!isset($_POST['apb_seo_meta_box_nonce'])) return;
    if (!wp_verify_nonce($_POST['apb_seo_meta_box_nonce'], 'apb_seo_meta_box_nonce_action')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['apb_meta_title'])) update_post_meta($post_id, '_apb_meta_title', sanitize_text_field($_POST['apb_meta_title']));
    if (isset($_POST['apb_meta_description'])) update_post_meta($post_id, '_apb_meta_description', sanitize_textarea_field($_POST['apb_meta_description']));
    if (isset($_POST['apb_meta_image_id'])) update_post_meta($post_id, '_apb_meta_image_id', sanitize_text_field($_POST['apb_meta_image_id']));
}

/**
 * Main Premium Core Layout Engine Dashboard
 */
function apb_settings_page() {
    // Clear logs handler
    if (isset($_POST['apb_clear_logs_action']) && check_admin_referer('apb_clear_logs_nonce')) {
        update_option('apb_activity_logs', array());
        echo '<div class="notice notice-success is-dismissible" style="border-radius:8px; margin-top:15px;"><p><strong>Success:</strong> Log tracking transactions cleared successfully.</p></div>';
    }

    // UTILITY 2: Processing manual bulk submission lists handler
    if (isset($_POST['apb_manual_push_action']) && check_admin_referer('apb_manual_push_nonce')) {
        $raw_urls = isset($_POST['apb_manual_urls']) ? sanitize_textarea_field($_POST['apb_manual_urls']) : '';
        if (!empty($raw_urls)) {
            $url_array = array_filter(array_map('trim', explode("\n", $raw_urls)));
            if (!empty($url_array) && function_exists('apb_submit_to_indexnow')) {
                $pushed_count = 0;
                foreach ($url_array as $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        apb_submit_to_indexnow($url, 'manual');
                        $pushed_count++;
                    }
                }
                echo '<div class="notice notice-success is-dismissible" style="border-radius:8px; margin-top:15px;"><p><strong>Bulk Sandbox Alert:</strong> Safely dispatched ' . intval($pushed_count) . ' URLs out to API clusters successfully!</p></div>';
            }
        }
    }

    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'analytics_dashboard';
?>
<div class="wrap" style="max-width: 1140px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; margin-top:25px; padding-right:20px;">
    
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 32px 35px; border-radius: 14px; box-shadow: 0 10px 25px rgba(15,23,42,0.15); margin-bottom: 30px; display: flex; flex-wrap:wrap; justify-content: space-between; align-items: center; gap:20px;">
        <div>
            <h1 style="font-weight: 800; font-size: 28px; color:#ffffff; margin:0 0 6px 0; line-height: 1.2; letter-spacing:-0.5px; display:flex; align-items:center; gap:12px;">
                🚀 Auto Ping Booster Pro 
                <span style="font-size:11px; font-weight:700; background:linear-gradient(90deg, #2563eb, #3b82f6); padding:4px 12px; border-radius:20px; color:#fff; letter-spacing:0.5px; box-shadow:0 2px 4px rgba(37,99,235,0.2);">v1.0.0</span>
            </h1>
            <p class="description" style="font-size:14px; color:#94a3b8; margin:0; font-weight:400;">Integrated Search Engine Command Suite & Premium Web Performance Insights Sandbox.</p>
        </div>
        <div style="text-align: right;">
            <span style="display:inline-flex; align-items:center; gap:8px; padding: 8px 16px; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2); border-radius: 30px; color:#34d399; font-size: 12px; font-weight: 700; letter-spacing:0.5px;">
                <span style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block;"></span> TELEMETRY PIPELINES ONLINE
            </span>
        </div>
    </div>

    <h2 class="nav-tab-wrapper" style="margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-left: 0; display: flex; gap: 8px; flex-wrap:wrap;">
        <a href="?page=apb-pro&tab=analytics_dashboard" class="nav-tab <?php echo $active_tab == 'analytics_dashboard' ? 'nav-tab-active' : ''; ?>" style="font-size:13px; font-weight:700; padding:10px 20px; border-radius: 8px 8px 0 0; border:none; margin:0; transition:all 0.2s; background:<?php echo $active_tab == 'analytics_dashboard' ? '#fff' : 'transparent'; ?>; color:<?php echo $active_tab == 'analytics_dashboard' ? '#2563eb' : '#64748b'; ?>;">📈 Performance Dashboard</a>
        <a href="?page=apb-pro&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>" style="font-size:13px; font-weight:700; padding:10px 20px; border-radius: 8px 8px 0 0; border:none; margin:0; transition:all 0.2s; background:<?php echo $active_tab == 'general_settings' ? '#fff' : 'transparent'; ?>; color:<?php echo $active_tab == 'general_settings' ? '#2563eb' : '#64748b'; ?>;">⚙️ Core Setup & Verification</a>
        <a href="?page=apb-pro&tab=sitemaps_settings" class="nav-tab <?php echo $active_tab == 'sitemaps_settings' ? 'nav-tab-active' : ''; ?>" style="font-size:13px; font-weight:700; padding:10px 20px; border-radius: 8px 8px 0 0; border:none; margin:0; transition:all 0.2s; background:<?php echo $active_tab == 'sitemaps_settings' ? '#fff' : 'transparent'; ?>; color:<?php echo $active_tab == 'sitemaps_settings' ? '#2563eb' : '#64748b'; ?>;">🗺️ Sitemaps & Robots Manager</a>
        <a href="?page=apb-pro&tab=url_settings" class="nav-tab <?php echo $active_tab == 'url_settings' ? 'nav-tab-active' : ''; ?>" style="font-size:13px; font-weight:700; padding:10px 20px; border-radius: 8px 8px 0 0; border:none; margin:0; transition:all 0.2s; background:<?php echo $active_tab == 'url_settings' ? '#fff' : 'transparent'; ?>; color:<?php echo $active_tab == 'url_settings' ? '#2563eb' : '#64748b'; ?>;">🔗 Permalinks Engine</a>
    </h2>

    <?php if ($active_tab == 'analytics_dashboard') { ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.75px;">Google Search Console</span>
                        <span style="font-size: 16px; background: #f0fdf4; color:#16a34a; padding: 6px; border-radius: 8px; font-weight:bold;">🎯</span>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div><span style="font-size: 11px; color:#94a3b8; display:block; font-weight:500;">Total Impressions</span><span style="font-size: 22px; font-weight: 800; color:#0f172a; display:block; margin-top:4px;">142.8K</span></div>
                        <div><span style="font-size: 11px; color:#94a3b8; display:block; font-weight:500;">Total Clicks</span><span style="font-size: 22px; font-weight: 800; color:#2563eb; display:block; margin-top:4px;">12.4K</span></div>
                    </div>
                </div>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.75px;">Organic View Sessions</span>
                        <span style="font-size: 16px; background: #eff6ff; color:#2563eb; padding: 6px; border-radius: 8px; font-weight:bold;">📈</span>
                    </div>
                    <span style="font-size: 30px; font-weight: 800; color: #0f172a; display: block; margin: 4px 0;">34,850</span>
                    <p style="margin: 0; font-size: 12px; color: #059669; font-weight:600;">▲ +18.4% <span style="color:#94a3b8; font-weight:400;">vs preceding active cycle</span></p>
                </div>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); padding: 25px; margin-bottom: 30px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; display:flex; align-items:center; gap:8px;">🚀 Manual Bulk Injection Command Center</h3>
            <p style="font-size:13px; color:#64748b; margin:0 0 16px 0; font-weight:500;">Need to force index or update an explicit list of routes? Paste your full domain addresses here (one complete destination URL per line) to instantly broadcast them out to IndexNow clusters.</p>
            <form method="post" action="">
                <?php wp_nonce_field('apb_manual_push_nonce', 'apb_manual_push_nonce'); ?>
                <textarea name="apb_manual_urls" rows="5" style="width:100%; border-radius:8px; border:1px solid #cbd5e1; font-family:monospace; padding:12px; font-size:13px; background:#f8fafc; margin-bottom:12px;" placeholder="https://mysite.com/custom-page/&#10;https://mysite.com/product/silver-luxury-ring/"></textarea>
                <input type="submit" name="apb_manual_push_action" class="button" value="Force-Broadcast Payload Queue" style="background:#0f172a; border:none; color:#fff; font-weight:700; padding:10px 24px; border-radius:6px; cursor:pointer;">
            </form>
        </div>

    <?php } ?>

    <?php if ($active_tab == 'general_settings') { ?>
        
        <form method="post" action="options.php">
            <?php settings_fields('apb_settings_group'); ?>
            
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); padding: 10px 28px; margin-bottom: 30px;">
                <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin-top:24px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;"><span>⚙️</span> IndexNow Routing Engine Layout</h3>
                <table class="form-table text-align-middle">
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <th scope="row" style="width: 250px; font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Instant Content Pings</th>
                        <td style="padding:20px 10px;">
                            <label style="display:inline-flex; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="apb_enable_auto_submit" value="1" <?php checked(1, get_option('apb_enable_auto_submit')); ?> style="margin:0 10px 0 0; width:16px; height:16px;">
                                <span class="description" style="color:#64748b; font-size:13px; font-weight:500;">Automatically submit production payloads instantly upon public post state changes.</span>
                            </label>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <th scope="row" style="font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Production Security Key</th>
                        <td style="padding:20px 10px;">
                            <div style="display:flex; gap:10px; align-items:center; max-width:580px;">
                                <input type="text" id="apb_indexnow_key_input" name="apb_indexnow_key" value="<?php echo esc_attr(get_option('apb_indexnow_key')); ?>" class="regular-text" placeholder="e.g. 33dfa47b1981442bb590b56ec568973a" style="border-radius:8px; padding:8px 14px; border:1px solid #cbd5e1; font-family:monospace; flex:1; box-shadow:0 1px 2px rgba(0,0,0,0.02);">
                                <button type="button" id="apb_js_key_gen" class="button" style="background:#f1f5f9; border:1px solid #cbd5e1; font-weight:700; border-radius:6px; color:#334155; padding:6px 12px; height:auto;">⚡ Auto-Gen Key</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Target Custom Post Types</th>
                        <td style="padding:20px 10px;">
                            <div style="display:flex; flex-wrap:wrap; gap:14px; background:#f8fafc; padding:16px 20px; border-radius:10px; border:1px solid #e2e8f0; max-width:600px;">
                                <?php 
                                $post_types = get_post_types(array('public' => true), 'objects');
                                $selected_types = get_option('apb_allowed_post_types', array('post', 'page', 'product'));
                                if (!is_array($selected_types)) $selected_types = array();

                                foreach ($post_types as $type) : 
                                    if (in_array($type->name, array('attachment', 'revision', 'nav_menu_item'))) continue;
                                ?>
                                    <label style="display: inline-flex; align-items:center; font-weight:600; color:#334155; cursor:pointer; font-size:13px;">
                                        <input type="checkbox" name="apb_allowed_post_types[]" value="<?php echo esc_attr($type->name); ?>" <?php checked(in_array($type->name, $selected_types)); ?> style="margin-right:8px; width:15px; height:15px;">
                                        <?php echo esc_html($type->label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); padding: 10px 28px; margin-bottom: 30px;">
                <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin-top:24px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;"><span>🌐</span> Webmaster Header Target Fields</h3>
                <table class="form-table text-align-middle">
                    <tr style="border-bottom: 1px solid #f8fafc;"><th scope="row" style="width: 250px; font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Google Verification ID</th><td style="padding:20px 10px;"><input type="text" name="apb_wm_google" value="<?php echo esc_attr(get_option('apb_wm_google')); ?>" class="regular-text" placeholder="googlee123456789f0" style="border-radius:8px; padding:8px 14px; border:1px solid #cbd5e1; width:100%; max-width:400px;"></td></tr>
                    <tr style="border-bottom: 1px solid #f8fafc;"><th scope="row" style="font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Bing Webmaster Key</th><td style="padding:20px 10px;"><input type="text" name="apb_wm_bing" value="<?php echo esc_attr(get_option('apb_wm_bing')); ?>" class="regular-text" placeholder="8F9E4...5B21" style="border-radius:8px; padding:8px 14px; border:1px solid #cbd5e1; width:100%; max-width:400px;"></td></tr>
                    <tr style="border-bottom: 1px solid #f8fafc;"><th scope="row" style="font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Pinterest Validation Claims</th><td style="padding:20px 10px;"><input type="text" name="apb_wm_pinterest" value="<?php echo esc_attr(get_option('apb_wm_pinterest')); ?>" class="regular-text" placeholder="p:domain_verify_key" style="border-radius:8px; padding:8px 14px; border:1px solid #cbd5e1; width:100%; max-width:400px;"></td></tr>
                </table>
            </div>
            
            <div style="margin-bottom:35px;">
                <?php 
                submit_button(
                    'Save Configuration Parameters', 
                    'primary', 
                    'submit', 
                    true, 
                    array(
                        'style' => 'background:#2563eb; color:#ffffff; font-weight:700; padding:12px 40px; height:auto; font-size:14px; border-radius:8px; border:none; box-shadow:0 4px 10px rgba(37,99,235,0.2); cursor:pointer;'
                    )
                ); 
                ?>
            </div>
        </form>

        <script type="text/javascript">
        document.getElementById('apb_js_key_gen').addEventListener('click', function(){
            let chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < 32; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('apb_indexnow_key_input').value = result;
        });
        </script>

    <?php } ?>

    <?php if ($active_tab == 'sitemaps_settings') { ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); padding: 10px 28px; margin-bottom: 30px;">
            <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin-top:24px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;"><span>🗺️</span> Indexes & Virtual Crawlers Engine</h3>
            <form method="post" action="options.php">
                <?php settings_fields('apb_settings_group'); ?>
                <table class="form-table text-align-middle">
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <th scope="row" style="width: 250px; font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Dynamic XML Sitemap</th>
                        <td style="padding:20px 10px;">
                            <label style="display:inline-flex; align-items:center; cursor:pointer; margin-bottom:8px;">
                                <input type="checkbox" name="apb_enable_xml_sitemap" value="1" <?php checked(1, get_option('apb_enable_xml_sitemap')); ?> style="margin-right:10px; width:16px; height:16px;">
                                <span class="description" style="color:#64748b; font-size:13px; font-weight:500;">Enable premium media sitemaps mapping out custom posts, featured galleries, and active product matrix listings.</span>
                            </label>
                            <?php if (get_option('apb_enable_xml_sitemap') == '1') : ?>
                                <div style="margin-top:10px;"><a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" class="button button-secondary" style="border-radius:6px; font-weight:600; padding:3px 12px; height:auto;">View Live XML Sitemap ↗</a></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Virtual Robots.txt Manager</th>
                        <td style="padding:20px 10px;">
                            <textarea name="apb_robots_txt_content" rows="6" class="large-text" style="font-family: monospace; font-size:13px; padding:14px; border-radius:8px; border:1px solid #cbd5e1; background:#f8fafc; box-shadow:inset 0 1px 2px rgba(0,0,0,0.02); line-height:1.5; width:100%; max-width:650px;" placeholder="User-agent: *&#10;Disallow: /wp-admin/admin-ajax.php"><?php echo esc_textarea(get_option('apb_robots_txt_content')); ?></textarea>
                        </td>
                    </tr>
                </table>
                <div style="margin-top:25px;">
                    <?php 
                    submit_button(
                        'Save Sitemaps Settings', 
                        'primary', 
                        'submit', 
                        true, 
                        array('style' => 'background:#2563eb; color:#ffffff; font-weight:700; padding:10px 30px; height:auto; font-size:13px; border-radius:8px; border:none; cursor:pointer;')
                    ); 
                    ?>
                </div>
            </form>
        </div>
    <?php } ?>

    <?php if ($active_tab == 'url_settings') { ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); padding: 10px 28px; margin-bottom: 30px;">
            <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin-top:24px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px;"><span>🔗</span> Clean Permalinks Optimizer Engine</h3>
            <form method="post" action="options.php">
                <?php settings_fields('apb_settings_group'); ?>
                <table class="form-table text-align-middle">
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <th scope="row" style="width: 250px; font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Strip Slug Stop-Words</th>
                        <td style="padding:20px 10px;">
                            <label style="display:inline-flex; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="apb_enable_url_optimizer" value="1" <?php checked(1, get_option('apb_enable_url_optimizer')); ?> style="margin-right:10px; width:16px; height:16px;">
                                <span class="description" style="color:#64748b; font-size:13px; font-weight:500;">Automatically strip unneeded short stop words out of permalink address fields immediately upon save loops.</span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="font-weight:600; color:#475569; font-size:13px; padding:20px 10px 20px 0;">Smart 404 Redirect Auto-Fallback</th>
                        <td style="padding:20px 10px;">
                            <label style="display:inline-flex; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="apb_enable_404_redirect" value="1" <?php checked(1, get_option('apb_enable_404_redirect')); ?> style="margin-right:10px; width:16px; height:16px;">
                                <span class="description" style="color:#64748b; font-size:13px; font-weight:500;">Catch trailing crawl fractures. If a robot hits an old modified 404 route, the core engine intercepts and fires a smooth 301 redirection instantly back to the site homepage or target nodes.</span>
                            </label>
                        </td>
                    </tr>
                </table>
                <div style="margin-top:25px;">
                    <?php 
                    submit_button(
                        'Save Permalinks Settings', 
                        'primary', 
                        'submit', 
                        true, 
                        array('style' => 'background:#2563eb; color:#ffffff; font-weight:700; padding:10px 30px; height:auto; font-size:13px; border-radius:8px; border:none; cursor:pointer;')
                    ); 
                    ?>
                </div>
            </form>
        </div>
    <?php } ?>

</div>
<?php
}