<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\Application\Services\PaymentLinkService;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\QuickPay\Api\SubscriptionClient;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use WC_Order;

/**
 * Handles the checkout payment flow — creates/retrieves a payment link and returns
 * the WooCommerce redirect result array.
 */
final class PaymentController {

	private PaymentLinkService $payment_link_service;
	private SubscriptionsFacade $subscriptions_facade;

	public function __construct( PaymentLinkService $payment_link_service, SubscriptionsFacade $subscriptions_facade ) {
		$this->payment_link_service = $payment_link_service;
		$this->subscriptions_facade = $subscriptions_facade;
	}

	/**
	 * Processes a checkout payment for the given order ID.
	 * Returns a WooCommerce result array on success, or null on failure (notice already added).
	 *
	 * @param int $order_id
	 *
	 * @return array|null
	 */
	public function process_payment( int $order_id ): ?array {
		$order = woocommerce_quickpay_get_order( $order_id );

		if ( ! $order ) {
			wc_add_notice( __( 'Order not found.', 'woocommerce-quickpay' ), 'error' );

			return null;
		}

		return $this->prepare_external_window_payment( $order );
	}

	/**
	 * Builds the redirect result for the external payment window.
	 *
	 * @param WC_Order $order
	 *
	 * @return array|null
	 */
	private function prepare_external_window_payment( WC_Order $order ): ?array {
		try {
			$needs_payment = true;
			$redirect_to   = $this->get_return_url( $order );

			// If the order contains a subscription, clean up legacy payment data before creating a new link.
			$transaction_instance = woocommerce_quickpay_get_transaction_instance_by_order( $order );
			if ( $transaction_instance instanceof SubscriptionClient ) {
				OrderPaymentsMeta::delete_payment_id( $order );
				OrderPaymentsMeta::delete_payment_link( $order );
			} // If the order contains a product switch and does not need payment, skip the payment window.
			elseif ( OrderUtils::contains_switch_order( $order ) && ! $order->needs_payment() ) {
				$needs_payment = false;
			}

			if ( $needs_payment ) {
				$redirect_to = $this->payment_link_service->create_link_for_checkout( $order );
			}

			return [
				'result'   => 'success',
				'redirect' => $redirect_to,
			];

		} catch ( QuickPayException $e ) {
			$e->write_to_logs();
			wc_add_notice( $e->getMessage(), 'error' );

			return null;
		}
	}

	/**
	 * Returns the thank-you page URL for the order, falling back to the WC gateway method.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function get_return_url( WC_Order $order ): string {
		return $order->get_checkout_order_received_url();
	}
}
