<?php
    $tmp  =  \Anax\Users\User::getUserID();
    $id = $tmp[0];
    $acronym = $tmp[1];
    // imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 $gravar = ( isset( $email ) )?  get_gravatar( $email, 50, 'monsterid' ): get_gravatar( null, 50, 'mm' );
 $link = ( is_null( $id ) ) ? $this->url->create() : $this->url->create("profil/show/id/{$id}/{$acronym}");
    $btn = ( isset( $btn ) ) ? "<div class='loggoutbtn'>{$btn}</div>" : null;
?>
<span id='timeOfDay'
<?php if (isset( $gridColor ) && $gridColor != '' ){ echo " class='=".$gridColor."'"; }?>>
<?=$btn?>
<?= $timeOfDay?>
<?php if( isset($icon) ) { echo " <i class='fa ".$icon."'></i>"; } ?><br /><?=$acronym?>


<a href="<?=$link?>" class='gravatarlink'>
<img src='<?= $gravar ?>' alt='gravatar' title='gravatar' class='gravatar' />
</a>

</span>


