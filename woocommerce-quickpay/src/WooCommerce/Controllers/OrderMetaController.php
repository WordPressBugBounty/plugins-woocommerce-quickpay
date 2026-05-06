<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;

class OrderMetaController {
	private ApiClientFactory $api_factory;

	public function __construct( ApiClientFactory $api_factory ) {
		$this->api_factory = $api_factory;
	}

	/**
	 * Inserts the content of the API actions meta box - Payments
	 *
	 * @param \WC_Order|\WP_Post $post_or_order_object
	 *
	 * @return void
	 */
	public function meta_box_payment( $post_or_order_object ): void {
		if ( ! $order = woocommerce_quickpay_get_order( $post_or_order_object ) ) {
			return;
		}

		$transaction_id = OrderUtils::get_transaction_id( $order );

		$template_data = [
			'transaction_id' => $transaction_id
		];

		if ( $transaction_id ) {
			$state = null;
			try {
  		( $transaction = $this->api_factory->payment() )->get( $transaction_id );
				$transaction->cache_transaction();

				$state = $transaction->get_state();

				try {
					$status = $transaction->get_current_type();
				} catch ( QuickPayApiException $e ) {
					if ( $state !== 'initial' ) {
						throw new QuickPayApiException( $e->getMessage() );
					}

					$status = $state;
				}

				$template_data = array_merge( $template_data, [
					'transaction'          => $transaction,
					'transaction_brand'    => $transaction->get_brand(),
					'transaction_status'   => $status,
					'transaction_order_id' => OrderPaymentsMeta::get_transaction_order_id( $order ),
				] );
			} catch ( QuickPayException|QuickPayApiException $e ) {
				$e->write_to_logs();
				if ( $state !== 'initial' ) {
					$e->write_standard_warning();
				}
			}
		}

		// Show payment ID and payment link for orders that have not yet
		// been paid. Show this information even if the transaction ID is missing.
		$template_data['payment_id']   = OrderPaymentsMeta::get_payment_id( $order );
		$template_data['payment_link'] = OrderPaymentsMeta::get_payment_link( $order );

		$template_data = apply_filters( 'woocommerce_quickpay_payment_meta_box_template_data', $template_data, $order );

		do_action( 'woocommerce_quickpay_meta_box_payment_before_content', $order, $template_data );

		woocommerce_quickpay_get_template( 'admin/meta-box-order.php', $template_data );

		do_action( 'woocommerce_quickpay_meta_box_payment_after_content', $order, $template_data );
	}

	/**
	 * Inserts the content of the API actions meta box - Subscriptions
	 *
	 * @param \WC_Subscription|\WP_Post $post_or_subscription_object
	 *
	 * @return void
	 */
	public function meta_box_subscription( $post_or_subscription_object ): void {
		if ( ! $subscription = woocommerce_quickpay_get_subscription( $post_or_subscription_object ) ) {
			return;
		}

		$transaction_id = OrderUtils::get_transaction_id( $subscription );

		$template_data = [
			'transaction_id' => $transaction_id
		];

		if ( $transaction_id && OrderPaymentsUtils::is_order_using_quickpay( $subscription ) ) {
			$state = null;
			try {
  		$transaction = $this->api_factory->subscription();
  		$transaction->get( $transaction_id );
				$state = $transaction->get_state();
				try {
					$status = $transaction->get_current_type() . ' (' . esc_html__( 'subscription', 'woocommerce-quickpay' ) . ')';
				} catch ( QuickPayApiException $e ) {
					if ( 'initial' !== $state ) {
						throw new QuickPayApiException( $e->getMessage() );
					}
					$status = $state;
				}

				$template_data = array_merge( $template_data, [
					'transaction'          => $transaction,
					'transaction_brand'    => $transaction->get_brand(),
					'transaction_status'   => $status,
					'transaction_order_id' => OrderPaymentsMeta::get_transaction_order_id( $subscription ),
				] );

			} catch ( QuickPayApiException $e ) {
				$e->write_to_logs();
				if ( 'initial' !== $state ) {
					$e->write_standard_warning();
				}
			}
		}

		$template_data = apply_filters( 'woocommerce_quickpay_payment_meta_box_template_data', $template_data, $subscription );

		do_action( 'woocommerce_quickpay_meta_box_subscription_before_content', $subscription, $template_data );

		woocommerce_quickpay_get_template( 'admin/meta-box-subscription.php', $template_data );

		do_action( 'woocommerce_quickpay_meta_box_subscription_after_content', $subscription, $template_data );
	}
}
