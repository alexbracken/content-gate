<?php
/**
 * Plugin Name: Custom Content Gate Plugin
 * Plugin URI: https://example.com/
 * Description: A custom content gate plugin that restricts content until a user submits their name and email.
 * Version: 1.2.0
 * Author: Your Name
 * License: GPL2+
 */

defined( 'ABSPATH' ) || exit;

// Include admin and settings files
require_once plugin_dir_path(__FILE__) . 'settings.php';
require_once plugin_dir_path(__FILE__) . 'admin-view.php';

/**
 * Create table on plugin activation.
 */
function cg_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cg_submissions';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        post_id BIGINT(20) UNSIGNED NOT NULL
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'cg_create_table' );

/**
 * Register the Gutenberg block.
 */
function cg_register_block() {
    wp_register_script(
        'cg-block-script',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
    );

    register_block_type( 'cg/content-gate', array(
        'editor_script'   => 'cg-block-script',
        'render_callback' => 'cg_render_callback',
    ));
}
add_action( 'init', 'cg_register_block' );

/**
 * Render callback for the block.
 */
function cg_render_callback( $attributes, $content ) {
    $site_key = get_option('cg_recaptcha_site_key', '');
    ob_start();
    ?>
    <div class="cg-content-gate">
        <form class="cg-gate-form">
            <label for="cg-name">Name:</label>
            <input type="text" id="cg-name" name="name" required>
            <label for="cg-email">Email:</label>
            <input type="email" id="cg-email" name="email" required>
            <input type="hidden" id="cg-post-id" value="<?php echo get_the_ID(); ?>">
            
            <?php if ($site_key): ?>
                <input type="hidden" id="cg-recaptcha-token" name="recaptcha">
                <script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>"></script>
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo esc_attr($site_key); ?>', { action: 'submit' }).then(function(token) {
                            document.getElementById('cg-recaptcha-token').value = token;
                        });
                    });
                </script>
            <?php endif; ?>

            <button type="submit">Submit</button>
        </form>
        <div class="cg-gated-content" style="display:none;">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Enqueue frontend scripts.
 */
function cg_enqueue_scripts() {
    if ( has_block( 'cg/content-gate' ) ) {
        wp_enqueue_script(
            'cg-frontend-script',
            plugins_url( 'frontend.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'frontend.js' ),
            true
        );

        wp_localize_script( 'cg-frontend-script', 'cg_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_ajax_nonce' ),
            'recaptcha_secret' => get_option('cg_recaptcha_secret_key', '')
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'cg_enqueue_scripts' );

/**
 * Handle form submission via AJAX.
 */
function cg_handle_form_submission() {
    check_ajax_referer( 'cg_ajax_nonce', 'nonce' );

    $name    = sanitize_text_field( $_POST['name'] );
    $email   = sanitize_email( $_POST['email'] );
    $post_id = intval( $_POST['post_id'] );
    $recaptcha_token = $_POST['recaptcha'];

    // Verify reCAPTCHA
    $secret_key = get_option('cg_recaptcha_secret_key', '');
    if ($secret_key) {
        $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", array(
            'body' => array(
                'secret'   => $secret_key,
                'response' => $recaptcha_token
            )
        ));

        $result = json_decode(wp_remote_retrieve_body($response), true);
        if (!$result['success']) {
            wp_send_json_error('reCAPTCHA verification failed.');
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cg_submissions';

    $wpdb->insert(
        $table_name,
        array(
            'name'  => $name,
            'email' => $email,
            'post_id' => $post_id
        ),
        array('%s', '%s', '%d')
    );

    wp_send_json_success();
}
add_action( 'wp_ajax_cg_handle_form_submission', 'cg_handle_form_submission' );
add_action( 'wp_ajax_nopriv_cg_handle_form_submission', 'cg_handle_form_submission' );

/**
 * Register admin menu item for viewing submissions.
 */
function cg_admin_menu() {
    add_menu_page(
        'Content Gate Submissions',
        'Content Gate',
        'manage_options',
        'cg-submissions',
        'cg_admin_view_page',
        'dashicons-visibility',
        20
    );
}
add_action( 'admin_menu', 'cg_admin_menu' );
