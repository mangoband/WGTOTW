<!doctype html>
<html class='no-js' lang='<?=$lang?>'>
<head>
<meta charset='utf-8'/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?=$title . $title_append?></title>
<?php if(isset($favicon)): ?>   <link rel='icon' href='<?=$this->url->asset($favicon)?>'/><?php endif; ?>
<?php foreach($stylesheets as $stylesheet): echo "\n"; ?>
<link rel='stylesheet' type='text/css' href='<?=$this->url->asset($stylesheet)?>'/>
<?php endforeach; ?>
<?php if(isset($style)): ?><style><?=$style?></style><?php endif; ?>
<script src='<?=$this->url->asset($modernizr)?>'></script>
</head>

<body <?php if (isset( $bodyClass )) echo "class='".$bodyClass."'"; ?>>

<div id='wrapper'  <?php if (isset( $wrapperClass )) echo "class='".$wrapperClass."'"; ?>>

<header>
<div id='header'>
<?php if(isset($header)) echo $header?>
<?php $this->views->render('header')?>
</div>

<?php if( isset( $breadcrumb ) ){ echo $breacrumb; } ?>
<?php $this->views->render('breadcrumb')?>

</header>

<?php if ($this->views->hasContent('navbar')) : ?>
<div id='sitelogo'>
<?php $this->views->render('sitelogo')?>
</div>
<div id='navbar'>
<?php $this->views->render('navbar')?>
</div>
<?php endif; ?>

 <div id='content' class='bg'> 
<?php if( isset( $dumpa )) echo $dumpa; ?>
<!-- important content or picture -->
<?php if ($this->views->hasContent('flash')) : ?>
<div id='flash'  class='<?=getGridClass($gridColor)?>'><?php $this->views->render('flash')?></div>
<?php endif; ?>

<!-- three imortant subject..-->
<?php if ($this->views->hasContent('featured-1', 'featured-2', 'featured-3')) : ?>
<div id='wrap-featured'>
    <div id='featured-1' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('featured-1')?></div>
    <div id='featured-2' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('featured-2')?></div>
    <div id='featured-3' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('featured-3')?></div>
</div>
<?php endif; ?>

<!-- Wide Aria for most of the content-->
<?php if ($this->views->hasContent('main-wide') && ! $this->views->hasContent('sidebar')) : ?>
<div id='wrap-main-wide'>
    <div id='main-wide'  class='<?=getGridClass($gridColor)?>'><?php $this->views->render('main-wide')?></div>
    
</div>
<?php endif; ?>
<!-- Aria for most of the content-->
<?php if ($this->views->hasContent('main', 'sidebar')) : ?>
<div id='wrap-main'>
    <div id='main'  class='<?=getGridClass($gridColor)?>'><?php $this->views->render('main')?></div>
    <div id='sidebar'  class='<?=getGridClass($gridColor)?>'><?php $this->views->render('sidebar')?></div>
</div>
<?php endif; ?>
<!-- Aria for most of the content-->
<?php if ( ! $this->views->hasContent('main', 'sidebar')) : ?>
<div id='wrap-main'>
    <div id='main' ></div>
    <div id='sidebar'  ></div>
</div>
<?php endif; ?>

<?php if ($this->views->hasContent('triptych_1', 'triptych_2', 'triptych_3')) : ?>
<div id='wrap-triptych'>
    <div id='triptych-1' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('triptych_1')?></div>
    <div id='triptych-2' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('triptych_2')?></div>
    <div id='triptych-3' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('triptych_3')?></div>
</div>
<?php endif; ?>



<?php if ($this->views->hasContent('footer-col-1', 'footer-col-2', 'footer-col-3','footer-col-4')) : ?>
<div id='wrap-footer'>
    <div id='footer-col-1' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('footer-col-1')?></div>
    <div id='footer-col-2' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('footer-col-2')?></div>
    <div id='footer-col-3' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('footer-col-3')?></div>
    <div id='footer-col-4' class='<?=getGridClass($gridColor)?>'><?php $this->views->render('footer-col-4')?></div>
</div>
<?php endif; ?>
<div class='bottomLink'><a href='#navbar'>Till Toppen</a></div>
 </div> 
<div id='footer'>
<?php if(isset($footer)) echo $footer?>
<?php $this->views->render('footer')?>
</div>

</div>

<?php if(isset($jquery)):?><script src='<?=$this->url->asset($jquery)?>'></script><?php endif; ?>

<?php if(isset($javascript_include)): foreach($javascript_include as $val): ?>
<script src='<?=$this->url->asset($val)?>'></script>
<?php endforeach; endif; ?>

<?php if(isset($google_analytics)): ?>
<script>
  var _gaq=[['_setAccount','<?=$google_analytics?>'],['_trackPageview']];
  (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
  g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
  s.parentNode.insertBefore(g,s)}(document,'script'));
</script>
<?php endif; ?>

</body>
</html>
