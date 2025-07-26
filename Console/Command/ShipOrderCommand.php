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
use Intelipost\Shipping\Cron\ShipOrder;

class ShipOrderCommand extends Command
{
    /**
     * @var ShipOrder
     */
    private $shipOrder;

    /**
     * @param ShipOrder $shipOrder
     * @param string|null $name
     */
    public function __construct(
        ShipOrder $shipOrder,
        ?string $name = null
    ) {
        $this->shipOrder = $shipOrder;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('intelipost:order:ship')
            ->setDescription('Ship orders to Intelipost')
            ->setHelp('This command executes the Intelipost ship order cron job to send orders for shipping.');
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
        $output->writeln('<info>Starting to ship orders to Intelipost...</info>');

        try {
            $this->shipOrder->execute();
            $output->writeln('<info>Orders shipped successfully.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error shipping orders: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
