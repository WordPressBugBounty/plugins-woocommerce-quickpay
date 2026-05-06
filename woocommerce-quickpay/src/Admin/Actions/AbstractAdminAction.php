<?php

namespace QuickpayPSP\Admin\Actions;

abstract class AbstractAdminAction implements AdminActionInterface {
	public function register(): void {
		add_action( 'woocommerce_api_quickpay/admin/' . $this->action(), [ $this, 'validate' ] );
	}

	abstract public function action(): string;

	abstract protected function execute(): void;

	public function validate(): void {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

		if ( ! $this->is_action_allowed() || ! wp_verify_nonce( $nonce, 'manage-woocommerce-quickpay' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action', 'woocommerce-quickpay' ) );
		}

		$this->execute();
	}

	protected function is_action_allowed(): bool {
		/** @var bool $allowed */
		$allowed = (bool) apply_filters(
			"woocommerce_quickpay_api_is_{$this->action()}_allowed",
			current_user_can( 'manage_woocommerce' )
		);

		return $allowed;
	}
}
