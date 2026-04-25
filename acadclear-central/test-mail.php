<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Services\MailDeliveryInspector;
use Illuminate\Support\Facades\Mail;

$recipient = $argv[1] ?? 'christiandavepombo@gmail.com';
$mailInspector = $app->make(MailDeliveryInspector::class);

try {
    Mail::raw('Test email from AcadClear with new Gmail App Password', function ($msg) use ($recipient) {
        $msg->to($recipient)
            ->subject('AcadClear SMTP Test - Gmail App Password');
    });

    if ($mailInspector->deliversToInbox()) {
        echo 'Email send completed using mailer: ' . $mailInspector->currentMailer() . PHP_EOL;
        echo 'Recipient: ' . $recipient . PHP_EOL;
        exit(0);
    }

    if ($mailInspector->logsPreview()) {
        echo 'MAIL_MAILER is set to log, so the message was not delivered to an inbox.' . PHP_EOL;
        echo 'Preview written to storage/logs/laravel.log for recipient: ' . $recipient . PHP_EOL;
        exit(0);
    }

    echo 'MAIL_MAILER is set to ' . $mailInspector->currentMailer() . ', so the message was generated but not delivered to an inbox.' . PHP_EOL;
    echo 'Recipient: ' . $recipient . PHP_EOL;
} catch (Exception $e) {
    echo 'Email failed: ' . $e->getMessage() . PHP_EOL;
}
