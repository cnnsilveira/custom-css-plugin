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

if ( ! defined( 'CNDCCSS__MIN' ) ) {
	/**
	 * Whether to search for minified version of files.
	 * 
	 * @since 1.0.0
	 */
	define( 'CNDCCSS__MIN', false );
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

if ( ! function_exists( 'cndev__styles_post_type' ) ) {
	/**
	 * Generates the stylesheet post-type.
	 */
	function cndev__styles_post_type() {
		$args   = array(
			'description'        => 'Portfolio stylesheets.',
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-admin-customizer',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'stylesheet' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'show_in_rest'       => false,
			'labels'             => array(
				'name'                  => __( 'Stylesheets', 'cndev' ),
				'singular_name'         => __( 'Stylesheet', 'cndev' ),
				'menu_name'             => __( 'Custom CSS', 'cndev' ),
				'name_admin_bar'        => __( 'Custom CSS', 'cndev' ),
				'add_new'               => __( 'Add New', 'cndev' ),
				'add_new_item'          => __( 'Add New Stylesheet', 'cndev' ),
				'new_item'              => __( 'New stylesheet', 'cndev' ),
				'edit_item'             => __( 'Edit stylesheet', 'cndev' ),
				'view_item'             => __( 'View stylesheet', 'cndev' ),
				'all_items'             => __( 'All stylesheets', 'cndev' ),
				'search_items'          => __( 'Search stylesheets', 'cndev' ),
				'parent_item_colon'     => __( 'Parent stylesheets:', 'cndev' ),
				'not_found'             => __( 'No stylesheet found.', 'cndev' ),
				'not_found_in_trash'    => __( 'No stylesheet found in Trash.', 'cndev' ),
				'featured_image'        => __( 'Stylesheet thumbnail', 'cndev' ),
				'set_featured_image'    => __( 'Set image', 'cndev' ),
				'remove_featured_image' => __( 'Remove image', 'cndev' ),
				'use_featured_image'    => __( 'Use as image', 'cndev' ),
				'archives'              => __( 'Stylesheet archives', 'cndev' ),
				'insert_into_item'      => __( 'Insert into stylesheet', 'cndev' ),
				'uploaded_to_this_item' => __( 'Uploaded to this stylesheet', 'cndev' ),
				'filter_items_list'     => __( 'Filter stylesheets list', 'cndev' ),
				'items_list_navigation' => __( 'Stylesheets list navigation', 'cndev' ),
				'items_list'            => __( 'stylesheets list', 'cndev' ),
			),
		);
		register_post_type( 'cndev__stylesheets', $args );
	}
	add_action( 'init', 'cndev__styles_post_type' );
}

/**
 * Add meta-boxes
 * 
 * Reference (name/title);
 * Path (upload file);
 * 
 */

function cndev__create_meta_box() {

}




/*
if ( ! function_exists( 'cndev__get_styles' ) ) {
	function cndev__get_styles() {
		$styles             = array_diff( scandir( CNDCCSS__PATH . 'styles' ), array( '..', '.' ) );
		$styles_to_enqueue  = array();

		// echo '<div style="background: #fff; width: 100dvw; min-height: 100dvh; position: absolute; left: 0; top: 0; z-index: 999999999999999999999999"><pre>';

		foreach ( $styles as $style ) {
			if ( str_ends_with( $style, '.css' ) ) {
				$style_path = cndev__get_path( $styles, $style );
				if ( ! in_array( $style_path, $styles_to_enqueue, true ) ) {
					$styles_to_enqueue[] = $style_path;
				}
			}
		}
		return $styles_to_enqueue;
		// echo '</pre></div>';
	}
}

if ( ! function_exists( 'cndev__get_path' ) ) {
	function cndev__get_path( array $styles, string $style ) {
		$style = str_replace( '.min', '', $style );
		$style_name = explode( '.', $style, 2 )[0];
	
		return CNDCCSS__URL . 'styles/' . CNDCCSS__MIN && in_array( $style_name . '.min.css', $styles, true ) ?  $style_name . '.min.css' : $style;
	}
}
*/