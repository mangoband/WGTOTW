<?php
require __DIR__.'/config_with_app.php';
// Create services and inject into the app. 
$di  = new \Anax\DI\CDIFactoryMango();

$app = new \Anax\MVC\CApplicationBasic($di);

$url = $app->url->create('reset-user');
$url2 = $app->url->create('reset-kommentarer');

$currentUrl = $app->request->getCurrentUrl();

if ( $app->url->getUrlType() != 'clean' ){
    $pageUrl = explode( 'webroot/firstTime.php/', $currentUrl); 
} else {
    $pageUrl = explode( 'webroot/', $currentUrl); 
}

if ( !isset( $pageUrl[1])){
    $site = 'home';
} else {
    $site = $pageUrl[1];
}

$tmp = explode( '/', $site );

$page     = ( isset( $tmp[0] ) ) ? $tmp[0] : null;

$CViewsComments = new Mango\Views\CViewsComments( $app );
$user = null;
$comment = null;

if ( $page == 'reset-user'){
    $CViewsComments->prepareDatabase( $app, 'user');
    $user = '.......... Skapar databas';
    
} else if ( $page == 'reset-kommentarer'){
    $CViewsComments->prepareDatabase( $app, 'comments');
    $comment = '.......... Skapar databas<br />';
}

?>
<html>
    <title>WGTOTW</title>
    <body>
        <h1>Skapa databas</h1>
        <p><a href='<?=$url?>'>Skapa/Återställ tabell för användare</a></p>
        <p><?=$user?></p>
        <p><a href='<?=$url2?>'>Skapa/Återställ tabell för kommentarer</a></p>
        <p><?=$comment?></p>
    </body>
</html>