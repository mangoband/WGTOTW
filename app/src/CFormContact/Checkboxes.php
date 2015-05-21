<?php

namespace Anax\CFormContact;
use \Mos\HTMLForm as h;

class Checkboxes extends h\CFormElement{
    
    private $maxColumn   = 4;    // default value of items from top to bottom
    private $maxRow      = 2;    // default value of items from left to right
    private $itemnr     = 0;
    
    private $items      = null; // array to put made checkboxes in
    private $names      = null; // all names of made checkboxes
    
    private $classname  = "checkboxAria";
    
    public function __construct( $param = null ){
        
        
        
        // check if total checkboxes is set
        if (! isset($param['total']) ){
            
            // set values to controll rows and columns    
            $this->maxColumn = ( isset( $param['maxCol'] ) )  ? $param['maxCol'] : $this->maxColumn;
            $this->maxRow    = ( isset( $param['maxRow'] ) )  ? $param['maxRow']    : $this->maxRow;
            
        } else {
            
            
            
        }
        if ( isset( $param['tags'] ) ){
            $this->makeDesign( $param['tags']);
            $this->outputCheckboxes();
        }
        
    }
    
    /**
     *  makeDesign
     */
    private function makeDesign( $tags = null ){
        
        if ( $tags ){
            foreach( $tags as $key => $tag ){
                $this->checkbox(['value'=>$tag->id, 'name'=>$tag->category]);
              //  echo "tag: ".print_r($tag->category,1)."<br />";
            }
        }
//parent::__construct($name, $attributes);
/*
        $this['type']     = 'checkbox';
        $this['checked']  = isset($attributes['checked']) ? $attributes['checked'] : false;
        $this['value']    = isset($attributes['value']) ? $attributes['value'] : $name;
        $this->UseNameAsDefaultLabel(null);*/
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
    
    /**
     *  output boxes
     */
    public function outputCheckboxes(){
        
        // step to handle rows
        $step = 1;
        
        // create ul with correct among rows
        function checkrow($boxes, $rows = null, $inRow = null, $step, $class ){
            
            $html = ( $rows > ($inRow * $step) ) ? "</ul><ul class='{$class}'>": '';
            return $html."\n\t<li>{$boxes}</li>\n"; 
        }
        

        if (! is_null($this->items) ){
            $html   = "<ul class='{$this->classname}'>\n";
            $row    = 0;
            
            // loop every created box to take them to screen
            foreach( $this->items as $key => $boxes ){
                
                if ( $row == ($this->maxRow * $step )){
                    
                    $step ++;
                    $html .= "</ul>\n<ul class='{$this->classname}'>\n".checkrow($boxes, $row, $this->maxRow, $step, $this->classname);
                    
                } else {
                    $html .= "\t<li>{$boxes}</li>\n";
                    
                }
                
                $row ++;
                
            }
            $html .= "</ul>";
            
            return $html;
        }
    }
}

/*$cb = new Checkboxes(['inRow'=>'3']);
$cb->checkbox( ['value' => 'ord', 'name' => 'vitsar', 'checked'=>false]);
$cb->checkbox( ['value' => 'ord', 'name' => 'ballonger', 'checked'=>true]);
$cb->checkbox( ['value' => 'ord', 'name' => 'music', 'checked'=>true]);
$cb->checkbox( ['value' => 'ord', 'name' => 'php', 'checked'=>true]);
$cb->checkbox( ['value' => 'ord', 'name' => 'word', 'checked'=>true]);
$cb->checkbox( ['value' => 'ord', 'name' => 'bass', 'checked'=>false]);

$cb->outputCheckboxes();*/
