<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

class LogEmailSent
{
    /**
     * Prevent duplicate logs when MessageSent is fired more than once per send.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->sent->getOriginalMessage();
            $to = [];
            foreach ($message->getTo() as $address) {
                $to[] = $address->getAddress();
            }
            if (empty($to)) {
                return;
            }
            $subject = $message->getSubject() ?? '';
            $mailableClass = null;
            if (isset($event->data['mailable']) && is_object($event->data['mailable'])) {
                $mailableClass = get_class($event->data['mailable']);
            }

            // Deduplicate: skip if we already logged the same send in the last 5 seconds
            $recent = EmailLog::where('subject', $subject)
                ->where('created_at', '>=', now()->subSeconds(5))
                ->whereRaw('JSON_CONTAINS(`to`, ?)', [json_encode($to[0])])
                ->exists();
            if ($recent) {
                return;
            }

            EmailLog::create([
                'to' => $to,
                'subject' => $subject,
                'mailable_class' => $mailableClass,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
