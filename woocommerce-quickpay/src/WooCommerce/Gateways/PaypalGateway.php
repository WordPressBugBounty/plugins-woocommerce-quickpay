<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;

class PaypalGateway extends BaseGateway {
	public const ID = 'quickpay_paypal';

	public function __construct() {
		$this->method_title = 'Quickpay - PayPal';

		$this->supports_products();

		parent::__construct();

		add_filter( 'woocommerce_quickpay_transaction_params_basket', [ $this, '_return_empty_array' ], 30, 2 );
		add_filter( 'woocommerce_quickpay_transaction_params_shipping_row', [ $this, '_return_empty_array' ], 30, 2 );
	}

	/**
	 * @param array $items
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	public function _return_empty_array( array $items, \WC_Order $order ): array {
		if ( $order->get_payment_method() === $this->id ) {
			$items = [];
		}

		return $items;
	}

	public function init_form_fields(): void {
		$this->form_fields = GatewaySettingsSchema::base_gateway_fields( $this->method_title );
	}

	public function default_card_type_lock(): string {
		return 'paypal';
	}

	protected function icon_slugs(): array {
		return [ 'paypal' ];
	}
}
