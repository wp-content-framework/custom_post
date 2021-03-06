<?php
/**
 * WP_Framework_Custom_Post TestCase
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Custom_Post\Tests;

use Phake;
use Phake_IMock;
use WP_Framework;
use WP_Framework_Cache\Classes\Models\Cache;
use WP_Framework_Common\Classes\Models\Array_Utility;
use WP_Framework_Common\Classes\Models\Define;
use WP_Framework_Common\Classes\Models\Deprecated;
use WP_Framework_Common\Classes\Models\File_Utility;
use WP_Framework_Common\Classes\Models\Input;
use WP_Framework_Common\Classes\Models\Option;
use WP_Framework_Common\Classes\Models\String_Utility;
use WP_Framework_Common\Classes\Models\User;
use WP_Framework_Common\Classes\Models\Utility;

/**
 * Class TestCase
 * @package WP_Framework_Custom_Post\Tests
 */
class TestCase extends \PHPUnit\Framework\TestCase {

	/**
	 * @var WP_Framework|Phake_IMock
	 */
	protected static $app;

	/**
	 * @var string
	 */
	protected static $plugin_name;

	/**
	 * @var string
	 */
	protected static $plugin_file;

	/**
	 * @var string
	 */
	protected static $plugin_dir;

	public static function setUpBeforeClass() {
		static::$app = Phake::mock( '\WP_Framework' );
		Phake::when( static::$app )->get_package_directory()->thenReturn( dirname( __DIR__ ) );
		Phake::when( static::$app )->get_mapped_class()->thenReturn( [ false, null ] );
		Phake::when( static::$app )->has_initialized()->thenReturn( true );
		Phake::when( static::$app )->is_enough_version()->thenReturn( true );
		Phake::when( static::$app )->get_packages()->thenReturn( [] );
		Phake::when( static::$app )->get_plugin_version()->thenReturn( '0.0.1' );
		Phake::when( static::$app )->get_config( 'deprecated' )->thenReturn( [
			'\WP_Framework_Custom_Post\Traits\Custom_Post' => '\WP_Framework_Custom_Post\Deprecated\Traits\Custom_Post',
			'\WP_Framework_Db\Classes\Models\Db'           => '\WP_Framework_Db\Deprecated\Classes\Models\Db',
		] );
		static::$plugin_name = md5( uniqid() );
		static::$plugin_file = __FILE__;
		static::$plugin_dir  = __DIR__;
		Phake::when( static::$app )->__get( 'plugin_name' )->thenReturn( static::$plugin_name );
		Phake::when( static::$app )->__get( 'plugin_file' )->thenReturn( static::$plugin_file );
		Phake::when( static::$app )->__get( 'plugin_dir' )->thenReturn( static::$plugin_dir );
		Phake::when( static::$app )->__get( 'slug_name' )->thenReturn( static::$plugin_name );
		Phake::when( static::$app )->__get( 'define' )->thenReturn( Define::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'input' )->thenReturn( Input::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'utility' )->thenReturn( Utility::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'array' )->thenReturn( Array_Utility::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'string' )->thenReturn( String_Utility::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'file' )->thenReturn( File_Utility::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'user' )->thenReturn( User::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'deprecated' )->thenReturn( Deprecated::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'option' )->thenReturn( Option::get_instance( static::$app ) );
		Phake::when( static::$app )->__get( 'cache' )->thenReturn( Cache::get_instance( static::$app ) );
	}

	public static function tearDownAfterClass() {
		static::$app->user->uninstall();
		static::$app->cache->uninstall();
		static::$app->option->uninstall();
	}
}
