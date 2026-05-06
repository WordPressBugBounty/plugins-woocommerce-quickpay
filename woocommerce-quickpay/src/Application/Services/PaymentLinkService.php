<?php

namespace QuickpayPSP\Application\Services;

use Exception;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Utilities\UrlUtils;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;
use WC_Order;
use WC_Subscriptions_Change_Payment_Gateway;

/**
 * Application Layer service responsible for creating and refreshing payment links.
 *
 * Handles:
 * - Renewal detection and card-update flag toggling
 * - Subscription parent-order resolution
 * - Transaction creation and link patching
 * - URL validation
 *
 * Both PaymentController (checkout flow) and OrderController (admin flow) delegate here.
 * The public BC function woocommerce_quickpay_create_payment_link() also delegates here.
 */
class PaymentLinkService {

	private ApiClientFactory $api_factory;
	private GatewaySettingsProvider $settings;
	private SubscriptionsFacade $subscriptions_facade;

	public function __construct(
		ApiClientFactory $api_factory,
		GatewaySettingsProvider $settings,
		SubscriptionsFacade $subscriptions_facade
	) {
		$this->api_factory          = $api_factory;
		$this->settings             = $settings;
		$this->subscriptions_facade = $subscriptions_facade;
	}

	/**
	 * Creates or refreshes a payment link for a checkout order.
	 *
	 * Handles renewal detection and the card-update flag toggle, then delegates
	 * to the core link creation logic shared with the BC global function.
	 *
	 * @param WC_Order $order
	 *
	 * @return string  The payment link URL.
	 * @throws QuickPayException
	 */
	public function create_link_for_checkout( WC_Order $order ): string {
		$is_renewal          = $this->subscriptions_facade->is_renewal( $order );
		$card_update_enabled = wc_string_to_bool( $this->settings->get( 'subscription_update_card_on_manual_renewal_payment' ) );
		$flag_was_modified   = false;

		if ( $is_renewal && $card_update_enabled ) {
			WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = true;
			$flag_was_modified = true;
		}

		try {
			$url = $this->create_payment_link( $order );
		} finally {
			if ( $flag_was_modified ) {
				WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = false;
			}
		}

		if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new QuickPayException( __( 'Could not create a valid payment link. Please try again.', 'woocommerce-quickpay' ) );
		}

		return $url;
	}

	/**
	 * Core payment link creation logic — shared between checkout flow and BC global function.
	 *
	 * Returns an existing payment link if available, or creates a new transaction and patches the link.
	 *
	 * @param WC_Order $order
	 * @param bool $force_update  Whether to always refresh the link even if one already exists.
	 *
	 * @return string|null
	 * @throws Exception
	 */
	public function create_payment_link( WC_Order $order, bool $force_update = true ): ?string {
		if ( ! $order->needs_payment() && ! WooCommerceContext::is_request_to_change_payment() ) {
			throw new Exception( esc_html__( 'Order does not need payment', 'woocommerce-quickpay' ) );
		}

		$transaction  = woocommerce_quickpay_get_transaction_instance_by_order( $order );
		$payment_link = OrderPaymentsMeta::get_payment_link( $order );
		$payment_id   = OrderPaymentsMeta::get_payment_id( $order );

		if ( empty( $payment_id ) && empty( $payment_link ) ) {
			$payment_id = woocommerce_quickpay_create_order_transaction( $order );
		} else {
			$transaction->patch_payment( $payment_id, $order );
		}

		if ( empty( $payment_link ) || $force_update ) {
			$link = $transaction->patch_link( $payment_id, $order );

			if ( UrlUtils::is_url( $link->url ) ) {
				OrderPaymentsMeta::set_payment_link( $order, $link->url );
				$payment_link = $link->url;
			}
		}

		return $payment_link;
	}

	/**
	 * Creates or refreshes a payment link for an order or subscription from the admin.
	 *
	 * Handles subscription parent-order resolution, renewal detection, card-update flag,
	 * transaction creation (with unique order number filter), link patching and URL validation.
	 *
	 * @param WC_Order $order  The order or subscription to create a link for.
	 * @param callable $make_unique_order_number  Callback for the woocommerce_quickpay_order_number_for_api filter.
	 *
	 * @return string  The payment link URL.
	 * @throws QuickPayException
	 * @throws Exception
	 */
	public function create_link_for_admin( WC_Order $order, callable $make_unique_order_number ): string {
		$is_subscription                         = $this->subscriptions_facade->is_subscription( $order );
		$resource_order                          = $order;
		$is_renewal_order                        = false;
		$is_card_update_enabled                  = wc_string_to_bool( $this->settings->get( 'subscription_update_card_on_manual_renewal_payment' ) );
		$is_change_payment_request_flag_modified = false;

		$order->set_payment_method( 'quickpay' );
		$order->set_payment_method_title( 'QuickPay' );

		$transaction_id = OrderUtils::get_transaction_id( $order );

		if ( $is_subscription ) {
			if ( ! $order_parent_id = $resource_order->get_parent_id() ) {
				throw new QuickPayException( esc_html__( 'A parent order must be mapped to the subscription.', 'woocommerce-quickpay' ) );
			}
			$resource_order = wc_get_order( $order_parent_id );

			$resource_order->set_payment_method( 'quickpay' );
			$resource_order->set_payment_method_title( 'QuickPay' );
			$resource_order->save();

			if ( $transaction_id ) {
				$check_subscription_transaction = woocommerce_quickpay_get_transaction_instance_by_order( $resource_order );
				$check_subscription_transaction->get( $transaction_id );

				if ( $check_subscription_transaction->get_state() !== 'initial' ) {
					$transaction_id = null;
				}
			}
		} else {
			if ( $is_card_update_enabled && ( $is_renewal_order = $this->subscriptions_facade->is_renewal( $order ) ) ) {
				WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = true;
				$is_change_payment_request_flag_modified                               = true;
			}
		}

		$resource = woocommerce_quickpay_get_transaction_instance_by_order( $resource_order );

		if ( ! $transaction_id ) {
			add_filter( 'woocommerce_quickpay_order_number_for_api', $make_unique_order_number, 5 );
			$transaction = $resource->create( $resource_order );
			remove_filter( 'woocommerce_quickpay_order_number_for_api', $make_unique_order_number, 5 );

			$transaction_id = $transaction->id;
			$order->set_transaction_id( $transaction_id );
		}

		$link = $resource->patch_link( $transaction_id, $resource_order );

		if ( $is_renewal_order && $is_card_update_enabled && $is_change_payment_request_flag_modified ) {
			WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = false;
		}

		if ( ! filter_var( $link->url, FILTER_VALIDATE_URL ) ) {
			throw new Exception( sprintf( esc_html__( 'Invalid payment link received from API for order #%s', 'woocommerce-quickpay' ), $order->get_id() ) );
		}

		OrderPaymentsMeta::set_payment_link( $order, $link->url );

		return $link->url;
	}
}
