<?php
/**
 * Bootstrapping functions, essential and needed for Anax to work together with some common helpers. 
 *
 */




/**
 * Utility for debugging.
 *
 * @param mixed $array values to print out
 *
 * @return void
 */
function dump($array) 
{
    if( is_null($array) ){
        $callers=debug_backtrace();
            
        $content = "<pre>NULL Value from ".$callers[1]['class']."::".$callers[1]['function']."</pre>";    
    } else {
        $content = "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";    
    }
    echo $content;
}

 
/**
*  getError
*  @param $nr
*/ 
function getError( $nr = null ){
    
   if ( isset( $_SESSION['error'])){
       $error = $_SESSION['error'];
       
      if ( isset( $error[ $nr ] ) ){
       return $error[ $nr ];
      }
      
   } else {
       return '';
      
   }
}



/**
*  viewTimeWithFa
*  @param $hour
*  @return $faicon
*/  
function viewTimeWithFa( $hour ){
   if ( $hour >= 8 && $hour <= 19 ){
       $faIcon = 'fa-sun-o';
   } else if ( $hour >=20 && $hour <23){
       $faIcon = 'fa-moon-o';
   } else{
       $faIcon = 'fa-bed';
   } 
   // sun  = fa-sun-o
   // moon = moon-o
   // bed  = bed
   return $faIcon;
}

/**
 *  setMenu
 *  defines navigation for page
 *  if session is set the admin menu is visible
 *  @return string filename
 */  
function setMenu( ){
    
    $id = ( isset( $_SESSION['user']['id'] )  ) ? $_SESSION['user']['id']: null;
    
    if( $id == 1 || $id == 2 ){
        $menu = "navbar_project_admin.php";
    } else if( $id && $id > 2){
        $menu = "navbar_project_user.php";
    } else{
        $menu = "navbar_project.php";
    }
    return $menu;
    
}

/**
*  setPageTitle
*/
function setPageTitle( $title = null, $app ){
   if ( $title ){
       // set pagetitle
    $app->theme->setTitle($title);
    $app->theme->setVariable('title', $title);
    $isTrue = $app->theme->getVariable('isTitle');
    
    // if title is set we dont print it again
    if ( $isTrue == false ){
        $app->theme->setVariable('isTitle', true);
        $pt = $app->theme->getVariable('title');
        $app->views->add('me/title', [ 'title' => $pt], 'title');    
    }
    
   }
}
    
/**
 *  getLastUrl
 *  @return string url
 */
function getLastUrl( $url = null ){
    
    // set last page to session
    if ( isset( $_SESSION['page']['this'] ) ){
        $_SESSION['page']['last'] = $_SESSION['page']['this'];
    }
    
    // set actuall page to session
    if( $url ){
        $_SESSION['page']['this'] = $url;
    }
    
    $link = ( isset( $_SESSION['page']['last'] ) ) ? $_SESSION['page']['last'] :'?';
    return $link;
    
}

/**
 *  getPickedData
 *  @param string name
 *  @return var $data
 */
function getPickedData( $var = null, $item = null, $default = null ){
    
    $default = ($default) ? $default : null;
    
    if( is_array( $var ) && $item ){
        $data = ( isset( $var[$item] ) ) ? $var[$item] : $default;    
    } else{
        $data = ( isset( $var ) ) ? $var : $default;    
    }
    
    
    return $data;
}


/*
function markdown($text) {
    echo '<br />'.ANAX_3pp. 'php-markdown/Michelf/Markdown.php<br />';
    require_once( ANAX_3pp  . 'php-markdown/Michelf/Markdown.inc.php');
  /*require_once( ANAX_3pp  . 'php-markdown/Michelf/Markdown.php');
  require_once(ANAX_3pp . 'php-markdown/Michelf/MarkdownExtra.php');*/
  /*return MarkdownExtra::defaultTransform($text);
}
*/
/**
 * Sort array but maintain index when compared items are equal.
 * http://www.php.net/manual/en/function.usort.php#38827
 *
 * @param array    &$array       input array
 * @param callable $cmp_function custom function to compare values
 *
 * @return void
 *
 */
function mergesort(&$array, $cmp_function) 
{
    // Arrays of size < 2 require no action.
    if (count($array) < 2) return;
    // Split the array in half
    $halfway = count($array) / 2;
    $array1 = array_slice($array, 0, $halfway);
    $array2 = array_slice($array, $halfway);
    // Recurse to sort the two halves
    mergesort($array1, $cmp_function);
    mergesort($array2, $cmp_function);
    // If all of $array1 is <= all of $array2, just append them.
    if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
        $array = array_merge($array1, $array2);
        return;
    }
    // Merge the two sorted arrays into a single sorted array
    $array = array();
    $ptr1 = $ptr2 = 0;
    while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
        if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
            $array[] = $array1[$ptr1++];
        } else {
            $array[] = $array2[$ptr2++];
        }
    }
    // Merge the remainder
    while ($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
    while ($ptr2 < count($array2)) $array[] = $array2[$ptr2++];
    return;
}