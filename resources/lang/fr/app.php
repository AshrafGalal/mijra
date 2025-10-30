<?php

return [
    'already_active' => 'L’abonnement est déjà actif.',
    'already_canceled' => 'L’abonnement est déjà annulé.',
    'already_expired' => 'L’abonnement est déjà expiré.',
    'already_suspended' => 'L’abonnement est déjà suspendu.',
    'already_past_due' => 'L’abonnement est déjà en retard de paiement.',

    'cannot_activate_canceled' => 'Impossible d’activer un abonnement annulé.',
    'cannot_expire_canceled' => 'Impossible d’expirer un abonnement annulé.',
    'cannot_suspend_canceled' => 'Impossible de suspendre un abonnement annulé.',
    'cannot_mark_past_due_canceled' => 'Impossible de marquer un abonnement annulé comme en retard de paiement.',

    'cannot_expire_pending' => 'Impossible d’expirer un abonnement en attente.',
    'cannot_suspend_pending' => 'Impossible de suspendre un abonnement en attente.',
    'cannot_mark_past_due_pending' => 'Impossible de marquer un abonnement en attente comme en retard de paiement.',

    'cannot_suspend_expired' => 'Impossible de suspendre un abonnement expiré.',
    'cannot_mark_past_due_expired' => 'Impossible de marquer un abonnement expiré comme en retard de paiement.',

    // Days of the week
    'days' => [
        'monday' => 'Lundi',
        'tuesday' => 'Mardi',
        'wednesday' => 'Mercredi',
        'thursday' => 'Jeudi',
        'friday' => 'Vendredi',
        'saturday' => 'Samedi',
        'sunday' => 'Dimanche',
    ],
    'cannot_mark_past_due_suspended' => 'Impossible de marquer un abonnement suspendu comme en retard de paiement.',

    // feature----------------------------------
    'limit' => 'Limites',
    'feature' => 'Fonctionnalités',
    'active' => 'Actif',
    'inactive' => 'Inactif',
    'tenant_missing' => "Nous n'avons pas pu trouver votre compte. Veuillez vérifier le lien que vous avez utilisé ou contacter le support.",
    'subscription' => [
        'pending' => 'En attente',
        'active' => 'Actif',
        'canceled' => 'Annulé',
        'expired' => 'Expiré',
        'suspended' => 'Suspendu',
        'past_due' => 'En retard',
    ],
    'stripe' => [
        'incorrect_number' => 'Le numéro de carte est incorrect.',
        'invalid_number' => 'Le numéro de carte est invalide.',
        'invalid_expiry_month' => 'Le mois d’expiration est invalide.',
        'invalid_expiry_year' => 'L’année d’expiration est invalide.',
        'invalid_cvc' => 'Le code de sécurité est invalide.',
        'expired_card' => 'La carte a expiré.',
        'incorrect_cvc' => 'Le code de sécurité est incorrect.',
        'incorrect_zip' => 'Le code postal est invalide.',
        'card_declined' => 'La carte a été refusée.',
        'processing_error' => 'Une erreur est survenue lors du traitement de la carte.',
        'rate_limit' => 'Trop de requêtes, veuillez réessayer plus tard.',

        'insufficient_funds' => 'Fonds insuffisants sur la carte.',
        'lost_card' => 'La carte est signalée perdue.',
        'stolen_card' => 'La carte est signalée volée.',
        'do_not_honor' => 'La carte a été refusée par l’émetteur.',
        'transaction_not_allowed' => 'Cette transaction n’est pas autorisée sur la carte.',

        'default' => 'Une erreur est survenue lors du paiement. Veuillez réessayer ou utiliser une autre carte.',

    ],
    'integration' => [
        'shopify_hmac_exception_message' => 'Échec de l’authentification : nous n’avons pas pu vérifier la demande de Shopify. Veuillez réessayer.',
        'shopify_token_exchange_exception_message' => 'Échec de la connexion : nous n’avons pas pu terminer l’authentification avec Shopify. Veuillez réessayer.',
        'shopify_tenant_not_exists_exception_message' => 'Nous n’avons pas pu trouver votre compte. Veuillez vérifier et réessayer.',
        'shopify_platform_not_exists_exception_message' => 'Impossible de se connecter à Shopify car la configuration est manquante. Veuillez contacter l’assistance',
    ],
    'activation_code' => [
        'status' => [
            'pending' => 'En attente',
            'available' => 'Disponible',
            'used' => 'Utilisé',
            'expired' => 'Expiré',
            'blocked' => 'Bloqué',
        ],
    ],
    'task_status' => [
        'pending' => 'En attente',
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        'overdue' => 'En retard',
        'cancelled' => 'Annulé',
    ],
    'tenant_user' => [
        'name_required' => 'Name is required.',
        'name_string' => 'Name must be a string.',
        'email_required' => 'Email is required.',
        'email_email' => 'Email must be a valid email address.',
        'email_unique' => 'This email is already taken.',
        'role_id_required' => 'Role is required.',
        'role_id_integer' => 'Role ID must be an integer.',
        'role_id_exists' => 'Selected role does not exist.',
        'phone_string' => 'Phone must be a string.',
        'department_id_integer' => 'Department ID must be an integer.',
        'department_id_exists' => 'Selected department does not exist.',
        'tenant_id_required' => 'Tenant is required.',
        'tenant_id_integer' => 'Tenant ID must be an integer.',
        'tenant_id_exists' => 'Selected tenant does not exist.',
    ],
    'collections' => [
        'status' => [
            'pending' => 'En attente',
            'collected' => 'Collecté',
        ],
    ],
    'stage' => [
        'name_required' => 'Le nom de l’étape est requis.',
        'name_string' => 'Le nom de l’étape doit être une chaîne de caractères.',
        'name_max' => 'Le nom de l’étape ne peut pas dépasser 255 caractères.',
        'workflow_id_required' => "L'identifiant du workflow est requis.",
        'workflow_id_integer' => "L'identifiant du workflow doit être un entier.",
        'workflow_id_exists' => 'Le workflow sélectionné est introuvable.',
        'is_active_required' => 'Le statut actif est requis.',
        'is_active_boolean' => 'Le statut actif doit être vrai ou faux.',
    ],
    'priority' => [
        'low' => 'Faible',
        'medium' => 'Moyenne',
        'high' => 'Élevée',
        'urgent' => 'Urgente',
    ],
    'feedback_status' => [
        'new' => 'Nouveau',
        'responded' => 'Répondu',
        'resolved' => 'Résolu',
        'escalated' => 'Escaladé',
    ],
    'opportunity' => [
        'status' => [
            'active' => 'Actif',
            'lost' => 'Perdu',
            'won' => 'Gagné',
            'abandoned' => 'Abandonné',
        ],
    ],
];
