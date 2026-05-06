<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;

class ViabillGateway extends BaseGateway {
	public const ID = 'viabill';

	public function __construct() {
		$this->method_title = 'Quickpay - ViaBill';

		$this->supports_products();

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = GatewaySettingsSchema::base_gateway_fields( $this->method_title );
	}

	public function default_card_type_lock(): string {
		return 'viabill';
	}

	protected function icon_slugs(): array {
		return [ 'viabill' ];
	}
}
