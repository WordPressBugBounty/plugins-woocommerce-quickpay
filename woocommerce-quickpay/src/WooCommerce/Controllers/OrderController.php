<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use Exception;
use QuickpayPSP\Application\Services\PaymentLinkService;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\Utilities\RandomUtils;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use WC_Order;

/**
 * Controller for general order-related logic.
 */
final class OrderController {

	private GatewaySettingsProvider $settings;
	private SubscriptionsFacade $subscriptions_facade;
	private ApiClientFactory $api_factory;
	private WooCommerceLogger $logger;
	private PaymentLinkService $payment_link_service;
	private array $gateway_ids;

	public function __construct(
		GatewaySettingsProvider $settings,
		SubscriptionsFacade $subscriptions_facade,
		ApiClientFactory $api_factory,
		WooCommerceLogger $logger,
		PaymentLinkService $payment_link_service,
		array $gateway_ids = []
	) {
		$this->settings             = $settings;
		$this->subscriptions_facade = $subscriptions_facade;
		$this->api_factory          = $api_factory;
		$this->logger               = $logger;
		$this->payment_link_service = $payment_link_service;
		$this->gateway_ids          = $gateway_ids;
	}

	/**
	 * Cancels the transaction if the order is cancelled and the setting is enabled.
	 *
	 * @param int $order_id
	 * @param WC_Order $order
	 */
	public function maybe_cancel_transaction( $order_id, $order ): void {
		if ( ! $order ) {
			return;
		}

		if ( ! $this->is_quickpay_order( $order ) ) {
			return;
		}

		$auto_cancel_transaction = wc_string_to_bool( $this->settings->get( 'quickpay_cancel_transaction_on_cancel' ) );

		if ( ! $auto_cancel_transaction ) {
			return;
		}

		$transaction_id = OrderUtils::get_transaction_id( $order );
		if ( ! $transaction_id ) {
			return;
		}

		$transaction = woocommerce_quickpay_get_transaction_instance_by_order( $order );
		try {
			$transaction->get( $transaction_id );
			if ( ! $transaction->is_action_allowed( 'cancel' ) ) {
				return;
			}

			$transaction->cancel( $transaction_id );
			$order->add_order_note( esc_html__( 'QuickPay: Payment cancelled due to order cancellation', 'woocommerce-quickpay' ) );
		} catch ( Exception $e ) {
			$this->logger->error( 'Event: Order cancelled -> Error occured when cancelling transaction: ' . $e->getMessage() );
		}
	}

	/**
	 * Resets the failed payment count for an order.
	 *
	 * @param int $order_id
	 */
	public function reset_failed_payment_count( $order_id ): void {
		if ( $order = woocommerce_quickpay_get_order( $order_id ) ) {
			OrderPaymentsMeta::reset_failed_payment_count( $order );
		}
	}

	/**
	 * Automatically completes renewal orders upon successful payment authorization.
	 *
	 * @param WC_Order $order
	 */
	public function on_payment_authorized( $order ): void {
		$is_mp_subscription          = $order->get_payment_method() === 'mobilepay-subscriptions';
		$autocomplete_renewal_orders = wc_string_to_bool( $this->settings->get( 'subscription_autocomplete_renewal_orders' ) );

		if ( ! $is_mp_subscription && $autocomplete_renewal_orders && $this->subscriptions_facade->is_renewal( $order ) ) {
			$order->update_status( 'completed', esc_html__( 'Automatically completing order status due to successful recurring payment', 'woocommerce-quickpay' ) );
		}
	}

	/**
	 * Creates a payment link for a given order or subscription.
	 *
	 * Delegates core link creation to PaymentLinkService and handles
	 * admin-specific concerns: subscription manual renewal flag, order note and action hook.
	 *
	 * @param WC_Order|WC_Subscription $order
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function create_payment_link( $order ): bool {
		if ( ! $order ) {
			return false;
		}

		$is_subscription = $this->subscriptions_facade->is_subscription( $order );

		$url = $this->payment_link_service->create_link_for_admin( $order, [ $this, 'make_api_order_number_unique' ] );

		if ( $is_subscription ) {
			$subscription = $this->subscriptions_facade->get_subscription( $order->get_id() );
			if ( $subscription ) {
				$subscription->set_requires_manual_renewal( false );
				$subscription->save();
			}
		}

		$order->save();
		$order->add_order_note( sprintf( esc_html__( 'Payment link manually created from backend: %s', 'woocommerce-quickpay' ), $url ), false, true );

		do_action( 'woocommerce_quickpay_order_action_payment_link_created', $url, $order );

		return true;
	}

	/**
	 * Handles WooCommerce Subscriptions' scheduled renewal payment for all QuickPay gateways.
	 * Hooked into `woocommerce_scheduled_subscription_payment_{gateway_id}` per gateway.
	 *
	 * @param float $amount_to_charge
	 * @param WC_Order $renewal_order
	 *
	 * @return object|array|null
	 */
	public function scheduled_subscription_payment( float $amount_to_charge, $renewal_order ) {
		if ( ! $renewal_order || ! $renewal_order->needs_payment() ) {
			return null;
		}

		$subscription   = $this->subscriptions_facade->get_subscriptions_for_renewal_order( $renewal_order, true );
		$transaction_id = $subscription ? OrderUtils::get_transaction_id( $subscription ) : null;

		if ( ! $transaction_id ) {
			$this->logger->error( sprintf( 'Scheduled subscription payment: no transaction ID found for renewal order #%s', $renewal_order->get_id() ) );

			return null;
		}

		$response = null;

		try {
			[ $response ] = $this->api_factory->subscription()->recurring( $transaction_id, $renewal_order, $amount_to_charge );
		} catch ( \Exception $e ) {
			OrderPaymentsMeta::increase_failed_payment_count( $renewal_order );
			$renewal_order->update_status(
				'failed',
				sprintf( 'Automatic renewal of %s failed. Message: %s', $renewal_order->get_order_number(), $e->getMessage() )
			);
			$this->logger->error( 'Scheduled subscription payment failed: ' . $e->getMessage() );
		}

		do_action( 'woocommerce_quickpay_scheduled_subscription_payment_after', $subscription, $renewal_order, $response );

		return $response;
	}

	/**
	 * Captures payment when an order is marked as completed, if the setting is enabled.
	 *
	 * @param int $order_id
	 */
	public function maybe_capture_on_order_completed( int $order_id ): void {
		$order = woocommerce_quickpay_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( ! $this->is_quickpay_order( $order ) ) {
			return;
		}

		if ( ! apply_filters( 'woocommerce_quickpay_capture_on_order_completion', wc_string_to_bool( $this->settings->get( 'quickpay_captureoncomplete' ) ), $order ) ) {
			return;
		}

		if ( $this->subscriptions_facade->is_subscription( $order ) ) {
			return;
		}

		$transaction_id = OrderUtils::get_transaction_id( $order );
		if ( ! $transaction_id ) {
			return;
		}

		try {
			$payment = $this->api_factory->payment();
			$payment->get( $transaction_id );

			if ( ! $payment->is_action_allowed( 'capture' ) ) {
				return;
			}

			$amount_multiplied = MoneyUtils::price_multiply( $order->get_total(), $payment->get_currency() ) - $payment->get_balance();
			$amount            = MoneyUtils::price_multiplied_to_float( $amount_multiplied, $payment->get_currency() );

			$payment->capture( $transaction_id, $order, $amount );
		} catch ( \Exception $e ) {
			$error = sprintf( 'Unable to capture payment on order #%s. Problem: %s', $order->get_id(), $e->getMessage() );
			woocommerce_quickpay_add_runtime_error_notice( $error );
			$order->add_order_note( $error );
			$this->logger->error( $error );
		}
	}

	/**
	 * Handles pre-order completion payments.
	 *
	 * @param \WC_Order $order
	 */
	public function process_pre_order_payments( $order ): void {
		$order          = woocommerce_quickpay_get_order( $order );
		$transaction_id = OrderUtils::get_transaction_id( $order );

		if ( ! $transaction_id ) {
			return;
		}

		try {
			$payment = $this->api_factory->payment();
			$payment->get( $transaction_id );

			if ( ! $payment->is_action_allowed( 'capture' ) ) {
				return;
			}

			$payment->capture( $transaction_id, $order );
		} catch ( \Exception $e ) {
			$this->logger->error( sprintf( 'Could not process pre-order payment for order #%s: %s', $order->get_id(), $e->getMessage() ) );
			$order->update_status( 'failed' );
		}
	}

	/**
	 * Cancels the subscription transaction when a subscription is cancelled.
	 *
	 * @param \WC_Order $order
	 */
	public function subscription_cancellation( $order ): void {
		if ( 'cancelled' !== $order->get_status() ) {
			return;
		}

		if ( ! $this->subscriptions_facade->is_subscription( $order ) ) {
			return;
		}

		if ( ! apply_filters( 'woocommerce_quickpay_allow_subscription_transaction_cancellation', true, $order ) ) {
			return;
		}

		$transaction_id = OrderUtils::get_transaction_id( $order );

		try {
			$subscription = $this->api_factory->subscription();
			$subscription->get( $transaction_id );

			if ( $subscription->is_action_allowed( 'cancel' ) ) {
				$subscription->cancel( $transaction_id );
			}
		} catch ( \Exception $e ) {
			$this->logger->error( 'Subscription cancellation error: ' . $e->getMessage() );
		}
	}

	/**
	 * Increments the payment method change count when a subscription's payment method is updated to a QuickPay gateway.
	 *
	 * @param \WC_Subscription $subscription
	 * @param string $old_payment_method
	 */
	public function on_subscription_payment_method_updated( $subscription, $old_payment_method ): void {
		OrderPaymentsMeta::increase_payment_method_change_count( $subscription );
	}

	/**
	 * Removes meta keys that should not be copied to renewal orders.
	 *
	 * @param array $meta
	 *
	 * @return array
	 */
	public function remove_renewal_meta_data( array $meta ): array {
		$avoid_keys = [
			'_quickpay_failed_payment_count',
			'_quickpay_transaction_id',
			'_transaction_id',
			'TRANSACTION_ID',
			'TRANSACTION_ORDER_ID',
			'QUICKPAY_PAYMENT_LINK',
		];

		foreach ( $avoid_keys as $key ) {
			unset( $meta[ $key ] );
		}

		return $meta;
	}

	/**
	 * Declares gateway's payment meta requirements for manual payment method changes by admins.
	 *
	 * @param array $payment_meta
	 * @param \WC_Subscription $subscription
	 *
	 * @return array
	 */
	public function subscription_payment_meta( array $payment_meta, $subscription ): array {
		$payment_meta['quickpay'] = [
			'post_meta' => [
				'_quickpay_transaction_id' => [
					'value' => OrderUtils::get_transaction_id( $subscription ),
					'label' => __( 'QuickPay Transaction ID', 'woocommerce-quickpay' ),
				],
			],
		];

		return $payment_meta;
	}

	/**
	 * Validates the transaction ID when an admin manually changes the payment meta on a subscription.
	 *
	 * @param array $payment_meta
	 * @param \WC_Subscription $subscription
	 *
	 * @throws \Exception
	 */
	public function validate_subscription_payment_meta( array $payment_meta, $subscription ): void {
		if ( ! isset( $payment_meta['post_meta']['_quickpay_transaction_id']['value'] ) ) {
			return;
		}

		$transaction_id     = $payment_meta['post_meta']['_quickpay_transaction_id']['value'];
		$sub_transaction_id = OrderUtils::get_transaction_id( $subscription );

		if ( $transaction_id === $sub_transaction_id ) {
			return;
		}

		$api_subscription = $this->api_factory->subscription();
		$api_subscription->get( $transaction_id );

		$subscription->add_order_note(
			sprintf(
				esc_html__( 'QuickPay Transaction ID updated from #%1$d to #%2$d', 'woocommerce-quickpay' ),
				$sub_transaction_id,
				$transaction_id
			),
			0,
			true
		);
	}

	/**
	 * Returns true if the order's payment method is handled by one of our registered gateways.
	 * When no gateway IDs are configured, all orders are considered owned (BC).
	 */
	private function is_quickpay_order( WC_Order $order ): bool {
		if ( empty( $this->gateway_ids ) ) {
			return true;
		}

		return in_array( $order->get_payment_method(), $this->gateway_ids, true );
	}

	/**
	 * Filter to append a random string to the order number sent to the API.
	 *
	 * @param string $api_order_number
	 *
	 * @return string
	 */
	public function make_api_order_number_unique( $api_order_number ): string {
		if ( ! preg_match( '/-.{3,}$/', $api_order_number ) ) {
			$api_order_number .= '-' . RandomUtils::create_random_string( 3 );
		}

		return $api_order_number;
	}
}
