<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use RuntimeException;
use WC_Order;
use WC_Subscription;

class SubscriptionEarlyRenewalsController {
	private SubscriptionsFacade $subscriptions_facade;

	public function __construct( SubscriptionsFacade $subscriptions_facade ) {
		$this->subscriptions_facade = $subscriptions_facade;
	}

	public function maybe_check_payment_status( WC_Subscription $subscription, WC_Order $renewal_order, $response ): void {
		if ( $this->should_payment_status_be_checked( $response, $renewal_order ) ) {
			$max_checks           = (int) apply_filters( 'woocommerce_quickpay_early_renewal_payment_checks_limit', 10 );
			$delay_between_checks = (int) apply_filters( 'woocommerce_quickpay_early_renewal_payment_checks_delay', 3 );

			try {
				$is_paid      = false;
				$checks_count = 1;
				while ( ! $is_paid && $checks_count <= $max_checks ) {
					$is_paid = $this->check_if_paid( $renewal_order, $max_checks, $checks_count, $delay_between_checks );
					$checks_count ++;
				}
			} catch ( RuntimeException $e ) {
				// NOOP
			}
		}
	}

	/**
	 * @param WC_Order $renewal_order
	 * @param int $max_checks
	 * @param int $checks_count
	 * @param int $delay_between_checks
	 *
	 * @return bool
	 */
	protected function check_if_paid( WC_Order $renewal_order, int $max_checks, int $checks_count, int $delay_between_checks ): bool {
		// Refresh order data
		$renewal_order->get_data_store()->read( $renewal_order );

		if ( $renewal_order->has_status( 'failed' ) ) {
			throw new RuntimeException( 'Payment failed' );
		}

		if ( $renewal_order->needs_payment() ) {
			if ( $delay_between_checks > 0 ) {
				sleep( $delay_between_checks );
			}

			return false; // Not paid yet, continue loop
		}

		return true;
	}

	/**
	 * @param $response
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	private function should_payment_status_be_checked( $response, WC_Order $order ): bool {
		$check = $this->subscriptions_facade->order_contains_early_renewal( $order ) && $order->needs_payment();

		return (bool) apply_filters( 'woocommerce_quickpay_scheduled_subscription_payment_check_status_after_payment', $check, $response, $order );
	}
}
