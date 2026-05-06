<?php

namespace QuickpayPSP\Admin\Actions;

use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\Admin\UserCapabilities;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\Utilities\HelperUtils;
use QuickpayPSP\WooCommerce\Support\OrderUtils;

final class ManagePaymentAction extends AbstractAdminAction {
	private UserCapabilities $caps;
	private SubscriptionsFacade $subscriptions;
	private ApiClientFactory $api;

	public function __construct( UserCapabilities $caps, SubscriptionsFacade $subscriptions, ApiClientFactory $api ) {
		$this->caps          = $caps;
		$this->subscriptions = $subscriptions;
		$this->api           = $api;
	}

	public function action(): string {
		return 'manage-payment';
	}

	protected function execute(): void {
		if ( ! isset( $_REQUEST['quickpay_action'], $_REQUEST['post'] ) ) {
			return;
		}

		$param_action = sanitize_text_field( wp_unslash( $_REQUEST['quickpay_action'] ) );
		$param_post   = absint( wp_unslash( $_REQUEST['post'] ) );

		if ( ! $this->caps->can_manage_payments( $param_action ) ) {
			wp_send_json_error( sprintf( 'Your user is not capable of %s payments.', $param_action ) );
		}

		$order = OrderUtils::get_order( (int) $param_post );
		if ( ! $order ) {
			wp_send_json_error( sprintf( 'We could not find your order with ID %d', (int) $param_post ) );
		}

		try {
			$transaction_id = OrderUtils::get_transaction_id( $order );
			if ( ! $transaction_id ) {
				wp_send_json_error( 'We could not find a transaction ID.' );
			}

			$payment = $this->subscriptions->is_subscription( $order )
				? $this->api->subscription()
				: $this->api->payment();

			$payment->get( (string) $transaction_id );

			if ( ! $payment->is_action_allowed( $param_action ) ) {
				throw new QuickPayException(
					sprintf(
						'Action: "%1$s", is not allowed for order #%2$d, with type state "%3$s"',
						$param_action,
						OrderUtils::get_clean_order_number( $order ),
						$payment->get_current_type()
					)
				);
			}

			if ( ! method_exists( $payment, $param_action ) ) {
				throw new QuickPayException( sprintf( 'Unsupported action: %s.', $param_action ) );
			}

			$method = new \ReflectionMethod( $payment, $param_action );
			$args   = [];

			$arg_count = $method->getNumberOfParameters();
			if ( $arg_count >= 1 ) {
				$args[] = $transaction_id;
			}
			if ( $arg_count >= 2 ) {
				$args[] = $order;
			}
			if ( $arg_count >= 3 ) {
				$amount = isset( $_REQUEST['quickpay_amount'] )
					? MoneyUtils::price_custom_to_multiplied(
						sanitize_text_field( wp_unslash( $_REQUEST['quickpay_amount'] ) ),
						$payment->get_currency()
					)
					: $payment->get_remaining_balance();

				$args[] = MoneyUtils::price_multiplied_to_float( $amount, $payment->get_currency() );
			}

			$method->invokeArgs( $payment, $args );
		} catch ( QuickPayException $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
