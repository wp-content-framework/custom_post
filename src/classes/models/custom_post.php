<?php
/**
 * WP_Framework_Custom_Post Classes Models Custom Post
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Custom_Post\Classes\Models;

use Exception;
use stdClass;
use WP_Framework_Common\Traits\Uninstall;
use WP_Framework_Core\Traits\Loader;
use WP_Framework_Custom_Post\Traits\Package;
use WP_Framework_Presenter\Traits\Presenter;
use WP_Post;
use WP_Query;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Custom_Post
 * @package WP_Framework_Custom_Post\Classes\Models
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Custom_Post implements \WP_Framework_Core\Interfaces\Loader, \WP_Framework_Presenter\Interfaces\Presenter, \WP_Framework_Common\Interfaces\Uninstall {

	use Loader, Presenter, Uninstall, Package;

	/**
	 * @var \WP_Framework_Custom_Post\Interfaces\Custom_Post[] $custom_posts
	 */
	private $custom_posts;

	/**
	 * @var string[] $custom_posts_mapper
	 */
	private $custom_posts_mapper;

	/**
	 * @var false|array $validation_errors
	 */
	private $validation_errors = false;

	/**
	 * register post types
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function register_post_types() {
		$this->get_custom_posts();
	}

	/**
	 * @param array $columns
	 * @param string $post_type
	 *
	 * @return array
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function manage_posts_columns( array $columns, $post_type ) {
		if ( $this->is_valid_custom_post_type( $post_type ) ) {
			$custom_post = $this->get_custom_post_type( $post_type );
			if ( ! $custom_post->user_can( 'edit_others_posts' ) ) {
				unset( $columns['cb'] );
			}
			$custom_post = $this->get_custom_post_type( $post_type );
			if ( ! empty( $custom_post ) ) {
				return $custom_post->manage_posts_columns( $columns );
			}
		}

		return $columns;
	}

	/**
	 * @param string $column_name
	 * @param int $post_id
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function manage_posts_custom_column( $column_name, $post_id ) {
		$post        = get_post( $post_id );
		$post_type   = $post->post_type;
		$custom_post = $this->get_custom_post_type( $post_type );
		if ( ! empty( $custom_post ) ) {
			$custom_post->manage_posts_custom_column( $column_name, $post );
		}
	}

	/**
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function post_row_actions( array $actions, WP_Post $post ) {
		if ( $this->is_valid_custom_post_type( $post->post_type ) ) {
			$custom_post = $this->get_custom_post_type( $post->post_type );

			return $custom_post->post_row_actions( $actions, $post );
		}

		return $actions;
	}

	/**
	 * @param string $search
	 * @param WP_Query|string $wp_query $wp_query
	 *
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function posts_search( $search, $wp_query ) {
		if ( is_string( $wp_query ) ) {
			$post_type = $wp_query;
		} else {
			if ( empty( $wp_query->query_vars['post_type'] ) || is_array( $wp_query->query_vars['post_type'] ) ) {
				return $search;
			}
			$post_type = $wp_query->query_vars['post_type'];
		}

		$custom_post = $this->get_custom_post_type( $post_type );
		if ( ! empty( $custom_post ) ) {
			return $custom_post->posts_search( $search, $wp_query );
		}

		return $search;
	}

	/**
	 * @param string $join
	 * @param WP_Query|string $wp_query
	 *
	 * @return string
	 */
	private function posts_join( $join, $wp_query ) {
		if ( is_string( $wp_query ) ) {
			$post_type = $wp_query;
		} else {
			if ( empty( $wp_query->query_vars['post_type'] ) || is_array( $wp_query->query_vars['post_type'] ) ) {
				return $join;
			}
			$post_type = $wp_query->query_vars['post_type'];
		}

		$custom_post = $this->get_custom_post_type( $post_type );
		if ( ! empty( $custom_post ) ) {
			return $custom_post->posts_join( $join, $wp_query );
		}

		return $join;
	}

	/**
	 * @param WP_Query $wp_query
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function setup_posts_orderby( $wp_query ) {
		if ( ! $wp_query->is_admin ) {
			return;
		}

		$post_type = $wp_query->get( 'post_type' );
		if ( empty( $post_type ) ) {
			return;
		}

		$custom_post = $this->get_custom_post_type( $post_type );
		if ( empty( $custom_post ) ) {
			return;
		}

		$custom_post->setup_posts_orderby( $wp_query );
	}

	/**
	 * @param object $counts
	 * @param string $type
	 * @param string $perm
	 *
	 * @return array|bool|mixed|object|stdClass
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function wp_count_posts( $counts, $type = 'post', $perm = '' ) {
		if ( ! is_admin() || ! $this->is_valid_custom_post_type( $type ) ) {
			return $counts;
		}

		if ( ! post_type_exists( $type ) ) {
			return new stdClass();
		}

		$cache_key = _count_posts_cache_key( $type, $perm ) . '_author';
		$cached    = wp_cache_get( $cache_key, 'counts' );
		if ( false !== $cached ) {
			return $cached;
		}

		$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$this->get_wp_table('posts')} ";
		$query = $query . $this->posts_join( '', $type );
		$query = $query . ' WHERE post_type = %s GROUP BY post_status';

		$results = (array) $this->wpdb()->get_results( $this->wpdb()->prepare( $query, $type ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$counts  = array_fill_keys( get_post_stati(), 0 );
		foreach ( $results as $row ) {
			$counts[ $row['post_status'] ] = $row['num_posts'];
		}
		$counts = (object) $counts;
		wp_cache_set( $cache_key, $counts, 'counts' );

		return $counts;
	}

	/**
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param bool $update
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function save_post( $post_id, WP_Post $post, $update ) {
		if ( $this->is_valid_update( $post->post_status, $post->post_type ) ) {
			$custom_post = $this->get_custom_post_type( $post->post_type );
			if ( ! empty( $custom_post ) ) {
				if ( $update ) {
					$related = $custom_post->get_related_data( $post_id );
					if ( $related ) {
						$old = $custom_post->get_data( $related['id'] );
					} else {
						$old    = false;
						$update = false;
					}
				} else {
					$old = false;
				}
				if ( ! $this->app->db->transaction( function () use ( $custom_post, $post_id, $post, $update, $old ) {
					$this->update_data( $custom_post, $post_id, $post, $update, $old );
				} ) ) {
					$this->validation_errors = [
						'Db error' => [
							$this->app->db->get_last_transaction_error()->getMessage(),
						],
					];
				}
			}
		}
	}

	/**
	 * @param \WP_Framework_Custom_Post\Interfaces\Custom_Post $custom_post
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param bool $update
	 * @param mixed $old
	 *
	 * @throws Exception
	 */
	private function update_data( $custom_post, $post_id, $post, $update, $old ) {
		$id = $custom_post->update_data(
			[
				'post_id' => $post_id,
			],
			[
				'post_id' => $post_id,
			],
			$post,
			$update
		);
		if ( ! empty( $id ) ) {
			$data = $custom_post->get_data( $id );
			if ( $data ) {
				if ( $update ) {
					$custom_post->data_updated( $post_id, $post, $old, $data );
				} else {
					$custom_post->data_inserted( $post_id, $post, $data );
				}
			}
		} else {
			throw new Exception( $this->app->db->get_last_error() );
		}
	}

	/**
	 * @param int $post_id
	 * @param WP_Post $post
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function untrash_post( $post_id, WP_Post $post ) {
		if ( $this->is_valid_update( $post->post_status, $post->post_type, true ) ) {
			$custom_post = $this->get_custom_post_type( $post->post_type );
			if ( ! empty( $custom_post ) ) {
				$custom_post->untrash_post( $post_id, $post );
			}
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function wp_trash_post( $post_id ) {
		$post        = get_post( $post_id );
		$post_type   = $post->post_type;
		$custom_post = $this->get_custom_post_type( $post_type );
		if ( ! empty( $custom_post ) ) {
			$custom_post->trash_post( $post_id );
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function delete_post( $post_id ) {
		$post        = get_post( $post_id );
		$post_type   = $post->post_type;
		$custom_post = $this->get_custom_post_type( $post_type );
		if ( ! empty( $custom_post ) ) {
			$custom_post->delete_data( $post_id );
		}
	}

	/**
	 * @param bool $maybe_empty
	 * @param array $post_array
	 *
	 * @return bool
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function post_validation( $maybe_empty, array $post_array ) {
		if ( $this->is_valid_update( $post_array['post_status'], $post_array['post_type'] ) ) {
			$custom_post = $this->get_custom_post_type( $post_array['post_type'] );
			if ( ! empty( $custom_post ) ) {
				$errors = $custom_post->validate_input( $post_array );
				if ( ! empty( $errors ) ) {
					$this->validation_errors = $errors;

					return true;
				} else {
					$this->validation_errors = false;
				}
			}
		}

		return $maybe_empty;
	}

	/**
	 * @param array $data
	 * @param array $post_array
	 *
	 * @return array
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function wp_insert_post_data( array $data, array $post_array ) {
		if ( $this->is_valid_update( $post_array['post_status'], $post_array['post_type'] ) ) {
			$custom_post = $this->get_custom_post_type( $post_array['post_type'] );
			if ( ! empty( $custom_post ) ) {
				$data = $custom_post->filter_post_data( $data, $post_array );
			}
		}

		return $data;
	}

	/**
	 * @param bool $result
	 * @param array $user
	 * @param array $userdata
	 *
	 * @return bool
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function send_email_change_email(
		/** @noinspection PhpUnusedParameterInspection */
		$result, array $user, array $userdata
	) {
		global $typenow;
		if ( $this->is_valid_custom_post_type( $typenow ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * @param bool $result
	 * @param array $user
	 * @param array $userdata
	 *
	 * @return bool
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function send_password_change_email(
		/** @noinspection PhpUnusedParameterInspection */
		$result, array $user, array $userdata
	) {
		global $typenow;
		if ( $this->is_valid_custom_post_type( $typenow ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * @param string $location
	 * @param int $post_id
	 *
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function redirect_post_location(
		/** @noinspection PhpUnusedParameterInspection */
		$location, $post_id
	) {
		if ( ! empty( $this->validation_errors ) ) {
			$location = remove_query_arg( 'message', $location );
			$this->app->set_session( 'validation_errors', $this->validation_errors, 60 );
			$this->app->set_session( $this->get_old_post_session_key(), $this->app->input->post(), 60 );
		} else {
			global $typenow;
			if ( $this->is_valid_custom_post_type( $typenow ) ) {
				$custom_post = $this->get_custom_post_type( $typenow );
				$this->app->set_session( 'updated_message', sprintf( $this->translate( 'Updated %s data.<br>[Back to list page](%s)' ), $this->translate( $custom_post->get_post_type_single_name() ), $custom_post->get_post_type_link() ), 60 );
			}
		}

		return $location;
	}

	/**
	 * setup list
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function setup_list() {
		global $typenow;
		$custom_post = $this->get_custom_post_type( $typenow );
		if ( ! empty( $custom_post ) ) {
			if ( $custom_post->is_support_io() ) {
				$this->add_style_view( 'admin/style/import_custom_post' );
				$this->add_script_view( 'admin/script/import_custom_post', [ 'post_type' => $custom_post->get_post_type() ] );
				$this->setup_modal();
				$this->app->api->add_use_api_name( 'import_custom_post' );
			}
			$custom_post->setup_list();
		}
	}

	/**
	 * setup page
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function setup_page() {
		global $typenow;
		if ( $this->is_valid_custom_post_type( $typenow ) ) {
			$custom_post = $this->get_custom_post_type( $typenow );
			if ( ! empty( $custom_post ) ) {
				$custom_post->setup_page();
			}
		}
	}

	/**
	 * admin notices
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function set_admin_notices() {
		global $typenow;
		if ( $this->is_valid_custom_post_type( $typenow ) ) {
			$validation_errors = $this->app->get_session( 'validation_errors' );
			$updated_message   = $this->app->get_session( 'updated_message' );
			if ( ! empty( $validation_errors ) || ! empty( $updated_message ) ) {
				$this->app->session->delete( 'validation_errors' );
				$this->app->session->delete( 'updated_message' );
				$custom_post = $this->get_custom_post_type( $typenow );
				if ( ! empty( $custom_post ) ) {
					if ( ! empty( $validation_errors ) ) {
						foreach ( $validation_errors as $key => $validation_error ) {
							foreach ( $custom_post->get_error_messages( $key, $validation_error ) as $message ) {
								$this->app->add_message( $message, 'validation', true, false );
							}
						}
					}
					if ( ! empty( $updated_message ) ) {
						$this->app->add_message( $updated_message, 'updated', false, false );
					}
				}
			}
		}
	}

	/**
	 * @param WP_Post $post
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function edit_form_after_title( WP_Post $post ) {
		if ( $this->is_valid_custom_post_type( $post->post_type ) ) {
			$custom_post = $this->get_custom_post_type( $post->post_type );
			if ( ! empty( $custom_post ) ) {
				if ( $this->app->is_valid_package( 'api' ) ) {
					$this->app->api->set_use_all_api_flag( true );
				}
				$this->app->session->delete( 'validation_errors' );
				$this->app->session->delete( 'updated_message' );
				$custom_post->output_edit_form( $post );
			}
		}
	}

	/**
	 * @param WP_Post $post
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function edit_form_after_editor( WP_Post $post ) {
		if ( $this->is_valid_custom_post_type( $post->post_type ) ) {
			$custom_post = $this->get_custom_post_type( $post->post_type );
			if ( ! empty( $custom_post ) ) {
				$custom_post->output_after_editor( $post );
			}
		}
	}

	/**
	 * @return array
	 */
	protected function get_namespaces() {
		return [
			$this->app->define->plugin_namespace . '\\Classes\\Models\\Custom_Post',
		];
	}

	/**
	 * @return bool
	 */
	protected function is_common_cache_class_settings() {
		return true;
	}

	/**
	 * @return string
	 */
	protected function get_instanceof() {
		return '\WP_Framework_Custom_Post\Interfaces\Custom_Post';
	}

	/**
	 * @return \WP_Framework_Custom_Post\Interfaces\Custom_Post[]
	 */
	public function get_custom_posts() {
		if ( ! isset( $this->custom_posts ) ) {
			$this->custom_posts        = $this->get_class_list();
			$post_types                = array_map( function ( $data ) {
				/** @var \WP_Framework_Custom_Post\Interfaces\Custom_Post $data */
				return $data->get_post_type();
			}, $this->custom_posts );
			$this->custom_posts_mapper = array_map( function ( $data ) {
				/** @var \WP_Framework_Custom_Post\Interfaces\Custom_Post $data */
				return $data->get_post_type_slug();
			}, $this->custom_posts );
			$this->custom_posts        = array_combine( $post_types, $this->custom_posts );
			$this->custom_posts_mapper = array_combine( $this->custom_posts_mapper, $post_types );
		}

		return $this->custom_posts;
	}

	/**
	 * @return array
	 */
	public function get_custom_post_types() {
		$custom_posts = $this->get_custom_posts();

		return array_keys( $custom_posts );
	}

	/**
	 * @param string $slug
	 *
	 * @return \WP_Framework_Custom_Post\Interfaces\Custom_Post|null
	 */
	public function get_post_type_by_slug( $slug ) {
		if ( ! isset( $this->custom_posts_mapper[ $slug ] ) ) {
			return null;
		}

		return $this->get_custom_post_type( $this->custom_posts_mapper[ $slug ] );
	}

	/**
	 * @param string|array $post_type
	 *
	 * @return bool
	 */
	public function is_valid_custom_post_type( $post_type ) {
		if ( empty( $post_type ) || is_array( $post_type ) ) {
			return false;
		}

		$custom_posts = $this->get_custom_posts();

		return isset( $custom_posts[ $post_type ] );
	}

	/**
	 * @param string|array $post_type
	 *
	 * @return \WP_Framework_Custom_Post\Interfaces\Custom_Post|null
	 */
	public function get_custom_post_type( $post_type ) {
		if ( ! $this->is_valid_custom_post_type( $post_type ) ) {
			return null;
		}

		$custom_posts = $this->get_custom_posts();

		return $custom_posts[ $post_type ];
	}

	/**
	 * @param string $post_status
	 * @param string $post_type
	 * @param bool $untrash
	 *
	 * @return bool
	 */
	private function is_valid_update( $post_status, $post_type, $untrash = false ) {
		return ! $this->app->utility->is_autosave() && in_array( $post_status, $this->get_valid_statuses(), true ) && $this->is_valid_custom_post_type( $post_type ) && ( ( 'untrash' === $this->app->input->get( 'action' ) ) === $untrash );
	}

	/**
	 * @return array
	 */
	private function get_valid_statuses() {
		return [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
		];
	}

	/**
	 * @return array|false
	 */
	public function get_validation_errors() {
		return $this->validation_errors;
	}

	/**
	 * delete posts
	 */
	public function uninstall() {
		foreach ( $this->get_custom_post_types() as $post_type ) {
			$this->wp_table( 'posts' )->where( 'post_type', $post_type )->chunk_for_delete( 1000, function ( $posts ) {
				foreach ( $posts as $post ) {
					wp_delete_post( $post['ID'] );
				}
			} );
		}
	}
}
