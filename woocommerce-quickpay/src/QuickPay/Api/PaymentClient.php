<?php

namespace QuickpayPSP\QuickPay\Api;

use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayCaptureException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Support\Http\HttpClientInterface;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\WooCommerce\Gateways\BaseGateway;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\TransactionDataBuilder;
use WC_Order;

class PaymentClient extends TransactionClient {
	public function __construct( ?HttpClientInterface $http = null, ?GatewaySettingsProvider $settings = null, $resource_data = null ) {
		parent::__construct( $http, $settings );

		if ( is_object( $resource_data ) ) {
			$this->resource_data = $resource_data;
		}

		$this->api_url .= 'payments/';
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
	public function patch_link( $transaction_id, WC_Order $order ) {
		$payment_method = strtolower( $order->get_payment_method() );

		$base_params = array_filter( [
			'language'                     => woocommerce_quickpay_get_language(),
			'currency'                     => $order->get_currency(),
			'callbackurl'                  => $this->get_callback_url(),
			'auto_capture'                 => TransactionDataBuilder::should_auto_capture_order( $order ),
			'autofee'                      => (int) wc_string_to_bool( $this->settings->get( 'quickpay_autofee', 'no' ) ),
			'payment_methods'              => $this->get_payment_method( $order ),
			'branding_id'                  => $this->settings->get( 'quickpay_branding_id', '' ),
			'google_analytics_tracking_id' => $this->settings->get( 'quickpay_google_analytics_tracking_id', '' ),
			'customer_email'               => $order->get_billing_email(),
			'referer_url'                  => wp_parse_url( home_url(), PHP_URL_HOST ),
			'deadline'                     => (int) $this->settings->get( 'quickpay_payment_window_timeout', null ) ?: null,
		] );

		$order_params = OrderPaymentsUtils::prepare_transaction_link_params( $order );

		return $this->put(
			sprintf( '%d/link', $transaction_id ),
			apply_filters( 'woocommerce_quickpay_transaction_link_params', array_merge( $base_params, $order_params ), $order, $payment_method )
		);
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function patch_payment( $transaction_id, WC_Order $order ) {
		$base_params = [
			'currency'      => $order->get_currency(),
			'order_post_id' => $order->get_id(),
		];

		$text_on_statement = (string) $this->settings->get( 'quickpay_text_on_statement', '' );
		if ( $text_on_statement !== '' ) {
			$base_params['text_on_statement'] = $text_on_statement;
		}

		$order_params = OrderPaymentsUtils::prepare_transaction_params( $order );

		return $this->patch( sprintf( '/%s', $transaction_id ), array_merge( $base_params, $order_params ) );
	}

	/**
	 * @throws QuickPayApiException
	 * @throws QuickPayException
	 * @throws QuickPayCaptureException
	 */
	public function capture( $transaction_id, WC_Order $order, $amount = null ) {
		if ( $amount === null ) {
			$amount = $order->get_total();
		}

		$request = $this->post( sprintf( '%1$d/%2$s', (int) $transaction_id, 'capture' ), [
			'amount' => MoneyUtils::price_multiply( $amount, $order->get_currency() ),
		], true );

		$this->check_last_operation_of_type_with_location_fallback( 'capture', $order, $request );

		return $this;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function cancel( $transaction_id ): void {
		$this->post( sprintf( '%1$d/%2$s', (int) $transaction_id, 'cancel' ) );
	}

	/**
	 * @throws QuickPayApiException
	 * @throws QuickPayException
	 * @throws QuickPayCaptureException
	 */
	public function refund( int $transaction_id, WC_Order $order, ?float $amount = null ): void {
		if ( $amount === null ) {
			$amount = $order->get_total();
		}

		$basket_items = TransactionDataBuilder::get_basket_params( $order );
		$product      = reset( $basket_items );

		$request = $this->post( sprintf( '%1$d/%2$s', $transaction_id, 'refund' ), [
			'amount'   => MoneyUtils::price_multiply( $amount, $order->get_currency() ),
			'vat_rate' => $product['vat_rate'] ?? 0,
		], true );

		$this->check_last_operation_of_type_with_location_fallback( 'refund', $order, $request );
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function is_action_allowed( $action ): bool {
		try {
			$state             = $this->get_current_type();
			$remaining_balance = $this->get_remaining_balance();

			$allowed_states = [
				'capture'          => [ 'authorize', 'recurring' ],
				'cancel'           => [ 'authorize', 'recurring' ],
				'refund'           => [ 'capture', 'refund' ],
				'renew'            => [ 'authorize' ],
				'splitcapture'     => [ 'authorize', 'capture' ],
				'recurring'        => [ 'subscribe' ],
				'standard_actions' => [ 'authorize', 'recurring' ],
			];

			if ( $action === 'capture' && 'mobilepaysubscriptions' === $this->get_acquirer() ) {
				return false;
			}

			if ( 'capture' === $state && $remaining_balance > 0 && $action !== 'cancel' ) {
				return true;
			}

			return in_array( $state, $allowed_states[ $action ] ?? [], true );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * @throws QuickPayApiException
	 * @throws QuickPayException
	 * @throws QuickPayCaptureException
	 */
	public function check_last_operation_of_type_with_location_fallback( string $action, WC_Order $order, $request ) {
		$follow_location = isset( $request[5]['location'] ) && ! empty( $request[5]['location'] );

		try {
			$_action = $this->get_last_operation_of_type( $action );
		} catch ( QuickPayException $e ) {
			$_action = null;
		}

		if ( $follow_location && ! $_action ) {
			$api     = new QuickPayClient( $this->http, $this->settings );
			$_action = $api->get( $request[5]['location'][0] );

			if ( empty( $_action ) ) {
				throw new QuickPayException( sprintf( '%s inconclusive. Response from location header is empty.', ucfirst( $action ) ) );
			}
		}

		if ( ! $follow_location && ! $_action ) {
			throw new QuickPayException( sprintf( 'No %s operation or location found: %s', $action, wp_json_encode( $this->resource_data ) ) );
		}

		if ( $_action->qp_status_code > 20200 ) {
			throw new QuickPayCaptureException( sprintf( '%s payment on order #%s failed. Message: %s', ucfirst( $action ), $order->get_id(), $_action->qp_status_msg ) );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function get_payment_method( WC_Order $order ): string {
		$default_payment_method = $this->settings->getCardtypelock();

		if ( ! $gateway = wc_get_payment_gateway_by_order( $order ) ) {
			return $default_payment_method;
		}

		if ( ! $gateway instanceof BaseGateway ) {
			return $default_payment_method;
		}

		return apply_filters( 'woocommerce_quickpay_cardtypelock_' . $gateway->id, $gateway->default_card_type_lock(), $gateway->id, $order, $gateway );
	}
}
