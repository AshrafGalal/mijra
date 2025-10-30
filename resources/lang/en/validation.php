<?php

return [
    'identifier_required' => 'Email or Phone is required',
    'password_invalid' => 'Please enter a valid password',
    'currency_code' => 'The selected currency code is invalid',
    'name_required' => 'The name field is required.',
    'name_array' => 'The name must be an array.',
    'name_min' => 'At least one name is required.',
    'description_string' => 'The description must be a string.',
    'is_active_required' => 'The status field is required.',
    'is_active_boolean' => 'The status must be true or false.',
    'slug_required' => 'The slug is required.',
    'slug_unique' => 'The name is already in use.',
    'slug_string' => 'The name must be a valid string.',
    'group_required' => 'The group field is required.',
    'group_invalid' => 'The selected group is invalid.',

    'auth_failed' => 'These credentials do not match our records.',
    // Locale-aware messages
    'name_locale_required' => 'The name in :locale is required.',
    'name_locale_string' => 'The name in :locale must be a string.',

    'mail_settings' => [
        'smtp_host_required' => 'The SMTP host is required.',
        'smtp_host_string' => 'The SMTP host must be a valid string.',

        'smtp_port_required' => 'The SMTP port is required.',
        'smtp_port_numeric' => 'The SMTP port must be a number.',

        'mail_username_required' => 'The mail username is required.',
        'mail_username_string' => 'The mail username must be a string.',

        'mail_password_required' => 'The mail password is required.',
        'mail_password_string' => 'The mail password must be a string.',

        'from_email_address_required' => 'The sender email address is required.',
        'from_email_address_string' => 'The sender email address must be a valid string.',

        'from_name_required' => 'The sender name is required.',
        'from_name_string' => 'The sender name must be a valid string.',
    ],
    'product' => [
        'name_required' => 'Product name is required.',
        'name_string' => 'Product name must be a valid string.',
        'slug_unique' => 'A product with the same name already exists. You can edit it or use a different name',
        'base_price_required' => 'Base price is required.',
        'base_price_numeric' => 'Base price must be a number.',
        'discount_numeric' => 'Discount must be a number.',
        'discount_type_in' => 'Discount type must be either percentage or fixed.',
        'vat_numeric' => 'VAT percentage must be numeric.',
        'status_in' => 'Status must be valid.',
        'category_exists' => 'Selected category does not exist.',
        'tags_array' => 'Tags must be an array.',
        'tag_string' => 'Each tag must be a string.',

        'thumbnail_exists' => 'The thumbnail file is invalid.',
        'media_array' => 'Media must be an array.',
        'media_exists' => 'Some media files are invalid.',

        'variants_array' => 'Variants must be provided as an array.',
        'variant_sku_required' => 'SKU is required for each variant.',
        'variant_sku_unique' => 'SKU must be unique.',
        'variant_price_required' => 'Price is required for each variant.',
        'variant_price_numeric' => 'Variant price must be a number.',
        'variant_stock_required' => 'Stock is required for each variant.',
        'variant_stock_integer' => 'Stock must be an integer.',
        'variant_barcode_string' => 'Barcode must be a string.',
        'variant_weight_numeric' => 'Weight must be numeric.',

        'variant_attributes_required' => 'Each variant must have attributes.',
        'variant_attributes_array' => 'Attributes must be an array.',
        'attribute_id_required' => 'Attribute ID is required.',
        'attribute_id_exists' => 'Selected attribute does not exist.',
        'attribute_value_id_required' => 'Attribute value is required.',
        'attribute_value_id_exists' => 'Selected attribute value does not exist.',
    ],
    'custom' => [
        'email' => [
            'required' => 'Email address is required.',
            'email' => 'Please enter a valid email address.',
            'unique' => 'This email address is already registered. Please use a different email or try logging in.',
        ],
        'name' => [
            'required' => 'Name is required.',
        ],
        'organization_name' => [
            'required' => 'Organization name is required.',
        ],
        'password' => [
            'required' => 'Password is required.',
            'confirmed' => 'Password confirmation does not match.',
        ],
        'locale' => [
            'in' => 'The selected locale not supported.',
            'required' => 'The locale field is required.',
        ],

        'stage_id' => [
            'required' => 'The stage selection is required.',
            'integer' => 'The stage must be a valid number.',
            'exists' => 'The selected stage does not exist.',
        ],
        'opportunity_id' => [
            'required' => 'The opportunity selection is required.',
            'integer' => 'The opportunity must be a valid number.',
            'exists' => 'The selected opportunity does not exist or has been deleted.',
        ],
        'template' => [
            // Basic template fields
            'name_required' => 'Template name is required',
            'name_string' => 'Template name must be a string',
            'name_max' => 'Template name cannot exceed :max characters',

            'description_string' => 'Description must be a string',
            'description_max' => 'Description cannot exceed :max characters',

            'category_required' => 'Campaign category is required',
            'category_in' => 'Selected campaign category is invalid. Valid options: :values',

            'template_type_required' => 'Template type is required',
            'template_type_in' => 'Selected template type is invalid. Valid options: :values',

            'content_required' => 'Template content is required',
            'content_string' => 'Template content must be a string',

            'header_content_string' => 'Header content must be a string',
            'header_content_max' => 'Header content cannot exceed :max characters',

            'footer_content_string' => 'Footer content must be a string',
            'footer_content_max' => 'Footer content cannot exceed :max characters',

            'is_active_required' => 'Active status is required',
            'is_active_boolean' => 'Active status must be true or false',

            // Template buttons validation
            'template_buttons_array' => 'Template buttons must be an array',
            'template_buttons_min' => 'At least :min button is required',

            'button_text_required' => 'Button text is required for button at position :position',
            'button_text_string' => 'Button text must be a string for button at position :position',
            'button_text_max' => 'Button text cannot exceed :max characters for button at position :position',

            'button_type_required' => 'Button type is required for button at position :position',
            'button_type_in' => 'Invalid button type for button at position :position. Valid types: :values',

            'action_value_required' => 'Action value is required for button at position :position',
            'action_value_string' => 'Action value must be a string for button at position :position',

            // Template parameters validation
            'template_parms_array' => 'Template parameters must be an array',
            'template_parms_min' => 'At least :min parameter is required',

            'parm_name_required' => 'Parameter name is required for parameter at position :position',
            'parm_name_string' => 'Parameter name must be a string for parameter at position :position',
            'parm_name_max' => 'Parameter name cannot exceed :max characters for parameter at position :position',

        ],
    ],
];
