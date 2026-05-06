<?php

namespace QuickpayPSP\Admin\Support;

use QuickpayPSP\Utilities\UrlUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;

final class AssetEnqueuer {
	public static function enqueue_javascript_backend(): void {
		if ( self::maybe_enqueue_admin_statics() ) {
			wp_enqueue_script( 'quickpay-backend', UrlUtils::get_plugin_url( '/assets/javascript/backend.js' ), [ 'jquery' ], self::static_version() );
			wp_localize_script( 'quickpay-backend', 'quickpayBackend', [
				'ajax_url' => UrlUtils::get_admin_api_base_url(),
				'nonce'    => wp_create_nonce( 'manage-woocommerce-quickpay' ),
			] );

			self::enqueue_stylesheet();
		}

		wp_enqueue_script( 'quickpay-backend-notices', UrlUtils::get_plugin_url( '/assets/javascript/backend-notices.js' ), [ 'jquery' ], self::static_version() );
		wp_localize_script( 'quickpay-backend-notices', 'wcqpBackendNotices', [ 'flush' => admin_url( 'admin-ajax.php?action=woocommerce_quickpay_flush_runtime_errors' ) ] );
	}

	public static function enqueue_stylesheet(): void {
		wp_enqueue_style( 'woocommere-quickpay-style', UrlUtils::get_plugin_url( '/assets/stylesheets/woocommerce-quickpay.css' ), [], self::static_version() );
	}

	public static function load_i18n(): void {
		load_plugin_textdomain( 'woocommerce-quickpay', false, dirname( plugin_basename( QUICKPAY_PLUGIN_FILE ) ) . '/languages/' );
	}

	public static function static_version(): string {
		return 'wcqp-' . ( defined( 'WCQP_VERSION' ) ? WCQP_VERSION : 'dev' );
	}

	protected static function maybe_enqueue_admin_statics(): bool {
		if ( isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) ) {
			if ( $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'checkout' && $_GET['section'] === 'quickpay' ) {
				return true;
			}
		} elseif ( WooCommerceContext::is_current_admin_screen( WooCommerceContext::get_edit_order_screen_id(), WooCommerceContext::get_edit_subscription_screen_id(), 'edit-shop_order', 'edit-shop_subscription' ) ) {
			return true;
		}

		return false;
	}
}
