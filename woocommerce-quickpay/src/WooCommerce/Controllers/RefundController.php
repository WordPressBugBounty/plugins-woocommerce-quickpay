<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use WP_Error;

/**
 * Handles refund processing via the QuickPay API.
 */
final class RefundController {

	private ApiClientFactory $api_factory;

	public function __construct( ApiClientFactory $api_factory ) {
		$this->api_factory = $api_factory;
	}

	/**
	 * Processes a refund for the given order.
	 *
	 * @param int        $order_id
	 * @param float|null $amount
	 * @param string     $reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( int $order_id, ?float $amount = null, string $reason = '' ) {
		try {
			$order = woocommerce_quickpay_get_order( $order_id );

			if ( ! $order ) {
				throw new QuickPayException( sprintf( 'Could not load the order with ID: %d', $order_id ) );
			}

			$transaction_id = OrderUtils::get_transaction_id( $order );

			if ( ! $transaction_id ) {
				throw new QuickPayException( sprintf(
					/* translators: 1: the order id */
					__( 'No transaction ID for order: %s', 'woocommerce-quickpay' ),
					$order_id
				) );
			}

			$payment = $this->api_factory->payment();
			$payment->get( $transaction_id );

			if ( ! $payment->is_action_allowed( 'refund' ) ) {
				if ( in_array( $payment->get_current_type(), [ 'authorize', 'recurring' ], true ) ) {
					throw new QuickPayException( __( 'A non-captured payment cannot be refunded.', 'woocommerce-quickpay' ) );
				}

				throw new QuickPayException( __( 'Transaction state does not allow refunds.', 'woocommerce-quickpay' ) );
			}

			$payment->refund( (int) $transaction_id, $order, $amount );

			return true;

		} catch ( QuickPayException $e ) {
			$e->write_to_logs();

			return new WP_Error( 'quickpay_refund_error', $e->getMessage() );
		}
	}
}
