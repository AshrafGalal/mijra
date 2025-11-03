<?php

return [
    'identifier_required' => 'Se requiere correo electrónico o número de teléfono.',
    'password_invalid' => 'Por favor, introduzca una contraseña válida.',
    'currency_code' => 'El código de moneda especificado no es válido.',
    'name_required' => 'El campo nombre es obligatorio.',
    'name_array' => 'El nombre debe ser un arreglo.',
    'name_min' => 'Se debe proporcionar al menos un nombre.',
    'description_string' => 'La descripción debe ser una cadena de texto.',
    'is_active_required' => 'El campo de estado es obligatorio.',
    'is_active_boolean' => 'El estado debe ser verdadero o falso.',
    'slug_required' => 'El identificador (slug) es obligatorio.',
    'slug_unique' => 'El identificador / nombre ya está en uso.',
    'slug_string' => 'El identificador / nombre debe ser una cadena de texto.',
    'group_required' => 'El grupo es obligatorio.',
    'group_invalid' => 'El grupo especificado no es válido.',
    'auth_failed' => 'Estas credenciales no coinciden con nuestros registros.',

    // Locale-aware messages
    'name_locale_required' => 'El nombre en :locale es obligatorio.',
    'name_locale_string' => 'El nombre en :locale debe ser una cadena de texto.',

    'mail_settings' => [
        'smtp_host_required' => 'El host SMTP es obligatorio.',
        'smtp_host_string' => 'El host SMTP debe ser una cadena válida.',

        'smtp_port_required' => 'El puerto SMTP es obligatorio.',
        'smtp_port_numeric' => 'El puerto SMTP debe ser un número.',

        'mail_username_required' => 'El nombre de usuario del correo es obligatorio.',
        'mail_username_string' => 'El nombre de usuario del correo debe ser una cadena.',

        'mail_password_required' => 'La contraseña del correo es obligatoria.',
        'mail_password_string' => 'La contraseña del correo debe ser una cadena.',

        'from_email_address_required' => 'La dirección de correo del remitente es obligatoria.',
        'from_email_address_string' => 'La dirección de correo del remitente debe ser una cadena válida.',

        'from_name_required' => 'El nombre del remitente es obligatorio.',
        'from_name_string' => 'El nombre del remitente debe ser una cadena.',
    ],
    'product' => [
        'name_required' => 'El nombre del producto es obligatorio.',
        'name_string' => 'El nombre del producto debe ser una cadena.',
        'slug_unique' => 'Ya existe un producto con el mismo nombre. Puedes editarlo o usar otro nombre diferente.',
        'base_price_required' => 'El precio base es obligatorio.',
        'base_price_numeric' => 'El precio base debe ser numérico.',
        'discount_numeric' => 'El descuento debe ser un número.',
        'discount_type_in' => 'El tipo de descuento debe ser porcentaje o fijo.',
        'vat_numeric' => 'El IVA debe ser numérico.',
        'status_in' => 'El estado del producto no es válido.',
        'category_exists' => 'La categoría seleccionada no existe.',
        'tags_array' => 'Las etiquetas deben ser un arreglo.',
        'tag_string' => 'Cada etiqueta debe ser una cadena.',

        'thumbnail_exists' => 'El archivo de miniatura no es válido.',
        'media_array' => 'Los medios deben ser un arreglo.',
        'media_exists' => 'Algunos archivos multimedia no son válidos.',

        'variants_array' => 'Las variantes deben enviarse como un arreglo.',
        'variant_sku_required' => 'El SKU es obligatorio para cada variante.',
        'variant_sku_unique' => 'El SKU debe ser único.',
        'variant_price_required' => 'El precio es obligatorio para cada variante.',
        'variant_price_numeric' => 'El precio de la variante debe ser numérico.',
        'variant_stock_required' => 'El stock es obligatorio para cada variante.',
        'variant_stock_integer' => 'El stock debe ser un número entero.',
        'variant_barcode_string' => 'El código de barras debe ser una cadena.',
        'variant_weight_numeric' => 'El peso debe ser numérico.',

        'variant_attributes_required' => 'Cada variante debe tener atributos.',
        'variant_attributes_array' => 'Los atributos deben ser un arreglo.',
        'attribute_id_required' => 'El ID del atributo es obligatorio.',
        'attribute_id_exists' => 'El atributo seleccionado no existe.',
        'attribute_value_id_required' => 'El valor del atributo es obligatorio.',
        'attribute_value_id_exists' => 'El valor del atributo seleccionado no existe.',
    ],
    'custom' => [
        'email' => [
            'required' => 'El correo electrónico es obligatorio.',
            'email' => 'Por favor, introduzca un correo electrónico válido.',
            'unique' => 'Este correo electrónico ya está registrado. Utilice otro o inicie sesión.',
        ],
        'name' => [
            'required' => 'El nombre es obligatorio.',
        ],
        'stage_id' => [
            'required' => 'La selección de la etapa es obligatoria.',
            'integer' => 'La etapa debe ser un número válido.',
            'exists' => 'La etapa seleccionada no existe.',
        ],
        'opportunity_id' => [
            'required' => 'La selección de la oportunidad es obligatoria.',
            'integer' => 'La oportunidad debe ser un número válido.',
            'exists' => 'La oportunidad seleccionada no existe o ha sido eliminada.',
        ],
        'organization_name' => [
            'required' => 'El nombre de la organización es obligatorio.',
        ],
        'password' => [
            'required' => 'La contraseña es obligatoria.',
            'confirmed' => 'La confirmación de la contraseña no coincide.',
        ],
        'locale' => [
            'in' => 'La langue sélectionnée n\'est pas valide.',
            'required' => 'Le champ langue est obligatoire.',
        ],
        'template' => [
            // Basic template fields
            'name_required' => 'El nombre de la plantilla es requerido',
            'name_string' => 'El nombre de la plantilla debe ser una cadena de texto',
            'name_max' => 'El nombre de la plantilla no puede exceder :max caracteres',

            'description_string' => 'La descripción debe ser una cadena de texto',
            'description_max' => 'La descripción no puede exceder :max caracteres',

            'category_required' => 'La categoría de campaña es requerida',
            'category_in' => 'La categoría de campaña seleccionada es inválida. Opciones válidas: :values',

            'template_type_required' => 'El tipo de plantilla es requerido',
            'template_type_in' => 'El tipo de plantilla seleccionado es inválido. Opciones válidas: :values',

            'content_required' => 'El contenido de la plantilla es requerido',
            'content_string' => 'El contenido de la plantilla debe ser una cadena de texto',

            'header_content_string' => 'El contenido del encabezado debe ser una cadena de texto',
            'header_content_max' => 'El contenido del encabezado no puede exceder :max caracteres',

            'footer_content_string' => 'El contenido del pie de página debe ser una cadena de texto',
            'footer_content_max' => 'El contenido del pie de página no puede exceder :max caracteres',

            'is_active_required' => 'El estado de activación es requerido',
            'is_active_boolean' => 'El estado de activación debe ser verdadero o falso',

            // Template buttons validation
            'template_buttons_array' => 'Los botones de la plantilla deben ser un array',
            'template_buttons_min' => 'Se requiere al menos :min botón',

            'button_text_required' => 'El texto del botón es requerido para el botón en la posición :position',
            'button_text_string' => 'El texto del botón debe ser una cadena para el botón en la posición :position',
            'button_text_max' => 'El texto del botón no puede exceder :max caracteres para el botón en la posición :position',

            'button_type_required' => 'El tipo de botón es requerido para el botón en la posición :position',
            'button_type_in' => 'Tipo de botón inválido para el botón en la posición :position. Tipos válidos: :values',

            'action_value_required' => 'El valor de acción es requerido para el botón en la posición :position',
            'action_value_string' => 'El valor de acción debe ser una cadena para el botón en la posición :position',

            // Template parameters validation
            'template_parms_array' => 'Los parámetros de la plantilla deben ser un array',
            'template_parms_min' => 'Se requiere al menos :min parámetro',

            'parm_name_required' => 'El nombre del parámetro es requerido para el parámetro en la posición :position',
            'parm_name_string' => 'El nombre del parámetro debe ser una cadena para el parámetro en la posición :position',
            'parm_name_max' => 'El nombre del parámetro no puede exceder :max caracteres para el parámetro en la posición :position',

        ],
    ],
];
