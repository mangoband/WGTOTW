<?php

namespace Anax\Users;

/**
 * Model for Users.
 *
 */
class User extends \Anax\MVC\CDatabaseModel
{
private $app;
private $online;
   function __construct( $app = null){
      $this->app = $app;
     
   }
   /**
    * addUser
    * @param string acronym - username
    * @param string name - users name
    * @param string email - a valid email
    * @param string password - a strong password
    *
    * @return string $user / 'no user'
    */ 
   public function addUser( $acronym = null,  $name = null, $email = null, $password = null ){
     
      if (  $acronym && $name && $email && $password  ){
        
        $user =    $this->addUserToDb( [$acronym, $email, $name, $password ], $this->app->db);
         return $user;
      } else {
         return 'no user';
      }
   }
   /**
    * getAcronymUser
    * @param string acronym - username
    * @return boolean - true / false 
    */ 
   public function getAcronymUser( $acronym = null ){
        if ( $acronym ){
         // call CDatabaseModel
         
            return $this->getAcronymUserFromDb( $acronym , $this->app->db );
        }
        return false;
   }
   
   /**
    * getUserName
    * @param int @userid
    * @return string username
    */
   public function getUserName( $userid = null, $type = 'acronym' ){
      if( $userid ){
         return $this->getAcronymUserFromDb( $userid , $this->app->db, $type );
      }
   }
   
   /**
    * getUsers
    * list all users 'softDeleted' and normal
    * output result on screen
    */
   public function getUsers( $link = null, $position = 'sidebar' ){
      
     
      //
      // collect users from database
      //
      $all            = $this->checkPostsInDatabase( $this->app->db );
      
      $users          = $this->getNotDeletedUsers( $this->app->db );
      
      $trashedUsers   = $this->getSoftDeletedUsers( $this->app->db );
      
      //
      // get userid if logged in
      //
      $userid = $this->getUserIDIndex();
      $gravatar = new \Anax\Users\Gravatar();
      
      if ( $this->app->url->getUrlType() != 'clean' ){
      } else {
      }
      
      
      // define link
      $link = ( $link ) ? $link : 'anv/visa-en'; 
      
      // 
      // set path to homepage
      //
      $path = $this->app->request->getBaseUrl() ."/index.php";
      $html = '';
      foreach( $users as $values){
          $trash = '';
          if ( $values->deleted == true ){ $trash = " <i class='fa fa-trash'></i> "; }
          $path = $this->app->url->create("{$link}/".$values->id);
           $gravatarImg = "<img src='".$gravatar->get_gravatar($values->email, 15, 'identicon')."' alt='gravatar' title='gravatar' class='userlist_gravatar' />";
        $html .= "\n<li >{$gravatarImg}<a href='{$path}' title='Uppdatera ". $values->name."'>". $values->name ." </a>{$trash}</li>\n";
      }
      
      
      //
      // view user/list
      //
      $this->app->views->add('users/list', ['header'=> 'Inlagda personer ', 'content' => $html], "{$position}");
      
      //
      // list trashedUsers
      //
      if ( isset( $trashedUsers[0] ) && $userid && ($userid == 1 || $userid == 2) ){
          $html = '';
          foreach( $trashedUsers as $trashed){
            if( $trashed->id == $userid ){
               $trash = '';
               if ( $trashed->deleted == true ){ $trash = " <i class='fa fa-trash'></i> "; }
               $path = $this->app->url->create("{$link}/".$trashed->id.'/'.$trashed->acronym);
               $html .= "\n<li ><a href='{$path}' title='Uppdatera ". $trashed->name."'>". $trashed->name ." </a>{$trash}</li>\n";
            }
          }
          
          // view trashed people
          $this->app->views->add('users/list', ['header'=> 'Andra chansen', 'content' => $html], "{$position}");  
       }

      
   }
   
   /**
    *   update user
    *   @param $id
    */
   public function getUserToUpdate( $id = null ){
   
      if ( ! is_null($id) ){
         $user = $this->getUserFromDb( $id, $this->app->db );
         return $user;
      }
   }
    
   /**
    *   updateUser
    *   @param $id
    *   @return true/false
    */
   public function updateUser( $userData = null ){
      
      if ( $userData ){
        $result = $this->updateUserInDb( $userData, $this->app->db );
        return $result;
      }
   }
   /**
     *  removeUser
     *  @param array()
     */
    public function removeUser( $userData = null ){
      
      if ( $userData ){
        return $this->removeUserFromDb( $userData, $this->app->db );
      }
    }
    
    /**
     * isOnline
     * @return boolen true / false
     */ 
    public function isOnline(  ){
      
      // Check if user and password is okey
      $acronym    = isset($_POST['acronym']) && !empty($_POST['acronym']) ? htmlentities($_POST['acronym']) : null;
      $password   = isset($_POST['password']) && !empty($_POST['password']) ? htmlentities($_POST['password']) : null;
   
      // Login user
      if ( $acronym && $password ){
         $res = $this->loginAction( $acronym, $password, $this->app->db);
         
         if ( isset( $res[0] )) {
            // user logged in
            $this->app->session->set('user',[ 'acronym'=>$acronym, 'id'=>$res[0]->id, 'email'=>$res[0]->email ]);
            
            $this->online = true;
            return true;
         
         }
      }
      return false;
        
    }
    
    /**
     *   getUserID
     */   
    public static function getUserID(){ 
      // check if user is logged in
     
      if ( isset( $_SESSION['user']['id'] ) ){ 
          return [ $_SESSION['user']['id'], $_SESSION['user']['acronym'] ];
      }
      return null;
    }
    /**
     *   getUserID
     */   
    public function getUserIDIndex(){ 
      // check if user is logged in
     
      if ( isset( $_SESSION['user']['id'] ) ){ 
          return [ $_SESSION['user']['id'], $_SESSION['user']['acronym'] ];
      }
      return null;
    }
    
    /**
     *   checkLogout
     *   perform log out and redirect
     */   
    public function checkLogout(){
        // Logout the user
        if(isset($_POST['doLogout'])) {
          unset($_SESSION['acronym']);
            $url = $this->app->url->create('kommentera');
            $this->app->response->redirect($url);
        } 
      
    }
    
    /**
     *   isUserOnline
     *   @return boolen true / false
     */   
    public function isUserOnline(){
       
        if ( isset( $_SESSION['user']['acronym'] ) ){
            $this->online = true;
            return true;
        } else {
            $this->online = false;
            return false;
        }
    }
    
    /**
     *   getUserMailAdr
     *   @return string $email / null
     */   
    public  function getUserMailAdr( $app = null){
      
      if ( isset( $_SESSION['user']['acronym'] ) ){
         
         $data = $this->getUserEmailFromDb( $_SESSION['user']['acronym'],  $this->app->db );
         
         $email = ( isset( $data[0]->email ) ) ? $data[0]->email : null;
         
         return $email;
      }
    }
    /**
     *   getLogoutBtn
     *   output logoutform on screen
     */   
    public  function getLogoutBtn(){
      
         if ( $this->isUserOnline() == true ) {
        $url = $this->app->url->create('logout');
        $form = "<form method='post' action='{$url}'>
                <p><input class='loggoutbtn' type='submit' name='doLogout' value='Exit'/></p>
               
                </form>";
                 
      
      } else {
         $url = $this->app->url->create('loggain');
        $form = "<form method='post' action='{$url}'>
                <p><input class='loggoutbtn' type='submit' name='doLogin' value='Login'/></p>
                
                </form>";
      }
      return $form;
         // view form
        $this->app->views->add('users/list', [ 'content' => $form], 'sidebar');  
    }
  
}
