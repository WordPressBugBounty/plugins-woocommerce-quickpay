<?php

namespace QuickpayPSP\WooCommerce\Controllers;

final class SubscriptionActionsController {

	private OrderActionsController $order_actions;

	public function __construct( OrderActionsController $order_actions ) {
		$this->order_actions = $order_actions;
	}

	public function register(): void {
		add_filter( 'woocommerce_subscription_bulk_actions', [ $this, 'bulk_actions' ], 20 );

		// Bulk action handler – HPOS
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders--shop_subscription', [ $this, 'handle_bulk_action' ], 10, 3 );

		// Bulk action handler – classic post table
		add_filter( 'handle_bulk_actions-edit-shop_subscription', [ $this, 'handle_bulk_action' ], 10, 3 );
	}

	public function bulk_actions( array $actions ): array {
		if ( apply_filters( 'woocommerce_quickpay_allow_subscriptions_bulk_actions', current_user_can( 'manage_woocommerce' ) ) ) {
			$actions['quickpay_create_payment_link'] = esc_html__( 'QuickPay: Create payment link', 'woocommerce-quickpay' );
		}

		return $actions;
	}

	/**
	 * Handles the bulk action for creating payment links on subscriptions.
	 *
	 * @param string|null $redirect_url
	 * @param string|null $action
	 * @param int[] $subscription_ids
	 *
	 * @return string|null
	 */
	public function handle_bulk_action( ?string $redirect_url, ?string $action, array $subscription_ids ): ?string {
		if ( 'quickpay_create_payment_link' !== ( $action ?? '' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return $redirect_url;
		}

		$processed = 0;
		$skipped   = 0;

		foreach ( $subscription_ids as $subscription_id ) {
			$subscription = wc_get_order( $subscription_id );

			if ( ! $subscription instanceof \WC_Abstract_Order ) {
				continue;
			}

			try {
				if ( $this->order_actions->create_payment_link_for_order( $subscription ) ) {
					$processed ++;
				} else {
					$skipped ++;
				}
			} catch ( \Exception $e ) {
				$skipped ++;
			}
		}

		return add_query_arg(
			[
				'quickpay_bulk_payment_link_processed' => $processed,
				'quickpay_bulk_payment_link_skipped'   => $skipped,
			],
			$redirect_url
		);
	}
}
