<?php

namespace Anax\MVC;


/**
 * Model for Users.
 *
 */
class CDatabaseModel implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;
    public $verbose     = false;
    public $dbVerbose   = false;
        
    function __construct( ){
       
    }
    
    
    
    /*********************************************************************
     *
     *      Tags are managed here
     *      
     *********************************************************************/
    
    
    /**
     *  updateTag
     *  @param int id
     *  @param string Tag
     */
    public function updateTag($db, $id = null, $tag = null ){
        
        // we have an ID and a Tag to update
        if ( $id && $tag ){
            
            $db->update(
                'commentCategory',
                ['category'],
                "id = ?"
            );
          
            $res = $db->execute( [
                $tag, $id
            ]);
            
        // we only have a Tag and want to insert it    
        } else if ( $tag && ! $id){
            
            // insert values into category
            $db->insert(
                   'commentCategory',
                   ['category']
            );
            
            $db->execute([
                    $tag
                    
            ]);
            echo $db->dump();
        }
       
    }
    
    
    
    /**
     *  removeTag
     */
    public function removeTag( $db, $tagid = null ){
        
        
        if( $tagid ){
            $db->setVerbose($this->dbVerbose);
                $db->delete(
                'commentCategory',
                "id = ?"
                );
          
            $res = $db->execute([$tagid]);    
        }
        
        
    }
    
    
    /**
     *  countTags
     *  @param int $tagid
     */
    public function countTags( $catid = null, $db = null ){
        
        
        if ( $catid && $db ) {
            
            $db->select("count(catid) as total")
            ->from("comment2Category as c2c")
            ->where("c2c.catid = ?");
            
            $res = $db->executeFetchAll( [$catid] );
            
            return $res;
        }
    }
    /**
     *  getTags
     *  @param array $db
     *  @return object $result
     */
    public function getTags( $db = null, $category = null, $popular = null, $commentid = null, $catid = null, $position = null ){
        
           if ( $db ){
                
            $db->setVerbose($this->dbVerbose);
                if ( $category && $popular ){
                    if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." popular");
                    }
                } else if ( $popular ){
                    if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." get all categories 1.");
                    }
                    
                    //
                    // count using of tags
                    //
             
                   $db->select("c.id, catid, count(catid) as popular, comment, header, category")
                    ->from("comment as c")
                    ->join("comment2Category as c2c", "c2c.commentid = c.id")
                    ->Join("commentCategory as cc", "cc.id = c2c.catid")
                    ->groupby("cc.id")
                    ->orderby("popular desc");
                    
                    if ( $catid ){
                        $db->where("catid = ?");
                    }
                    if ( $position == 'triptych_2'){
                        $db->limit("10");
                    }
                    $res = (! $catid ) ? $db->executeFetchAll(  ): $db->executeFetchAll( [$catid] );
                    
                    
               
                    return $res;
                
                } else if ( $category ){
                  if( $this->verbose == true ){
                        dump( __LINE__. " ". __METHOD__." get category id from db.");
                    }
                  // get category id from db
                    $db->select("*")
                    ->from("commentCategory");
                     if ( $catid ){
                        $db->where("commentid = ?");
                    } else{
                        $db->where('category = ?');    
                    }
                    $db->orderby("category");
                    $res = ( is_null( $catid ) ) ? $db->executeFetchAll( [strtolower($category)] ) :$db->executeFetchAll( [strtolower($catid)] );
                    
                    
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
                        dump( __LINE__. " ". __METHOD__." get all categories 2.");
                    }
                  
                    $db->select("category, cc.id")
                    ->from("commentCategory as cc")
                    
                    ->orderby("category asc");
                    $res = $db->executeFetchAll(  );
                    
                    return $res;
                    
                }
                
           }
    }
    
    /*************************
     *
     *      Users
     *
     **************************/
    
    
    
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
     *  getNotDeletedUsers
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
    
    
    
    /**
     *  countTable
     */  
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
      try{
            $app->db->dropTableIfExists('user')->execute();
        } catch(EXCEPTION $e){
            
        }
        
     
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
    protected function getCommentsGroupedByParentID( $db = null, $parentid = null ){
        if( $this->verbose == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
            
        }
       
        
        if ( $db ){
            $db->setVerbose($this->dbVerbose);
            // gets a list with parent comments
            $db->select("*,commentid, c.comment, c.header, c.created, catid, userid, name, parentid,
                        strftime('%Y-%m-%d %H:%M', c.created) as created, user.email,
                        group_concat( distinct cc.category) as tag, group_concat(userid) as users,
                        group_concat( distinct cc.id) as tagid, count(c2c.parentid) -1  as answers")
            ->from("comment as c")
            ->join("comment2Category as c2c", "c2c.parentid = c.id")
            ->join("commentCategory as cc", "c2c.catid = cc.id")
            ->join("user","user.id = c.userid");
           
           
           // if parentid is set
           if ( ! is_null( $parentid ) ){
            $db->where("parentid = ?");
            
           }
           
            $db->groupby("c2c.parentid")
            ->orderby('created desc, parentid asc, commentid asc, header' );
            
            // if parentid is set
           if ( ! is_null( $parentid ) ){
            $db->limit(1);
           }
           

            $data = ( is_null( $parentid ) ) ? $db->executeFetchAll(  ) : $db->executeFetchAll( [$parentid] );
           
        
            
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
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
        }
        // gets a list with answers to comment
        if( $db && $parentid){
           
            $db->select("c2c.commentid, user.name, user.email, c.comment as comment, c.header as header, c.created as created, c2c.parentid, strftime('%Y-%m-%d %H:%M', c.created) as created, 
                        group_concat( distinct cc.category) as tag, group_concat( distinct cc.id) as tagid, c.userid as childid") 
            ->from("comment2Category as c2c")
            ->join("commentCategory as cc", "cc.id = c2c.catid")
            ->join("comment as c", "c.id = c2c.commentid")
            ->join("comment as p", "p.id = c2c.parentid")
            ->join("user ", "user.id = c.userid")
            ->where("c2c.parentid = ?")
           // ->andwhere("c2c.parentid != c.id")
            ->groupby("c2c.commentid")
            ->orderby(' commentid asc,created desc' );
            
            $data = $db->executeFetchAll( [$parentid] );
            
            return $data;
          //group_concat(cc.category) as tag, group_concat(cc.id) as tagid,count(c2c.parentid) -1  as answers")
        }
    }
    
    
    
    /**
     *  getCommentAndCategories
     */
    protected function getCommentAndCategoriesAndUserID( $db = null, $commentid = null, $limit = null, $child = null ){
    
        if( $this->verbose == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__." ".__METHOD__ ." ". $commentid. " ". $limit . " " . $child." ". $callers[1]['function']);
        }
        if ( ! is_null( $db ) ){
        
            $param      = null;
            $rowCount   = null;
            
            $db->setVerbose($this->dbVerbose );
            $db->select("c2c.commentid, cc.category,p.ip, u.email, child.userid, u.name, p.comment as parent, p.id, c2c.commentid, c2c.parentid, p.created as created,
                        child.comment as comment, child.id as childid, child.created as childdate, p.header as header, child.header as cheader")
            ->from("comment2Category as c2c")
            ->join("comment as p", "p.id = c2c.parentid")
            ->join("comment as child", "child.id = c2c.commentid")
            ->join("commentCategory as cc", "cc.id = c2c.catid")
            ->join('user AS u', 'child.userid = u.id');
            
            // if not commentid = null
            if ( $commentid && ! $child){
         
                $db->where("commentid = ?");
             
                $param = [$commentid];
               
            } else if ( $child  && $child == 'child'){
         
                $db->where("childid = ?");
               // $db->andwhere("p.id != child.id");
                $param = [$commentid];
                
                
            } else if( $child && $child == 'tag' ){
                $db->where("c2c.catid = ?");
                $param = [$commentid];
                
            }
            
            $db->orderby('created desc, childdate desc, c2c.parentid asc, child.id asc' );
            
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
            
            $db->select("count(DISTINCT c2c.commentid) as rows, parentid")
            ->from("comment2Category as c2c")
            ->where("parentid = ?")
            ->andwhere("parentid != commentid")
            ->groupby("c2c.parentid")
            ;
            
            $data = $db->executeFetchAll( [$commentID] );
            
        
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__ ." ". $commentID);
                
            }
            
            return $data;
        
        } else if ( $db ){
            
            $db->select("count(DISTINCT c2c.commentid) as rows, parentid")
            ->from("comment2Category as c2c")
            ->where("parentid != c2c.commentid")
            ->groupby("parentid");
            
            $data = $db->executeFetchAll(  );
            
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__ ." ". $commentid);
                
            }
            
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
            ->orderby("parentid desc")
            ->limit(1);
            
            $data = $db->executeFetchAll();
         
            return $data[0]->parentid;
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
        ->join("user", "user.id = userid")
        ->orderby("parentid, parentid")
        //->where("commentid = 14")
        ;
        $p = null;
        $c = null;
        $m      = [];
        $child  = [];
         $data = $db->executeFetchAll();
         foreach( $data as $row ){
            $r[] = "id: {$row->id}, catid: {$row->catid}, commentid: {$row->commentid}, parent: {$row->parentid} category: {$row->category}, header: {$row->header}, userid: {$row->userid}, name: {$row->name}";
            
            /*$m[] = $row->parentid;
            if( $p == $c  ){
                
            }
            
            dump( $row);
            if ( $p && $p == $row->parentid ){
                $commentkey = $p;
            } else {
               
                $commentkey = $row->parentid;
                
            }
            $p = array_search($row->parentid, $m); // $key = 2;
            
            $m[$commentkey] = [$row->commentid => [
                    'id'        => $row->id,
                    'catid'     => $row->catid,
                    'commentid' => $row->commentid,
                    'parentid'  => $row->parentid,
                    'header'    => $row->header,
                    'comment'   => $row->comment,
                    'created'   => $row->created,
                    'updated'   => $row->updated,
                    'userid'    => $row->userid,
                    'acronym'   => $row->acronym,
                    'email'     => $row->email,
                    'deleted'   => $row->deleted,
                    ]
                ];*/
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
                    
                    'parentid'    => ['integer']
                    
            ]
        )->execute();
        
        // insert values into category
        $app->db->insert(
               'comment2Category',
               ['catid', 'commentid',  'parentid']
        );
        
        $app->db->execute([
                1,1,1
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'comment2Category',
               ['catid', 'commentid', 'parentid']
        );
        
        $app->db->execute([
                2,2,1
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'comment2Category',
               ['catid', 'commentid',  'parentid']
        );
        
        $app->db->execute([
                1,3,3
                
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
                    'category' => ['varchar(255)', 'unique'],                    
                    
            ]
        )->execute();
        
        // insert values into category
        $app->db->insert(
               'commentCategory',
               ['category']
        );
        
        $app->db->execute([
                'elbas'
                
        ]);
        
        // insert values into category
        $app->db->insert(
               'commentCategory',
               ['category']
        );
        
        $app->db->execute([
                'strängar'
                
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
        try{
            $app->db->dropTableIfExists('comment')->execute();    
        } catch(EXCEPTION $e){
            
        }
        
        
        $app->db->createTable(
            'comment',
            [
                    'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                    'header'  => ['varchar(80)'],
                    'comment' => ['varchar(255)'],                    
                    'created' => ['datetime'],
                    'updated' => ['datetime'],
                    'userid' => ['integer'],
                    'ip'      => ['varchar(30)']
                    
            ]
        )->execute();
        $now = gmdate('Y-m-d H:i:s');
        
        $app->db->insert(
               'comment',
               ['comment', 'header', 'created', 'updated',  'userid', 'ip']
        );
        
        $app->db->execute([
                'Var hittar man en bra elbas?',
                'Köpa nytt',
                $now,
                null,
                1,
                '127.1.1.1'
                
        ]);
        $now = gmdate('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), date("j")+1, date("Y")));
        
        $app->db->insert(
               'comment',
               ['comment', 'header', 'created', 'updated',  'userid', 'ip']
        );
        
        $app->db->execute([
                'Finns det någon bra ubass?',
                're: Köpa nytt',
                $now,
                null,
                2,
                '127.1.1.1'
        ]);
        
        $now = gmdate('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), date("j")+2, date("Y")));
        
        $app->db->insert(
               'comment',
               ['comment', 'header', 'created', 'updated',  'userid', 'ip']
        );
        
        $app->db->execute([
                'Hitta noter',
                'Finns det bra noter att köpa',
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
            $db->select("c.*, u.name, u.email, c2c.parentid, c2c.commentid, c.userid, c2c.catid")
            ->from('comment AS c')
            ->join('user AS u', 'c.userid = u.id')
            ->join("comment2Category as c2c", "c2c.commentid = c.id")
            ->orderby('created desc' )
            ->groupby("c2c.commentid");
    
            $data = $db->executeFetchAll();
            
            return  $data;
        } else {
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__." only this users ({$userID}) comments");
            }
            
            // return only this users comments
            $db->select("c.*, u.name, u.email, c2c.parentid")
            ->from('comment AS c')
            ->join('user AS u', 'c.userid = u.id')
            ->join("comment2Category as c2c", "c2c.parentid = c.id")
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
    public function getThisComment( $id, $db, $parentid = null ){
        // correct bug 20150401
        $db->select('*')
        ->from('comment AS c')
        ->join("comment2Category as c2c", "c2c.commentid = c.id");
        
        if( $parentid ){
            $db->where("c2c.parentid = ?");
        } else{
            $db->where("c.id = ?");    
        }
        
        
        $db->orderby("c2c.parentid");
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
        
        $check = $this->getThisComment( $id, $db, 'parent' );
        
        $msg = "<h3>Följande inlägg är raderade:</h3>\n";
        if( isset($check[0]) && $check[0]->commentid == $check[0]->parentid ){
            
            // remove comment and answers...
            if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__ ." remove parent and answers ". $id);
               
            }
            
            
            foreach( $check as $comment ){
                
                // remove link to categories
                $db->delete(
                'comment2Category',
                'commentid = ?'
                );
                
                $res = $db->execute([$comment->commentid]);
                 
                  // remove answers...
                $db->delete(
                    'comment',
                    "id = ?"
                );
                
                $res = $db->execute([$comment->id]);
                
                $msg .= "{$comment->header}<br />\n";
            }
            
        } else{
             $check = $this->getThisComment( $id, $db, null );
             
             if( $this->verbose == true ){
                dump( "rad: ".__LINE__." ".__METHOD__ ." remove answer ". $id);
                
            }
            
            // remove answers...
            $db->delete(
                'comment',
                "id = ?"
            );
          
            $res = $db->execute([$id]);
            
            $db->delete(
                'comment2Category',
                'commentid = ?'
            );
            $res = $db->execute([$id]); 
            $msg .= "".$check[0]->header."<br />\n";
        }
       
        
        
        return $msg;
    }
    
    /**
     *  addNewComment
     *  @param string comment
     *  @param int id
     *  @param object $app
     */  
    public function addNewComment(  $comment, $id, $app, $commentid = null, $tags = null, $parentid = null, $header = null ) {
        
        $callers=debug_backtrace();
     //       dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['class']." ".$callers[1]['function']);
       
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
                    ['comment', 'header', 'created', 'updated',  'userid', 'ip']
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
            
            foreach( $tags as $key => $value  ){
        
        
              //  $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
              //  $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
                //
                //  get catId for the connection in db
                //
                $catid = $this->getTags( $db, $value);
           
               
               if ( isset( $catid[0] )){
                //
                // insert data to connect comment and category
                //
                $db->insert(
                    'comment2Category',
                    ['catid'     ,
                    'commentid' ,
                    
                    'parentid']
                    
                );
                
                $db->execute([
                    $catid[0]->id,                
                    $lastInserted,
                    
                    $parentid
                   
                ]);
               }
                
                
           }
         //  die();
           $url = $app->url->create("kommentar/visa/{$parentid}"); 
           $app->response->redirect($url);
        }
         
    
    }
  
}