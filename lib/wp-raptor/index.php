<?php
/*
Plugin Name: Raptor Editor
Plugin URI: http://pagesofinterest.net/projects/wp-raptor/
Description: Edit your content in style with Raptor Editor, this generation's WYSIWYG editor.
Version: 1.0.20
Author: Michael Robinson
Author URI: http://pagesofinterest.net/
License: http://www.gnu.org/licenses/gpl.html
*/

define('RAPTOR_ROOT', dirname(__FILE__));

include RAPTOR_ROOT.'/include.php';

$raptor = new RaptorInitialiser();
