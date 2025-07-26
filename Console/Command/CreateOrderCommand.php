<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Intelipost\Shipping\Cron\CreateOrder;

class CreateOrderCommand extends Command
{
    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @param CreateOrder $createOrder
     * @param string|null $name
     */
    public function __construct(
        CreateOrder $createOrder,
        string $name = null
    ) {
        $this->createOrder = $createOrder;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('intelipost:order:create')
            ->setDescription('Create orders in Intelipost')
            ->setHelp('This command executes the Intelipost create order cron job to send new orders to Intelipost.');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting to create orders in Intelipost...</info>');
        
        try {
            $this->createOrder->execute();
            $output->writeln('<info>Orders created successfully.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error creating orders: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}