<?php

namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Controllers\OrderListTableController;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;

class OrderListTableRegistrar  {

	private OrderListTableController $controller;

	public function __construct( OrderListTableController $controller ) {
		$this->controller = $controller;
	}

	public function register(): void {
		if ( ! is_admin() ) {
			return;
		}

		if ( WooCommerceContext::is_hpos_enabled() ) {
			// HPOS: use the dedicated HPOS hooks
			add_filter( 'woocommerce_shop_order_list_table_columns', [ $this->controller, 'add_payment_info_column' ] );
			add_action( 'woocommerce_shop_order_list_table_custom_column', [ $this->controller, 'render_custom_column_data' ], 10, 2 );

			// Subscription support
			add_action( 'manage_woocommerce_page_wc-orders--shop_subscription_custom_column', [ $this->controller, 'render_custom_column_data' ], 10, 2 );
		} else {
			// Classic order table
			add_filter( "manage_edit-shop_order_columns", [ $this->controller, 'add_payment_info_column' ] );
			add_action( "manage_shop_order_posts_custom_column", [ $this->controller, 'render_custom_column_data' ], 10, 2 );

			// Subscription support
			add_action( 'manage_shop_subscription_posts_custom_column', [ $this->controller, 'render_custom_column_data' ], 10, 2 );
		}


	}
}
