<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;
use QuickpayPSP\WooCommerce\Gateways\GatewayIcons;

class ExtraGateway extends BaseGateway {

	public const ID = 'quickpay-extra';

	public function __construct() {
		$this->method_title = 'Quickpay - Extra';

		$this->supports_products();

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = array_merge( GatewaySettingsSchema::base_gateway_fields( $this->method_title ), [
			'cardtypelock'   => [
				'title'       => esc_html__( 'Payment methods', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'Default: creditcard. Type in the cards you wish to accept (comma separated). See the valid payment types here: <b>https://learn.quickpay.net/tech-talk/appendixes/payment-methods/#payment-methods</b>', 'woocommerce-quickpay' ),
				'default'     => 'creditcard',
			],
			'quickpay_icons' => [
				'title'             => esc_html__( 'Credit card icons', 'woocommerce-quickpay' ),
				'type'              => 'multiselect',
				'description'       => esc_html__( 'Choose the card icons you wish to show next to the QuickPay payment option in your shop.', 'woocommerce-quickpay' ),
				'desc_tip'          => true,
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 450px;',
				'custom_attributes' => [
					'data-placeholder' => esc_html__( 'Select icons', 'woocommerce-quickpay' )
				],
				'default'           => '',
				'options'           => GatewayIcons::all(),
			],
		] );
	}

	public function default_card_type_lock(): string {
		return '';
	}
}
