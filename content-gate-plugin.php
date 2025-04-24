<?php
/**
 * Plugin Name: Content Gate
 * Description: Minimal content gate plugin for WordPress.
 * Version: 0.1
 * Author: Alex Bracken
 * License: GPL2+
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register block and assets
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
        'render_callback' => 'cg_render_callback'
    ));
}
add_action( 'init', 'cg_register_block' );

/**
 * Render callback: outputs gate and hidden content
 */
function cg_render_callback( $attributes, $content ) {
    $post_id = get_the_ID();
    $content = is_null($content) ? '' : $content;
    $encoded = base64_encode($content);
    ob_start();
    ?>
    <div class="cg-content-gate">
        <form class="cg-gate-form">
            <label>Name <input type="text" name="name" required></label>
            <label>Email <input type="email" name="email" required></label>
            <button type="submit">Continue</button>
        </form>
        <div class="cg-gated-content" style="display:none" data-content="<?php echo esc_attr($encoded); ?>"></div>
        <input type="hidden" class="cg-post-id" value="<?php echo esc_attr($post_id); ?>">
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Enqueue assets only if block is present
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
    }
}
add_action( 'wp_enqueue_scripts', 'cg_enqueue_scripts' );

/**
 * Handle AJAX form submissions
 */
function cg_save_submission() {
    if (!isset($_POST['data'])) {
        wp_send_json_error('No data received');
        return;
    }

    $data = $_POST['data'];
    
    // Optional: Save submission to database
    // global $wpdb;
    // $wpdb->insert('your_submissions_table', array(
    //     'name' => sanitize_text_field($data['name']),
    //     'email' => sanitize_email($data['email']),
    //     'post_id' => intval($data['post_id']),
    //     'date_submitted' => current_time('mysql')
    // ));

    wp_send_json_success();
}
add_action('wp_ajax_cg_save_submission', 'cg_save_submission');
add_action('wp_ajax_nopriv_cg_save_submission', 'cg_save_submission');