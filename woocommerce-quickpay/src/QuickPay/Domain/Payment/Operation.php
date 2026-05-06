<?php

namespace QuickpayPSP\QuickPay\Domain\Payment;

final class Operation {

	/** @var int */
	private $id;

	/** @var string */
	private $type;

	/** @var int */
	private $amount;

	/** @var bool */
	private $pending;

	/** @var string */
	private $qp_status_code;

	/** @var string */
	private $aq_status_code;

	/** @var \DateTimeImmutable */
	private $created_at;

	public function __construct(
		int $id,
		string $type,
		int $amount,
		bool $pending,
		string $qp_status_code,
		string $aq_status_code,
		\DateTimeImmutable $created_at
	) {
		$this->id             = $id;
		$this->type           = $type;
		$this->amount         = $amount;
		$this->pending        = $pending;
		$this->qp_status_code = $qp_status_code;
		$this->aq_status_code = $aq_status_code;
		$this->created_at     = $created_at;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return $this->type;
	}

	public function get_amount(): int {
		return $this->amount;
	}

	public function is_pending(): bool {
		return $this->pending;
	}

	public function is_authorize(): bool {
		return 'authorize' === $this->type;
	}

	public function is_capture(): bool {
		return 'capture' === $this->type;
	}

	public function is_cancel(): bool {
		return 'cancel' === $this->type;
	}

	public function is_successful(): bool {
		return '20000' === $this->qp_status_code && '000' === $this->aq_status_code;
	}
}
