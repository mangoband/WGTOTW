<?=getImg($this->url->create(), $this->url->asset("img/people.png"), 'siteLogo')?>
<?php $btn = ( isset( $btn ) ) ? "<div class='loggoutbtn'>{$btn}</div>" : null; ?>
<span id='sitedescription'>
<ul>
    <li class='siteslogan'><?=getImg($this->url->create(), $this->url->asset("img/We_Gonna_Take_Over_The_World.png"), 'header_img')?></li>
    <li class='header_text'><?=getName()?></li>
    <li class='header_text'><?= date('G : i')?>
<?php if( isset($icon) ) { echo " <i class='fa ".$icon."'></i>"; } ?></li>
    <li class='sitegravatar'><a href='<?=getProfileLink( $this )?>'><img src='<?=getGravatarLink( getEmailFromHeader() )?>' alt='gravatar' title='gravatar' /></a></li>
    <li class='sitetitle'><?= getLoginBtn( $this->url->create(), $this->url->asset("img/") , 'header_img')?></li>
    
    
</ul>

</span>