<?php
/**
 * Plugin Name: Content Gate
 * Description: A block that gates content behind a name/email form
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL-2.0-or-later
 * Text Domain: content-gate
 */

if (!defined('ABSPATH')) {
    exit;
}

function content_gate_register_block() {
    register_block_type(__DIR__ . '/build');

    wp_add_inline_style('content-gate-style', '
        .content-gate-block {
            margin: 2em 0;
            padding: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .content-gate-notice {
            background: #f8f8f8;
            padding: 0.5em;
            margin-bottom: 1em;
            border-radius: 4px;
        }
        .content-gate-form {
            max-width: 500px;
            margin: 2em auto;
            padding: 1em;
        }
        .content-gate-form-fields {
            display: flex;
            flex-direction: column;
            gap: 1em;
        }
        .content-gate-form-fields input[type="text"],
        .content-gate-form-fields input[type="email"] {
            width: 100%;
            padding: 0.5em;
        }
        .content-gate-form-fields button {
            cursor: pointer;
        }
    ');
}
add_action('init', 'content_gate_register_block');

// Add settings page
function content_gate_add_settings_page() {
    add_options_page(
        'Content Gate Settings',
        'Content Gate',
        'manage_options',
        'content-gate-settings',
        'content_gate_settings_page'
    );
}
add_action('admin_menu', 'content_gate_add_settings_page');

function content_gate_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('content_gate_options');
            do_settings_sections('content-gate-settings');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function content_gate_register_settings() {
    register_setting('content_gate_options', 'content_gate_recaptcha_site_key', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    register_setting('content_gate_options', 'content_gate_recaptcha_secret_key', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    add_settings_section(
        'content_gate_recaptcha_section',
        'reCAPTCHA v2 Settings',
        'content_gate_recaptcha_section_callback',
        'content-gate-settings'
    );

    add_settings_field(
        'content_gate_recaptcha_site_key',
        'Site Key',
        'content_gate_recaptcha_site_key_callback',
        'content-gate-settings',
        'content_gate_recaptcha_section'
    );

    add_settings_field(
        'content_gate_recaptcha_secret_key',
        'Secret Key',
        'content_gate_recaptcha_secret_key_callback',
        'content-gate-settings',
        'content_gate_recaptcha_section'
    );
}
add_action('admin_init', 'content_gate_register_settings');

function content_gate_recaptcha_section_callback() {
    echo '<p>Enter your reCAPTCHA v2 credentials. You can get these from the <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin Console</a>.</p>';
}

function content_gate_recaptcha_site_key_callback() {
    $site_key = get_option('content_gate_recaptcha_site_key');
    printf(
        '<input type="text" id="content_gate_recaptcha_site_key" name="content_gate_recaptcha_site_key" value="%s" class="regular-text">',
        esc_attr($site_key)
    );
}

function content_gate_recaptcha_secret_key_callback() {
    $secret_key = get_option('content_gate_recaptcha_secret_key');
    printf(
        '<input type="password" id="content_gate_recaptcha_secret_key" name="content_gate_recaptcha_secret_key" value="%s" class="regular-text">',
        esc_attr($secret_key)
    );
}

function content_gate_enqueue_frontend_scripts() {
    if (has_block('content-gate/main')) {
        $site_key = get_option('content_gate_recaptcha_site_key');
        
        if ($site_key) {
            wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
        }

        wp_enqueue_script(
            'content-gate-frontend',
            plugins_url('build/frontend.js', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'build/frontend.js'),
            true
        );

        wp_localize_script('content-gate-frontend', 'contentGateData', array(
            'nonce' => wp_create_nonce('content_gate_submit'),
            'recaptchaSiteKey' => $site_key
        ));
    }
}
add_action('wp_enqueue_scripts', 'content_gate_enqueue_frontend_scripts');

function content_gate_handle_submission() {
    if (!isset($_POST['content_gate_nonce']) || 
        !wp_verify_nonce($_POST['content_gate_nonce'], 'content_gate_submit')) {
        wp_send_json_error('Invalid nonce');
    }

    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $remember = isset($_POST['remember']) ? true : false;
    $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response'] ?? '');

    if (empty($name) || empty($email)) {
        wp_send_json_error('Name and email are required');
    }

    // Verify reCAPTCHA if configured
    $secret_key = get_option('content_gate_recaptcha_secret_key');
    if ($secret_key && empty($recaptcha_response)) {
        wp_send_json_error('Please complete the reCAPTCHA verification');
    }

    if ($secret_key && $recaptcha_response) {
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = wp_remote_post($verify_url, array(
            'body' => array(
                'secret' => $secret_key,
                'response' => $recaptcha_response
            )
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to verify reCAPTCHA');
        }

        $result = json_decode(wp_remote_retrieve_body($response));
        if (!$result->success) {
            wp_send_json_error('Invalid reCAPTCHA');
        }
    }

    $response = array(
        'success' => true,
        'token' => wp_create_nonce('content_gate_access')
    );

    if ($remember) {
        $token = wp_hash($email . time());
        setcookie(
            'content_gate_access',
            $token,
            time() + (30 * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        $response['cookie_token'] = $token;
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_content_gate_submit', 'content_gate_handle_submission');
add_action('wp_ajax_nopriv_content_gate_submit', 'content_gate_handle_submission');