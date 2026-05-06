<?php

namespace QuickpayPSP\QuickPay\Api;

use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Support\Http\HttpClientInterface;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\TransactionDataBuilder;
use WC_Order;
use WC_Subscriptions_Order;

class SubscriptionClient extends PaymentClient {
	public function __construct( ?HttpClientInterface $http = null, ?GatewaySettingsProvider $settings = null, $resource_data = null ) {
		TransactionClient::__construct( $http, $settings );

		if ( is_object( $resource_data ) ) {
			$this->resource_data = $resource_data;
		}

		$this->api_url .= 'subscriptions/';
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function create( WC_Order $order ) {
		$base_params = [
			'currency'      => $order->get_currency(),
			'order_post_id' => $order->get_id(),
		];

		$text_on_statement = (string) $this->settings->get( 'quickpay_text_on_statement', '' );
		if ( $text_on_statement !== '' ) {
			$base_params['text_on_statement'] = $text_on_statement;
		}

		$order_params = OrderPaymentsUtils::prepare_transaction_params( $order );

		$params = array_merge( $base_params, $order_params );

		return $this->post( '/', $params );
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function recurring( $subscription_id, WC_Order $order, $amount = null ) {
		if ( $amount === null ) {
			$amount = WC_Subscriptions_Order::get_recurring_total( $order );
		}

		$order_number = OrderPaymentsUtils::get_order_number_for_api( $order, true );

		$request_url = sprintf( '%1$d/%2$s', (int) $subscription_id, 'recurring' );

		$request_data = apply_filters( 'woocommerce_quickpay_create_recurring_payment_data', [
			'amount'            => MoneyUtils::price_multiply( $amount, $order->get_currency() ),
			'order_id'          => $order_number,
			'auto_capture'      => TransactionDataBuilder::should_auto_capture_order( $order ),
			'autofee'           => (int) wc_string_to_bool( $this->settings->get( 'quickpay_autofee', 'no' ) ),
			'text_on_statement' => $this->settings->get( 'quickpay_text_on_statement', '' ),
			'order_post_id'     => $order->get_id(),
		], $order, $subscription_id );

		$request_data = apply_filters( 'woocommerce_quickpay_create_recurring_payment_data_' . strtolower( $order->get_payment_method() ), $request_data, $order, $subscription_id );

		return $this->post( $request_url, $request_data, true );
	}


	/**
	 * @throws QuickPayApiException
	 */
	public function is_action_allowed( $action ): bool {
		try {
			$state = $this->get_current_type();

			$allowed_states = [
				'cancel'           => [ 'authorize' ],
				'standard_actions' => [ 'authorize' ],
			];

			return array_key_exists( $action, $allowed_states ) && in_array( $state, $allowed_states[ $action ], true );
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
