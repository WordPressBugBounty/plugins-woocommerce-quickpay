<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * @var WC_QuickPay_API_Transaction $transaction
 * @var int $transaction_id
 * @var null|string $transaction_status
 * @var null|string $payment_link
 * @var null|string $payment_id
 * @var mixed $transaction_order_id
 * @var string $transaction_brand
 */

?>
<?php if ( isset( $transaction ) ) : ?>
    <p class="woocommerce-quickpay-<?php echo esc_attr( $transaction_status ) ?>">
        <strong><?php esc_html_e( 'Current payment state', 'woocommerce-quickpay' ) ?>: <?php echo esc_html( $transaction_status ) ?></strong>
    </p>

    <?php if ( $transaction->is_action_allowed( 'standard_actions' ) ) : ?>
        <h4><strong><?php esc_html_e( 'Actions', 'woocommerce-quickpay' ) ?></strong></h4>
        <ul class="order_action">
            <?php if ( $transaction->is_action_allowed( 'capture' ) ) : ?>
                <li class="qp-full-width">
                    <a class="button button-primary" data-action="capture"
                       data-confirm="<?php echo esc_attr( __( 'You are about to capture this payment', 'woocommerce-quickpay' ) ) ?>">
                        <?php /* translators: %s: remaining balance */ ?>
                        <?php echo wp_kses_post( sprintf( __( 'Capture Full Amount (%s)', 'woocommerce-quickpay' ), wc_price( $transaction->get_remaining_balance_as_float(), [ 'currency' => $transaction->get_currency() ] ) ) ) ?>
                    </a>
                </li>
            <?php endif ?>

            <li class="qp-balance">
                <span class="qp-balance__label"><?php esc_html_e( 'Remaining balance', 'woocommerce-quickpay' ) ?>:</span>
                <span class="qp-balance__amount">
                <span class='qp-balance__currency'>
                <?php echo esc_html( $transaction->get_currency() ) ?>
                </span>
                <?php echo esc_html( $transaction->get_formatted_remaining_balance() ) ?></span>
            </li>

            <?php if ( $transaction->is_action_allowed( 'capture' ) ) : ?>
                <li class="qp-balance last">
                <span class="qp-balance__label">
                    <?php esc_html_e( 'Capture amount', 'woocommerce-quickpay' ) ?>:
                </span>
                    <span class="qp-balance__amount">
                    <span class='qp-balance__currency'><?php echo esc_html( $transaction->get_currency() ) ?></span>
                    <input id='qp-balance__amount-field' type='text' value='<?php echo esc_attr( $transaction->get_formatted_remaining_balance() ) ?> '/>
                </span>
                </li>

                <li class="qp-full-width">
                    <a class="button" data-action="captureAmount" data-confirm="<?php esc_attr__( 'You are about to capture this payment', 'woocommerce-quickpay' ) ?>">
                        <?php esc_html_e( 'Capture Specified Amount', 'woocommerce-quickpay' ) ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if ( $transaction->is_action_allowed( 'cancel' ) ) : ?>
                <li class="qp-full-width">
                    <a class="button" data-action="cancel" data-confirm="<?php esc_attr__( 'You are about to cancel this payment', 'woocommerce-quickpay' ) ?>">
                        <?php esc_html_e( 'Cancel', 'woocommerce-quickpay' ) ?>
                    </a>
                </li>
            <?php endif ?>
        </ul>
    <?php endif ?>
    <p>
        <small>
            <strong><?php echo esc_html__( 'Transaction ID', 'woocommerce-quickpay' ) ?>:</strong> <?php echo esc_html( $transaction_id ) ?>
            <?php if ( $brand_image_url = WC_Quickpay_Helper::get_payment_type_logo( $transaction_brand ) ) : ?>
                <span class="qp-meta-card">
                <img src="<?php echo esc_attr( $brand_image_url ) ?>" alt="<?php echo esc_attr( $transaction_brand ) ?>"/>
            </span>
            <?php endif ?>
        </small>
    </p>
<?php endif ?>

<?php if ( ! empty( $transaction_order_id ) ) : ?>
    <p>
        <small>
            <strong><?php esc_html_e( 'Transaction Order ID', 'woocommerce-quickpay' ) ?>:</strong> <?php echo esc_html( $transaction_order_id ) ?>
        </small>
    </p>
<?php endif ?>

<?php if ( ! empty( $payment_id ) ) : ?>
    <p>
        <small>
            <strong><?php esc_html_e( 'Payment ID', 'woocommerce-quickpay' ) ?>:</strong> <?php echo esc_html( $payment_id ) ?>
        </small>
    </p>
<?php endif ?>

<?php if ( ! empty( $payment_link ) ) : ?>
    <p>
        <small>
            <strong><?php esc_html_e( 'Payment Link', 'woocommerce-quickpay' ) ?>:</strong> <br/>
            <input type="text" style="width: 100%;" value="<?php echo esc_attr( $payment_link ) ?>" readonly/>
        </small>
    </p>
<?php endif ?>
