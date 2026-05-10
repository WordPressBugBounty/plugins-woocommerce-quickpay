=== Quickpay for WooCommerce ===
Contributors: PerfectSolution
Tags: gateway, payment, quickpay, woocommerce, subscriptions
Requires at least: 6.7
Tested up to: 6.8
Stable tag: 8.0.1
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrates your Quickpay payment gateway into your WooCommerce installation.

== Description ==
With Quickpay for WooCommerce, you are able to integrate your QuickPay gateway to your WooCommerce install. With a wide list of API features including secure capturing, refunding and cancelling payments directly from your WooCommerce order overview. This is only a part of the many features found in this plugin.

== Installation ==
1. Upload the 'woocommerce-quickpay' folder to /wp-content/plugins/ on your server.
2. Log in to WordPress administration, click on the 'Plugins' tab.
3. Find Quickpay for WooCommerce in the plugin overview and activate it.
4. Go to WooCommerce -> Settings -> Payment Gateways -> Quickpay.
5. Fill in all the fields in the "Quickpay account" section and save the settings.
6. You are good to go.

== Dependencies ==
General:
1. PHP: >= 7.4
2. WooCommerce >= 7.1.0
3. If WooCommerce Subscriptions is used, the required minimum version is >= 5.0

== External services ==

This plugin uses the Quickpay API which is necessary to process payments in your store.

The information sent to Quickpay is based on the information stored in your WooCommerce store and can be adjusted
in the plugin settings, but transaction details may include order information and customer details.

Link to Quickpay terms of service: https://quickpay.net/terms-of-service/

== Changelog ==
= 8.0.1 =
* Fix: auto-capture and cancel logic for orders were incorrectly triggered on all gateways, causing warnings to be shown in the admin.

= 8.0.0 =
* Breaking: The plugin has been fully rewritten with a modern, layered architecture. While backwards compatibility is maintained through class aliases and legacy wrappers, any custom code relying on internal classes or static helpers should be reviewed.
* Feature: Add setting for adding a deadline in the payment window.
* Feature: Show confirmation notice when a payment link is created from the single order page.
* Feature: Add possibility to show custom info/warning notices in the admin via the `woocommerce_quickpay_add_admin_notice` hook.
* Feature: Added support for QTranslate X language switching.
* Feature: Added SpamShield integration support.
* Change: Removed Anyday availability cart filter which has been requested. This should now be handled on merchant side.
* Dev: Complete architectural rewrite into a layered structure (Admin, Application, WooCommerce, Compatibility, Extensions, Infrastructure) for improved maintainability and testability.
* Dev: Legacy static helpers (`WC_QuickPay_Helper`, `WC_QuickPay_Subscription`, `WC_QuickPay_Log`, etc.) are now backed by compatibility wrappers — deprecated and will be removed in a future version.
* Dev: Introduced PHPUnit test suite with isolated domain-layer tests (no WordPress bootstrap required).

= 7.5.1 =
* Feat: Adding WordPress home URL domain name to referer_url when creating transactions.

= 7.5.0 =
* Fik: Changed plugin display name to Quickpay for WooCommerce to comply with the WooCommerce trademark and WP guidelines.
* Fix: bumped tested with WC version to 10.5
* Fix: Improved sanitizing of various input fields
* Fix: Added nonce checks on ajax endpoints
* Fix: Added 'External services' section to readme
* Fix: Changed text-domain to 'woocommerce-quickpay'

= 7.4.0 =
* Fix: Subscription switching was not always creating a subscription payment when upgrading from a free subscription to a paid variant where no previous payments have been made.

= 7.3.5 =
* Fix: woocommerce_quickpay_get_order now makes a specific check on WC_Order instance to avoid possible direct property access on a WC_Order object.
* Fix: add check in callback handler to avoid payment failed transitioning on orders and subscriptions that does not actually require a payment.
* Fix: add extra check on qp_status_code in callback handler to avoid possible incorrect logging when refunds or captures fail.

= 7.3.4 =
* Fix: MobilePay Subscriptions now calls WC_Subscription::cancel_order to leverage support of pending-cancel/cancelled logic when setting "Cancelled agreement status" to 'Cancelled'.
* Fix: Change QuickPay to Quickpay in text strings
* Fix: Bump tested with WC version to 8.9

= 7.3.3 =
* Fix: WC_QuickPay_Admin_Orders_Lists_Table::handle_bulk_actions_orders relied on WC_QuickPay_Subscription::get_subscription_id for fetching a subscription entity.
* Feat: Add Requires Plugins header to define WooCommerce dependency which was introduced in WordPress 6.5

= 7.3.2 =
* Fix: Bump tested with version to 6.5 (WP)
* Fix: Bump tested with version to 8.8 (WC)
* Fix: Deprecation notice for wc_get_log_file_path in 8.6 and above

= 7.3.1 =
* Fix: Setting quickpay_complete_on_capture was missing from the settings UI

= 7.3.0 =
* Feat: MobilePay Checkout has been removed due to EOL of the product after MobilePay/Vipps merge.

= 7.2.0 =
* Feat: Add possibility to set a new order status upon cancelled payments
* Feat: MobilePay Subscriptions is now transitioning orders with failed payments to "failed" to maintain correct state of orders and their corresponding subscription.
* Fix: WC_QuickPay_Helper::is_browser now checks if HTTP_USER_AGENT is set on the request.
* Fix: Accessing the callback handler directly without a payload resulted in an Uncaught JsonException. This exception is now handled by returning an HTTP 400 response with a proper message.

= 7.1.0 =
* Feat: Add payment gateway support for WC Checkout Blocks
* Fix: Apple Pay - Rely on WC_Payment_Gateway::is_available when disabling gateways in checkout based on gateway specific criteria.
* Fix: Google Pay - Rely on WC_Payment_Gateway::is_available when disabling gateways in checkout based on gateway specific criteria.
* Fix: Anyday - Rely on WC_Payment_Gateway::is_available when disabling gateways in checkout based on gateway specific criteria.
* Fix: Deprecation notice of dynamically declared property $instructions in WC_QuickPay (PHP 8.2)

= 7.0.4 =
* Fix: Remove ISK from list of non-decimal currencies as the QuickPay API requires ISK amount to be multiplied

= 7.0.3 =
* Fix: Update autofee helper text.
* Fix: Bump tested WP version number to 6.3
* Fix: Bump tested WC version number to 8.1

= 7.0.2 =
* Fix: Manually creating a payment link from wp-admin on subscriptions with empty transaction IDs could lead to errors on link generation
* Fix: Problem with transaction fee from callbacks triggering an error when setting it on the order object.

= 7.0.1 =
* Fix: Remove strict return type from WC_QuickPay_Paypal::apply_gateway_icons

= 7.0.0 =
* Feat: Added support for High Performance Order Storage / Custom Order Tables
* Feat: Added template for meta-box-order.php
* Feat: Added template for meta-box-subscription.php
* Feat: Added support for Early Renewals modal
* Fix: Added payment.quickpay.net as a whitelisted host to avoid problems with wp_safe_redirect when changing payment method in WCS 5.1.0 and above.
* Fix: Adjust the link to payment methods documentation
* Fix: WC_QuickPay::remove_renewal_meta_data wasn't removing subscription meta data from renewal orders properly.
* Fix: 'Create payment' now patches transactions in 'initial' state and creates new payments in case they have already been authorized.
* Fix: 'Create payment' now ensures unique order numbers by adding a random string to the order number before sending it to the API. This fixes problems with duplicate order number errors from the API.
* Dev: Refactor order logic in general which means we are deprecating the WC_QuickPay_Order object and its methods. For better compatibility, and to avoid overhead, we are solely relying on the WC_Order object.
* Dev: Introducing utility helper classes used to replace logic in the WC_QuickPay_Order object
* Dev: Bump minimum required version of WooCommerce to 7.1.0
* Dev: Bump minimum required version of WooCommerce Subscriptions to 5.0
* Dev: Bump minimum required version of PHP to 7.4

= 6.8.3 =
* Fix: Avoid requesting quickpay_fetch_private_key on all order / subscription related pages.

= 6.8.2 =
* Fix: Add fees to basket items array
* Fix: Refactor WC_QuickPay_Order::get_transaction_basket_params_line_helper
* Fix: Remove shipping[tracking_number] and shipping[tracking_url] by default as they were empty anyway and resulted in problems with Resurs payments
* Fix: Vipps - adjust payment method to "vipps,vippspsp"
* Dev: Introducing filter woocommerce_quickpay_transaction_params_basket_apply_fees

= 6.8.1 =
* Fix: Rely on auto_capture_at instead of due_date for MPS payments
* Fix: Enhance the way auto_capture_at is calculated. It now relies on the timezone used in WordPress but can be changed with the filter woocommerce_quickpay_mps_timezone

= 6.8.0 =
* Feat: MobilePay Subscriptions - setting added to control status transition when a payment agreement is cancelled outside WooCommerce.
* Dev: add filter woocommerce_quickpay_mps_cancelled_from_status
* Dev: add filter woocommerce_quickpay_mps_cancel_agreement_status_options
* Fix: Bump tested with WC version to 6.6
* Fix: Bump tested with WP version to 6.0

= 6.7.0 =
* Feat: Anyday - hide gateway if currency is not DKK and if cart total is not within 300 - 30.000
* Fix: Remove VISA Electron card logo

= 6.6.0 =
* Feat: Add Google Pay as payment gateway
* Fix: Adjust SVG icons for Paypal, Apple Pay and Klarna to show properly in Safari
* Feat: Only show Apple Pay in Safari browsers

= 6.5.1 =
* Fix: MobilePay Subscription gateway is now available when using the "Change Payment" option from the account page.

= 6.5.0 =
* Feat: Add Apple Pay gateway - works only in Safari.
* Feat: Show a more user-friendly error message when payments fail in the callback handler.
* Dev: Add new filter woocommerce_quickpay_checkout_gateway_icon
* Fix: Bump WC + WP tested with versions to latest versions

= 6.4.3 =
* Dev: Add WC_QuickPay_Countries::getAlpha2FromAlpha3
* Fix: Use alpha2 country code instead of alpha3 country code in MP Checkout callbacks

= 6.4.2 =
* Fix: Modify force checkout logic used for MobilePay Checkout to enhance theme support.

= 6.4.1 =
* Fix: WC_QuickPay_API_Transaction::get_brand removes prefixed quickpay_ when fallback to variables.
* Fix: Refund now supports location header to avoid wrong response messages when capturing Klarna and Anyday payments.
* Dev: Add filter woocommerce_quickpay_transaction_params
* Dev: Add filter woocommerce_quickpay_transaction_params_description
* Bump WC tested with version
* Bump WP tested with version

= 6.4.0 =
* Feat: MobilePay Checkout now automatically ticks the terms and condition field during checkout.
* Fix: PHP8 compatability
* Fix: Capture now supports location header to avoid wrong response messages when capturing Klarna and Anyday payments.
* Fix: WC_QuickPay_API_Transaction::get_brand now falls back to variables.payment_methods sent from the shop if brand is empty on metadata.

= 6.3.0 =
* Remove: BETA from MobilePay Subscriptions
* Feature: Anyday split payments as payment gateway.
* Feature: MobilePay Checkout now shows the description as copy in checkout/mobilepay-checkout.php by default which makes it easier by merchants to adjust their communication.

= 6.2.0 =
* Remove: Bitcoin through Coinify

= 6.1.0 =
* Feature: New setting 'Cancel payments on order cancellation' allows merchants to automatically cancel payments when an order is cancelled. Disabled by default.
* Fix: Orders with multiple subscriptions didn't get the subscription transaction stored on every subscription.

= 6.0.3 =
* Fix: Danish translations not being loaded when enabled.
* Fix: Balance with decimals were incorrectly shown on "Capture Full Amount" button
* Fix: Bump 'tested with' versions

= 6.0.2 =
* Fix: Setting "Complete renewal orders" triggered on regular orders as well when enabled.

= 6.0.1 =
* Fix: Callbacks not being properly handled for non-subscription transactions

= 6.0.0 =
* Feature: MobilePay Subscriptions gateway.
* Feature: New setting 'Complete order on capture callbacks' - Completes an order when a callback regarding a captured payment is received from QuickPay.
* Feature: Add support for WCML country specific gateways added in WCML 4.10 (https://wpml.org/announcements/2020/08/wcml-4-10-currencies-and-payment-options-based-on-location/)
* Change: Recurring payments are no longer synchronized due to ?synchronization being deprecated.
* Fix: Undefined property: stdClass::$payment_method in WC_QuickPay_MobilePay_Checkout::callback_save_address
* Fix: Hide balance amount field when payment cannot be captured
* Fix: Show MobilePay logo as "Method" in the order list
* Breaking Change: Embedded / Overlay payments have been removed due to PSD2. Contact support@quickpay.net for questions regarding this decision.
* Developer: Add filter woocommerce_quickpay_create_recurring_payment_data
* Developer: Add filter woocommerce_quickpay_create_recurring_payment_data_{payment_gateway_id}
* Developer: Add filter woocommerce_quickpay_callback_payment_authorized_complete_payment
* Developer: Removed WC_QuickPay_Subscription::process_recurring_response as the logic has been refactored into hooks and callback handlers.


== Upgrade Notice ==
= 7.0.0 =
This is a major update. We recommend testing on a development or staging server before upgrading.
