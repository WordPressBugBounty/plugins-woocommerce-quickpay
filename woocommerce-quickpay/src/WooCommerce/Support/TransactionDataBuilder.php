<?php

namespace QuickpayPSP\WooCommerce\Support;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\Plugin;
use QuickpayPSP\Utilities\AddressUtils;
use QuickpayPSP\Utilities\Countries;
use QuickpayPSP\Utilities\MoneyUtils;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Fee;
use WC_Tax;

final class TransactionDataBuilder {

	public static function get_shipping_address( WC_Order $order ): array {
		$shipping_name = trim( $order->get_formatted_shipping_full_name() );
		$company_name  = $order->get_shipping_company();

		if ( empty( $shipping_name ) && ! empty( $company_name ) ) {
			$shipping_name = $company_name;
		}

		$address = (string) $order->get_shipping_address_1();

		$params = [
			'name'            => $shipping_name,
			'street'          => AddressUtils::get_street_name( $address ),
			'house_number'    => AddressUtils::get_house_number( $address ),
			'house_extension' => AddressUtils::get_house_extension( $address ),
			'city'            => $order->get_shipping_city(),
			'region'          => $order->get_shipping_state(),
			'zip_code'        => $order->get_shipping_postcode(),
			'country_code'    => Countries::alpha3_from_alpha2( $order->get_shipping_country() ),
			'phone_number'    => $order->get_billing_phone(),
			'mobile_number'   => $order->get_billing_phone(),
			'email'           => $order->get_billing_email(),
		];

		return apply_filters( 'woocommerce_quickpay_transaction_params_shipping', $params, $order );
	}

	public static function get_invoice_address( WC_Order $order ): array {
		$billing_name = trim( $order->get_formatted_billing_full_name() );
		$company_name = $order->get_billing_company();

		if ( empty( $billing_name ) && ! empty( $company_name ) ) {
			$billing_name = $company_name;
		}

		$address = (string) $order->get_billing_address_1();

		$params = [
			'name'            => $billing_name,
			'street'          => AddressUtils::get_street_name( $address ),
			'house_number'    => AddressUtils::get_house_number( $address ),
			'house_extension' => AddressUtils::get_house_extension( $address ),
			'city'            => $order->get_billing_city(),
			'region'          => $order->get_billing_state(),
			'zip_code'        => $order->get_billing_postcode(),
			'country_code'    => Countries::alpha3_from_alpha2( $order->get_billing_country() ),
			'phone_number'    => $order->get_billing_phone(),
			'mobile_number'   => $order->get_billing_phone(),
			'email'           => $order->get_billing_email(),
		];

		return apply_filters( 'woocommerce_quickpay_transaction_params_invoice', $params, $order );
	}

	public static function get_basket_params( WC_Order $order ): array {
		$basket = [];

		foreach ( $order->get_items() as $item_line ) {
			$basket[] = self::get_transaction_basket_params_line_helper( $order, $item_line );
		}

		if ( apply_filters( 'woocommerce_quickpay_transaction_params_basket_apply_fees', true, $order ) ) {
			foreach ( $order->get_items( 'fee' ) as $item_line ) {
				/** @var WC_Order_Item_Fee $item_line */
				$basket[] = self::get_transaction_basket_params_fee_helper( $order, $item_line );
			}
		}

		return apply_filters( 'woocommerce_quickpay_transaction_params_basket', $basket, $order );
	}

	public static function get_shop_system_params( WC_Order $order ): array {
		$version = defined( 'WCQP_VERSION' ) ? WCQP_VERSION : '';

		$params = [
			'name'    => 'WooCommerce',
			'version' => $version,
		];

		return apply_filters( 'woocommerce_quickpay_transaction_params_shopsystem', $params, $order );
	}

	public static function get_custom_variables( WC_Order $order ): array {
		$custom_vars_settings = (array) self::settings()->get( 'quickpay_custom_variables', [] );
		$custom_vars          = [];

		if ( in_array( 'customer_email', $custom_vars_settings, true ) ) {
			$custom_vars[ esc_html__( 'Customer Email', 'woocommerce-quickpay' ) ] = $order->get_billing_email();
		}

		if ( in_array( 'customer_phone', $custom_vars_settings, true ) ) {
			$custom_vars[ esc_html__( 'Customer Phone', 'woocommerce-quickpay' ) ] = $order->get_billing_phone();
		}

		if ( in_array( 'browser_useragent', $custom_vars_settings, true ) ) {
			$custom_vars[ esc_html__( 'User Agent', 'woocommerce-quickpay' ) ] = $order->get_customer_user_agent();
		}

		if ( in_array( 'shipping_method', $custom_vars_settings, true ) ) {
			$custom_vars[ esc_html__( 'Shipping Method', 'woocommerce-quickpay' ) ] = $order->get_shipping_method();
		}

		$custom_vars['order_post_id'] = $order->get_id();

		if ( $subscription_id = self::subscriptions()->get_subscription_id( $order ) ) {
			$custom_vars['subscription_post_id'] = $subscription_id;
		}

		if ( WooCommerceContext::is_request_to_change_payment() ) {
			$custom_vars['change_payment'] = true;
		}

		$custom_vars['payment_method'] = $order->get_payment_method();

		$custom_vars = apply_filters( 'woocommerce_quickpay_transaction_params_variables', $custom_vars, $order );

		ksort( $custom_vars );

		return [ 'variables' => $custom_vars ];
	}

	public static function get_continue_url( WC_Order $order ): string {
		if ( method_exists( $order, 'get_checkout_order_received_url' ) ) {
			return $order->get_checkout_order_received_url();
		}

		return add_query_arg( 'key', $order->get_order_key(), add_query_arg( 'order', $order->get_id(), get_permalink( get_option( 'woocommerce_thanks_page_id' ) ) ) );
	}

	public static function get_cancellation_url( WC_Order $order ): string {
		if ( method_exists( $order, 'get_cancel_order_url' ) ) {
			return str_replace( '&amp;', '&', $order->get_cancel_order_url() );
		}

		return add_query_arg( 'key', $order->get_order_key(), add_query_arg( [
			'order'                => $order->get_id(),
			'payment_cancellation' => 'yes',
		], get_permalink( get_option( 'woocommerce_cart_page_id' ) ) ) );
	}

	public static function should_auto_capture_order( WC_Order $order ): bool {
		$auto_capture_default = wc_string_to_bool( self::settings()->get( 'quickpay_autocapture', 'no' ) );
		$auto_capture_virtual = wc_string_to_bool( self::settings()->get( 'quickpay_autocapture_virtual', 'no' ) );

		$has_virtual_products  = OrderUtils::contains_virtual_products( $order );
		$has_physical_products = OrderUtils::contains_physical_products( $order );

		if ( $auto_capture_default === $auto_capture_virtual ) {
			return $auto_capture_default;
		}

		if ( $has_virtual_products && $has_physical_products ) {
			return $auto_capture_default;
		}

		if ( $has_virtual_products ) {
			return $auto_capture_virtual;
		}

		return $auto_capture_default;
	}

	public static function get_shipping_params( WC_Order $order ): array {
		$shipping_tax      = (float) $order->get_shipping_tax();
		$shipping_total    = (float) $order->get_shipping_total();
		$shipping_incl_vat = $shipping_total;
		$shipping_vat_rate = 0;

		if ( $shipping_tax && $shipping_total ) {
			$shipping_incl_vat += $shipping_tax;
			$shipping_vat_rate = $shipping_tax / $shipping_total;
		}

		return apply_filters( 'woocommerce_quickpay_transaction_params_shipping_row', [
			'method'   => 'own_delivery',
			'company'  => $order->get_shipping_method(),
			'amount'   => MoneyUtils::price_multiply( $shipping_incl_vat, $order->get_currency() ),
			'vat_rate' => $shipping_vat_rate,
		], $order );
	}

	private static function get_transaction_basket_params_line_helper( WC_Order $order, WC_Order_Item $line_item ): array {
		$price = ( (float) $line_item->get_total() + (float) $line_item->get_total_tax() ) / $line_item->get_quantity();

		return [
			'qty'        => $line_item->get_quantity(),
			'item_no'    => $line_item->get_product_id(),
			'item_name'  => esc_attr( $line_item->get_name() ),
			'item_price' => MoneyUtils::price_multiply( $price, $order->get_currency() ),
			'vat_rate'   => self::get_tax_rate( $line_item ),
		];
	}

	private static function get_transaction_basket_params_fee_helper( WC_Order $order, WC_Order_Item_Fee $fee_item ): array {
		$price = ( (float) $fee_item->get_total() + (float) $fee_item->get_total_tax() ) / $fee_item->get_quantity();

		return [
			'qty'        => $fee_item->get_quantity(),
			'item_no'    => sanitize_title( $fee_item->get_name() ),
			'item_name'  => esc_attr( $fee_item->get_name() ),
			'item_price' => MoneyUtils::price_multiply( $price, $order->get_currency() ),
			'vat_rate'   => self::get_tax_rate( $fee_item ),
		];
	}

	private static function get_tax_rate( WC_Order_Item $item ) {
		$vat_rate = 0;

		if ( wc_tax_enabled() ) {
			$taxes    = WC_Tax::get_rates( $item->get_tax_class() );
			$rates    = array_shift( $taxes );
			$vat_rate = ! empty( $rates ) ? round( array_shift( $rates ) ) : 0;
		}

		return $vat_rate > 0 ? $vat_rate / 100 : 0;
	}

	private static function settings(): GatewaySettingsProvider {
		return Plugin::services()->get_as( GatewaySettingsProvider::class, 'gateway/settings_provider' );
	}

	private static function subscriptions(): SubscriptionsFacade {
		return Plugin::services()->get_as( SubscriptionsFacade::class, 'woocommerce/subscriptions' );
	}
}
