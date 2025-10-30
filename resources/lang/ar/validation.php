<?php

return [
    'identifier_required' => 'البريد الإلكتروني أو رقم الهاتف مطلوب',
    'password_invalid' => 'الرجاء إدخال كلمة مرور صالحة',
    'currency_code' => 'رمز العملة المحدد غير صالح',
    'name_required' => 'حقل الاسم مطلوب.',
    'name_array' => 'يجب أن يكون الاسم مصفوفة.',
    'name_min' => 'يجب إدخال اسم واحد على الأقل.',
    'description_string' => 'يجب أن يكون الوصف نصًا.',
    'is_active_required' => 'حقل الحالة مطلوب.',
    'is_active_boolean' => 'يجب أن تكون الحالة صحيحة أو خاطئة.',
    'slug_required' => 'المعرف (slug) مطلوب.',
    'slug_unique' => 'المعرف / الاسم مستخدم بالفعل.',
    'slug_string' => 'يجب أن يكون المعرف / الاسم نصًا.',
    'group_required' => 'المجموعة مطلوبة.',
    'group_invalid' => 'المجموعة المحددة غير صالحة.',
    'auth_failed' => 'بيانات الاعتماد هذه غير مطابقة لسجلاتنا.',
    // Locale-aware messages
    'name_locale_required' => 'الاسم باللغة :locale مطلوب.',
    'name_locale_string' => 'الاسم باللغة :locale يجب أن يكون نصًا.',
    'mail_settings' => [
        'smtp_host_required' => 'مطلوب إدخال مضيف SMTP.',
        'smtp_host_string' => 'يجب أن يكون مضيف SMTP نصاً صالحاً.',

        'smtp_port_required' => 'مطلوب إدخال منفذ SMTP.',
        'smtp_port_numeric' => 'يجب أن يكون منفذ SMTP رقماً.',

        'mail_username_required' => 'اسم مستخدم البريد مطلوب.',
        'mail_username_string' => 'يجب أن يكون اسم مستخدم البريد نصاً.',

        'mail_password_required' => 'كلمة مرور البريد مطلوبة.',
        'mail_password_string' => 'يجب أن تكون كلمة مرور البريد نصاً.',

        'from_email_address_required' => 'مطلوب إدخال عنوان البريد الإلكتروني للمرسل.',
        'from_email_address_string' => 'يجب أن يكون عنوان البريد الإلكتروني للمرسل نصاً صالحاً.',

        'from_name_required' => 'اسم المرسل مطلوب.',
        'from_name_string' => 'يجب أن يكون اسم المرسل نصاً.',
    ],
    'product' => [
        'name_required' => 'اسم المنتج مطلوب.',
        'name_string' => 'يجب أن يكون اسم المنتج نصًا.',
        'slug_unique' => 'المنتج موجود بنفس الاسم مسبقا يمكنتج التعديل او استخدام اسم اخر.',
        'base_price_required' => 'السعر الأساسي مطلوب.',
        'base_price_numeric' => 'يجب أن يكون السعر الأساسي رقمًا.',
        'discount_numeric' => 'يجب أن تكون قيمة الخصم رقمية.',
        'discount_type_in' => 'يجب أن يكون نوع الخصم نسبة مئوية أو ثابت.',
        'vat_numeric' => 'يجب أن تكون نسبة الضريبة رقمية.',
        'status_in' => 'يجب أن تكون حالة المنتج صحيحة.',
        'category_exists' => 'الفئة المحددة غير موجودة.',
        'tags_array' => 'يجب أن تكون الوسوم في شكل مصفوفة.',
        'tag_string' => 'يجب أن يكون كل وسم نصًا.',

        'thumbnail_exists' => 'الملف المصغر غير صالح.',
        'media_array' => 'يجب أن تكون ملفات الوسائط في شكل مصفوفة.',
        'media_exists' => 'بعض ملفات الوسائط غير صالحة.',

        'variants_array' => 'يجب إرسال الأنواع كمصفوفة.',
        'variant_sku_required' => 'رمز SKU مطلوب لكل نوع.',
        'variant_sku_unique' => 'رمز SKU مستخدم مسبقًا.',
        'variant_price_required' => 'السعر مطلوب لكل نوع.',
        'variant_price_numeric' => 'يجب أن يكون السعر رقمًا.',
        'variant_stock_required' => 'الكمية مطلوبة لكل نوع.',
        'variant_stock_integer' => 'يجب أن تكون الكمية رقمًا صحيحًا.',
        'variant_barcode_string' => 'يجب أن يكون الباركود نصًا.',
        'variant_weight_numeric' => 'يجب أن يكون الوزن رقمًا.',

        'variant_attributes_required' => 'يجب تحديد الخصائص لكل نوع.',
        'variant_attributes_array' => 'يجب أن تكون الخصائص في شكل مصفوفة.',
        'attribute_id_required' => 'معرّف الخاصية مطلوب.',
        'attribute_id_exists' => 'الخاصية المحددة غير موجودة.',
        'attribute_value_id_required' => 'قيمة الخاصية مطلوبة.',
        'attribute_value_id_exists' => 'قيمة الخاصية المحددة غير موجودة.',
    ],

    'custom' => [
        'email' => [
            'required' => 'البريد الإلكتروني مطلوب.',
            'email' => 'يرجى إدخال بريد إلكتروني صالح.',
            'unique' => 'هذا البريد الإلكتروني مسجل بالفعل. يرجى استخدام بريد إلكتروني آخر أو تسجيل الدخول.',
        ],
        'name' => [
            'required' => 'حقل الاسم مطلوب.',
            'string' => 'يجب أن يكون الاسم نصيًا.',
            'max' => 'لا يمكن أن يزيد الاسم عن :max حرفًا.',
        ],
        'organization_name' => [
            'required' => 'اسم المؤسسة مطلوب.',
        ],
        'password' => [
            'required' => 'كلمة المرور مطلوبة.',
            'confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ],
        'locale' => [
            'in' => 'اللغة المختارة غير مدعومة.',
            'required' => 'حقل اللغة مطلوب.',
        ],
        'stage_id' => [
            'required' => 'اختيار المرحلة مطلوب.',
            'integer' => 'يجب أن تكون المرحلة رقماً صحيحاً.',
            'exists' => 'المرحلة المحددة غير موجودة.',
        ],
        'opportunity_id' => [
            'required' => 'اختيار الفرصة مطلوب.',
            'integer' => 'يجب أن تكون الفرصة رقماً صحيحاً.',
            'exists' => 'الفرصة المحددة غير موجودة أو تم حذفها.',
        ],
        'template' => [
            // Basic template fields
            'name_required' => 'اسم القالب مطلوب',
            'name_string' => 'اسم القالب يجب أن يكون نص',
            'name_max' => 'اسم القالب لا يمكن أن يتجاوز :max حرف',

            'description_string' => 'الوصف يجب أن يكون نص',
            'description_max' => 'الوصف لا يمكن أن يتجاوز :max حرف',

            'category_required' => 'فئة الحملة مطلوبة',
            'category_in' => 'فئة الحملة المحددة غير صحيحة. الخيارات الصحيحة: :values',

            'template_type_required' => 'نوع القالب مطلوب',
            'template_type_in' => 'نوع القالب المحدد غير صحيح. الخيارات الصحيحة: :values',

            'content_required' => 'محتوى القالب مطلوب',
            'content_string' => 'محتوى القالب يجب أن يكون نص',

            'header_content_string' => 'محتوى الرأس يجب أن يكون نص',
            'header_content_max' => 'محتوى الرأس لا يمكن أن يتجاوز :max حرف',

            'footer_content_string' => 'محتوى التذييل يجب أن يكون نص',
            'footer_content_max' => 'محتوى التذييل لا يمكن أن يتجاوز :max حرف',

            'is_active_required' => 'حالة التفعيل مطلوبة',
            'is_active_boolean' => 'حالة التفعيل يجب أن تكون صحيح أو خطأ',

            // Template buttons validation
            'template_buttons_array' => 'أزرار القالب يجب أن تكون مصفوفة',
            'template_buttons_min' => 'مطلوب على الأقل :min زر',

            'button_text_required' => 'نص الزر مطلوب للزر في الموضع :position',
            'button_text_string' => 'نص الزر يجب أن يكون نص للزر في الموضع :position',
            'button_text_max' => 'نص الزر لا يمكن أن يتجاوز :max حرف للزر في الموضع :position',

            'button_type_required' => 'نوع الزر مطلوب للزر في الموضع :position',
            'button_type_in' => 'نوع الزر غير صحيح للزر في الموضع :position. الأنواع الصحيحة: :values',

            'action_value_required' => 'قيمة العمل مطلوبة للزر في الموضع :position',
            'action_value_string' => 'قيمة العمل يجب أن تكون نص للزر في الموضع :position',

            // Template parameters validation
            'template_parms_array' => 'معاملات القالب يجب أن تكون مصفوفة',
            'template_parms_min' => 'مطلوب على الأقل :min معامل',

            'parm_name_required' => 'اسم المعامل مطلوب للمعامل في الموضع :position',
            'parm_name_string' => 'اسم المعامل يجب أن يكون نص للمعامل في الموضع :position',
            'parm_name_max' => 'اسم المعامل لا يمكن أن يتجاوز :max حرف للمعامل في الموضع :position',

        ],
    ],
];
