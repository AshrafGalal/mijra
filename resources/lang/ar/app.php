<?php

return [
    'already_active' => 'الاشتراك مفعل بالفعل.',
    'already_canceled' => 'الاشتراك ملغى بالفعل.',
    'already_expired' => 'الاشتراك منتهي بالفعل.',
    'already_suspended' => 'الاشتراك معلق بالفعل.',
    'already_past_due' => 'الاشتراك متأخر بالفعل.',
    'cannot_activate_canceled' => 'لا يمكن تفعيل اشتراك ملغى.',
    'cannot_expire_canceled' => 'لا يمكن إنهاء اشتراك ملغى.',
    'cannot_suspend_canceled' => 'لا يمكن تعليق اشتراك ملغى.',
    'cannot_mark_past_due_canceled' => 'لا يمكن وضع اشتراك ملغى كمتأخر.',
    'cannot_expire_pending' => 'لا يمكن إنهاء اشتراك معلق.',
    'cannot_suspend_pending' => 'لا يمكن تعليق اشتراك معلق.',
    'cannot_mark_past_due_pending' => 'لا يمكن وضع اشتراك معلق كمتأخر.',
    'cannot_suspend_expired' => 'لا يمكن تعليق اشتراك منتهي.',
    'cannot_mark_past_due_expired' => 'لا يمكن وضع اشتراك منتهي كمتأخر.',
    'cannot_mark_past_due_suspended' => 'لا يمكن وضع اشتراك معلق كمتأخر.',

    // Days of the week
    'days' => [
        'monday' => 'الاثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
        'saturday' => 'السبت',
        'sunday' => 'الأحد',
    ],

    // feature----------------------------------
    'limit' => 'القيود',
    'feature' => 'الميزات',

    'active' => 'نشط',
    'inactive' => 'غير نشط',

    'tenant_missing' => 'تعذر العثور على حسابك. يرجى التحقق من الرابط أو التواصل مع الدعم.',
    'subscription' => [
        'pending' => 'قيد الانتظار',
        'active' => 'نشط',
        'canceled' => 'ملغي',
        'expired' => 'منتهي',
        'suspended' => 'معلق',
        'past_due' => 'متأخر عن السداد',
    ],
    'stripe' => [
        'incorrect_number' => 'رقم البطاقة غير صحيح.',
        'invalid_number' => 'رقم البطاقة غير صالح.',
        'invalid_expiry_month' => 'شهر انتهاء البطاقة غير صالح.',
        'invalid_expiry_year' => 'سنة انتهاء البطاقة غير صالحة.',
        'invalid_cvc' => 'رمز الأمان غير صالح.',
        'expired_card' => 'انتهت صلاحية البطاقة.',
        'incorrect_cvc' => 'رمز الأمان غير صحيح.',
        'incorrect_zip' => 'رمز البريد غير صحيح.',
        'card_declined' => 'تم رفض البطاقة.',
        'processing_error' => 'حدث خطأ أثناء معالجة البطاقة.',
        'rate_limit' => 'عدد كبير جداً من الطلبات، حاول مرة أخرى لاحقاً.',

        'insufficient_funds' => 'الرصيد غير كافٍ في البطاقة.',
        'lost_card' => 'البطاقة مفقودة.',
        'stolen_card' => 'البطاقة مسروقة.',
        'do_not_honor' => 'تم رفض البطاقة من البنك المصدر.',
        'transaction_not_allowed' => 'هذه العملية غير مسموحة على البطاقة.',

        'default' => 'حدث خطأ أثناء معالجة الدفع. الرجاء المحاولة مرة أخرى أو استخدام بطاقة مختلفة.',
    ],
    'integration' => [
        'shopify_hmac_exception_message' => 'فشل تسجيل الدخول: لم نتمكن من التحقق من الطلب من شوبيفاي. يرجى المحاولة مرة أخرى.',
        'shopify_token_exchange_exception_message' => 'فشل الاتصال: لم نتمكن من إكمال المصادقة مع شوبيفاي. يرجى المحاولة مرة أخرى.',
        'shopify_tenant_not_exists_exception_message' => 'لم نتمكن من العثور على حسابك. يرجى التحقق والمحاولة مرة أخرى.',
        'shopify_platform_not_exists_exception_message' => 'تعذر الاتصال بشوبيفاي لعدم وجود الإعدادات اللازمة. يرجى التواصل مع الدعم.',
    ],
    'activation_code' => [
        'status' => [
            'pending' => 'قيد الانتظار',
            'available' => 'متاح',
            'used' => 'مستخدم',
            'expired' => 'منتهي الصلاحية',
            'blocked' => 'محظور',
        ],
    ],
    'task_status' => [
        'pending' => 'قيد الانتظار',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'overdue' => 'متأخر',
        'cancelled' => 'ملغي',
    ],
    'priority' => [
        'low' => 'منخفض',
        'medium' => 'متوسط',
        'high' => 'مرتفع',
        'urgent' => 'عاجل',
    ],
    'tenant_user' => [
        'name_required' => 'الاسم مطلوب.',
        'name_string' => 'يجب أن يكون الاسم نصًا.',
        'email_required' => 'البريد الإلكتروني مطلوب.',
        'email_email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.',
        'email_unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
        'role_id_required' => 'الدور مطلوب.',
        'role_id_integer' => 'يجب أن يكون معرف الدور رقمًا صحيحًا.',
        'role_id_exists' => 'الدور المحدد غير موجود.',
        'phone_string' => 'يجب أن يكون رقم الهاتف نصًا.',
        'department_id_integer' => 'يجب أن يكون معرف القسم رقمًا صحيحًا.',
        'department_id_exists' => 'القسم المحدد غير موجود.',
        'tenant_id_required' => 'المستأجر مطلوب.',
        'tenant_id_integer' => 'يجب أن يكون معرف المستأجر رقمًا صحيحًا.',
        'tenant_id_exists' => 'المستأجر المحدد غير موجود.',
    ],
    'collections' => [
        'status' => [
            'pending' => 'قيد الانتظار',
            'collected' => 'تم التحصيل',
        ],
    ],

    'stage' => [
        'name_required' => 'اسم المرحلة مطلوب.',
        'name_string' => 'يجب أن يكون اسم المرحلة نصًا.',
        'name_max' => 'يجب ألا يزيد اسم المرحلة عن 255 حرفًا.',
        'workflow_id_required' => 'معرف سير العمل مطلوب.',
        'workflow_id_integer' => 'يجب أن يكون معرف سير العمل رقمًا صحيحًا.',
        'workflow_id_exists' => 'سير العمل المحدد غير موجود.',
        'is_active_required' => 'حالة التفعيل مطلوبة.',
        'is_active_boolean' => 'يجب أن تكون حالة التفعيل صحيحة أو خاطئة.',
    ],
    'feedback_status' => [
        'new' => 'جديد',
        'responded' => 'تم الرد',
        'resolved' => 'تم الحل',
        'escalated' => 'تم التصعيد',
    ],
    'opportunity' => [
        'status' => [
            'active' => 'نشط',
            'lost' => 'خاسر',
            'won' => 'فائز',
            'abandoned' => 'مهمل',
        ],
    ],
];
