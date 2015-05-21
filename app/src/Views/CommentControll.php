<?php

namespace Mango\Views;
use \Anax\MVC as MVC; 
/**
 *  CommentController for db-managing
 */
class CommentControlls extends \Anax\MVC\CDatabaseModel 
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
    
    /**
     *  fetchUserComments
     *  @param obj $db
     *  @param userID - optional
     */  
    public function fetchUserComments( $db = null, $userID = null ){
        
        dump ( "rad: ".__LINE__." ".__METHOD__ );
        if ( $db && ! $userID ){
            
            // no userid is set lets return all comments    
            return $this->getAllComments( $db);
            
        } else if ( $db && $userID ){
            
            // userid is set lets return only this users comments
            return $this->getAllComments( $db, $userID);
            
        }
        
    }
    
    /**
     *  viewCommentAction
     *  @param string $group
     */  
    public function viewCommentAction( $group = 'php' ){
     
        $this->group = $group;
        $all = $this->fetchUserComments( $this->app->db);
        
        // get userid from session 
        $userid = $this->app->session->get('user', ['id']);
        
        $commentRespons = null;
        foreach( $all as $comments ){
        
            $commentRespons[$comments->id][] = $this->getCommentList( $comments->id, $this->app->db, 'left', 'home' );
            $commentHeader[$comments->id][]  = $comments->header;
           
        }
    //  dump( $commentRespons);
        if(  isset( $_SESSION['user']['acronym'] ) ){
            
            $online = 'online';
            
        } else {
            $online = 'offline';
        }
       dump( "rad: ".__LINE__." ".__METHOD__);
            
            $this->app->views->add('comment/list', [
            'comments'      => $all,
            'header'        => $commentHeader,
            'online'        => $online,
            'group'         => $this->group,
            'new'           =>'' ,
            'userid'        => $userid,
            'errorContent'  => $this->error['errorContent'],
            'errorMail'     => $this->error['errorMail'],
            'errorHomepage' => $this->error['errorHomepage'],
            'errorName'     => $this->error['errorName'],
            'commentlist'   => $commentRespons
        ]);
            
        
        return $all;
    }
    
    /**
     *  getCommentToRespond
     *  @param app $db
     *  @param int $id
     *  @return object $result
     */
    public function getCommentToRespond( $commentid = null ){
        
        
        if (  $commentid ){
            $commentRespons = $this->getCommentAndCategoriesAndUserID( $this->app->db, $commentid, 'limit' );
         
            // returns comment
            if ( isset($commentRespons['data'][0]->parent) ){
                return $commentRespons['data'][0];
            }
           
        }
        
    }
    
    /**
     *  getTotalAnswer
     *  @param int commentid
     *  @param int answers
     */
    public function getTotalAnswers( $commentid = null ){
        
        dump( "rad: ".__LINE__." ".__METHOD__);
        if( $commentid ){
            $commentRespons = $this->countAnswersToComment( $this->app->db, $commentid );
            dump($this->viewComment2Category( $this->app->db));
            return $commentRespons;
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
        
        if ( $db && $type ){
            dump( __LINE__." ".__METHOD__);
            return ( $type == "child" ) ? $this->getCommentAnswersFromParentID($db, $parentid) : $this->getCommentsGroupedByParentID( $this->app->db );
        } else if ( $db && is_null( $type ) ){
            dump( __LINE__." ".__METHOD__);
            return $this->getCommentAndCategoriesAndUserID( $this->app->db, $parentid );
        }
    }
    
    /**
     *  getCommentList
     *  @param int $commentId
     *  @param object $db
     *  @return object @result
     */
    public function getCommentList( $commentId = null, $db = null, $position = 'right', $list = null, $people = null, $type = null ){
        
        // get comments
        if ( $db && ! $people && ! $type ){
            $commentRespons = $this->getCommentsGroupedByParentID( $this->app->db );
         
            dump( __LINE__ . " ". __METHOD__ );
        } else if( $db && $type){
            $commentRespons = $this->getCommentAndCategoriesAndUserID( $this->app->db, $commentId );
        } else {
            dump( __LINE__ . " ". __METHOD__ );
           $commentRespons = $this->getCommentAndCategoriesAndUserID( $this->app->db, $commentId, null, 'child' );    
        }
        
        dump( "rad: ".__LINE__." ".__METHOD__." commentid: ".$commentId);
     
        if ( $db && $commentId && is_null( $list ) ){
         
           
            $html = "\n";
            
            // set defaultLength on comments
            $maxChars   = ( $position == 'right' ) ? 14 : 75;
            $chars      = $maxChars - 5;
            
            $parID = 0;
           
            foreach( $commentRespons as $respose ){
              
                
                // create url to comment
                $url = $this->app->url->create("kommentar/visa/".$respose->commentid);    
                
                // deside what to display
                $comment    = ( $respose->commentid == $commentId ) ? $respose->parent : $respose->child;
                
                // make comment shorter if needed
                $comment    = ( strlen($comment) > $maxChars ) ? trim(substr($comment, 0, $chars)) . '…' /*. substr($comment, -5) */: $comment;
                
                // make link
                $link = "\n\t\t<a href='{$url}'>{$comment}</a>";
                
                // fill html
                if ( $respose->commentid == $commentId && $parID == $respose->parentid ){
                    $html .= "\n\t<span>\n\t\t{$respose->created}: {$comment}\n\t</span><br />";
                } else if ( $parID == $respose->parentid ) {
                    $html .= "\n\t<span class='commentListResponse'>{$respose->created}: {$link}\n\t</span><br />";
                }
                
                
               $parID = $respose->parentid;
                
             //   echo "radnr: ".__LINE__." getCommentList: <br />". dump ( $respose );
            }
            $html .= "\n";
            return $html;
        
        // list is set - Return
        } else if ( $list ){ 
                return $commentRespons;
            }
        
    }
    
    /**
     *  updateComment
     *  @param int $commentId
     *  @return string $comment
     *  
     */  
    public function updateComment( $commentId = null){
       
        if ( $commentId ){
            $res = $this->getThisComment( $commentId, $this->app->db );
            $comment = $res[0];
            
            return $comment;
             
        }
    }
    /**
     *  callDbmodell
     *  @param string $name
     *  @return $result
     */
    public function fillTagsfromDb( $db = null, $commentid = null ){
        dump("rad: ".__LINE__." ".__METHOD__." ". $commentid);
        //
        // fill $tags with all tags from db
        //
        $tags = $this->getTags( $db,null,null, $commentid );
        return $tags;
    }
    
    /**
     *  updateThisComment
     *  @param $id, $db
     */
    public function updateThisComment( $id, $comment){
      
     
        $res = $this->updateThisCommentInDb( $this->app->db, $id,  $comment );
    }
    /**
     *  deleteThisComment
     *  @param $id
     */
    public function deleteThisComment( $id ){
        
       
        $this->deleteThisCommentFromDb( $id, $this->app->db );
        $url = $this->app->url->create('kommentera');
        
        $this->app->response->redirect($url);
    }
    
    /**
     * addNew
     * adds a comment to database
     */
    public function addNew( $values = null ){
        
        if ( $values ){
            dump( "rad: ".__LINE__ . " ". __METHOD__);
             return $this->addNewComment( $values['comment'], $values['uid'],
                                         $this->app, $values['cid'],
                                         $values['tags'], $values['pid'], $values['header'] );
        }
        dump( "rad: ".__LINE__ . " ". __METHOD__);
        $data = [
            'tags' => ( isset( $comment['items'] ) ) ? $comment['items'] : ['default'],
        ];
        
        //addNewComment( $comment, $id, $app, $commentid = null, $tags = null, $parentid = null ) {
       
        return $this->addNewComment( $values['comment'], $id, $this->app, $commentid, $tags, $parentid );
    }
    
    /**
     *  Check input from form
     */
    public function checkInput( $value, $type ){
        
       
        switch( $type ){
            case 'content':
                if ( isset($value) && strlen($value) <= 3 ){
                    $this->validate = false;
                    return  'Ej tillräckligt många tecken i ditt meddelande';
                } else {
                     
                   return '';
                }
            break;
            case 'mail':
                if ( isset($value) && strlen($value) <= 3 ){
                    
                    $this->validate = false;
                    return 'Så ser nog inte din riktiga adress ut??';
                } else if ( isset($value) && strlen($value) >=3 && preg_match("/^[a-z0-9\å\ä\ö._-]+@[a-z0-9\å\ä\ö.-]+\.[a-z]{2,6}$/i", $value) == 1){
                   return ''; 
                } else {
                    return 'Så ser nog inte din riktiga adress ut??'; 
                    $this->validate = false;
                }
                break;
            case 'web':
                if ( strlen($value) == 0 ){
                    return '';
                } else if ( isset($value) && strlen($value) <= 3 ){
                    $this->validate = false;
                    return  'Fel inmatning';
                } else if( strlen($value) >= 3 && preg_match("/^[a-z0-9\å\ä\ö.-]+\.[a-z]{2,6}$/i", $value) == 1){
                    return '';
                } else {
                    return 'Är detta din hemsida verkligen??';
                    $this->validate = false;
                }
                break;
            case 'name':
                if ( isset($value) && strlen($value) <= 3 ){
                    $this->validate = false;
                    return 'Vilket kort namn du har '.$value;
                $this->i ++;
                } else {
                     
                   return '';
                }
                break;
            
            
        }
    }
    
}