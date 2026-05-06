<?php

namespace QuickpayPSP\Admin;

final class UserCapabilities {
	public function can_empty_logs(): bool {
		$default = current_user_can( 'administrator' );

		/** @var bool $allowed */
		$allowed = (bool) apply_filters( 'woocommerce_quickpay_can_user_empty_logs', $default );

		return $allowed;
	}

	public function can_flush_cache(): bool {
		$default = current_user_can( 'administrator' );

		/** @var bool $allowed */
		$allowed = (bool) apply_filters( 'woocommerce_quickpay_can_user_flush_cache', $default );

		return $allowed;
	}

	public function can_manage_payments( ?string $action = null ): bool {
		$default = current_user_can( 'manage_woocommerce' );

		/** @var bool $allowed */
		$allowed = (bool) apply_filters( 'woocommerce_quickpay_can_user_manage_payment', $default );

		if ( $action !== null && $action !== '' ) {
			/** @var bool $allowed */
			$allowed = (bool) apply_filters( 'woocommerce_quickpay_can_user_manage_payment_' . $action, $default );
		}

		return $allowed;
	}
}
