<?php

namespace QuickpayPSP\Admin\Actions;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Support\Http\HttpClientInterface;
use QuickpayPSP\QuickPay\Api\QuickPayClient;

final class PrivateKeyAction extends AbstractAdminAction {
	private HttpClientInterface $http;
	private GatewaySettingsProvider $settings;

	public function __construct( HttpClientInterface $http, GatewaySettingsProvider $settings ) {
		$this->http     = $http;
		$this->settings = $settings;
	}

	public function action(): string {
		return 'settings/private-key';
	}

	protected function execute(): void {
		try {
			if ( empty( $_POST['api_key'] ) ) {
				throw new \Exception( esc_html__( 'Please type in the API key before requesting a private key', 'woocommerce-quickpay' ) );
			}

			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$api     = new QuickPayClient( $this->http, $this->settings, $api_key );

			$response = $api->get( 'account/private-key' );

			wp_send_json_success( $response );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
