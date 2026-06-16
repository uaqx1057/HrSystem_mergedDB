<?php

namespace App\Mail\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class FallbackSmtpTransport implements TransportInterface
{
    public function __construct(
        protected TransportInterface $primaryTransport,
        protected TransportInterface $backupTransport,
        protected array $backupFrom = [],
        protected ?LoggerInterface $logger = null,
    ) {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        try {
            return $this->primaryTransport->send($message, $envelope);
        } catch (\Throwable $primaryException) {
            [$backupMessage, $backupEnvelope] = $this->prepareBackupDelivery($message, $envelope);

            if ($this->logger) {
                $this->logger->warning('Primary SMTP delivery failed, retrying with backup SMTP transport.', [
                    'primary_transport' => (string) $this->primaryTransport,
                    'backup_transport'  => (string) $this->backupTransport,
                    'exception'         => $primaryException->getMessage(),
                ]);
            }

            try {
                $sentMessage = $this->backupTransport->send($backupMessage, $backupEnvelope);

                if ($sentMessage) {
                    $sentMessage->appendDebug("Primary SMTP failed: {$primaryException->getMessage()}\n");
                }

                return $sentMessage;
            } catch (\Throwable $backupException) {
                if ($this->logger) {
                    $this->logger->error('Backup SMTP delivery failed after primary transport failure.', [
                        'primary_transport'  => (string) $this->primaryTransport,
                        'backup_transport'   => (string) $this->backupTransport,
                        'primary_exception'  => $primaryException->getMessage(),
                        'backup_exception'   => $backupException->getMessage(),
                    ]);
                }

                throw $backupException;
            }
        }
    }

    public function __toString(): string
    {
        return sprintf('fallback_smtp(%s => %s)', (string) $this->primaryTransport, (string) $this->backupTransport);
    }

    protected function prepareBackupDelivery(RawMessage $message, ?Envelope $envelope): array
    {
        if (empty($this->backupFrom['address'])) {
            return [$message, $envelope];
        }

        $backupAddress = new Address(
            $this->backupFrom['address'],
            $this->backupFrom['name'] ?? ''
        );

        $backupMessage = clone $message;

        if ($backupMessage instanceof Email) {
            $backupMessage->from($backupAddress);
            $backupMessage->sender($backupAddress);
            $backupMessage->returnPath($backupAddress->getAddress());
        }

        $backupEnvelope = $envelope
            ? new Envelope(new Address($backupAddress->getAddress()), $envelope->getRecipients())
            : null;

        return [$backupMessage, $backupEnvelope];
    }
}
