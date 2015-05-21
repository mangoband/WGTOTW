<?php
namespace Mango\Views;
/**
 *  controller for vievs
 */
use ANAX\Users;
use Mos\HTMLForm;
use \Phpmvc\Comment as c;
class CViewController  extends CViewsFlash  {
    
    private $app;
    
    // email for gravatar
    protected $email = null;
    
    // variables for handling user
    private $loggedInUser   = null;
    private $userID         = null;
    private $commentID      = null;
    private $acronym        = null;
    
    private $dumpa          = false;
    private $msg            = null;
    
    function __construct($app = null){
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        $this->app  = $app;
       // $this->app->session();
     //  echo "<pre>".print_r( $app,1 )."</pre>";
     //    // Will load the session service which also starts the session
        $this->loggedInUser = \Anax\Users\User::getUserID();
        
        if ( isset($_SESSION['user'])){
            $_SESSION['user'] = $_SESSION['user'];
        
            
        }
       
    }
    
    
    /**
     *  dumpa
     */
    protected function dumpa(  ){
        if ( $this->msg ){
            foreach( $this->msg as $m ){
                $this->app->views->add('default/article', ['content' => "<p>{$m}</p>"], 'flash');    
            }
            
        } else {
            $this->app->views->add('default/article', ['content' => "<p>date('r')</p>"], 'flash');
        }
            
        
    }
    protected function setDump( $msg = null ){
        $callers=debug_backtrace();
       
        if ( $msg ){
            $this->msg[] .= $msg." - ".$callers[1]['function'];
            
        } 
    }
    
    /**
     *  viewTimeWithFa
     *  @param $hour
     *  @return $faicon
     */  
    protected function viewTimeWithFa( $hour ){
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
     *  viewContent
     *  check the actuall url and view content
     */  
    public function viewContent(){
        
        $param = null;
        // set default to no color on grid
        $this->app->theme->setVariable('gridColor', '');
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        function startsWith($haystack, $needle)
        {
             $length = strlen($needle);
             return (substr($haystack, 0, $length) === $needle);
        }

        /**
         *  Get CurrentUrlType to test if index.php is going to be in url.
         */  
        $currentUrl = $this->app->request->getCurrentUrl();
        if ( $this->app->url->getUrlType() != 'clean' ){
            $pageUrl = explode( 'webroot/index.php/', $currentUrl);    
        } else {
            $pageUrl = explode( 'webroot/', $currentUrl);
        }
        
        $user = new \Anax\Users\User( $this->app );
        $this->email = $user->getUserMailAdr();
       // $this->app->views->add('me/timeOfDay', ['icon' => $this>viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email, 'btn' => $user->getLogoutBtn()], 'header');
        
        //echo $this>viewTimeWithFa(date('G'));
        /**
         *  login
         */
        
        
        
        //$this->app->theme->setVariable('email', $email);
        
        if ( !isset( $pageUrl[1])){
            $site = 'taggar';
        } else {
            $site = $pageUrl[1];
        }
         // check if page show one
        if ( startsWith( $site, "visa-en") === true ) {
            
            //visa-en/id=3
            $tmpUrl = explode( 'visa-en/', $site);
            $site = "visa-en";
         
            if( startsWith( $tmpUrl[1], 'user' ) === true  ){
                $tmp = explode( '/', $tmpUrl[1] );
                foreach( $tmp as $key => $params ){
                    
                    switch( $params ){
                        case 'user':
                            if ( isset( $tmp[$key + 1] ) && is_numeric( $tmp[$key + 1 ] ) === true ){
                                $this->userID = (int)$tmp[$key + 1 ];
                               
                            }
                            
                            break;
                    }    
                }
                
            }
            
           
            
        } else if ( startsWith ( $site,  'kommentar') ){
            
           // $site = "comment";
            $tmp = explode( '/', $site );
            $site = "kommentar";
            foreach( $tmp as $key => $params ){
                switch( $params ){
                    case 'uppdatera':
                        if ( isset( $tmp[$key + 1] ) && is_numeric( $tmp[$key + 1 ] ) === true ){
                                $this->commentID = (int)$tmp[$key + 1 ];
                               
                        }
                        $site = 'uppdaterakommentar';
                        break;
                    case 'radera':
                        if ( isset( $tmp[$key + 1] ) && is_numeric( $tmp[$key + 1 ] ) === true ){
                                $this->commentID = (int)$tmp[$key + 1 ];
                               
                        }
                        $site = 'raderakommentar';    
                        break;
                    case 'svara':
                        if ( isset( $tmp[$key + 1] ) && is_numeric( $tmp[$key + 1 ] ) === true ){
                                $this->commentID = (int)$tmp[$key + 1 ];
                               
                        }
                        $site = 'svarakommentar';
                        break;
                }
            }
        
            // handle profile
        } else if ( startsWith( $site, 'profil' ) ){
            
            $tmp = explode( '/', $site );
            
            
            $this->userID   = ( isset($tmp[4]) ) ? $tmp[3] : null;
            $site           = ( isset($tmp[4]) ) ? $tmp[1]."-".$tmp[2] :null; 
            $this->acronym  = ( isset($tmp[4]) ) ? $tmp[4] : null;
           
            
        } else if ( startsWith( $site, 'taggar' ) ){
            
            $tmp = explode( '/', $site );
            $param['tag'] = ( isset( $tmp[2] ) ) ? $tmp[2] : null;
          //  print_r( $param);
            //Array ( [0] => taggar [1] => tag [2] => 1 )
            
        }
        
     // die();
        $this->getContent($site, $currentUrl, $param, $user);
        $this->dumpa();
    }
    
    /**
     *  Function getContent
     *  $param $site
     */  
    private function getContent( $site, $currentUrl, $param = null,$user = null  ){
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ . " ".$site);
      
        $this->app->views->add('me/breadcrumb', [], 'breadcrumb');
        $app = $this->app;
        
        $CViewsComments = new CViewsComments( $app );
        
        
    
        switch( $site ){
            
            case 'me':
            case 'index.php':
		$this->meAction( $app );               
                break;
            case 'logout':
                if ( isset($_SESSION['user'])){
                    unset($_SESSION['user']);
                    $url = $this->app->url->create('kommentera');
                    $this->app->response->redirect($url);
                }
                break;
            case 'loggain':
                $form2 = new  \Anax\CFormContact\CFormComment( $this->app, $user, null );
                $form2->loginForm();
                break;
            case 'me?grid':
                $this->meGridAction( $app );
                break;
            case 'redovisning':
               $this->redovisningAction( $app, false );
                break;
            case 'redovisning?grid':
                $this->redovisningAction( $app, true );
                break;
            case 'cflash':
                
                $this->flashtest( $app );
                break;
            case 'scource.php':
                $app->theme->setTitle("Källkod");
                $app->theme->addStylesheet('css/source.css');
                $source = new \Mos\Source\CSource([
                        'secure_dir' => '..', 
                        'base_dir' => '..', 
                        'add_ignore' => ['.htaccess'],
                    ]);
                $content = $source->View();
                $app->views->add( 'me/source', [
                        'content' => $content,
                    ]);
                
                break;
            case 'kommentar':
                $CViewsComments->kommenteraAction( $app, $currentUrl );
                echo "update";
                break;
            
            case 'kommentera':
                $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
                  $CViewsComments->commentActionWithDb( $app, $currentUrl );
                   $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
                break;
            case 'svarakommentar':
                $CViewsComments->respondComment( $app );
                break;
            case 'uppdaterakommentar':
                $CViewsComments->updateComment( $app );
                break;
            case 'raderakommentar':
                $CViewsComments->deleteComment( $app );
                break;
            case 'nykommentar':
                $CViewsComments->addNewComment( $app );
                break;
            case 'add':
                $app->theme->addStylesheet('css/comment.css');
                $app->theme->setVariable('gridColor', '');
                break;
            case 'taggar':
                $CViewsComments->viewListWithComments( $param );
                
            break;
        
            case 'regioner':
                
                $this->regionerAction( $app );
                break;
            
            case 'regioner?grid':
                $this->regionerGridAction( $app );
                
                break;
            case 'test':
                $app->views->add('welcome/hello_world');
                $app->views->add('test/quote', ['today' => date('r')], 'header');
                $app->views->add('test/quote', ['today' => date('r')], 'footer');
                break;
            case 'font-awesome':
                $this->fontAWAction( $app );
                break;
            case 'font-awesome?grid':
                $this->fontAWGridAction( $app );
                break;
            case 'cform':
                    $this->cformAction( $app );
                break;
            
            case 'show-id':
                $this->showUserAction( $app ); 
                break;
            case 'ny':
                $this->addUserAction( $app );
                break;
            case 'uppdateUser':
                break;
            case 'visa-alla':
                $this->showUsersAction( $app );
                break;
            case 'uppdatera':
            case 'visa-en':
                $this->showUserAction( $app ); 
                break;
            case 'skapa-tabell':
                $this->createTableAction( $app );
                break;
            case 'reset-kommentarer':
                
                $CViewsComments->resetCommentTable( $app );
                
                break;
            case 'delete':
                break;
            case 'test1':
                
                    $this->test1Action( $app );
                
                break;
              case 'setup':
                $this->setupAction($app);
                break;
        }
    }
    /**
     *  getError
     *  @param $nr
     */  
    protected function getError( $nr ){ 
        if ( isset( $_SESSION['error'])){
            $error = $_SESSION['error'];
            
           if ( isset( $error[ $nr ] )){
            return $error[ $nr ];
           }
           
        } else {
            return '';
           
        }
    }
    
    
    
    /**
     *  addUser
     *  @param $add
     */
    private function addUserAction( $app ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        $user = new \Anax\Users\User( $app );
        
        $app->session(); // Will load the session service which also starts the session
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Lägg till användare";
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        
        $form = new  \Anax\CFormContact\CFormContact( $app, $user );
        $form->newUserAction();
        $user->isOnline();
        $online = $user->isUserOnline();
        
        if ( $online === true ){
           // $f = $user->getLogoutBtn();
            //$form->logoutForm();
            $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar');
            
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
            $user->getUsers();
            
        }
        else {
            $form2 = new  \Anax\CFormContact\CFormComment( $app, $user );
            $form2->loginForm();
            $status = $form2->Check();
            
            $content = 'För att kunna lägga till någon måste du vara inloggad...';
            // What to do if the form was submitted?
            if($status === true) {
     
                header("Location: " . $_SERVER['PHP_SELF']);
            }
         
            // What to do when form could not be processed?
            else if($status === false){
      
                header("Location: " . $_SERVER['PHP_SELF']);
            }
           // $this->app->views->add('users/list', ['content' => $form2->getHTML()], 'sidebar');  
        }
        
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        
    }
    /**
     *  createTable
     *  @param $add
     */
    private function createTableAction( $app ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        $this->setDump( "rad: ".__LINE__ . __FUNCTION__ );
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Skapa tabell";
        $app->theme->setTitle($title);
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
            $form = new  \Anax\CFormContact\CFormComment( $app, $user );
            $form->loginForm();
            $status = $form->Check();
            // What to do if the form was submitted?
            if($status === true) {
     
                header("Location: " . $_SERVER['PHP_SELF']);
            }
         
            // What to do when form could not be processed?
            else if($status === false){
      
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }
        
        
       
       
       
       $app->views->add('default/article', ['content' => $header.$content], 'main');
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $user->getUsers();
      
     
    }
    /**
     *  updateUser
     *  @param $add
     */
    private function updateUserAction( $app ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Uppdatera användare";
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        
        $user = new \Anax\Users\User( $app );
        $form = new  \Anax\CFormContact\CFormContact( $app, $user );
        $form->createUpdateForm();
        
        $content = 'Text';
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        
    }
    
    /**
     *  showUsers
     *  @param $add
     */
    private function showUsersAction( $app, $profil = false ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        
        $title  = "Visa användare";
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        $content = 'Till höger ser du en lista på de användare som är registrerade.';
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $online = $user->isUserOnline();
        
        if ( $online === true ){
           // $f = $user->getLogoutBtn();
            //$form->logoutForm();
            $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar');  
        }
        else {
            $form = new  \Anax\CFormContact\CFormComment( $app, $user );
            $form->loginForm();
            $status = $form->Check();
            // What to do if the form was submitted?
            if($status === true) {
     
                header("Location: " . $_SERVER['PHP_SELF']);
            }
         
            // What to do when form could not be processed?
            else if($status === false){
      
                header("Location: " . $_SERVER['PHP_SELF']);
            }
           
        }
        
        if(  $this->loggedInUser[0] == 1 || $this->loggedInUser[0] == 2 ){
            $user->getUsers();
        }
    }
    /**
     *  showUser
     *  @param $add
     */
    private function showUserAction( $app ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        $app->theme->setVariable('bodyColor', '');
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        $title  = "Visa användare";
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        $content = '';
       
        
        
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $online = $user->isUserOnline();
        
        if ( $this->userID && $online === true ){
           $app->session(); // Will load the session service which also starts the session
            $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar');  
           $content = $user->getUserToUpdate( $this->userID );
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
                $content = "Du måste välja en användare först...";
                $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar'); 
            } else {
                $content = "Du måste logga in först...";
                $form2 = new  \Anax\CFormContact\CFormComment( $app, $user, null );
                $form2->loginForm();
            }
            
        }
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email, 'btn' => $user->getLogoutBtn()], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        if(   $this->loggedInUser[0] == 1 || $this->loggedInUser[0] == 2  ){
            $user->getUsers();
        }
    }
     /**
     *  redovisningAction
     *  @param $app
     */
    private function redovisningAction( $app, $grid = false ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        if ( $grid == true ){
            $app->theme->setVariable('bodyColor', 'bodyColorGray');
            $app->theme->setVariable('wrapperClass', 'bg');
        } else {
             $app->theme->setVariable('bodyColor', '');
             $app->theme->setVariable('wrapperClass', '');
             
        }
                        
        $app->theme->setTitle("Redovisning");
        
        $app->theme->setVariable('gridColor', '');
        
        $content = $app->fileContent->get('redovisning.md');
        $content = $app->textFilter->doFilter($content, 'shortcode, markdown');
        
        $bas = $app->fileContent->get('bas.md');
        $bas = $app->textFilter->doFilter($bas, 'shortcode, markdown');
        
        $right = $app->fileContent->get('rightLinks.md');
        $right = $app->textFilter->doFilter($right, 'shortcode, markdown');
     
        $byline = $app->fileContent->get('byline.md');
        $byline = $app->textFilter->doFilter($byline, 'shortcode, markdown');
        
        $app->views->add('default/article', ['content' => $content], 'main');
        $app->views->add('me/sidebar', ['img' => $bas, 'byline' => $byline], 'triptych_1');
    }
    
    
    
    
    /**
     *  regionerAction
     *  @param $app, $grid true/false
     */
    private function regionerAction( $app, $grid = false ){
        
        $this->setDump( "rad: ".__LINE__ . __FUNCTION__ );
        
        $this->app->theme->setVariable('wrapperClass', 'bg');
                  
        $this->app->theme->setVariable('gridColor', 'gridColor');
        $this->app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        
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
     *  regionerGridAction
     *  @param $app
     */
    private function regionerGridAction( $app ){
        
        
        $this->app->theme->setVariable('bodyColor', 'bodyColorGray');
        $this->app->theme->setVariable('wrapperClass', 'bg');
        $this->app->theme->setVariable('gridColor', '');
            
        $this->app->theme->setTitle("Me");
        
        //$this->app->theme->addStylesheet('css/comment.css');
    
        
        $me = $this->app->fileContent->get('me.md');
        $me = $this->app->textFilter->doFilter($me, 'shortcode, markdown');
        
        $bas = $this->app->fileContent->get('bas.md');
        $bas = $this->app->textFilter->doFilter($bas, 'shortcode, markdown');
        
        $byline = $this->app->fileContent->get('byline.md');
        $byline = $this->app->textFilter->doFilter($byline, 'shortcode, markdown');
        
      
        $this->app->views->add('default/article', ['content' => $me], 'main');
        
        $this->app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $this->app->views->add('me/simple', ['text_before' => 'Jag bor i', 'icon' => 'fa-building-o'], 'sidebar');
        $this->app->views->add('me/simple', ['text_before' => 'När jag ska gå in behöver jag ', 'icon' => 'fa-key'], 'sidebar');
        $this->app->views->add('me/simple', ['text_before' => 'Blir jag hungrig så fixar jag mat och plockar fram ', 'icon' => 'fa-cutlery', 'text_after' => 'och äter.'], 'sidebar');
        $this->app->views->add('me/simple', ['text_before' => 'När timmen är sen och det snart är dax att sova använder jag', 'icon' => 'fa-headphones', 'text_after' => 'ofta framför datorn.'], 'sidebar');
            
        
       // $this->app->views->add('me/simple', [ 'byline' => $byline], 'sidebar');
        $this->app->views->add('me/sidebar', ['img' => $bas, 'byline' => $byline], 'sidebar');
        
    }
    /**
     *  fontAWAction
     *  @param $app
     */
    private function fontAWAction( $app ){
        $app->theme->setTitle("Font-awesome");
        $app->theme->setVariable('gridColor', '');
        
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i')], 'header');
        $app->views->add('default/page', ['title' => 'Font-awesome','content' => 'När det var dax att få in Font awesome blev det hjärngympa till att börja med.'], 'main');
        $app->views->add('me/simple', ['text_before' => '1x','icon' => 'fa-recycle fa-1x'], 'main');
        $app->views->add('me/simple', ['text_before' => '2x','icon' => 'fa-recycle fa-2x'], 'main');
        $app->views->add('me/simple', ['text_before' => '3x','icon' => 'fa-recycle fa-3x'], 'main');
        $app->views->add('me/simple', ['text_before' => '4x','icon' => 'fa-recycle fa-4x'], 'main');
        $app->views->add('me/simple', ['text_before' => '5x','icon' => 'fa-recycle fa-5x'], 'main');
        $app->views->add('me/simple', ['text_before' => '5x','icon' => 'fa-thumbs-up fa-5x'], 'main');
        
        //$app->views->add('me/simple', ['text_before' => '5x','icon_after' => 'fa-recycle fa-5x'], 'main');
        
        $app->views->add('me/simple', ['text_before' => 'Jag bor i', 'icon' => 'fa-building-o'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När jag ska gå in behöver jag ', 'icon' => 'fa-key'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'Blir jag hungrig så fixar jag mat och plockar fram ', 'icon' => 'fa-cutlery', 'text_after' => 'och äter.'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När timmen är sen och det snart är dax att sova använder jag', 'icon' => 'fa-headphones', 'text_after' => 'ofta framför datorn.'], 'sidebar');
        
    }
    /**
     *  fontAWGridAction
     *  @param $app
     */
    private function fontAWGridAction( $app ){
        $app->theme->setTitle("Font-awesome");
        $app->theme->setVariable('gridColor', '');
        $app->theme->setVariable('wrapperClass', 'bg');
        
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i')], 'header');
        $app->views->add('default/page', ['title' => 'Font-awesome','content' => 'När det var dax att få in Font awesome blev det hjärngympa till att börja med.'], 'main');
        $app->views->add('me/simple', ['text_before' => '1x','icon' => 'fa-recycle fa-1x'], 'main');
        $app->views->add('me/simple', ['text_before' => '2x','icon' => 'fa-recycle fa-2x'], 'main');
        $app->views->add('me/simple', ['text_before' => '3x','icon' => 'fa-recycle fa-3x'], 'main');
        $app->views->add('me/simple', ['text_before' => '4x','icon' => 'fa-recycle fa-4x'], 'main');
        $app->views->add('me/simple', ['text_before' => '5x','icon' => 'fa-recycle fa-5x'], 'main');
        $app->views->add('me/simple', ['text_before' => '5x','icon' => 'fa-thumbs-up fa-5x'], 'main');
        
        //$app->views->add('me/simple', ['text_before' => '5x','icon_after' => 'fa-recycle fa-5x'], 'main');
        
        $app->views->add('me/simple', ['text_before' => 'Jag bor i', 'icon' => 'fa-building-o'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När jag ska gå in behöver jag ', 'icon' => 'fa-key'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'Blir jag hungrig så fixar jag mat och plockar fram ', 'icon' => 'fa-cutlery', 'text_after' => 'och äter.'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När timmen är sen och det snart är dax att sova använder jag', 'icon' => 'fa-headphones', 'text_after' => 'ofta framför datorn.'], 'sidebar');
        
    }
    /**
     *  meGridAction
     *  @param $app
     */  
    private function meGridAction( $app ){
        
        $this->setDump( "rad: ".__LINE__ . __FUNCTION__ );
        
        $app->theme->setVariable('bodyColor', 'bodyColorGray');
        $app->theme->setVariable('wrapperClass', 'bg');
        $app->theme->setVariable('gridColor', '');
        
        $app->theme->setTitle("Me");
    
        //$app->theme->addStylesheet('css/comment.css');
    
        
        $me = $app->fileContent->get('me.md');
        $me = $app->textFilter->doFilter($me, 'shortcode, markdown');
        
        $bas = $app->fileContent->get('bas.md');
        $bas = $app->textFilter->doFilter($bas, 'shortcode, markdown');
        
        $byline = $app->fileContent->get('byline.md');
        $byline = $app->textFilter->doFilter($byline, 'shortcode, markdown');
        
      
        $app->views->add('default/article', ['content' => $me], 'main');
        
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i')], 'header');
        $app->views->add('me/simple', ['text_before' => 'Jag bor i', 'icon' => 'fa-building-o'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När jag ska gå in behöver jag ', 'icon' => 'fa-key'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'Blir jag hungrig så fixar jag mat och plockar fram ', 'icon' => 'fa-cutlery', 'text_after' => 'och äter.'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När timmen är sen och det snart är dax att sova använder jag', 'icon' => 'fa-headphones', 'text_after' => 'ofta framför datorn.'], 'sidebar');
            
        
       // $app->views->add('me/simple', [ 'byline' => $byline], 'sidebar');
        $app->views->add('me/sidebar', ['img' => $bas, 'byline' => $byline], 'sidebar');
    }
    
    /**
     *  meAction
     *  @param $app
     */
    private function meAction( $app ) {
        $app->theme->setTitle("Me");
        $app->theme->setVariable('wrapperClass', '');
        
        $app->theme->setVariable('gridColor', '');
        
   // $app->theme->addStylesheet('css/comment.css');
  
        
        $me = $app->fileContent->get('me.md');
        $me = $app->textFilter->doFilter($me, 'shortcode, markdown');
        
        $bas = $app->fileContent->get('bas.md');
        $bas = $app->textFilter->doFilter($bas, 'shortcode, markdown');
        
        $byline = $app->fileContent->get('byline.md');
        $byline = $app->textFilter->doFilter($byline, 'shortcode, markdown');
        
  
        $app->views->add('default/article', ['content' => $me], 'main');
        
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $app->views->add('me/simple', ['text_before' => 'Jag bor i', 'icon' => 'fa-building-o'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När jag ska gå in behöver jag ', 'icon' => 'fa-key'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'Blir jag hungrig så fixar jag mat och plockar fram ', 'icon' => 'fa-cutlery', 'text_after' => 'och äter.'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När timmen är sen och det snart är dax att sova använder jag', 'icon' => 'fa-headphones', 'text_after' => 'ofta framför datorn.'], 'sidebar');
                
        
  //  $app->views->add('me/simple', [ 'byline' => $byline], 'sidebar');
        $app->views->add('me/sidebar', ['img' => $bas, 'byline' => $byline], 'triptych_1');
       
    }
    
    /**
     *  setupAction
     *  @param $app
     */
    private function setupAction( $app ){
       
        $app->session(); // Will load the session service which also starts the session
        $app->theme->setTitle("Setup");
        $app->theme->setVariable('wrapperClass', '');
        $app->theme->setVariable('gridColor', '');
        $app->theme->setVariable('bodyColor', '');
        
        $app->db->setVerbose(false);
      
	
        $app->db->dropTableIfExists('user')->execute();
     
        $app->db->createTable(
            'user',
            [
                    'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                    'acronym' => ['varchar(20)', 'unique', 'not null'],
                    'email' => ['varchar(80)'],
                    'name' => ['varchar(80)'],
                    'password' => ['varchar(255)'],
                    'created' => ['datetime'],
                    'updated' => ['datetime'],
                    'deleted' => ['datetime'],
                    'active' => ['datetime'],
            ]
        )->execute();
        
        $app->db->insert(
               'user',
               ['acronym', 'email', 'name', 'password', 'created', 'active']
        );
     
        $now = gmdate('Y-m-d H:i:s');
     
        $app->db->execute([
                'admin',
                'admin@dbwebb.se',
                'Administrator',
                password_hash('admin', PASSWORD_DEFAULT),
                $now,
                $now
        ]);
     
        $app->db->execute([
                'doe',
                'doe@dbwebb.se',
                'John/Jane Doe',
                password_hash('doe', PASSWORD_DEFAULT),
                $now,
                $now
        ]);
        
       // $uc = new Users\UsersController();
    //    $res = $uc->listAction();
      //  echo "<br />rad ".__LINE__." setup";
   //     echo "<pre>".dump( $app->dispatcher )."</pre>";
       $user = new Users\User( $app );
       ;
     //  $UsersController = new \Mango\io\CFormContact();
     //  echo $UsersController->indexAction();
      //        echo "<pre>".dump( $app->UsersController)."<pre>";
       
       
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i')], 'header');
        $app->views->add('default/article', ['content' => $user->getUsers()], 'main');
        $app->views->add('me/simple', ['text_before' => '<h3>Inlagda personer</h3>', 'icon' => null], 'sidebar');
        $app->views->add('me/simple', ['text_before' => $user->getUsers(), 'icon' => null], 'sidebar');
     
    }
    
    /**
     *  test1Action
     *  @param $app
     */
    private function test1Action( $app ){
            
            
            $app->session(); // Will load the session service which also starts the session
    
            $form = $app->form->create([], [
                    'name' => [
                            'type'        => 'text',
                            'label'       => 'Name of contact person:',
                            'required'    => true,
                            'validation'  => ['not_empty'],
                    ],
                    'email' => [
                            'type'        => 'text',
                            'required'    => true,
                            'validation'  => ['not_empty', 'email_adress'],
                    ],
                    'phone' => [
                            'type'        => 'text',
                            'required'    => true,
                            'validation'  => ['not_empty', 'numeric'],
                    ],
                    'submit' => [
                            'type'      => 'submit',
                            'callback'  => function ($form) {
                                    $form->AddOutput("<p><i>DoSubmit(): Form was submitted. Do stuff (save to database) and return true (success) 
                                    or false (failed processing form)</i></p>");
                                    $form->AddOutput("<p><b>Name: " . $form->Value('name') . "</b></p>");
                                    $form->AddOutput("<p><b>Email: " . $form->Value('email') . "</b></p>");
                                    $form->AddOutput("<p><b>Phone: " . $form->Value('phone') . "</b></p>");
                                    $form->saveInSession = true;
                                    return true;
                            }
                    ],
                    'submit-fail' => [
                            'type'      => 'submit',
                            'callback'  => function ($form) {
                                    $form->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
                                    return false;
                            }
                    ],
            ]);
    
    
            // Check the status of the form
            $form->check(
                    function ($form) use ($app) {
                    
                            // What to do if the form was submitted?
                            $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
                            $app->redirectTo();
    
                    },
                    function ($form) use ($app) {
            
                            // What to do when form could not be processed?
                            $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
                            $app->redirectTo();
            
                    }
            );
    

    
            $callbackSuccess = function ($form) use ($app) {
                    // What to do if the form was submitted?
                    $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
                    $app->redirectTo();
            };
    
            $callbackFail = function ($form) use ($app) {
                            // What to do when form could not be processed?
                            $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
                            $app->redirectTo();
            };
    
    
            // Check the status of the form
            $form->check($callbackSuccess, $callbackFail);
    
    
            $app->theme->setTitle("Testing CForm with Anax");
            $app->views->add('default/page', [
                    'title' => "Try out a form using CForm",
                    'content' => $form->getHTML()
            ]);
            $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i')], 'header');
    }
    
    /**
     *  cformAction
     *  @param $app
     */  
    private function cformAction( $app ){
        
        $app->theme->setTitle("CForm");
        
        $app->theme->setVariable('wrapperClass', '');
  
        $app->theme->setVariable('gridColor', '');
        $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i')], 'header');
        
        $app->session(); // Will load the session service which also starts the session
//echo "568: CViewController: ".dump( $app->db );
        $form = $app->form->create([], [
                'name' => [
                        'type'        => 'text',
                        'label'       => 'Name of contact person:',
                        'required'    => true,
                        'validation'  => ['not_empty'],
                ],
                'email' => [
                        'type'        => 'text',
                        'required'    => true,
                        'validation'  => ['not_empty', 'email_adress'],
                ],
                'phone' => [
                        'type'        => 'text',
                        'required'    => true,
                        'validation'  => ['not_empty', 'numeric'],
                ],
                'submit' => [
                        'type'      => 'submit',
                        'callback'  => function ($form) {
                            
                    $user = new Users\User( $this->app );
                    
                    $saveStatus = $user->addUser( $form->Value('name'), $form->Value('email'),$form->Value('phone') );
                  //  echo $saveStatus;
                            //$app->db
                                $form->AddOutput("<p><i>{$saveStatus}DoSubmit(): Form was submitted here. Do stuff (save to database) and return true 
                                (success) or false (failed processing form)</i></p>");
                                $form->AddOutput("<p><b>Name: " . $form->Value('name') . "</b></p>");
                                $form->AddOutput("<p><b>Email: " . $form->Value('email') . "</b></p>");
                                $form->AddOutput("<p><b>Phone: " . $form->Value('phone') . "</b></p>");
                                $form->saveInSession = true;
                                return true;
                        }
                ],
                ]);
                
                
        // Check the status of the form
        $status = $form->check();

        if ($status === true) {

                // What to do if the form was submitted?
                $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
                
                $app->redirectTo();

        } else if ($status === false) {
        
                // What to do when form could not be processed?
                $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
                
                $app->redirectTo();

        }
        
        $callbackSuccess = function ($form) use ($app) {
                // What to do if the form was submitted?
                $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
                $app->redirectTo();
        };

        $callbackFail = function ($form) use ($app) {
                        // What to do when form could not be processed?
                        $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
                        $app->redirectTo();
        };

        $app->views->add('default/article', ['content' => $form->getHTML()], 'main');
        
      //  $app->views->add('default/article', ['content' => print_r(  $app->theme  , 1)], 'main');
        
        
        $app->views->addString('bodyColor', 'bodyColorGray');
        
    }
}