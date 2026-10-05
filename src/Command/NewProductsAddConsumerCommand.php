<?php

namespace App\Command;

use App\Service\RabbitMqMessengerService;
use Doctrine\ORM\EntityManagerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:new-products:consume',
    description: 'Consumes new products messages',
)]
class NewProductsAddConsumerCommand extends Command
{

    public function __construct(
        private RabbitMqMessengerService $rabbitMqMessengerService,
        private \App\Service\ProductService $productService,
        private EntityManagerInterface $entityManagerInterface
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Awaiting new products messages. Consumes messages from the queue.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $queue = 'add_new_product_queue';
        $channel = $this->rabbitMqMessengerService->getConsumerChannel();

        $channel->queue_declare($queue, false, true, false, false);

        $output->writeln('Waiting for messages in ' . $queue . '. To exit press CTRL+C');

        $callback = function (AMQPMessage $message) use ($io) {
            $data = json_decode($message->getBody(), true);

            $io->writeln('Message received, adding products now...');

            try {
                $this->productService->addNewProduct($data ?? []);
                $message->ack();
                $io->success('Message processed successfully.');
            } catch (\Throwable $e) {
                $io->writeln('Error processing message: ' . $e->getMessage());
                $productFailure = new \App\Entity\ProductFailure();
                $productFailure->setErrorType($e->getMessage());
                $productFailure->setPayload($data ?? []);
                $this->entityManagerInterface->persist($productFailure);
                $this->entityManagerInterface->flush();
                $message->nack(false, false);
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