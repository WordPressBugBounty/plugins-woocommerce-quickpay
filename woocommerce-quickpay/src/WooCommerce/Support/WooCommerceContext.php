<?php

namespace QuickpayPSP\WooCommerce\Support;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\Plugin;

final class WooCommerceContext {
	public static function is_subscription_status( $status ): bool {
		if ( strpos( (string) $status, 'wc-' ) !== 0 ) {
			$status = 'wc-' . $status;
		}

		return function_exists( 'wcs_get_subscription_statuses' ) && array_key_exists( $status, wcs_get_subscription_statuses() );
	}

	public static function is_hpos_enabled(): bool {
		if ( ! function_exists( 'wc_get_container' ) || ! class_exists( CustomOrdersTableController::class ) ) {
			return false;
		}

		$controller = wc_get_container()->get( CustomOrdersTableController::class );
		if ( ! $controller || ! method_exists( $controller, 'custom_orders_table_usage_is_enabled' ) ) {
			return false;
		}

		return (bool) $controller->custom_orders_table_usage_is_enabled();
	}

	public static function get_shop_order_screen_id(): string {
		if ( self::is_hpos_enabled() ) {
			return 'woocommerce_page_wc-orders';
		}

		return 'edit-shop_order';
	}

	public static function is_request_to_change_payment(): bool {
		$is_request_to_change_payment = false;

		if ( self::subscriptions()->is_active() ) {
			if ( class_exists( 'WC_Subscriptions_Change_Payment_Gateway' ) ) {
				$is_request_to_change_payment = (bool) \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment;
			}

			if ( ! $is_request_to_change_payment && ! empty( $_GET['quickpay_change_payment_method'] ) ) {
				$is_request_to_change_payment = true;
			}
		}

		return (bool) apply_filters( 'woocommerce_quickpay_is_request_to_change_payment', $is_request_to_change_payment );
	}

	public static function is_current_admin_screen( string ...$screen_ids ): bool {
		$screen = get_current_screen();

		return $screen && in_array( $screen->id, $screen_ids, true );
	}

	public static function get_edit_order_screen_id(): string {
		return self::is_hpos_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
	}

	public static function get_edit_subscription_screen_id(): string {
		return self::is_hpos_enabled() && function_exists( 'wcs_get_page_screen_id' )
			? wcs_get_page_screen_id( 'shop-subscription' )
			: 'shop_subscription';
	}

	private static function subscriptions(): SubscriptionsFacade {
		return Plugin::services()->get_as( SubscriptionsFacade::class, 'woocommerce/subscriptions' );
	}
}
