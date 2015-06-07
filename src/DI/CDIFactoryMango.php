<?php
namespace Anax\DI;
/**
 *  My CDI Factory
 */
class CDIFactoryMango extends CDIFactoryDefault{
   
    public function __construct(){
       
        $di = parent::__construct();
        
        $this->set( 'form', '\Mos\HTMLForm\CForm');
       // $this->theme->setBaseTitle(" - Anax test case");
        
       
      try{
      
        $this->setShared('db', function() { 
            $db = new \Mos\Database\CDatabaseBasic(); 
            $db->setOptions(require ANAX_APP_PATH . 'config/database_sqlite.php'); 
            $db->connect( false ); 
            return $db;
        });
    
      
        $this->setShared('MangoFlash', function()   {
            $flash = new \Mango\Flash\CFlash();
            $flash->setDI($this);
            return $flash;
        });
        
        $this->set('UsersController', function($this)  { echo "UsersController";
            $controller = new \Anax\Users\UsersController();
            $controller->setDI($this);
            return $controller;
        });
        $this->set('CommentController', function()  {
            $controller = new \Phpmvc\Comment\CommentController();
            $controller->setDI($this);
            return $controller;
        });
         $this->set('CommentController2', function()  {
            $controller = new \Phpmvc\Comment\CommentController2();
            $controller->setDI($this);
            return $controller;
        });
        
            
        
        $this->set('CommentControll', function() {
            $controller = new Mango\CommentControll();
            $controller->setDI($this);
            return $controller;
        });
        } catch( Exception $e ){
            echo "Fel i Factory ".$e->getMessage();
             die();
        }
        
        
        
    }
    
}