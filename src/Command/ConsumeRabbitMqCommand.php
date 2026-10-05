<?php

namespace App\Command;

use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\RabbitMqMessengerService;

#[AsCommand(
    name: 'app:rabbitmq:consume',
    description: 'Consumes custom RabbitMQ messages independently from Messenger.',
)]
class ConsumeRabbitMqCommand extends Command
{
    public function __construct(
        private RabbitMqMessengerService $rabbitMqMessengerService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Consuming messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $queue = 'test_queue';
        $channel = $this->rabbitMqMessengerService->getConsumerChannel();

        $channel->queue_declare($queue, false, true, false, false);

        $output->writeln('Waiting for messages in ' . $queue . '. To exit press CTRL+C');

        $callback = function (AMQPMessage $message) use ($io) {
            $data = json_decode($message->getBody(), true);

            $io->writeln('Received message: ' . json_encode($data));

            try {
                // Process the message here

                $message->ack();
                $io->success('Message processed successfully.');
            } catch (\Exception $e) {
                $io->writeln('Error processing message: ' . $e->getMessage());
                $message->nack();
            }
        };

        $channel->basic_qos(0, 1, false);

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $this->rabbitMqMessengerService->closeConnection();

        return Command::SUCCESS;
    }
}