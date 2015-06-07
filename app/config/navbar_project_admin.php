<?php
/**
 * Config-file for navigation bar.
 *
 */

return [

    // Use for styling the menu
    'class' => 'navbar',
 
    // Here comes the menu strcture
    'items' => [
        'ask' => [
            'text'  => 'Ställ fråga',
            'url'   => $this->di->get('url')->create('kommentar/fraga'),
            'title' => '',
            
        ],
        'comment'  => [
            'text'  => 'Admin',
            'url'   => $this->di->get('url')->create('loggain'),
            'title' => '',
            'class' => 'adminMenu',
            
            'submenu' => [

                'items' => [

                  
                    
                    // This is a menu item of the submenu
                    'item 2'  => [
                        'text'  => 'Lägg till Kommentar',
                        'url'   => $this->di->get('url')->create('kommentar/ny'),
                        'title' => 'Lägg till ny Kommentar'
                    ],
                     // This is a menu item of the submenu
                    'item 3'  => [
                        'text'  => 'Lägg till användare',
                        'url'   => $this->di->get('url')->create('anv/ny'),
                        'title' => 'Lägg till ny användare'
                    ],

                    
                    
                     // This is a menu item of the submenu
                    'item 5'  => [
                        'text'  => 'Uppdatera användare',
                        'url'   => $this->di->get('url')->create('anv/uppdatera'),
                        'title' => 'Visa alla användare',
                       // 'class' => 'italic'
                    ],

                    
                    // This is a menu item of the submenu
                    'item 7'  => [
                        'text'  => 'Hantera databas',
                        'url'   => $this->di->get('url')->create('setup'),
                        'title' => 'Hantera databas'
                    ],

                    
                    'source' => [
                        'text'  =>'Källkod',
                        'url'   => $this->di->get('url')->create('source'),
                        'title' => '',
                        'mark-if-parent-of' => 'controller'
                    ],

                    
                ],
                
            ], 
          
        ],
        
        'home' => [
            'text'  => 'Hem',
            'url'   => $this->di->get('url')->create('hem'),
            'title' => '',
            'mark-if-parent-of' => 'controller',
        ],
        // This is a menu item
        'anv'  => [
            'text'  => 'Användare',
            'url'   => $this->di->get('url')->create('anv/visa'),
            'title' => ''
        ],
        'tags'  => [
            'text'  => 'Taggar',
            'url'   => $this->di->get('url')->create('taggar/visa'),
            'title' => ''
        ],
        // This is a menu item
        'me'  => [
            'text'  => 'Om',
            'url'   => $this->di->get('url')->create('om'),
            'title' => ''
        ],
        
 
        

        // This is a menu item
        
        
        
        
        
        
        
        
    
        
        
        
        
        
        
        
        
    ],
 


    /**
     * Callback tracing the current selected menu item base on scriptname
     *
     */
    'callback' => function ($url) {
        if ($this->di->get('request')->getCurrentUrl($url) == $this->di->get('url')->create($url)) {
            return true;
        }
    },



    /**
     * Callback to check if current page is a decendant of the menuitem, this check applies for those
     * menuitems that has the setting 'mark-if-parent' set to true.
     *
     */
    'is_parent' => function ($parent) {
        $route = $this->di->get('request')->getRoute();
        return !substr_compare($parent, $route, 0, strlen($parent));
    },



   /**
     * Callback to create the url, if needed, else comment out.
     *
     */
   
    'create_url' => function ($url) {
        return $this->di->get('url')->create($url);
    },
    
];
