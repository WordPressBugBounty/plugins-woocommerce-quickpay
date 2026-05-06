<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use WC_Order;

/**
 * Legacy order payments utility wrapper.
 *
 * @deprecated Use OrderPaymentsMeta or OrderPaymentsUtils instead.
 */
class LegacyOrderPaymentsUtils {

	/**
	 * @deprecated Use OrderPaymentsMeta::get_payment_id() instead.
	 *
	 * @param WC_Order $order
	 *
	 * @return string|null
	 */
	public static function get_payment_id( WC_Order $order ): ?string {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_payment_id()' );

		return OrderPaymentsMeta::get_payment_id( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::set_payment_id() instead.
	 */
	public static function set_payment_id( WC_Order $order, string $payment_link ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::set_payment_id()' );

		OrderPaymentsMeta::set_payment_id( $order, $payment_link );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::delete_payment_id() instead.
	 */
	public static function delete_payment_id( WC_Order $order ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::delete_payment_id()' );

		OrderPaymentsMeta::delete_payment_id( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::get_payment_link() instead.
	 */
	public static function get_payment_link( WC_Order $order ): ?string {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_payment_link()' );

		return OrderPaymentsMeta::get_payment_link( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::set_payment_link() instead.
	 */
	public static function set_payment_link( WC_Order $order, $payment_link ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::set_payment_link()' );

		OrderPaymentsMeta::set_payment_link( $order, $payment_link );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::delete_payment_link() instead.
	 */
	public static function delete_payment_link( WC_Order $order ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::delete_payment_link()' );

		OrderPaymentsMeta::delete_payment_link( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::get_transaction_order_id() instead.
	 */
	public static function get_transaction_order_id( WC_Order $order ): ?string {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_transaction_order_id()' );

		return OrderPaymentsMeta::get_transaction_order_id( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::set_transaction_order_id() instead.
	 */
	public static function set_transaction_order_id( WC_Order $order, $transaction_order_id ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::set_transaction_order_id()' );

		OrderPaymentsMeta::set_transaction_order_id( $order, $transaction_order_id );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::add_order_item_transaction_fee() instead.
	 */
	public static function add_order_item_transaction_fee( WC_Order $order, int $fee_in_cents ): bool {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::add_order_item_transaction_fee()' );

		return OrderPaymentsMeta::add_order_item_transaction_fee( $order, $fee_in_cents );
	}

	/**
	 * @deprecated Use OrderPaymentsUtils::prepare_transaction_params() instead.
	 */
	public static function prepare_transaction_params( WC_Order $order ): array {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::prepare_transaction_params()' );

		return OrderPaymentsUtils::prepare_transaction_params( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsUtils::prepare_transaction_link_params() instead.
	 */
	public static function prepare_transaction_link_params( WC_Order $order ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::prepare_transaction_link_params()' );

		OrderPaymentsUtils::prepare_transaction_link_params( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsUtils::get_order_number_for_api() instead.
	 */
	public static function get_order_number_for_api( WC_Order $order, bool $recurring = false ): string {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::get_order_number_for_api()' );

		return OrderPaymentsUtils::get_order_number_for_api( $order, $recurring );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::get_failed_payment_count() instead.
	 */
	public static function get_failed_payment_count( WC_Order $order ): int {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_failed_payment_count()' );

		return OrderPaymentsMeta::get_failed_payment_count( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::increase_failed_payment_count() instead.
	 */
	public static function increase_failed_payment_count( WC_Order $order ): int {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::increase_failed_payment_count()' );

		return OrderPaymentsMeta::increase_failed_payment_count( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::reset_failed_payment_count() instead.
	 */
	public static function reset_failed_payment_count( WC_Order $order ): void {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::reset_failed_payment_count()' );

		OrderPaymentsMeta::reset_failed_payment_count( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::get_payment_method_change_count() instead.
	 */
	public static function get_payment_method_change_count( WC_Order $order ): int {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_payment_method_change_count()' );

		return OrderPaymentsMeta::get_payment_method_change_count( $order );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::increase_payment_method_change_count() instead.
	 */
	public static function increase_payment_method_change_count( WC_Order $order ): int {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::increase_payment_method_change_count()' );

		return OrderPaymentsMeta::increase_payment_method_change_count( $order );
	}

	/**
	 * Checks if the order is paid with the QuickPay module.
	 *
	 * @deprecated Use OrderPaymentsUtils::is_order_using_quickpay() instead.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public static function is_order_using_quickpay( WC_Order $order ): bool {
		_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::is_order_using_quickpay()' );

		return OrderPaymentsUtils::is_order_using_quickpay( $order );
	}
}
