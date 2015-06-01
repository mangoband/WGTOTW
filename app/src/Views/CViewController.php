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
        
        // set timedata and gravatar in header
        $user = new \Anax\Users\User( $this->app );
        $this->email = $user->getUserMailAdr();
      //  $this->app->views->add('me/timeOfDay', ['icon' => viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email, 'btn' => $user->getLogoutBtn()], 'header');
        
        // set default colors
        $this->app->theme->setVariable('bodyColor', '');
        $this->app->theme->setVariable('wrapperClass', '');
        $this->app->theme->setVariable('gridColor', '');
        
        $this->app->navbar->configure(ANAX_APP_PATH . 'config/' . setMenu() );
        
        
        if ( !isset( $pageUrl[1])){
            $site = 'home';
        } else {
            $site = $pageUrl[1];
        }
        $tmp = explode( '/', $site );
        $param['id']        = ( isset( $tmp[2] ) ) ? $tmp[2] : null;
        $param['option']    = ( isset( $tmp[1] ) ) ? $tmp[1] : null;
        $param['page']      = ( isset( $tmp[0] ) ) ? $tmp[0] : null;
        $param['url']       = ( isset( $currentUrl ) ) ? $currentUrl : null;
        $param['user']      = $user;
        
        $param['verbose']   = false; // If set to true info is written to screen
        
        $this->param        = $param;
        
        
        
        
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
            
           
            
           
            
        }  else if ( startsWith( $site, 'profil' ) ){
            
            $tmp = explode( '/', $site );
            
            
            $this->userID   = ( isset($tmp[4]) ) ? $tmp[3] : null;
            $site           = ( isset($tmp[4]) ) ? $tmp[1]."-".$tmp[2] :null; 
            $this->acronym  = ( isset($tmp[4]) ) ? $tmp[4] : null;
           
            
        }else if ( startsWith( $site, 'home' ) ){
            
            $tmp = explode( '/', $site );
            $param['tag']       = ( isset( $tmp[2] ) ) ? $tmp[2] : null;
            
            
        }
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
                $CTagViews = new \Mango\Views\CTagViews( $app, $param );
                $CTagViews->doAction();
                
                break;
            
            case 'kommentar':
            case 'kommentera':
                $CViewsComments = new CViewsComments( $app, $user, $param, $currentUrl );
                $CViewsComments->doAction( );
                
                break;
            
        }
        
        switch( $site ){
            
            case 'me':
            
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
                $this->login( $user );
                
                break;
          
            case 'me?grid':
                $this->meGridAction( $app );
                break;
            case 'index.php':
            case 'home':
                
                $CViewsComments = new CViewsComments( $app, $user, $param );
               
                $CViewsComments->viewListWithComments( $param );
                 
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
          
            case 'add':
              
                $app->theme->setVariable('gridColor', '');
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
            case 'visa-en-anv':
                $this->showUserAction( $app ); 
                break;
            case 'visa-anv':
                 
                $CViewsComments = new CViewsComments( $app, $user,$param );
                $CViewsComments->userComments( $app, $this->userID );
                break;
            
            case 'skapa-tabell':
                $this->createTableAction( $app );
                break;
            
            // remove this code after install ------> or comment out with /*   */
            case 'reset-kommentarer':
            case 'reset-user':
            case 'firstTime':
                $this->restoreDb( $app );
                break;
            
            // --------<
            
              case 'setup':
                $this->setupAction($app);
                break;
            case 'om':
                $this->omAction( $app );
                break;
            
        }
    }
  
    
    private function login( $user = null ){
        
        if ( $user ){
            $form = new  \Anax\CFormContact\CFormComment( $this->app, $user, null );
            $form->loginForm();
                    
            // Check the status of the form
            $status = $form->Check();
            
            // What to do if the form was submitted?
           if($status === true) {
                
                $url = $this->app->url->create('kommentera');
               header("Location: " . $url);
           }
        
           // What to do when form could not be processed?
           else if($status === false){
     
               header("Location: " . $_SERVER['PHP_SELF']);
           }
        }
    }
    
    
    
    private function restoreDb( $app ){
        
        $url = $app->url->create('reset-user');
        $url2 = $app->url->create('reset-kommentarer');
        $url3 = $app->url->create();
        
        
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
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        
        $form = new  \Anax\CFormContact\CFormContact( $app, $user );
        $form->newUserAction();
        $user->isOnline();
        $online = $user->isUserOnline();
        
        if ( $online === true ){
           
            
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
           
            $content = 'För att kunna lägga till någon måste du vara inloggad...';
            
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
        $app->theme->setTitle($title);
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
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        $content = 'Till höger ser du en lista på de användare som är registrerade.';
   //     $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email], 'header');
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $online = $user->isUserOnline();
        
        
        
        if(  $this->loggedInUser[0] == 1 || $this->loggedInUser[0] == 2 ){
            $user->getUsers();
        }
    }
    /**
     *  showUser
     *  @param $add
     */
    private function showUserAction( $app ){
        
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
        //    $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar');  
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
              //  $this->app->views->add('users/list', ['content' => $user->getLogoutBtn()], 'sidebar'); 
            } else {
                $content = "Du måste logga in först...";
              
            }
            
        }
   //    $app->views->add('me/timeOfDay', ['icon' => $this->viewTimeWithFa(date('G')),'timeOfDay' => date('G : i'), 'email'=> $this->email, 'btn' => $user->getLogoutBtn()], 'header');
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
     *  regionerGridAction
     *  @param $app
     */
    private function regionerGridAction( $app ){
        
        
        $this->app->theme->setVariable('bodyColor', 'bodyColorGray');
        $this->app->theme->setVariable('wrapperClass', 'bg');
        $this->app->theme->setVariable('gridColor', '');
            
        $this->app->theme->setTitle("Me");
        
        
    
        
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
     *  omAction
     *  @param $app
     */
    private function omAction( $app = null ){
        
        $app->theme->setTitle("Om");
        
        // read content of file
        $om = $app->fileContent->get('om.md');
        
        // filter data 
        $om = $app->textFilter->doFilter($om, 'shortcode, markdown');
        
        // output data
        $app->views->add('default/article', ['content' => $om], 'main');
    }
    /**
     *  meAction
     *  @param $app
     */
    private function meAction( $app ) {
        $app->theme->setTitle("Me");
        $app->theme->setVariable('wrapperClass', '');
        
        $app->theme->setVariable('gridColor', '');
        
   
  
        
        $me = $app->fileContent->get('me.md');
        $me = $app->textFilter->doFilter($me, 'shortcode, markdown');
        
        $bas = $app->fileContent->get('bas.md');
        $bas = $app->textFilter->doFilter($bas, 'shortcode, markdown');
        
        $byline = $app->fileContent->get('byline.md');
        $byline = $app->textFilter->doFilter($byline, 'shortcode, markdown');
        
  
        $app->views->add('default/article', ['content' => $me], 'main');
        
   
        $app->views->add('me/simple', ['text_before' => 'Jag bor i', 'icon' => 'fa-building-o'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När jag ska gå in behöver jag ', 'icon' => 'fa-key'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'Blir jag hungrig så fixar jag mat och plockar fram ', 'icon' => 'fa-cutlery', 'text_after' => 'och äter.'], 'sidebar');
        $app->views->add('me/simple', ['text_before' => 'När timmen är sen och det snart är dax att sova använder jag', 'icon' => 'fa-headphones', 'text_after' => 'ofta framför datorn.'], 'sidebar');
                
        
  
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
               // "md5(concat('admin', salt))",
                password_hash('admin', PASSWORD_DEFAULT),
                $now,
                $now
        ]);
     
        $app->db->execute([
                'doe',
                'doe@dbwebb.se',
                'John/Jane Doe',
              //  "md5(concat('doe', salt))",
                password_hash('doe', PASSWORD_DEFAULT),
                $now,
                $now
        ]);
        
       
       $user = new Users\User( $app );
       ;
    
        $app->views->add('default/article', ['content' => $user->getUsers()], 'main');
        $app->views->add('me/simple', ['text_before' => '<h3>Inlagda personer</h3>', 'icon' => null], 'sidebar');
        $app->views->add('me/simple', ['text_before' => $user->getUsers(), 'icon' => null], 'sidebar');
     
    }
    
    
    
    /**
     *  cformAction
     *  @param $app
     */  
    private function cformAction( $app ){
        
        $app->theme->setTitle("CForm");
        
        $app->theme->setVariable('wrapperClass', '');
  
        $app->theme->setVariable('gridColor', '');

        
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