<?php
/*
Plugin Name: Custom Dashboard Plugin
Description: A custom plugin to add a dashboard with a navbar and stats.
Version: 1.0
Author: Tanzil
*/

// Hook to add admin menu
add_action('admin_menu', 'cdp_add_admin_menu');

function cdp_add_admin_menu() {
    add_menu_page(
        'Custom Dashboard', // Page title
        'Custom Dashboard', // Menu title
        'manage_options',   // Capability
        'custom-dashboard', // Menu slug
        'cdp_display_dashboard', // Callback function
        'dashicons-chart-area', // Icon
        6 // Position
    );
}

// Display the dashboard
function cdp_display_dashboard() {
    ?>
    <div class="wrap" style="padding: 20px;">
        <div style="border-radius: 20px; background-color: #f1f1f1; padding: 10px;">
            <ul style="display: flex; list-style: none; padding: 0;">
                <li style="margin-right: 20px;"><a href="?page=custom-dashboard&tab=new">Dashboard</a></li>
                <li style="margin-right: 20px;"><a href="?page=custom-dashboard&tab=stats">Stats</a></li>
                <li style="margin-right: 20px;"><a href="?page=custom-dashboard&tab=email">Email</a></li>
                <li style="margin-right: 20px;"><a href="?page=custom-dashboard&tab=license">License</a></li>
            </ul>
        </div>
        <div style="margin-top: 20px;">
            <?php cdp_display_tab_content(); ?>
        </div>
    </div>
    <?php
}

function cdp_display_tab_content() {
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'new';
    switch ($tab) {
        case 'new':
            echo '<h2>New Content</h2>';
            break;
        case 'stats':
            echo '<h2>Stats</h2>';
            echo '<div style="margin-top: 20px; display: flex;">';
            echo '<div style="flex: 1; margin-right: 10px; padding: 10px; background-color: #e2e2e2;">Stats Retrieve</div>';
            echo '<div style="flex: 1; margin-left: 10px; padding: 10px; background-color: #e2e2e2;">';
            echo '<p>Some stats data...</p>';
            echo '<div style="filter: blur(5px);">More stats data... Unlock by buying a subscription.</div>';
            echo '</div>';
            echo '</div>';
            break;
        case 'email':
            echo '<h2>Email</h2>';
            break;
        case 'license':
            echo '<h2>License</h2>';
            break;
        default:
            echo '<h2>New Content</h2>';
            break;
    }
}