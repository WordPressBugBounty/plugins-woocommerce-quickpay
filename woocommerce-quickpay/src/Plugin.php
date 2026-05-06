<?php

namespace QuickpayPSP;

use QuickpayPSP\Admin\Controllers\PluginController;
use QuickpayPSP\Admin\Registrars\AdminNoticesRegistrar;
use QuickpayPSP\Admin\Registrars\AssetsRegistrar;
use QuickpayPSP\Extensions\ExtensionsRegistrar;
use QuickpayPSP\Providers\ApiProvider;
use QuickpayPSP\Providers\ExtensionsProvider;
use QuickpayPSP\Providers\GatewayProvider;
use QuickpayPSP\Providers\InstallProvider;
use QuickpayPSP\Providers\WooCommerceProvider;
use QuickpayPSP\Providers\WordPressProvider;
use QuickpayPSP\Support\Services;
use QuickpayPSP\WooCommerce\Controllers\OrderActionsController;
use QuickpayPSP\WooCommerce\Controllers\SubscriptionActionsController;
use QuickpayPSP\WooCommerce\Registrars\CallbacksRegistrar;
use QuickpayPSP\WooCommerce\Registrars\EmailsRegistrar;
use QuickpayPSP\WooCommerce\Registrars\GatewayRegistrar;
use QuickpayPSP\WooCommerce\Registrars\OrderListTableRegistrar;
use QuickpayPSP\WooCommerce\Registrars\OrderMetaRegistrar;
use QuickpayPSP\WooCommerce\Registrars\BlocksRegistrar;
use QuickpayPSP\WooCommerce\Registrars\OrdersRegistrar;
use QuickpayPSP\WooCommerce\Gateways\GatewayHandlerRegistrar;
use QuickpayPSP\WooCommerce\Registrars\SubscriptionsRegistrar;
use QuickpayPSP\WooCommerce\WooGuard;

class Plugin {
	private static bool $booted = false;
	private static ?Services $services = null;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		WooGuard::when_woocommerce_ready( static function () {
			self::services()->get_as( GatewayRegistrar::class, 'gateway/registrar' )->register();
			self::services()->get_as( OrdersRegistrar::class, 'woocommerce/orders/registrar' )->register();
			self::services()->get_as( OrderListTableRegistrar::class, 'woocommerce/admin/order-list-table/registrar' )->register();
			self::services()->get_as( OrderMetaRegistrar::class, 'woocommerce/admin/order-meta/registrar' )->register();
			self::services()->get_as( EmailsRegistrar::class, 'woocommerce/emails/registrar' )->register();
			self::services()->get_as( BlocksRegistrar::class, 'woocommerce/blocks/registrar' )->register();
			self::services()->get_as( OrderActionsController::class, 'woocommerce/admin/order-actions' )->register();
			self::services()->get_as( SubscriptionActionsController::class, 'woocommerce/admin/subscription-actions' )->register();
			self::services()->get_as( SubscriptionsRegistrar::class, 'woocommerce/subscriptions/registrar' )->register();
			self::services()->get_as( CallbacksRegistrar::class, 'woocommerce/callbacks/registrar' )->register();
			self::services()->get_as( GatewayHandlerRegistrar::class, 'woocommerce/gateway-callbacks/registrar' )->register();
		} );

		self::services()->get( 'wordpress/allowed_redirects' )->register();
		self::services()->get( 'wordpress/admin-ajax/registrar' )->register();
		self::services()->get( 'install/action/run-upgrader' )->register();

		$plugin_controller = self::services()->get_as( PluginController::class, 'wordpress/plugin/controller' );
		add_action( 'in_plugin_update_message-woocommerce-quickpay/woocommerce-quickpay.php', [ $plugin_controller, 'in_plugin_update_message' ] );
		add_filter( 'plugin_action_links_woocommerce-quickpay/woocommerce-quickpay.php', [ $plugin_controller, 'add_action_links' ] );

		add_action( 'plugins_loaded', static function () {
			self::services()->get_as( ExtensionsRegistrar::class, 'extensions/registrar' )->register();
			self::services()->get_as( AdminNoticesRegistrar::class, 'wordpress/admin-notices/registrar' )->register();
		}, 20 );

		do_action( 'woocommerce_quickpay_loaded' );
	}

	public static function services(): Services {
		if ( self::$services ) {
			return self::$services;
		}
		$s = new Services();

		( new AssetsRegistrar() )->register();
		( new InstallProvider() )->register( $s, QUICKPAY_PLUGIN_PATH );
		( new WordPressProvider() )->register( $s );
		( new ApiProvider() )->register( $s );
		( new GatewayProvider() )->register( $s );
		( new ExtensionsProvider() )->register( $s );
		( new WooCommerceProvider() )->register( $s );

		return self::$services = $s;
	}
}
