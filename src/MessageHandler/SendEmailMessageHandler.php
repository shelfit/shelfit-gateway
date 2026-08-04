<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private HttpClientInterface $mailerClient,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendEmailMessage $message)
    {
        try {
            $this->mailerClient->request('POST', '/api/send', [
                'json' => [
                    'idempotencyKey' => $message->getIdempotencyKey(),
                    'subject' => $message->getSubject(),
                    'from' => $message->getFrom(),
                    'to' => $message->getTo(),
                    'template' => $message->getTemplate(),
                    'variables' => $message->getVariables(),
                    'cc' => $message->getCc(),
                    'bcc' => $message->getBcc(),
                ]
            ]);
        }
        catch (Throwable $e) {
            $this->logger->error("Error dispatching SendEmailMessage", ['exception' => $e]);
            throw $e;
        }
    }
}