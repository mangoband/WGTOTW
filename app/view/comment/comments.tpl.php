<hr>

<h2 class='commentHead' id='comments'>Kommentarer</h2>

<?php if (is_array($comments)) : ?>
<div class='comments'>
    <form method=post>
<?php foreach ($comments as $id => $comment) : ?>
<span class="comment">

<h4 class='comment_id' <?= $new?>>Kommentar #<?=$id?></h4>

<span class="commentInfo">    
<p class='commentContent'><?=$comment['content']?></p>
</span>
<span class="commentUser">
<p>Namn: <?=$comment['name']?></p>
<p>Hemsida: <?=$comment['web']?></p>
<p>Epost: <?=$comment['mail']?></p>
<p>IP: <?=$comment['ip']?></p>

<p><?=date("Y-m-d H:i:s", $comment['timestamp']);?></p>

<input type='hidden' name='id' value='<?= $comment['timestamp'] ?>' readonly />
<input type='hidden' name='group' value='<?= $comment['group'] ?>' />
<input type='hidden' name='page' value='<?= $this->di->request->getCurrentUrl() ?>' />
<input type='submit' name='doDelete' value='Ta bort' onClick="this.form.action = '<?=$this->url->create('comment/delete/'.$comment['timestamp'].'')?>'"/>
<input type='submit' name='doUpdate' value='Uppdatera' onClick="this.form.action = '<?=$this->url->create('comment/update/'.$comment['timestamp'].'')?>'"/>
</span>
</span>
<?php endforeach; ?>
</form>
</div>
<?php endif; ?>