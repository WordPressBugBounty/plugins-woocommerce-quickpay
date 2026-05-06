<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use WC_Order;
use WC_Subscription;

/**
 * Legacy subscription helpers wrapper.
 *
 * The old WC_QuickPay_Subscription class is aliased to this class.
 *
 * @deprecated Use SubscriptionsFacade instead.
 */
class LegacySubscription {
	private static function facade(): SubscriptionsFacade {
		static $facade = null;
		if ( null === $facade ) {
			$facade = new SubscriptionsFacade();
		}

		return $facade;
	}

	/**
	 * @deprecated Use SubscriptionsFacade::is_renewal() instead.
	 */
	public static function is_renewal( $order ): bool {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::is_renewal()' );

		return self::facade()->is_renewal( $order );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::is_active() instead.
	 */
	public static function plugin_is_active(): bool {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::is_active()' );

		return self::facade()->is_active();
	}

	/**
	 * @deprecated Use SubscriptionsFacade::cart_contains_failed_renewal_order_payment() instead.
	 */
	public static function cart_contains_failed_renewal_order_payment() {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::cart_contains_failed_renewal_order_payment()' );

		return self::facade()->cart_contains_failed_renewal_order_payment();
	}

	/**
	 * @deprecated Use SubscriptionsFacade::cart_contains_renewal() instead.
	 */
	public static function cart_contains_renewal() {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::cart_contains_renewal()' );

		return self::facade()->cart_contains_renewal();
	}

	/**
	 * @deprecated Use SubscriptionsFacade::order_contains_early_renewal() instead.
	 */
	public static function order_contains_early_renewal( $order ): bool {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::order_contains_early_renewal()' );

		return self::facade()->order_contains_early_renewal( $order );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::get_subscriptions_for_renewal_order() instead.
	 */
	public static function get_subscriptions_for_renewal_order( $order, bool $single = false ) {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::get_subscriptions_for_renewal_order()' );

		return self::facade()->get_subscriptions_for_renewal_order( $order, $single );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::get_subscriptions_for_order() instead.
	 */
	public static function get_subscriptions_for_order( $order, $args = [] ): array {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::get_subscriptions_for_order()' );

		return self::facade()->get_subscriptions_for_order( $order, $args );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::get_last_subscription_for_order() instead.
	 */
	public static function get_last_subscription_for_order( $order, array $args = [] ): ?WC_Subscription {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::get_last_subscription_for_order()' );

		return self::facade()->get_last_subscription_for_order( $order, $args );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::get_subscription_id() instead.
	 */
	public static function get_subscription_id( WC_Order $order ): ?int {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::get_subscription_id()' );

		return self::facade()->get_subscription_id( $order );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::get_subscription() instead.
	 */
	public static function get_subscription( $entity ): ?WC_Subscription {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::get_subscription()' );

		return self::facade()->get_subscription( $entity );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::is_subscription() instead.
	 */
	public static function is_subscription( $subscription ): bool {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::is_subscription()' );

		return self::facade()->is_subscription( $subscription );
	}

	/**
	 * @deprecated Use SubscriptionsFacade::cart_contains_switches() instead.
	 */
	public static function cart_contains_switches(): bool {
		_deprecated_function( __METHOD__, '8.0.0', SubscriptionsFacade::class . '::cart_contains_switches()' );

		return self::facade()->cart_contains_switches();
	}
}
