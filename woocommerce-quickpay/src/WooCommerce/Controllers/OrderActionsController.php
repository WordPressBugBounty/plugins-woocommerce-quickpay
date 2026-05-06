<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;

final class OrderActionsController {

	private OrderController $order_controller;
	private SubscriptionsFacade $subscriptions_facade;

	public function __construct( OrderController $order_controller, SubscriptionsFacade $subscriptions_facade ) {
		$this->order_controller     = $order_controller;
		$this->subscriptions_facade = $subscriptions_facade;
	}

	public function register(): void {
		add_filter( 'woocommerce_order_actions', [ $this, 'order_actions' ], 10, 2 );
		add_action( 'woocommerce_order_action_quickpay_create_payment_link', [ $this, 'order_action_quickpay_create_payment_link' ], 50, 2 );

		// Bulk actions – HPOS
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', [ $this, 'register_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', [ $this, 'handle_bulk_action' ], 10, 3 );

		// Bulk actions – classic post table
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'register_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this, 'handle_bulk_action' ], 10, 3 );

		// Capture recurring bulk action result notice
		add_action( 'admin_notices', [ $this, 'capture_recurring_admin_notice' ] );

	}

	/**
	 * Creates a payment link for an order. Pure logic — no side effects.
	 *
	 * @param \WC_Order|\WC_Subscription $order
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function create_payment_link_for_order( $order ): bool {
		if ( ! $order ) {
			return false;
		}

		if ( ! apply_filters( 'woocommerce_quickpay_order_action_create_payment_link_for_order', ! $order->is_paid(), $order ) ) {
			return false;
		}

		return $this->order_controller->create_payment_link( $order );
	}

	/**
	 * WooCommerce order action hook handler — creates a payment link and shows admin notices.
	 *
	 * @param \WC_Order|\WC_Subscription $order
	 *
	 * @return void
	 */
	public function order_action_quickpay_create_payment_link( $order ): void {
		if ( ! $order ) {
			return;
		}

		if ( ! apply_filters( 'woocommerce_quickpay_order_action_create_payment_link_for_order', ! $order->is_paid(), $order ) ) {
			woocommerce_quickpay_add_admin_notice( sprintf( esc_html__( 'Payment link creation skipped for order #%s', 'woocommerce-quickpay' ), $order->get_id() ), 'error' );

			return;
		}

		try {
			if ( $this->order_controller->create_payment_link( $order ) ) {
				woocommerce_quickpay_add_admin_notice( sprintf(
				/* translators: %d: number of orders */
					_n( 'Payment link created for %d order.', 'Payment links created for %d orders.', 1, 'woocommerce-quickpay' ),
					1
				), 'success' );
			}
		} catch ( \Exception $e ) {
			woocommerce_quickpay_add_admin_notice( sprintf( esc_html__( 'Payment link could not be created for order #%1$s. Error: %2$s', 'woocommerce-quickpay' ), $order->get_id(), $e->getMessage() ), 'error' );
		}
	}

	public function send_customer_payment_link( $payment_link, $order ): void {

	}


	/**
	 * Handles the bulk action for creating payment links.
	 *
	 * @param string $redirect_url
	 * @param string $action
	 * @param int[] $order_ids
	 *
	 * @return string
	 */
	public function handle_bulk_action( string $redirect_url, string $action, array $order_ids ): string {
		if ( 'quickpay_capture_recurring' === $action && current_user_can( 'manage_woocommerce' ) ) {
			$processed = $this->bulk_capture_recurring( $order_ids );

			return add_query_arg( [ 'quickpay_bulk_capture_recurring_processed' => $processed ], $redirect_url );
		}

		if ( 'quickpay_create_payment_link' !== $action ) {
			return $redirect_url;
		}

		$processed = 0;
		$skipped   = 0;

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order instanceof \WC_Abstract_Order ) {
				continue;
			}

			try {
				if ( $this->create_payment_link_for_order( $order ) ) {
					$processed ++;
				} else {
					$skipped ++;
				}
			} catch ( \Exception $e ) {
				$skipped ++;
			}
		}

		$redirect_url = add_query_arg(
			[
				'quickpay_bulk_payment_link_processed' => $processed,
				'quickpay_bulk_payment_link_skipped'   => $skipped,
			],
			$redirect_url
		);

		return $redirect_url;
	}

	/**
	 * Adds custom actions
	 *
	 * @param array $actions
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	public function order_actions( array $actions, $order ): array {
		if ( ! $order instanceof \WC_Abstract_Order ) {
			return $actions;
		}

		$actions['quickpay_create_payment_link'] = esc_html__( 'QuickPay: Create payment link', 'woocommerce-quickpay' );

		return $actions;
	}

	/**
	 * Adds the bulk action to the orders list table dropdown.
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function register_bulk_actions( array $actions ): array {
		if ( apply_filters( 'woocommerce_quickpay_allow_orders_bulk_actions', current_user_can( 'manage_woocommerce' ) ) ) {
			$actions['quickpay_capture_recurring']   = esc_html__( 'QuickPay: Capture payment and activate subscription', 'woocommerce-quickpay' );
			$actions['quickpay_create_payment_link'] = esc_html__( 'QuickPay: Create payment link', 'woocommerce-quickpay' );
		}

		return $actions;
	}

	/**
	 * Captures recurring payments for renewal orders that still need payment.
	 *
	 * @param int[] $order_ids
	 *
	 * @return int Number of orders processed.
	 */
	private function bulk_capture_recurring( array $order_ids ): int {
		$processed = 0;

		foreach ( $order_ids as $order_id ) {
			$order = woocommerce_quickpay_get_order( $order_id );

			if ( ! $order ) {
				continue;
			}

			if ( $this->subscriptions_facade->is_renewal( $order ) && $order->needs_payment() ) {
				$this->order_controller->scheduled_subscription_payment( (float) $order->get_total(), $order );
				$processed ++;
			}
		}

		return $processed;
	}

	/**
	 * Shows an admin notice after the capture recurring bulk action.
	 */
	public function capture_recurring_admin_notice(): void {
		$processed = isset( $_REQUEST['quickpay_bulk_capture_recurring_processed'] ) ? (int) $_REQUEST['quickpay_bulk_capture_recurring_processed'] : null;

		if ( $processed === null ) {
			return;
		}

		woocommerce_quickpay_add_admin_notice(
			sprintf(
				/* translators: %d: number of orders processed */
				_n( 'QuickPay: Capture initiated for %d renewal order.', 'QuickPay: Capture initiated for %d renewal orders.', $processed, 'woocommerce-quickpay' ),
				$processed
			)
		);
	}
}
