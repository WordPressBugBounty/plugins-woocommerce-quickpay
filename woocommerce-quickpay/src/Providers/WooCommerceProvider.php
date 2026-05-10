<?php
namespace QuickpayPSP\Providers;

use QuickpayPSP\WooCommerce\Controllers\CallbackController;
use QuickpayPSP\WooCommerce\Callbacks\WcApiCallbackEndpoint;
use QuickpayPSP\WooCommerce\Registrars\CallbacksRegistrar;
use QuickpayPSP\WooCommerce\Registrars\OrderMetaRegistrar;
use QuickpayPSP\WooCommerce\Controllers\OrderController;
use QuickpayPSP\WooCommerce\Controllers\OrderActionsController;
use QuickpayPSP\WooCommerce\Controllers\OrderMetaController;
use QuickpayPSP\WooCommerce\Controllers\OrderListTableController;
use QuickpayPSP\WooCommerce\Controllers\SubscriptionActionsController;
use QuickpayPSP\WooCommerce\Controllers\SubscriptionEarlyRenewalsController;
use QuickpayPSP\WooCommerce\Controllers\SubscriptionChangePaymentMethodController;
use QuickpayPSP\WooCommerce\Controllers\EmailsController;
use QuickpayPSP\Application\Services\PaymentLinkService;
use QuickpayPSP\WooCommerce\Controllers\PaymentController;
use QuickpayPSP\WooCommerce\Controllers\RefundController;
use QuickpayPSP\WooCommerce\Controllers\BlocksController;
use QuickpayPSP\WooCommerce\Registrars\EmailsRegistrar;
use QuickpayPSP\WooCommerce\Registrars\BlocksRegistrar;
use QuickpayPSP\WooCommerce\Registrars\OrdersRegistrar;
use QuickpayPSP\WooCommerce\Registrars\OrderListTableRegistrar;
use QuickpayPSP\WooCommerce\Gateways\GatewayHandlerRegistrar;
use QuickpayPSP\WooCommerce\Gateways\Handlers\MobilePaySubscriptionsGatewayHandler;
use QuickpayPSP\WooCommerce\Gateways\Handlers\SofortGatewayHandler;
use QuickpayPSP\WooCommerce\Registrars\SubscriptionsRegistrar;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\Providers\Contracts\ServiceProviderInterface;
use QuickpayPSP\Support\Services;

/**
 * Registers all WooCommerce integration services within the Integration Layer.
 *
 * Covers:
 * - Logger and subscriptions facade
 * - Payment, refund, email, and blocks controllers
 * - Order management (controller, registrar, meta, list table, admin actions)
 * - Subscription handling (early renewals, payment method changes, registrar)
 * - Callback processing (controller, endpoint, registrar, gateway-specific handlers)
 *
 * This provider acts as the adapter between WooCommerce/WordPress and the
 * Application Layer — no direct business logic should live here.
 */
class WooCommerceProvider implements ServiceProviderInterface {
	public function register( Services $services ): void {
		$this->register_core( $services );
		$this->register_orders( $services );
		$this->register_subscriptions( $services );
		$this->register_callbacks( $services );
	}

	/**
	 * Core WooCommerce services.
	 *
	 * Registers the logger, subscriptions facade, and the controllers/registrars
	 * for payment processing, refunds, emails, and Gutenberg blocks.
	 */
	private function register_core( Services $services ): void {
		$services->set( 'logger', static fn() => new WooCommerceLogger( 'woocommerce-quickpay' ) );

		$services->set( 'woocommerce/subscriptions', SubscriptionsFacade::class );

		$services->set( PaymentLinkService::class, fn( $s ) => new PaymentLinkService(
			$s->get( 'quickpay/api/factory' ),
			$s->get( 'gateway/settings_provider' ),
			$s->get( 'woocommerce/subscriptions' ),
		) );

		$services->set( 'woocommerce/payment/controller', fn( $s ) => new PaymentController(
			$s->get( PaymentLinkService::class ),
			$s->get( 'woocommerce/subscriptions' ),
		) );

		$services->set( 'woocommerce/refund/controller', fn( $s ) => new RefundController(
			$s->get( 'quickpay/api/factory' ),
		) );

		$services->set( 'woocommerce/emails/controller', fn( $s ) => new EmailsController(
			$s->get( 'gateway/settings_provider' )
		) );
		$services->set( 'woocommerce/emails/registrar', function ( $s ) {
			return new EmailsRegistrar( $s->get( 'woocommerce/emails/controller' ) );
		} );

		$services->set( 'woocommerce/blocks/controller', BlocksController::class );
		$services->set( 'woocommerce/blocks/registrar', function ( $s ) {
			return new BlocksRegistrar( $s->get( 'woocommerce/blocks/controller' ) );
		} );
	}

	/**
	 * Order management services.
	 *
	 * Registers the order controller (core order operations), its registrar,
	 * the admin order-actions controller, order meta (custom fields in the
	 * order edit screen), and the order list table column enhancements.
	 */
	private function register_orders( Services $services ): void {
		$services->set( 'woocommerce/orders/controller', function ( $s ) {
			return new OrderController(
				$s->get( 'gateway/settings_provider' ),
				$s->get( 'woocommerce/subscriptions' ),
				$s->get( 'quickpay/api/factory' ),
				$s->get( 'logger' ),
				$s->get( PaymentLinkService::class ),
				$s->get( 'gateway/registry' )->ids()
			);
		} );

		$services->set( 'woocommerce/orders/registrar', function ( $s ) {
			return new OrdersRegistrar(
				$s->get( 'woocommerce/orders/controller' ),
				$s->get( 'gateway/registry' )->ids(),
			);
		} );

		// Admin action buttons rendered inside the order edit screen
		$services->set( 'woocommerce/admin/order-actions', function ( $s ) {
			return new OrderActionsController( $s->get( 'woocommerce/orders/controller' ), $s->get( 'woocommerce/subscriptions' ) );
		} );

		// Custom meta box / fields on the order edit screen
		$services->set( 'woocommerce/admin/order-meta/controller', function ( $s ) {
			return new OrderMetaController( $s->get( 'quickpay/api/factory' ) );
		} );
		$services->set( 'woocommerce/admin/order-meta/registrar', function ( $s ) {
			return new OrderMetaRegistrar( $s->get( 'woocommerce/admin/order-meta/controller' ) );
		} );

		// Extra columns and bulk actions in the orders list table
		$services->set( 'woocommerce/admin/order-list-table/controller', function ( $s ) {
			return new OrderListTableController(
				$s->get( 'gateway/settings_provider' ),
				$s->get( 'woocommerce/subscriptions' ),
				$s->get( 'quickpay/api/factory' )
			);
		} );
		$services->set( 'woocommerce/admin/order-list-table/registrar', function ( $s ) {
			return new OrderListTableRegistrar( $s->get( 'woocommerce/admin/order-list-table/controller' ) );
		} );
	}

	/**
	 * Subscription services.
	 *
	 * Registers the admin subscription-actions controller, the early renewals
	 * controller, the payment method change controller, and the registrar that
	 * hooks all subscription-related functionality into WooCommerce Subscriptions.
	 */
	private function register_subscriptions( Services $services ): void {
		$services->set( 'woocommerce/admin/subscription-actions', function ( $s ) {
			return new SubscriptionActionsController( $s->get( 'woocommerce/admin/order-actions' ) );
		} );

		$services->set( SubscriptionEarlyRenewalsController::class, function ( $s ) {
			return new SubscriptionEarlyRenewalsController( $s->get( 'woocommerce/subscriptions' ) );
		} );

		$services->set( SubscriptionChangePaymentMethodController::class, function ( $s ) {
			return new SubscriptionChangePaymentMethodController(
				$s->get( 'gateway/settings_provider' ),
				$s->get( 'woocommerce/subscriptions' )
			);
		} );

		$services->set( 'woocommerce/subscriptions/registrar', function ( $s ) {
			return new SubscriptionsRegistrar(
				$s->get( SubscriptionEarlyRenewalsController::class ),
				$s->get( SubscriptionChangePaymentMethodController::class ),
				$s->get( 'woocommerce/orders/controller' ),
				$s->get( 'gateway/registry' ),
			);
		} );
	}

	/**
	 * Callback processing services.
	 *
	 * Registers the callback controller (processes incoming payment notifications),
	 * the WC API endpoint that receives them, the registrar that hooks the endpoint
	 * into WooCommerce, and the gateway-specific handler registrar for gateways
	 * that require custom callback routing (Sofort, MobilePay Subscriptions).
	 */
	private function register_callbacks( Services $services ): void {
		$services->set( 'woocommerce/callbacks/controller', function ( $s ) {
			return new CallbackController(
				$s->get( 'quickpay/api/factory' ),
				$s->get( 'gateway/settings_provider' ),
				$s->get( 'logger' ),
				$s->get( 'woocommerce/subscriptions' )
			);
		} );

		$services->set( 'woocommerce/callbacks/endpoint', function ( $s ) {
			return new WcApiCallbackEndpoint(
				$s->get( 'woocommerce/callbacks/controller' ),
				$s->get( 'logger' ),
				$s->get( 'gateway/settings_provider' )
			);
		} );

		$services->set( 'woocommerce/callbacks/registrar', function ( $s ) {
			return new CallbacksRegistrar( $s->get( 'woocommerce/callbacks/endpoint' ) );
		} );

		// Gateway-specific callback handlers (e.g. Sofort redirect, MobilePay Subscriptions)
		$services->set( 'woocommerce/gateway-callbacks/registrar', function ( $s ) {
			return new GatewayHandlerRegistrar(
				new SofortGatewayHandler( $s->get( 'woocommerce/callbacks/controller' ) ),
				new MobilePaySubscriptionsGatewayHandler( $s->get( 'gateway/settings_provider' ), $s->get( 'logger' ) ),
			);
		} );
	}
}
