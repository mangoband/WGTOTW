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
<input type='hidden' name='commentId[<?= $comment->id?>][]' id='<?= $comment->id ?>' value='<?= $comment->id ?>' readonly />
<input type='hidden' name='page' value='<?= $this->di->request->getCurrentUrl() ?>' />
<h4 class='comment_id' <?= $new?>><?php
if ( isset( $header )) { echo $comment->header; }

?></h4>
<div class="commentUser">
    
    <p><?=$comment->name?></p>
    
    <p><?= $comment->created;?></p>
    
<?php if ( isset( $online ) && $online == 'online'  ){ ?>
<input type='submit' name='doCommentSave' id='<?= $comment->commentid ?>' value='svara' title='<?= $comment->commentid ?>'  onclick='form.action="<?=$this->url->create('kommentar/svara/'.$comment->commentid)?>"' />
<?php if ( isset( $userid ) && ($userid == 1 || $userid == 2 || $userid == $comment->userid) ){ ?>
    <input type='submit' name='doCommentUpdate' id='<?= $comment->commentid ?>' value='Uppdatera' title='Uppdatera' onclick='form.action="<?=$this->url->create('kommentar/uppdatera/'.$comment->commentid)?>"'  />
    <input type='submit' name='doCommentDelete' id='<?= $comment->commentid ?>' value='Ta bort' title='Ta bort' onclick='form.action="<?=$this->url->create('kommentar/radera/'.$comment->commentid)?>"'  />
    
<?php } ?>
    
    <?php }?>
</div>
<div class="commentInfo <?php echo isParent( $comment->parentid, $comment->id ) ?>">    
<span class='commentContent <?php echo isParent( $comment->parentid, $comment->id ) ?>'><?=markdown($comment->comment)?></span>
<span class='commentTags'><?php
if ( isset( $tags[$comment->commentid] )){ echo $tags[$comment->commentid];  } 
?></span>
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