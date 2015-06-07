<?php
namespace Anax\CFormContact;
use Mos\HTMLForm as h;

/**
 * Create a class for a contact-form with name, email and phonenumber.
 */
class CFormContact extends \Mos\HTMLForm\CForm  {
 use \Anax\DI\TInjectionaware;
 
 private $app = null;
 private $user = null;
 

  /** 
   * Create all form elements and validation rules in the constructor.
   */
  public function __construct( $app,  $user, $updateID = null) {
    parent::__construct();
    $this->app = $app;
    $this->user = $user;
    
   
    }
    /**
     *  newUser
     */
    public function newUserAction(){
        
        $this->AddElement(new h\CFormElementText('acronym', array('label' => 'Användarnamn:', 'required' => true)))
         ->AddElement(new h\CFormElementText('name', array('label' => 'Namn:', 'required' => true)))
         ->AddElement(new h\CFormElementText('email', array( 'label' => 'E-post:', 'required' => true )))
         ->AddElement(new h\CFormElementPassword('password', array( 'label' => 'Lösenord:', 'required' => true )))
         ->AddElement(new h\CFormElementSubmit('submit', array('callback'=>array($this, 'DoAddNewSubmit'))))
         ->AddElement(new h\CFormElementReset('reset', array('callback'=>array($this, 'DoSubmitFail'))));
        
            
        $this->SetValidation('name', array('not_empty'))
             ->SetValidation('email', array('not_empty', 'email_adress'))
             ->SetValidation('password', array('not_empty', ))
             ->SetValidation('acronym', array('not_empty'));      
    }
    /**
     * Index action using external form.
     *
     *//*
    public function indexAction()
    {
        $this->di->session();
        $form = new \Anax\HTMLForm\CFormExample();
        $form->setDI($this->di);
        $form->check();
        $this->di->theme->setTitle("Testing CForm with Anax");
        
        $this->di->views->add('default/page', [
            'title' => "Try out a form using CFormContact",
            'content' => $form->getHTML()
        ]);
    }
       */
    
 
    /**
     *  create Update form
     */
    public function createUpdateForm( $userData, $userid = null, $acronym = null, $logged = [] ){
       function removeQuotes($string)
    {
        $string=implode("",explode("'",$string));
        return stripslashes(trim($string));
    }
    
     if ( $userData ){
     foreach( $userData as $user ){
       
        $delete = false;
        if (  $user->deleted == 1 ){
            $delete =  true;
        }
        $save = isset($user->id) ? 'save' : 'create';
        $this->AddElement(new h\CFormElementHidden('id', array('value'=>$user->id)))
         ->AddElement(new h\CFormElementText('acronym', array('label'=>'Användarnamn','value'=>removeQuotes($user->acronym), 'readonly' => true)))
         ->AddElement(new h\CFormElementText('name', array('label'=>'Namn','value'=>removeQuotes($user->name), 'required' => true)))
         ->AddElement(new h\CFormElementText('email', array('label'=>'Epost','value'=>removeQuotes($user->email), 'required' => true)));
         if ( $userid != 1 && $logged[0] == 1 || ( $userid != 2 && $logged[0] == 2 ) ){
         $this->AddElement(new h\CFormElementCheckbox('deleted', array('label'=>'Papperkorg.', 'value'=>true, 'checked'=>$delete )));
         }
         
         $this->AddElement(new h\CFormElementText('uppdaterad', array('value'=>$user->updated, 'readonly'=>true)))
         ->AddElement(new h\CFormElementSubmit('Uppdatera', array('label'=>'Uppdatera','callback'=>array($this, 'DoUpdate'))));
         
         if ( $userid != 1 && $logged[0] == 1 || ( $userid != 2 && $logged[0] == 2 ) ){
         $this->AddElement(new h\CFormElementSubmit('Radera', array('label'=>'Radera','callback'=>array($this, 'DoRemove'))));
         }
         
         
         }
            $this->SetValidation('name', array('not_empty'))
             ->SetValidation('email', array('not_empty', 'email_adress'))
             
             ->SetValidation('acronym', array('not_empty'));     
     } else {
        return false;
       
     }
    }
  /**
   * Callback for submitted forms, will always fail
   */
  protected function DoSubmitFail() {
  //  $this->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
    return false;
  }
 
    /**
     *  Callbacks
     */
    protected function DoRemove(){
        $this->AddOutput("Person raderad");
        $this->AddOutput( $this->user->removeUser(  $this->Value('id') ) );    
        return true;
    }
    protected function DoUpdate(){
     
     
     
      
        $this->AddOutput("Data uppdaterad");
        $delete = 0;
        if ( isset( $_POST['deleted'] ) && ( $_POST['deleted'] == true)) {
            
            $delete = $_POST['deleted'];
        }
            $this->AddOutput(
                $this->user->updateUser( array( 'id'=>$this->Value('id'), 'acronym'=> $this->Value('acronym'),
                                                'name'=> $this->Value('name'), 'email'=> $this->Value('email'),
                                                'deleted' => $delete ) ) );
            
          
        
     //   return true;
     
    }
 
  /**
   * Callback for submitted forms
   */
  protected function DoAddNewSubmit() {
 
    if ( $this->user->getAcronymUser( $this->Value('acronym') ) === true ){
        
            $this->AddOutput("<p><i>Välj ett annat användarnamn...</i></p>");
             $this->saveInSession = false;
        return false;
    } else{
 
        $this->validate( $this->Value('password') );
        $this->user->addUser( $this->Value('acronym'), $this->Value('name'), $this->Value('email'),$this->Value('password')  );
        $this->AddOutput("<p><b>Name: " . $this->Value('name') . "</b></p>");
        $this->AddOutput("<p><b>Email: " . $this->Value('email') . "</b></p>");
        $this->AddOutput("<p><b>Användarnamn: " . $this->Value('acronym') . "</b></p>");
        $this->saveInSession = true;
        return true;
    }
  }
   /**
     * Callback What to do if the form was submitted?
     *
     */
    public function callbackSuccess()
    {
      
     $this->AddOUtput("<p><i>Form was submitted and the callback method returned true CFormContact.</i></p>");
     
        $this->redirectTo();
    }
    
    /**
     *  validate_password
     */
    private function validate( $value = null, $type = 'password'){
        if ( $value && strlen( $value ) > 3){
            return true;
        }else{
            return false;
        }
    }
 
}