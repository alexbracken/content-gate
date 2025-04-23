<?php
/**
 * Plugin Name: Content Gate
 * Plugin URI: https://github.com/alexbracken/content-gate
 * Description: A content gate plugin that restricts content until a user submits their name and email.
 * Version: 0.1
 * Author: Alex Bracken
 * License: GPL2+
 */

// Prevent direct access
defined( 'ABSPATH' ) || exit;

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
    
    // Set default form message if it doesn't exist
    if (!get_option('cg_form_message')) {
        add_option('cg_form_message', 'This content is protected.');
    }
}
register_activation_hook( __FILE__, 'cg_create_table' );

require_once plugin_dir_path(__FILE__) . 'settings.php';
require_once plugin_dir_path(__FILE__) . 'admin-view.php';

/**
 * Register the Gutenberg block.
 */
function cg_register_block() {
    wp_register_script(
        'cg-block-script',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
    );

    register_block_type( 'cg/content-gate', array(
        'editor_script'   => 'cg-block-script',
        'render_callback' => 'cg_render_callback',
        'attributes'      => array(
            'useCustomGate' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'content' => array(
                'type' => 'string',
                'source' => 'html',
                'selector' => '.cg-custom-gate-area'
            )
        )
    ));
}
add_action( 'init', 'cg_register_block' );

/**
 * Render callback for the block.
 */
function cg_render_callback( $attributes, $content ) {
    $site_key = get_option('cg_recaptcha_site_key', '');
    
    // Check if using custom gate layout
    $use_custom_gate = isset($attributes['useCustomGate']) ? $attributes['useCustomGate'] : false;
    
    // Generate a unique ID for this gate instance
    $gate_id = 'cg-' . uniqid();
    
    // Ensure content is a string before encoding
    $content = is_null($content) ? '' : $content;
    $encrypted_content = base64_encode($content);
    
    ob_start();
    ?>
    <div class="cg-content-gate" id="<?php echo esc_attr($gate_id); ?>">
        <?php if ($use_custom_gate): ?>
            <div class="cg-custom-gate-area wp-block-cg-gate-form">
                <!-- Custom blocks will be rendered here from content -->
                <?php echo do_blocks($attributes['content'] ?? ''); ?>
            </div>
        <?php else: ?>
            <div class="cg-message" role="heading" aria-level="2">
                <h4 class="cg-heading">This content is protected.</h4>
                <p class="cg-subheading">Please enter your information to continue.</p>
            </div>
        <?php endif; ?>
        
        <form class="cg-gate-form" aria-labelledby="cg-form-title">
            <span id="cg-form-title" class="screen-reader-text">Content access form</span>
            
            <div class="cg-form-row">
                <div class="cg-form-group">
                    <label for="cg-name-<?php echo esc_attr($gate_id); ?>">Name</label>
                    <input type="text" id="cg-name-<?php echo esc_attr($gate_id); ?>" name="name" required aria-required="true">
                </div>
                
                <div class="cg-form-group">
                    <label for="cg-email-<?php echo esc_attr($gate_id); ?>">Email</label>
                    <input type="email" id="cg-email-<?php echo esc_attr($gate_id); ?>" name="email" required aria-required="true">
                </div>
                
                <div class="cg-form-submit">
                    <button type="submit" class="button button-primary">Submit</button>
                </div>
            </div>
            
            <!-- Honeypot field for spam prevention -->
            <div class="cg-form-group" style="display:none !important; position:absolute; left:-9999px;">
                <label for="cg-website-<?php echo esc_attr($gate_id); ?>">Website</label>
                <input type="text" id="cg-website-<?php echo esc_attr($gate_id); ?>" name="website" autocomplete="off" tabindex="-1">
            </div>
            
            <input type="hidden" id="cg-post-id" value="<?php echo get_the_ID(); ?>">
            <input type="hidden" id="cg-content-data" value="<?php echo esc_attr($encrypted_content); ?>">
            
            <?php if ($site_key): ?>
                <input type="hidden" id="cg-recaptcha-token" name="recaptcha">
                <script>
                    window.addEventListener('load', function() {
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.ready(function() {
                                grecaptcha.execute('<?php echo esc_attr($site_key); ?>', { action: 'submit' }).then(function(token) {
                                    document.getElementById('cg-recaptcha-token').value = token;
                                });
                            });
                        }
                    });
                </script>
                <script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>" async defer></script>
            <?php endif; ?>
        </form>
        <div class="cg-gated-content" style="display:none;" aria-live="polite" data-content-id="<?php echo esc_attr($gate_id); ?>">
            <!-- Content will be inserted here via JavaScript -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Enqueue frontend scripts and styles.
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

        wp_enqueue_style(
            'cg-frontend-style',
            plugins_url( 'style.css', __FILE__ ),
            array(),
            filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
        );

        wp_localize_script( 'cg-frontend-script', 'cg_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_ajax_nonce' ),
            'recaptcha_secret' => get_option('cg_recaptcha_secret_key', ''),
            'remember_user' => get_option('cg_remember_user', 'yes'),
            'cookie_days' => intval(get_option('cg_cookie_days', '30'))
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
    $recaptcha_token = isset($_POST['recaptcha']) ? $_POST['recaptcha'] : '';

    // Verify reCAPTCHA
    $secret_key = get_option('cg_recaptcha_secret_key', '');
    if ($secret_key && $recaptcha_token) {
        $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", array(
            'body' => array(
                'secret'   => $secret_key,
                'response' => $recaptcha_token
            )
        ));

        $result = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($result['success']) || !$result['success']) {
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