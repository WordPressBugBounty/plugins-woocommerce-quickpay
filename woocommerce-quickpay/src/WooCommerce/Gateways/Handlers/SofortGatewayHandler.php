<?php
declare(strict_types=1);

namespace QuickpayPSP\WooCommerce\Gateways\Handlers;

use QuickpayPSP\WooCommerce\Gateways\Contracts\GatewayHandlerInterface;
use QuickpayPSP\WooCommerce\Controllers\CallbackController;
use WC_Order;

/**
 * Sofort payments do not send an authorize callback — only a capture callback is sent.
 * This handler intercepts the capture callback for Sofort orders and triggers the
 * payment_complete flow that would normally happen on authorize.
 */
class SofortGatewayHandler implements GatewayHandlerInterface {
	private CallbackController $callback_controller;

	public function __construct( CallbackController $callback_controller ) {
		$this->callback_controller = $callback_controller;
	}

	public function gateway_id(): string {
		return 'sofort';
	}

	public function register(): void {
		add_action( 'woocommerce_quickpay_accepted_callback_status_capture', [ $this, 'handle_capture' ], 10, 2 );
	}

	/**
	 * Sofort skips the authorize step, so on capture we complete the payment
	 * and fire the authorized actions to ensure order state is correct.
	 *
	 * @param WC_Order $order
	 * @param object   $transaction
	 */
	public function handle_capture( WC_Order $order, object $transaction ): void {
		if ( $order->get_payment_method() !== $this->gateway_id() ) {
			return;
		}

		// Sofort sends capture instead of authorize — trigger the authorization logic.
		$this->callback_controller->handle_authorize( $order, $transaction, null );
		$this->callback_controller->handle_payment_authorized( $order, $transaction );
	}
}
