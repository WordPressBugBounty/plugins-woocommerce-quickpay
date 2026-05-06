<?php

namespace QuickpayPSP\Utilities;

/**
 * Utility methods for building and validating URLs used throughout the plugin.
 *
 * Covers callback URLs passed to the QuickPay API, the admin API base URL,
 * plugin asset URLs, and the WooCommerce settings page URL.
 *
 * Depends on WordPress/WooCommerce URL helpers (home_url, admin_url, plugins_url).
 * Belongs to the Integration Layer.
 */
final class UrlUtils {
	/**
	 * Checks whether the given value is a valid URL.
	 *
	 * @param mixed $url Value to validate.
	 * @return bool True if the value passes PHP's URL filter.
	 */
	public static function is_url( $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	/**
	 * Builds the callback URL that QuickPay will POST payment notifications to.
	 *
	 * Passes through two filters so integrations can append extra query args
	 * or override the URL entirely:
	 * - woocommerce_quickpay_callback_args
	 * - woocommerce_quickpay_callback_url
	 *
	 * @param int|string|null $post_id Optional order post ID to include as a query arg.
	 * @return string Absolute callback URL.
	 */
	public static function get_callback_url( $post_id = null ): string {
		$args = [ 'wc-api' => 'WC_QuickPay' ];

		if ( $post_id !== null ) {
			$args['order_post_id'] = $post_id;
		}

		$args = apply_filters( 'woocommerce_quickpay_callback_args', $args, $post_id );

		return (string) apply_filters( 'woocommerce_quickpay_callback_url', add_query_arg( $args, home_url( '/' ) ), $args, $post_id );
	}

	/**
	 * Returns the base URL for the plugin's admin WC API endpoint.
	 *
	 * Used when constructing admin AJAX action URLs that go through
	 * the wc-api/quickpay/ rewrite endpoint.
	 *
	 * @return string Absolute URL, forced to HTTPS when the site is on SSL.
	 */
	public static function get_admin_api_base_url(): string {
		return get_home_url( null, 'wc-api/quickpay/', is_ssl() ? 'https' : 'http' );
	}

	/**
	 * Returns the absolute URL to a file inside the plugin directory.
	 *
	 * @param string|null $path Relative path within the plugin, e.g. 'assets/js/app.js'.
	 * @return string Absolute plugin URL.
	 */
	public static function get_plugin_url( ?string $path = null ): string {
		return plugins_url( $path, QUICKPAY_PLUGIN_FILE );
	}

	/**
	 * Returns the URL to the QuickPay section on the WooCommerce settings page.
	 *
	 * @return string Absolute admin URL.
	 */
	public static function get_settings_page_url(): string {
		return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=quickpay' );
	}
}
