<?php

use QuickpayPSP\Admin\UserCapabilities;
use QuickpayPSP\Plugin;

if ( ! function_exists( 'woocommerce_quickpay_can_user_empty_logs' ) ) {
	function woocommerce_quickpay_can_user_empty_logs(): bool {
		/** @var UserCapabilities $caps */
		$caps = Plugin::services()->get_as( UserCapabilities::class, 'wordpress/user_caps' );

		return $caps->can_empty_logs();
	}
}

if ( ! function_exists( 'woocommerce_quickpay_can_user_flush_cache' ) ) {
	function woocommerce_quickpay_can_user_flush_cache(): bool {
		/** @var UserCapabilities $caps */
		$caps = Plugin::services()->get_as( UserCapabilities::class, 'wordpress/user_caps' );

		return $caps->can_flush_cache();
	}
}

if ( ! function_exists( 'woocommerce_quickpay_can_user_manage_payments' ) ) {
	function woocommerce_quickpay_can_user_manage_payments( $action = null ): bool {
		/** @var UserCapabilities $caps */
		$caps = Plugin::services()->get_as( UserCapabilities::class, 'wordpress/user_caps' );

		return $caps->can_manage_payments( is_string( $action ) ? $action : null );
	}
}
