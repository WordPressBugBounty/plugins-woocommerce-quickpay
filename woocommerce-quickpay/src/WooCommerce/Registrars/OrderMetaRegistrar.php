<?php

namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Controllers\OrderMetaController;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;

class OrderMetaRegistrar {

	/**
	 * @var OrderMetaController
	 */
	protected $controller;

	/**
	 * @param OrderMetaController $controller
	 */
	public function __construct( OrderMetaController $controller ) {
		$this->controller = $controller;
	}

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
	}

	/**
	 * add_meta_boxes function.
	 *
	 * Adds the action meta box inside the single order view.
	 *
	 * @param string $post_type
	 * @param \WP_Post|\WC_Order $post_or_order
	 *
	 * @return void
	 */
	public function add_meta_boxes( $post_type, $post_or_order ): void {
			$screen_orders = WooCommerceContext::get_edit_order_screen_id();
			$screen_subs   = WooCommerceContext::get_edit_subscription_screen_id();

			if ( WooCommerceContext::is_current_admin_screen( $screen_orders, $screen_subs ) ) {
			if ( ( $order = woocommerce_quickpay_get_order( $post_or_order ) ) && OrderPaymentsUtils::is_order_using_quickpay( $order ) ) {
				add_meta_box(
					'quickpay-payment-actions',
					esc_html__( 'QuickPay Payment', 'woocommerce-quickpay' ),
					[ $this->controller, 'meta_box_payment' ],
					$screen_orders,
					'side',
					'high'
				);
				add_meta_box(
					'quickpay-payment-actions',
					esc_html__( 'QuickPay Subscription', 'woocommerce-quickpay' ),
					[ $this->controller, 'meta_box_subscription' ],
					$screen_subs,
					'side',
					'high'
				);
			}
		}
	}
}
