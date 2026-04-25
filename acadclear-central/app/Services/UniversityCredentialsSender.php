<?php

namespace App\Services;

use App\Mail\UniversityCredentialsMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UniversityCredentialsSender
{
    public function __construct(
        private MailDeliveryInspector $mailDeliveryInspector
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $logContext
     * @return array{ok: bool, delivered: bool, message: string}
     */
    public function send(array $payload, array $logContext = []): array
    {
        try {
            Mail::to((string) $payload['adminEmail'])->send(new UniversityCredentialsMail(
                tenantName: (string) $payload['tenantName'],
                adminEmail: (string) $payload['adminEmail'],
                adminPassword: (string) $payload['adminPassword'],
                planName: (string) $payload['planName'],
                amountPaid: (float) $payload['amountPaid'],
                startsAt: $payload['startsAt'],
                endsAt: $payload['endsAt'],
                paymentMethod: (string) $payload['paymentMethod'],
                domain: (string) $payload['domain'],
                loginUrl: (string) $payload['loginUrl']
            ));

            if ($this->mailDeliveryInspector->deliversToInbox()) {
                return [
                    'ok' => true,
                    'delivered' => true,
                    'message' => 'Credentials email sent to ' . $payload['adminEmail'] . '.',
                ];
            }

            if ($this->mailDeliveryInspector->logsPreview()) {
                return [
                    'ok' => true,
                    'delivered' => false,
                    'message' => 'Email was not delivered to an inbox because MAIL_MAILER is set to log. A preview was written to storage/logs/laravel.log.',
                ];
            }

            return [
                'ok' => true,
                'delivered' => false,
                'message' => 'Email was generated, but not delivered to an inbox because MAIL_MAILER is set to ' . $this->mailDeliveryInspector->currentMailer() . '.',
            ];
        } catch (\Throwable $mailError) {
            Log::error('University credentials email failed to send.', array_merge($logContext, [
                'admin_email' => (string) $payload['adminEmail'],
                'mailer' => $this->mailDeliveryInspector->currentMailer(),
                'error' => $mailError->getMessage(),
            ]));

            return [
                'ok' => false,
                'delivered' => false,
                'message' => 'Credentials email could not be sent. Check the mail configuration and try again.',
            ];
        }
    }
}
