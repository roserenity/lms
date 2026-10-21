<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    const OFFLINE = 'offline';
    const ONLINE = 'online';

    const PAYMENT_SUCCESS = 1; #APPROVED
    const PAYMENT_PENDING = 2;
    const PAYMENT_FAILED = 3; #DISQUALIFIED
    const PAYMENT_CANCELLED = 4;
    const PAYMENT_COMMITMENT_LETTER = 5;
    const PAYMENT_FREE = 6;
    const PAYMENT_DISAPPROVED = 7; #INVALID POP

    const PAYMENT_STATUS_LABELS = [
        1 => 'APPROVED',
        2 => 'PENDING',
        3 => 'DISQUALIFIED',
        4 => 'CANCELLED',
        5 => 'COMMITMENT LETTER',
        6 => 'FREE',
        7 => 'DISAPPROVED',
    ];

    const PARTICIPANT_TYPE_LABELS = [
        'INTERNAL EMPLOYEE' => 'internal_employee',
        'REGULAR EMPLOYEE' => 'regular_payee',
        'REGULAR EMPLOEE ( Partner Awardee )' => 'regular_payee_hrm',
    ];

    const PT_CASH_DEPOSIT = 'cash_deposit';
    // const PT_CASH_CASHIER = 'cash-cashier';
    const PT_CHECK_DEPOSIT = 'check_deposit';
    // const PT_CHECK_CASHIER = 'check-cashier';
    const PT_DEBIT_MEMO = 'debit_memo';
    const PT_FUND_TRANSFER = 'fund_transfer';
    const PT_COMMITMENT_LETTER = 'commitment_letter';
    // const PT_LDDAP_ADA = 'lddap_ada';

    const PARTICIPANT_PRIME = 'regular_payee_hrm';
    const PARTICIPANT_INTERNAL = 'internal_employee';
    const PARTICIPANT_REGULAR= 'regular_payee';

    const RECEIPT_VIA_PICK_UP = 'pick_up';
    const RECEIPT_COURIER = 'courier';

    protected $fillable = [
        'reference_id',
        'official_receipt_no',
        'course_id',
        'user_id',
        'enroll_by_id',
        'type_of_participant',
        'type_of_payment',
        'mode_of_payment',
        'amount',
        'is_free',
        'for_verification',
        'status',
        'data',
        'receipt_pick_up_option',
        'receipt_pick_up_address'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollee()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function enroll_by()
    {
        return $this->belongsTo(User::class, 'enroll_by_id', 'id');
    }

    public function nomination_form()
    {
        return $this->hasOne(NominationForm::class);
    }

    public function proof_of_payment()
    {
        return $this->hasOne(ProofOfPayment::class)->latestOfMany();
    }

    public function commitment_letter()
    {
        return $this->hasOne(CommitmentLetter::class);
    }

    public function endorsement_letter()
    {
        return $this->hasOne(EndorsementLetter::class);
    }

    public function prime_resolution()
    {
        return $this->hasOne(PrimeResolution::class);
    }

    public function scopeSuccess($query)
    {
      return $query->where('status', Invoice::PAYMENT_SUCCESS);
    }

    public function scopePending($query)
    {
      return $query->where('status', Invoice::PAYMENT_PENDING);
    }

    public function scopeFailed($query)
    {
      return $query->where('status', Invoice::PAYMENT_FAILED);
    }

    public function scopeCancelled($query)
    {
      return $query->where('status', Invoice::PAYMENT_CANCELLED);
    }

    public function scopeCommitmentLetter($query)
    {
      return $query->where('status', Invoice::PAYMENT_COMMITMENT_LETTER);
    }

    public function scopeForEnrollment($query, $enrollment)
    {
        return $query->where('user_id', $enrollment->user_id)
                    ->where('course_id', $enrollment->course_id);
    }

    public function scopePendingOrCommitted($query, $courseId, $userId)
    {
        return $query->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('status', self::PAYMENT_PENDING)
                    ->orWhere('status', self::PAYMENT_COMMITMENT_LETTER);
            });
    }
    
}
