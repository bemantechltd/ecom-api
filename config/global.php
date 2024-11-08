<?php
    return [
        'domain_title' => env('APP_NAME',''),
        'domain_url' => env('APP_DOMAIN',''),
        'http_protocol' => env('HTTP_PROTOCOL',''),
        'developer_website' => env('DEVELOPER_WEBSITE',''),
        'encrypt_text' => 'Pharmacy ecommerce solutions',
        'username' => '',

        'sms_config' => [
            'api_end_point' => 'http://66.45.237.70/api.php',
            'username'  => 'medquicker',
            'password'  => '36Z24R8W'
        ],

        'logoz_base_path' => base_path() . '/public/storage/images/logoz',
        'logoz_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/logoz',
        
        'category_icon_base_path' => base_path() . '/public/storage/images/category-iconz',
        'category_icon_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/category-iconz',

        'prescription_base_path' => base_path() . '/public/storage/images/prescriptions',
        'prescription_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/prescriptions',
        
        'product_type_icon_base_path' => base_path() . '/public/storage/images/product-type-iconz',
        'product_type_icon_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/product-type-iconz',
        
        'price_type_icon_base_path' => base_path() . '/public/storage/images/price-type-iconz',
        'price_type_icon_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/price-type-iconz',
        
        'product_return_image_base_path' => base_path() . '/public/storage/product-return-images',
        'product_return_image_base_url' => env('APP_URL','http://localhost:8037') . '/storage/product-return-images',
        
        'company_logo_base_path' => base_path() . '/public/storage/images/company-logos',
        'company_logo_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/company-logos',

        'timeline_images_base_path' => base_path() . '/public/storage/images/timeline-images',
        'timeline_images_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/timeline-images',
        
        'media_gallery_base_path' => base_path() . '/public/storage/media-gallery',
        'media_gallery_base_url' => env('APP_URL','http://localhost:8037') . '/storage/media-gallery',

        'desktop_banner_image_path' => base_path() . '/public/storage/images/promotional-banner-images/desktop',
        'desktop_banner_image_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/promotional-banner-images/desktop',

        'mobile_banner_image_path' => base_path() . '/public/storage/images/promotional-banner-images/mobile',
        'mobile_banner_image_base_url' => env('APP_URL','http://localhost:8037') . '/storage/images/promotional-banner-images/mobile'
    ]
?>