<?php
/**
 * @package FOFOGutenToggler
 */
/*
Plugin Name: Foxdell Folio Block Editor feature toggler
Plugin URI: 
Description: Provide a set of hooks which will allow features gutenberg features to be turned off
Version: 1.0.0
Author: Foxdell Folio
Author URI: 
License: GPLv2 or later
Text Domain: fofogutentog
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    To remove the feature toggler settings from the dashboard completely
    set the FOFO_GUTENTOG_SHOW_SETTINGS value to false
*/
if( !defined( 'FOFO_GUTENTOG_SHOW_SETTINGS' ) ) {
    define( 'FOFO_GUTENTOG_SHOW_SETTINGS', true );
}

/**
 * Include the class file with core functionality & which calls any library functions
 */
include_once plugin_dir_path( __FILE__ ).'includes/class-fofo-gutentog.php';

/**
 * Bootstrap the pluugin
 * 
 * Creates an instance of the core class and initialises the plugin
 * 
 * @since 1.0.0
 */
function fofo_gutentog_start() {

    $fofogutentog = new \FoFoGutenTog\FoFo_GutenTog();
    $fofogutentog->attach();
}

fofo_gutentog_start();


