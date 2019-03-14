<?php
/**
 * WP_Framework_Custom_Post Models Custom Post Test
 *
 * @version 0.0.25
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Custom_Post\Tests\Models;

require_once __DIR__ . DS . 'misc' . DS . 'custom_post.php';
require_once __DIR__ . DS . 'misc' . DS . 'test.php';
require_once __DIR__ . DS . 'misc' . DS . 'db.php';

/**
 * Class Custom_Post
 * @package WP_Framework_Custom_Post\Tests\Models
 * @group wp_framework
 * @group models
 */
class Custom_Post extends \WP_Framework_Custom_Post\Tests\TestCase {

	/**
	 * @var Misc\Db $_db
	 */
	private static $_db;

	/**
	 * @var Misc\Test $_test
	 */
	private static $_test;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_db = Misc\Db::get_instance( static::$app );
		\Phake::when( static::$app )->__get( 'db' )->thenReturn( static::$_db );
		static::$_db->setup( 'test', [
			'columns' => [
				'post_id' => [
					'type'     => 'BIGINT(20)',
					'unsigned' => true,
					'null'     => false,
				],
				'test1'   => [
					'type'          => 'VARCHAR(32)',
					'default'       => 'test1',
					'prior_default' => true,
				],
				'test2'   => [
					'type'     => 'INT(11)',
					'unsigned' => true,
					'null'     => false,
					'default'  => 10,
				],
				'test3'   => [
					'type'     => 'TINYINT(1)',
					'unsigned' => true,
					'null'     => false,
					'default'  => 1,
				],
			],
			'index'   => [
				'unique' => [
					'uk_post_id' => [ 'post_id' ],
				],
			],
		] );
		static::$_db->_table_update( 'test' );
		static::$_test = Misc\Test::get_instance( static::$app );
		Misc\Custom_Post::get_instance( static::$app );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$_test->uninstall();
		static::$_db->drop( 'test' );
	}

	/**
	 * @dataProvider _test_insert_provider
	 *
	 * @param bool $expected
	 * @param array $data
	 */
	public function test_validate_insert( $expected, $data ) {
		$this->assertEquals( $expected, empty( static::$_test->validate_insert( $data ) ) );
	}

	/**
	 * @dataProvider _test_insert_provider
	 *
	 * @param bool $expected
	 * @param array $data
	 */
	public function test_insert( $expected, $data ) {
		$result = static::$_test->insert( $data );
		$this->assertEquals( $expected, is_int( $result ) && $result > 0 );
	}

	/**
	 * @return array
	 */
	public function _test_insert_provider() {
		return [
			[
				true,
				[
					'post_title' => 'test1',
					'test1'      => 'test1-1',
					'test2'      => 1,
					'test3'      => 0,
				],
			],
			[
				true,
				[
					'post_title' => 'test2',
					'test1'      => 'test1-2',
					'test2'      => 2,
					'test3'      => 1,
				],
			],
			[
				true,
				[
					'post_title' => 'test3',
				],
			],
			[
				false,
				[
					'post_title' => '',
				],
			],
		];
	}

	/**
	 * @dataProvider _test_update_provider
	 * @depends      test_insert
	 *
	 * @param mixed $expected
	 * @param array $data
	 * @param array $where
	 */
	public function test_update( $expected, $data, $where ) {
		$result = static::$_test->update( $data, $where );
		$this->assertEquals( $expected, is_int( $result ) && $result > 0 );
	}

	/**
	 * @return array
	 */
	public function _test_update_provider() {
		return [
			[
				true,
				[ 'post_title' => 'test10', 'test1' => 'test10', 'test2' => 10, 'test3' => 0 ],
				[ 'id' => 1 ],
			],
			[
				false,
				[ 'test1' => 'test10' ],
				[ 'id' => 10 ],
			],
		];
	}

	/**
	 * @dataProvider _test_data_provider
	 * @depends      test_update
	 *
	 * @param \Closure $check
	 * @param int $id
	 */
	public function test_data( $check, $id ) {
		$data    = static::$_test->get_data( $id );
		$related = false;
		if ( false !== $data ) {
			$related = static::$_test->get_related_data( $data['post_id'] );
		}
		$check( $data, $related, $id );
	}

	/**
	 * @return array
	 */
	public function _test_data_provider() {
		return [
			[
				function ( $data, $related ) {
					$this->assertNotEmpty( $data );
					$this->assertNotEmpty( $related );
					$this->assertEquals( 'test10', $data['post_title'] );
					$this->assertEquals( 'test10', $data['test1'] );
					$this->assertEquals( 10, $data['test2'] );
					$this->assertEquals( 0, $data['test3'] );
				},
				1,
			],
			[
				function ( $data, $related ) {
					$this->assertNotEmpty( $data );
					$this->assertNotEmpty( $related );
					$this->assertEquals( 'test2', $data['post_title'] );
					$this->assertEquals( 'test1-2', $data['test1'] );
					$this->assertEquals( 2, $data['test2'] );
					$this->assertEquals( 1, $data['test3'] );
				},
				2,
			],
			[
				function ( $data, $related ) {
					$this->assertNotEmpty( $data );
					$this->assertNotEmpty( $related );
					$this->assertEquals( 'test3', $data['post_title'] );
					$this->assertEquals( 'test1', $data['test1'] );
					$this->assertEquals( 10, $data['test2'] );
					$this->assertEquals( 1, $data['test3'] );
				},
				3,
			],
			[
				function ( $data, $related ) {
					$this->assertEmpty( $data );
					$this->assertEmpty( $related );
				},
				4,
			],
		];
	}

	/**
	 * @dataProvider _test_get_list_data_provider
	 * @depends      test_update
	 *
	 * @param \Closure $check
	 * @param \Closure $callback
	 * @param bool $is_valid
	 * @param int|null $per_page
	 * @param int $page
	 */
	public function test_get_list_data( $check, $callback, $is_valid = true, $per_page = null, $page = 1 ) {
		$data = static::$_test->get_list_data( $callback, $is_valid, $per_page, $page );
		$check( $data );
	}

	/**
	 * @return array
	 */
	public function _test_get_list_data_provider() {
		return [
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 3, $data['total'] );
					$this->assertCount( 3, $data['data'] );
					$this->assertEquals( 1, $data['total_page'] );
					$this->assertEquals( 1, $data['page'] );
				},
				null,
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 1, $data['total'] );
					$this->assertEquals( 1, $data['data'][0]['test_id'] );
					$this->assertEquals( 1, $data['data'][0]['id'] );
					$this->assertEquals( 'test10', $data['data'][0]['test1'] );
					$this->assertEquals( 10, $data['data'][0]['test2'] );
					$this->assertEquals( 0, $data['data'][0]['test3'] );
				},
				function ( $query ) {
					/** @var \WP_Framework_Db\Classes\Models\Query\Builder $query */
					$query->where( 'id', 1 );
				},
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 0, $data['total'] );
					$this->assertEmpty( $data['data'] );
				},
				function ( $query ) {
					/** @var \WP_Framework_Db\Classes\Models\Query\Builder $query */
					$query->where( 'id', 4 );
				},
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 3, $data['total'] );
					$this->assertCount( 2, $data['data'] );
					$this->assertEquals( 2, $data['total_page'] );
					$this->assertEquals( 1, $data['page'] );
				},
				null,
				true,
				2,
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 3, $data['total'] );
					$this->assertCount( 1, $data['data'] );
					$this->assertEquals( 2, $data['total_page'] );
					$this->assertEquals( 2, $data['page'] );
				},
				null,
				true,
				2,
				5,
			],
		];
	}

	/**
	 * @dataProvider _test_list_data_provider
	 * @depends      test_update
	 *
	 * @param \Closure $check
	 * @param bool $is_valid
	 * @param int|null $per_page
	 * @param int $page
	 * @param array|null $where
	 * @param array|null $order_by
	 */
	public function test_list_data( $check, $is_valid = true, $per_page = null, $page = 1, $where = null, $order_by = null ) {
		$data = static::$_test->list_data( $is_valid, $per_page, $page, $where, $order_by );
		$check( $data );
	}

	/**
	 * @return array
	 */
	public function _test_list_data_provider() {
		return [
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 3, $data['total'] );
					$this->assertCount( 3, $data['data'] );
					$this->assertEquals( 1, $data['total_page'] );
					$this->assertEquals( 1, $data['page'] );
				},
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 1, $data['total'] );
					$this->assertEquals( 1, $data['data'][0]['id'] );
					$this->assertEquals( 'test10', $data['data'][0]['test1'] );
					$this->assertEquals( 10, $data['data'][0]['test2'] );
					$this->assertEquals( 0, $data['data'][0]['test3'] );
				},
				true,
				null,
				1,
				[ 't.test_id' => 1 ],
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 3, $data['total'] );
					$this->assertCount( 2, $data['data'] );
					$this->assertEquals( 2, $data['total_page'] );
					$this->assertEquals( 1, $data['page'] );
				},
				true,
				2,
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 3, $data['total'] );
					$this->assertCount( 1, $data['data'] );
					$this->assertEquals( 2, $data['total_page'] );
					$this->assertEquals( 2, $data['page'] );
				},
				true,
				2,
				5,
			],
		];
	}
}