<?php

namespace QuickpayPSP\WooCommerce\Blocks\Integrations;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use QuickpayPSP\WooCommerce\Gateways\BaseGateway;

class QuickPayBlock extends AbstractPaymentMethodType {

	/**
	 * @var BaseGateway
	 */
	protected $gateway;

	/**
	 * QuickPayBlock constructor.
	 *
	 * @param BaseGateway $gateway
	 */
	public function __construct( BaseGateway $gateway ) {
		$this->gateway = $gateway;
		$this->name    = 'payment-gateway-' . $this->gateway->id;
	}

	/**
	 * @return void
	 */
	public function initialize() {
		$this->settings = $this->gateway->settings;
	}

	/**
	 * Retrieves the script handles for the payment method.
	 *
	 * @return string[] An array of script handles.
	 */
	public function get_payment_method_script_handles() {
		$dependencies = [
			'wc-blocks-registry',
			'wc-settings',
			'wp-element',
			'wp-html-entities',
			'wp-i18n',
			'wp-hooks',
		];

		$plugin_url = defined( 'QUICKPAY_PLUGIN_FILE' ) ? plugin_dir_url( QUICKPAY_PLUGIN_FILE ) : '';

		wp_register_script(
			'quickpay-blocks-integration',
			$plugin_url . 'assets/javascript/checkout-blocks.js',
			$dependencies,
			null,
			[ 'in_footer' => true ]
		);

		wp_register_style(
			'quickpay-blocks-integration-styles',
			$plugin_url . 'assets/stylesheets/checkout-blocks.css',
			[],
			null
		);

		return [ 'quickpay-blocks-integration' ];
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'label'       => $this->gateway->get_title(),
			'description' => $this->gateway->description,
			'supports'    => $this->gateway->supports,
			'icon'        => $this->gateway->get_icon(),
		];
	}
}
