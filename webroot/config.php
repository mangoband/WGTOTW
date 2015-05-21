<?php
/**
 * Sample configuration file for Anax webroot.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly

/**
 * Define essential Anax paths, end with /
 *
 */
define('ANAX_INSTALL_PATH', realpath(__DIR__ . '/../') . '/');
define('ANAX_APP_PATH',     ANAX_INSTALL_PATH . 'app/');

define('ANAX_3pp', ANAX_INSTALL_PATH . '3pp/');




/**
 * Include autoloader.
 *
 */
include(ANAX_APP_PATH . 'config/autoloader.php'); 


/**
 * Include global functions.
 *
 */
include(ANAX_INSTALL_PATH . 'src/functions.php'); 


