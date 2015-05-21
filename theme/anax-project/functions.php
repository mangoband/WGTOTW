<?php
/**
 *  Themas related functions
 */
function getGridClass($gridColor){
   
    if (! isset( $gridColor ) ){
    } else if(isset( $gridColor ) && $gridColor != ''){
        return 'gridColor';
    }
   
   
}

/**
* Get a gravatar based on the user's email.
*/
function get_gravatar( $email = null, $size=null, $imageset = null ) {
    
    $d = ( $imageset ) ? $imageset : 'wavatar';
   
    $grav_url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '.jpg?' . 'd='. urlencode($d). ($size ? "&s=$size" : null);
  
  return $grav_url;
}

/**
     *  dumpa
     */
    function dumpa( $msg = null ){
        if ( $msg ){
            $this->app->views->add('default/article', ['content' => $msg], 'main');
        } else {
            $this->app->views->add('default/article', ['content' => date('r')], 'main');
        }
            
        
    }
    
    


