<?php

namespace Mango\Views;
use \Anax\MVC as MVC; 
/**
 *  CommentController for db-managing
 */
class CommentHandler extends \Anax\MVC\CDatabaseModel 
{
    
    private $dbModule       = null;
    private $app            = null;
    private $group          = null;
    private $error          = null;
    
    
    public function __construct( $app = null, $errorArray = null ){
        parent::__construct();
        if ( $app ){
            $this->app          = $app;    
        }
        if( $errorArray ){
            $this->error        = $errorArray;
        }
        
    }
    

    /*************************************************************
     *
     *          List data
     *
     *************************************************************/
    
    /**
     *  fetchUserComments
     *  @param obj $db
     *  @param userID - optional
     */  
    public function fetchUserComments( $db = null, $userID = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        if ( $db && ! $userID ){
            
            // no userid is set lets return all comments    
            return $this->getAllComments( $db);
            
        } else if ( $db && $userID ){
            
            // userid is set lets return only this users comments
            return $this->getAllComments( $db, $userID);
            
        }
        
    }
    
    
    
    /**
     *  getAllComments
     *  returns list including parentid and commentid
     *  @return object $result || false
     */
    public function getAllCommentData(){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        
        // get comments
        $this->app->db->select("*")
            ->from("comment as c")
            ->join("comment2Category as c2c", "c2c.commentid = c.id")
            ->join("user", "user_id = c2c.userid")
            ->groupby("c2c.parentid");
            
        $res = $this->app->db->executeFetchAll(  );
        
        if( $this->verbose == true && $res ){
            dump( $this->app->db->getSQL() );
            dump("hämtade ut: ".count($res)." rader");
        }
        
        return $res;
    }
    
    /**
     *  getCommentList
     *  @param int $commentId
     *  @param object $db
     *  @return object @result
     */
    public function getCommentList( $parentID = null, $maxChars = 75, $type = 'parent' ){
        
        if( $type ){
            
            switch( $type ){
                case 'child':
                    return $this->getCommentAndCategoriesAndUserID($this->app->db, $parentID, null, 'child');
                    break;
                case 'parent':
                    return $this->getCommentAndCategoriesAndUserID($this->app->db, $parentID, null, 'parent');
                    break;
                case 'all':
                    return $this->getCommentAndCategoriesAndUserID($this->app->db);
                    break;
            }
        }
        
        
    }
    /**
     *  getChildsToComment
     *  @param  int $commentid
     *  @return object $result || false
     */
    public function getChildToComment( $parentID = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        
        if ( $parentID ){
        
          //  return $this->getCommentAndCategoriesAndUserID($this->app->db, $parentID, null, 'child');
        
            // get children
            $this->app->db->select("*")
                ->from("comment as c")
                ->join("comment2Category as c2c", "c2c.commentid = c.id")
                ->join("comment as p", "p.id = c2c.parentid")
                ->join("user", "userid = c2c.userid")
                
                ->where("parentid = ?")
                ->groupby("parentid")
                ->orderby("commentid asc")
                
            ;
            
            $res = $this->app->db->executeFetchAll( [$parentID] );
        
            if( $this->verbose == true && $res ){
                dump( $this->app->db->getSQL() );
                dump("hämtade ut: ".count($res)." rader");
                dump( $res);
            }
            
            return $res;  
            
        }
        
        
    }
    
    /**
     *  readGroupedComments
     *  @param object $db
     *  @param string $type parent || child
     *  
     *  @return object $result
     */
    public function getGroupedComments( $db = null, $parentid = null, $type = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__." type: ".$type);
            dump($this->viewComment2Category( $db ));
            
        }
    
        if ( $db && $type ){
            
            return ( $type == "child" ) ? $this->getCommentAnswersFromParentID($db, $parentid) : $this->getCommentsGroupedByParentID( $this->app->db, $parentid );
        } else if ( $db && is_null( $type ) ){
            
            return $this->getCommentAndCategoriesAndUserID( $this->app->db, $parentid );
        }
    }
    
    
    /**
     *  getCommentToRespond
     *  @param app $db
     *  @param int $id
     *  @return object $result
     */
    public function getCommentToRespond( $commentid = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        
        if (  $commentid ){
            $commentRespons = $this->getCommentAndCategoriesAndUserID( $this->app->db, $commentid, 'limit' );
         
            // returns comment
            if ( isset($commentRespons['data'][0]->parent) ){
                return $commentRespons['data'][0];
            }
           
        }
        
    }
    
    /**
     *  updateComment
     *  @param int $commentId
     *  @return string $comment
     *  
     */  
    public function getCommentToUpdate( $commentId = null){
       
        if ( $commentId ){
            $res = $this->getThisComment( $commentId, $this->app->db );
            $comment = $res[0];
            
            return $comment;
             
        }
    }
    
    /**
     * getTagComments
     * @param int $tagid
     * @return object result
     */
    public function getTagComments( $tagid = null ){
        if( $this->verbose == true ){
        dump( "rad: ".__LINE__." ".__METHOD__);    
        }
        
        // we dont need to get the comments unless a tag is picked
        if ( $tagid ){
            $res = $this->getCommentAndCategoriesAndUserID( $this->app->db, $tagid, null, 'tag');
            return $res;
        }
        
    }
    
     
     /*************************************************************
      *
      *     tags
      *     
      *************************************************************/
     
     /**
     *  callDbmodell
     *  @param string $name
     *  @return $result
     */
    public function fillTagsfromDb( $db = null, $commentid = null ){
       
        //
        // fill $tags with all tags from db
        //
        $tags = $this->getTags( $db,null,null, $commentid );
        
        return $tags;
    }
    
    /**
     *  getTotalAnswers
     *  @param int commentid
     *  @return int $commentRespons
     */  
    public function getTotalAnswers( $commentid = null ){
        
        
        if( $commentid ){
            $commentRespons = $this->countAnswersToComment( $this->app->db, $commentid );
            //dump($this->viewComment2Category( $this->app->db));
            return $commentRespons;
        }
    }
    
    /***************************************************************
     *
     *          Update data
     *
     ***************************************************************/
    
    /**
     *  updateComment
     *  @param int $commentid
     *  @param array $param
     *  @return string $result
     */
    public function updateThisComment( $commentid = null, $param = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        if ( $commentid && $param ){
            
            // handle data
             $res = $this->updateThisCommentInDb( $this->app->db, $commentid, $param );
        }
    }
    
    /******************************************************************
     *
     *          Delete data
     *
     ******************************************************************/
    
    /**
     *  deleteThisComment
     *  @param int $id
     *  redirect
     */  
    public function deleteThisComment( $id ){
        
       if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        $this->deleteThisCommentFromDb( $id, $this->app->db );
        $url = $this->app->url->create('kommentera');
        
        $this->app->response->redirect($url);
    }
    
    
    
    /*********************************************************************
     *
     *             Add data
     *
     *********************************************************************/
    
    /**
     *  prepareToAddNewComment
     *  @param array $param - comment, header, userid, parentid
     *  
     */
    public function prepareToAddNewComment( $param = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
        }
        if( $param ){
            
            if ( $param ){
                
                 return $this->addNewComment( $param['comment'], $param['uid'],
                                             $this->app, $param['cid'],
                                             $param['tags'], $param['pid'], $param['header'] );
            }
            
            $data = [
                'tags' => ( isset( $param['items'] ) ) ? $param['items'] : ['default'],
            ];
            
            //addNewComment( $comment, $id, $app, $commentid = null, $tags = null, $parentid = null ) {
           
            return $this->addNewComment( $values['comment'], $id, $this->app, $commentid, $tags, $parentid );
        }
    }
    
    /*************************************************************************
     *
     *          Output data
     *
     ************************************************************************/
    
    /**
     *  outputUpdateList
     *  @param array $param
     */  
    public function outputUpdateList( $param = null ){
        
        if( $this->verbose == true ){
            dump( "rad: ".__LINE__." ".__METHOD__);
            $callers=debug_backtrace();
            dump( "function called by ". $callers[1]['function']);
        }
        
        $new = ( isset( $param['new'] ) ) ? $param['new'] : '';
        
        $this->app->views->add('comment/list', [
            'comments'      => $param['all'],
            'header'        => $param['header'],
            'online'        => $param['online'],
            'group'         => $param['group'],
            'new'           => $new ,
            'userid'        => $param['userid'],
            'errorContent'  => $param['errorContent'],
            'errorMail'     => $param['errorMail'],
            'errorHomepage' => $param['errorHomepage'],
            'errorName'     => $param['errorName'],
            'children'      => $param['children']
        ]);
    }
}