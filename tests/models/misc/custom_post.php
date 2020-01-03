<?php
/**
 * WP_Framework_Custom_Post Tests Models Misc Custom_Post
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Custom_Post\Tests\Models\Misc;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Custom_Post
 * @package WP_Framework_Custom_Post\Tests\Models\Misc
 */
class Custom_Post extends \WP_Framework_Custom_Post\Classes\Models\Custom_Post {

	/**
	 * @var \WP_Framework_Custom_Post\Interfaces\Custom_Post[] $custom_posts
	 */
	private $custom_posts;

	/**
	 * @var string[] $custom_posts_mapper
	 */
	private $custom_posts_mapper;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->get_custom_posts();

		add_filter( 'save_post', function () {
			return $this->filter_callback( 'save_post', func_get_args() );
		}, 10, 100 );
		add_filter( 'save_post', function () {
			return $this->filter_callback( 'untrash_post', func_get_args() );
		}, 10, 100 );
		add_filter( 'wp_trash_post', function () {
			return $this->filter_callback( 'wp_trash_post', func_get_args() );
		}, 10, 100 );
		add_filter( 'delete_post', function () {
			return $this->filter_callback( 'delete_post', func_get_args() );
		}, 10, 100 );
		add_filter( 'wp_insert_post_empty_content', function () {
			return $this->filter_callback( 'post_validation', func_get_args() );
		}, 10, 100 );
		add_filter( 'wp_insert_post_data', function () {
			return $this->filter_callback( 'wp_insert_post_data', func_get_args() );
		}, 10, 100 );
	}

	/**
	 * @return \WP_Framework_Custom_Post\Interfaces\Custom_Post[]
	 */
	public function get_custom_posts() {
		if ( ! isset( $this->custom_posts ) ) {
			$this->custom_posts        = [ Test::get_instance( $this->app ) ];
			$post_types                = array_map( function ( $custom_post ) {
				/** @var \WP_Framework_Custom_Post\Interfaces\Custom_Post $custom_post */
				return $custom_post->get_post_type();
			}, $this->custom_posts );
			$this->custom_posts_mapper = array_map( function ( $custom_post ) {
				/** @var \WP_Framework_Custom_Post\Interfaces\Custom_Post $custom_post */
				return $custom_post->get_post_type_slug();
			}, $this->custom_posts );
			$this->custom_posts        = array_combine( $post_types, $this->custom_posts );
			$this->custom_posts_mapper = array_combine( $this->custom_posts_mapper, $post_types );
		}

		return $this->custom_posts;
	}
}
