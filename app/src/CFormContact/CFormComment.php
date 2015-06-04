<?php
namespace Anax\CFormContact;
use \Mos\HTMLForm as h;
use \Phpmvc\Comment as c;

/**
 *  class for comments
 */  
class CFormComment extends h\CForm  {
    use \Anax\DI\TInjectionaware;
 
    private $app        = null;
    private $comments   = null;
    private $user       = null;
    private $CTagViews   = null;
     private $Checkbox  = null;
    
    public function __construct( $app = null, $user = null, $commentController = null, $tagView = null ){
        parent::__construct();
        $this->app      = $app;
        $this->comments = $commentController;
        $this->user     = $user;
        $this->CTagViews = $tagView;
        
        
    }
    
    /**
     *  newComment
     */  
    public function newComment(  $userId, $param = null, $tags = '',  $selectedTags = null, $header = null ){
        
        
        $this->Checkbox = new Checkboxes(['maxRow' => '3']);
        
        
        
        $this->AddElement(new h\CFormElementText('header', array('label'=>'Ämne','value' =>  $param['header'], 'required' => true )))
        ->AddElement(new h\CFormElementTextarea('comment', array('label' => 'Kommentar:', 'required' => true)))
         ->AddElement(new h\CFormElementHidden('userId', array('value' =>  $userId )))
         ->AddElement(new h\CFormElementHidden('commentId', array('value' =>  $param['commentid'] )))
         ->AddElement(new h\CFormElementHidden('parentId', array('value' =>  $param['parentid'] )));
         
         // get tags
         $tags          = ( isset( $param['tags'] ) ) ? $param['tags'] : $tags;
         $selectedTags  = ( isset( $param['selectedTags'] ) ) ? $param['selectedTags'] : null;
        $selected = null;
        
      
        if ( $selectedTags ){
            foreach( $selectedTags as $sel ){
                $selected[] = ucfirst($sel->category);
              
            }
        }
         if ( $tags ){
            
            // loop tags and makeCheckboxes
            foreach( $tags as  $genre ){
               
                $category[] = ucfirst($genre->category);
               
                
            }
            $this->AddElement(new h\CFormElementCheckboxMultiple('items', array('values'=>$category, 'checked'=>$selected)));
        }
        
        $this->AddElement(new h\CFormElementSubmit('submit', array('callback'=>array($this, 'DoNewComment'))))
         ->AddElement(new h\CFormElementSubmit('Reset', array('callback'=>array($this, 'DoSubmitFail'))));
        
        
        $this->SetValidation('comment', array('not_empty'));
        
       
        
    }
    
    
    /**
     *  updateComment
     */
    public function updateComment( $comment, $tags = null, $selectedTags = null, $answer = null, $userid = null ){
        
        
        
        if ( $comment ){
            $this->AddElement(new h\CFormElementText('header', array('label'=>'Ämne','value' =>  $comment->header ,'required')))
            ->AddElement(new h\CFormElementTextarea('comment', array('label' => 'Kommentar:', 'required' => true, 'value'=> $comment->comment)))
            ->AddElement(new h\CFormElementHidden('userId', array('value' =>  $comment->userid )))
            ->AddElement(new h\CFormElementHidden('commentId', array('value' =>  $comment->id )));
        
            // get tags
        
        $selected = null;
        
      
        if ( $selectedTags ){
            foreach( $selectedTags as $sel ){
                $selected[] = ucfirst($sel->category);
              
            }
        }
         if ( $tags ){
            
            // loop tags and makeCheckboxes
            foreach( $tags as  $genre ){
               
                $category[] = ucfirst($genre->category);
               
                
            }
            $this->AddElement(new h\CFormElementCheckboxMultiple('items', array('values'=>$category, 'checked'=>$selected)));
        }
         
    
            $this->AddElement(new h\CFormElementSubmit('Uppdatera', array('callback'=>array($this, 'DoUpdateComment'))))
            ->AddElement(new h\CFormElementSubmit('Radera', array('callback'=>array($this, 'DoDeleteComment'))));
        
            
            $this->SetValidation('comment', array('not_empty'));
        }
        return $this->getHTML();
    }
    
    /**
     *  loginForm
     */
    public  function loginForm(){ 
        $this->AddElement(new h\CFormElementText('acronym', array('label' => 'Acronym', 'required' => true )))
            ->AddElement(new h\CFormElementText('password', array('label' =>  'Lösenord', 'required' => true )))
            ->AddElement(new h\CFormElementSubmit('Login', array('callback'=>array($this, 'DoLogin'))))
            ->AddElement(new h\CFormElementSubmit('Reset', array('callback'=>array($this, 'DoSubmitFail'))));
            
            $this->SetValidation('acronym', array('not_empty'));
            $this->SetValidation('password', array('not_empty'));
            
            $this->app->views->add('users/list', [ 'content' => $this->getHTML()], 'main');  
    }
    
    /**
     *  logoutForm
     */
    public function logoutForm(){
        $this->AddElement(new h\CFormElementSubmit('Logga ut', array('callback'=>array($this, 'DoLogout'))));
    }
    
    /**
     *  updateTags
     */
    public function updateTagForm( $tagid = null, $name = null ){
        
        $this->AddElement(new h\CFormElementHidden('tagid', array('value' => $tagid )))
            ->AddElement(new h\CFormElementText('tagg', array('value' =>  $name, 'required' => true )))
            ->AddElement(new h\CFormElementSubmit('Uppdatera', array('callback'=>array($this, 'DoUpdateTag'))))
            ->AddElement(new h\CFormElementSubmit('Radera', array('callback'=>array($this, 'DoRemoveTag'))))
            ->AddElement(new h\CFormElementSubmit('Ny', array('callback'=>array($this, 'DoNewTag'))));
            
            
        
    }
    
    /*************************************************************************************************
     *
     *  Callback for forms
     *
     */
    
    /**
     *  DoNewTag
     */
    protected function DoNewTag(){
        $url = $this->app->url->create('taggar/add');
        $this->app->response->redirect($url);
    }
    
    /**
     *  DoUpdateTag
     */
    protected function DoUpdateTag(){
        
        $this->CTagViews->updateTag($this->app->db, $this->Value('tagid'), $this->Value('tagg'));
         $url = $this->app->url->create('taggar/view');
        $this->app->response->redirect($url);
    }
    
    /**
     *  DoRemoveTag
     */
    protected function DoRemoveTag(){
        
        $this->CTagViews->removeTag( $this->app->db,  $this->Value('tagid'));
         $url = $this->app->url->create('taggar/view');
        $this->app->response->redirect($url);
    }
    
    /**
     *  DoLogin()
     */
    protected function DoLogin(){
        
        if( strlen( $this->Value('acronym') ) > 4 && strlen( $this->Value('password') ) > 4 ){
            $this->user->isOnline();
            return true;
        }
        return false;
        
        
        
    }
    
    /**
     *  DoUpdateComment()
     */
    protected function DoUpdateComment(){
      
        
        
        
        $this->comments->updateThisComment( $this->Value('commentId'), [ 'comment'=>$this->Value('comment'), 'header'=>$this->Value('header')] );
        
       
        $id = $this->Value('commentId');
        $url = $this->app->url->create('kommentera#'.$id);
        $this->app->response->redirect($url);
        return true;
    }
    
    /**
     *  DoNewComment()
     */
    protected function DoNewComment(){
        
        $commentId  = ( $this->Value('commentId') )  ? $this->Value('commentId') : null;
        $comment    = ( $this->Value('comment') )    ? $this->Value('comment')   : null;
        $parentId   = ( $this->Value('parentId') )   ? $this->Value('parentId')  : null;
        $userId     = ( $this->Value('userId') )     ? $this->Value('userId')    : null;
        $header     = ( $this->Value('header'))      ? $this->Value('header')    : null;
        $category   = ( $this->Value('items' ) )     ? $this->Value('items')     : null;
        
        $tags       = ( isset( $_POST['items'] ) )   ? $_POST['items']           : ['default'];
        
      
        //$parentid && $comment && $header && $id && $tags
     
        if ( $tags && $header && $comment && $userId ){ 
            $this->comments->prepareToAddNewComment( ['cid'=>$commentId, 'pid'=>$parentId, 'cat'=>$category, 'uid'=>$userId, 'comment'=>$comment, 'header'=>$header, 'tags'=>$tags] );
            $url = $this->app->url->create('kommentar/visa/'.$parentId);
            $this->app->response->redirect($url);
            return true;    
        }
       
            return false;
       
        
    }
    
    /**
     *  DoDeleteComment
     */
    protected function DoDeleteComment(){
        $this->AddOUtput(
        $this->comments->deleteThisComment( $this->Value('commentId') )
        );
        $id = $this->Value('commentId');
        $url = $this->app->url->create('kommentera');
        
        $this->app->response->redirect($url);
        return true;
    }
    /**
    * Callback for submitted forms, will always fail
    */
    protected function DoSubmitFail() {
       return false;
    }
    /**
     * Callback What to do if the form was submitted?
     *
     */
    public function callbackSuccess()
    {
      
     $this->AddOUtput("<p><i>Kommentar skickad...</i></p>");
     
        $this->redirectTo();
    }
}