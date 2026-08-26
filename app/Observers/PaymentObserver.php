<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'payment',
            'subject_id' => $payment->id,
            'action' => 'payment_processed',
            'meta' => [
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'invoice_id' => $payment->invoice_id
            ]
        ]);
    }

    /**
     * Handle the Payment "updated" event.
     */
     public function updated(Payment $payment): void
    {
        // Log activity when the payment status is updated (e.g. pending to completed)
        if ($payment->isDirty('status')) {
            ActivityLog::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'subject_type' => 'payment',
                'subject_id' => $payment->id,
                'action' => 'status_changed',
                'meta' => [
                    'before' => ['status' => $payment->getOriginal('status')],
                    'after' => ['status' => $payment->status],
                ]
            ]);
        }
    }
}