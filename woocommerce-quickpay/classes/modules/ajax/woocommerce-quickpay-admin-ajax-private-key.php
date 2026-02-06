<?php

class WC_QuickPay_Admin_Ajax_Private_Key extends WC_QuickPay_Admin_Ajax_Action {

	public function action(): string {
		return 'settings/private-key';
	}

	public function execute(): void {
		try {
			if ( empty( $_POST['api_key'] ) ) {
				throw new \Exception( esc_html__( 'Please type in the API key before requesting a private key', 'woocommerce-quickpay' ) );
			}

			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] )) : '';

			if ( ! current_user_can( 'manage_woocommerce' ) || ! wp_verify_nonce( $nonce, 'manage-woocommerce-quickpay' ) ) {
				throw new \Exception( esc_html__( 'You are not authorized to perform this action.', 'woocommerce-quickpay' ) );
			}

			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );

			$api = new WC_QuickPay_API( $api_key );

			$response = $api->get( 'account/private-key' );

			wp_send_json_success( $response );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
