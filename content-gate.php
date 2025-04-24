<?php
/**
 * Plugin Name: Content Gate
 * Description: Creates a content gate block with reader registration form
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add reader role on plugin activation
function content_gate_activate() {
    add_role('reader', 'Reader', array(
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false
    ));
}
register_activation_hook(__FILE__, 'content_gate_activate');

// Remove reader role on plugin deactivation
function content_gate_deactivate() {
    remove_role('reader');
}
register_deactivation_hook(__FILE__, 'content_gate_deactivate');

// Register block
function content_gate_register_block() {
    register_block_type(__DIR__ . '/build');
}
add_action('init', 'content_gate_register_block');

// Handle form submission
function content_gate_handle_submission() {
    if (!wp_verify_nonce($_POST['nonce'], 'content_gate_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $email = sanitize_email($_POST['email']);
    $name = sanitize_text_field($_POST['name']);

    if (email_exists($email)) {
        wp_send_json_error('Email already exists');
    }

    $userdata = array(
        'user_login' => $email,
        'user_email' => $email,
        'user_pass' => wp_generate_password(),
        'display_name' => $name,
        'role' => 'reader'
    );

    $user_id = wp_insert_user($userdata);

    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
    }

    wp_send_json_success('Account created successfully');
}
add_action('wp_ajax_nopriv_content_gate_submit', 'content_gate_handle_submission');
