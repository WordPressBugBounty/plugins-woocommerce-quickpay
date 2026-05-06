<?php

namespace QuickpayPSP\Support\Config;

/**
 * Reads and exposes WooCommerce gateway settings stored in wp_options.
 *
 * All shared settings are read from the main gateway's option key; per-gateway
 * overrides (e.g. title, description, enabled) can be fetched via getForGateway().
 * Lives in the Infrastructure layer — no business logic, only option retrieval.
 */
class GatewaySettingsProvider {
	private string $mainGatewayId;

	public function __construct( string $mainGatewayId ) {
		$this->mainGatewayId = $mainGatewayId;
	}

	/**
	 * Shared setting from MAIN gateway.
	 *
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		$settings = $this->mainSettings();

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * Setting from a specific gateway (useful for title/description/enabled per method gateway).
	 *
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function getForGateway( string $gatewayId, string $key, $default = null ) {
		$settings = $this->gatewaySettings( $gatewayId );

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * Returns whether the main gateway is enabled.
	 *
	 * @return bool
	 */
	public function isMainEnabled(): bool {
		return (string) $this->get( 'enabled', 'no' ) === 'yes';
	}

	/**
	 * Returns the QuickPay API key configured on the main gateway.
	 *
	 * @return string
	 */
	public function getApiKey(): string {
		return (string) $this->get( 'quickpay_apikey', '' );
	}

	/**
	 * Returns the QuickPay private key configured on the main gateway.
	 *
	 * @return string
	 */
	public function getPrivateKey(): string {
		return (string) $this->get( 'quickpay_privatekey', '' );
	}

	/**
	 * Returns the card type lock setting, defaulting to 'creditcard' if not set.
	 *
	 * @param string $default Fallback value when the setting is absent.
	 *
	 * @return string
	 */
	public function getCardtypelock( string $default = 'creditcard' ): string {
		return (string) $this->get( 'quickpay_cardtypelock', $default );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function mainSettings(): array {
		return $this->readSettingsArray( $this->optionKey( $this->mainGatewayId ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function gatewaySettings( string $gatewayId ): array {
		return $this->readSettingsArray( $this->optionKey( $gatewayId ) );
	}

	/**
	 * Builds the wp_options key for a given gateway ID.
	 *
	 * @param string $gatewayId WooCommerce gateway ID.
	 *
	 * @return string
	 */
	private function optionKey( string $gatewayId ): string {
		return 'woocommerce_' . $gatewayId . '_settings';
	}

	/**
	 * Reads a settings array from wp_options, returning an empty array on failure.
	 *
	 * @param string $optionKey The wp_options key to read.
	 *
	 * @return array<string, mixed>
	 */
	private function readSettingsArray( string $optionKey ): array {
		$value = get_option( $optionKey, [] );

		return is_array( $value ) ? $value : [];
	}
}
