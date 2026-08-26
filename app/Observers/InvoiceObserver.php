<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'invoice',
            'subject_id' => $invoice->id,
            'action' => 'created',
            'meta' => [
                'invoice_code' => $invoice->invoice_code,
                'total' => $invoice->total,
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount
            ]
        ]);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Log activity when the invoice status is updated
        if ($invoice->isDirty('status')) {
            ActivityLog::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'subject_type' => 'invoice',
                'subject_id' => $invoice->id,
                'action' => 'status_changed',
                'meta' => [
                    'before' => ['status' => $invoice->getOriginal('status')],
                    'after' => ['status' => $invoice->status],
                ]
            ]);
        }
    }
}