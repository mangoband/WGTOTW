<article>
    


<?php if (is_array($comments)) { ?>

<ul class='comment_holder'>
    <li><h2 class='commentHead' id='comments'><?=$sectionheader?></h2></li>
    <?php foreach ($comments as $id => $comment) { ?>
    <?php if ( $comment->header != $parentHeader ){ ?>
        <li class='top'>
            <input type='hidden' name='commentId[<?= $comment->commentid?>][]' id='<?= $comment->commentid ?>' value='<?= $comment->commentid ?>'  />
            <input type='hidden' name='page' value='<?= $this->di->request->getCurrentUrl() ?>' />
<h2 class='comment_id' <?= $new?>><?php


if ( isset( $header )) { echo $comment->header; } ?></h2>
<div class='commentAnswerList'>
<?php if ( isset( $online ) && $online == 'online'  ){
    $url_answer = $this->url->create('kommentar/svara/'.$comment->commentid);
    $url_del    = $this->url->create('kommentar/radera/'.$comment->commentid);
    $url_update = $this->url->create('kommentar/uppdatera/'.$comment->commentid);
?>
<a href='<?=$url_answer?>' class='respondBtn' title='Svara'>Besvara</a>

<?php if ( isset( $userid ) && ($userid == 1 || $userid == 2 || $userid == $comment->childid) ){ ?>
<a href='<?=$url_update?>' class='respondBtn' title='Uppdatera'>Uppdatera</a>
<a href='<?=$url_del?>' class='respondBtn' title='radera'>Radera</a>

<?php }}  ?>
</div> 
<span class='commentUserList parentComment'>
    <?= $comment->created;?>, <?=$comment->name?> 
</span>


        </li>
        <li>
            <?=markdown($comment->comment)?>
            
        </li>
        <li class='viewTags'>
            <?php
if ( isset( $tags[$comment->commentid] )){ echo $tags[$comment->commentid];  } 
?>
        </li>
        
        <?php dump($children);
if ( isset( $children[$comment->commentid][0] ) ) { dump($children); ?>
<li>
    
</li>
<?php } ?>
        





<?php } } ?>

</ul>
</article>
<?php  }; ?>