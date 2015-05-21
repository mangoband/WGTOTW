<div class='comment-form'>
    <form method=post>
        <input type=hidden name="redirect" value="<?=$this->url->create('')?>">
        
        <fieldset>
        <legend>Update a comment</legend>
        <p><label>Comment:<br/><textarea name='content'><?=$content?></textarea></label></p>
        <p class=buttons>
            <input type='text' name='id' value='<?= $id?>' readonly />
            
            <input type='submit' name='makeUpdate' value='Uppdatera' onClick="this.form.action = '<?=$this->url->create('comment/makeUpdate')?>'"/>
            
        </p>
        <output><?=$output?></output>
        </fieldset>
    </form>
</div>