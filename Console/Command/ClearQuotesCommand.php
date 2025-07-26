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
use Intelipost\Shipping\Cron\ClearQuotes;

class ClearQuotesCommand extends Command
{
    /**
     * @var ClearQuotes
     */
    private $clearQuotes;

    /**
     * @param ClearQuotes $clearQuotes
     * @param string|null $name
     */
    public function __construct(
        ClearQuotes $clearQuotes,
        ?string $name = null
    ) {
        $this->clearQuotes = $clearQuotes;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('intelipost:quotes:clear')
            ->setDescription('Delete old Intelipost quotes')
            ->setHelp('This command executes the Intelipost clear quotes cron job to remove old quote records.');
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
        $output->writeln('<info>Starting to clear old Intelipost quotes...</info>');

        try {
            $this->clearQuotes->execute();
            $output->writeln('<info>Old quotes cleared successfully.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error clearing quotes: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
