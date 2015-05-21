<?php
namespace Anax\Users;
/**
 *  update Contact
 */
class UpdateContact{
    private $acronym    = null;
    private $name       = null;
    private $email      = null;
    private $delete     = false;
    
    public function __construct( $userData ){
        $this->acronym  = $userData->acronym;
        $this->name     = $userData->name;
        $this->email    = $userData->email;
        $this->delete   = $userData->delete;
        
    }
    /**
     *  printUpdateForm
     */
    public function printUpdateForm(){
        $form = "<form method='post'><table>";
        
        $form .= "<tbody><tr><td><input type='text' value='{$this->acronym}'/> </td></tr>";
        
        return $form."</form>";
        
    }
    
    
}
?>