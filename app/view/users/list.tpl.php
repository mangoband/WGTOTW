<?php if ( isset( $header ) ) { ?><h3><?=$header?></h3><?php }
$class = ( isset( $position) ) ? "name{$position}": ''; ?>
<ul class='users <?=$class?>'><?=$content?></ul>
    