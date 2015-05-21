<?php

namespace Anax\MVC;


/**
 * Model for Users.
 *
 */
class CDatabaseModel implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;
    public $verbose = false;
    
    function __construct(){
        //date_default_timezone_set('Europe/Stockholm');
       
    }
    
    
    
    /**
     *  getSoftDeletedUsers
     *  @param $db
     *  @return $result
     */
    public function getSoftDeletedUsers( $db ){
        $db->select( "*")
            ->from( "user")
            ->where("deleted = 1");
    
        return $db->executeFetchAll(  );
    }
    
    /**
     *  getTags
     *  @param array $db
     *  @return object $result
     */
    public function getTags( $db = null, $category = null, $popular = null, $commentid = null ){
        
           if ( $db ){
                
            $db->setVerbose(false);
                if ( $category && $popular ){
                    if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." popular");
                    }
                } else if ( $popular ){
                    if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." get all categories.");
                    }
                    $db->setVerbose(false);
                    //
                    // count using of tags
                    //
             
                   $db->select("c.id, catid, count(catid) as popular, comment, header, category")
                    ->from("comment as c")
                    ->join("comment2Category as c2c", "c2c.commentid = c.id")
                    ->join("commentCategory as cc", "cc.id = c2c.catid")
                    ->groupby("catid")
                    ->orderby("popular desc");
                    
                    
                    $res = $db->executeFetchAll(  );
               
                    return $res;
                
                } else if ( $category ){
                  if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." get category id from db.");
                    }
                  // get category id from db
                    $db->select("*")
                    ->from("commentCategory")
                    ->where('category = ?');
                    $res = $db->executeFetchAll( [strtolower($category)] );
                    
                    
                    return $res;
                    
                } else if ( $commentid ){
                    if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." get selected category.");
                    }
                    // get selected tags to comment
                    $db->select("*")
                    ->from("comment2Category as c2c")
                    ->join("commentCategory as cc", "cc.id = c2c.catid")
                    ->where("commentid = ? ");
                    
                    $res = $db->executeFetchAll( [$commentid] );
                    
                    return $res;
                    
                    
                } else {
                    
                    // get all categories
                    if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." get all categories.");
                    }
                  
                    $db->select("category")
                    ->from("commentCategory");
                    return $db->executeFetchAll(  );
                    
                }
                
           }
    }
    
    /**
     *  getSoftDeletedUsers
     *  @param $db
     *  @return $result
     */
    public function getNotDeletedUsers( $db ){
        $db->select( "*")
            ->from( "user")
            ->where("deleted = 0");
        return $db->executeFetchAll(  );
    }
    
    /**
     *  checkPostsInDatabase
     *  @return obj - all data from user
     */  
    public function checkPostsInDatabase( $db ){
        // Select from database
        //
        $db->select("*")
            ->from('user')
        ;
        return  $db->executeFetchAll();
    }
    
    /**
     *  addUserToDb
     *  @params array - acronym, email, name, password
     *  @return true/false
     */
    public function addUserToDb( $row, $db ){
        
        $db->insert(
               'user',
               ['acronym', 'email', 'name', 'password', 'created', 'active', 'deleted']
        );
     
        $now = gmdate('Y-m-d H:i:s');
     
    
        $db->execute([
                "{$row[0]}",
                "{$row[1]}",
                "{$row[2]}",
                password_hash($row[3], PASSWORD_DEFAULT),
                $now,
                $now,
                0
        ]);
        
        $id1 = $db->lastInsertId();
        return true;
    }   
    
    
    /**
     *  updateUserInDb
     *  @param array()
     *  @return true/false
     */
    public function updateUserInDb( $row, $db ){
        
      $html = '';
      $now = date('Y-m-d H:i:s');
      $delete = 0;
      if (  isset($row['deleted'])  ){
        $delete = $row['deleted'];
      }
        $db->update(
            'user',
            ['name', 'email', 'acronym', 'deleted', 'updated'],
            "id = ?"
        );
        $db->execute([
            $row['name'], $row['email'], $row['acronym'], $delete, $now, $row['id']
        ]);
        
       
    }
    
    /**
     *  removeUserFromDb
     *  @param array()
     */
    public function removeUserFromDb( $id, $db ){
        
        $db->delete(
            'user',
            "id = ?"
        );
      //  die( $db->getSQL() );
        $res = $db->execute([$id]);
        return $res;
        
    }
    /**
     *  getUserFromDb
     *  @param $id
     *  @return result
     */
    public function getUserFromDb( $id = null, $db ){
        
        if ( $id ){
            // Select from database
            //
            $db->select("*")
                ->from('user')
                ->where("id = ?")
            ;
            $data = $db->executeFetchAll( [$id] );
        
            return  $data;
        }
    }
    /**
     *  loginAction
     *  
     */
    public function loginAction( $acronym, $password, $db ){
        
       
        
        
        if ( $acronym && $password ){
            
            // get userdata based by username
            $db->select("*")
            ->from('user')
            ->where("acronym = ?");
         
             // fetch data
            $data = $db->executeFetchAll( [$acronym]);
            
          
            // verify password
            
            if ( isset($data[0]->password) && password_verify($password, $data[0]->password)) {
                // password is valid
                return  $data;
                
            } else {
                // invalid password
                //$url = $this->app->url->create('loggain');
                
               // header("Location: " . $url);
                return false;
                
            }
            
       
            
            
        } else {
            return false;
        }
    }
    
    /**
     *  getAcronymUserFromDb
     *  @param $acronym
     *  @return true / false
     *
     */
    public function getAcronymUserFromDb( $acronym, $db, $type = 'acronym' ){
        // corrected bug 20150401
            try{
                if ( $type == 'acronym'){
                    
                    $db->select("acronym")
                    ->from('user')
                    ->where("acronym = ?");
                    
                } else if( $type == 'name' ){
                    
                    $db->select("name")
                    ->from('user')
                    ->where("id = ?");
                    
                }
            
            
              $data = $db->executeFetchAll( [ $acronym ]);      
            } catch( EXCEPTION $e){
                 
                die($e.getMessage()." trace: ". $e-getTrace());
            }
        
           
            if ( isset($data[0] )  ){
                if( $type == 'name'){
                    return $data[0];
                }
                return true;
            } else {
                return false;
            }
            
    }
    
    /**
     *  getUserEmailFromDb
     *  @param string acronym
     *  @param obj $db
     *  @return obj $data
     */  
    protected static function getUserEmailFromDb( $acronym = null, $db = null ){
        
        if ( ! is_null( $acronym ) && ! is_null( $db ) ){ 
            try{
                $db->select("email")
                ->from("user")
                ->where("acronym = ?");
                
                $data = $db->executeFetchAll( [$acronym] );
                
                return $data;
            } catch ( EXCEPTION $e ){
                die('Error in when looking for email...');
            }
        }
        return false;
    }
    
    
    
    
    protected function countTable( $db  = null ){
        if ( $db ){
            $db->select("count(*)")
            ->from("sqlite_master")
            ->where("tbl_name <table_name>")
            ->andwhere("type = table");
            
             $data = $db->executeFetchAll();
         
        }
        
        
        //SELECT count(*) > 0 FROM sqlite_master where tbl_name = "<table_name>" and type="table"
    }
    /**
     *  restore table
     *  user
     *  
     */  
    public function restoreTable( $app ){
        
      //  $this->countTable( $app->db );
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
                    'deleted' => ['integer'],
                    'active' => ['datetime'],
            ]
        )->execute();
        
        $app->db->insert(
               'user',
               ['acronym', 'email', 'name', 'password', 'created', 'active', 'deleted']
        );
     
        $now = gmdate('Y-m-d H:i:s');
     
        $app->db->execute([
                'admin',
                'admin@dbwebb.se',
                'Administrator',
                
                password_hash('admin', PASSWORD_DEFAULT),
                $now,
                $now,
                0
        ]);
     
        $app->db->execute([
                'doe',
                'doe@dbwebb.se',
                'John/Jane Doe',
                
                password_hash('doe', PASSWORD_DEFAULT),
                $now,
                $now,
                0
        ]);
        
         $app->db->execute([
                '007',
                'bond@dbwebb.se',
                'James Bond',
                
                password_hash('bond', PASSWORD_DEFAULT),
                $now,
                $now,
                1
        ]);
      
    }
    
    /**********************************************************************
     *
     *              Comments handler below line
     */              
    
    /**
     *  getCommentsGroupedByParentid
     *  @param obj db
     *
     */
    protected function getCommentsGroupedByParentID( $db = null ){
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        if ( $db ){
            
            // gets a list of with parent comments
            $db->select("*,commentid, comment, header, c.created, catid, userid, name, parent as parentid , count(c2c.parent) -1 as answers")
            ->from("comment as c")
            ->join("comment2Category as c2c", "c2c.parent = c.id")
            ->join("user","user.id = c.user_id")
           
            ->groupby("c2c.parent, c.id")
            ->orderby('created desc, parentid desc, commentid desc, header' );
            
            $data = $db->executeFetchAll(  );
           
            
            return $data;
        }
    }
    
    /**
     *  getCommentAnswersFromParentID
     *  @param object $db
     *  @return $object $result
     */
    protected function getCommentAnswersFromParentID( $db = null, $parentid = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        // gets a list with answers to comment
        if( $db && $parentid){
            $db->select("*, c2c.parent as parentid")
            ->from("comment2Category as c2c")
            ->join("comment as c", "c2c.commentid = c.id")
            ->join("user ", "user.id = c.user_id")
            ->where("c2c.parent = ?")
            //->andwhere("c2c.parent != c2c.commentid")
            ->groupby("c.id")
            ->orderby(' parentid asc, commentid asc,created desc' );
            
            $data = $db->executeFetchAll( [$parentid] );
            
            return $data;
          
        }
    }
    
    /**
     *  getCommentAndCategories
     */
    protected function getCommentAndCategoriesAndUserID( $db = null, $commentid = null, $limit = null, $child = null ){
    
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__ ." ". $commentid);
        }
        if ( ! is_null( $db ) ){
        
            $param      = null;
            $rowCount   = null;
            
            $db->setVerbose(false );
            $db->select("c2c.commentid, cc.category, c2c.userid, u.name, p.comment as parent, p.id as parentid, p.created as created,
                        child.comment as child, child.id as childid, child.created as childdate, p.header as header")
            ->from("comment2Category as c2c")
            ->join("comment as p", "p.id = c2c.parent")
            ->join("comment as child", "child.id = c2c.commentid")
            ->join("commentCategory as cc", "cc.id = c2c.catid")
            ->join('user AS u', 'c2c.userid = u.id');
            
            // if not commentid = null
            if ( $commentid && ! $child){
         
                $db->where("commentid = ?");
                $db->andwhere("c2c.parent = p.id");
                $param = [$commentid];
               
            } else if ( $child  && $child == 'child'){
         
                $db->where("childid = ?");
                $param = [$commentid];
                
            } else if( $child && $child == 'tag' ){
                $db->where("c2c.catid = ?");
                $param = [$commentid]; 
            }
            
            $db->orderby('c2c.parent,created desc, childdate desc' );
            
            if ( $limit ){
                $db->limit(1);
            }
            
            
          
            $data = $db->executeFetchAll( $param );
            
            if ( $commentid ){
         
                $rowCount = $this->countAnswersToComment( $db,  $commentid );
            } else{
         
                $rowCount = $this->countAnswersToComment( $db );
            }
           
            return ['data'=>$data, 'rowCount'=>$rowCount];
        }
      
        
    }
    
    /**
     *  fetchCommentToRespond
     */
    protected function fetchCommentToRespond( $db = null, $id = null ){
        
        if ( $db && $id ){
            
            //
            // fetch the comment user responds to from db to display
            //
            $db->select("*")
            ->from("comment")
            ->where("");
        }
    }
    
    private $run = false;
   
   
   /**
    *   countAnswersToComment
    *   @param int parentid
    *   @return int total-answers
    */
   public function countAnswersToComment( $db = null, $commentID = null ){
    
    
        
        if ( $commentID && $db ){
            
            $db->select("count(*) as rows, parent")
            ->from("comment2Category as c2c")
            ->where("parent = ?")
            ->groupby("c2c.commentid")
            ;
            
            $data = $db->executeFetchAll( [$commentID] );
          
            return $data;
        
        } else if ( $db ){
            
            $db->select("count(*) as rows, parent")
            ->from("comment2Category as c2c")
            
            ->groupby("parent, c2c.commentid");
            
            $data = $db->executeFetchAll(  );
        
           return $data;
        
        } 
    
   }
   
   /**
    *   returnLatestParentID
    *   @param $db
    *   @return int parentID
    */
   public function returnLatestParentID( $db ){
        
        if( $db ){
            $db->select("*")
            ->from("comment2Category as c2c")
            ->orderby("parent desc")
            ->limit(1);
            
            $data = $db->executeFetchAll();
         
            return $data[0]->parent;
        }
   }
   /**
    *   viewComment2Category
    */   
   public function viewComment2Category( $db ){
    
    if ( $this->run == false ){
        $this->run = true;
        $db->select("*")
        ->from("comment2Category as c2c")
        ->join("commentCategory as c", "c.id = c2c.catid")
        ->join("comment", "comment.id = c2c.commentid")
        ->join("user", "user.id = comment.user_id");
         $data = $db->executeFetchAll();
         foreach( $data as $row ){
            $r[] = "id: {$row->id}, catid: {$row->catid}, commentid: {$row->commentid}, parent: {$row->parent} category: {$row->category}, header: {$row->header}, userid: {$row->userid}, name: {$row->name}";
         }
         
         return $r;
    }
        
         
   }
    
    /**
     *  comment2Category
     *  @param object $app
     */
    protected function createComment2Category( $app ){
        
       
        $app->db->dropTableIfExists('comment2Category')->execute();
        
        $app->db->createTable(
            'comment2Category',
            [
                    'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                    'catid'     => ['integer'],
                    'commentid' => ['integer'],
                    'userid'    => ['integer'],
                    'parent'    => ['integer']
                    
            ]
        )->execute();
        
        // insert values into category
        $app->db->insert(
               'comment2Category',
               ['catid', 'commentid', 'userid', 'parent']
        );
        
        $app->db->execute([
                1,1,1,1
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'comment2Category',
               ['catid', 'commentid', 'userid', 'parent']
        );
        
        $app->db->execute([
                1,2,2,1
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'comment2Category',
               ['catid', 'commentid', 'userid', 'parent']
        );
        
        $app->db->execute([
                1,3,2,2
                
        ]);
    }
    
    /**
     *  createCommentCategory
     *  @param $app
     */  
    protected function createCommentCategory( $app ){
        
        
        $app->db->dropTableIfExists('commentCategory')->execute();
        
        $app->db->createTable(
            'commentCategory',
            [
                    'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                    'category' => ['varchar(255)'],                    
                    
            ]
        )->execute();
        
        // insert values into category
        $app->db->insert(
               'commentCategory',
               ['category']
        );
        
        $app->db->execute([
                'default'
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'commentCategory',
               ['category']
        );
        
        $app->db->execute([
                'mysql'
                
        ]);
        // insert values into category
        $app->db->insert(
               'commentCategory',
               ['category']
        );
        
        $app->db->execute([
                'fritid'
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'commentCategory',
               ['category']
        );
        
        $app->db->execute([
                'musik'
                
        ]);
        
    }
    /**
     *  CreateTable
     *  @param object $app
     */
    public function createCommentTable( $app ){
        
        // drops table and create a new one with some data
        
        //
        //  create table for categories
        //
        $this->createComment2Category( $app );
        //
        //  create table for categories
        //
        $this->createCommentCategory( $app );
        
        
        //
        //  create table comment
        //
        $app->db->dropTableIfExists('comment')->execute();
        
        $app->db->createTable(
            'comment',
            [
                    'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                    'header'  => ['varchar(80)'],
                    'comment' => ['varchar(255)'],                    
                    'created' => ['datetime'],
                    'updated' => ['datetime'],
                    'user_id' => ['integer'],
                    'ip'      => ['varchar(30)']
                    
            ]
        )->execute();
        $now = gmdate('Y-m-d H:i:s');
        
        $app->db->insert(
               'comment',
               ['comment', 'header', 'created', 'updated',  'user_id', 'ip']
        );
        
        $app->db->execute([
                'Första kommentar',
                'Första rubriken',
                $now,
                null,
                1,
                '127.1.1.1'
                
        ]);
        $now = gmdate('Y-m-d H:i:s');
        
        $app->db->insert(
               'comment',
               ['comment', 'header', 'created', 'updated',  'user_id', 'ip']
        );
        
        $app->db->execute([
                'Andra kommentar',
                'Andra',
                $now,
                null,
                2,
                '127.1.1.1'
        ]);
        
        $now = gmdate('Y-m-d H:i:s');
        
        $app->db->insert(
               'comment',
               ['comment', 'header', 'created', 'updated',  'user_id', 'ip']
        );
        
        $app->db->execute([
                'Tredje kommentar',
                'tredje',
                $now,
                null,
                2,
                '127.1.1.1'
        ]);
        $id1 = $app->db->lastInsertId();
        
        return $id1;
    }
    
    
    
    /**
     *  db for getAllComments
     */
    public function getAllComments( $db, $userID = null ){
        
        if ( ! $userID ){
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__." returns all comments");
            }
            
            // return all comments
            $db->select("c.*, u.name, u.email, c2c.parent")
            ->from('comment AS c')
            ->join('user AS u', 'c.User_id = u.id')
            ->join("comment2Category as c2c", "c2c.commentid = c.id")
            ->orderby('created desc' );
    
            $data = $db->executeFetchAll();
            
            return  $data;
        } else {
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__." only this users ({$userID}) comments");
            }
            
            // return only this users comments
            $db->select("c.*, u.name, u.email, c2c.parent")
            ->from('comment AS c')
            ->join('user AS u', 'c.User_id = u.id')
            ->join("comment2Category as c2c", "c2c.parent = c.id")
            ->where("u.id = ?")
            
            ->orderby('created desc' );
    
            $data = $db->executeFetchAll( [$userID] );
            
            return  $data;
        }
    
    }
    
    /**
     *  getThisComment
     *  @param $id, $db
     */  
    public function getThisComment( $id, $db ){
        // correct bug 20150401
        $db->select('*')
        ->from('comment AS c')
        ->where("c.id = ?");
         $data = $db->executeFetchAll( [$id] );
       
        return  $data;
    }
    
    /**
     *  updateThisComment
     *  @param $id, $db
     */
    public function updateThisCommentInDb( $db, $commentid = null, $param = null ){
        $now = gmdate('Y-m-d H:i:s');
        
        if( count($param ) == 2  && $commentid){
            
                $db->update(
                'comment',
                ['comment', 'updated', 'header'],
                "id = ?"
            );
          
            $res = $db->execute([
                $param['comment'], $now, $param['header'], $commentid
            ]);
            
        }
        
        
        
     }
    
    /**
     *  deleteThisCommentFromDb
     *  @param $id, $db
     *  @return $result
     */
    public function deleteThisCommentFromDb( $id , $db ){
        $db->delete(
            'comment',
            "id = ?"
        );
      //  die( $db->getSQL() );
        $res = $db->execute([$id]);
        return $res;
    }
    
    /**
     *  addNewComment
     *  @param string comment
     *  @param int id
     *  @param object $app
     */  
    public function addNewComment(  $comment, $id, $app, $commentid = null, $tags = null, $parentid = null, $header = null ) {
        
       
        $db = $app->db;
        $ip = $app->request->getServer('REMOTE_ADDR');
        $db->setVerbose(false);
        
        $latestParentID = $this->returnLatestParentID($db) + 1;
       
        $now = gmdate('Y-m-d H:i:s');
      
        if (  $comment && $header && $id && $tags ){
         
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__);
            }
            // make stmt to comment
            $db->insert(
                    'comment',
                    ['comment', 'header', 'created', 'updated',  'user_id', 'ip']
             );   
            
            // make insert
             $db->execute([
                    $comment,
                    $header,
                    $now,
                    null,
                   
                    $id,
                    $ip
            ]);
             
            // get last Inserted ID 
            $lastInserted = $db->lastInsertId();
            
            // if the post in new there are no parent and we set it self as parent
            $parentid = ( ! is_null( $parentid) )? $parentid : $lastInserted;
            
            foreach( $tags as $tag ){
        
                //
                //  get catId for the connection in db
                //
                $catid = $this->getTags( $db, $tag);
           
               
                //
                // insert data to connect comment and category
                //
                $db->insert(
                    'comment2Category',
                    ['catid'     ,
                    'commentid' ,
                    'userid',
                    'parent']
                    
                );
                
                $db->execute([
                    $catid[0]->id,                
                    $lastInserted,
                    $id,
                    $parentid
                   
                ]);
                
           }
        }
         
       
       
       
       
    //    dump($this->viewComment2Category( $db));
      // die();
       
       
    }
  
}