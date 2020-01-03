<?php
/**
 * WP_Framework_Custom_Post Models Custom Post Test
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 * @see https://github.com/wp-content-framework/custom_post/issues/86
 * @see https://github.com/wp-content-framework/custom_post/issues/89
 */

namespace WP_Framework_Custom_Post\Tests\Models;

use Closure;
use Phake;
use WP_Framework_Custom_Post\Tests\TestCase;
use WP_Framework_Db\Classes\Models\Query\Builder;

require_once __DIR__ . DS . 'misc' . DS . 'custom_post.php';
require_once __DIR__ . DS . 'misc' . DS . 'test.php';
require_once __DIR__ . DS . 'misc' . DS . 'db.php';

/**
 * Class Custom_Post
 * @package WP_Framework_Custom_Post\Tests\Models
 * @group wp_framework
 * @group models
 */
class Custom_Post extends TestCase {

	/**
	 * @var Misc\Db $db
	 */
	private static $db;

	/**
	 * @var Misc\Test $test
	 */
	private static $test;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$db = Misc\Db::get_instance( static::$app );
		Phake::when( static::$app )->__get( 'db' )->thenReturn( static::$db );
		static::$db->setup( 'test', [
			'columns' => [
				'post_id' => [
					'type'     => 'BIGINT(20)',
					'unsigned' => true,
					'null'     => false,
				],
				'test1'   => [ // not nullable, has default
					'type'    => 'VARCHAR(32)',
					'default' => 'test1',
					'null'    => false,
				],
				'test2'   => [ // not nullable, has default
					'type'     => 'INT(11)',
					'unsigned' => true,
					'null'     => false,
					'default'  => 10,
				],
				'test3'   => [ // not nullable, has default
					'type'     => 'BIT(1)',
					'unsigned' => true,
					'null'     => false,
					'default'  => 1,
				],
				'test4'   => [ // nullable, has default, prior_default = false
					'type'    => 'VARCHAR(32)',
					'default' => 'test4',
				],
				'test5'   => [ // nullable, has default, prior_default = true
					'type'          => 'VARCHAR(32)',
					'default'       => 'test5',
					'prior_default' => true,
				],
				'test6'   => [ // nullable, has not default
					'type' => 'VARCHAR(32)',
				],
				'test7'   => [ // not nullable, has not default
					'type' => 'VARCHAR(32)',
					'null' => false,
				],
				'test8'   => [ // nullable, has default, prior_default = false
					'type'     => 'INT(11)',
					'unsigned' => true,
					'default'  => 8,
				],
				'test9'   => [ // nullable, has default, prior_default = true
					'type'          => 'INT(11)',
					'unsigned'      => true,
					'default'       => 9,
					'prior_default' => true,
				],
				'test10'  => [ // nullable, has not default
					'type'     => 'INT(11)',
					'unsigned' => true,
				],
				'test11'  => [ // not nullable, has not default
					'type'     => 'INT(11)',
					'unsigned' => true,
					'null'     => false,
				],
				'test12'  => [ // nullable, has default, prior_default = false
					'type'     => 'BIT(1)',
					'unsigned' => true,
					'default'  => 1,
				],
				'test13'  => [ // nullable, has default, prior_default = true
					'type'          => 'BIT(1)',
					'unsigned'      => true,
					'default'       => 1,
					'prior_default' => true,
				],
				'test14'  => [ // nullable, has not default
					'type'     => 'BIT(1)',
					'unsigned' => true,
				],
				'test15'  => [ // not nullable, has not default
					'type'     => 'BIT(1)',
					'unsigned' => true,
					'null'     => false,
				],
			],
			'index'   => [
				'unique' => [
					'uk_post_id' => [ 'post_id' ],
				],
			],
		] );
		static::$db->wrap_table_update( 'test' );
		static::$test = Misc\Test::get_instance( static::$app );
		Misc\Custom_Post::get_instance( static::$app );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$test->uninstall();
		static::$db->drop( 'test' );
	}

	/**
	 * @dataProvider provider_test_insert
	 *
	 * @param bool $expected
	 * @param array $data
	 */
	public function test_validate_insert( $expected, $data ) {
		$this->assertEquals( $expected, empty( static::$test->validate_insert( $data ) ) );
	}

	/**
	 * @dataProvider provider_test_insert
	 *
	 * @param bool $expected
	 * @param array $data
	 */
	public function test_insert( $expected, $data ) {
		$result = static::$test->insert( $data );
		if ( ! empty( $data['post_status'] ) && 'trash' === $data['post_status'] ) {
			$expected = true;
		}
		$this->assertEquals( $expected, is_int( $result ) && $result > 0 );
	}

	/**
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function provider_test_insert() {
		return [
			[
				false,
				[
					'post_title' => 'test1',
					'test1'      => 'test1-1',
					'test2'      => 1,
					'test3'      => 0,
				],
			],
			[
				false,
				[
					'post_title' => 'test2',
					'test1'      => 'test1-2',
					'test2'      => 2,
					'test3'      => 1,
				],
			],
			[
				false,
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
			[
				false,
				[
					'post_title'  => 'test5',
					'test1'       => 'test1-5',
					'post_status' => 'draft',
				],
			],
			[
				false,
				[
					'post_title'  => 'test6',
					'test1'       => 'test1-6',
					'post_status' => 'trash',
				],
			],

			[
				true,
				[
					'post_title' => 'test1',
					'test1'      => 'test1-1',
					'test2'      => 1,
					'test3'      => 1,
					'test7'      => 'test7-1',
					'test11'     => 11,
					'test15'     => 1,
				],
			],
			[
				true,
				[
					'post_title' => 'test2',
					'test1'      => 'test1-2',
					'test2'      => 2,
					'test3'      => 1,
					'test7'      => 'test7-2',
					'test11'     => 11,
					'test15'     => 1,
				],
			],
			[
				true,
				[
					'post_title' => 'test3',
					'test7'      => 'test7-3',
					'test11'     => 11,
					'test15'     => 1,
				],
			],
			[
				false,
				[
					'post_title' => '',
					'test7'      => 'test7-4',
					'test11'     => 11,
					'test15'     => 1,
				],
			],
			[
				true,
				[
					'post_title'  => 'test5',
					'test1'       => 'test1-5',
					'post_status' => 'draft',
					'test7'       => 'test7-5',
					'test11'      => 11,
					'test15'      => 1,
				],
			],
			[
				true,
				[
					'post_title'  => 'test6',
					'test1'       => 'test1-6',
					'post_status' => 'trash',
					'test7'       => 'test7-6',
					'test11'      => 11,
					'test15'      => 1,
				],
			],

			[
				false,
				[
					'post_title' => 'test7',
					'test1'      => 'test1-1',
					'test2'      => 2,
					'test3'      => 0,
					'test7'      => 'test7-7',
					'test11'     => 'abc',
					'test15'     => 1,
				],
			],
		];
	}

	/**
	 * @dataProvider provider_test_update
	 * @depends      test_insert
	 *
	 * @param mixed $expected
	 * @param array $data
	 * @param array $where
	 */
	public function test_update( $expected, $data, $where ) {
		$result = static::$test->update( $data, $where );
		$this->assertEquals( $expected, is_int( $result ) && $result > 0 );
	}

	/**
	 * @return array
	 */
	public function provider_test_update() {
		return [
			[
				false,
				[
					'post_title' => 'test10',
					'test1'      => 'test10',
					'test2'      => 10,
					'test3'      => 0,
				],
				[ 'id' => 1 ],
			],
			[
				false,
				[
					'test1'  => 'test10',
					'test7'  => 'test7',
					'test11' => 110,
					'test15' => 0,
				],
				[ 'id' => 100 ],
			],
			[
				true,
				[
					'post_title' => 'test10',
					'test1'      => 'test10',
					'test2'      => 10,
					'test3'      => 0,
					'test7'      => 'test7',
					'test11'     => 110,
					'test15'     => 0,
				],
				[ 'id' => 1 ],
			],
		];
	}

	/**
	 * @dataProvider provider_test_data
	 * @depends      test_update
	 *
	 * @param Closure $check
	 * @param int $id
	 */
	public function test_data( $check, $id ) {
		$data    = static::$test->get_data( $id );
		$related = false;
		if ( false !== $data ) {
			$related = static::$test->get_related_data( $data['post_id'] );
		}
		$check( $data, $related, $id );
	}

	/**
	 * @return array
	 */
	public function provider_test_data() {
		return [
			[
				function ( $data, $related ) {
					$this->assertNotEmpty( $data );
					$this->assertNotEmpty( $related );
					$this->assertEquals( 'test10', $data['post_title'] );
					$this->assertEquals( 'test10', $data['test1'] );
					$this->assertEquals( 10, $data['test2'] );
					$this->assertEquals( 0, $data['test3'] );
					$this->assertEquals( 'test7', $data['test7'] );
					$this->assertEquals( 110, $data['test11'] );
					$this->assertEquals( 0, $data['test15'] );
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

					$this->assertNull( $data['test4'] );
					$this->assertEquals( 'test5', $data['test5'] );
					$this->assertNull( $data['test6'] );
					$this->assertEquals( 'test7-2', $data['test7'] );

					$this->assertNull( $data['test8'] );
					$this->assertEquals( 9, $data['test9'] );
					$this->assertNull( $data['test10'] );
					$this->assertEquals( 11, $data['test11'] );

					$this->assertNull( $data['test12'] );
					$this->assertNull( $data['test13'] );
					$this->assertNull( $data['test14'] );
					$this->assertEquals( 1, $data['test15'] );
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
					$this->assertEquals( 0, $data['test3'] );
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
	 * @dataProvider provider_test_get_list_data
	 * @depends      test_update
	 *
	 * @param Closure $check
	 * @param Closure $callback
	 * @param bool $is_valid
	 * @param int|null $per_page
	 * @param int $page
	 */
	public function test_get_list_data( $check, $callback, $is_valid, $per_page, $page ) {
		$data = static::$test->get_list_data( $callback, $is_valid, $per_page, $page );
		$check( $data );
	}

	/**
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function provider_test_get_list_data() {
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
				true,
				null,
				1,
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 4, $data['total'] );
					$this->assertCount( 4, $data['data'] );
					$this->assertEquals( 1, $data['total_page'] );
					$this->assertEquals( 1, $data['page'] );
				},
				null,
				false,
				null,
				1,
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
					/** @var Builder $query */
					$query->where( 'id', 1 );
				},
				true,
				null,
				1,
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
					/** @var Builder $query */
					$query->where( 'id', 4 );
				},
				true,
				null,
				1,
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
				1,
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
	 * @dataProvider provider_test_list_data
	 * @depends      test_update
	 *
	 * @param Closure $check
	 * @param bool $is_valid
	 * @param int|null $per_page
	 * @param int $page
	 * @param array|null $where
	 * @param array|null $order_by
	 */
	public function test_list_data( $check, $is_valid, $per_page, $page, $where, $order_by ) {
		$data = static::$test->list_data( $is_valid, $per_page, $page, $where, $order_by );
		$check( $data );
	}

	/**
	 * @return array
	 */
	public function provider_test_list_data() {
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
				true,
				null,
				1,
				null,
				null,
			],
			[
				function ( $data ) {
					$this->assertNotEmpty( $data );
					$this->assertArrayHasKey( 'total', $data );
					$this->assertArrayHasKey( 'total_page', $data );
					$this->assertArrayHasKey( 'page', $data );
					$this->assertArrayHasKey( 'data', $data );
					$this->assertEquals( 4, $data['total'] );
					$this->assertCount( 4, $data['data'] );
					$this->assertEquals( 1, $data['total_page'] );
					$this->assertEquals( 1, $data['page'] );
				},
				false,
				null,
				1,
				null,
				null,
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
				null,
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
				1,
				null,
				null,
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
				null,
				null,
			],
		];
	}

	/**
	 * @dataProvider provider_test_count
	 * @depends      test_update
	 *
	 * @param int $expected
	 * @param bool $only_publish
	 */
	public function test_count( $expected, $only_publish ) {
		$this->assertEquals( $expected, static::$test->count( $only_publish ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_count() {
		return [
			[ 4, false ],
			[ 3, true ],
		];
	}

	/**
	 * @dataProvider provider_test_is_empty1
	 * @depends      test_update
	 *
	 * @param bool $expected
	 * @param bool $only_publish
	 */
	public function test_is_empty1( $expected, $only_publish ) {
		$this->assertEquals( $expected, static::$test->is_empty( $only_publish ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_is_empty1() {
		return [
			[ false, false ],
			[ false, true ],
		];
	}

	public function test_get_update_data_params1() {
		foreach ( array_keys( static::$app->input->post() ) as $key ) {
			static::$app->input->delete_post( $key );
		}
		$data = static::$app->array->get( static::$test->get_list_data(), 'data.0' );

		static::$app->input->set_post( static::$test->get_post_field_name( 'test7' ), 'test7' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test11' ), '11' );
		$params = static::$test->get_update_data_params( $data['post'], false );
		$this->assertArrayNotHasKey( 'test1', $params );
		$this->assertArrayNotHasKey( 'test2', $params );
		$this->assertEquals( 0, $params['test3'] );

		$this->assertNull( $params['test4'] );
		$this->assertArrayNotHasKey( 'test5', $params );
		$this->assertNull( $params['test6'] );
		$this->assertEquals( 'test7', $params['test7'] );

		$this->assertNull( $params['test8'] );
		$this->assertArrayNotHasKey( 'test9', $params );
		$this->assertNull( $params['test10'] );
		$this->assertEquals( 11, $params['test11'] );

		$this->assertNull( $params['test12'] );
		$this->assertNull( $params['test13'] );
		$this->assertNull( $params['test14'] );
		$this->assertEquals( 0, $params['test15'] );

		static::$app->input->delete_post( static::$test->get_post_field_name( 'test7' ) );
		static::$app->input->delete_post( static::$test->get_post_field_name( 'test11' ) );
		$params = static::$test->get_update_data_params( $data['post'], true );
		$this->assertArrayNotHasKey( 'test1', $params );
		$this->assertArrayNotHasKey( 'test2', $params );
		$this->assertEquals( 0, $params['test3'] );

		$this->assertArrayNotHasKey( 'test4', $params );
		$this->assertArrayNotHasKey( 'test5', $params );
		$this->assertArrayNotHasKey( 'test6', $params );
		$this->assertArrayNotHasKey( 'test7', $params );

		$this->assertArrayNotHasKey( 'test8', $params );
		$this->assertArrayNotHasKey( 'test9', $params );
		$this->assertArrayNotHasKey( 'test10', $params );
		$this->assertArrayNotHasKey( 'test11', $params );

		$this->assertArrayNotHasKey( 'test12', $params );
		$this->assertArrayNotHasKey( 'test13', $params );
		$this->assertArrayNotHasKey( 'test14', $params );
		$this->assertEquals( 0, $params['test15'] );
	}

	/**
	 * @depends test_get_update_data_params1
	 */
	public function test_get_update_data_params2() {
		$data = static::$app->array->get( static::$test->get_list_data(), 'data.0' );

		static::$app->input->set_post( static::$test->get_post_field_name( 'test1' ), 'test100' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test2' ), '100' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test3' ), '1' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test4' ), 'test400' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test5' ), 'test500' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test6' ), 'test600' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test7' ), 'test700' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test8' ), '800' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test9' ), '900' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test10' ), '1000' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test11' ), '1100' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test12' ), '1' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test13' ), '0' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test14' ), '1' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test15' ), 'false' );

		$params = static::$test->get_update_data_params( $data['post'], false );
		$this->assertEquals( $params['test1'], 'test100' );
		$this->assertEquals( $params['test2'], 100 );
		$this->assertEquals( $params['test3'], 1 );
		$this->assertEquals( $params['test4'], 'test400' );
		$this->assertEquals( $params['test5'], 'test500' );
		$this->assertEquals( $params['test6'], 'test600' );
		$this->assertEquals( $params['test7'], 'test700' );
		$this->assertEquals( $params['test8'], 800 );
		$this->assertEquals( $params['test9'], 900 );
		$this->assertEquals( $params['test10'], 1000 );
		$this->assertEquals( $params['test11'], 1100 );
		$this->assertEquals( $params['test12'], 1 );
		$this->assertEquals( $params['test13'], 0 );
		$this->assertEquals( $params['test14'], 1 );
		$this->assertEquals( $params['test15'], 0 );

		$params = static::$test->get_update_data_params( $data['post'], true );
		$this->assertEquals( $params['test1'], 'test100' );
		$this->assertEquals( $params['test2'], 100 );
		$this->assertEquals( $params['test3'], 1 );
		$this->assertEquals( $params['test4'], 'test400' );
		$this->assertEquals( $params['test5'], 'test500' );
		$this->assertEquals( $params['test6'], 'test600' );
		$this->assertEquals( $params['test7'], 'test700' );
		$this->assertEquals( $params['test8'], 800 );
		$this->assertEquals( $params['test9'], 900 );
		$this->assertEquals( $params['test10'], 1000 );
		$this->assertEquals( $params['test11'], 1100 );
		$this->assertEquals( $params['test12'], 1 );
		$this->assertEquals( $params['test13'], 0 );
		$this->assertEquals( $params['test14'], 1 );
		$this->assertEquals( $params['test15'], 0 );
	}

	/**
	 * @depends test_get_update_data_params2
	 */
	public function test_get_update_data_params3() {
		$data = static::$app->array->get( static::$test->get_list_data(), 'data.0' );

		static::$app->input->set_post( static::$test->get_post_field_name( 'test1' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test2' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test3' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test4' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test5' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test6' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test7' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test8' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test9' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test10' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test11' ), '11' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test12' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test13' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test14' ), '' );
		static::$app->input->set_post( static::$test->get_post_field_name( 'test15' ), '' );

		$params = static::$test->get_update_data_params( $data['post'], false );
		$this->assertEquals( $params['test1'], 'test1' );
		$this->assertEquals( $params['test2'], 10 );
		$this->assertEquals( $params['test3'], 1 );

		$this->assertNull( $params['test4'] );
		$this->assertEquals( $params['test5'], 'test5' );
		$this->assertNull( $params['test6'] );
		$this->assertEquals( $params['test7'], '' );

		$this->assertNull( $params['test8'] );
		$this->assertEquals( $params['test9'], 9 );
		$this->assertNull( $params['test10'] );
		$this->assertEquals( 11, $params['test11'] );

		$this->assertNull( $params['test12'] );
		$this->assertEquals( 1, $params['test13'] );
		$this->assertNull( $params['test14'] );
		$this->assertEquals( 0, $params['test15'] );

		$params = static::$test->get_update_data_params( $data['post'], true );
		$this->assertEquals( $params['test1'], 'test1' );
		$this->assertEquals( $params['test2'], 10 );
		$this->assertEquals( $params['test3'], 1 );

		$this->assertNull( $params['test4'] );
		$this->assertEquals( $params['test5'], 'test5' );
		$this->assertNull( $params['test6'] );
		$this->assertEquals( $params['test7'], '' );

		$this->assertNull( $params['test8'] );
		$this->assertEquals( $params['test9'], 9 );
		$this->assertNull( $params['test10'] );
		$this->assertEquals( 11, $params['test11'] );

		$this->assertNull( $params['test12'] );
		$this->assertEquals( 1, $params['test13'] );
		$this->assertNull( $params['test14'] );
		$this->assertEquals( 0, $params['test15'] );
	}

	/**
	 * @depends      test_update
	 */
	public function test_delete_data() {
		foreach ( static::$app->array->get( static::$test->get_list_data( null, false ), 'data' ) as $data ) {
			$this->assertInstanceOf( '\WP_Post', wp_delete_post( $data['post_id'] ) );
		}
	}

	/**
	 * @dataProvider provider_test_is_empty2
	 * @depends      test_delete_data
	 *
	 * @param bool $expected
	 * @param bool $only_publish
	 */
	public function test_is_empty2( $expected, $only_publish ) {
		$this->assertEquals( $expected, static::$test->is_empty( $only_publish ) );
	}

	/**
	 * @return array
	 */
	public function provider_test_is_empty2() {
		return [
			[ true, false ],
			[ true, true ],
		];
	}
}
