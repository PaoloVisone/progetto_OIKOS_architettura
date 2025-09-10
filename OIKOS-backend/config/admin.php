<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazioni per il pannello amministrativo
    |
    */

    'name' => env('ADMIN_NAME', 'Admin Panel'),

    'logo' => env('ADMIN_LOGO', null),

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        'max_file_size' => 20 * 1024 * 1024, // 20MB in bytes
        'max_files_per_upload' => 10,
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'allowed_video_types' => ['mp4', 'mov', 'avi', 'webm'],
        'thumbnail_size' => 300,
        'thumbnail_quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 10,
        'projects_per_page' => 12,
        'media_per_page' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'recent_projects_limit' => 5,
        'show_stats_cards' => true,
        'show_chart' => true,
        'chart_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Settings
    |--------------------------------------------------------------------------
    */
    'projects' => [
        'auto_generate_slug' => true,
        'require_featured_image' => false,
        'default_status' => 'draft',
        'enable_seo_fields' => true,
        'max_title_length' => 255,
        'max_description_length' => 500,
        'max_meta_title_length' => 60,
        'max_meta_description_length' => 160,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'require_admin_field' => true,
        'logout_on_browser_close' => false,
        'session_timeout' => 120, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'theme' => 'light', // light, dark, auto
        'sidebar_collapsed' => false,
        'show_breadcrumbs' => true,
        'show_timestamps' => true,
        'date_format' => 'd/m/Y H:i',
        'timezone' => 'Europe/Rome',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'enable_search' => true,
        'enable_filters' => true,
        'enable_bulk_actions' => true,
        'enable_export' => true,
        'enable_import' => false,
        'enable_api' => true,
        'enable_activity_log' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => 'public',
        'path' => 'projects',
        'image_path' => 'projects/images',
        'video_path' => 'projects/videos',
        'thumbnail_path' => 'projects/thumbnails',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'enable_caching' => true,
        'cache_duration' => 3600, // seconds
        'enable_compression' => true,
        'lazy_load_images' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => false,
        'schedule' => 'daily',
        'keep_backups' => 7,
        'include_media' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email_on_new_project' => false,
        'email_on_media_upload' => false,
        'slack_webhook' => env('ADMIN_SLACK_WEBHOOK'),
        'discord_webhook' => env('ADMIN_DISCORD_WEBHOOK'),
    ],
];
