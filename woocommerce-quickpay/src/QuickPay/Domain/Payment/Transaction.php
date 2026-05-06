<?php

namespace QuickpayPSP\QuickPay\Domain\Payment;

final class Transaction {

	/** @var int */
	private $id;

	/** @var string */
	private $order_id;

	/** @var string */
	private $currency;

	/** @var int */
	private $amount;

	/** @var string */
	private $state;

	/** @var bool */
	private $accepted;

	/** @var array<string,mixed> */
	private $variables;

	/** @var Operation[] */
	private $operations;

	/** @var int|null */
	private $fee;

	/** @var bool */
	private $test_mode;

	/**
	 * @param array<string,mixed> $variables
	 * @param Operation[] $operations
	 */
	public function __construct(
		int $id,
		string $order_id,
		string $currency,
		int $amount,
		string $state,
		bool $accepted,
		array $variables,
		array $operations,
		?int $fee,
		bool $test_mode
	) {
		$this->id         = $id;
		$this->order_id   = $order_id;
		$this->currency   = $currency;
		$this->amount     = $amount;
		$this->state      = $state;
		$this->accepted   = $accepted;
		$this->variables  = $variables;
		$this->operations = $operations;
		$this->fee        = $fee;
		$this->test_mode  = $test_mode;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_order_id(): string {
		return $this->order_id;
	}

	public function get_currency(): string {
		return $this->currency;
	}

	public function get_amount(): int {
		return $this->amount;
	}

	public function get_state(): string {
		return $this->state;
	}

	public function is_accepted(): bool {
		return $this->accepted;
	}

	/** @return array<string,mixed> */
	public function get_variables(): array {
		return $this->variables;
	}

	/** @return mixed|null */
	public function get_variable( string $key ) {
		return $this->variables[ $key ] ?? null;
	}

	public function get_fee(): ?int {
		return $this->fee;
	}

	public function is_test_mode(): bool {
		return $this->test_mode;
	}

	/** @return Operation[] */
	public function get_operations(): array {
		return $this->operations;
	}

	public function get_latest_operation(): ?Operation {
		if ( empty( $this->operations ) ) {
			return null;
		}

		return $this->operations[ array_key_last( $this->operations ) ];
	}

	public function is_authorized(): bool {
		$op = $this->get_latest_operation();

		return $op && $op->is_authorize() && $op->is_successful();
	}

	public function is_captured(): bool {
		$op = $this->get_latest_operation();

		return $op && $op->is_capture() && $op->is_successful();
	}

	public function is_cancelled(): bool {
		$op = $this->get_latest_operation();

		return $op && $op->is_cancel() && $op->is_successful();
	}
}
