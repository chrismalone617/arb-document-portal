<?php
/**
 * Plugin Name: ARB Document Portal
 * Description: A plugin to manage document uploads and dynamically populate dropdowns in Gravity Forms with Select2 functionality.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ARB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once ARB_PLUGIN_DIR . 'includes/class-settings-page.php';
require_once ARB_PLUGIN_DIR . 'includes/dynamic-dropdown.php';
require_once ARB_PLUGIN_DIR . 'includes/class-custom-field.php';


// Initialize the settings page
add_action( 'admin_menu', [ 'ARB\SettingsPage', 'init' ] );

// Enqueue scripts and styles
function arb_enqueue_assets() {
    if ( is_admin() ) {
        wp_enqueue_style( 'arb-select2', ARB_PLUGIN_URL . 'css/select2.min.css' );
        wp_enqueue_style( 'arb-custom-styles', ARB_PLUGIN_URL . 'css/custom-styles.css' );
        wp_enqueue_script( 'arb-select2', ARB_PLUGIN_URL . 'js/select2.min.js', [ 'jquery' ], null, true );
        wp_enqueue_script( 'arb-custom-script', ARB_PLUGIN_URL . 'js/custom-script.js', [ 'jquery', 'arb-select2' ], null, true );
    }
}
add_action( 'admin_enqueue_scripts', 'arb_enqueue_assets' );

// Create and delete database table
register_activation_hook(__FILE__, 'arb_create_custom_table');
register_uninstall_hook(__FILE__, 'arb_delete_custom_table');

function arb_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'arb_settings';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(255) NOT NULL,
        setting_value TEXT NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY setting_key (setting_key)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Insert default placeholder
    $wpdb->insert($table_name, [
        'setting_key' => 'placeholder',
        'setting_value' => 'Select an option'
    ]);
}

function arb_delete_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'arb_settings';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
