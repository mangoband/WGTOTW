
<h2 class='commentHead' id='comments'>Kommentarer</h2>

<?php if (is_array($comments)) : ?>
<div class='comments'>
    <form method=post >
<?php foreach ($comments as $id => $comment) : ?>

<div class="comment <?php echo isParent( $comment->parentid, $comment->id ) ?>">
<input type='hidden' name='commentId[<?= $comment->id?>][]' id='<?= $comment->id ?>' value='<?= $comment->id ?>' readonly />
<input type='hidden' name='page' value='<?= $this->di->request->getCurrentUrl() ?>' />
<h4 class='comment_id' <?= $new?>><?php
if ( isset( $header )) { echo $comment->header; }

?></h4>

<div class="commentInfo <?php echo isParent( $comment->parentid, $comment->id ) ?>">    
<span class='commentContent <?php echo isParent( $comment->parentid, $comment->id ) ?>'><?=markdown($comment->comment)?></span>
<p class='commentRespond'><?php
if ( isset( $children[$comment->id][0] ) ) {
 
 echo $children[$comment->id][0];
 
} else { echo '&nbsp;'; }
   ?></p>

</div>

<div class="commentUser">
    
    <p><?=$comment->name?></p>
    
    <p><?= $comment->created;?></p>
    
<?php if ( isset( $online ) && $online == 'online'  ){ ?>
<input type='submit' name='doCommentSave' id='<?= $comment->id ?>' value='svara' title='svara' onclick='form.action="<?=$this->url->create('kommentar/svara/'.$comment->id)?>"'  title='<?=$comment->id?>'/>
<?php if ( isset( $userid ) && ($userid == 1 || $userid == 2 || $userid == $comment->userid) ){ ?>
    <input type='submit' name='doCommentDelete' id='<?= $comment->id ?>' value='Ta bort' title='Ta bort' onclick='form.action="<?=$this->url->create('kommentar/radera/'.$comment->id)?>"'  title='<?=$comment->id?>'/>
    <input type='submit' name='doCommentUpdate' id='<?= $comment->id ?>' value='Uppdatera' title='Uppdatera' onclick='form.action="<?=$this->url->create('kommentar/uppdatera/'.$comment->id)?>"' title='<?=$comment->id?>' />
<?php }?>
    
    <?php }?>
</div>

</div>
<?php endforeach; ?>
</form>
</div>
<?php endif; ?>