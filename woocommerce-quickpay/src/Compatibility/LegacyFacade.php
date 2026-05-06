<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\Plugin;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Controllers\OrderController;
use QuickpayPSP\WooCommerce\Controllers\PaymentController;

/**
 * Legacy facade returned by WC_QP().
 *
 * The old WC_QP_Legacy_Facade class is aliased to this class.
 *
 * @deprecated Use the individual service classes instead.
 */
class LegacyFacade {
	/** @var LegacyLog */
	public LegacyLog $log;

	private GatewaySettingsProvider $settings;

	public string $id = 'quickpay';

	public function __construct() {
		_deprecated_function( __METHOD__, '8.0.0', 'Plugin::services()' );
		$this->log      = new LegacyLog();
		$this->settings = new GatewaySettingsProvider( 'quickpay' );
	}

	/**
	 * Legacy settings getter — mirrors the old WC_QuickPay::s() behaviour.
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 * @deprecated Use GatewaySettingsProvider::get() instead.
	 *
	 */
	public function s( string $key, $default = null ) {
		_deprecated_function( __METHOD__, '8.0.0', 'GatewaySettingsProvider::get()' );

		$value = $this->settings->get( $key );

		if ( $value === null ) {
			$value = apply_filters(
				'woocommerce_quickpay_get_setting_' . $key,
				! is_null( $default ) ? $default : '',
				$this
			);
		}

		return $value;
	}

	/**
	 * @deprecated Use QUICKPAY_PLUGIN_PATH or plugin_dir_path( Plugin::file() ) instead.
	 */
	public function plugin_path(): string {
		_deprecated_function( __METHOD__, '8.0.0', 'QUICKPAY_PLUGIN_PATH' );

		return defined( 'QUICKPAY_PLUGIN_PATH' ) ? QUICKPAY_PLUGIN_PATH : plugin_dir_path( Plugin::file() );
	}

	/**
	 * @deprecated Use QUICKPAY_PLUGIN_URL or plugin_dir_url( Plugin::file() ) instead.
	 */
	public function plugin_url(): string {
		_deprecated_function( __METHOD__, '8.0.0', 'QUICKPAY_PLUGIN_URL' );

		return defined( 'QUICKPAY_PLUGIN_URL' ) ? QUICKPAY_PLUGIN_URL : plugin_dir_url( Plugin::file() );
	}

	/**
	 * @deprecated Use PaymentController::process_payment() instead.
	 */
	public function process_payment( $order_id ) {
		_deprecated_function( __METHOD__, '8.0.0', PaymentController::class . '::process_payment()' );

		return Plugin::services()->get_as( PaymentController::class, 'woocommerce/payment/controller' )->process_payment( (int) $order_id );
	}

	/**
	 * @deprecated Use OrderController::scheduled_subscription_payment() instead.
	 */
	public function process_recurring_payment( $order_id ) {
		_deprecated_function( __METHOD__, '8.0.0', OrderController::class . '::scheduled_subscription_payment()' );

		$order = woocommerce_quickpay_get_order( $order_id );

		if ( ! $order || ! $order->needs_payment() ) {
			return null;
		}

		return Plugin::services()->get_as( OrderController::class, 'woocommerce/orders/controller' )->scheduled_subscription_payment( (float) $order->get_total(), $order );
	}
}
