<?php

/**
 * Configuration par table
 * 
 * Personnaliser le comportement pour des tables spécifiques
 * Tous les paramètres sont optionnels — les valeurs par défaut seront utilisées si non définies
 */

$hiddenColumnsDefault = [
    'created_at',
    'updated_at',
    'deleted_at',
];

$readOnlyColumnsDefault = [
    'created_at',
    'updated_at',
    'deleted_at',
];

return [
    'default'=>[
        // Colonnes cachées par défaut
        'hidden_columns' => $hiddenColumnsDefault,
        
        // Colonnes en lecture seule par défaut
        'readonly_columns' => $readOnlyColumnsDefault,
    ],

    'users' => [
        // Colonne utilisée pour les labels dans les listes déroulantes
        'display_column' => 'username',
        
        // Nom d’affichage de la table
        'label' => 'Utilisateurs & Rôles',
        
        // Colonnes cachées dans les formulaires et les vues
        'hidden_columns' => [
            'password',
            'remember_token',
        ],
        
        // Colonnes non modifiables
        'readonly_columns' => [
            'created_at',
            'updated_at',
            'deleted_at',
        ],
        
        // Labels personnalisés des colonnes
        'labels' => [
            'first_name' => 'Prénom',
            'last_name'  => 'Nom',
            'domain_research' => 'Domaine de recherche',
        ],
        
        // Désactiver la suppression pour cette table
        'disable_delete' => false,
    ],
    
    'equipes' => [
        'display_column' => 'name',
        'label' => 'Équipes',
        
        'labels' => [
            'name' => 'Nom de l’équipe',
            'description' => 'Description de l’équipe',
        ],
    ],
    
    'publications' => [
        'display_column' => 'title',
        'label' => 'Publications',
    ],
    
    'ressources' => [
        'display_column' => 'name',
        'label' => 'Ressources',
        
        'labels' => [
            'name' => 'Nom de la ressource',
            'type' => 'Type de ressource',
            'status' => 'Statut de disponibilité',
        ],
    ],
];
