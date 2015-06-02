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
 *  serParentClass
 *
 */
function isParent( $parentid = null, $childID = null ){
    return ( $parentid && $childID && $childID == $parentid ) ? "comment_parent" : '';
    
}

/**
 *  getImg
 *  @param string url
 *  @param string path
 *  @param string class
 */
function getImg( $url = null, $path = null, $class = null, $alt = null ){
    return "<a href='{$url}' class='{$class}'><img class='' src='{$path}' alt='{$alt}' title='{$alt}' /></a>";
    
}

/**
 *  getGravatarLink
 *  @return gravatar
 */  
function getGravatarLink( $email = null){
    
    
    $tmp  =  \Anax\Users\User::getUserID();
    $id = $tmp[0];
    $acronym = $tmp[1];
    // imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
    $gravar = ( isset( $email ) )?  get_gravatar( $email, 40, 'identicon' ): get_gravatar( null, 40, 'mm' );
    
    return $gravar;
}

/**
 *  getLoginBtn
 *  @return htmlcode btn
 */
function getLoginBtn( $url = null, $path = null, $class = null ){
   
    // set timedata and gravatar in header
    $user = new \Anax\Users\User( );
    
    $online = $user->isUserOnline();
    
    // check what img to use
    $logbtn = ( isset( $online ) && $online == true ) ? $path.='/logout.png' : $path.='login.png';
    
    // check what link to use
    $link = ( isset( $online) && $online == true ) ? $url.="/logout": $url.='/loggain';
    // get image
    $btn = getImg( $link, $logbtn, $class);
    
    // return link
    return $btn;
}

/**
 *  getEmailFromHeader
 */  
function getEmailFromHeader( ){
    
    $email = ( isset( $_SESSION['user']['email'] ) ) ? $_SESSION['user']['email'] : null;
    
    return $email;    
}

/**
 *  getProfileLink
 */
function getProfileLink( $app ){
    
    $tmp  =  \Anax\Users\User::getUserID();
    $id = $tmp[0];
    $acronym = $tmp[1];
    $link = ( is_null( $id ) ) ? $app->url->create("loggain") : $app->url->create("profil/show/id/{$id}/{$acronym}");
    
    return $link;
}

/**
 *  getGravatarAlt
 */
function getGravatarAlt(){
    $text = ( isset( $_SESSION['user']['acronym'] ) ) ? "Användarprofil" : "Gravatar";
    return $text;
}
/**
 *  getName
 */
function getName(){
    $name = ( isset( $_SESSION['user']['acronym'] ) ) ? $_SESSION['user']['acronym'] : null;
    return $name;
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
    
    


