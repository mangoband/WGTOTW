<article>
<h2 class='commentHead' id='comments'><?=$sectionheader?></h2>

<?php if (is_array($comments)) : ?>

<div class='comments'>
    <form method=post >
<?php foreach ($comments as $id => $comment) { ?>
<?php

    if ( $comment->header != $parentHeader ){
?>
<div class="comment <?php echo isParent( $comment->parentid, $comment->id ) ?>">
<input type='hidden' name='commentId[<?= $comment->id?>][]' id='<?= $comment->id ?>' value='<?= $comment->id ?>'  />
<input type='hidden' name='page' value='<?= $this->di->request->getCurrentUrl() ?>' />
<h4 class='comment_id' <?= $new?>><?php
if ( isset( $header )) { echo $comment->header; }

?></h4>
<div class="commentUser">
    
    <p><?=$comment->name?>, <?= $comment->created;?></p>
    
<?php if ( isset( $online ) && $online == 'online'  ){
    $url_answer = $this->url->create('kommentar/svara/'.$comment->commentid);
    $url_del    = $this->url->create('kommentar/radera/'.$comment->commentid);
    $url_update = $this->url->create('kommentar/uppdatera/'.$comment->commentid);
?>
<a href='<?=$url_answer?>' class='tag' title='Svara'>Svara</a>

<?php if ( isset( $userid ) && ($userid == 1 || $userid == 2 || $userid == $comment->userid) ){ ?>
<a href='<?=$url_update?>' class='tag' title='Uppdatera'>Uppdatera</a>
<a href='<?=$url_del?>' class='tag' title='radera'>Radera</a>
    
<?php } ?>
    
    <?php }?>
</div>
<div class="commentInfo <?php echo isParent( $comment->parentid, $comment->id ) ?>">    
<div class='commentContent <?php echo isParent( $comment->parentid, $comment->id ) ?>'><?=markdown($comment->comment)?></div>
<div class='commentTags'><?php
if ( isset( $tags[$comment->commentid] )){ echo $tags[$comment->commentid];  } 
?></div>
<p class='commentRespond'><?php
if ( isset( $children[$comment->id][0] ) ) {
 
 echo $children[$comment->id][0];
 
} else { echo '&nbsp;'; } 
   ?></p>

</div>



</div>
<?php } } ?>
</form>
</div>
</article>
<?php endif; ?>