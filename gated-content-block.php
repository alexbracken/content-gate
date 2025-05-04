<?php
/**
 * Plugin Name: Gated Content Block
 * Description: Adds a Gutenberg block to gate content behind a name/email form with reCAPTCHA v3 and AJAX, storing submissions in the database.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2+
 */

if (!defined('ABSPATH')) exit;

define('GCB_VERSION', '1.0.0');
define('GCB_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Activation: create DB table
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $table = $wpdb->prefix . 'gcb_submissions';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        post_id BIGINT UNSIGNED NOT NULL,
        submitted_at DATETIME NOT NULL,
        ip_address VARCHAR(45) NULL
    ) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// Enqueue block assets
add_action('init', function() {
    // Register block
    include_once GCB_PLUGIN_DIR . 'build/register-block.php';
});

// REST API endpoint for AJAX
add_action('rest_api_init', function() {
    register_rest_route('gcb/v1', '/submit', [
        'methods' => 'POST',
        'callback' => 'gcb_handle_submission',
        'permission_callback' => '__return_true'
    ]);
});

function gcb_handle_submission($request) {
    $params = $request->get_json_params();
    $name = sanitize_text_field($params['name'] ?? '');
    $email = sanitize_email($params['email'] ?? '');
    $post_id = intval($params['post_id'] ?? 0);
    $recaptcha = sanitize_text_field($params['recaptcha'] ?? '');
    $remember = !empty($params['remember']);

    // Validate
    if (!$name || !$email || !$post_id || !$recaptcha) {
        return new WP_Error('missing_fields', 'Missing required fields', ['status' => 400]);
    }

    // reCAPTCHA v3 verification
    $site_secret = get_option('gcb_recaptcha_secret');
    if (!$site_secret) {
        return new WP_Error('recaptcha_not_configured', 'reCAPTCHA not configured', ['status' => 500]);
    }
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret' => $site_secret,
            'response' => $recaptcha,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]
    ]);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['success']) || $body['score'] < 0.5) {
        return new WP_Error('recaptcha_failed', 'reCAPTCHA failed', ['status' => 403]);
    }

    // Store in DB
    global $wpdb;
    $table = $wpdb->prefix . 'gcb_submissions';
    $wpdb->insert($table, [
        'name' => $name,
        'email' => $email,
        'post_id' => $post_id,
        'submitted_at' => current_time('mysql'),
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);

    return [
        'success' => true,
        'remember' => $remember,
        'message' => 'Thank you! The content is now unlocked.'
    ];
}

// Admin menu for settings
add_action('admin_menu', function() {
    add_options_page(
        'Gated Content Block Settings',
        'Gated Content Block',
        'manage_options',
        'gcb-settings',
        'gcb_settings_page'
    );
});

function gcb_settings_page() {
    if (isset($_POST['gcb_save'])) {
        check_admin_referer('gcb_settings');
        update_option('gcb_recaptcha_site', sanitize_text_field($_POST['gcb_recaptcha_site']));
        update_option('gcb_recaptcha_secret', sanitize_text_field($_POST['gcb_recaptcha_secret']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    $site = esc_attr(get_option('gcb_recaptcha_site', ''));
    $secret = esc_attr(get_option('gcb_recaptcha_secret', ''));
    ?>
    <div class="wrap">
        <h1>Gated Content Block Settings</h1>
        <form method="post">
            <?php wp_nonce_field('gcb_settings'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="gcb_recaptcha_site">reCAPTCHA v3 Site Key</label></th>
                    <td><input type="text" name="gcb_recaptcha_site" id="gcb_recaptcha_site" value="<?php echo $site; ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="gcb_recaptcha_secret">reCAPTCHA v3 Secret Key</label></th>
                    <td><input type="text" name="gcb_recaptcha_secret" id="gcb_recaptcha_secret" value="<?php echo $secret; ?>" class="regular-text" required></td>
                </tr>
            </table>
            <p><input type="submit" name="gcb_save" class="button-primary" value="Save Changes"></p>
        </form>
    </div>
    <?php
}

// Expose reCAPTCHA site key to block editor/frontend
add_action('wp_enqueue_scripts', function() {
    $site_key = get_option('gcb_recaptcha_site', '');
    if ($site_key) {
        wp_localize_script('gcb-block-frontend', 'gcbRecaptcha', [
            'siteKey' => $site_key
        ]);
    }
});
add_action('enqueue_block_editor_assets', function() {
    $site_key = get_option('gcb_recaptcha_site', '');
    if ($site_key) {
        wp_localize_script('gcb-block-editor', 'gcbRecaptcha', [
            'siteKey' => $site_key
        ]);
    }
});
