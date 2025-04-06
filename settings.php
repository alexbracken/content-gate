<?php
// Add settings page
function cg_settings_page() {
    add_options_page( 'Content Gate Settings', 'Content Gate', 'manage_options', 'cg-settings', 'cg_settings_page_html' );
}
add_action( 'admin_menu', 'cg_settings_page' );

function cg_register_settings() {
    register_setting( 'cg_settings_group', 'cg_recaptcha_site_key' );
    register_setting( 'cg_settings_group', 'cg_recaptcha_secret_key' );
    register_setting( 'cg_settings_group', 'cg_form_message' );
}
add_action( 'admin_init', 'cg_register_settings' );

function cg_settings_page_html() {
    ?>
    <div class="wrap">
        <h1>Content Gate Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'cg_settings_group' ); ?>
            <?php do_settings_sections( 'cg_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th>Form Message:</th>
                    <td>
                        <textarea name="cg_form_message" rows="3" cols="50"><?php echo esc_textarea( get_option('cg_form_message', 'This content is protected.') ); ?></textarea>
                        <p class="description">Message displayed to users above the form</p>
                    </td>
                </tr>
                <tr>
                    <th>reCAPTCHA Site Key:</th>
                    <td>
                        <input type="text" name="cg_recaptcha_site_key" value="<?php echo esc_attr( get_option('cg_recaptcha_site_key') ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th>reCAPTCHA Secret Key:</th>
                    <td>
                        <input type="text" name="cg_recaptcha_secret_key" value="<?php echo esc_attr( get_option('cg_recaptcha_secret_key') ); ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}