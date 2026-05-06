<?php

declare( strict_types=1 );

namespace QuickpayPSP\WooCommerce\Controllers;

use Exception;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use WC_Data_Exception;
use WC_Order;
use WC_Pre_Orders_Order;

/**
 * Orchestrates the callback logic, deciding what to do based on the callback data.
 */
class CallbackController {
	private ApiClientFactory $api_factory;
	private GatewaySettingsProvider $settings;
	private WooCommerceLogger $logger;
	private SubscriptionsFacade $subscriptions;

	public function __construct(
		ApiClientFactory $api_factory,
		GatewaySettingsProvider $settings,
		WooCommerceLogger $logger,
		SubscriptionsFacade $subscriptions
	) {
		$this->api_factory   = $api_factory;
		$this->settings      = $settings;
		$this->logger        = $logger;
		$this->subscriptions = $subscriptions;
	}

	/**
	 * Main entry point for processing a decoded callback.
	 */
	public function handle_callback( object $data, string $raw_body ): void {
		$order_id = $this->get_order_id_from_callback( $data );
		$order    = OrderUtils::get_order( $order_id );

		if ( ! $order ) {
			$this->logger->error( "Callback received for unknown order: #{$order_id}" );

			return;
		}

		$subscription_id = $this->get_subscription_id_from_callback( $data );
		$subscription    = $subscription_id ? $this->subscriptions->get_subscription( $subscription_id ) : null;

		$operation = end( $data->operations );

		if ( ! $data->accepted || ! in_array( (int) $operation->qp_status_code, [ 20000, 20200 ], true ) ) {
			$this->handle_rejected_callback( $order, $data, $operation, $raw_body );

			return;
		}

		do_action( 'woocommerce_quickpay_accepted_callback_before_processing', $order, $data );
		do_action( 'woocommerce_quickpay_accepted_callback_before_processing_status_' . $operation->type, $order, $data );

		try {
			switch ( $operation->type ) {
				case 'authorize':
					$this->handle_authorize( $order, $data, $subscription );
					break;
				case 'capture':
					$this->handle_capture( $order, $data );
					break;
				case 'refund':
					$this->handle_refund( $order, $data, $operation );
					break;
				case 'cancel':
					$this->handle_cancel( $order, $data, $operation, $subscription );
					break;
				case 'recurring':
					$this->handle_recurring( $order, $data );
					break;
			}

			if ( wc_string_to_bool( $this->settings->get( 'quickpay_orders_transaction_info', 'yes' ) ) ) {
				$this->cache_transaction_data( $data );
			}

			do_action( 'woocommerce_quickpay_accepted_callback', $order, $data );
			do_action( 'woocommerce_quickpay_accepted_callback_status_' . $operation->type, $order, $data );

		} catch ( Exception $e ) {
			$this->logger->error( "Error processing callback for order #{$order->get_id()}: " . $e->getMessage() );
		}
	}

	public function handle_authorize( WC_Order $order, object $data, ?WC_Order $subscription ): void {
		// Common authorized logic
		OrderPaymentsMeta::set_transaction_order_id( $order, $data->order_id );
		OrderPaymentsMeta::delete_payment_link( $order );
		OrderPaymentsMeta::delete_payment_id( $order );

		if ( $subscription && strtolower( (string) $data->type ) === 'subscription' ) {
			$this->handle_subscription_authorized( $order, $data, $subscription );
		} else {
			$this->handle_payment_authorized( $order, $data );
		}
	}

	public function handle_payment_authorized( WC_Order $order, object $data ): void {
		if ( ! empty( $data->fee ) ) {
			OrderPaymentsMeta::add_order_item_transaction_fee( $order, (int) $data->fee );
		}

		// Handle pre-orders if applicable (this logic is still somewhat coupled with the old Helper)
		if ( class_exists( 'WC_Pre_Orders_Order' ) && WC_Pre_Orders_Order::order_contains_pre_order( $order ) && WC_Pre_Orders_Order::order_requires_payment_tokenization( $order->get_id() ) ) {
			try {
				$order->set_transaction_id( $data->id );
			} catch ( WC_Data_Exception $e ) {
				$this->logger->error( 'Error setting transaction ID for pre-order: ' . $e->getMessage() );
			}
			WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		} else {
			$should_complete = apply_filters( 'woocommerce_quickpay_callback_payment_authorized_complete_payment', true, $order, $data );
			if ( $should_complete ) {
				$order->payment_complete( $data->id );
			}
		}

		OrderUtils::add_note( $order, sprintf( __( 'Payment authorized. Transaction ID: %s', 'woocommerce-quickpay' ), $data->id ) );
		$this->save_transaction_id_fallback( $order, $data );

		do_action( 'woocommerce_quickpay_callback_payment_authorized', $order, $data );
	}

	private function handle_subscription_authorized( WC_Order $order, object $data, WC_Order $subscription ): void {
		OrderUtils::add_note( $subscription, sprintf( __( 'Subscription authorized. Transaction ID: %s', 'woocommerce-quickpay' ), $data->id ) );

		$this->save_transaction_id_fallback( $subscription, $data );
		OrderPaymentsMeta::set_transaction_order_id( $subscription, $data->order_id );

		if ( $order->get_total() > 0 ) {
			$is_renewal   = $this->subscriptions->is_renewal( $order );
			$contains_sub = OrderUtils::contains_subscription( $order );

			if ( ! $this->subscriptions->is_subscription( $order ) && ( $contains_sub || $is_renewal ) ) {
				$wcs_sub       = wcs_get_subscription( $subscription->get_id() );
				$needs_payment = $wcs_sub && ( $wcs_sub->needs_payment() || $order->needs_payment() );

				if ( $needs_payment ) {
					try {
						$this->api_factory->subscription()->recurring( $data->id, $order, $order->get_total() );
					} catch ( QuickPayApiException $e ) {
						$this->logger->error( "Failed to process recurring payment on subscription authorized callback: " . $e->getMessage() );
					}
				}
			}
		} else if ( empty( $data->variables->change_payment ) ) {
			$order->payment_complete();
		}

		do_action( 'woocommerce_quickpay_callback_subscription_authorized', $subscription, $order, $data );
	}

	private function handle_capture( WC_Order $order, object $data ): void {
		$capture_note = __( 'Payment captured.', 'woocommerce-quickpay' );
		$complete     = (string) $this->settings->get( 'quickpay_complete_on_capture', 'no' ) === 'yes' && ! $order->has_status( 'completed' );

		if ( apply_filters( 'woocommerce_quickpay_complete_order_on_capture', $complete, $order, $data ) ) {
			$order->update_status( 'completed', $capture_note );
		} else {
			$order->add_order_note( 'Quickpay: ' . $capture_note );
		}

		do_action( 'woocommerce_quickpay_callback_payment_captured', $order, $data );
	}

	private function handle_refund( WC_Order $order, object $data, object $operation ): void {
		$amount = (float) ( MoneyUtils::price_normalize( $operation->amount ?? 0, (string) $data->currency ) );
		$order->add_order_note( sprintf( 'Quickpay: ' . __( 'Refunded %1$s %2$s', 'woocommerce-quickpay' ), $amount, $data->currency ) );
	}

	private function handle_cancel( WC_Order $order, object $data, object $operation, ?WC_Order $subscription ): void {
		if ( $subscription ) {
			do_action( 'woocommerce_quickpay_callback_subscription_cancelled', $subscription, $order, $operation, $data );
		}

		if ( strtolower( (string) $data->type ) === 'payment' ) {
			$transition_status = $this->settings->get( 'quickpay_payment_cancelled_order_transition_status', '' );
			$transition_status = apply_filters( 'woocommerce_quickpay_payment_cancelled_order_transition_status', $transition_status, $order, $data, $operation );

			$note = apply_filters( 'woocommerce_quickpay_payment_cancelled_order_transition_status_note', __( 'Payment cancelled.', 'woocommerce-quickpay' ), $order, $data, $operation, $transition_status );

			if ( ! empty( $transition_status ) ) {
				$order->update_status( $transition_status, $note );
			} else {
				$order->add_order_note( $note );
			}

			do_action( 'woocommerce_quickpay_callback_payment_cancelled', $order, $data, $operation );
		}
	}

	private function handle_recurring( WC_Order $order, object $data ): void {
		$this->handle_payment_authorized( $order, $data );
	}

	private function handle_rejected_callback( WC_Order $order, object $data, object $operation, string $raw_body ): void {
		$this->logger->info( [
			'message'        => 'Rejected callback',
			'order'          => $order->get_id(),
			'qp_status_code' => $operation->qp_status_code,
			'qp_status_msg'  => $operation->qp_status_msg,
			'aq_status_code' => $operation->aq_status_code,
			'aq_status_msg'  => $operation->aq_status_msg,
			'request'        => $raw_body,
		] );

		if ( $order->needs_payment() && ( $operation->type === 'recurring' || strtolower( (string) $data->state ) !== 'rejected' ) ) {
			$order->update_status( 'failed', sprintf( 'Payment failed <br />QuickPay Message: %s<br />Acquirer Message: %s', $operation->qp_status_msg, $operation->aq_status_msg ) );
		}
	}

	private function save_transaction_id_fallback( WC_Order $order, object $data ): void {
		try {
			if ( ! empty( $data->id ) ) {
				$order->set_transaction_id( (string) $data->id );
				$order->update_meta_data( '_quickpay_transaction_id', (string) $data->id );
				$order->save();
			}
		} catch ( WC_Data_Exception $e ) {
			$this->logger->error( 'Error in save_transaction_id_fallback: ' . $e->getMessage() );
		}
	}

	private function get_order_id_from_callback( object $data ): int {
		if ( ! empty( $data->variables->order_post_id ) ) {
			return (int) $data->variables->order_post_id;
		}

		if ( isset( $_GET['order_post_id'] ) ) {
			return (int) sanitize_text_field( wp_unslash( $_GET['order_post_id'] ) );
		}

		preg_match( '/\d{4,}/', (string) $data->order_id, $matches );

		return (int) end( $matches );
	}

	private function get_subscription_id_from_callback( object $data ): ?int {
		if ( ! empty( $data->variables->subscription_post_id ) ) {
			return (int) $data->variables->subscription_post_id;
		}

		if ( isset( $_GET['subscription_post_id'] ) ) {
			return (int) sanitize_text_field( wp_unslash( $_GET['subscription_post_id'] ) );
		}

		return null;
	}

	private function cache_transaction_data( object $data ): void {
		try {
			$this->api_factory->payment( $data )->cache_transaction();
		} catch ( Exception $e ) {
			$this->logger->error( "Failed to cache transaction data from callback: " . $e->getMessage() );
		}
	}
}
