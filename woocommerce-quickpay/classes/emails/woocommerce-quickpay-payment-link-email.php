<?php

class WC_QuickPay_Payment_Link_Email extends WC_Email {

	private $wcqp_template_path;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customer_email = true;
		$this->id             = 'woocommerce_quickpay_payment_link';
		$this->title          = esc_html__( 'Payment link created', 'woocommerce-quickpay' );
		$this->description    = esc_html__( 'This e-mail is sent upon manual payment link creation by a shop admin.', 'woocommerce-quickpay' );
		$this->template_html  = 'emails/customer-quickpay-payment-link.php';
		$this->template_plain = 'emails/plain/customer-quickpay-payment-link.php';
		$this->placeholders   = [
			'{site_title}'   => $this->get_blogname(),
			'{order_date}'   => '',
			'{order_number}' => '',
			'{payment_link}' => '',
		];

		$this->wcqp_template_path = WCQP_PATH . 'templates/woocommerce/';
		// Triggers for this email.

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param WC_Order $order
	 * @param null               $payment_link
	 */
	public function trigger( $payment_link, WC_Order $order ) {
		$this->setup_locale();

		$this->object                         = $order;
		$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
		$this->placeholders['{order_number}'] = $this->object->get_order_number();
		$this->placeholders['{payment_link}'] = $payment_link;
		$this->recipient                      = $this->object->get_billing_email();

		if ( $payment_link && $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, [
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'         => $this,
			'payment_link'  => $this->placeholders['{payment_link}'],
		], '', $this->wcqp_template_path );
	}

	/**
	 * Get content plain.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, [
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'         => $this,
			'payment_link'  => $this->placeholders['{payment_link}'],
		], '', $this->wcqp_template_path );
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'    => [
				'title'   => esc_html__( 'Enable/Disable', 'woocommerce-quickpay' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable this email notification', 'woocommerce-quickpay' ),
				'default' => 'yes',
			],
			'subject'    => [
				'title'       => esc_html__( 'Subject', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( esc_html__( 'Available placeholders: %s', 'woocommerce-quickpay' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			],
			'heading'    => [
				'title'       => esc_html__( 'Email heading', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( esc_html__( 'Available placeholders: %s', 'woocommerce-quickpay' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			],
			'email_type' => [
				'title'       => esc_html__( 'Email type', 'woocommerce-quickpay' ),
				'type'        => 'select',
				'description' => esc_html__( 'Choose which format of email to send.', 'woocommerce-quickpay' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * Get email subject.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_subject() {
		return esc_html__( 'Payment link for your order ({order_number})', 'woocommerce-quickpay' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_heading() {
		return esc_html__( 'This is your payment link', 'woocommerce-quickpay' );
	}
}
