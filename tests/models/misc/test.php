<?php
/**
 * WP_Framework_Custom_Post Tests Models Misc Test
 *
 * @version 0.0.1
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
 * Class Test
 * @package WP_Framework_Custom_Post\Tests\Models\Misc
 */
class Test implements \WP_Framework_Custom_Post\Interfaces\Custom_Post {

	use \WP_Framework_Custom_Post\Traits\Custom_Post, \WP_Framework_Core\Traits\Helper\Data_Helper, \WP_Framework_Core\Traits\Helper\Validate;

	/**
	 * @return string
	 */
	public function get_post_type() {
		return '___cp_test-' . $this->get_post_type_slug();
	}

	/**
	 * @return array
	 */
	protected function get_capabilities() {
		return (array) get_post_type_object( 'post' )->cap;
	}

	/**
	 * @return string|false
	 */
	protected function get_post_type_parent() {
		return false;
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		$this->wp_table( 'posts' )->where( 'post_type', $this->get_post_type() )->chunk_for_delete( 1000, function ( $posts ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post['ID'] );
			}
		} );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function translate( $value ) {
		return $value;
	}
}
