<?php

use QuickpayPSP\Compatibility\LegacyFacade;
use QuickpayPSP\Compatibility\LegacyHelper;
use QuickpayPSP\Compatibility\LegacyLog;
use QuickpayPSP\Compatibility\LegacyOrder;
use QuickpayPSP\Compatibility\LegacyOrderPaymentsUtils;
use QuickpayPSP\Compatibility\LegacyRequestsUtils;
use QuickpayPSP\Compatibility\LegacySubscription;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayCaptureException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\QuickPay\Api\PaymentClient;
use QuickpayPSP\QuickPay\Api\QuickPayClient;
use QuickpayPSP\QuickPay\Api\SubscriptionClient;
use QuickpayPSP\QuickPay\Api\TransactionClient;
use QuickpayPSP\Utilities\AddressUtils;
use QuickpayPSP\Utilities\Countries;
use QuickpayPSP\WooCommerce\Emails\PaymentLinkEmail;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\WooCommerce\Support\TransactionDataBuilder;

if ( ! defined( 'QUICKPAYPSP_COMPAT_ALIAS_LOADED' ) ) {
	define( 'QUICKPAYPSP_COMPAT_ALIAS_LOADED', true );

	$aliases = [
		'WC_QuickPay_API'                          => QuickPayClient::class,
		'WC_QuickPay_API_Transaction'              => TransactionClient::class,
		'WC_QuickPay_API_Payment'                  => PaymentClient::class,
		'WC_QuickPay_API_Subscription'             => SubscriptionClient::class,
		'QuickPay_Exception'                       => QuickPayException::class,
		'QuickPay_API_Exception'                   => QuickPayApiException::class,
		'QuickPay_Capture_Exception'               => QuickPayCaptureException::class,
		'WC_QuickPay_Helper'                       => LegacyHelper::class,
		'WC_QuickPay_Address'                      => AddressUtils::class,
		'WC_QuickPay_Countries'                    => Countries::class,
		'WC_QuickPay_Order_Utils'                  => OrderUtils::class,
		'WC_QuickPay_Order_Payments_Utils'         => LegacyOrderPaymentsUtils::class,
		'WC_QuickPay_Order_Transaction_Data_Utils' => TransactionDataBuilder::class,
		'WC_QuickPay_Requests_Utils'               => LegacyRequestsUtils::class,
		'WC_QuickPay_Payment_Link_Email'           => PaymentLinkEmail::class,
		'WC_QuickPay_Log'                          => LegacyLog::class,
		'WC_QuickPay_Subscription'                 => LegacySubscription::class,
		'WC_QP_Legacy_Facade'                      => LegacyFacade::class,
		'WC_QuickPay_Order'                        => LegacyOrder::class,
	];

	spl_autoload_register( static function ( string $class ) use ( $aliases ): void {
		if ( ! isset( $aliases[ $class ] ) ) {
			return;
		}

		if ( class_exists( $class, false ) ) {
			return;
		}

		$target = $aliases[ $class ];
		if ( ! class_exists( $target ) ) {
			return;
		}

		_deprecated_function( $class, '8.0.0', $target );
		class_alias( $target, $class );
	} );
}
