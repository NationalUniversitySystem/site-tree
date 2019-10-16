<?php
/**
 * Plugin Name: Site Tree
 * Description: Display website tree for providing as a PDF print out.
 * Version: 1.0.0
 * Author: Chris Maust / Mike Estrada
 *
 * @package site-tree
 */

if ( ! defined( 'WPINC' ) ) {
	die( 'YOU! SHALL NOT! PASS!' );
}

define( 'SITE_TREE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SITE_TREE_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( SITE_TREE_PATH . 'inc/class-page-templater.php' ) ) {
	require SITE_TREE_PATH . 'inc/class-page-templater.php';

	if ( class_exists( 'Page_Templater' ) && method_exists( 'Page_Templater', 'singleton' ) ) {
		add_action( 'plugins_loaded', [ 'Page_Templater', 'singleton' ] );

		if ( method_exists( 'Page_Templater', 'activate' ) ) {
			register_activation_hook( __FILE__, [ 'Page_Templater', 'activate' ] );
		}
	}
}
