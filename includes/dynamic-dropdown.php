<?php
namespace ARB;

add_filter( 'gform_pre_render', [ __CLASS__, 'populate_dynamic_dropdown' ] );
add_filter( 'gform_pre_validation', [ __CLASS__, 'populate_dynamic_dropdown' ] );
add_filter( 'gform_pre_submission_filter', [ __CLASS__, 'populate_dynamic_dropdown' ] );
add_filter( 'gform_admin_pre_render', [ __CLASS__, 'populate_dynamic_dropdown' ] );

class DynamicDropdown {

    public static function populate_dynamic_dropdown($form) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'arb_settings';
    
        $placeholder = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table_name WHERE setting_key = %s",
            'placeholder'
        ));
    
        foreach ($form['fields'] as &$field) {
            if ($field->type === 'select' && isset($field->adminLabel) && $field->adminLabel === 'dynamic_dropdown') {
                // Add custom placeholder
                $field->placeholder = $placeholder ?: 'Select an option';
                // ... existing dropdown logic
            }
        }
        return $form;
    }
    
}
