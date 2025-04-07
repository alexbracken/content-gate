<?php
/**
 * Admin View: Display stored submissions.
 */

function cg_admin_view_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cg_submissions';

    // Check if export action is requested
    if (isset($_GET['action']) && $_GET['action'] === 'export' && current_user_can('manage_options')) {
        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="content-gate-submissions-' . date('Y-m-d') . '.csv"');
        $csv = fopen('php://output', 'w');
        fputcsv($csv, ['ID', 'Name', 'Email', 'Submitted At', 'Article', 'Post ID']);
        
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");
        foreach ($results as $row) {
            $post_title = get_the_title($row->post_id);
            fputcsv($csv, [$row->id, $row->name, $row->email, $row->submitted_at, $post_title, $row->post_id]);
        }
        fclose($csv);
        exit;
    }
    
    // Get submissions with pagination
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $per_page);
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));

    echo '<div class="wrap">';
    echo '<h1>Content Gate Submissions</h1>';
    echo '<div class="tablenav top">';
    echo '<div class="alignleft actions">';
    echo '<a href="' . esc_url(admin_url('admin.php?page=cg-submissions&action=export')) . '" class="button">Export to CSV</a>';
    echo '</div>';
    
    // Pagination
    echo '<div class="tablenav-pages">';
    echo '<span class="displaying-num">' . number_format_i18n($total_items) . ' items</span>';
    
    if ($total_pages > 1) {
        $pagination_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $total_pages,
            'current' => $current_page
        ));
        echo $pagination_links;
    }
    
    echo '</div>';
    echo '</div>';
    
    if (empty($results)) {
        echo '<div class="notice notice-info"><p>No submissions found.</p></div>';
    } else {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Submitted At</th>
            <th scope="col">Article</th>
        </tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            $post_title = get_the_title($row->post_id);
            $post_link = get_edit_post_link($row->post_id);
            echo "<tr>
                <td>{$row->id}</td>
                <td>" . esc_html($row->name) . "</td>
                <td>" . esc_html($row->email) . "</td>
                <td>" . esc_html($row->submitted_at) . "</td>
                <td><a href='" . esc_url($post_link) . "'>" . esc_html($post_title) . "</a></td>
            </tr>";
        }

        echo '</tbody>';
        echo '</table>';
    }
    
    echo '</div>';
}