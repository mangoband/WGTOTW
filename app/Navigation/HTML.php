<?php

namespace Mango;
class HTMLOutput{
    
    private $debug = null;
    public function __construct(){
        
    }
    
    private function createInput( $name, $type, $value, $alt = '', $required = '' ){
            $html = "\n\t\t\t<td><input type = '{$type}' value='{$value}' name='{$name}' title='{$alt}' {$required} /></td>";
            return $html;
        }
    public function doAdmin( $input ){
        echo dump( $input );
        if ( is_null( $input['item_order'] ) ){
            $input['item_order'] = 1;
        }
        
        $html = "\t<form method='post'>\n\t<div>\n\t\t<table>
                <thead><tr><th>Order</th><th>PageName</th><th>Url</th><th>Parent</th><th>Add</th><th>Delete</th><th></th></tr></thead>
                <tbody><tr>";
        $html   .= $this->createInput( 'item_order', 'text', $input['item_order'] , 'PageName', 'required');
        $html   .= $this->createInput( 'title', 'text', $input['title'] , 'PageName', 'required');
        $html   .= $this->createInput( 'url', 'text', $input['url'], 'Url', 'required');
        $html   .= $this->createInput( 'parent', 'text', $input['parent'], 'Parent');
        $html   .= $this->createInput( 'add', 'submit', 'Add', 'Add');
        $html   .= $this->createInput( 'delete', 'submit', 'Remove', 'Remove');
        $html   .= $this->createInput( 'reset', 'reset', 'Reset', 'reset');
        $html .= "\n\t\t\t</tr>\n\t\t</tbody>
            </table>
        </div>\n\t</form>\n\t";

    return $html;
    }
    
    /**
     *  createInput
     *  @return $html with input
     */
    public function existingItems( $list ){
        $html = "<h2>Existerande menyval</h2>\n\t<form method='post'>\n\t\t<table>";
        
        foreach( $list as $m ){
            $html   .= "\n\t\t<tr>";
            $html   .= $this->createInput( 'item_order', 'text', $m->item_order , 'Order', 'readonly');
            $html   .= $this->createInput( 'title', 'text', $m->title , 'PageName', 'readonly');
            $html   .= $this->createInput( 'url', 'text', $m->url, 'Url', 'readonly');
            $html   .= $this->createInput( 'parent', 'text', $m->item_parent, 'Parent', 'readonly');
            $html   .= $this->createInput( 'up', 'submit', 'Up', 'Up');
            $html   .= $this->createInput( 'down', 'submit', 'Down', 'Down');
            $html   .= $this->createInput( 'update', 'submit', 'Update', 'Update');
            $html   .= "\n\t\t</tr>";
        }
        
        $html .= "\n\t\t</table>\n\t</form>\n";
       // $html .= "<p>PageTitle: name, Url: url, Parent: parent <a href='?nav&update'>[ Update ]</p>";
        return $html;
    }
    /**
     *  makeMenu
     *  @return $html
     */  
    public function makeMenu( $list, $pageName ){
        
        function link( $url, $title, $selected = '' ){
            $item = "<a href='{$url}' title='{$title}' class='{$selected}' >{$title}</a>";
            return $item;
        }
        
        $nav = "<nav";
        
        
        foreach( $list as $key => $item){
            $selected = '';
            if ( $pageName == $item->url ){
                $selected = ' selected ';
            }
                $menuitems[] = "\n\t\t\t<li class='{$selected}'>".link( $item->url, $item->title, $selected )."</li>";
        }
        $selected = (isset($page)) && $page == 'page.php' ? 'selected' : null; 
        $html .= "<li class='submenu {$selected}'><a href='page.php'>Sidor</a>\n\t\t<ul>";
        foreach( $blogMenuItems as $blogPages ){
                $html .= $blogPages;	
        }
        $html .= "\n\t\t</ul>\n\t";
        return $menuitems;
        
    }
}
