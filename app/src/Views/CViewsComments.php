<?php

namespace Mango\Views;

class CViewsComments  {
    
    private $app = null;
    private $user = null;
    public $param  = null;
    
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
        $CTagViews = new \Mango\Views\CTagViews( $this->app, $this->param, true );
      
    }
    
    /**
     *  doAction
     *  depending on the currentUrl makes this different actions
     */  
    public function doAction(){
        
        if( isset( $this->param['verbose'] ) && $this->param['verbose'] == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." ( option: ".$this->param['option']." ) function called by ". $callers[1]['function']);
        }
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
        
      
       if( isset( $this->param['verbose'] ) && $this->param['verbose'] == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
        }
        
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
    public function viewListWithComments( $param = null, $data = null, $isTag = false, $tagid = null, $popular = null ){
        
       $callers=debug_backtrace();
       if( isset( $this->param['verbose'] ) && $this->param['verbose'] == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
        }
       
        // view tags from db ( CDatabaseModel)
        if ( is_null( $popular) ){
            $this->viewPopularTags( $tagid);    
        }
        
        
        // make object of commentControll
        $cc = null;
        $cc = new CommentHandler( $this->app, array('errorContent'=>getError(0), 'errorMail'=>getError(1), 'errorHomepage'=>getError(2),
                                    'errorName' => getError(3)) );
        
        // fetch data from database
        
        $comments = (  $isTag == true ) ? $data :$cc->getGroupedComments($this->app->db, null, 'parent');
        
    //    echo ( $isTag == true  ) ? dump(__LINE__." data ".$callers[1]['function']) : dump(__LINE__." getGroupedComments ".$callers[1]['function']);
            
        // define content & header
        $title = "Svar";
        $header = "<h2>{$title}</h2>";
        if ( $comments ){
           $content = "\n<table class='commentList'>";
        
            // declare th
            $content .= "<tr><th class='commentAnswerNr'>Svar</th><th class='commentHeader'>Inlägg</th><th>Datum</th></tr>";
            $parentID = null;
            
            $totalAnswers = count( $comments ) - 1;
            
           
            // fill td in table
            foreach( $comments as $comment ){
              
              
                $answers = ( isset($comment->answers ) ) ? $comment->answers : $this->returnAnswers($comment->parentid, $cc);
                $answers = $this->returnAnswers($comment->parentid, $cc); 
           //     echo ( isset($comment->answers ) ) ? dump(__LINE__." comment->answers ".$callers[1]['function']) : dump(__LINE__." returnAnswers ".$callers[1]['function']);
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
            
            
            
        } else{
            $content = "Inga inlägg gjorda under tagg";
        }
        
        // view comments
        $this->app->views->add('default/article', ['header'=>$header, 'content' => $content], 'main-wide');
       
        // set pagetitle
        setPageTitle( 'Frågor', $this->app);
        
        
     
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
        
        if( isset( $this->param['verbose'] ) && $this->param['verbose'] == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
        }
        
        // make tags as links
        function makeATag( $id = null, $name = null, $app = null ){
            
                $a = '';
                if( $name && $id ){
                    foreach( $name as $key => $value ){

                        $url = $app->url->create("taggar/visa/{$id[$key]}");
                        $a .= "<a href='$url' class='tag'>{$name[$key]}</a>";
                    }       
                }
            
            return $a;
            
            
        }
       
        // collect data from array $p
        $header     = ( isset( $p->header ) ) ? $p->header  : '';
        $content    = ( isset( $p->comment) ) ? $p->comment : '';
        $id         = ( isset( $p->parentid ) ) ? $p->parentid      : '';
        $userid     = ( isset( $p->userid ) ) ? $p->userid  : '';
        $user       = ( isset( $p->name ) )   ? $p->name    : '';
        $date       = ( isset( $p->date ) )   ? $p->date    : '';
        $row        = ( isset( $p->row ) )    ? $p->row     : '';
        $tags       = ( isset( $p->tag ) )   ? explode(',', $p->tag)    : null;
        $tagid       = ( isset( $p->tagid ) )   ? explode(',',$p->tagid)    : null;
        $answers    = ( isset( $p->answers))  ? $p->answers : '';
        
        $url = $this->app->url->create("kommentar/visa/".$id);
        $url2 = $this->app->url->create("kommentar/svara/".$id);
        
        $html = '';
       if ( $row == 0 ){
            $html .= "\n\t<li>\n\t<h2>\n\t{$header}</h2></li>";
        }
        $html .= "\n\t<li class='comment'>".markdown($content)."</li>";
        if ( $row == 0 ){
            $html .= "\n\t<li class='viewTags'>".makeATag( $tagid, $tags, $this->app )."<input type='hidden' value='{$id}' name='view[{$id}]' /></li>";
            $html .= "\n\t<li><span class='commentAnswerList'><a href='{$url2}' class='respondBtn'>Besvara</a>  </span><span class='commentUserList parentComment'>{$user}</li>";
        }
        return $html;
    }
    
    /**
     *  commentActionWidthDb
     */
    public function commentActionWithDb( $app, $currentUrl ){
       
        if( isset( $this->param['verbose'] ) && $this->param['verbose'] == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
        }
        
        try{
       
        $title = "Kommentera";
        $app->theme->setTitle($title);
       
        // set pagetitle
        setPageTitle( $title, $this->app);
        
        $app->MangoFlash->get('notice');
        
        //$user = new \Anax\Users\User( $app );
        $this->user->isOnline();
        $online = $this->user->isUserOnline();
        
        
        
        $ch = new CommentHandler( $app,['errorContent'=>getError(0), 'errorMail'=>getError(1),
                                        'errorHomepage'=>getError(2),'errorName' => getError(3)]);
        
        $allUserComments = $ch->fetchUserComments( $this->app->db);
        $comments = $ch->getAllCommentData();
        
        // get tags
        $CTagViews = new \Mango\Views\CTagViews( $this->app );
        
        $tags = $CTagViews->fillTagsfromDb( $this->app->db );
        $commentTags = $CTagViews->outputCheckboxes( $tags[0] );
        dump(__METHOD__.__LINE__);
        dump( $commentTags);
        // make content to updateform
        
        foreach( $comments as $comment ){
          
            $tmpData = $ch->getChildToComment( $comment->parentid );
        
        
            // get formated childdata
            $childComments[$comment->commentid][] = $this->formatChildComments( $tmpData, $comment->parentid );
            $parentHeader = ( isset( $parent[0]->header ) ) ? $parent[0]->header : null;
      
        }
        
        $ch->outputUpdateList([
            'all'       => $allUserComments,
            'online'    => $online,
            'new'       => '',
            'userid'    => $comment->user_id,
            'errorContent'  => getError(0),
            'errorMail'     => getError(1),
            'errorHomepage' => getError(2),
            'errorName'     => getError(4),
            'children'      => $childComments,
            'header'        => $comment->header,
            'group'         => null,
            'tags'          => $commentTags,
            'sectionheader' => null,
            'parentHeader'  => $parentHeader
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
        
    
        $title = ( is_null( $parentID ) )? "Ställ fråga" : 'Svara';
        // set pagetitle
        setPageTitle( $title, $this->app);
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
        $this->app->views->add('me/article', ['header'=>null, 'content' => $content], 'main');
        
        $this->app->views->add('me/article', ['header'=> $param['header'], 'content' => $param['comment']], 'main');
        
                
        
      
        
    }
    
    
    /**
     *  respondComment
     */
    public function respondComment( $app = null, $commentID = null ){
        
        
        if( isset( $this->param['verbose'] ) && $this->param['verbose'] == true ){
            $callers=debug_backtrace();
            dump( "rad: ".__LINE__. " ".__METHOD__." function called by ". $callers[1]['function']);
        }
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
       
      //  $header = ( isset( $res['data'][0]->header) ) ? $res['data'][0]->header : '';
        $header = ( isset( $res['data'][0]->cheader) ) ? $res['data'][0]->cheader : '';
        $comment = ( isset( $res['data'][0]->comment ) ) ? $res['data'][0]->comment : '';
       
        
        // make form
        $this->addNewComment( $app, ['commentid'=>null, 'parentid'=>$commentID, 'tags'=>$tags,
                                     'selectedTags'=>$selectedTags, 'responseHeader'=>$header,'header'=>'re: '.$header, 'comment' =>$comment] );
       
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
        
        
        $title = "Konversationer";
        
        // set pagetitle
        setPageTitle( $title, $this->app);
        
        
        
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
        $parent         = $ch->getGroupedComments($this->app->db, $commentID, 'parent');
        $answers        = $ch->getGroupedComments($this->app->db, $commentID, 'child');
        
        $parentHeader = ( isset( $parent[0]->header ) ) ? $parent[0]->header : null;
        
        $header = "<h2>{$title}</h2>";
        $this->app->theme->setTitle($title);
        
        $parentTags = $CTagViews->getTagForComment( $parent );
        
        $htmlParent = $this->createCommentStructure( $parent[0] );
        $this->app->views->add('default/article', ['header'=>null, 'content' => "<ul>{$htmlParent}</ul>"], 'main');
       
        
        $commentTags = $CTagViews->getTagForComment( $answers );
        
        
        // get formated childdata
       // $childComments[$parent[0]->commentid][] =  $this->formatChildComments( $parent, $parent[0]->commentid );
        
        $this->user->isOnline();
        $online = $this->user->isUserOnline();
        
          
        
        $ch->outputUpdateList([
                'all'       => $answers,
                'online'    => $online,
                'new'       => '',
                'userid'    => $userid[0],
                'errorContent'  => getError(0),
                'errorMail'     => getError(1),
                'errorHomepage' => getError(2),
                'errorName'     => getError(4),
                'children'      => null,
                'header'        => $header,
                'group'         => null,
                'tags'          => $commentTags,
                'sectionheader' => 'Svar',
                'parentHeader'  => $parentHeader
                ]); 
      
        
    }
    
    
    /**
     *  userComments
     */
    public function userComments( $app = null, $userid = null, $show = null ){
        
      
        if ( $app  ){
            // set pagetitle
            setPageTitle( 'Användare', $app);
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