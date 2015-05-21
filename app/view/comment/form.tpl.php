<div class='comment-form'>
    <form method=post>
        <input type=hidden name="redirect" value="<?=$this->di->request->getCurrentUrl()?>">
        <input type=hidden name="group" value="<?=$group?>">
        <input type=hidden name="tmp" value="<?=$tmp?>">
        <fieldset>
        <legend>Lämna en kommentar</legend>
        <p><label>Kommentar: *<br/><textarea name='content'><?=$content?></textarea></label><span> <?=$errorContent?></span></p>
        <p><label>namn: *<br/><input type='text' name='name' value='<?=$name?>'/></label><span> <?=$errorName?></span></p>
        <p><label>Hemsida:<br/><input type='text' name='web' value='<?=$web?>'/></label> <span> <?=$errorHomepage?></span></p>
        <p><label>E-post: *<br/><input type='text' name='mail' value='<?=$mail?>'/></label><span> <?=$errorMail?></span></p>
        <p class=buttons>
            <input type='submit' name='doCreate' value='Skicka' onClick="this.form.action = '<?=$this->url->create('comment/add')?>'"/>
            <input type='reset' value='Reset'/>
            <input type='submit' name='doRemoveAll' value='Radera alla' onClick="this.form.action = '<?=$this->url->create('comment/remove-all')?>'"/>
            <input type='submit' name='doRemoveGroup' value='Radera grupp' onClick="this.form.action = '<?=$this->url->create('comment/remove-group')?>'"/>
        </p>
        <output><?=$output?></output>
        </fieldset>
    </form>
</div>
