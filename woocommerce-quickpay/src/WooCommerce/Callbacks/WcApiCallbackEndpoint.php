<?php

declare( strict_types=1 );

namespace QuickpayPSP\WooCommerce\Callbacks;

use JsonException;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Controllers\CallbackController;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;

/**
 * Handles the raw callback from QuickPay via WooCommerce's WC-API.
 */
class WcApiCallbackEndpoint {
	private CallbackController $controller;
	private WooCommerceLogger $logger;
	private GatewaySettingsProvider $settings;

	public function __construct(
		CallbackController $controller,
		WooCommerceLogger $logger,
		GatewaySettingsProvider $settings
	) {
		$this->controller = $controller;
		$this->logger     = $logger;
		$this->settings   = $settings;
	}

	/**
	 * Main handler for the 'woocommerce_api_wc_quickpay' action.
	 */
	public function handle(): void {
		try {
			$request_body = file_get_contents( 'php://input' );

			if ( empty( $request_body ) ) {
				$this->logger->error( 'Callback received with empty body.' );
				status_header( 400 );
				exit;
			}

			if ( ! $this->validate_checksum( $request_body ) ) {
				$this->logger->error( 'Callback received with invalid checksum.' );
				status_header( 401 );
				exit;
			}

			$data = json_decode( $request_body, false, 512, JSON_THROW_ON_ERROR );

			$this->controller->handle_callback( $data, $request_body );

			status_header( 200 );
		} catch ( JsonException $e ) {
			$this->logger->error( 'Failed to decode callback JSON: ' . $e->getMessage() );
			status_header( 400 );
		} catch ( \Exception $e ) {
			$this->logger->error( 'Error handling callback: ' . $e->getMessage() );
			status_header( 500 );
		}
		exit;
	}

	/**
	 * Validates the QuickPay checksum.
	 */
	private function validate_checksum( string $request_body ): bool {
		$checksum = $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256'] ?? '';

		if ( empty( $checksum ) ) {
			return false;
		}

		$private_key = $this->settings->getPrivateKey();

		if ( empty( $private_key ) ) {
			$this->logger->error( 'QuickPay Private Key is not configured. Cannot validate callback checksum.' );

			return false;
		}

		return hash_hmac( 'sha256', $request_body, $private_key ) === $checksum;
	}
}
