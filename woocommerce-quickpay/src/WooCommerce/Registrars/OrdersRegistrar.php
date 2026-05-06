<?php
namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Controllers\OrderController;

/**
 * Registers order-related hooks.
 */
class OrdersRegistrar {
	private OrderController $controller;
	private array $gateway_ids;

	public function __construct( OrderController $controller, array $gateway_ids = [] ) {
		$this->controller  = $controller;
		$this->gateway_ids = $gateway_ids;
	}

	public function register(): void {
		// Reset failed payment count
		add_action( 'woocommerce_order_status_completed', [ $this->controller, 'reset_failed_payment_count' ], 10 );
		add_action( 'woocommerce_order_status_processing', [ $this->controller, 'reset_failed_payment_count' ], 10 );

		// Maybe cancel transaction
		add_action( 'woocommerce_order_status_cancelled', [ $this->controller, 'maybe_cancel_transaction' ], 10, 2 );

		// Auto-complete renewals on authorization
		add_action( 'woocommerce_quickpay_callback_payment_authorized', [ $this->controller, 'on_payment_authorized' ], 10 );

		// Capture on order completion
		add_action( 'woocommerce_order_status_completed', [ $this->controller, 'maybe_capture_on_order_completed' ], 20 );

		// Pre-order completion payments — registered per gateway ID
		foreach ( $this->gateway_ids as $gateway_id ) {
			add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $gateway_id, [ $this->controller, 'process_pre_order_payments' ] );
		}
	}
}
