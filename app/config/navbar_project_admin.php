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
        'home' => [
            'text'  => 'Home',
            'url'   => $this->di->get('url')->create('home'),
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
        'source' => [
            'text'  =>'Källkod',
            'url'   => $this->di->get('url')->create('source'),
            'title' => '',
            'mark-if-parent-of' => 'controller'
        ],
        
        
        
        
        
        
        'comment'  => [
            'text'  => 'Admin',
            'url'   => $this->di->get('url')->create('loggain'),
            'title' => '',
            
            'submenu' => [

                'items' => [

                    // This is a menu item of the submenu
                    'item 1'  => [
                        'text'  => 'Visa Kommentarer',
                        'url'   => $this->di->get('url')->create('kommentera'),
                        'title' => 'Lägg till ny Kommentar'
                    ],
                    
                    // This is a menu item of the submenu
                    'item 2'  => [
                        'text'  => 'Lägg till Kommentar',
                        'url'   => $this->di->get('url')->create('nykommentar'),
                        'title' => 'Lägg till ny Kommentar'
                    ],
                     // This is a menu item of the submenu
                    'item 3'  => [
                        'text'  => 'Lägg till användare',
                        'url'   => $this->di->get('url')->create('ny'),
                        'title' => 'Lägg till ny användare'
                    ],

                    // This is a menu item of the submenu
                    'item 4'  => [
                        'text'  => 'Visa alla användare',
                        'url'   => $this->di->get('url')->create('visa-alla'),
                        'title' => 'Visa alla användare',
                       // 'class' => 'italic'
                    ],
                    
                     // This is a menu item of the submenu
                    'item 5'  => [
                        'text'  => 'Uppdatera användare',
                        'url'   => $this->di->get('url')->create('uppdatera'),
                        'title' => 'Visa alla användare',
                       // 'class' => 'italic'
                    ],

                    // This is a menu item of the submenu
                    'item 6'  => [
                        'text'  => 'Regioner',
                        'url'   => $this->di->get('url')->create('regioner'),
                        'title' => 'Regioner',
                     //   'class' => 'italic'
                    ],
                    
                    // This is a menu item of the submenu
                    'item 7'  => [
                        'text'  => 'Återställ användare',
                        'url'   => $this->di->get('url')->create('reset-user'),
                        'title' => 'Återställer databas för användare'
                    ],

                    // This is a menu item of the submenu
                    'item 8'  => [
                        'text'  => 'Återställ kommentarer',
                        'url'   => $this->di->get('url')->create('reset-kommentarer'),
                        'title' => 'Återställer kommentarer',
                       // 'class' => 'italic'
                    ],

                    
                ],
                
            ], 
          
        ],
    
        
        
        
        
        
        
        
        
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
