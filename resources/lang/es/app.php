<?php

return [
    'already_active' => 'La suscripción ya está activa.',
    'already_canceled' => 'La suscripción ya está cancelada.',
    'already_expired' => 'La suscripción ya ha expirado.',
    'already_suspended' => 'La suscripción ya está suspendida.',
    'already_past_due' => 'La suscripción ya está vencida.',

    'cannot_activate_canceled' => 'No se puede activar una suscripción cancelada.',
    'cannot_expire_canceled' => 'No se puede expirar una suscripción cancelada.',
    'cannot_suspend_canceled' => 'No se puede suspender una suscripción cancelada.',
    'cannot_mark_past_due_canceled' => 'No se puede marcar una suscripción cancelada como vencida.',

    'cannot_expire_pending' => 'No se puede expirar una suscripción pendiente.',
    'cannot_suspend_pending' => 'No se puede suspender una suscripción pendiente.',
    'cannot_mark_past_due_pending' => 'No se puede marcar una suscripción pendiente como vencida.',

    'cannot_suspend_expired' => 'No se puede suspender una suscripción expirada.',
    'cannot_mark_past_due_expired' => 'No se puede marcar una suscripción expirada como vencida.',

    // Days of the week
    'days' => [
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ],
    'cannot_mark_past_due_suspended' => 'No se puede marcar una suscripción suspendida como vencida.',

    // feature----------------------------------
    'limit' => 'Límites',
    'feature' => 'Funciones',
    'active' => 'Activo',
    'inactive' => 'Inactivo',
    'tenant_missing' => 'No pudimos encontrar tu cuenta. Por favor revisa el enlace que usaste o contacta al soporte.',
    'subscription' => [
        'pending' => 'Pendiente',
        'active' => 'Activo',
        'canceled' => 'Cancelado',
        'expired' => 'Expirado',
        'suspended' => 'Suspendido',
        'past_due' => 'Vencido',
    ],
    'stripe' => [
        'incorrect_number' => 'El número de tarjeta es incorrecto.',
        'invalid_number' => 'El número de tarjeta no es válido.',
        'invalid_expiry_month' => 'El mes de vencimiento no es válido.',
        'invalid_expiry_year' => 'El año de vencimiento no es válido.',
        'invalid_cvc' => 'El código de seguridad no es válido.',
        'expired_card' => 'La tarjeta ha caducado.',
        'incorrect_cvc' => 'El código de seguridad es incorrecto.',
        'incorrect_zip' => 'El código postal no es válido.',
        'card_declined' => 'La tarjeta fue rechazada.',
        'processing_error' => 'Se produjo un error al procesar la tarjeta.',
        'rate_limit' => 'Demasiadas solicitudes, inténtelo más tarde.',

        'insufficient_funds' => 'Fondos insuficientes en la tarjeta.',
        'lost_card' => 'La tarjeta está reportada como perdida.',
        'stolen_card' => 'La tarjeta está reportada como robada.',
        'do_not_honor' => 'La tarjeta fue rechazada por el emisor.',
        'transaction_not_allowed' => 'Esta transacción no está permitida en la tarjeta.',

        'default' => 'Ocurrió un error al procesar el pago. Intente de nuevo o use otra tarjeta.',
    ],
    'integration' => [
        'shopify_hmac_exception_message' => 'Error de autenticación: no pudimos verificar la solicitud de Shopify. Por favor, inténtalo de nuevo.',
        'shopify_token_exchange_exception_message' => 'Error de conexión: no pudimos completar la autenticación con Shopify. Por favor, inténtalo de nuevo.',
        'shopify_tenant_not_exists_exception_message' => 'No pudimos encontrar tu cuenta. Por favor, verifica e inténtalo de nuevo.',
        'shopify_platform_not_exists_exception_message' => 'No se pudo conectar con Shopify porque falta la configuración. Por favor, contacta con soporte.',
    ],
    'activation_code' => [
        'status' => [
            'pending' => 'Pendiente',
            'available' => 'Disponible',
            'used' => 'Usado',
            'expired' => 'Expirado',
            'blocked' => 'Bloqueado',
        ],
    ],
    'task_status' => [
        'pending' => 'Pendiente',
        'in_progress' => 'En progreso',
        'completed' => 'Completado',
        'overdue' => 'Atrasado',
        'cancelled' => 'Cancelado',
    ],
    'priority' => [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],
    'tenant_user' => [
        'name_required' => 'El nombre es obligatorio.',
        'name_string' => 'El nombre debe ser una cadena de texto.',
        'email_required' => 'El correo electrónico es obligatorio.',
        'email_email' => 'El correo electrónico debe ser una dirección de correo válida.',
        'email_unique' => 'Este correo electrónico ya está en uso.',
        'role_id_required' => 'El rol es obligatorio.',
        'role_id_integer' => 'El ID de rol debe ser un número entero.',
        'role_id_exists' => 'El rol seleccionado no existe.',
        'phone_string' => 'El teléfono debe ser una cadena de texto.',
        'department_id_integer' => 'El ID del departamento debe ser un número entero.',
        'department_id_exists' => 'El departamento seleccionado no existe.',
        'tenant_id_required' => 'El inquilino es obligatorio.',
        'tenant_id_integer' => 'El ID del inquilino debe ser un número entero.',
        'tenant_id_exists' => 'El inquilino seleccionado no existe.',
    ],
    'collections' => [
        'status' => [
            'pending' => 'Pendiente',
            'collected' => 'Cobrado',
        ],
    ],
    'stage' => [
        'name_required' => 'El nombre de la etapa es obligatorio.',
        'name_string' => 'El nombre de la etapa debe ser una cadena de texto.',
        'name_max' => 'El nombre de la etapa no puede tener más de 255 caracteres.',
        'workflow_id_required' => 'El ID del flujo de trabajo es obligatorio.',
        'workflow_id_integer' => 'El ID del flujo de trabajo debe ser un número entero.',
        'workflow_id_exists' => 'El flujo de trabajo seleccionado no existe.',
        'is_active_required' => 'El estado activo es obligatorio.',
        'is_active_boolean' => 'El estado activo debe ser verdadero o falso.',
    ],
    'feedback_status' => [
        'new' => 'Nuevo',
        'responded' => 'Respondido',
        'resolved' => 'Resuelto',
        'escalated' => 'Escalado',
    ],
    'opportunity' => [
        'status' => [
            'active' => 'Activo',
            'lost' => 'Perdido',
            'won' => 'Ganado',
            'abandoned' => 'Abandonado',
        ],
    ],
];
