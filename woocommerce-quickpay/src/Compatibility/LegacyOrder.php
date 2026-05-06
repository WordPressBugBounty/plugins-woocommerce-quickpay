<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\Utilities\AddressUtils;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\WooCommerce\Support\TransactionDataBuilder;
use RequestsUtils;
use WC_Order;

/**
 * @deprecated Use the individual service classes instead.
 */
class LegacyOrder extends WC_Order {

	// -------------------------------------------------------------------------
	// Static / callback helpers
	// -------------------------------------------------------------------------

	/**
	 * @deprecated
	 */
	public static function get_order_id_from_callback( $callback_data ): int {
		_deprecated_function( __METHOD__, '8.0.0' );

		return 0;
	}

	/**
	 * @deprecated
	 */
	public static function get_subscription_id_from_callback( $callback_data ): int {
		_deprecated_function( __METHOD__, '8.0.0' );

		return 0;
	}

	// -------------------------------------------------------------------------
	// Payment ID meta
	// -------------------------------------------------------------------------

	public function get_payment_id(): ?string {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_payment_id' );

		return OrderPaymentsMeta::get_payment_id( $this );
	}

	public function set_payment_id( $payment_id ): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::set_payment_id' );
		OrderPaymentsMeta::set_payment_id( $this, (string) $payment_id );
	}

	public function delete_payment_id(): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::delete_payment_id' );
		OrderPaymentsMeta::delete_payment_id( $this );
	}

	// -------------------------------------------------------------------------
	// Payment link meta
	// -------------------------------------------------------------------------

	public function get_payment_link(): ?string {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_payment_link' );

		return OrderPaymentsMeta::get_payment_link( $this );
	}

	public function set_payment_link( $payment_link ): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::set_payment_link' );
		OrderPaymentsMeta::set_payment_link( $this, (string) $payment_link );
	}

	public function delete_payment_link(): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::delete_payment_link' );
		OrderPaymentsMeta::delete_payment_link( $this );
	}

	// -------------------------------------------------------------------------
	// Transaction order ID meta
	// -------------------------------------------------------------------------

	public function get_transaction_order_id(): string {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_transaction_order_id' );

		return (string) OrderPaymentsMeta::get_transaction_order_id( $this );
	}

	public function set_transaction_order_id( $transaction_order_id ): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::set_transaction_order_id' );
		OrderPaymentsMeta::set_transaction_order_id( $this, (string) $transaction_order_id );
	}

	// -------------------------------------------------------------------------
	// Transaction fee
	// -------------------------------------------------------------------------

	public function add_transaction_fee( $fee_in_cents ): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::add_order_item_transaction_fee' );

		return OrderPaymentsMeta::add_order_item_transaction_fee( $this, (int) $fee_in_cents );
	}

	// -------------------------------------------------------------------------
	// Failed payment count
	// -------------------------------------------------------------------------

	public function get_failed_quickpay_payment_count(): int {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_failed_payment_count' );

		return OrderPaymentsMeta::get_failed_payment_count( $this );
	}

	public function increase_failed_quickpay_payment_count(): int {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::increase_failed_payment_count' );

		return OrderPaymentsMeta::increase_failed_payment_count( $this );
	}

	public function reset_failed_quickpay_payment_count(): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::reset_failed_payment_count' );
		OrderPaymentsMeta::reset_failed_payment_count( $this );
	}

	// -------------------------------------------------------------------------
	// Payment method change count
	// -------------------------------------------------------------------------

	public function get_payment_method_change_count(): int {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::get_payment_method_change_count' );

		return OrderPaymentsMeta::get_payment_method_change_count( $this );
	}

	public function increase_payment_method_change_count(): int {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsMeta::class . '::increase_payment_method_change_count' );

		return OrderPaymentsMeta::increase_payment_method_change_count( $this );
	}

	// -------------------------------------------------------------------------
	// Order utils
	// -------------------------------------------------------------------------

	public function subscription_is_renewal_failure(): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderUtils::class . '::is_failed_renewal' );

		return OrderUtils::is_failed_renewal( $this );
	}

	public function note( $message ): void {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderUtils::class . '::add_note' );
		OrderUtils::add_note( $this, (string) $message );
	}

	public function contains_subscription(): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderUtils::class . '::contains_subscription' );

		return OrderUtils::contains_subscription( $this );
	}

	public function order_contains_switch(): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderUtils::class . '::contains_switch_order' );

		return OrderUtils::contains_switch_order( $this );
	}

	public function get_clean_order_number(): string {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderUtils::class . '::get_clean_order_number' );

		return OrderUtils::get_clean_order_number( $this );
	}

	public function get_transaction_id( $context = 'view' ) {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderUtils::class . '::get_transaction_id' );

		return OrderUtils::get_transaction_id( $this );
	}

	// -------------------------------------------------------------------------
	// Payment utils
	// -------------------------------------------------------------------------

	public function get_transaction_params(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::prepare_transaction_params' );

		return OrderPaymentsUtils::prepare_transaction_params( $this );
	}

	public function get_transaction_link_params(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::prepare_transaction_link_params' );

		return OrderPaymentsUtils::prepare_transaction_link_params( $this );
	}

	public function get_order_number_for_api( bool $recurring = false ): ?string {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::get_order_number_for_api' );

		return OrderPaymentsUtils::get_order_number_for_api( $this, $recurring );
	}

	public function has_quickpay_payment(): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', OrderPaymentsUtils::class . '::is_order_using_quickpay' );

		return OrderPaymentsUtils::is_order_using_quickpay( $this );
	}

	// -------------------------------------------------------------------------
	// Request utils
	// -------------------------------------------------------------------------

	public function is_request_to_change_payment(): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', RequestsUtils::class . '::is_request_to_change_payment' );

		return RequestsUtils::is_request_to_change_payment();
	}

	// -------------------------------------------------------------------------
	// Transaction data builder
	// -------------------------------------------------------------------------

	public function get_transaction_basket_params(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_basket_params' );

		return TransactionDataBuilder::get_basket_params( $this );
	}

	public function get_transaction_shipping_address_params(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_shipping_address' );

		return TransactionDataBuilder::get_shipping_address( $this );
	}

	public function get_transaction_invoice_address_params(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_invoice_address' );

		return TransactionDataBuilder::get_invoice_address( $this );
	}

	public function get_transaction_shopsystem_params(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_shop_system_params' );

		return TransactionDataBuilder::get_shop_system_params( $this );
	}

	public function get_custom_variables(): array {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_custom_variables' );

		return TransactionDataBuilder::get_custom_variables( $this );
	}

	public function get_continue_url(): string {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_continue_url' );

		return TransactionDataBuilder::get_continue_url( $this );
	}

	public function get_cancellation_url(): string {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::get_cancellation_url' );

		return TransactionDataBuilder::get_cancellation_url( $this );
	}

	public function get_autocapture_setting(): bool {
		wc_deprecated_function( __METHOD__, '8.0.0', TransactionDataBuilder::class . '::should_auto_capture_order' );

		return TransactionDataBuilder::should_auto_capture_order( $this );
	}

	// -------------------------------------------------------------------------
	// Address helpers (delegated to AddressUtils via AddressUtils alias)
	// -------------------------------------------------------------------------

	public function get_shipping_street_name() {
		wc_deprecated_function( __METHOD__, '8.0.0', 'AddressUtils::get_street_name( $order->get_shipping_address_1() )' );

		return AddressUtils::get_street_name( $this->get_shipping_address_1() );
	}

	public function get_shipping_house_number(): string {
		wc_deprecated_function( __METHOD__, '8.0.0', 'AddressUtils::get_house_number( $order->get_shipping_address_1() )' );

		return AddressUtils::get_house_number( $this->get_shipping_address_1() );
	}

	public function get_shipping_house_extension(): string {
		wc_deprecated_function( __METHOD__, '8.0.0', 'AddressUtils::get_house_extension( $order->get_shipping_address_1() )' );

		return AddressUtils::get_house_extension( $this->get_shipping_address_1() );
	}

	public function get_billing_street_name() {
		wc_deprecated_function( __METHOD__, '8.0.0', 'AddressUtils::get_street_name( $order->get_billing_address_1() )' );

		return AddressUtils::get_street_name( $this->get_billing_address_1() );
	}

	public function get_billing_house_number() {
		wc_deprecated_function( __METHOD__, '8.0.0', 'AddressUtils::get_house_number( $order->get_billing_address_1() )' );

		return AddressUtils::get_house_number( $this->get_billing_address_1() );
	}

	public function get_billing_house_extension() {
		wc_deprecated_function( __METHOD__, '8.0.0', 'AddressUtils::get_house_extension( $order->get_billing_address_1() )' );

		return AddressUtils::get_house_extension( $this->get_billing_address_1() );
	}
}
