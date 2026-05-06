<?php

namespace QuickpayPSP\Admin\Actions;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Support\Http\HttpClientInterface;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\QuickPayClient;

final class PingAction extends AbstractAdminAction {
	private HttpClientInterface $http;
	private GatewaySettingsProvider $settings;

	public function __construct( HttpClientInterface $http, GatewaySettingsProvider $settings ) {
		$this->http     = $http;
		$this->settings = $settings;
	}

	public function action(): string {
		return 'settings/ping';
	}

	protected function execute(): void {
		if ( empty( $_POST['api_key'] ) ) {
			wp_send_json_error();
		}

		try {
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$api     = new QuickPayClient( $this->http, $this->settings, $api_key );
			$api->get( '/payments?page_size=1' );
			wp_send_json_success();
		} catch ( QuickPayApiException $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
