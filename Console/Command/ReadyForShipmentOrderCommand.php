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
use Intelipost\Shipping\Cron\ReadyForShipmentOrder;

class ReadyForShipmentOrderCommand extends Command
{
    /**
     * @var ReadyForShipmentOrder
     */
    private $readyForShipmentOrder;

    /**
     * @param ReadyForShipmentOrder $readyForShipmentOrder
     * @param string|null $name
     */
    public function __construct(
        ReadyForShipmentOrder $readyForShipmentOrder,
        ?string $name = null
    ) {
        $this->readyForShipmentOrder = $readyForShipmentOrder;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('intelipost:order:ready-for-shipment')
            ->setDescription('Process orders ready for shipment to Intelipost')
            ->setHelp('This command executes the Intelipost ready for shipment order cron job to process orders that are ready to be shipped.');
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
        $output->writeln('<info>Processing orders ready for shipment...</info>');

        try {
            $this->readyForShipmentOrder->execute();
            $output->writeln('<info>Orders processed successfully.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error processing orders: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
