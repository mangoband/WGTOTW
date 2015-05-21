<article class="article1">
 
<?=$content?>



<?php if(isset($byline)) : ?>
<footer class="byline">
<?=$byline?>
</footer>
<?php endif; ?>
 
</article>
<?php if(isset($right)) : ?>
<span class="rightside">
    <?=$right?>
</span>
<?php endif; ?>