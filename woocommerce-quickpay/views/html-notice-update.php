<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="woocommerce-upgrade-notice" class="updated woocommerce-message wc-connect">
    <h3><strong><?php _e( 'WooCommerce Quickpay - Data Update', 'woo-quickpay' ); ?></strong></h3>
    <p><?php _e( 'To ensure you get the best experience at all times, we need to update your store\'s database to the latest version.', 'woo-quickpay' ); ?></p>
    <p class="submit"><a href="#" class="woocommerce-quickpay-update-now button-primary"><?php _e( 'Run the updater', 'woo-quickpay' ); ?></a></p>
</div>
<script type="text/javascript">
    (function ($) {
        $( '.woocommerce-quickpay-update-now' ).click( 'click', function() {
            var confirm = window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'woo-quickpay' ) ); ?>' ); // jshint ignore:line

            if (confirm) {
                var message = $('#woocommerce-upgrade-notice');

                message.find('p').fadeOut();

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'quickpay_run_data_upgrader',
                    nonce: '<?php echo WC_QuickPay_Install::create_run_upgrader_nonce(); ?>'
                }, function () {
                    message.append($('<p></p>').text("<?php _e('The upgrader is now running. This might take a while. The notice will disappear once the upgrade is complete.', 'woo-quickpay'); ?>"));
                });
            }
        });
    })(jQuery);
</script>
