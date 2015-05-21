<?php

namespace Mango\Views;

/**
 * views
 */
class CViewsFlash{
    
    public function flashtest( $app ){
      
        $app->theme->addStylesheet('css/flash.css');
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');


        $message    = "is time right now...";
        $name       = "Developer";

     //   $flash = new \Mango\Flash\CFlash();

        $app->MangoFlash->set( $message , $type = 'notice' );
        $app->MangoFlash->set( $message , $type = 'warning' );
        $app->MangoFlash->set( $message , $type = 'error' );
        $app->MangoFlash->set( $message , $type = 'success' );
        $app->MangoFlash->set( $name ,    $type = 'hello' );
        
        
        
        $notice     = $app->MangoFlash->get('notice');
        $warning    = $app->MangoFlash->get('warning');
        $error      = $app->MangoFlash->get('error');
        $success    = $app->MangoFlash->get('success');
        $hello      = $app->MangoFlash->get('hello');
        
        $app->views->add('default/article', ['content' => $hello], 'flash');
        
        
        $app->views->add('default/article', ['content' => $notice], 'main');
        
    }
}