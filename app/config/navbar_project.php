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

        // This is a menu item
        'home'  => [
            'text'  => 'Hem',
            'url'   => $this->di->get('url')->create('home'),
            'title' => ''
        ],
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
        'source' => [
            'text'  =>'Källkod',
            'url'   => $this->di->get('url')->create('source'),
            'title' => '',
            'mark-if-parent-of' => 'controller'
        ],
        
        
        
        
        'comment'  => [
            'text'  => 'Kommentera',
            'url'   => $this->di->get('url')->create('kommentera'),
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

                    
                ],
                
            ], 
          
        ],
        // This is a menu item
        'about' => [
            'text'  =>'Om',
            'url'   => $this->di->get('url')->create('om'),
            'title' => 'Om',
            
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
