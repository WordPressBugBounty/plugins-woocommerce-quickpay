<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;

/**
 * Legacy logger wrapper.
 *
 * The old WC_QuickPay_Log class is aliased to this class.
 *
 * @deprecated Use WooCommerceLogger instead.
 */
class LegacyLog {
	private WooCommerceLogger $logger;
	private string $domain;

	public function __construct( ?WooCommerceLogger $logger = null, string $domain = 'woocommerce-quickpay' ) {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceLogger::class );
		$this->domain = $domain;
		$this->logger = $logger ?: new WooCommerceLogger( $domain );
	}

	/**
	 * @deprecated Use WooCommerceLogger::info() instead.
	 */
	public function add( $param, $file = null, $line = null ): void {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceLogger::class . '::info()' );

		$message = is_array( $param ) ? wp_json_encode( $param, JSON_PRETTY_PRINT ) : (string) $param;

		if ( $file ) {
			$message = sprintf( 'File: %s -> %s', (string) $file, $message );
		}
		if ( $line ) {
			$message = sprintf( 'Line: %s -> %s', (string) $line, $message );
		}

		$this->logger->info( $message );
	}

	/**
	 * @deprecated Use WooCommerceLogger::clear() instead.
	 */
	public function clear() {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceLogger::class . '::clear()' );

		return $this->logger->clear();
	}

	/**
	 * @deprecated Use WooCommerceLogger::info() instead.
	 */
	public function separator(): void {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceLogger::class . '::info()' );

		$this->logger->info( '--------------------' );
	}

	/**
	 * @deprecated Use WooCommerceLogger directly instead.
	 */
	public function get_domain(): string {
		_deprecated_function( __METHOD__, '8.0.0' );

		return $this->domain;
	}

	/**
	 * @deprecated Use WooCommerceLogger::get_admin_link() instead.
	 */
	public function get_admin_link(): ?string {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceLogger::class . '::get_admin_link()' );

		return $this->logger->get_admin_link();
	}
}
