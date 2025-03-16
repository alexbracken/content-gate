<?php
/**
 * Plugin Name: Content Gate with Contact Form 7
 * Plugin URI: https://example.com/
 * Description: A plugin that creates a content gate using a Gutenberg block and a Contact Form 7 form. Users must submit their name and email to reveal hidden content.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2+
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Gutenberg block and its editor assets.
 */
function cg_register_block() {
    // Register block editor script.
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
            'contactFormShortcode' => array(
                'type'    => 'string',
                'default' => '[contact-form-7 id="1" title="Contact form 1"]'
            )
        ),
    ));
}
add_action( 'init', 'cg_register_block' );

/**
 * Render callback for the block output on the frontend.
 */
function cg_render_callback( $attributes, $content ) {
    $contact_form = isset( $attributes['contactFormShortcode'] ) ? $attributes['contactFormShortcode'] : '[contact-form-7 id="1" title="Contact form 1"]';
    ob_start();
    ?>
    <div class="cg-content-gate">
        <div class="cg-gate-form">
            <?php echo do_shortcode( $contact_form ); ?>
        </div>
        <div class="cg-gated-content" style="display:none;">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Enqueue the frontend JavaScript and localize the AJAX variables.
 */
function cg_enqueue_scripts() {
    // Optionally, remove the has_block() condition if needed.
    if ( has_block( 'cg/content-gate' ) ) {
        wp_enqueue_script(
            'cg-frontend-script',
            plugins_url( 'frontend.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'frontend.js' ),
            true
        );

        // Pass the AJAX URL and a security nonce to the script.
        wp_localize_script( 'cg-frontend-script', 'cg_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_ajax_nonce' )
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'cg_enqueue_scripts' );

/**
 * AJAX handler for verifying the contact form submission.
 */
function cg_handle_form_submission() {
    // Verify nonce for security.
    check_ajax_referer( 'cg_ajax_nonce', 'nonce' );

    // Get and sanitize the form inputs.
    $name  = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

    if ( empty( $name ) || empty( $email ) ) {
        wp_send_json_error( 'Missing required fields.' );
    }

    // Optional: Add further validation or logging here.
    wp_send_json_success();
}
add_action( 'wp_ajax_cg_handle_form_submission', 'cg_handle_form_submission' );
add_action( 'wp_ajax_nopriv_cg_handle_form_submission', 'cg_handle_form_submission' );
