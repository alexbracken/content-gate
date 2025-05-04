/*
* Plugin Name: Simple Content Gate
* Description: A simple plugin that adds a form to gate content, requiring name and email to view full posts
* Version: 1.0.0
* Author: Your Name
* License: GPL v2 or later
*/

if (!defined('ABSPATH')) {
    exit;
}

function scg_enqueue_scripts() {
    wp_enqueue_script('scg-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('scg-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_localize_script('scg-script', 'scgAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'scg_enqueue_scripts');

function scg_register_block() {
    if (!function_exists('register_block_type')) {
        return;
    }

    wp_register_script(
        'scg-block-editor',
        plugin_dir_url(__FILE__) . 'js/block.js',
        array('wp-blocks', 'wp-element', 'wp-editor')
    );

    register_block_type('simple-content-gate/gate-block', array(
        'editor_script' => 'scg-block-editor',
        'render_callback' => 'scg_render_gate_block'
    ));
}
add_action('init', 'scg_register_block');

function scg_render_gate_block($attributes, $content) {
    if (!is_single() || is_user_logged_in()) {
        return '';
    }

    $cookie_name = 'scg_verified_' . get_the_ID();
    if (isset($_COOKIE[$cookie_name])) {
        return '';
    }

    $form = '
    <div class="scg-form-container">
        <form id="scg-form">
            <h3>Please enter your details to continue reading</h3>
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <label>
                <input type="checkbox" name="remember" value="1">
                Remember me on this device
            </label>
            <input type="hidden" name="post_id" value="' . get_the_ID() . '">
            <button type="submit">Continue Reading</button>
        </form>
    </div>';

    return $form . '<div class="scg-hidden-content" style="display: none;">' . do_blocks($content) . '</div>';
}

function scg_filter_content($content) {
    if (!is_single() || is_user_logged_in()) {
        return $content;
    }

    $cookie_name = 'scg_verified_' . get_the_ID();
    if (isset($_COOKIE[$cookie_name])) {
        return $content;
    }

    $excerpt = wp_trim_words($content, 50, '...');
    $form = '
    <div class="scg-form-container">
        <form id="scg-form">
            <h3>Please enter your details to continue reading</h3>
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <label>
                <input type="checkbox" name="remember" value="1">
                Remember me on this device
            </label>
            <input type="hidden" name="post_id" value="' . get_the_ID() . '">
            <button type="submit">Continue Reading</button>
        </form>
    </div>';

    return $excerpt . $form;
}
add_filter('the_content', 'scg_filter_content');

function scg_verify_user() {
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $post_id = intval($_POST['post_id']);
    $remember = isset($_POST['remember']) ? true : false;

    if (!$name || !$email || !$post_id) {
        wp_send_json_error('Invalid data');
    }

    $response = array(
        'success' => true,
        'message' => 'Verified successfully'
    );

    if ($remember) {
        $cookie_name = 'scg_verified_' . $post_id;
        setcookie($cookie_name, '1', time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
    }

    do_action('scg_user_verified', array(
        'name' => $name,
        'email' => $email,
        'post_id' => $post_id,
        'timestamp' => current_time('mysql')
    ));

    wp_send_json_success($response);
}
add_action('wp_ajax_nopriv_scg_verify_user', 'scg_verify_user');
add_action('wp_ajax_scg_verify_user', 'scg_verify_user');