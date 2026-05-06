<?php

namespace QuickpayPSP\WooCommerce\Subscriptions;

use Automattic\WooCommerce\Utilities\OrderUtil;
use QuickpayPSP\Support\Dependencies;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use WC_Order;
use WC_Subscription;

final class SubscriptionsFacade {
	public function is_active(): bool {
		return Dependencies::is_woo_subscriptions_active();
	}

	public function is_renewal( $order ): bool {
		return function_exists( 'wcs_order_contains_renewal' ) && (bool) \wcs_order_contains_renewal( $order );
	}

	public function is_subscription( $subscription ): bool {
		return function_exists( 'wcs_is_subscription' ) && (bool) \wcs_is_subscription( $subscription );
	}

	/** @return WC_Subscription[] */
	public function get_subscriptions_for_order( $order, array $args = [] ): array {
		return function_exists( 'wcs_get_subscriptions_for_order' )
			? (array) \wcs_get_subscriptions_for_order( $order, $args )
			: [];
	}

	/** @return WC_Subscription|WC_Subscription[] */
	public function get_subscriptions_for_renewal_order( $order, bool $single = false ) {
		if ( ! function_exists( 'wcs_get_subscriptions_for_renewal_order' ) ) {
			return $single ? null : [];
		}

		$subs = (array) wcs_get_subscriptions_for_renewal_order( $order );

		return $single ? $this->last_item_in_array( $subs ) : $subs;
	}

	public function get_last_subscription_for_order( $order, array $args = [] ): ?WC_Subscription {
		$subs = $this->get_subscriptions_for_order( $order, $args );

		return $this->last_item_in_array( $subs );
	}

	public function get_subscription_id( WC_Order $order ): ?int {
		$orderId = $order->get_id();

		if ( $this->is_subscription( $orderId ) ) {
			return $orderId;
		}

		// hvis du har din egen contains_subscription util, behold den i integration/woo
		if ( OrderUtils::contains_subscription( $order ) ) {
			$subs = $this->get_subscriptions_for_order( $orderId );
			$last = end( $subs );

			return $last ? $last->get_id() : null;
		}

		if ( $this->is_renewal( $order ) ) {
			$subs = $this->get_subscriptions_for_order( $order, [ 'order_type' => [ 'renewal' ] ] );
			$last = end( $subs );

			return $last ? $last->get_id() : null;
		}

		return null;
	}

	public function get_subscription( $entity ): ?WC_Subscription {
		if ( ! $this->is_active() ) {
			return null;
		}

		if ( ! is_object( $entity ) ) {
			return function_exists( 'wcs_get_subscription' )
				? \wcs_get_subscription( $entity )
				: null;
		}

		$type = OrderUtil::get_order_type( $entity );

		if ( $type === 'shop_subscription' ) {
			/** @var WC_Subscription $entity */
			return $entity;
		}

		if ( $type === 'shop_order' ) {
			$subs = $this->get_subscriptions_for_order( $entity );

			return $this->last_item_in_array( $subs );
		}

		return null;
	}

	public function cart_contains_failed_renewal_order_payment() {
		return function_exists( 'wcs_cart_contains_failed_renewal_order_payment' )
			? \wcs_cart_contains_failed_renewal_order_payment()
			: false;
	}

	public function cart_contains_renewal() {
		return function_exists( 'wcs_cart_contains_renewal' )
			? \wcs_cart_contains_renewal()
			: false;
	}

	public function order_contains_early_renewal( $order ): bool {
		return function_exists( 'wcs_order_contains_early_renewal' ) && (bool) \wcs_order_contains_early_renewal( $order );
	}

	public function cart_contains_switches(): bool {
		return class_exists( 'WC_Subscriptions_Switcher' )
		       && method_exists( 'WC_Subscriptions_Switcher', 'cart_contains_switches' )
		       && \WC_Subscriptions_Switcher::cart_contains_switches() !== false;
	}


	/**
	 * @param array $array
	 *
	 * @return false|mixed|null
	 */
	private function last_item_in_array( array $array ) {
		if ( empty( $array ) ) {
			return null;
		}

		return end( $array );
	}
}
