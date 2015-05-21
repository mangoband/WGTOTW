<?php

namespace Mango\Views;

/**
 *  CTagView
 */
class CTagViews extends \Anax\MVC\CDatabaseModel {
    
    private $maxColumn  = 7;    // default value of items from top to bottom
    private $maxRow     = 2;    // default value of items from left to right
    
    private $tagid      = null;
    private $option     = null;
    
    private $items      = null; // array to put made checkboxes in
    private $names      = null; // all names of made checkboxes
    
    private $classname  = "checkboxAria";
    
    private $app        = null;
    private $cHandler   = null;
    
    private $output     = null; // data to output
    
    public function __construct( $app, $param = null ){
        
        $this->app = $app;
        
        // set values to controll rows and columns    
        $this->maxColumn = getPickedData( $param, 'maxCol', $this->maxColumn ); 
        $this->maxRow    = getPickedData( $param, 'maxRow', $this->maxRow ); 
        
        $option          = getPickedData( $param, 'option' );
        $tagid           = getPickedData( $param, 'id' );
        
        $this->cHandler  = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                        'errorName' => getError(3)) );
        
        
      
        
        switch( $param['option'] ){
            case 'view':
                if (! $tagid ){
                    $this->prepareTagList(  );
                    };
                $this->prepareCommentView( $tagid );
                break;
            case 'update':
                break;
            case 'add':
                break;
            default:
                $this->prepareTagList();
        }
    }
    
    
    /**********************************************************************
     *
     *      Prepare data
     *
     **********************************************************************/
    
    /**
     *  prepareTagList for later output
     */  
    private function prepareTagList( $tagid = null ){
        
        
        
        
        
        //
        // fill $tags with all tags from db
        //
        $tags = $this->cHandler->getTags( $this->app->db, null, true );
        
        // popular tags
        
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        
        // make html to view nr and tag
        $this->output[] = $this->outputCheckboxes( $list );
        
    }
    
    private function prepareCommentView( $tagid = null ){
        
        // we dont need to get the comments unless a tag is picked
        if ( $tagid ){
         
            $res = $this->cHandler->getTagComments($tagid);
            $CViewsComments = new CViewsComments( $this->app );
            
            
            $CViewsComments->viewListWithComments( null, $res['data'], true );
            
        }
    }
    /**
     *  outputTags - sends view to app->views
     */
    public function outputTags(){
        
        dump( "rad: ".__LINE__." ".__METHOD__);
        
        // get prepared data
        $data = ( $this->output ) ? $this->output : null;
        
        if( $data ){
            foreach( $data as $html ){
                // output data
                $this->app->views->add('default/article', ['content' => $html], 'main-wide');        
            }
        }
        
        
    }
    /**
     *  listTags
     *
     */
    public function listTags( $param = null ){
        
        
       // $list = [];
        
        //
        // fill $tags with all tags from db
        //
        $tags = $this->getTags( $this->app->db, null, true );
        
        // popular tags
        
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        
        /*
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        foreach( $tags as $key => $tag ){
            $nr = $key + 1;
            $url = $this->app->url->create("taggar/view/{$tag->catid}");
            $list[] = "{$nr} : <a href='{$url}'>{$tag->category}</a>";
        }
        */
        
        
        // make html to view nr and tag
        $html = $this->outputCheckboxes( $list );
        
        $this->app->views->add('default/article', ['content' => $html], 'main-wide');
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