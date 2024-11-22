<?php
namespace ARB;

class CustomField {

    public static function init() {
        add_filter('gform_add_field_buttons', [__CLASS__, 'add_custom_field_button']);
        add_filter('gform_field_type_title', [__CLASS__, 'add_custom_field_title']);
        add_action('gform_editor_js', [__CLASS__, 'add_custom_field_editor']);
        add_filter('gform_field_input', [__CLASS__, 'render_custom_field'], 10, 5);
    }

    public static function add_custom_field_button($field_groups) {
        foreach ($field_groups as &$group) {
            if ($group['name'] === 'standard_fields') {
                $group['fields'][] = [
                    'class' => 'button',
                    'value' => __('ARB Address'),
                    'data-type' => 'arb_address'
                ];
                break;
            }
        }
        return $field_groups;
    }

    public static function add_custom_field_title($type) {
        if ($type === 'arb_address') {
            return __('ARB Address');
        }
        return $type;
    }

    public static function add_custom_field_editor() {
        ?>
        <script type="text/javascript">
            // Add custom field settings
            fieldSettings.arb_address = ".admin_label_setting, .label_setting, .description_setting";

            // Prepopulate field data
            jQuery(document).on('gform_load_field_settings', function(event, field, form) {
                if (field.type === 'arb_address') {
                    jQuery('#field_admin_label').val('dynamic_dropdown');
                    field.className = 'dynamic-dropdown';
                }
            });
        </script>
        <?php
    }

    public static function render_custom_field($input, $field, $value, $lead_id, $form_id) {
        if ($field->type === 'arb_address') {
            $input = sprintf(
                '<select name="input_%d" id="input_%d" class="%s">%s</select>',
                $field->id,
                $field->id,
                esc_attr($field->className),
                '<option value="">' . esc_html__('Select an option') . '</option>'
            );
        }
        return $input;
    }
}

CustomField::init();
