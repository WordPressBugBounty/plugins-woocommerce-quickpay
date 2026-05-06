<?php

namespace QuickpayPSP\QuickPay\Api;

use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\Utilities\MoneyUtils;

class TransactionClient extends QuickPayClient {
	protected bool $loaded_from_cache = false;

	/**
	 * @throws QuickPayApiException
	 */
	public function get_current_type(): string {
		$last_operation = $this->get_last_operation();

		if ( ! is_object( $last_operation ) ) {
			throw new QuickPayApiException( 'Malformed operation response', 0 );
		}

		return $last_operation->type;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function is_accepted(): bool {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		return wc_string_to_bool( $this->resource_data->accepted );
	}

	/**
	 * @return \stdClass
	 * @throws QuickPayApiException
	 */
	public function get_last_operation() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		$successful_operations = array_filter( $this->resource_data->operations, static function ( $operation ) {
			return (int) $operation->qp_status_code === 20000 || wc_string_to_bool( $operation->pending );
		} );

		$last_operation = end( $successful_operations );

		if ( ! is_object( $last_operation ) ) {
			throw new QuickPayApiException( 'Malformed operation object' );
		}

		if ( wc_string_to_bool( $last_operation->pending ) ) {
			$last_operation->type = esc_html__( 'Pending - check your QuickPay manager', 'woocommerce-quickpay' );
		}

		return $last_operation;
	}

	/**
	 * @param string $type
	 *
	 * @return mixed|null
	 * @throws QuickPayApiException
	 */
	public function get_last_operation_of_type( string $type ) {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		$operations = array_reverse( $this->resource_data->operations );

		foreach ( $operations as $operation ) {
			if ( $operation->type === $type ) {
				return $operation;
			}
		}

		return null;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function is_test(): bool {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		return (bool) $this->resource_data->test_mode;
	}

	/**
	 * Returns the payment type / card type used on the transaction.
	 *
	 * @throws QuickPayApiException
	 */
	public function get_brand() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		if ( ! empty( $this->resource_data->metadata->brand ) ) {
			return $this->resource_data->metadata->brand;
		}

		if ( ! empty( $this->resource_data->variables->payment_method ) ) {
			return str_replace( 'quickpay_', '', $this->resource_data->variables->payment_method );
		}

		if ( ! empty( $this->resource_data->link->payment_methods ) ) {
			return $this->resource_data->link->payment_methods;
		}

		return null;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_balance() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		return ! empty( $this->resource_data->balance ) ? $this->resource_data->balance : null;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_currency() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->currency;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_formatted_remaining_balance() {
		return MoneyUtils::price_normalize( $this->get_remaining_balance(), $this->get_currency() );
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_remaining_balance_as_float() {
		$remaining_balance = $this->get_remaining_balance();

		if ( $remaining_balance > 0 && MoneyUtils::is_currency_using_decimals( $this->get_currency() ) ) {
			return $remaining_balance / 100;
		}

		return $remaining_balance;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_remaining_balance() {
		$balance = $this->get_balance();

		$authorized_operations = array_filter( $this->resource_data->operations, static function ( $operation ) {
			return 'authorize' === $operation->type || 'recurring' === $operation->type;
		} );

		if ( empty( $authorized_operations ) ) {
			return null;
		}

		$operation = reset( $authorized_operations );

		$amount = $operation->amount;

		$remaining = $amount;

		if ( $balance > 0 ) {
			$remaining = $amount - $balance;
		}

		return $remaining;
	}

	public function get_acquirer(): ?string {
		if ( is_object( $this->resource_data ) && isset( $this->resource_data->acquirer ) ) {
			return $this->resource_data->acquirer;
		}

		return null;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_metadata() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->metadata;
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get_state() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayApiException( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->state;
	}

	/**
	 * @throws QuickPayApiException
	 * @throws QuickPayException
	 */
	public function maybe_load_transaction_from_cache( $transaction_id ) {
		$is_caching_enabled = self::is_transaction_caching_enabled( $this->settings );

		if ( empty( $transaction_id ) ) {
			throw new QuickPayException( esc_html__( 'Transaction ID cannot be empty', 'woocommerce-quickpay' ) );
		}

		if ( $is_caching_enabled && false !== ( $transient = get_transient( 'wcqp_transaction_' . $transaction_id ) ) ) {
			$this->loaded_from_cache = true;

			return $this->resource_data = (object) json_decode( $transient );
		}

		$this->get( (string) $transaction_id );

		if ( $is_caching_enabled ) {
			$this->cache_transaction();
		}

		return $this->resource_data;
	}

	public static function is_transaction_caching_enabled( $settings ): bool {
		$is_enabled = ! ( strtolower( (string) $settings->get( 'quickpay_caching_enabled', 'yes' ) ) === 'no' );

		return (bool) apply_filters( 'woocommerce_quickpay_transaction_cache_enabled', $is_enabled );
	}

	/**
	 * @throws QuickPayException
	 */
	public function cache_transaction(): bool {
		if ( ! is_object( $this->resource_data ) ) {
			throw new QuickPayException( 'Cannot cache empty transaction.' );
		}

		if ( ! self::is_transaction_caching_enabled( $this->settings ) ) {
			return false;
		}

		$expiration = (int) $this->settings->get( 'quickpay_caching_expiration', 0 );
		if ( ! $expiration ) {
			$expiration = 7 * DAY_IN_SECONDS;
		}

		$expiration = (int) apply_filters( 'woocommerce_quickpay_transaction_cache_expiration', $expiration );

		return set_transient( 'wcqp_transaction_' . $this->resource_data->id, wp_json_encode( $this->resource_data ), $expiration );
	}

	public function is_loaded_from_cached(): bool {
		return $this->loaded_from_cache;
	}

	public function get_data() {
		return $this->resource_data;
	}
}
