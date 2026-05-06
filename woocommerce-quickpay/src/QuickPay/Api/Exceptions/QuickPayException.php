<?php

namespace QuickpayPSP\QuickPay\Api\Exceptions;

use Exception;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;

class QuickPayException extends Exception {
	protected string $request_url = '';
	protected string $request_data = '';
	protected string $response_data = '';
	protected WooCommerceLogger $logger;

	public function __construct( string $message, int $code = 0, ?Exception $previous = null, string $request_url = '', string $request_data = '', string $response_data = '' ) {
		parent::__construct( $message, $code, $previous );

		$this->request_url   = $request_url;
		$this->request_data  = $request_data;
		$this->response_data = $response_data;

		$this->logger = new WooCommerceLogger();
	}

	public function request_url(): string {
		return $this->request_url;
	}

	public function request_data(): string {
		return $this->request_data;
	}

	public function response_data(): string {
		return $this->response_data;
	}

	/**
	 * Stores the exception dump in the WooCommerce system logs
	 *
	 * @return void
	 */
	public function write_to_logs(): void {
		$this->logger->info( [
			'Quickpay Exception file'    => $this->getFile(),
			'Quickpay Exception line'    => $this->getLine(),
			'Quickpay Exception code'    => $this->getCode(),
			'Quickpay Exception message' => $this->getMessage(),
		] );
	}

	/**
	 * Prints out a standard warning
	 *
	 * @return void
	 */
	public function write_standard_warning(): void {
		echo wp_kses_post( sprintf(
			/* translators: 1: The text domain */
			__( 'An error occurred. For more information check out the <strong>%s</strong> logs inside <strong>WooCommerce -> System Status -> Logs</strong>.', 'woocommerce-quickpay' ),
			$this->logger->source()
		) );
	}
}
