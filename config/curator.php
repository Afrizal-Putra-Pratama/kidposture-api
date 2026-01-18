<?php

return [
    'disk' => 'public',
    
    'directory' => 'media',
    
    'visibility' => 'public',
    
    'is_limited_to_directory' => false,
    
    'should_preserve_filenames' => true,
    
    'should_register_navigation' => true,
    
    'navigation' => [
        'group' => 'Content',
        'icon' => 'heroicon-o-photo',
        'sort' => 3,
    ],
    
    'model' => \Awcodes\Curator\Models\Media::class,
    
    // ✅ TAMBAHKAN INI (YANG KURANG!)
    'resources' => [
        'resource' => \Awcodes\Curator\Resources\MediaResource::class,  // ✅ PENTING!
        'label' => 'Media',
        'plural_label' => 'Media',
        'navigation_group' => 'Content',
        'navigation_icon' => 'heroicon-o-photo',
        'navigation_sort' => 3,
        'navigation_count_badge' => false,
        'register_on_panel' => true,
    ],
    
    'glide' => [
        'server' => \Awcodes\Curator\Glide\DefaultServerFactory::class,
        'max_image_size' => 2000,
        'fallbacks' => [],
    ],
    
    'accepted_file_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/svg+xml',
        'image/gif',
        'application/pdf',
    ],
    
    'max_size' => 5120,  // 5MB
    
    'min_size' => 0,
    
    'image_crop_aspect_ratio' => null,
    
    'image_resize_target_width' => null,
    
    'image_resize_target_height' => null,
    
    // ✅ CURATOR PICKER OPTIONS
    'is_tenant_aware' => false,
    
    'tenant_ownership_relationship_name' => 'tenant',
    
    'cloud_disks' => [
        's3',
        'cloudinary',
        'imgix',
    ],
];
