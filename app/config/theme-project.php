<?php
/**
 * Config-file for Anax, theme related settings, return it all as array.
 *
 */
$css = (is_file('css/mango-project/style.php')) ? 'css/mango-project/style.php' : 'css/wgtotw.css';
return [

    /**
     * Settings for Which theme to use, theme directory is found by path and name.
     *
     * path: where is the base path to the theme directory, end with a slash.
     * name: name of the theme is mapped to a directory right below the path.
     */
    'settings' => [
        'path' => ANAX_INSTALL_PATH . 'theme/',
        'name' => 'anax-project',
    ],
       

     //$this->app->views->add('me/simple', ['icon' => $this->viewTimeWithFa(date('G')),'content' => date('G : i')], 'featured-1');
    /** 
     * Add default views.
     */
    'views' => [
        [
            'region'   => 'header', 
            'template' => 'me/header', 
            'data'     => [
                'siteTitle' => "WGTOTW ",
                'siteTagline' => "We Gonna Take Over The World",
                
                          
                
            ], 
            'sort'     => -1
        ],
         // header
        [
        'region' => 'navbar', 
        'template' => [
            'callback' => function() {
                return $this->di->navbar->create();
            },
        ], 
        'data' => [ ], 
        'sort' => -1
    // footer
        
    ],
        ['region' => 'footer',
     'template' => 'me/footer',
     'data' => ['footer'=>'Copyright (c) Marcus Johansson (majo15@student.bth.se) | <a href="http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance">Unicorn</a>'],
                'sort' => -1],
],


    /** 
     * Data to extract and send as variables to the main template file.
     */
    'data' => [

        // Language for this page.
        'lang' => 'sv',

        // Append this value to each <title>
        'title_append' => ' | We Gonna Take Over The World',

        // Stylesheets
        'stylesheets' => [$css],
        
        // BodyTheme
        'bodyClass' => 'bodyColorGray',

        // Inline style
        'style' => null,

        // Favicon
        'favicon' => 'favicon.ico',

        // Path to modernizr or null to disable
        'modernizr' => 'js/modernizr.js',

        // Path to jquery or null to disable
        'jquery' => '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js',

        // Array with javscript-files to include
        'javascript_include' => [],

        // Use google analytics for tracking, set key or null to disable
        'google_analytics' => null,
    ],
];

