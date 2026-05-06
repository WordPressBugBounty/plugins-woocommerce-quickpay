<?php

namespace QuickpayPSP\Admin\Actions;

use QuickpayPSP\Admin\UserCapabilities;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;

final class EmptyLogsAction extends AbstractAdminAction {
	private UserCapabilities $caps;
	private WooCommerceLogger $logger;

	public function __construct( UserCapabilities $caps, WooCommerceLogger $logger ) {
		$this->caps   = $caps;
		$this->logger = $logger;
	}

	public function action(): string {
		return 'settings/empty-logs';
	}

	protected function execute(): void {
		if ($this->logger->clear()) {
			wp_send_json_success( [ 'message' => 'Logs successfully emptied.' ] );
		}

		wp_send_json_error( [ 'message' => 'Failed to empty logs.' ] );
	}

	protected function is_action_allowed(): bool {
		/** @var bool $allowed */
		$allowed = (bool) apply_filters(
			"woocommerce_quickpay_api_is_{$this->action()}_allowed",
			$this->caps->can_empty_logs()
		);

		return $allowed;
	}
}
