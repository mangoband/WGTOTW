<?php
namespace Mango\Views;
/**
 *  controller for vievs
 */
use ANAX\Users;
use Mos\HTMLForm;
use \Phpmvc\Comment as c;
class CViewController extends CViewsFlash {
    
    private $app;
    
    // email for gravatar
    private $email = null;
    
    // variables for handling user
    private $loggedInUser   = null;
    private $userID         = null;
    private $commentID      = null;
    private $acronym        = null;
    
    private $param          = null;
    
    function __construct($app = null){
        
        $this->app  = $app;
        $app->session(); // Will load the session service which also starts the session
        $this->loggedInUser = \Anax\Users\User::getUserID();
        
       
        if ( isset($_SESSION['user'])){
            $_SESSION['user'] = $_SESSION['user'];
        
            
        }
       
    }
    
    /**
     *  getQueryStringID
     *  @param string $urlPart
     *  @return string ID
     */
     private function getQueryStringID( $urlPart = null, $key = null ){
        
        if ( $urlPart && $key ){
            if ( isset( $urlPart[$key + 1] ) && is_numeric( $urlPart[$key + 1 ] ) === true ){
               return (int)$urlPart[$key + 1 ];

            }    
        }
        

     }
    /**
     *  viewContent
     *  manage where things are viewed and by what
     */  
    public function viewContent(){
        function startsWith($haystack, $needle)
        {
             $length = strlen($needle);
             return (substr($haystack, 0, $length) === $needle);
        }
        
        
        
        $param = null;

     
        
        /**
         *  Get CurrentUrlType to test if index.php is going to be in url.
         */  
        $currentUrl = $this->app->request->getCurrentUrl();
        if ( $this->app->url->getUrlType() != 'clean' ){
            $pageUrl = explode( 'webroot/index.php/', $currentUrl);    
        } else {
            $pageUrl = explode( 'webroot/', $currentUrl);
        }
        
      
      
        
        // set default colors
        $this->app->theme->setVariable('bodyColor', '');
        $this->app->theme->setVariable('wrapperClass', '');
        $this->app->theme->setVariable('gridColor', '');
        
        // set pagetitle
        $this->app->theme->setVariable('isTitle', false);
       
        
        $this->app->navbar->configure(ANAX_APP_PATH . 'config/' . setMenu() );
        
        
        if ( !isset( $pageUrl[1])){
            $site = 'hem';
        } else {
            $site = $pageUrl[1];
        }
        $tmp = explode( '/', $site );
        $param['id']        = ( isset( $tmp[2] ) ) ? $tmp[2] : null;
        $param['option']    = ( isset( $tmp[1] ) ) ? $tmp[1] : null;
        $param['page']      = ( isset( $tmp[0] ) ) ? $tmp[0] : null;
        $param['url']       = ( isset( $currentUrl ) ) ? $currentUrl : null;
        
          // set timedata and gravatar in header
          if ( $param['page'] != 'firstTime' && $param['page'] != 'reset-user' && $param['page'] != 'reset-kommentarer'){
            $user = new \Anax\Users\User( $this->app );
            $this->email = $user->getUserMailAdr();    
          } else {
            $user = null;
          }
        
        $param['user']      = $user;
        
        $param['verbose']   = false; // If set to true info is written to screen
        
        $this->param        = $param;
        $site           = ( isset($tmp[4]) ) ? $tmp[1]."-".$tmp[2] :null;
       /*
        
        
        
         // check if page show one
        if ( startsWith( $site, "anv") === true ) {
            
            // $site = "Användare";
            $tmp = explode( '/', $site );
            $site = "anv";
          
            foreach( $tmp as $key => $params ){
                switch( $params ){
                    case 'visa-en':
                        $this->userID = $this->getQueryStringID( $tmp, $key );
                        
                        $site = 'visa-en-anv';
                        break;
                    case 'ny':
                        $site = 'ny-anv';
                        break;
                    case 'visa':
                        $site = 'visa-anv';
                        $this->userID = $this->getQueryStringID( $tmp, $key );
                        break;
                }
            }
            
           
            
           
            
        }*/
        /* if ( startsWith( $site, 'profil' ) ){
            
            $tmp = explode( '/', $site );
            
            
            $this->userID   = ( isset($tmp[4]) ) ? $tmp[3] : null;
            $site           = ( isset($tmp[4]) ) ? $tmp[1]."-".$tmp[2] :null; 
            $this->acronym  = ( isset($tmp[4]) ) ? $tmp[4] : null;
           
            
        }else if ( startsWith( $site, 'home' ) ){
            
            $tmp = explode( '/', $site );
            $param['tag']       = ( isset( $tmp[2] ) ) ? $tmp[2] : null;
            
            
        }*/
     // die();
        $this->getContent($site, $currentUrl, $user, $param);
    }
    
    /**
     *  Function getContent
     *  $param $site
     */  
    private function getContent( $site, $currentUrl, $user, $param = null ){
      
        $this->app->views->add('me/breadcrumb', [], 'breadcrumb');
        $app = $this->app;
        
        
            switch($param['page']){
                case 'taggar':
                    $CTagViews = new \Mango\Views\CTagViews( $app, $param, $user );
                    $CTagViews->doAction();
                    
                    break;
                
                case 'kommentar':
                //case 'kommentera':
                    $CViewsComments = new CViewsComments( $app, $user, $param, $currentUrl );
                    $CViewsComments->doAction( );
                    
                    break;
                
                case 'anv':
                    
                    // go to user pages
                    switch( $param['option']){
                        case 'uppdatera':
                        case 'visa-en':
                            $this->showUserAction( $app, $param['id'] ); 
                            break;
                        case 'visa':
                         
                            $CViewsComments = new CViewsComments( $app, $user,$param );
                            $CViewsComments->userComments( $app, $param['id'] );
                            break;
                        
                        case 'ny':
                            $this->addUserAction( $app );
                            break;
                        case 'uppdateUser':
                            break;
                        case 'visa-alla':
                        case 'visa':
                            $this->showUsersAction( $app );
                            break;
                    }
                    
                    break;
                case 'loggain':
                    $this->login( $user );
                    break;
                
                case 'logout':
                if ( isset($_SESSION['user'])){
                    unset($_SESSION['user']);
                    unset($_SESSION['user']['acronym']);
                    $url = $this->app->url->create('hem');
                    $this->app->response->redirect($url);
                }
                break;
                case 'profil':
                case 'show-id':
                    $this->showUserAction( $app, $param['id'] ); 
                    break;
                
                 case 'regioner':
                
                $this->regionerAction( $app );
                break;
                
                
                // remove this code after install ------> or comment out with /*   */
                case 'reset-kommentarer':
                case 'reset-user':
                case 'setup':
                    $this->restoreDb( $app );
                    break;
                
                // --------<
                
                  case 'setup':
                    $this->setupAction($app);
                    break;
                case 'om':
                    $this->omAction( $app );
                    break;
                case 'index.php':
                case 'hem':
                default:    
                 
                   $mangoFlash = $app->MangoFlash->get('notice');
                 
                   $app->views->add('default/article', ['content' => $mangoFlash], 'flash');
                   
                    $CViewsComments = new CViewsComments( $app, $user, $param );
                   
                    $CViewsComments->viewListWithComments( $param );
                    $this->listMostActive( $app );
                     
                break;
            }
        
        
    }
  
    /**
     *  login
     *  @param object user
     */  
    private function login( $user = null ){
        
        // set pagetitle
        setPageTitle( 'Login', $this->app);
        if ( $user ){
            $form = new  \Anax\CFormContact\CFormComment( $this->app, $user, null );
            $form->loginForm();
                    
            // Check the status of the form
            $status = $form->Check();
            
            // What to do if the form was submitted?
           if($status === true) {
                
                $url = $this->app->url->create('hem');
               header("Location: " . $url);
           }
        
           // What to do when form could not be processed?
           else if($status === false){
     
               header("Location: " . $_SERVER['PHP_SELF']);
           }
           $url = $this->app->url->create('anv/ny');
           
           $content = "<a href='{$url}'>Skapa ny användare</a>";
           $this->app->views->add('default/article', ['content' => $content], 'main');
        }
    }
    
    /**
     *  listMostActiveUsers
     *  @param $app
     */
    private function listMostActive($app = null){
        
        $user = new \Anax\Users\User( $app );
        
        // get the object CommentHandler
        $ch = new CommentHandler( $app );
        $ch->listMostActiveUsers();
        
        $CTagViews = new \Mango\Views\CTagViews( $this->app );
        $CTagViews->listPopularTags();
    }
    
    /**
     *  restoreDb
     *  @param object app 
     */
    private function restoreDb( $app ){
        
        $url = $app->url->create('reset-user');
        $url2 = $app->url->create('reset-kommentarer');
        $url3 = $app->url->create();
        
        // set pagetitle
        setPageTitle( 'Databashantering', $app);
        
        $CViewsComments = new \Mango\Views\CViewsComments( $app );
        $user = null;
        $comment = null;
        
        if ( $this->param['page'] == 'reset-user'){
            $CViewsComments->prepareDatabase( $app, 'user');
            $user = '.......... Skapar databas';
            
        } else if ( $this->param['page'] == 'reset-kommentarer'){
            $CViewsComments->prepareDatabase( $app, 'comment');
            $comment = ".......... Skapar databas <a href='{$url3}'>--> Startsida <-- </a><br />";
        }
$html = <<<EOD
 <h1>Skapa databas</h1>
        <p><a href='{$url}'>Skapa/Återställ tabell för användare</a></p>
        <p>{$user}</p>
        <p><a href='{$url2}'>Skapa/Återställ tabell för kommentarer</a></p>
        <p>{$comment}</p>
EOD;
        
   $app->views->add('default/article', ['content' => $html], 'main');
    }
    
    
    
    /**
     *  addUser
     *  @param $add
     */
    private function addUserAction( $app ){
        
        $user = new \Anax\Users\User( $app );
        
        $app->session(); // Will load the session service which also starts the session
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Lägg till användare";
        // set pagetitle
        setPageTitle( 'Användare', $app);
        $header = "<h2>{$title}</h2>";
        
        $form = new  \Anax\CFormContact\CFormContact( $app, $user );
        $form->newUserAction();
       
        $online = $user->isUserOnline();
        
      
           
            
            // Check the status of the form
            $status = $form->Check();
            $cPost = ''; 
            
    
            // What to do if the form was submitted?
            if($status === true) {
                
           //   $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
              header("Location: " . $_SERVER['PHP_SELF']);
            }
             
            // What to do when form could not be processed?
            else if($status === false){
           //  $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
              header("Location: " . $_SERVER['PHP_SELF']);
            }
            
            $content = $form->getHTML();
            
        if ( $online === true ){
            $user->getUsers();
            
        }
     
        
  
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        
    }
    /**
     *  createTable
     *  @param $add
     */
    private function createTableAction( $app ){
        
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Skapa tabell";
        // set pagetitle
        setPageTitle( $title, $app);
        $header = "<h2>{$title}</h2>";
        
        
        
        $app->session(); // Will load the session service which also starts the session
        
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $online = $user->isUserOnline();
        
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        $app->theme->setVariable('bodyColor', '');
        
        $app->db->setVerbose(false);
      
	if ( $online === true ){
            $dbModel = new \Anax\MVC\CDatabaseModel(  );
           // $dbModel->restoreTable( $app );
            $dbModel->createCommentTable( $app );
            $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar');
            $content = 'För att kunna lägga in användare i databasen måste det finnas en tabell. Den är skapad nu...';
        } else {
            $content = "Vill du skapa tabellerna??? Då får du se till att logga in först:)";
            
        }
        
        
       
       
       
       $app->views->add('default/article', ['content' => $header.$content], 'main');
  
        $user->getUsers();
      
     
    }
    /**
     *  updateUser
     *  @param $add
     */
    private function updateUserAction( $app ){
        
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Uppdatera användare";
        // set pagetitle
        setPageTitle( 'Uppdatera', $app);
        $header = "<h2>{$title}</h2>";
        
        $user = new \Anax\Users\User( $app );
        $form = new  \Anax\CFormContact\CFormContact( $app, $user );
        $form->createUpdateForm();
        
        $content = 'Text';
   //     $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        
    }
    
    /**
     *  showUsers
     *  @param $add
     */
    private function showUsersAction( $app, $profil = false ){
        
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Visa användare";
        // set pagetitle
        setPageTitle( 'Uppdatera', $app);
        $header = "<h2>{$title}</h2>";
        $content = 'Till höger ser du en lista på de användare som är registrerade.';
   
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $online = $user->isUserOnline();
        
        
        
        if(  $this->loggedInUser[0] == 1 || $this->loggedInUser[0] == 2 ){
            $user->getUsers(null, 'sidebar');
        }
    }
    /**
     *  showUser
     *  @param $add
     */
    private function showUserAction( $app, $userid = null ){
        
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        $title  = "Profil";
        // set pagetitle
        setPageTitle( 'Uppdatera', $app);
        $header = "<h2>{$title}</h2>";
        $content = '';
       
        
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $online = $user->isUserOnline();
        
        if ( $userid && $online === true ){
           $app->session(); // Will load the session service which also starts the session
        //    $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar');  
           $content = $user->getUserToUpdate( $userid);
           if ( $content ){
                $form = new  \Anax\CFormContact\CFormContact( $app, $user );
                //
                //  sends in userid to createUpdateForm
                //  we are not going to show a removebutton if userid = 1 -> admin
                //  or normal user
                $form->createUpdateForm( $content, $this->userID, $this->acronym, $this->loggedInUser );
           
                // Check the status of the form
                $status = $form->Check();
                // What to do if the form was submitted?
                if($status === true) {
         
                    header("Location: " . $_SERVER['PHP_SELF']);
                }
             
                // What to do when form could not be processed?
                else if($status === false){
          
                    header("Location: " . $_SERVER['PHP_SELF']);
                }
             
                $content = $form->getHTML(); 
           } else {
                $content = "Personen som söks har lämnat databasen...";
           }
           
            
        } else {
            if ( $online === true ){
                $content = "Välj en användare till höger...";
              //  $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar'); 
            } else {
                $content = "Du måste logga in först...";
              
            }
            
        }
   //    $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email, 'btn' => $user->getLogoutBtn()], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        if(   $this->loggedInUser[0] == 1 || $this->loggedInUser[0] == 2  ){
            $user->getUsers(null, 'sidebar', 'Uppdatera');
            
        }
    }
     
    
    
    
    
    
    
    
    
    
    /**
     *  regionerAction
     *  @param $app, $grid true/false
     */
    private function regionerAction( $app, $grid = false ){
        
        $this->app->theme->setVariable('wrapperClass', 'bg');
                  
        $this->app->theme->setVariable('gridColor', 'gridColor');
      
        
        $this->app->views->addString('timeOfDay', 'timeOfDay')
                  ->addString('flash', 'flash')
                  ->addString('featured-1', 'featured-1')
                  ->addString('featured-2', 'featured-2')
                  ->addString('featured-3', 'featured-3')
                  ->addString('main', 'main')
                  ->addString('sidebar', 'sidebar')
                  ->addString('triptych_1', 'triptych_1')
                  ->addString('triptych_2', 'triptych_2')
                  ->addString('triptych_3', 'triptych_3')
                  ->addString('footer-col-1', 'footer-col-1')
                  ->addString('footer-col-2', 'footer-col-2')
                  ->addString('footer-col-3', 'footer-col-3')
                  ->addString('footer-col-4', 'footer-col-4');
        $this->app->views->addString('bodyColor', 'bodyColorGray');
    }
    
    
    
    
    
    
    /**
     *  omAction
     *  @param $app
     */
    private function omAction( $app = null ){
        
        
        // set pagetitle
        setPageTitle( 'Om', $app);
        
        // read content of file
        $om = $app->fileContent->get('om.md');
        
        // filter data 
        $om = $app->textFilter->doFilter($om, 'shortcode, markdown');
        
        // output data
        $app->views->add('default/article', ['content' => $om], 'main');
    }
    
    
    
    
}