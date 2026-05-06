<?php

namespace QuickpayPSP\WooCommerce\Support;

use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\Plugin;
use WC_Order;
use WC_Order_Item_Product;
use WP_Post;

final class OrderUtils {
	public static function get_order( $mixed_entity ): ?WC_Order {
		if ( $mixed_entity instanceof WC_Order ) {
			return $mixed_entity;
		}

		switch ( true ) {
			case is_int( $mixed_entity ):
				return wc_get_order( $mixed_entity ) ?: null;
			case $mixed_entity instanceof WP_Post:
				return wc_get_order( $mixed_entity->ID ) ?: null;
			default:
				return null;
		}
	}

	public static function is_failed_renewal( WC_Order $order ): bool {
		$subs = self::subscriptions();

		if ( ! $subs->is_active() ) {
			return false;
		}

		return $subs->is_renewal( $order ) && $order->get_status() === 'failed';
	}

	/**
	 * Checks if an order contains a subscription product.
	 *
	 * @param WC_Order|int $order_id_or_object
	 */
	public static function contains_subscription( $order_id_or_object ): bool {
		if ( ! self::subscriptions()->is_active() ) {
			return false;
		}

		return function_exists( 'wcs_order_contains_subscription' )
			? (bool) wcs_order_contains_subscription( $order_id_or_object )
			: false;
	}

	/**
	 * @param WC_Order|int $order_id_or_object
	 */
	public static function contains_switch_order( $order_id_or_object ): bool {
		return function_exists( 'wcs_order_contains_switch' )
			? (bool) wcs_order_contains_switch( $order_id_or_object )
			: false;
	}

	/**
	 * Checks if the order switches a subscription from free to paid.
	 * In this case we often require authorization of a subscription transaction in order to
	 * be able to authorize payments automatically in the future.
	 */
	public static function switches_from_free_to_paid( WC_Order $order ): bool {
		if ( ! self::contains_switch_order( $order ) ) {
			return false;
		}

		$subscription_id = $order->get_meta( '_subscription_switch' );
		$subscription    = $subscription_id ? self::subscriptions()->get_subscription( $subscription_id ) : null;

		if ( ! $subscription ) {
			return false;
		}

		$old_total = (float) $subscription->get_total();
		$new_total = (float) $order->get_total();

		return $old_total === 0.0 && $new_total > 0;
	}

	public static function add_note( WC_Order $order, ?string $message ): void {
		if ( $message ) {
			$order->add_order_note( 'QuickPay: ' . $message );
		}
	}

	public static function contains_virtual_products( WC_Order $order ): bool {
		$order_items = $order->get_items( 'line_item' );
		foreach ( $order_items as $order_item ) {
			if ( ( $order_item instanceof WC_Order_Item_Product )
				&& ( $product = $order_item->get_product() )
				&& $product->is_virtual()
			) {
				return true;
			}
		}

		return false;
	}

	public static function contains_physical_products( WC_Order $order ): bool {
		$order_items = $order->get_items( 'line_item' );
		foreach ( $order_items as $order_item ) {
			if ( ( $order_item instanceof WC_Order_Item_Product )
				&& ( $product = $order_item->get_product() )
				&& ! $product->is_virtual()
			) {
				return true;
			}
		}

		return false;
	}

	public static function get_transaction_id( WC_Order $order ) {
		// Search for custom transaction meta added in 4.8 to avoid transaction ID
		// sometimes being empty on subscriptions in WC 3.0.
		$transaction_id = $order->get_meta( '_quickpay_transaction_id' );
		if ( empty( $transaction_id ) ) {
			$transaction_id = $order->get_transaction_id();

			if ( empty( $transaction_id ) ) {
				// Search for original transaction ID. The transaction might be temporarily removed by
				// subscriptions. Use this one instead (if available).
				$transaction_id = $order->get_meta( '_transaction_id_original' );
				if ( empty( $transaction_id ) ) {
					// Check if the old legacy TRANSACTION ID meta value is available.
					$transaction_id = $order->get_meta( 'TRANSACTION_ID' );
				}
			}
		}

		return $transaction_id ?: null;
	}

	public static function get_clean_order_number( WC_Order $order ): string {
		return str_replace( '#', '', $order->get_order_number() );
	}

	private static function subscriptions(): SubscriptionsFacade {
		return Plugin::services()->get_as( SubscriptionsFacade::class, 'woocommerce/subscriptions' );
	}
}
