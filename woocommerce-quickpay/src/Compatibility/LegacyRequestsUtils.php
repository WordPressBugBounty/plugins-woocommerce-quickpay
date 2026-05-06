<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\WooCommerce\Support\WooCommerceContext;

/**
 * @deprecated Use WooCommerceContext instead.
 */
final class LegacyRequestsUtils {

	/**
	 * @deprecated Use WooCommerceContext::is_request_to_change_payment() instead.
	 */
	public static function is_request_to_change_payment(): bool {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceContext::class . '::is_request_to_change_payment()' );

		return WooCommerceContext::is_request_to_change_payment();
	}

	/**
	 * @deprecated Use WooCommerceContext::is_current_admin_screen() instead.
	 */
	public static function is_current_admin_screen( string ...$screen_ids ): bool {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceContext::class . '::is_current_admin_screen()' );

		return WooCommerceContext::is_current_admin_screen( ...$screen_ids );
	}

	/**
	 * @deprecated Use WooCommerceContext::get_edit_order_screen_id() instead.
	 */
	public static function get_edit_order_screen_id(): string {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceContext::class . '::get_edit_order_screen_id()' );

		return WooCommerceContext::get_edit_order_screen_id();
	}

	/**
	 * @deprecated Use WooCommerceContext::get_edit_subscription_screen_id() instead.
	 */
	public static function get_edit_subscription_screen_id(): string {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceContext::class . '::get_edit_subscription_screen_id()' );

		return WooCommerceContext::get_edit_subscription_screen_id();
	}
}
