<?php

namespace Anax\Users;
 
/**
 * A controller for users and admin related events.
 *
 */
class UsersController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;
    private $di;
    public function __construct( $di ){
        $this->di = $di;
      
    }
    /**
    * List all users.
    *
    * @return void
    */
    public function listAction(){
        $this->users = new \Anax\Users\User();
        $this->users->setDI($this->di);
             
        $all = $this->users->findAll();
     
        $this->theme->setTitle("List all users");
        $this->views->add('users/list-all', [
            'users' => $all,
            'title' => "View all users",
        ]);
    }
    
    /**
    * Get the table name.
    *
    * @return string with the table name.
    */
    public function getSource(){
        return strtolower(implode('', array_slice(explode('\\', get_class($this)), -1)));
    }
    
    /**
     * Find and return all.
     *
     * @return array
     */
    public function findAll(){ 
        $this->db->select()
                 ->from($this->getSource());
     
        $this->db->execute();
        $this->db->setFetchModeClass(__CLASS__);
        return $this->db->fetchAll();
    }
    
    /**
    * Get object properties.
    *
    * @return array with object properties.
    */
    public function getProperties() {
        
       $properties = get_object_vars($this);
       unset($properties['di']);
       unset($properties['db']);
    
       return $properties;
    }
    
    /**
    * List user with id.
    *
    * @param int $id of user to display
    *
    * @return void
    */
    public function idAction($id = null){
        
       $this->users = new \Anax\Users\User();
       $this->users->setDI($this->di);
    
       $user = $this->users->find($id);
    
       $this->theme->setTitle("View user with id");
       $this->views->add('users/view', [
           'user' => $user,
       ]);
    }
    
    /**
    * Find and return specific.
    *
    * @return this
    */
    public function find($id){
        
       $this->db->select()
                ->from($this->getSource())
                ->where("id = ?");
    
       $this->db->execute([$id]);
       return $this->db->fetchInto($this);
   }
   
   /**
    * Initialize the controller.
    *
    * @return void
    */
    public function initialize(){
        
       $this->users = new \Anax\Users\User();
       $this->users->setDI($this->di);
    }
    
    /**
    * Add new user.
    *
    * @param string $acronym of user to add.
    *
    * @return void
    */
    public function addAction($acronym = null){
        
       if (!isset($acronym)) {
           die("Missing acronym");
       }
    
       $now = gmdate('Y-m-d H:i:s');
    
       $this->users->save([
           'acronym' => $acronym,
           'email' => $acronym . '@mail.se',
           'name' => 'Mr/Mrs ' . $acronym,
           'password' => password_hash($acronym, PASSWORD_DEFAULT),
           'created' => $now,
           'active' => $now,
       ]);
    
       $url = $this->url->create('users/id/' . $this->users->id);
       $this->response->redirect($url);
    }
    
    /**
    * Save current object/row.
    *
    * @param array $values key/values to save or empty to use object properties.
    *
    * @return boolean true or false if saving went okey.
    */
    public function save($values = []){
        
       $this->setProperties($values);
       $values = $this->getProperties();
    
       if (isset($values['id'])) {
           return $this->update($values);
       } else {
           return $this->create($values);
       }
    }
    
    /**
    * Set object properties.
    *
    * @param array $properties with properties to set.
    *
    * @return void
    */
    public function setProperties($properties){
        
       // Update object with incoming values, if any
       if (!empty($properties)) {
           foreach ($properties as $key => $val) {
               $this->$key = $val;
           }
       }
    }
    
    /**
    * Update row.
    *
    * @param array $values key/values to save.
    *
    * @return boolean true or false if saving went okey.
    */
    public function update($values){
        
       $keys   = array_keys($values);
       $values = array_values($values);
    
       // Its update, remove id and use as where-clause
       unset($keys['id']);
       $values[] = $this->id;
    
       $this->db->update(
           $this->getSource(),
           $keys,
           "id = ?"
       );
    
       return $this->db->execute($values);
    }
    
    /**
    * Delete user.
    *
    * @param integer $id of user to delete.
    *
    * @return void
    */
    public function deleteAction($id = null) {
        
       if (!isset($id)) {
           die("Missing id");
       }
    
       $res = $this->users->delete($id);
    
       $url = $this->url->create('users');
       $this->response->redirect($url);
    }
    
    /**
    * Delete row.
    *
    * @param integer $id to delete.
    *
    * @return boolean true or false if deleting went okey.
    */
    public function delete($id) {
        
       $this->db->delete(
           $this->getSource(),
           'id = ?'
       );
    
       return $this->db->execute([$id]);
    }
    
    /**
    * Delete (soft) user.
    *
    * @param integer $id of user to delete.
    *
    * @return void
    */
    public function softDeleteAction($id = null) {
        
       if (!isset($id)) {
           die("Missing id");
       }
    
       $now = gmdate('Y-m-d H:i:s');
    
       $user = $this->users->find($id);
    
       $user->deleted = $now;
       $user->save();
    
       $url = $this->url->create('users/id/' . $id);
       $this->response->redirect($url);
    }
    
    /**
    * List all active and not deleted users.
    *
    * @return void
    */
    public function activeAction(){
        
       $all = $this->users->query()
           ->where('active IS NOT NULL')
           ->andWhere('deleted is NULL')
           ->execute();
    
       $this->theme->setTitle("Users that are active");
       $this->views->add('users/list-all', [
           'users' => $all,
           'title' => "Users that are active",
       ]);
    }
    
    /**
    * Build a select-query.
    *
    * @param string $columns which columns to select.
    *
    * @return $this
    */
    public function query($columns = '*') {
        
       $this->db->select($columns)
                ->from($this->getSource());
    
       return $this;
    }
    
    /**
    * Build the where part.
    *
    * @param string $condition for building the where part of the query.
    *
    * @return $this
    */
    public function where($condition){
        
       $this->db->where($condition);
    
       return $this;
    }
    
    /**
    * Build the where part.
    *
    * @param string $condition for building the where part of the query.
    *
    * @return $this
    */
    public function andWhere($condition){
        
       $this->db->andWhere($condition);
    
       return $this;
    }
    
    /**
     *  Build the Order by part
     *  @param array $column
     *  @param array $sortType
     *
     *  return $this
     */
    public function orderBy( $column = array(), $sortType = array() ){
        
        
        if ( isset( $column[0] ) && isset( $sortType[0] ) ){
            $orderby = '';    
            foreach( $column as $key => $instruction ){
                if ( strlen( $orderby ) > 0 ){
                    $orderby .= ', ';
                }
                $orderby .= $column[$key]." = ". $sortType[$key];     
            }
            $this->db->orderby = $orderby;
            
        }
        return $orderby;
    }
    
    /**
    * Execute the query built.
    *
    * @param string $query custom query.
    *
    * @return $this
    */
    public function execute($params = []){
        
       $this->db->execute($this->db->getSQL(), $params);
       $this->db->setFetchModeClass(__CLASS__);
    
       return $this->db->fetchAll();
   }
}