<?php
/**
 * WP_Framework_Custom_Post Tests Models Misc Db
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
 * Class Db
 * @package WP_Framework_Custom_Post\Tests\Models\Misc
 */
class Db extends \WP_Framework_Db\Classes\Models\Db {

	/**
	 * initialize
	 */
	protected function initialize() {

	}

	/**
	 * @param string $table
	 * @param array $define
	 */
	public function setup( $table, array $define ) {
		$this->drop( $table );
		list( $id, $columns ) = $this->setup_table_columns( $table, $define );
		if ( $id ) {
			$this->table_defines[ $table ]            = $define;
			$this->table_defines[ $table ]['id']      = $id;
			$this->table_defines[ $table ]['columns'] = $columns;
		}
	}

	/**
	 * @param string $table
	 */
	public function drop( $table ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$sql = 'DROP TABLE IF EXISTS `' . $this->get_table( $table ) . '`';
		$wpdb->query( $sql );
	}

	/**
	 * @param string $table
	 *
	 * @return array
	 */
	public function _table_update( $table ) {
		return $this->table_update( $table, $this->table_defines[ $table ] );
	}
}
