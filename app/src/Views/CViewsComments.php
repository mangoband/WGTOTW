<?php

namespace Mango\Views;

class CViewsComments  {
    
    private $app = null;
    private $user = null;
    private $param  = null;
    
    public function __construct( $app, $user = null, $param = null ){
        
        
      $this->app    = ( $app )  ? $app  : null;
      $this->user   = ( $user ) ? $user : null;
      $this->param  = $param;
    //  parent::__construct();
    }
    
    /**
     *  viewPopularTags
     */  
    private function viewPopularTags( $tagid = null ){
        $CTagViews = new \Mango\Views\CTagViews( $this->app );
        $CTagViews->listTags($tagid);
    }
    
    /**
     *  doAction
     *  depending on the currentUrl makes this different actions
     */  
    public function doAction(){
        
        // we need to have param to check where to go
        if ( isset( $this->param ) ){
            
            if ( $this->param['page'] == 'kommentera' ){
                $this->commentActionWithDb( $this->app, $this->param['url'] );
            } else{
                
                switch( $this->param['option'] ){
                    case 'uppdatera':
                        
                        $this->updateComment( $this->app, $this->param['id'] );
                        
                        break;
                    case 'visa':
                        
                        $this->showComment( $this->param['id'] );
                        break;
                    
                    case 'svara':
                        
                        $this->respondComment( $this->app, $this->param['id'] );
                        break;
                    
                    case 'radera':
                        
                        $this->deleteComment( $this->app, $this->param['id'] );
                        break;
                    
                    case 'anv':
                        
                        $this->userComments( $this->app, $this->param['id'] );
                        break;
                    
                    case 'fraga':
                    case 'ny':
                        
                        $this->addNewComment( $this->app );
                        break;
                }
            
            }
        
        }
        
        
    }
    /**
     *  returAnswers
     *  @param obj items
     *  @param int id
     *  @return int answers
     */  
    private function returnAnswers(  $commentid = null, $cc = null ){
        
      
       
        
        $rows = 0;
        if ( $commentid  && $cc ){
            $answers = $cc->getTotalAnswers( $commentid);
        
            return ( isset( $answers[0]->rows ) &&  ! is_null( $answers[0]->rows ) && $answers[0]->rows > 1 ) ? $answers[0]->rows - 1 : 0;
            
            
        }
        return $rows;
    }
    /**
     *  viewListWithComments
     */
    public function viewListWithComments( $param = null, $data = null, $isTag = false, $tagid = null ){
        
       
       dump( __METHOD__);
       
        // view tags from db ( CDatabaseModel)
        $this->viewPopularTags( $tagid);
        
        // make object of commentControll
        $cc = null;
        $cc = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        
        // fetch data from database
        
        $comments = (  $isTag == true ) ? $data :$cc->getGroupedComments($this->app->db, null, 'parent');
        
            
        // define content & header
        $header = '<h2>Kommentarer</h2>';
        $content = "\n<table class='commentList'>";
        
        // declare th
        $content .= "<tr><th>Svar</th><th class='commentHeader'>Inlägg</th><th>Datum</th></tr>";
        $parentID = null;
        
        $totalAnswers = count( $comments ) - 1;
        
        
        // fill td in table
        foreach( $comments as $comment ){
          
          
            $answers = ( isset($comment->answers ) ) ? $comment->answers : $this->returnAnswers($comment->parentid, $cc); 
            
            if( $parentID != $comment->parentid ){
            
                $url = $this->app->url->create("kommentar/visa/".$comment->parentid);
                
                $content .= "\n\t<tr class='commentListRow'>".$this->createCommentRow([
                                                 'date'     => $comment->created,
                                                 'header'   => $comment->header,
                                                 'views'    => 1,
                                                 'answerNr' => $answers,
                                                 'url'      => $url
                                                 ])."</tr>";
                $parentID = $comment->parentid;   
            }
            if ( $parentID != $comment->parentid ){
                 
            }
            
        }
        $content .= "\n</table>";
        
        // view comments
        $this->app->views->add('default/article', ['header'=>$header, 'content' => $content], 'main-wide');
       
       
        
     
    }
    
    
    /**
     *  createCommentRow
     */
    public function createCommentRow( $p = null ){
        
        // collect data from array $p
        $url                = ( isset( $p['url'] ) )        ?  $p['url']    : '';
        $element            = ( isset( $p['type'] ))        ?  "<{$p['type']}>" : "<li>";
        $commentHeader      = ( isset( $p['header'] ) )     ?  "<td class='commentHeader'><a href='{$url}'>{$p['header']}</a></td>"      : '';
        $commentAnswerNr    = ( isset( $p['answerNr'] ) )   ?  "<td class='commentAnswerNr'><p>( {$p['answerNr']} )</p></td>"  : '';
        $commentDate        = ( isset( $p['date'] ) )       ?  "<td class=''>{$p['date']}</td>"          : '';
        $commentViews       = ( isset( $p['views'] ) )      ?  "<td class='commentViews'><p>( {$p['views']} )</p></td>"        : '';
        
        return $commentAnswerNr.$commentHeader.$commentDate;
        
    }
    
    
    /**
     *  createCommentStructure
     *  @param array $param - $header, content, id, userid, date
     *  @return string $html
     *
     */
    private function createCommentStructure( $p = null ){
        
        // collect data from array $p
        $header     = ( isset( $p['header'] ) ) ? $p['header']  : '';
        $content    = ( isset( $p['content']) ) ? $p['content'] : '';
        $id         = ( isset( $p['parentid'] ) )     ? $p['parentid']      : '';
        $userid     = ( isset( $p['userid'] ) ) ? $p['userid']  : '';
        $user       = ( isset( $p['user'] ) )   ? $p['user']    : '';
        $date       = ( isset( $p['date'] ) )   ? $p['date']    : '';
        $row        = ( isset( $p['row'] ) )    ? $p['row']     : '';
        $tags       = ( isset( $p['tags'] ) )   ? $p['tags']    : '';
        $answers    = ( isset( $p['answers']))  ? $p['answers'] : '';
        
        $url = $this->app->url->create("kommentar/visa/".$id);
        $url2 = $this->app->url->create("kommentar/svara/".$id);
        
        $html = '';
       if ( $row == 0 ){
            $html .= "\n\t<li>\n\t<h2>\n\t{$header}</h2></li>";
        }
        $html .= "\n\t<li class='comment'>".markdown($content)."</li>";
        if ( $row == 0 ){
            $html .= "\n\t<li class='viewTags'>{$tags}<input type='hidden' value='{$id}' name='view[{$id}]' /></li>";
            $html .= "\n\t<li><span class='commentAnswerList'><a href='{$url2}'>Besvara</a>  <a href='{$url}'>Svar</a>({$answers}) </span><span class='commentUserList'>{$user}</li>";
        }
        return $html;
    }
    
    /**
     *  commentActionWidthDb
     */
    public function commentActionWithDb( $app, $currentUrl ){
       
      
        try{
       
        $app->theme->setVariable('gridColor', '');
        $app->theme->addStylesheet('css/comment.css');
        $title = "Kommentera";
        $app->theme->setTitle($title);
       
       
        //$user = new \Anax\Users\User( $app );
        $this->user->isOnline();
        $online = $this->user->isUserOnline();
        
        
        
        $ch = new CommentHandler( $app,['errorContent'=>getError(0), 'errorMail'=>getError(1),
                                        'errorHomepage'=>getError(2),'errorName' => getError(3)]);
        
        $allUserComments = $ch->fetchUserComments( $this->app->db);
        $comments = $ch->getAllCommentData();
        
        // errors
        $errorContent   = ( isset( $this->error['errorContent'] ) )  ? $this->error['errorContent']  : null;
        $errorMail      = ( isset( $this->error['errorMail'] ) )     ? $this->error['errorMail']     : null;
        $errorHomepage  = ( isset( $this->error['errorHomepage'] ) ) ? $this->error['errorHomepage'] : null;
        $errorName      = ( isset( $this->error['errorName'] ) )     ? $this->error['errorName']     : null;
        
      
        // make content to updateform
        
        foreach( $comments as $comment ){
          
            $tmpData = $ch->getChildToComment( $comment->parentid );
        
        
            // get formated childdata
            $childComments[$comment->commentid][] = $this->formatChildComments( $tmpData, $comment->parentid );
            
      
        }
        
        $ch->outputUpdateList([
            'all'       => $allUserComments,
            'online'    => $online,
            'new'       => '',
            'userid'    => $comment->user_id,
            'errorContent'  => $errorContent,
            'errorMail'     => $errorMail,
            'errorHomepage' => $errorHomepage,
            'errorName'     => $errorName,
            'children'      => $childComments,
            'header'        => $comment->header,
            'group'         => null,
            
            ]);
        
        
        
        } catch( Exception $e ){
            $content = $e->getMessage();
        }
        
      
      
      
    }
    
    /**
     *  formatChildComments
     *  @param object $result
     *  @return array $result
     */  
    private function formatChildComments( $result = null, $parentID = null ){
        
         
        
        // set defaultLength on comments
        $maxChars   = 75;//( $position == 'right' ) ? 14 : 75;
        $chars      = $maxChars - 5;
        $html       = "\n";
        $parID      = 0;
        
        if ( $result && $parentID ){
            
            // compress to only output header and date
            foreach( $result as $child ){
                
              
                // create url to comment
                $url = $this->app->url->create("kommentar/visa/".$child->commentid);
            
                // make comment shorter if needed
                $header    = ( strlen( $child->header) > $maxChars ) ? trim(substr($child->header, 0, $chars)) . '…' /*. substr($comment, -5) */: $child->header;
                
                // make link
                $link = "\n\t\t<a href='{$url}'>{$header}</a>";
                
                // fill html
                if( $child->commentid != $parentID ){
                    $html .= "\n\t<span>\n\t\t{$child->created}: {$header}\n\t</span><br />";
                    $html .= "\n\t<span class='commentListResponse'>{$child->created}: {$link}\n\t</span><br />";
                }
                /*
                if ( $child->commentid != $parentID && $parID == $child->parent ){
                    $html .= "\n\t<span>\n\t\t{$child->created}: {$header}\n\t</span><br />";
                } else if ( $parID != $child->parent ) {
                    $html .= "\n\t<span class='commentListResponse'>{$child->created}: {$link}\n\t</span><br />";
                }
                */
                $parID = $child->parentid;
                
                
                
            }
             $html .= "\n";
            
            return $html;
        }
    }
    
    
    
    /**
     *  listOldComments
     *  @param object $data
     *  @param array $app
     *  @return string $html - a list of parents to comment.
     */  
    protected function listOldComments( $data = null, $app = null ){
        
        $this->setDump( "rad: ".__LINE__ ." ". __FUNCTION__ );
        
        //
        // get object with parents to the comment...
        //
        if ( ! is_null( $data ) ){
            
            
            $html = '';
            foreach( $data as $key => $row ){
              //  echo $row->parent."<br />";
                if ( $row->commentid = $row->parentid ){
                    $comment = $row->parent;
                } else {
                    $comment = $row->child;
                }
                $url = $app->url->create("kommentar/visa/".$row->userid);    
                $link = "<a href='{$url}'>{$row->created}</a>";
                if(strlen($comment) > 75) {
                    $comment = substr($comment, 0, 70) . '…' . substr($comment, -5);
                } else {
                    $comment = $comment;
                }
                
                $html .= "\n ". $link ."<span class='commentListResponse'>   |--- ".$comment."</span><br />";
            }
            return $html;
            
        }
       //  $this->commentID
       
       
        $html = "kommentar<br />   |---Titel";
        return $html;
    }
    /**
     *  kommenteraAction
     *  $param $app
     */
    public function kommenteraAction( $app, $currentUrl ){
        
        
        $app->theme->setVariable('gridColor', '');
        $app->theme->addStylesheet('css/comment.css');
        $app->theme->setTitle("Kommentera");
        $app->views->add('comment/form', [
              'mail'      => null,
              'web'       => null,
              'name'      => null,
              'content'   => null,
              'output'    => null,
              'id'        => null,
              'group'       => 'mysql',
              'errorContent'  => getError(0),
              'errorMail'     => getError(1),
              'errorHomepage' => getError(2),
              'errorName'     => getError(3),
              'tmp'             => '',
          ]);
          $app->dispatcher->forward([
              'controller' => 'comment',
              'action'     => 'view',
              'params'     => ['mysql', getError(0), getError(1), getError(2), getError(3), $currentUrl],
          ]);
        
    }
    
    
    
    
    /**
     *  deleteComment
     */
    private function deleteComment( $app, $commentID = null ){
        $cc = new CommentHandler( $app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        if ( $commentID ){
            
            $cc->deleteThisComment(  $commentID     );
        }
    }
    /**
     *  updateComment
     *  @param $app
     */  
    public function updateComment( $app, $commentID, $answer = null ){
        
       
      // public function updateComment( $comment, $tags = null, $selectedTags = null, $answer = null ){  
        
        $app->theme->setVariable('gridColor', '');
        $app->theme->addStylesheet('css/comment.css');
        $title = "Uppdatera kommentar";
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        
        $app->session(); // Will load the session service which also starts the session
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $ch = new CommentHandler( $app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        $comment = $ch->getCommentToUpdate( $commentID );
        
        //
        // fill $tags with all tags from db
        //
        $tags =  $ch->fillTagsfromDb($this->app->db);
        
        //
        // fill $tags with all tags from db
        //
        $selectedTags =  $ch->fillTagsfromDb($app->db,  $commentID);
        
        
        
        
        $form = new  \Anax\CFormContact\CFormComment( $app, $user, $ch );
        
        $form->updateComment( $comment, $tags, $selectedTags, $answer, $_SESSION['user']['id']);
        $this->user->isOnline();
        $online = $user->isUserOnline();
        
        //
        // view logoutbtn if logged in
        //
        if ( $online === true ){
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
     
        $app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
      //  $this->app->views->add('me/article', ['header'=>$responseHeader, 'content' => $responsComment], 'main');
        }
    
        
        
    }
    
    /**
     *  addNewComment
     *  @param array $app
     */  
    public function addNewComment( $app, $param  = null , $commentid = null, $tags = null, $parentID = null ){
        
    
        
        $app->theme->setVariable('gridColor', '');
        $app->theme->addStylesheet('css/comment.css');
        $title = ( is_null( $parentID ) )? "Lägg till ny kommentar" : 'Svara';
        $app->theme->setTitle($title);
        $header = "<h2>{$title}</h2>";
        
        $app->session(); // Will load the session service which also starts the session
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
        $ch = new CommentHandler( $app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        
        //
        // fill $tags with all tags from db
        //
        $tags = $ch->fillTagsfromDb($this->app->db);
        
        
        //
        //
        // get comment user respond to
        //
        $commentToRespond = $ch->getCommentToRespond( $param['parentid'] );
       
        // sets header to parent comment
        $responseHeader = ( isset( $commentToRespond->header )) ? $commentToRespond->header   : '';
        $responsComment = ( isset( $commentToRespond->parent )) ? $commentToRespond->parent   : '';
        
        
        $online = $user->isUserOnline();
        
        if ( $online === true ){
            $form = new  \Anax\CFormContact\CFormComment( $app, $user, $ch );
            $form->newComment( $_SESSION['user']['id'],  $param, $tags );
            
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
           
        } else {
            $content = "Logga in för att kommentera";
        }
        $this->app->views->add('me/article', ['header'=>$header, 'content' => $content], 'main');
        
        $this->app->views->add('me/article', ['header'=>$responseHeader, 'content' => $responsComment], 'main');
        
                
        
      
        
    }
    
    
    /**
     *  respondComment
     */
    public function respondComment( $app = null, $commentID = null ){
        
        
        $title = "Kommentera";
        $app->theme->setTitle($title);
        $app->theme->setVariable('gridColor', '');
        
        $header = "<h2>{$title}</h2>";
        
        $ch = null;
        $ch = new CommentHandler( $app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        
      
      
        //
        // fill $tags with all tags from db
        //
        $tags = $ch->fillTagsfromDb($app->db);
        
        //
        // fill $tags with all tags from db
        //
        $selectedTags = $ch->fillTagsfromDb($app->db,  $commentID);
        
        // get commentList
        $res = $ch->getCommentList( $commentID, 75, 'child' );
        $content = $this->formatChildComments($res);
       
        $header = ( isset( $res['data'][0]->header) ) ? "re: ".$res['data'][0]->header : '';
       
        
        // make form
        $this->addNewComment( $app, ['commentid'=>null, 'parentid'=>$commentID, 'tags'=>$tags, 'selectedTags'=>$selectedTags, 'header'=>$header] );
       
        //$this->updateComment( $app, $commentID, 'answer');
        // check if user is online
        $user = new \Anax\Users\User( $app );
        $user->isOnline();
       
        $this->app->views->add('default/article', ['header'=>$header, 'content' => $content], 'main-wide');
        
    }
    
    
    /**
     *  resetComments
     */
    protected function resetCommentTable( $app ){
        
      
        
        $dbModel = new \Anax\MVC\CDatabaseModel(  );
        $dbModel->createCommentTable( $app );
        
        $url = $app->url->create("kommentera");
        header("Location: " . $url);
    }
    
    /**
     *  prepareDatabase
     */  
    public function prepareDatabase($app = null, $type = 'user' ){
        
        $dbModel = new \Anax\MVC\CDatabaseModel(  );
        
        if ( $app && $type == 'user' ){
            // restore userTable
            $dbModel->restoreTable( $app );    
        } else if ( $type == 'comment') {
            // create commentTable
            $dbModel->createCommentTable( $app );
            
        }
        
        
        
        
    }
    /**
     *  showComment
     *  lists comment with answers below
     */
    public function showComment(  $commentID = null ){
        
        
        $title = "Kommentarer";
        
        $content = "<ul class='commentList'>";
        $row        = 0;
        $answerNr   = 0;
        $parentid   = null;
        $userid     = $this->user->getUserIDIndex();
        
        // get the object CommentHandler
        $ch = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        // get tags
        $CTagViews = new \Mango\Views\CTagViews( $this->app );
        $tags = $CTagViews->fillTagsfromDb( $this->app->db );
        $commentTags = $CTagViews->outputCheckboxes( $tags[0] );
        
        
        // get parent comment
        $tmp         = $ch->getGroupedComments($this->app->db, $commentID, 'parent');
        $answers        = $ch->getGroupedComments($this->app->db, $commentID, 'child');
        
       // $parent = $tmp[0];
      //  $childComments   = $ch->getChildToComment( $parent->parentid );
        
        
        
        // get formated childdata
       // $childComments[$parent[0]->commentid][] =  $this->formatChildComments( $parent, $parent[0]->commentid );
        
        $this->user->isOnline();
        $online = $this->user->isUserOnline();
        
        // errors
            $errorContent   = ( isset( $this->error['errorContent'] ) )  ? $this->error['errorContent']  : null;
            $errorMail      = ( isset( $this->error['errorMail'] ) )     ? $this->error['errorMail']     : null;
            $errorHomepage  = ( isset( $this->error['errorHomepage'] ) ) ? $this->error['errorHomepage'] : null;
            $errorName      = ( isset( $this->error['errorName'] ) )     ? $this->error['errorName']     : null;
           
          
           
            $header = "<h2>{$title}</h2>";
        $this->app->theme->setTitle($title);
        
       
        
        $ch->outputUpdateList([
                'all'       => $answers,
                'online'    => $online,
                'new'       => '',
                'userid'    => $userid[0],
                'errorContent'  => $errorContent,
                'errorMail'     => $errorMail,
                'errorHomepage' => $errorHomepage,
                'errorName'     => $errorName,
                'children'      => null,
                'header'        => $header,
                'group'         => null,
                
                ]); 
      
        
    }
    
    
    /**
     *  userComments
     */
    public function userComments( $app = null, $userid = null, $show = null ){
        
      
        if ( $app  ){
            
            $link = ( $userid) ? "anv/visa" : "anv/visa";
            
            // list users
            $this->user->getUsers($link, 'main-wide');
          
            // get selected user
            $selectedUser = $this->user->getUserName($userid, 'name' );
            
            $name = ( isset( $selectedUser->name ) ) ? " av ".$selectedUser->name : '...';
            
            // make object of commentControll
            $cc = null;
            $cc = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                        'errorName' => getError(3)) );
            
            $commentid = ( isset($this->param['id'] )) ? $this->param['id'] : null;
            
            // only output list with comments if a user is picked
            if ( $commentid ){
                
                // tell CommentControll to fetch all userComments
                $comments = $cc->fetchUserComments( $app->db, $userid );
                $cid = null;
                
                $content = "<h2>Inlägg i debatten{$name}</h2>"; 
                
                if ( count(  $comments ) > 0 ){
                    $content .= "\n<table class='commentList'>";
                    
                    // loop and output data
                    foreach( $comments as $comment ){
                        if( $comment->id == $comment->parentid && $cid != $comment->id ){
                            $url = $app->url->create("kommentar/visa/".$comment->id);
                        $content .= "\n\t<tr class='commentListRow'>".$this->createCommentRow([
                                                         'date'     => $comment->created,
                                                         'header'   => $comment->header,
                                                         
                                                         'answerNr' => $this->returnAnswers($comment->id, $cc),
                                                         'url'      => $url
                                                         ])."</tr>";
                        $cid = $comment->id;
                        }
                        
                    }
                    $content .= "</table>";
                } else {
                    $content .= "<p>Inga inlägg gjorda av vald användare...</p>";
                }
                
                $url = $this->app->url->create($link);
                $backUrl = "<a href='{$url}'>[Tillbaka]</a>";
                $content .= $backUrl;
                $header = "Användare";
                // view comments
                $app->views->add('default/article', ['header'=>$header, 'content' => $content], 'main-wide');
            }
         
        }
    }
    
    
    
}