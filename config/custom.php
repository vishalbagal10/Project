<?php

return [
    //OLD Theme
    //'pie_chartsec78_bg_color' => env('pie_chartsec78_bg_color',['#ffcd03', '#a7a8a9']),

    'pie_chartsec78_bg_color' => env('pie_chartsec78_bg_color',['#0097BB', '#DCD2C9']),

    'genre_bg_color_array' => env('genre_bg_color_array', ['#fff0c7', '#ffe69f', '#ffcd03', '#ebbc00', '#d8ad00', '#ffd6de', '#fbbac6', '#f39eae', '#dd8797', '#d27788', '#bbbbbd', '#aaabad', '#97989a', '#86878a', '#76777a', '#ffcd03', '#f39eae']),

    //OLD Theme
    //'music_type_usage_color_array' => env('music_type_usage_color_array', ['owned'=>'#88AB88', 'publicdomainlicensed'=>'#E6A1AD', 'stock'=>'#F8CD46', 'licensed'=>'#C57B88', 'custom'=>'#F7D7DD', 'nomusic'=>'#B8B8B8']),

    //'music_type_usage_lighter_color_array' => env('music_type_usage_lighter_color_array', ['owned'=>'#adccad', 'publicdomainlicensed'=>'#f1bbc5', 'stock'=>'#fbde83', 'licensed'=>'#dca1ab', 'custom'=>'#feebef', 'nomusic'=>'#d8d8d8']),

    'music_type_usage_color_array' => env('music_type_usage_color_array', ['owned'=>'#05A885', 'publicdomainlicensed'=>'#15ABCE', 'stock'=>'#C84662', 'licensed'=>'#5CCFCD', 'custom'=>'#2E8EFF', 'nomusic'=>'#DCD2C9']),

    'music_type_usage_lighter_color_array' => env('music_type_usage_lighter_color_array', ['owned'=>'#05A885', 'publicdomainlicensed'=>'#15ABCE', 'stock'=>'#C84662', 'licensed'=>'#5CCFCD', 'custom'=>'#2E8EFF', 'nomusic'=>'#DCD2C9']),

    'datasets10_bg_color_array' => env('datasets10_bg_color_array', ['#fff0c7', '#ffe69f', '#ffcd03', '#ebbc00', '#d8ad00', '#f5dde1', '#e7adb7', '#dd8797', '#d27788']),

    'section10_bg_image_array' => env('section10_bg_image_array', ['01.jpg','02.jpg','03.jpg','04.jpg','05.jpg','06.jpg','07.jpg','08.jpg','09.jpg','10.jpg']),

    'section13_bg_image_array' => env('section13_bg_image_array', ['01.jpg','02.jpg','03.jpg','04.jpg','05.jpg','06.jpg','07.jpg','08.jpg','09.jpg','10.jpg']),

    //'a_to_z_letters_array' => env('a_to_z_letters_array', ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z', 'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z']),

    'a_to_z_letters_array' => env('a_to_z_letters_array', ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z']),

    'initial_number_of_items' => env('number_of_items','10'),

    'scroll_number_of_items' => env('number_of_items','6'),

    'sharing_time' => env('sharing_time','10'),

    'sharing_view_count' => env('sharing_view_count','10'),

    // 'admin_to_mail_id' => env('admin_to_mail_id','hiteshraja@ampcontact.com'), // Live
    'admin_to_mail_id' => env('admin_to_mail_id','support@wits.bz'), // Test

    // 'cc_mail_id' => env('cc_mail_id','hiteshraja@ampcontact.com'), // Live
    'cc_mail_id' => env('cc_mail_id','amol.thorat@gophygital.io'), // Test

    // 'bcc_mail_id' => env('bcc_mail_id',['hitesh@gophygital.io']), // Live
    'bcc_mail_id' => env('bcc_mail_id',['amol.thorat@gophygital.io', 'vikas.patil@gophygital.io']), // Test

    // 'request_cv_admin_mail_id' => env('request_cv_admin_mail_id','sonicradar@ampcontact.com') // Live
    'request_cv_admin_mail_id' => env('request_cv_admin_mail_id','support@wits.bz') // Test
];
?>
