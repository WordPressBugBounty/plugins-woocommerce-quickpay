<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="woocommerce-quickpay-order-transaction-data">
	<table border="0" cellpadding="0" cellspacing="0" class="meta">
		<tr>
			<td><?php esc_html_e('ID', 'woocommerce-quickpay' ) ?>:</td>
			<td>#<?php echo esc_html($transaction_id) ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e('Order ID', 'woocommerce-quickpay' ) ?>:</td>
			<td><?php echo esc_html($transaction_order_id) ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e('Method', 'woocommerce-quickpay' ) ?>:</td>
			<td>
				<span class="transaction-brand"><img src="<?php echo esc_attr($transaction_brand_logo_url) ?>" alt="<?php echo esc_attr($transaction_brand) ?>" title="<?php echo esc_attr($transaction_brand) ?>" /></span>
			</td>
		</tr>
	</table>
	<div class="tags">
		<?php if ( $transaction_is_test ) : ?>
			<?php $tip_transaction_test = esc_attr( __( 'This order has been paid with test card data!', 'woocommerce-quickpay' ) ) ?>
			<span class="tag is-test tips" data-tip="<?php echo esc_attr($tip_transaction_test) ?>"><?php esc_html_e( 'Test', 'woocommerce-quickpay' ) ?></span>
		<?php endif; ?>
		<span class="tag is-<?php echo esc_attr($transaction_status) ?>">
			<?php echo esc_html($transaction_status) ?>
		</span>
		<?php if ( $is_cached ) : ?>
			<?php $tip_transaction_cached = esc_attr( __( 'NB: The transaction data is served from cached results. Click to view the order and update the cached data.', 'woocommerce-quickpay' ) )?>
			<span class="tag tips" data-tip="<?php echo esc_attr($tip_transaction_cached) ?>"><?php esc_html_e( 'Cached', 'woocommerce-quickpay' ) ?></span>
		<?php endif; ?>

	</div>
</div>
