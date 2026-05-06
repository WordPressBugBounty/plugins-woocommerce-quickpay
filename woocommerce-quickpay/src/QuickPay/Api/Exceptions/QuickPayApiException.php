<?php

namespace QuickpayPSP\QuickPay\Api\Exceptions;

class QuickPayApiException extends QuickPayException {
	/**
	 * Stores the exception dump in the WooCommerce system logs
	 *
	 * @return void
	 */
	public function write_to_logs(): void {
		$log_data = [
			'Quickpay Exception file'    => $this->getFile(),
			'Quickpay Exception line'    => $this->getLine(),
			'Quickpay Exception code'    => $this->getCode(),
			'Quickpay Exception message' => $this->getMessage(),
		];

		if ( ! empty( $this->request_url ) ) {
			$log_data['Quickpay API Exception Request URL'] = $this->request_url;
		}

		if ( ! empty( $this->request_data ) ) {
			$log_data['Quickpay API Exception Request DATA'] = $this->request_data;
		}

		if ( ! empty( $this->response_data ) ) {
			$log_data['Quickpay API Exception Response DATA'] = $this->response_data;
		}

		$this->logger->info( $log_data );
	}
}
