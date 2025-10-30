<?php

return [
    'identifier_required' => "L'e-mail ou le numéro de téléphone est requis.",
    'password_invalid' => 'Veuillez entrer un mot de passe valide.',
    'currency_code' => 'Le code de devise spécifié est invalide.',
    'name_required' => 'Le champ nom est requis.',
    'name_array' => 'Le nom doit être un tableau.',
    'name_min' => 'Au moins un nom est requis.',
    'description_string' => 'La description doit être une chaîne de caractères.',
    'is_active_required' => 'Le champ état est requis.',
    'is_active_boolean' => "L'état doit être vrai ou faux.",
    'slug_required' => 'Le slug est requis.',
    'slug_unique' => 'Le slug / nom est déjà utilisé.',
    'slug_string' => 'Le slug / nom doit être une chaîne de caractères.',
    'group_required' => 'Le groupe est requis.',
    'group_invalid' => 'Le groupe spécifié est invalide.',
    'auth_failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',

    // Locale-aware messages
    'name_locale_required' => 'Le nom en :locale est requis.',
    'name_locale_string' => 'Le nom en :locale doit être une chaîne de caractères.',

    'mail_settings' => [
        'smtp_host_required' => 'L’hôte SMTP est requis.',
        'smtp_host_string' => 'L’hôte SMTP doit être une chaîne valide.',

        'smtp_port_required' => 'Le port SMTP est requis.',
        'smtp_port_numeric' => 'Le port SMTP doit être un nombre.',

        'mail_username_required' => "Le nom d'utilisateur de messagerie est requis.",
        'mail_username_string' => "Le nom d'utilisateur de messagerie doit être une chaîne.",

        'mail_password_required' => 'Le mot de passe de messagerie est requis.',
        'mail_password_string' => 'Le mot de passe de messagerie doit être une chaîne.',

        'from_email_address_required' => "L'adresse e-mail de l’expéditeur est requise.",
        'from_email_address_string' => "L'adresse e-mail de l’expéditeur doit être une chaîne valide.",

        'from_name_required' => 'Le nom de l’expéditeur est requis.',
        'from_name_string' => 'Le nom de l’expéditeur doit être une chaîne.',
    ],
    'product' => [
        'name_required' => 'Le nom du produit est requis.',
        'name_string' => 'Le nom du produit doit être une chaîne de caractères.',
        'slug_unique' => 'Un produit portant le même nom existe déjà. Vous pouvez le modifier ou utiliser un autre nom.',
        'base_price_required' => 'Le prix de base est requis.',
        'base_price_numeric' => 'Le prix de base doit être numérique.',
        'discount_numeric' => 'La remise doit être un nombre.',
        'discount_type_in' => 'Le type de remise doit être pourcentage ou fixe.',
        'vat_numeric' => 'La TVA doit être numérique.',
        'status_in' => 'Le statut du produit est invalide.',
        'category_exists' => 'La catégorie sélectionnée n’existe pas.',
        'tags_array' => 'Les balises doivent être un tableau.',
        'tag_string' => 'Chaque balise doit être une chaîne.',

        'thumbnail_exists' => 'Le fichier miniature est invalide.',
        'media_array' => 'Les médias doivent être un tableau.',
        'media_exists' => 'Certains fichiers multimédias sont invalides.',

        'variants_array' => 'Les variantes doivent être un tableau.',
        'variant_sku_required' => 'Le SKU est requis pour chaque variante.',
        'variant_sku_unique' => 'Le SKU doit être unique.',
        'variant_price_required' => 'Le prix est requis pour chaque variante.',
        'variant_price_numeric' => 'Le prix de la variante doit être numérique.',
        'variant_stock_required' => 'Le stock est requis pour chaque variante.',
        'variant_stock_integer' => 'Le stock doit être un entier.',
        'variant_barcode_string' => 'Le code-barres doit être une chaîne.',
        'variant_weight_numeric' => 'Le poids doit être numérique.',

        'variant_attributes_required' => 'Chaque variante doit avoir des attributs.',
        'variant_attributes_array' => 'Les attributs doivent être un tableau.',
        'attribute_id_required' => 'L’ID de l’attribut est requis.',
        'attribute_id_exists' => 'L’attribut sélectionné n’existe pas.',
        'attribute_value_id_required' => 'La valeur de l’attribut est requise.',
        'attribute_value_id_exists' => 'La valeur de l’attribut sélectionnée n’existe pas.',
    ],
    'custom' => [
        'email' => [
            'required' => "L'adresse e-mail est requise.",
            'email' => 'Veuillez saisir une adresse e-mail valide.',
            'unique' => 'Cette adresse e-mail est déjà enregistrée. Veuillez en utiliser une autre ou vous connecter.',
        ],
        'name' => [
            'required' => 'Le nom est requis.',
        ],
        'organization_name' => [
            'required' => "Le nom de l'organisation est requis.",
        ],
        'password' => [
            'required' => 'Le mot de passe est requis.',
            'confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ],
        'locale' => [
            'in' => 'La langue sélectionnée n\'est pas valide.',
            'required' => 'Le champ langue est obligatoire.',
        ],
        'stage_id' => [
            'required' => 'La sélection de l\'étape est requise.',
            'integer' => 'L\'étape doit être un nombre valide.',
            'exists' => 'L\'étape sélectionnée n\'existe pas.',
        ],
        'opportunity_id' => [
            'required' => 'La sélection de l\'opportunité est requise.',
            'integer' => 'L\'opportunité doit être un nombre valide.',
            'exists' => 'L\'opportunité sélectionnée n\'existe pas ou a été supprimée.',
        ],
        'template' => [
            // Basic template fields
            'name_required' => 'Le nom du modèle est requis',
            'name_string' => 'Le nom du modèle doit être une chaîne de caractères',
            'name_max' => 'Le nom du modèle ne peut pas dépasser :max caractères',

            'description_string' => 'La description doit être une chaîne de caractères',
            'description_max' => 'La description ne peut pas dépasser :max caractères',

            'category_required' => 'La catégorie de campagne est requise',
            'category_in' => 'La catégorie de campagne sélectionnée est invalide. Options valides: :values',

            'template_type_required' => 'Le type de modèle est requis',
            'template_type_in' => 'Le type de modèle sélectionné est invalide. Options valides: :values',

            'content_required' => 'Le contenu du modèle est requis',
            'content_string' => 'Le contenu du modèle doit être une chaîne de caractères',

            'header_content_string' => 'Le contenu de l\'en-tête doit être une chaîne de caractères',
            'header_content_max' => 'Le contenu de l\'en-tête ne peut pas dépasser :max caractères',

            'footer_content_string' => 'Le contenu du pied de page doit être une chaîne de caractères',
            'footer_content_max' => 'Le contenu du pied de page ne peut pas dépasser :max caractères',

            'is_active_required' => 'Le statut d\'activation est requis',
            'is_active_boolean' => 'Le statut d\'activation doit être vrai ou faux',

            // Template buttons validation
            'template_buttons_array' => 'Les boutons du modèle doivent être un tableau',
            'template_buttons_min' => 'Au moins :min bouton est requis',

            'button_text_required' => 'Le texte du bouton est requis pour le bouton à la position :position',
            'button_text_string' => 'Le texte du bouton doit être une chaîne pour le bouton à la position :position',
            'button_text_max' => 'Le texte du bouton ne peut pas dépasser :max caractères pour le bouton à la position :position',

            'button_type_required' => 'Le type de bouton est requis pour le bouton à la position :position',
            'button_type_in' => 'Type de bouton invalide pour le bouton à la position :position. Types valides: :values',

            'action_value_required' => 'La valeur d\'action est requise pour le bouton à la position :position',
            'action_value_string' => 'La valeur d\'action doit être une chaîne pour le bouton à la position :position',

            // Template parameters validation
            'template_parms_array' => 'Les paramètres du modèle doivent être un tableau',
            'template_parms_min' => 'Au moins :min paramètre est requis',

            'parm_name_required' => 'Le nom du paramètre est requis pour le paramètre à la position :position',
            'parm_name_string' => 'Le nom du paramètre doit être une chaîne pour le paramètre à la position :position',
            'parm_name_max' => 'Le nom du paramètre ne peut pas dépasser :max caractères pour le paramètre à la position :position',

        ],
    ],
];
