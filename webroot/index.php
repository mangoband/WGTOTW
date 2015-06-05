<?php
/**
 *  Frontcontroller
 *
 */
require __DIR__.'/config_with_app.php';
require ANAX_APP_PATH .'Source/CSource.php';

use \Michelf\MarkdownExtra;
use \Mos\Source\CSource ;
use \Mango;
use \Mos\HTMLForm;


function markdown($text) {
  require_once( ANAX_3pp . 'php-markdown/Michelf/Markdown.php');
  require_once( ANAX_3pp . 'php-markdown/Michelf/MarkdownExtra.php');
  return MarkdownExtra::defaultTransform($text);
}
// Create services and inject into the app. 
$di  = new \Anax\DI\CDIFactoryMango();

$app = new \Anax\MVC\CApplicationBasic($di);

$app->theme->setTitle("WGTOTW");

$app->url->setUrlType(\Anax\Url\CUrl::URL_APPEND);


// settings for project
$app->theme->configure(ANAX_APP_PATH . 'config/theme-project.php');

// get adminmenu or normal menu

$app->navbar->configure(ANAX_APP_PATH . 'config/navbar_project.php');



$app->router->add('*', function() use ( $app ) {
   
session_name('kmom4');
//session_start();
  
  $CViewController = new Mango\Views\CViewController( $app );
  $CViewController->viewContent();

  
});


$app->router->add('source', function() use ($app) {
    $app->theme->setTitle("Källkod");
    $app->theme->addStylesheet('css/source.css');
    $app->theme->setVariable('gridColor', '');
    $app->views->add('me/breadcrumb', [], 'breadcrumb');
    $source = new \Mos\Source\CSource([
            'secure_dir' => '..', 
            'base_dir' => '..', 
            'add_ignore' => ['.htaccess'],
        ]);
    $content = $source->View();
    $app->views->add( 'me/source', [
            'content' => $content,
           
        ]);
});



$app->router->handle();
$app->theme->render();
