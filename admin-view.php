<?php
/**
 * Admin View: Display stored submissions.
 */

function cg_admin_view_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cg_submissions';

    $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY submitted_at DESC" );

    echo '<div class="wrap">';
    echo '<h1>Content Gate Submissions</h1>';
    echo '<table class="widefat fixed">';
    echo '<thead>';
    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Submitted At</th><th>Article</th></tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ( $results as $row ) {
        $post_title = get_the_title( $row->post_id );
        $post_link = get_edit_post_link( $row->post_id );
        echo "<tr>
            <td>{$row->id}</td>
            <td>{$row->name}</td>
            <td>{$row->email}</td>
            <td>{$row->submitted_at}</td>
            <td><a href='{$post_link}'>{$post_title}</a></td>
        </tr>";
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
