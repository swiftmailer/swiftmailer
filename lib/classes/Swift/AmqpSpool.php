<?php

use Interop\Amqp\AmqpConsumer;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Queue\ExceptionInterface as PsrException;

/**
 * The class spools emails to the message queue via queue interop compatible transport.
 *
 * @author Max Kotliar
 */
class Swift_AmqpSpool extends \Swift_ConfigurableSpool
{
    /**
     * @var AmqpContext
     */
    private $context;

    /**
     * @var AmqpQueue
     */
    private $queue;

    /**
     * @param AmqpContext      $context
     * @param AmqpQueue|string $queue
     */
    public function __construct(AmqpContext $context, $queue = 'swiftmailer_spool')
    {
        if (false === $queue instanceof AmqpQueue) {
            $queue = $this->context->createQueue($queue);
        }

        $this->context = $context;
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function queueMessage(Swift_Mime_SimpleMessage $message)
    {
        try {
            $message = $this->context->createMessage(serialize($message));

            $this->context->createProducer()->send($this->queue, $message);
        } catch (PsrException $e) {
            throw new \Swift_IoException(sprintf('Unable to send message to message queue.'), null, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        $consumer = $this->context->createConsumer($this->queue);

        $isTransportStarted = false;

        $failedRecipients = (array) $failedRecipients;
        $count = 0;
        $time = time();

        $this->context->subscribe($consumer, function(AmqpMessage $psrMessage, AmqpConsumer $consumer) use ($transport, &$count) {
            $message = unserialize($psrMessage->getBody());

            $count += $transport->send($message, $failedRecipients);

            $consumer->acknowledge($psrMessage);

            return true;
        });

        while (true) {
            if (false == $isTransportStarted) {
                $transport->start();
                $isTransportStarted = true;
            }

            $this->context->consume(1000);

            if ($this->getMessageLimit() && $count >= $this->getMessageLimit()) {
                break;
            }

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }
}
