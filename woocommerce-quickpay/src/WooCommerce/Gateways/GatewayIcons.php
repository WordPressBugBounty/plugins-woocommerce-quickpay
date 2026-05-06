<?php

namespace QuickpayPSP\WooCommerce\Gateways;

final class GatewayIcons {
	/**
	 * Returns all available gateway icons with their display names.
	 */
	public static function all(): array {
		return [
			'apple-pay'             => 'Apple Pay',
			'dankort'               => 'Dankort',
			'google-pay'            => 'Google Pay',
			'visa'                  => 'Visa',
			'visa-verified'         => 'Verified by Visa',
			'mastercard'            => 'Mastercard',
			'mastercard-securecode' => 'Mastercard SecureCode',
			'mastercard-idcheck'    => 'Mastercard ID Check',
			'maestro'               => 'Maestro',
			'jcb'                   => 'JCB',
			'americanexpress'       => 'American Express',
			'diners'                => 'Diner\'s Club',
			'discovercard'          => 'Discover Card',
			'viabill'               => 'ViaBill',
			'paypal'                => 'Paypal',
			'danskebank'            => 'Danske Bank',
			'nordea'                => 'Nordea',
			'mobilepay'             => 'MobilePay',
			'forbrugsforeningen'    => 'Forbrugsforeningen',
			'ideal'                 => 'iDEAL',
			'unionpay'              => 'UnionPay',
			'sofort'                => 'Sofort',
			'cirrus'                => 'Cirrus',
			'klarna'                => 'Klarna',
			'bankaxess'             => 'BankAxess',
			'vipps'                 => 'Vipps',
			'swish'                 => 'Swish',
			'trustly'               => 'Trustly',
			'paysafecard'           => 'Paysafe Card',
		];
	}

	/**
	 * Returns the URL for a payment type logo, or null if not found.
	 */
	public static function logo_url( ?string $payment_type ): ?string {
		$logos = [
			'american-express'        => 'americanexpress.svg',
			'anyday'                  => 'anyday.svg',
			'apple-pay'               => 'apple-pay.svg',
			'dankort'                 => 'dankort.svg',
			'diners'                  => 'diners.svg',
			'edankort'                => 'edankort.png',
			'fbg1886'                 => 'forbrugsforeningen.svg',
			'google-pay'              => 'google-pay.svg',
			'jcb'                     => 'jcb.svg',
			'maestro'                 => 'maestro.svg',
			'mastercard'              => 'mastercard.svg',
			'mastercard-debet'        => 'mastercard.svg',
			'mobilepay'               => 'mobilepay.svg',
			'mobilepaysubscriptions'  => 'mobilepay.svg',
			'mobilepay-subscriptions' => 'mobilepay.svg',
			'visa'                    => 'visa.svg',
			'paypal'                  => 'paypal.svg',
			'sofort'                  => 'sofort.svg',
			'viabill'                 => 'viabill.svg',
			'klarna'                  => 'klarna.svg',
			'bank-axess'              => 'bankaxess.svg',
			'unionpay'                => 'unionpay.svg',
			'cirrus'                  => 'cirrus.svg',
			'ideal'                   => 'ideal.svg',
			'vipps'                   => 'vipps.png',
		];

		if ( $payment_type !== null && array_key_exists( trim( $payment_type ), $logos ) ) {
			$base = defined( 'QUICKPAY_PLUGIN_FILE' ) ? plugin_dir_url( QUICKPAY_PLUGIN_FILE ) : plugin_dir_url( __FILE__ );

			return $base . 'assets/images/cards/' . $logos[ $payment_type ];
		}

		return null;
	}
}
