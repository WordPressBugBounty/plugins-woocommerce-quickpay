<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;

class VippsGateway extends BaseGateway {
	public const ID = 'vipps';

	public function __construct() {
		$this->method_title = 'Quickpay - Vipps';

		$this->supports_products();

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = GatewaySettingsSchema::base_gateway_fields( $this->method_title );
	}

	public function default_card_type_lock(): string {
		return 'vipps,vippspsp';
	}

	protected function icon_slugs(): array {
		return [ 'vipps' ];
	}
}
