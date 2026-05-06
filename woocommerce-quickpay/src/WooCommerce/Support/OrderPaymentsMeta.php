<?php

namespace QuickpayPSP\WooCommerce\Support;

use WC_Order;
use WC_Order_Item_Fee;

final class OrderPaymentsMeta {
	public static function get_payment_id( WC_Order $order ): ?string {
		return $order->get_meta( 'QUICKPAY_PAYMENT_ID' ) ?: null;
	}

	public static function set_payment_id( WC_Order $order, string $payment_id ): void {
		$order->update_meta_data( 'QUICKPAY_PAYMENT_ID', $payment_id );
		$order->save_meta_data();
	}

	public static function delete_payment_id( WC_Order $order ): void {
		$order->delete_meta_data( 'QUICKPAY_PAYMENT_ID' );
		$order->save_meta_data();
	}

	public static function get_payment_link( WC_Order $order ): ?string {
		return $order->get_meta( 'QUICKPAY_PAYMENT_LINK' ) ?: null;
	}

	public static function set_payment_link( WC_Order $order, string $payment_link ): void {
		$order->update_meta_data( 'QUICKPAY_PAYMENT_LINK', $payment_link );
		$order->save_meta_data();
	}

	public static function delete_payment_link( WC_Order $order ): void {
		$order->delete_meta_data( 'QUICKPAY_PAYMENT_LINK' );
		$order->save_meta_data();
	}

	public static function get_transaction_order_id( WC_Order $order ): ?string {
		return $order->get_meta( 'TRANSACTION_ORDER_ID' ) ?: null;
	}

	public static function set_transaction_order_id( WC_Order $order, string $transaction_order_id ): void {
		$order->update_meta_data( 'TRANSACTION_ORDER_ID', $transaction_order_id );
		$order->save_meta_data();
	}

	public static function get_failed_payment_count( WC_Order $order ): int {
		return (int) $order->get_meta( '_quickpay_failed_payment_count' );
	}

	public static function increase_failed_payment_count( WC_Order $order ): int {
		$count = self::get_failed_payment_count( $order );
		$order->update_meta_data( '_quickpay_failed_payment_count', ++ $count );
		$order->save_meta_data();

		return $count;
	}

	public static function reset_failed_payment_count( WC_Order $order ): void {
		$order->delete_meta_data( '_quickpay_failed_payment_count' );
		$order->save_meta_data();
	}

	public static function get_payment_method_change_count( WC_Order $order ): int {
		return (int) $order->get_meta( '_quickpay_payment_method_change_count' );
	}

	public static function increase_payment_method_change_count( WC_Order $order ): int {
		$count = self::get_payment_method_change_count( $order );
		$order->update_meta_data( '_quickpay_payment_method_change_count', ++ $count );
		$order->save_meta_data();

		return $count;
	}

	public static function add_order_item_transaction_fee( WC_Order $order, int $fee_in_cents ): bool {
		if ( $fee_in_cents <= 0 ) {
			return false;
		}

		$fee = new WC_Order_Item_Fee();

		$fee->set_name( __( 'Payment Fee', 'woocommerce-quickpay' ) );
		$fee->set_total( $fee_in_cents / 100 );
		$fee->set_tax_status( 'none' );
		$fee->set_total_tax( 0 );
		$fee->set_order_id( $order->get_id() );

		$fee->save();

		$order->add_item( apply_filters( 'woocommerce_quickpay_transaction_fee_data', $fee, $order ) );

		$order->calculate_taxes();
		$order->calculate_totals( false );
		$order->save();

		return true;
	}
}
