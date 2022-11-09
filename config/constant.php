<?php

return [
    //System Roles
    'roles' => [
        'superadmin'      => 'superadmin',
        'admin'           => 'admin',
        'creator'         => 'creator',
        'agent'           => 'agent',
        'user'            => 'user',
        'district-leader' => 'district-leader',
        'state-leader'    => 'state-leader',
        'core-team'       => 'core-team',
    ],

    //Status
    'status' => [
        'active_value'     => 'active',
        'active_label'     => 'Active',
        'inactive_value'   => 'inactive',
        'inactive_label'   => 'Inactive',
        'processing_value' => 'processing',
        'processing_label' => 'Processing',
        'requested_value'  => 'requested',
        'requested_label'  => 'Pending',
        'approved_value'   => 'approved',
        'approved_label'   => 'Approved',
        'rejected_value'   => 'rejected',
        'rejected_label'   => 'Rejected',
        'booked_value'     => 'booked',
        'booked_label'     => 'Booked',
        'attended_value'   => 'attended',
        'attended_label'   => 'Attended',
        'missed_value'     => 'missed',
        'missed_label'     => 'Missed',
        'in_progress_value' => 'in-progress',
        'in_progress_label' => 'In Progress',
        'cancelled_value' => 'cancelled',
        'cancelled_label' => 'Cancelled',
        'failed_value' => 'failed',
        'failed_label' => 'Failed',
        'completed_value' => 'completed',
        'completed_label' => 'Completed',
        'failed_value' => 'failed',
        'failed_label' => 'Failed',
    ],

    //Gender
    'gender' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ],

    //Invitation Status
    'invitation' => [
        'approve' => 'approve',
        'reject' => 'reject',
    ],

    //Default Pagination
    'default_pagination' => 10,

    //Bunny.net streaming based URL
    'video_base_url'   => env('VIDEO_BASE_URL_BUNNY', 'https://vz-bdc40e4d-eda.b-cdn.net/'),
    'video_library_id' => env('VIDEO_LIBRARY_ID', '48549'),
    'video_api_key'    => env('VIDEO_API_KEY', '0e84771b-708e-41ec-b42b546597fe-4aba-4022'),
    'video_api_url'    => env('VIDEO_API_URL', 'https://video.bunnycdn.com/library/'),
    
    //Paytm settings
    'paytm_merchant_id'  => env('PAYTM_MERCHANT_ID', 'sflkTw60138434558880'),
    'paytm_merchant_key' => env('PAYTM_MERCHANT_KEY', 'NPGq9uh8ar%_UZk7'),
    'paytm_website'      => env('PAYTM_WEBSITE', 'WEBSTAGING'),
    'paytm_callback'     => env('PAYTM_CALLBACK', 'WEBSTAGING'),
    'paytm_gateway_base_url'     => env('PAYTM_GATEWAY_BASE_URL', 'https://securegw-stage.paytm.in'),
    'paytm_gateway_url'  => env('PAYTM_GATEWAY_URL', 'https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction'),

    //Filter By
    'filter_by' => [
        'today'      => 'today',
        'yesterday'  => 'yesterday',
        '7-day'      => '7-day',
        'this-month' => 'this-month',
        'last-month' => 'last-month',
        'all'        => 'all',
    ],

    //Pusher Chat Keys
    'PUSHER_BEAM_INSTANCE_ID' => env('PUSHER_BEAM_INSTANCE_ID'),
    'PUSHER_BEAM_SECRET_KEY' => env('PUSHER_BEAM_SECRET_KEY'),

    //RazorPay settings
    'razorpay_key'    => env('RAZORPAY_KEY', 'rzp_test_aLQum1rpHCH3rS'),
    'razorpay_secret' => env('RAZORPAY_SECRET', 'UTEvxXADIwfoLkd7C8Nm1b7x'),

    //Playstore URL
    'playstore_url' => env('PLAYSTORE_URL', 'https://play.google.com/store/apps/details?id=com.riser.wespiremedia'),

    //Zego Cloud Settings
    'zego_app_id' => env('ZEGO_APP_ID', '940185957'),
    'zego_app_secret' => env('ZEGO_APP_SECRET', '061c073324b7f9ce8662e0a4002a6544'),
    
    'rupee_symbol' => '₹',

    //payment gateway enable/disable
    'is_paytm' => env('IS_PAYTM', 'true'),
    'is_razorpay' => env('IS_RAZORPAY', 'true'),

];
