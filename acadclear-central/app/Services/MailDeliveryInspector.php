<?php

namespace App\Services;

class MailDeliveryInspector
{
    public function currentMailer(): string
    {
        return (string) config('mail.default', 'log');
    }

    public function transport(?string $mailer = null): string
    {
        $mailer ??= $this->currentMailer();

        return (string) config("mail.mailers.{$mailer}.transport", $mailer);
    }

    public function deliversToInbox(?string $mailer = null, array $visited = []): bool
    {
        $mailer ??= $this->currentMailer();

        if (in_array($mailer, $visited, true)) {
            return false;
        }

        $visited[] = $mailer;
        $transport = $this->transport($mailer);

        if (in_array($transport, ['log', 'array'], true)) {
            return false;
        }

        if (in_array($transport, ['failover', 'roundrobin'], true)) {
            $mailers = config("mail.mailers.{$mailer}.mailers", []);

            foreach ($mailers as $nestedMailer) {
                if ($this->deliversToInbox((string) $nestedMailer, $visited)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function logsPreview(?string $mailer = null, array $visited = []): bool
    {
        $mailer ??= $this->currentMailer();

        if (in_array($mailer, $visited, true)) {
            return false;
        }

        $visited[] = $mailer;
        $transport = $this->transport($mailer);

        if ($transport === 'log') {
            return true;
        }

        if (in_array($transport, ['failover', 'roundrobin'], true)) {
            $mailers = config("mail.mailers.{$mailer}.mailers", []);

            foreach ($mailers as $nestedMailer) {
                if ($this->logsPreview((string) $nestedMailer, $visited)) {
                    return true;
                }
            }
        }

        return false;
    }
}
