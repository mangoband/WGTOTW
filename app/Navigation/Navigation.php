<?php
namespace Mango;
require_once "HTML.php";
class Navigation implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;
    
    private $html   = null;
    private $app    = null;
    private $public = true;
    private $debug  = null;
    
    public function __construct( $app ){
        $this->html = new \Mango\HTMLOutput();
        $this->app  = $app;
    }
    
    public function viewNav(){
        $list = $this->getMenuItems();
        if ( is_string( $list ) ){
            return $list;
        }
        //return $this->html->makeMenu( $list, $this->get_pageName() );
        //echo dump( $this->app->views );
        //$this->app->views->add('navbar', 'breadcrumb');
    }
    
    public function adminNavigation(){
       
        $html = $this->html->doAdmin( $this->getValues() );
        $html .= $this->listMenuItems();
        return $html;
    }
    
    /**
     *  tableExist
     *  @param $tablename
     *  @return true/false
     */  
    private function tableExists( $tablename ){
        $this->app->database->select("count(*)")
            ->from( $tablename );
        $result = $this->app->database->execute([], false);
        
        
        if ( $result === "dontExist" ){
            
            return false;
        }
        
        return true;
    }
    
    private function listMenuItems(){
        $list = $this->getMenuItems();
        if ( is_string( $list ) ){
            return $list;
        }
        return $this->html->existingItems( $list, $this->get_pageName() ); // "<p>Lista med existerande val...</p>\n";
    }
    
    
    private function getMenuItems(){
     
        $tablename = "Menu";
        $tableExist = $this->tableExists( $tablename );
        
        if(  $this->app->database  ){
             if ( $tableExist == false ){
                $this->debug[] = "Navigation->getMenuItems() -- Table {$tablename} do not exist in db we create it... ";
                
                $this->createMyTable( $tablename );
                $this->insertDefaultData( $tablename );
            }
            if ( $tableExist == true ) {
                $this->debug[] = "getBlogContent() - SELECT ";
            }
            
            $this->tableExists( 'menu');
            $this->app->database->select("*")
            ->from( $tablename )
            ->orderby('item_order');
            
            $data = $this->app->database->executeFetchAll([]);
            
       //     echo dump( $this->debug );
            return $data ;
        } else {
            return "<p>You need a databaseConnection to work with the menu...</p>";
        }
    }
    
    private function createMyTable( $tablename ){
        $this->debug[] = "createTable( {$tablename} )";
    
        $this->app->database->createTable(
            $tablename,
            [
                    'id'         => ['integer', 'primary key', 'not null', 'auto_increment'],
                    
                    'url'        => ['varchar(80)', 'UNIQUE'],
                    'item_order' => ['integer(10)'],
                    'title'      => ['varchar(80)'],
                    'parent'     => ['varchar(80)'],
                    'updated'    => ['datetime'],
                    
            ]
        )->execute([]);
        
        
        
        return true;
    }
    
    private function insertDefaultData( $tablename ){
        $this->debug[] = "insertDefaultData( {$tablename} )";
      
        $this->app->database->insert(
               $tablename,
               ['url', 'item_order', 'title']
        );
        
        $now = gmdate('Y-m-d H:i:s');
        $this->app->database->execute([
                'hello.php',                
                1,
                'Home'
                
                
        ]);
      //  echo print_r( $this->app->database->getSQL() );
    }
    
    protected function get_pageName(){
            $p = explode('webroot/', htmlentities( $_SERVER['PHP_SELF']) );
            $p = explode('?', $p[1]);
            return $p[0];	
    }
        
    /**
     *  getValuues from POST and GET
     *  @return $value
     */  
    protected function getValues(){
        /**
        *  getValue
        *  @param $type, $name
        *  @return $value
        */  
        function getValue( $type, $name ){
           
          
           if ( $type && $name ){
               if ( $type == 'POST' ){
                   $value    =  isset( $_POST[ $name ] )   ? strip_tags( trim( $_POST[ $name ]) )      : null;
               } else if ( $type == 'GET' ){
                   $value    =  isset( $_GET[ $name ] )    ? strip_tags( trim( $_GET[ $name ] ) )       : null;
               } else if ( $type == 'SESSION' ){
                   $value    =  isset( $_SESSION[ $name ] )? strip_tags( trim( $_SESSION[ $name ] ) )   : null;
               }
               
               
             //  echo "<br />".$type." : ".$value;
               return $value;
           }
           return null;
        }
        
        $title      =  getValue('POST', 'title');
        $url        =  getValue('POST', 'url');
        $parent     =  getValue('POST', 'parent');
        $add        =  getValue('POST', 'title');
        $remove     =  getValue('POST', 'title');
        $item_order =  getValue('POST', 'item_order');
        
        return ['title' => $title, 'url' => $url, 'parent' => $parent, 'remove' => $remove, 'item_order' => $item_order ];
    }
}