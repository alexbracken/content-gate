<?php
/**
 * Plugin Name: Hello world!
 * Plugin URI: https://example.com/
 * Description: A custom content gate plugin that restricts content until a user submits their name and email.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2+
 */

defined( 'ABSPATH' ) || exit;

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
        'attributes'      => array(),
    ));
}
add_action( 'init', 'cg_register_block' );

/**
 * Render callback for the block.
 */
function cg_render_callback( $attributes, $content ) {
    ob_start();
    ?>
    <div class="cg-content-gate">
        <form class="cg-gate-form">
            <label for="cg-name">Name:</label>
            <input type="text" id="cg-name" name="name" required>
            <label for="cg-email">Email:</label>
            <input type="email" id="cg-email" name="email" required>
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
            'nonce'    => wp_create_nonce( 'cg_ajax_nonce' )
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'cg_enqueue_scripts' );

/**
 * Handle form submission via AJAX.
 */
function cg_handle_form_submission() {
    check_ajax_referer( 'cg_ajax_nonce', 'nonce' );

    $name  = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

    if ( empty( $name ) || empty( $email ) ) {
        wp_send_json_error( 'Please provide both name and email.' );
    }

    // Here, you can add additional validation or save data to the database.

    wp_send_json_success( 'Form submission successful.' );
}
add_action( 'wp_ajax_cg_handle_form_submission', 'cg_handle_form_submission' );
add_action( 'wp_ajax_nopriv_cg_handle_form_submission', 'cg_handle_form_submission' );
