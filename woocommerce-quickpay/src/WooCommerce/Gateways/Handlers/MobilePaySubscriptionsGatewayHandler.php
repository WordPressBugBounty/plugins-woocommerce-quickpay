<?php

declare( strict_types=1 );

namespace QuickpayPSP\WooCommerce\Gateways\Handlers;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Gateways\Contracts\GatewayHandlerInterface;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use WC_Order;
use WC_Subscription;

/**
 * Handles all MobilePay Subscriptions-specific hook logic that previously lived
 * inside the gateway class itself. By extracting this into a standalone handler,
 * the gateway no longer needs to be instantiated during callbacks or other flows.
 *
 * Covers:
 * - Card type lock
 * - Phone number removal from invoice params
 * - Gateway availability (subscription-only)
 * - Recurring payment data (auto_capture_at + description)
 * - Subscription authorized → instant activation
 * - After scheduled payment created → keep active
 * - Payment captured → complete order if needed
 * - Subscription payment meta (admin UI)
 * - Subscription cancelled → status transition
 * - Payment cancelled → failed status on aq_status_code 50000/50001
 * - Payment cancelled → note on aq_status_code 50000/50001
 */
class MobilePaySubscriptionsGatewayHandler implements GatewayHandlerInterface {
	public const GATEWAY_ID = 'mobilepay-subscriptions';

	private GatewaySettingsProvider $settings;
	private WooCommerceLogger $logger;

	public function __construct( GatewaySettingsProvider $settings, WooCommerceLogger $logger ) {
		$this->settings = $settings;
		$this->logger   = $logger;
	}

	public function gateway_id(): string {
		return self::GATEWAY_ID;
	}

	public function register(): void {
		add_filter( 'woocommerce_quickpay_transaction_params_invoice', [ $this, 'maybe_remove_phone_number' ], 10, 2 );
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'adjust_available_gateways' ] );
		add_filter( 'woocommerce_quickpay_create_recurring_payment_data_' . self::GATEWAY_ID, [ $this, 'recurring_payment_data' ], 10, 3 );
		add_action( 'woocommerce_quickpay_callback_subscription_authorized', [ $this, 'on_subscription_authorized' ], 10, 3 );
		add_action( 'woocommerce_quickpay_scheduled_subscription_payment_after', [ $this, 'on_after_scheduled_payment_created' ], 10, 2 );
		add_filter( 'woocommerce_quickpay_callback_payment_captured', [ $this, 'maybe_process_order_on_capture' ], 10, 2 );
		add_filter( 'woocommerce_subscription_payment_meta', [ $this, 'subscription_payment_meta' ], 10, 2 );
		add_action( 'woocommerce_quickpay_callback_subscription_cancelled', [ $this, 'on_subscription_cancelled' ], 10, 4 );
		add_filter( 'woocommerce_quickpay_payment_cancelled_order_transition_status', [ $this, 'payment_cancelled_order_transition_status' ], 10, 4 );
		add_filter( 'woocommerce_quickpay_payment_cancelled_order_transition_status_note', [ $this, 'payment_cancelled_order_transition_status_note' ], 10, 4 );
		add_filter( 'woocommerce_quickpay_callback_payment_authorized_complete_payment', [ $this, 'callback_payment_authorized_complete_payment' ], 50, 2 );
	}

	/**
	 * Lock card type to mobilepay-subscriptions.
	 */
	public function filter_cardtypelock(): string {
		return self::GATEWAY_ID;
	}

	/**
	 * Remove phone number from invoice params if the setting is disabled.
	 *
	 * @param array $data
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function maybe_remove_phone_number( array $data, WC_Order $order ): array {
		if ( $order->get_payment_method() !== self::GATEWAY_ID ) {
			return $data;
		}

		$prefill_enabled = (string) $this->settings->getForGateway( self::GATEWAY_ID, 'checkout_prefill_phone_number', 'yes' ) === 'yes';

		if ( ! $prefill_enabled && isset( $data['phone_number'] ) ) {
			$data['phone_number'] = null;
		}

		return $data;
	}

	/**
	 * Only show the gateway if the cart contains a subscription product.
	 *
	 * @param array $available_gateways
	 *
	 * @return array
	 */
	public function adjust_available_gateways( array $available_gateways ): array {
		if ( ! isset( $available_gateways[ self::GATEWAY_ID ] ) ) {
			return $available_gateways;
		}

		if ( ! class_exists( 'WC_Subscriptions_Cart' ) || ! class_exists( 'WC_Subscriptions_Change_Payment_Gateway' ) ) {
			return $available_gateways;
		}

		$is_change_payment = \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment;
		$cart_has_sub      = \WC_Subscriptions_Cart::cart_contains_subscription();

		if ( ! $cart_has_sub && ! $is_change_payment && ( is_cart() || is_checkout() ) ) {
			unset( $available_gateways[ self::GATEWAY_ID ] );
		}

		return $available_gateways;
	}

	/**
	 * Add auto_capture_at and description to recurring payment data.
	 *
	 * @param array $data
	 * @param WC_Order $order
	 * @param int $subscription_id
	 *
	 * @return array
	 */
	public function recurring_payment_data( array $data, WC_Order $order, int $subscription_id ): array {
		if ( empty( $data['due_date'] ) ) {
			$timezone                = apply_filters( 'woocommerce_quickpay_mps_timezone', null, $data, $order, $subscription_id );
			$data['auto_capture_at'] = wp_date( 'Y-m-d', strtotime( 'now + 2 days' ), $timezone );
			/* translators: 1: the order number */
			$data['description'] = sprintf( esc_html__( 'Payment of #%s', 'woocommerce-quickpay' ), $order->get_order_number() );
		}

		return $data;
	}

	/**
	 * If "Activate subscriptions immediately" is enabled, activate the subscription
	 * right after the MobilePay agreement has been authorized.
	 *
	 * @param WC_Subscription $subscription
	 * @param WC_Order $parent_order
	 * @param mixed $transaction
	 */
	public function on_subscription_authorized( WC_Subscription $subscription, WC_Order $parent_order, $transaction ): void {
		if ( $subscription->get_payment_method() !== self::GATEWAY_ID ) {
			return;
		}

		try {
			$instant_activation = wc_string_to_bool( $this->settings->getForGateway( self::GATEWAY_ID, 'checkout_instant_activation', 'no' ) );

			if ( $instant_activation && ! $subscription->has_status( 'active' ) ) {
				$subscription->update_status(
					'active',
					esc_html__( "'Activate subscriptions immediately.' enabled. Activating subscription due to authorized MobilePay agreement", 'woocommerce-quickpay' )
				);
				$subscription->save();
			}
		} catch ( \Exception $e ) {
			$this->logger->error( 'Unable to activate subscription immediately after payment authorization: ' . $e->getMessage() );
		}
	}

	/**
	 * If "Keep subscription active" is enabled, keep the subscription active after
	 * a renewal payment has been scheduled.
	 *
	 * @param WC_Subscription $subscription
	 * @param WC_Order $renewal_order
	 */
	public function on_after_scheduled_payment_created( $subscription, WC_Order $renewal_order ): void {
		if ( ! $subscription instanceof WC_Subscription || $subscription->get_payment_method() !== self::GATEWAY_ID ) {
			return;
		}

		$keep_active = wc_string_to_bool( $this->settings->getForGateway( self::GATEWAY_ID, 'renewal_keep_active', 'no' ) );

		if ( $keep_active ) {
			try {
				$subscription->update_status( 'active' );
			} catch ( \Exception $e ) {
				$subscription->add_order_note( $e->getMessage() );
			}
		}
	}

	/**
	 * Complete the order on capture if it still needs payment.
	 * MobilePay Subscriptions may send a capture callback before the order is completed.
	 *
	 * @param WC_Order $order
	 * @param mixed $transaction
	 */
	public function maybe_process_order_on_capture( WC_Order $order, $transaction ): void {
		if ( $order->get_payment_method() !== self::GATEWAY_ID ) {
			return;
		}

		if ( $order->needs_payment() ) {
			$order->payment_complete( (string) $transaction->id );
		}
	}

	/**
	 * Declare gateway's meta data requirements for manual payment gateway changes by admins.
	 *
	 * @param array $payment_meta
	 * @param WC_Subscription $subscription
	 *
	 * @return array
	 */
	public function subscription_payment_meta( array $payment_meta, WC_Subscription $subscription ): array {
		$payment_meta[ self::GATEWAY_ID ] = [
			'post_meta' => [
				'_quickpay_transaction_id' => [
					'value' => OrderUtils::get_transaction_id( $subscription ),
					'label' => esc_html__( 'QuickPay Transaction ID', 'woocommerce-quickpay' ),
				],
			],
		];

		return $payment_meta;
	}

	/**
	 * Handle subscription cancellation: transition the subscription to the configured status.
	 *
	 * @param WC_Subscription $subscription
	 * @param WC_Order $order
	 * @param mixed $operation
	 * @param mixed $json
	 *
	 * @throws \Exception
	 */
	public function on_subscription_cancelled( WC_Subscription $subscription, WC_Order $order, $operation, $json ): void {
		if ( $subscription->get_payment_method() !== self::GATEWAY_ID ) {
			return;
		}

		$transition_to = (string) $this->settings->getForGateway( self::GATEWAY_ID, 'mps_transaction_cancellation_status', '' );

		if ( empty( $transition_to ) || $transition_to === 'none' ) {
			return;
		}

		// Allow third party plugins to determine which statuses a subscription can transition from. Defaults to only target active subscriptions
		$allowed_transition_from = apply_filters( 'woocommerce_quickpay_mps_cancelled_from_status', [ 'active' ], $subscription, $order, $json );

		// Check if the subscription has the allowed status
		if ( ! $subscription->has_status( $allowed_transition_from ) ) {
			return;
		}

		$note = ! empty( $operation->aq_status_msg )
			? $operation->aq_status_msg
			: esc_html__( 'Subscription transaction has been cancelled by merchant or customer', 'woocommerce-quickpay' );

		// If the setting has been set to cancelled, we will run the cancel_order method on the subscription to
		// take advantage of the built-in pending-cancel/cancelled functionality.
		if ( $transition_to === 'cancelled' ) {
			$subscription->cancel_order( $note );
		}
		// Otherwise, check that:
		// 1. the subscription does not already have the status, we want to transition to
		// 2. The 'transition to' status is a valid subscription status.
		elseif ( ! $subscription->has_status( $transition_to ) && $this->is_valid_subscription_status( $transition_to ) ) {
			$subscription->update_status( $transition_to, $note );
		}
	}

	/**
	 * Override the cancelled order transition status to 'failed' for MobilePay-specific error codes.
	 *
	 * aq_status_code 50000: Payment failed to execute during the due-date.
	 * aq_status_code 50001: User rejected the Pending payment in MobilePay.
	 *
	 * @param string|null $transition_to_status
	 * @param WC_Order $order
	 * @param mixed $transaction
	 * @param mixed $operation
	 *
	 * @return string
	 */
	public function payment_cancelled_order_transition_status( ?string $transition_to_status, WC_Order $order, $transaction, $operation ): string {
		$transition_to_status = $transition_to_status ?? '';
		if ( $this->is_cancelled_transaction_failed( $operation, $order ) ) {
			$transition_to_status = 'failed';
		}

		return $transition_to_status;
	}

	/**
	 * Do not mark the payment as complete if the payment is a scheduled payment from MobilePay Subscriptions. Scheduled payments can still fail even when authorized,
	 * so we should wait marking the payment as complete until the capture.
	 *
	 * @param bool|null $authorize
	 * @param $order
	 * @param $data
	 *
	 * @return bool
	 */
	public function callback_payment_authorized_complete_payment( ?bool $authorize, $order ): bool {
		$authorize = $authorize ?? false;
		if ( $authorize && $order->get_payment_method() === self::GATEWAY_ID ) {
			return false;
		}

		return $authorize;
	}

	/**
	 * Append the acquirer status message to the cancellation note for MobilePay-specific error codes.
	 *
	 * @param string|null $note
	 * @param WC_Order $order
	 * @param mixed $transaction
	 * @param mixed $operation
	 *
	 * @return string
	 */
	public function payment_cancelled_order_transition_status_note( ?string $note, WC_Order $order, $transaction, $operation ): string {
		$note = $note ?? '';
		if ( $this->is_cancelled_transaction_failed( $operation, $order ) ) {
			$note = sprintf( '%s - %s', $note, $operation->aq_status_msg );
		}

		return $note;
	}

	/**
	 * Returns true if the cancel operation should be treated as a failed payment.
	 * Only applies to MobilePay Subscriptions orders with aq_status_code 50000 or 50001.
	 */
	private function is_cancelled_transaction_failed( $operation, WC_Order $order ): bool {
		return $order->get_payment_method() === self::GATEWAY_ID
		       && in_array( (int) ( $operation->aq_status_code ?? 0 ), [ 50000, 50001 ], true );
	}

	/**
	 * Check if a given status is a valid WooCommerce Subscriptions status.
	 */
	private function is_valid_subscription_status( string $status ): bool {
		return function_exists( 'wcs_get_subscription_statuses' )
		       && array_key_exists( 'wc-' . $status, wcs_get_subscription_statuses() );
	}
}
