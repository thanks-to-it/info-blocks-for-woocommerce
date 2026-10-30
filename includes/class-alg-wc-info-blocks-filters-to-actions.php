<?php
/**
 * Add Custom Messages Anywhere in WooCommerce - Filters to Actions Class
 *
 * @version 2.1.0
 * @since   1.3.0
 *
 * @author Algoritmika Ltd
 *
 * @package Algoritmika\WC_Info_Blocks
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Info_Blocks_Filters_To_Actions' ) ) :

	/**
	 * Alg_WC_Info_Blocks_Filters_To_Actions class.
	 *
	 * @version 2.1.0
	 * @since   1.3.0
	 */
	class Alg_WC_Info_Blocks_Filters_To_Actions {

		/**
		 * Action filters.
		 *
		 * @version 2.0.0
		 * @since   2.0.0
		 *
		 * @var array
		 */
		public $action_filters;

		/**
		 * Constructor.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 */
		public function __construct() {
			$this->action_filters = array(
				'alg_wc_ib_before_content'         => 'the_content',
				'alg_wc_ib_after_content'          => 'the_content',
				'alg_wc_ib_instead_content'        => 'the_content',
				'alg_wc_ib_before_product_description_heading' => 'woocommerce_product_description_heading',
				'alg_wc_ib_after_product_description_heading' => 'woocommerce_product_description_heading',
				'alg_wc_ib_instead_product_description_heading' => 'woocommerce_product_description_heading',
				'alg_wc_ib_before_product_additional_information_heading' => 'woocommerce_product_additional_information_heading',
				'alg_wc_ib_after_product_additional_information_heading' => 'woocommerce_product_additional_information_heading',
				'alg_wc_ib_instead_product_additional_information_heading' => 'woocommerce_product_additional_information_heading',
				'alg_wc_ib_before_price_html'      => 'woocommerce_get_price_html',
				'alg_wc_ib_after_price_html'       => 'woocommerce_get_price_html',
				'alg_wc_ib_instead_price_html'     => 'woocommerce_get_price_html',
				'alg_wc_ib_after_empty_price_html' => 'woocommerce_empty_price_html',
			);

			add_action(
				'alg_wc_info_blocks_position_action_added',
				array( $this, 'add_action_filter' )
			);
		}

		/**
		 * Add action filter.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $action Action name.
		 *
		 * @return void
		 */
		public function add_action_filter( $action ) {
			if ( isset( $this->action_filters[ $action ] ) ) {
				add_filter(
					$this->action_filters[ $action ],
					array( $this, $action ),
					PHP_INT_MAX
				);
				unset( $this->action_filters[ $action ] );
			}
		}

		/**
		 * Before product additional information heading.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_before_product_additional_information_heading( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_before_product_additional_information_heading',
				'before'
			);
		}

		/**
		 * After product additional information heading.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_after_product_additional_information_heading( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_after_product_additional_information_heading',
				'after'
			);
		}

		/**
		 * Instead of product additional information heading.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_instead_product_additional_information_heading( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_instead_product_additional_information_heading',
				'instead'
			);
		}

		/**
		 * Before product description heading.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_before_product_description_heading( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_before_product_description_heading',
				'before'
			);
		}

		/**
		 * After product description heading.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_after_product_description_heading( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_after_product_description_heading',
				'after'
			);
		}

		/**
		 * Instead of product description heading.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_instead_product_description_heading( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_instead_product_description_heading',
				'instead'
			);
		}

		/**
		 * After empty price HTML.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_after_empty_price_html( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_after_empty_price_html',
				'after',
				array( 'is_frontend' )
			);
		}

		/**
		 * Before price HTML.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_before_price_html( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_before_price_html',
				'before',
				array( 'is_frontend' )
			);
		}

		/**
		 * After price HTML.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_after_price_html( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_after_price_html',
				'after',
				array( 'is_frontend' )
			);
		}

		/**
		 * Instead of price HTML.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_instead_price_html( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_instead_price_html',
				'instead',
				array( 'is_frontend' )
			);
		}

		/**
		 * Before content.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_before_content( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_before_content',
				'before',
				array( 'is_product' )
			);
		}

		/**
		 * After content.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_after_content( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_after_content',
				'after',
				array( 'is_product' )
			);
		}

		/**
		 * Instead of content.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 */
		public function alg_wc_ib_instead_content( $content ) {
			return $this->alg_wc_ib_filter_to_action(
				$content,
				'alg_wc_ib_instead_content',
				'instead',
				array( 'is_product' )
			);
		}

		/**
		 * Filter to action.
		 *
		 * @version 2.1.0
		 * @since   1.3.0
		 *
		 * @param string $content                    Content.
		 * @param string $action                     Action.
		 * @param string $before_or_after_or_instead Before, after or instead.
		 * @param array  $checks                     Checks.
		 *
		 * @return string
		 */
		public function alg_wc_ib_filter_to_action( $content, $action, $before_or_after_or_instead, $checks = array() ) {
			if (
				(
					! in_array( 'is_product', $checks, true ) ||
					( function_exists( 'is_product' ) && is_product() )
				) &&
				(
					! in_array( 'is_frontend', $checks, true ) ||
					( ! is_admin() || wp_doing_ajax() )
				)
			) {
				ob_start();
				do_action( $action ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
				$result = ob_get_clean();
				switch ( $before_or_after_or_instead ) {
					case 'before':
						return $result . $content;
					case 'after':
						return $content . $result;
					case 'instead':
						return $result;
				}
			}
			return $content;
		}
	}

endif;

return new Alg_WC_Info_Blocks_Filters_To_Actions();
