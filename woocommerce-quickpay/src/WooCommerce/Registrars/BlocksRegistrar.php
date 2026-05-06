<?php

namespace QuickpayPSP\WooCommerce\Registrars;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use QuickpayPSP\WooCommerce\Blocks\Integrations\QuickPayBlock;
use QuickpayPSP\WooCommerce\Controllers\BlocksController;
use QuickpayPSP\WooCommerce\Gateways\BaseGateway;
use QuickpayPSP\WooCommerce\Support\GatewayUtils;

class BlocksRegistrar {

	/**
	 * @var BlocksController
	 */
	protected $controller;

	/**
	 * BlocksRegistrar constructor.
	 *
	 * @param BlocksController $controller
	 */
	public function __construct( BlocksController $controller ) {
		$this->controller = $controller;
	}

	/**
	 * @return void
	 */
	public function register(): void {
		if ( did_action( 'woocommerce_blocks_loaded' ) ) {
			$this->check_blocks_support();
		} else {
			add_action( 'woocommerce_blocks_loaded', [ $this, 'check_blocks_support' ] );
		}

		// Stylesheets
		add_action( 'wp_print_footer_scripts', [ $this->controller, 'maybe_apply_block_styles' ], 1 );
		add_action( 'admin_print_scripts', [ $this->controller, 'maybe_apply_block_styles' ], 5 );
	}

	/**
	 * Checks if the current environment supports blocks integration.
	 *
	 * @return void
	 */
	public function check_blocks_support(): void {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			add_action( 'woocommerce_blocks_payment_method_type_registration', [ $this, 'register_gateway_blocks' ] );
		}
	}

	/**
	 * Registers gateway blocks for the specified payment method registry.
	 *
	 * @param PaymentMethodRegistry $payment_method_registry
	 *
	 * @return void
	 */
	public function register_gateway_blocks( PaymentMethodRegistry $payment_method_registry ): void {
		$this->register_module_block_settings();

		foreach ( GatewayUtils::get_plugin_gateways() as $payment_gateway ) {
			if ( $payment_gateway instanceof BaseGateway ) {
				$payment_method_registry->register( new QuickPayBlock( $payment_gateway ) );
			}
		}
	}

	/**
	 * @return void
	 */
	public function register_module_block_settings(): void {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry' ) ) {
			\Automattic\WooCommerce\Blocks\Package::container()
				->get( \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class )
				->add( 'quickpay-plugin', $this->controller->get_plugin_settings_data() );
		}
	}
}
