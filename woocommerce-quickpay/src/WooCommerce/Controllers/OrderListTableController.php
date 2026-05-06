<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use Automattic\WooCommerce\Utilities\OrderUtil;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\WooCommerce\Gateways\GatewayIcons;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;

class OrderListTableController {

	private GatewaySettingsProvider $settings;
	private SubscriptionsFacade $subscriptions_facade;

	private ApiClientFactory $api_factory;

	public function __construct( GatewaySettingsProvider $settings, SubscriptionsFacade $subscriptions_facade, ApiClientFactory $api_factory ) {
		$this->settings             = $settings;
		$this->subscriptions_facade = $subscriptions_facade;
		$this->api_factory          = $api_factory;
	}

	/**
	 * Adds a separate column for payment info
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_payment_info_column( array $columns ): array {
		$column_name   = 'quickpay_transaction_info';
		$column_header = esc_html__( 'Payment', 'woocommerce-quickpay' );

		// Insert after shipping address if possible, otherwise at the end
		$new_columns = [];
		$inserted    = false;

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'shipping_address' === $key ) {
				$new_columns[ $column_name ] = $column_header;
				$inserted                    = true;
			}
		}

		if ( ! $inserted ) {
			$new_columns[ $column_name ] = $column_header;
		}

		return $new_columns;
	}

	/**
	 * Applies transaction ID and state to the order data overview
	 *
	 * @param string $column
	 * @param int|\WC_Order $post_id_or_order_object
	 *
	 * @return void
	 */
	public function render_custom_column_data( $column, $post_id_or_order_object ): void {
		$order = OrderUtils::get_order( $post_id_or_order_object );
		if ( ! $order ) {
			return;
		}

		$order_type = OrderUtil::get_order_type( $order );

		// Show transaction ID on the overview
		if ( ( $order_type === 'shop_order' && $column === 'quickpay_transaction_info' ) || ( $order_type === 'shop_subscription' && $column === 'order_title' ) ) {

			$transaction_id = OrderUtils::get_transaction_id( $order );

			try {
				if ( $transaction_id && OrderPaymentsUtils::is_order_using_quickpay( $order ) ) {
						$transaction = $this->subscriptions_facade->is_subscription( $order->get_id() ) ? $this->api_factory->subscription() : $this->api_factory->payment();
					$transaction->maybe_load_transaction_from_cache( $transaction_id );

					$brand = $transaction->get_brand();

					woocommerce_quickpay_get_view( 'html-order-table-transaction-data.php', [
						'transaction_id'             => $transaction_id,
						'transaction_order_id'       => OrderPaymentsMeta::get_transaction_order_id( $order ),
						'transaction_brand'          => $brand,
      'transaction_brand_logo_url' => GatewayIcons::logo_url( $brand ?: $transaction->get_acquirer() ),
						'transaction_status'         => OrderUtils::is_failed_renewal( $order ) ? esc_html__( 'Failed renewal', 'woocommerce-quickpay' ) : $transaction->get_current_type(),
						'transaction_is_test'        => $transaction->is_test(),
 					'is_cached'                  => $transaction->is_loaded_from_cached(),
					] );
				}
			} catch ( QuickPayException|QuickPayApiException $e ) {
				if ( function_exists( 'WC_QP' ) ) {
					WC_QP()->log->add( sprintf( 'Order list: #%s - %s', $order->get_id(), $e->getMessage() ) );
				}
			}
		}
	}
}
