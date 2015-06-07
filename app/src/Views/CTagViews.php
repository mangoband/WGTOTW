<?php

namespace Mango\Views;

/**
 *  CTagView
 */
class CTagViews extends \Anax\MVC\CDatabaseModel {
    
    private $maxColumn  = 7;    // default value of items from top to bottom
    private $maxRow     = 7;    // default value of items from left to right
    
    private $tagid      = null;
    private $option     = null;
    private $param      = null;
    
    private $user       = null;
    private $items      = null; // array to put made checkboxes in
    private $names      = null; // all names of made checkboxes
    
    private $classname  = "checkboxAria";
    
    private $app        = null;
    private $cHandler   = null;
    
    private $output     = null; // data to output
    
    private $printed    = false;
    private $tmpTags    = null;
    
    public function __construct( $app, $param = null, $user = null ){
        
        $this->app = $app;
        $this->user = $user;
        
        
        // set values to controll rows and columns    
        $this->maxColumn = getPickedData( $param, 'maxCol', $this->maxColumn ); 
        $this->maxRow    = getPickedData( $param, 'maxRow', $this->maxRow ); 
        
        $this->option    = getPickedData( $param, 'option' );
        $tagid           = getPickedData( $param, 'id' );
        $this->tagid     = $tagid;
        $this->param     = $param;
        
        $this->cHandler  = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                        'errorName' => getError(3)) );
        
  
        
    }
    
    /**
     *  doAction
     */
    public function doAction(){
        
        
        switch( $this->param['option'] ){
            case 'view':
            case 'visa':
                // set pagetitle
                setPageTitle( 'Visa Taggar', $this->app);
                $this->prepareTagList( $this->param['id'], 'visa');    
                if (! $this->param['id'] ){
                    
                    $this->prepareBtns();
                    };
                $this->prepareCommentView( $this->param['id'] );
                break;
            case 'update':
                
                $this->prepareTagsToUpdate( $this->param['id'], $this->param['page'] );
                  
                break;
            case 'add':
                
                $this->prepareTagsToUpdate( $this->param['id'], $this->param['page'] );
                break;
            default:
                if( $this->param['option'] != 'home'){
                    $this->prepareTagList();
                }
        }
    }
    
    /**
     *  makeTag
     *  @param int tagid
     *  @param string tagname
     */
    private function makeTag( $tagid = null, $tagname = null, $comentid = null ){
        
        if( $tagid && $tagname ){
            $url = $this->app->url->create("taggar/visa/{$tagid}");
            $html = "<a href='{$url}' class='tag'> {$tagname}</a>";
            return $html;
        }
    }
    /**********************************************************************
     *
     *      Prepare data
     *
     **********************************************************************/
    
    /**
     *  getTagForComment
     *  @param int commentid
     *  @return htmlcode tags
     */
    public function getTagForComment( $comments = null, $level = 'child' ){
        
       
        $level = ( isset($comments ) && is_array($comments)) ? 'child' : 'parent';
       $style = '';
       $html = '';
       
       // set style to use
       if( isset( $comments->tagid ) ){
            
            $style = 'one';
            
       } else if ( is_array($comments) && isset( $comments[0]->tagid ) ){
        
            $style = 'two';
            
       } else if ( isset( $comments->commentid )){
            
            $style ='tree';
            
       } else if ( isset( $comments ) ) {
        
            $style = 'four';
            
       } else {
        
            return null;
            
       }
       $list= [];
       $tagList = null;
       
       // pick style
       switch( $style ){
            case 'one':
                
                // page
                // home
                $tagid = explode(',', $comments->tagid);
                $tagname = explode(',', $comments->tag);
                
                foreach( $tagname as $key => $tags ){
                   
                    $html .= $this->makeTag($tagid[$key], $tagname[$key]);
                }
                $tagList[$comments->commentid] = $html;
                
                break;
            case 'two':
                
                // page
                // kommentar/visa
                 foreach( $comments as $comment ){ 
                    $tagid = explode(',', $comment->tagid);
                    $tagname = explode(',', $comment->tag);
                    
                    
                   // $tagnames = $this->getTags( $this->app->db, true, null,null, $comment->catid );
                    
                    foreach( $tagname as $key => $tag ){
                        if (! in_array( $tagid[$key], $list )){
                          
                           $list[] = $tagid[$key];
                           $html .= $this->makeTag($tagid[$key], $tagname[$key], $comment->commentid);
                        
                       }
                         
                    }
                    
                    
                    
                    $tagList[$comment->commentid] = $html;
                    
                }
                
                break;
            case 'tree':
                // page
                // taggar/visa
                 $tmp = $this->getTags( $this->app->db, null, null, $comments->commentid );
                    
                foreach( $tmp as $key => $tags ){
                  
                   $html .= $this->makeTag($tags->id, $tags->category);
                   
                }
                
                $tagList[$comments->commentid] = $html;
                break;
            case 'four':
                
                // page
                // kommentera
                
                foreach( $comments as $key => $value ){
               
                    $tmp = $this->getTags( $this->app->db, null, null, $value->commentid );
                 //   dump( $tmp);
                    foreach( $tmp as $key => $tags ){
                        
                        if (! in_array( $tmp[$key]->id, $list )){
                          
                            $list[] = $tmp[$key]->id;
                            $html .= $this->makeTag($tmp[$key]->id, $tags->category);
                        }
                    }
                     
                     $tagList[$value->commentid] = $html;
                }
                break;
       }
       
       return $tagList;
     
    }
    
    /**
     *  prepareTagList for output
     */  
    private function prepareTagList( $tagid = null, $option = null ){
        
        
        //
        // fill $tags with all tags from db
        //
        $tags = (! $option || $option == 'visa'  ) ? $this->getTags( $this->app->db, null, true ) : $this->getTags( $this->app->db, null, null );
        
       
        switch($this->option){
            
            case 'update':
            
                $action = 'update';
                break;
            default:
            $action = 'visa';
        }
        
       
            // popular tags
            
            foreach( $tags as $key => $tag ){
                
                $nr = $key + 1;
                $num = $this->cHandler->countTags( $tag->catid, $this->app->db );
                $url = $this->app->url->create("taggar/{$action}/{$tag->catid}");
                $list[] = "<a href='{$url}'>({$tag->popular}) {$tag->category}</a>";
            }
            
        
            // output data
        
                $this->app->views->add('default/article', ['content' => $this->outputCheckboxes( $list )], 'main-wide');
                
        
        
    }
    
    /**
     *  prepareCommentView
     *  @param int $tagid
     */  
    private function prepareCommentView( $tagid = null ){
        
        
        // we dont need to get the comments unless a tag is picked
        if ( $tagid ){
         
            $res = $this->cHandler->getTagComments($tagid);
            $CViewsComments = new CViewsComments( $this->app );
            
            $CViewsComments->viewListWithComments( null, $res['data'], true, $tagid, true );
            
        }
    }
    
    /**
     *  prepareTagsToUpdate
     */
    private function prepareTagsToUpdate( $tagid, $page = null){
        
        //
        // fill $tags with all tags from db
        //
        $tags = $this->cHandler->getTags( $this->app->db, null,null );
     
        // make html to view nr and tag
        $html = $this->listTags( $tags, 'update', $page );
        
        $this->app->views->add('default/article', ['content' => $html], 'main-wide');
        
        $this->prepareForm( $tagid, $tags, $this->app);
        
    }
    
    /**
     *  prepareForm
     */  
    protected function prepareForm( $tagid = null, $tags = null, $app ){
        
        $form = new  \Anax\CFormContact\CFormComment( $this->app, null, null, $this );
        
        if ( $tags && $tagid ){
            foreach( $tags as $row ){
                if ( $row->id == $tagid ){
                    
                    $ch = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
                    $form = new  \Anax\CFormContact\CFormComment( $this->app, null, null, $this );
                    $form->updateTagForm( $tagid, $row->category, $this->option );        
                }
            }    
        } else {
            $form->updateTagForm();
        }
        
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
            
            $this->app->views->add('default/article', ['content' => $content], 'main-wide');
    }
    /**
     *  prepareBtns
     */  
    private function prepareBtns(){
        
        $online = \Anax\Users\User::getUserID();
        
        
        if ( isset( $online[0] ) && $online[0] == 1 ||$online[0] == 2 ){
            $url = $this->app->url->create("taggar/update/");
        $url2 = $this->app->url->create("taggar/add/");
        $html = "\t<p class='makeTag'><hr /><a href='{$url}' class='tag' >Hantera taggar</a>";
        $html .= "\n\t<a href='{$url2}'  class='tag' >Skapa nya taggar</a></p>";
        
        // output data
        
        $this->app->views->add('default/article', ['content' => $html], 'main-wide');
        
        } else{
            $CViewsComments = new CViewsComments( $this->app );
            
            
            $CViewsComments->viewListWithComments( null, null, null, null, true );
        }
        
    }
    
    /**
     *  outputTags - sends view to app->views
     */
    public function outputTags( $data = null){
        
        
        
        // get prepared data
        $data = ( $this->output ) ? $this->output : null;
        
        if( $data ){
            foreach( $data as $html ){
                // output data
      //          $this->app->views->add('default/article', ['content' => $html], 'main-wide');        
            }
        }
        
        
    }
    /**
     *  listTags
     *
     */
    public function listTags( $tagid = null, $action = null, $page = null, $position = 'main-wide' ){
        
        
        switch($action){
            
            case 'update':
                
                $action = 'update';
                break;
            default:
            $action = 'visa';
        }
        
        //
        // fill $tags with all tags from db
        //
        switch( $page ){
            
            case 'taggar':
                
                $tags = $tagid;
                break;
            case 'kommentar':
                
                $tags = $this->getTags( $this->app->db, null, true, null, null, $position );
                break;
            default:
                $tags = $this->getTags( $this->app->db, null, true  );
            
            
        }
        
        
        // popular tags
        
        foreach( $tags as $key => $tag ){
            
            $selected = ( $tagid == $tag->id ) ? " class='selected'": '';
            $catid = ( isset( $tag->catid ) ) ? $tag->catid : $tag->id;
            $num = $this->cHandler->countTags( $catid, $this->app->db );
            $popular = (! isset( $tag->catid ) ) ? $num[0]->total : $tag->popular;
            
            $nr = $key + 1;
            $url = ( $this->option == 'update' ) ? $this->app->url->create("taggar/{$action}/{$catid}") : $this->app->url->create("taggar/{$action}/{$tag->id}");
            $list[] = "<a href='{$url}'{$selected}>({$popular}) {$tag->category}</a>";
        }
        
        
        
        
        // make html to view nr and tag
        $html = $this->outputCheckboxes( $list );
        $header = ( $position == 'triptych_2') ? '<h2>Populära taggar</h2>' : null;
        
        $this->app->views->add('me/article', ['header' => $header, 'content' => $html], $position);
        $this->printed = true;
    }
    
    /**
     *  callDbmodell
     *  @param string $name
     *  @return $result
     */
    public function fillTagsfromDb( $db = null ){
        //
        // fill $tags with all tags from db
        //
        $tags = $this->getTags( $db );
        return $tags;
    }
    
    /**
     *  countMaxRows
     *  @param int $totalItems
     */
    public function countMaxRows( $totalItems = 0 ){
        
        
        
        // add 1 to $this->maxRow until items fit in columns
        while( ( $totalItems /  $this->maxRow   ) >= $this->maxColumn ){
            
            $this->maxRow ++;
            
        }
      
    }
    /**
     * create ul with correct among rows
     */ 
    private function checkrow($boxes, $rows = null, $inRow = null, $step, $class ){
        
        $html = ( $rows > ($inRow * $step) ) ? "</ul><ul class='{$class}'>": '';
        return $html."\n\t<li>{$boxes}</li>\n"; 
    }
    /************************************************************************
     *
     *          Output
     *
     ************************************************************************/
    
    /**
     *  listPopularTags
     */
    public function listPopularTags(){
        $this->listTags( null, null, null, 'triptych_2');
    }
    /**
     *  btnForUpdate
     *  @return htmlcode
     */
    private function btns(){
        
        $form = new  \Anax\CFormContact\CFormComment( $app, $user, $ch );
        $html = "<a href=''>[Uppdatera taggar]</a>";
        $this->output[] = $html;
    }
    
    /**
     *  output boxes
     */
    public function outputCheckboxes( $items = null ){
        
        // step to handle rows
        $step = 1;
        
        // count if tags fit in aria
        $totalItems = ( ! is_null( $items ) ) ? $this->countMaxRows( count( $items ) ): 0;
        
        
        
        

        if (! is_null($items) ){
            $html   = "<ul class='{$this->classname}'>\n";
            $row    = 0;
            
            // loop every created box to take them to screen
            foreach( $items as $key => $boxes ){
                
                if ( $row == ($this->maxRow * $step )){
                    
                    $step ++;
                    $html .= "</ul>\n<ul class='{$this->classname}'>\n".$this->checkrow($boxes, $row, $totalItems, $step, $this->classname);
                    
                } else {
                    $html .= "\t<li>{$boxes}</li>\n";
                    
                }
                
                $row ++;
                
            }
            $html .= "</ul>";
            
            return $html;
        }
    }
    
    /**
     *  declare checkbox
     */  
    public function checkbox($values = []){
        
       
        $this->itemnr ++;
        
        $label       = ( isset( $values['name'] ) )     ? strtolower($values['name'])     : $this->itemnr;
        
        if (  is_null( $this->names ) || ! in_array( $label, $this->names )  ){
            $this->names[] = $label;
            $id         = ( isset( $values['id'] ) )       ? " id='form-element-{$values['id']}'"   : " id='form-element-".$label."'";
            $name       = " name='{$label}'";
            $value      = ( isset( $values['value'] ) )    ? " value='{$values['value']}'"    : '';
            $checked    = ( isset( $values['checked'] ) && $values['checked'] == 1  )  ? " checked"           : '';
            
            $this->items[] = "<input type='checkbox'{$name}{$id}{$checked} /><label>".ucfirst($label)."</label>";
        } else {
            echo "<pre>".print_r( $this->items, 1 )."</pre>";
            die("try to give your checkbox a new name: {$label} already exists!!!<br />Error trigged at: row ".__LINE__." in ".__FILE__."::".__METHOD__);
        }
        
        
    }
}