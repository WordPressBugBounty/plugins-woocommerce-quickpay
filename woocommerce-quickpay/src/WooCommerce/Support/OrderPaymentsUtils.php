<?php

namespace QuickpayPSP\WooCommerce\Support;

use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\Plugin;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\Utilities\RandomUtils;
use WC_Order;
use WC_Order_Item_Fee;

final class OrderPaymentsUtils {

	public static function prepare_transaction_params( WC_Order $order ): array {
		$subs = self::subscriptions();

		$is_subscription = OrderUtils::contains_subscription( $order )
			|| WooCommerceContext::is_request_to_change_payment()
			|| $subs->is_subscription( $order->get_id() );

		$params_subscription = [];

		if ( $is_subscription ) {
			$params_subscription = [
				'description' => apply_filters( 'woocommerce_quickpay_transaction_params_description', 'woocommerce-subscription', $order ),
			];
		}

		$params = array_merge( [
			'order_id'         => self::get_order_number_for_api( $order ),
			'basket'           => TransactionDataBuilder::get_basket_params( $order ),
			'shipping_address' => TransactionDataBuilder::get_shipping_address( $order ),
			'invoice_address'  => TransactionDataBuilder::get_invoice_address( $order ),
			'shipping'         => TransactionDataBuilder::get_shipping_params( $order ),
			'shopsystem'       => TransactionDataBuilder::get_shop_system_params( $order ),
		], TransactionDataBuilder::get_custom_variables( $order ) );

		return apply_filters( 'woocommerce_quickpay_transaction_params', array_merge( $params, $params_subscription ), $order );
	}

	public static function prepare_transaction_link_params( WC_Order $order ): array {
		return [
			'order_id'    => self::get_order_number_for_api( $order ),
			'continueurl' => TransactionDataBuilder::get_continue_url( $order ),
			'cancelurl'   => TransactionDataBuilder::get_cancellation_url( $order ),
			'amount'      => MoneyUtils::price_multiply( $order->get_total(), $order->get_currency() ),
		];
	}

	public static function get_order_number_for_api( WC_Order $order, bool $recurring = false ): string {
		$minimum_length = 4;

		$order_id = $order->get_id();
		$subs     = self::subscriptions();

		if ( $subs->is_subscription( $order_id ) ) {
			$order_number = $order_id;
		} elseif ( ( ! $recurring || OrderUtils::switches_from_free_to_paid( $order ) ) && OrderUtils::contains_subscription( $order ) ) {
			$subscriptions = $subs->get_subscriptions_for_order( $order_id );
			$subscription  = end( $subscriptions );
			$order_number  = $subscription ? $subscription->get_id() : $order_id;

			if ( ( $failed_payment_count = self::get_failed_payment_count( $order ) ) > 0 ) {
				$order_number .= sprintf( '-%d', $failed_payment_count );
			}
		} else {
			$order_number = OrderUtils::get_clean_order_number( $order );
			if ( ( $failed_payment_count = self::get_failed_payment_count( $order ) ) > 0 ) {
				$order_number .= sprintf( '-%d', $failed_payment_count );
			} elseif ( $subs->cart_contains_failed_renewal_order_payment() ) {
				$subscription = $subs->get_subscriptions_for_renewal_order( $order, true );
				$order_number .= sprintf( '-%s', $subscription ? $subscription->get_failed_payment_count() : self::create_random_string( 3 ) );
			} elseif ( $subs->cart_contains_renewal() ) {
				$order_number .= sprintf( '-%s', self::create_random_string( 3 ) );
			}
		}

		if ( WooCommerceContext::is_request_to_change_payment() ) {
			$order_number .= sprintf( '-%s', self::create_random_string( 3 ) );
		}

		$order_number_length = strlen( $order_number );

		if ( $order_number_length < $minimum_length ) {
			preg_match( '/\d+/', $order_number, $digits );

			if ( ! empty( $digits ) ) {
				$missing_digits = $minimum_length - $order_number_length;
				$order_number   = str_replace( $digits[0], str_pad( $digits[0], strlen( $digits[0] ) + $missing_digits, 0, STR_PAD_LEFT ), $order_number );
			}
		}

		return apply_filters( 'woocommerce_quickpay_order_number_for_api', $order_number, $order, $recurring );
	}

	/**
	 * @deprecated Use OrderPaymentsMeta::get_failed_payment_count() directly.
	 */
	public static function get_failed_payment_count( WC_Order $order ): int {
		return OrderPaymentsMeta::get_failed_payment_count( $order );
	}


	public static function is_order_using_quickpay( WC_Order $order ): bool {
		return in_array( $order->get_payment_method(), [
			'quickpay_anyday',
			'quickpay_apple_pay',
			'quickpay_google_pay',
			'ideal',
			'fbg1886',
			'ideal',
			'klarna',
			'mobilepay',
			'mobilepay_checkout',
			'mobilepay-subscriptions',
			'quickpay_paypal',
			'quickpay',
			'quickpay-extra',
			'resurs',
			'sofort',
			'swish',
			'trustly',
			'viabill',
			'vipps',
		], true );
	}

	private static function create_random_string( int $length ): string {
		return RandomUtils::create_random_string( $length );
	}

	private static function subscriptions(): SubscriptionsFacade {
		return Plugin::services()->get_as( SubscriptionsFacade::class, 'woocommerce/subscriptions' );
	}
}
