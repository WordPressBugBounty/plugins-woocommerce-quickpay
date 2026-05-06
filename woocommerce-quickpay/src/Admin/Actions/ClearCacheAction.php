<?php

namespace QuickpayPSP\Admin\Actions;

use QuickpayPSP\Admin\UserCapabilities;

final class ClearCacheAction extends AbstractAdminAction {
	private UserCapabilities $caps;

	public function __construct( UserCapabilities $caps ) {
		$this->caps = $caps;
	}

	public function action(): string {
		return 'settings/clear-cache';
	}

	protected function execute(): void {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcqp_transaction_%' OR option_name LIKE '_transient_timeout_wcqp_transaction_%'" );

		wp_send_json_success( [ 'message' => 'The transaction cache has been cleared.' ] );
	}

	protected function is_action_allowed(): bool {
		/** @var bool $allowed */
		$allowed = (bool) apply_filters(
			"woocommerce_quickpay_api_is_{$this->action()}_allowed",
			$this->caps->can_flush_cache()
		);

		return $allowed;
	}
}
