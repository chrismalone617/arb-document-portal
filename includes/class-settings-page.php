<?php
namespace ARB;

class SettingsPage {

    public static function init() {
        // Register the settings page
        add_action('admin_menu', [__CLASS__, 'register_settings_page']);

        // Handle file upload
        add_action('admin_post_arb_upload_file', [__CLASS__, 'handle_file_upload']);

        // Handle placeholder save
        add_action('admin_post_arb_save_placeholder', [__CLASS__, 'handle_save_placeholder']);
    }

    public static function register_settings_page() {
        add_options_page(
            'ARB Document Portal',
            'ARB Document Portal',
            'manage_options',
            'arb-document-portal',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function render_settings_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'arb_settings';

        // Fetch the placeholder from the database
        $placeholder = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table_name WHERE setting_key = %s",
            'placeholder'
        ));
        ?>
        <div class="wrap">
            <h1>ARB Document Portal</h1>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="arb_save_placeholder">
                <?php wp_nonce_field('arb_save_placeholder_nonce'); ?>

                <label for="custom_placeholder">Custom Placeholder:</label><br>
                <input type="text" id="custom_placeholder" name="custom_placeholder" 
                       value="<?php echo esc_attr($placeholder); ?>" 
                       placeholder="Enter a custom placeholder"><br><br>

                <button type="submit" class="button button-primary">Save Placeholder</button>
            </form>

            <hr>
            <!-- Existing file upload form -->
            <?php self::render_file_upload_form(); ?>
        </div>
        <?php
    }

    public static function render_file_upload_form() {
        $current_file_info = self::get_current_file_info();
        ?>
        <h2>Upload File</h2>
        <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="arb_upload_file">
            <?php wp_nonce_field('arb_upload_file_nonce'); ?>

            <label for="upload_file">Select File:</label><br>
            <input type="file" id="upload_file" name="upload_file"><br><br>

            <?php if ($current_file_info): ?>
                <p><strong>Current File:</strong> <?php echo esc_html($current_file_info['name']); ?></p>
                <p><strong>Uploaded On:</strong> <?php echo esc_html($current_file_info['date']); ?></p>
            <?php endif; ?>

            <button type="submit" class="button button-primary">Upload File</button>
        </form>
        <?php
    }

    public static function handle_file_upload() {
        check_admin_referer('arb_upload_file_nonce');
        
        if (!empty($_FILES['upload_file']['name'])) {
            $file = $_FILES['upload_file'];
            $upload_dir = plugin_dir_path(__FILE__) . 'uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_path = $upload_dir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                update_option('arb_document_file', [
                    'name' => $file['name'],
                    'date' => current_time('mysql'),
                    'path' => $file_path,
                ]);
                wp_redirect(admin_url('options-general.php?page=arb-document-portal&success=true'));
                exit;
            }
        }

        wp_redirect(admin_url('options-general.php?page=arb-document-portal&error=true'));
        exit;
    }

    public static function handle_save_placeholder() {
        check_admin_referer('arb_save_placeholder_nonce');

        if (isset($_POST['custom_placeholder'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'arb_settings';

            $wpdb->replace($table_name, [
                'setting_key' => 'placeholder',
                'setting_value' => sanitize_text_field($_POST['custom_placeholder'])
            ]);
        }

        wp_redirect(admin_url('options-general.php?page=arb-document-portal&success=true'));
        exit;
    }

    private static function get_current_file_info() {
        return get_option('arb_document_file');
    }
}
