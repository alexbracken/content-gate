<?php
function gcb_register_block() {
    $dir = plugin_dir_url(__FILE__);
    wp_register_script(
        'gcb-block-editor',
        plugins_url('build/block.js', dirname(__FILE__)),
        ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data'],
        GCB_VERSION,
        true
    );
    wp_register_script(
        'gcb-block-frontend',
        plugins_url('build/frontend.js', dirname(__FILE__)),
        [],
        GCB_VERSION,
        true
    );
    wp_register_style(
        'gcb-block-style',
        plugins_url('build/style.css', dirname(__FILE__)),
        [],
        GCB_VERSION
    );
    register_block_type('gcb/gated-content', [
        'editor_script' => 'gcb-block-editor',
        'script' => 'gcb-block-frontend',
        'style' => 'gcb-block-style',
        'render_callback' => 'gcb_render_block'
    ]);
}
add_action('init', 'gcb_register_block');

function gcb_render_block($attributes, $content) {
    ob_start();
    ?>
    <div class="gcb-gated-content-block" data-post-id="<?php echo get_the_ID(); ?>">
        <div class="gcb-gate-form">
            <!-- The form will be rendered by JS -->
        </div>
        <div class="gcb-gated-section" style="display:none;">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}