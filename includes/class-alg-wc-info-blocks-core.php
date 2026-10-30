<?php
/**
 * Add Custom Messages Anywhere in WooCommerce - Core Class
 *
 * @version 2.1.0
 * @since   1.0.0
 *
 * @author Algoritmika Ltd
 *
 * @package Algoritmika\WC_Info_Blocks
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Info_Blocks_Core' ) ) :

	/**
	 * Alg_WC_Info_Blocks_Core class.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	class Alg_WC_Info_Blocks_Core {

		/**
		 * Info blocks.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var array
		 */
		public $info_blocks;

		/**
		 * Constructor.
		 *
		 * @version 2.0.0
		 * @since   1.0.0
		 *
		 * @todo (v1.0.0) Test with WPML.
		 * @todo (feature) Country (by IP) visibility?
		 */
		public function __construct() {
			// WP stuff.
			add_action( 'init', array( $this, 'create_info_blocks_post_type' ), 10 );
			add_action( 'admin_menu', array( $this, 'add_menu_link' ), PHP_INT_MAX );

			// Filters to actions.
			require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-info-blocks-filters-to-actions.php';

			// Displaying info blocks.
			$this->init_info_blocks();
			foreach ( $this->info_blocks as $position => $info_block_priority_data ) {
				foreach ( $info_block_priority_data as $priority => $info_block_data ) {
					add_action( $position, array( $this, 'alg_wc_display_info_blocks' ), $priority );
					do_action( 'alg_wc_info_blocks_position_action_added', $position );
				}
			}

			// Info block (display) shortcode.
			add_shortcode( 'alg_wc_info_block', array( $this, 'alg_wc_info_block_shortcode' ) );

			// Shortcodes.
			require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-info-blocks-shortcodes.php';
		}

		/**
		 * Current filter priority.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function current_filter_priority() {
			global $wp_filter;
			$current_filter_data = $wp_filter[ current_filter() ];
			if ( class_exists( 'WP_Hook' ) && is_a( $current_filter_data, 'WP_Hook' ) ) {
				// Since WordPress v4.7.
				return $current_filter_data->current_priority();
			} else {
				// Before WordPress v4.7.
				return key( $current_filter_data );
			}
		}

		/**
		 * Info block shortcode.
		 *
		 * @version 2.1.0
		 * @since   1.4.0
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string
		 */
		public function alg_wc_info_block_shortcode( $atts ) {
			$block_id = (
				isset( $atts['id'] ) ?
				$atts['id'] :
				(
					(
						isset( $atts['slug'] ) &&
						( $post = get_page_by_path( $atts['slug'], OBJECT, 'alg_wc_info_block' ) ) // phpcs:ignore WordPress.CodeAnalysis.AssignmentInTernaryCondition.FoundInTernaryCondition, Squiz.PHP.DisallowMultipleAssignments.Found
					) ?
					$post->ID :
					false
				)
			);
			if ( empty( $block_id ) ) {
				return '';
			}

			$content = $this->get_info_block_content(
				$this->get_info_block_data( $block_id ),
				$block_id,
				'shortcode',
				'NA'
			);
			if ( apply_filters( 'alg_wc_info_blocks_display_sanitize_content', false ) ) {
				return wp_kses_post( $content );
			} else {
				return $content;
			}
		}

		/**
		 * Get info block content.
		 *
		 * @version 2.0.0
		 * @since   1.4.0
		 *
		 * @param array      $block_data The block data.
		 * @param int        $block_id   The block ID.
		 * @param string     $position   The position.
		 * @param int|string $priority   The priority.
		 *
		 * @return string
		 *
		 * @todo (dev) Add option to choose between (probably for each block individually): a) `do_shortcode()`, b) `apply_filters( 'the_content' )` or c) no filtering at all?
		 */
		public function get_info_block_content( $block_data, $block_id = '', $position = '', $priority = '' ) {
			if ( ! $this->is_block_visible( $block_data ) ) {
				return '';
			}

			remove_shortcode( 'alg_wc_info_block' );
			$result = do_shortcode( $block_data['content'] );
			add_shortcode( 'alg_wc_info_block', array( $this, 'alg_wc_info_block_shortcode' ) );

			// Debug (optional).
			if (
				isset( $_GET['alg_wc_ib_debug'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				current_user_can( 'manage_woocommerce' ) // phpcs:ignore WordPress.WP.Capabilities.Unknown
			) {
				$result .= '<span style="font-size:x-small;border:1px dotted red;margin-left:5px;padding:2px;">' .
					sprintf(
						/* Translators: %s: Block position. */
						__( 'Position: %s', 'info-blocks-for-woocommerce' ),
						'<code>' . $position . '</code>'
					) . ' ' .
					sprintf(
						/* Translators: %s: Block position priority. */
						__( 'Priority: %s', 'info-blocks-for-woocommerce' ),
						'<code>' . $priority . '</code>'
					) . ' ' .
					sprintf(
						/* Translators: %s: Block ID. */
						__( 'Info block ID: %s', 'info-blocks-for-woocommerce' ),
						'<code>' . $block_id . '</code>'
					) . ' ' .
				'</span>';
			}

			return $result;
		}

		/**
		 * Is block visible.
		 *
		 * @version 2.1.0
		 * @since   1.1.0
		 *
		 * @param array $block_data The block data.
		 *
		 * @return bool
		 */
		public function is_block_visible( $block_data ) {
			$post_id = get_the_ID();

			// Posts (i.e., products).
			if ( '' !== $block_data['required_post_ids'] ) {
				$required_post_ids = array_map( 'trim', explode( ',', $block_data['required_post_ids'] ) );
				if ( ! in_array( (int) $post_id, array_map( 'intval', $required_post_ids ), true ) ) {
					return false;
				}
			}
			if ( '' !== $block_data['hidden_post_ids'] ) {
				$hidden_post_ids = array_map( 'trim', explode( ',', $block_data['hidden_post_ids'] ) );
				if ( in_array( (int) $post_id, array_map( 'intval', $hidden_post_ids ), true ) ) {
					return false;
				}
			}

			// Product categories and tags.
			if (
				(
					! empty( $block_data['required_product_cat_ids'] ) &&
					! has_term( $block_data['required_product_cat_ids'], 'product_cat', $post_id )
				) ||
				(
					! empty( $block_data['hidden_product_cat_ids'] ) &&
					has_term( $block_data['hidden_product_cat_ids'], 'product_cat', $post_id )
				) ||
				(
					! empty( $block_data['required_product_tag_ids'] ) &&
					! has_term( $block_data['required_product_tag_ids'], 'product_tag', $post_id )
				) ||
				(
					! empty( $block_data['hidden_product_tag_ids'] ) &&
					has_term( $block_data['hidden_product_tag_ids'], 'product_tag', $post_id )
				)
			) {
				return false;
			}

			// User roles.
			if (
				(
					! empty( $block_data['required_user_roles'] ) &&
					! $this->user_has_role( $block_data['required_user_roles'] )
				) ||
				(
					! empty( $block_data['hidden_user_roles'] ) &&
					$this->user_has_role( $block_data['hidden_user_roles'] )
				)
			) {
				return false;
			}

			// Cart amount.
			if (
				(
					'' !== $block_data['min_cart_amount'] &&
					$this->get_cart_amount() < (float) $block_data['min_cart_amount']
				) ||
				(
					'' !== $block_data['max_cart_amount'] &&
					$this->get_cart_amount() > (float) $block_data['max_cart_amount']
				)
			) {
				return false;
			}

			// It's visible...
			return true;
		}

		/**
		 * Get cart amount.
		 *
		 * @version 2.0.4
		 * @since   2.0.4
		 */
		public function get_cart_amount() {
			if ( empty( WC()->cart ) ) {
				return 0.0;
			}
			return (float) WC()->cart->get_subtotal();
		}

		/**
		 * User has role.
		 *
		 * @version 2.0.3
		 * @since   2.0.3
		 *
		 * @param array $roles The roles to check against.
		 *
		 * @return bool
		 */
		public function user_has_role( $roles ) {
			$user       = wp_get_current_user();
			$user_roles = ( $user->ID ? $user->roles : array( 'alg_wc_ib_guest' ) );
			return (bool) array_intersect(
				$roles,
				$user_roles
			);
		}

		/**
		 * Display info blocks.
		 *
		 * @version 2.0.2
		 * @since   1.0.0
		 */
		public function alg_wc_display_info_blocks() {
			$position = current_filter();
			$priority = $this->current_filter_priority();
			if ( $position && $priority ) {
				foreach ( $this->info_blocks[ $position ][ $priority ] as $block_id => $block_data ) {
					$content = $this->get_info_block_content(
						$block_data,
						$block_id,
						$position,
						$priority
					);
					if ( apply_filters( 'alg_wc_info_blocks_display_sanitize_content', false ) ) {
						echo wp_kses_post( $content );
					} else {
						echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			}
		}

		/**
		 * Get info block data.
		 *
		 * @version 2.0.4
		 * @since   1.4.0
		 *
		 * @param int $block_id The ID of the info block.
		 *
		 * @return mixed
		 */
		public function get_info_block_data( $block_id ) {
			return array(
				'content'                  => get_post_field( 'post_content', $block_id ),
				'required_post_ids'        => get_post_meta( $block_id, '_required_post_ids', true ),
				'hidden_post_ids'          => get_post_meta( $block_id, '_hidden_post_ids', true ),
				'required_product_cat_ids' => get_post_meta( $block_id, '_required_product_cat_ids', true ),
				'hidden_product_cat_ids'   => get_post_meta( $block_id, '_hidden_product_cat_ids', true ),
				'required_product_tag_ids' => get_post_meta( $block_id, '_required_product_tag_ids', true ),
				'hidden_product_tag_ids'   => get_post_meta( $block_id, '_hidden_product_tag_ids', true ),
				'required_user_roles'      => get_post_meta( $block_id, '_required_user_roles', true ),
				'hidden_user_roles'        => get_post_meta( $block_id, '_hidden_user_roles', true ),
				'min_cart_amount'          => get_post_meta( $block_id, '_min_cart_amount', true ),
				'max_cart_amount'          => get_post_meta( $block_id, '_max_cart_amount', true ),
			);
		}

		/**
		 * Init info blocks.
		 *
		 * @version 2.1.0
		 * @since   1.0.0
		 */
		public function init_info_blocks() {
			$this->info_blocks = array();

			$args = array(
				'post_type'      => 'alg_wc_info_block',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
			);

			$loop = new WP_Query( $args );

			if ( $loop->have_posts() ) {
				foreach ( $loop->posts as $block_id ) {
					$positions = get_post_meta( $block_id, '_positions', true );
					if ( ! empty( $positions ) ) {
						$position_priorities = get_post_meta( $block_id, '_position_priorities', true );
						foreach ( $positions as $position ) {
							$position_priority = (
								$position_priorities[ $position ] ??
								10
							);
							$this->info_blocks[ $position ][ $position_priority ][ $block_id ] = $this->get_info_block_data( $block_id );
						}
					}
				}
			}
		}

		/**
		 * Add menu link.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_menu_link() {
			add_submenu_page(
				'woocommerce',
				__( 'Info Blocks', 'info-blocks-for-woocommerce' ),
				__( 'Info Blocks', 'info-blocks-for-woocommerce' ),
				'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
				'edit.php?post_type=alg_wc_info_block'
			);
		}

		/**
		 * Create info blocks post type.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @see https://codex.wordpress.org/Function_Reference/register_post_type
		 *
		 * @todo (dev) Add info: "Title is for admin only".
		 * @todo (dev) Re-check `capabilities` and `capability_type`.
		 */
		public function create_info_blocks_post_type() {
			register_post_type(
				'alg_wc_info_block',
				array(
					'labels'             => array(
						'name'               => _x( 'Info Blocks', 'post type general name', 'info-blocks-for-woocommerce' ),
						'singular_name'      => _x( 'Info Block', 'post type singular name', 'info-blocks-for-woocommerce' ),
						'menu_name'          => _x( 'Info Blocks', 'admin menu', 'info-blocks-for-woocommerce' ),
						'name_admin_bar'     => _x( 'Info Block', 'add new on admin bar', 'info-blocks-for-woocommerce' ),
						'add_new'            => _x( 'Add New', 'info block', 'info-blocks-for-woocommerce' ),
						'add_new_item'       => __( 'Add New Info Block', 'info-blocks-for-woocommerce' ),
						'new_item'           => __( 'New Info Block', 'info-blocks-for-woocommerce' ),
						'edit_item'          => __( 'Edit Info Block', 'info-blocks-for-woocommerce' ),
						'view_item'          => __( 'View Info Block', 'info-blocks-for-woocommerce' ),
						'all_items'          => __( 'All Info Blocks', 'info-blocks-for-woocommerce' ),
						'search_items'       => __( 'Search Info Blocks', 'info-blocks-for-woocommerce' ),
						'parent_item_colon'  => __( 'Parent Info Blocks:', 'info-blocks-for-woocommerce' ),
						'not_found'          => __( 'No info blocks found.', 'info-blocks-for-woocommerce' ),
						'not_found_in_trash' => __( 'No info blocks found in Trash.', 'info-blocks-for-woocommerce' ),
					),
					'description'        => __( 'WooCommerce custom info block', 'info-blocks-for-woocommerce' ),
					'public'             => false,
					'publicly_queryable' => false,
					'show_ui'            => true,
					'show_in_menu'       => false,
					'query_var'          => false,
					'map_meta_cap'       => true,
					'has_archive'        => false,
					'hierarchical'       => false,
					'menu_position'      => null,
					'supports'           => array( 'title', 'editor', 'revisions' ),
				)
			);
		}
	}

endif;

return new Alg_WC_Info_Blocks_Core();
