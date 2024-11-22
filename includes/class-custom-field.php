<?php
namespace ARB;

class CustomField {

    public static function init() {
        // Register the custom field type with Gravity Forms
        add_filter('gform_add_field_buttons', [__CLASS__, 'add_custom_field_button']);
        add_filter('gform_field_type_title', [__CLASS__, 'set_field_title']);
        add_action('gform_editor_js', [__CLASS__, 'add_editor_js']);
        add_filter('gform_field_input', [__CLASS__, 'render_field_input'], 10, 5);
    }

    // Add the custom field button to the Gravity Forms editor
    public static function add_custom_field_button($field_groups) {
        foreach ($field_groups as &$group) {
            if ($group['name'] === 'advanced_fields') {
                $group['fields'][] = [
                    'class' => 'button',
                    'value' => __('ARB Address', 'arb-document-portal'),
                    'data-type' => 'arb_address'
                ];
                break;
            }
        }
        return $field_groups;
    }

    // Set the title of the custom field in the editor
    public static function set_field_title($type) {
        if ($type === 'arb_address') {
            return __('ARB Address', 'arb-document-portal');
        }
        return $type;
    }

    // Add custom field JS to the editor
    public static function add_editor_js() {
        ?>
        <script type="text/javascript">
            // Add custom field settings to the editor
            gform.addFilter('gform_field_standard_settings', function(settings, fieldType) {
                if (fieldType === 'arb_address') {
                    settings.push({
                        name: 'class',
                        label: '<?php _e('CSS Class', 'arb-document-portal'); ?>',
                        type: 'text',
                        tooltip: '<?php _e('Add a custom class for styling.', 'arb-document-portal'); ?>'
                    });
                }
                return settings;
            });
        </script>
        <?php
    }

    // Render the input for the custom field
    public static function render_field_input($input, $field, $value, $lead_id, $form_id) {
        if ($field['type'] === 'arb_address') {
            $css_class = esc_attr(rgar($field, 'cssClass'));
            $input = sprintf(
                '<input type="text" name="input_%d" id="input_%d_%d" class="%s" value="%s" placeholder="%s" />',
                $field['id'],
                $form_id,
                $field['id'],
                $css_class,
                esc_attr($value),
                esc_attr(rgar($field, 'placeholder'))
            );
        }
        return $input;
    }
}

CustomField::init();
