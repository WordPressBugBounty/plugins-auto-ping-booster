<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'apb_admin_menu');
add_action('admin_init', 'apb_register_settings');

function apb_admin_menu() {
    add_menu_page(
        'Auto Ping Booster Pro',
        'APB Pro',
        'manage_options',
        'apb-pro',
        'apb_settings_page',
        'dashicons-chart-line',
        80
    );
}

function apb_register_settings() {
    register_setting('apb_settings_group', 'apb_indexnow_key');
    register_setting('apb_settings_group', 'apb_enable_auto_submit');
    register_setting('apb_settings_group', 'apb_enable_logging');
}

function apb_settings_page() {
?>
<div class="wrap" style="max-width: 900px; font-family: sans-serif;">
    <h1 style="font-weight: 700; color:#23282d;">Auto Ping Booster Pro Dash</h1>
    <p class="description">Modern, automated infrastructure built to instantly crawl your web content.</p>
    
    <hr style="margin:20px 0; border:0; border-top:1px solid #ccd0d4;">

    <form method="post" action="options.php">
        <?php
        settings_fields('apb_settings_group');
        do_settings_sections('apb_settings_group');
        ?>

        <table class="form-table" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <th scope="row" style="width: 250px;"><strong>IndexNow Engine Activation</strong></th>
                <td>
                    <input type="checkbox" name="apb_enable_auto_submit" value="1" <?php checked(1, get_option('apb_enable_auto_submit')); ?>>
                    <span class="description">Automatically ping Bing, Yandex, & Seznam instantly upon public post transitions.</span>
                </td>
            </tr>

            <tr>
                <th scope="row"><strong>IndexNow API Key</strong></th>
                <td>
                    <input type="text" name="apb_indexnow_key" value="<?php echo esc_attr(get_option('apb_indexnow_key')); ?>" class="regular-text" placeholder="e.g. 33dfa47b1981442bb590b56ec568973a">
                    <p class="description">Your dynamic identification string will verify itself natively with target search spiders.</p>
                </td>
            </tr>

            <tr style="border-top: 1px solid #f0f0f1;">
                <th scope="row"><strong>Debug Engine Logs</strong></th>
                <td>
                    <input type="checkbox" name="apb_enable_logging" value="1" <?php checked(1, get_option('apb_enable_logging')); ?>>
                    <span class="description">Track server network payloads locally inside internal <code>debug.log</code> records.</span>
                </td>
            </tr>

            <!-- Upsell Frameworks Section -->
            <tr style="border-top: 2px dashed #ccd0d4; background: #f9f9f9;">
                <th scope="row" style="color: #50575e;">🔒 Google Indexing API (Pro Only)</th>
                <td>
                    <input type="text" disabled class="regular-text" placeholder="Upload Service JSON Key File">
                    <span class="badge" style="background:#007cba; color:#fff; padding:3px 8px; border-radius:4px; font-size:11px; margin-left:10px;">Upgrade to Pro</span>
                </td>
            </tr>

            <tr style="background: #f9f9f9;">
                <th scope="row" style="color: #50575e;">🧠 Autonomous AI Schema (AI Tier)</th>
                <td>
                    <input type="checkbox" disabled> <span class="description" style="color:#72777c;">Automate JSON-LD generation and dynamic semantic structure creation via API proxy.</span>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Synchronization Fields', 'primary', 'submit', true, array('style' => 'margin-top:20px; padding: 6px 30px; font-size: 14px;')); ?>
    </form>
</div>
<?php
}