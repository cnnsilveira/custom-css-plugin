<?php
/**
 * Plugin Name:         Custom CSS
 * Plugin URI:          https://caionunes.dev/
 * GitHub URI:          https://caionunes.dev/
 * Description:         Custom styles for your website on a optimized way.
 * Version:             1.0.0
 * Requires at least:   6.3
 * Requires PHP:        8.0
 * Author:              Caio Nunes
 * Author URI:          https://caionunes.dev/
 * Text Domain:         cndev-custom-css
 * Provides:            Custom CSS
 * 
 * This is an original plugin I developed with the goal of maximizing my clients' 
 * website performance, ensuring that no piece of code will delay page loading. 
 * The plugin allows for custom styles to be split into smaller files and 
 * correctly enqueued only on the desired pages or scopes.
 * 
 * Next step is to create an dynamic panel for the admin to manage these styles 
 * settings directly through the back-end panel, which will make non-coders 
 * life much easier.
 * 
 * Please, only make changes on the plugin code if you know what this is about.
 * Otherwise, contact me (Caio Nunes).
 * 
 * @package             Custom CSS
 * @author              Caio Nunes
 * @copyright           2024 - Caio Nunes
 */

if ( ! defined( 'CNDCCSS__FILES' ) ) {
	/**
	 * Files to be enqueued.
	 * 
	 * You may modify, add or remove files by modifying the array.
	 * 
	 * Available arguments for each file:
	 * - "path"    File path relative to the plugin root.
	 * - "deps"    File dependencies (optional).
	 * - "version" File version (optional).
	 * - "media"   File media type (optional).
	 * - "post"    Specific post (int), or collection of posts (array[ints]) which the file must be included (optional).
	 * - "scope"   Front-end using 'front', back-end using 'admin', or both using 'all'.
	 * 
	 * @since 1.0.0
	 */
	define( 
		'CNDCCSS__FILES',
		array(
			'example' => array(
				'path' => '/styles/example.min.css',
			)
		)
	);
}

if ( ! defined( 'CNDCCSS__URL' ) ) {
	/**
	 * Custom CSS plugin root directory.
	 * 
	 * @since 1.0.0
	 */
	define( 'CNDCCSS__URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CNDCCSS__PATH' ) ) {
	/**
	 * Custom CSS plugin root directory.
	 * 
	 * @since 1.0.0
	 */
	define( 'CNDCCSS__PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CNDCCSS__HOOK_PRIORITY' ) ) {
	/**
	 * Priority for the enqueue hooks.
	 * 
	 * @since 1.0.0
	 */
	define( 'CNDCCSS__HOOK_PRIORITY', PHP_INT_MAX );
}

if ( ! function_exists( 'cndev__valid_enqueue' ) ) {
	/**
	 * Tells if the current file is valid and 
	 * if the current page is suppose to 
	 * include the current style.
	 * 
	 * @param string $scope Front-end using 'front', back-end using 'admin', or both using 'all'.
	 * @param string $path  File path relative to the plugin root.
	 * @param mixed  $post  Post (int) or collection of posts (array of ints) where the style must be included.
	 * 
	 * @since 1.0.0
	 * 
	 * @return bool
	 */
	function cndev__valid_enqueue( string $scope, string $path, mixed $post ) {
		if ( ( 'front' === $scope && is_admin() ) || ( 'admin' === $scope && ! is_admin() ) ) {
			return false;
		}
		return file_exists( CNDCCSS__PATH . $path ) && ( ! $post || ( is_array( $post ) ? in_array( get_the_ID(), $post, true ) : $post === get_the_ID() ) );
	}
}

if ( ! function_exists( 'cndev__enqueues' ) ) {
	/**
	 * Includes the custom CSS for this project on a 
	 * more effective way, by enqueueing a minified version 
	 * instead of outputting it inline code via "Appearance".
	 * 
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	function cndev__enqueues() {
		/**
		 * Looping files.
		 */
		foreach ( CNDCCSS__FILES as $reference => $file_info ) {
			/**
			 * Verifies file information.
			 */
			$scope   = isset( $file_info['scope'] ) && in_array( $file_info['scope'], array( 'front', 'admin', 'all' ), true ) ? $file_info['scope'] : 'front';
			$path    = isset( $file_info['path'] ) ? $file_info['path'] : '';
			$deps    = isset( $file_info['deps'] ) ? $file_info['deps'] : array();
			$version = isset( $file_info['version'] ) ? $file_info['version'] : false;
			$media   = isset( $file_info['media'] ) ? $file_info['media'] : 'all';
			$post    = isset( $file_info['post'] ) ? $file_info['post'] : false;
			/**
			 * Checks whether the file should be enqueued.
			 */
			if ( cndev__valid_enqueue( $scope, $path, $post ) ) {
				/**
				 * Enqueues the file based on it's information.
				 */
				wp_enqueue_style( $reference, CNDCCSS__URL . $path, $deps, $version, $media );
			}
		}
	}
	/**
	 * Enqueue hooks.
	 */
	add_action( 'wp_enqueue_scripts', 'cndev__enqueues', CNDCCSS__HOOK_PRIORITY );
	add_action( 'admin_enqueue_scripts', 'cndev__enqueues', CNDCCSS__HOOK_PRIORITY );
}
