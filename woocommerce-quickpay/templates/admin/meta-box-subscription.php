<?php

use QuickpayPSP\WooCommerce\Support\GatewayUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @var string $transaction_status
 * @var WC_QuickPay_API_Subscription $transaction
 * @var string $transaction_brand
 */
?>
<?php if ( ! empty( $transaction_status ) ) : ?>
    <p class="woocommerce-quickpay-<?php echo esc_attr( $transaction_status ) ?>">
        <strong>
			<?php esc_html_e( 'Current payment state', 'woocommerce-quickpay' ) ?>: <?php echo esc_html($transaction_status) ?>
        </strong>
    </p>
<?php endif ?>

<?php if ( isset( $transaction_id, $transaction ) ) : ?>
    <p>
        <small>
            <strong><?php esc_html_e( 'Transaction ID', 'woocommerce-quickpay' ) ?>:</strong> <?php echo esc_html($transaction_id) ?>
            <span class="qp-meta-card">
                <img src="<?php echo esc_attr( GatewayUtils::get_payment_type_logo( $transaction_brand ) ) ?>"
                     alt="<?php echo esc_attr( $transaction_brand ) ?>"/>
            </span>
        </small>
    </p>
<?php endif ?>

<?php if ( isset( $transaction_order_id ) ) : ?>
    <p>
        <small>
            <strong><?php esc_html_e( 'Transaction Order ID', 'woocommerce-quickpay' ) ?>:</strong> <?php echo esc_html($transaction_order_id) ?>
        </small>
    </p>
<?php endif ?>
