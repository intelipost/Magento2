<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Config\Source\Stock;

use \Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Sources implements OptionSourceInterface
{
    /** @var  SourceRepositoryInterface */
    protected $sourceRepository;

    public function __construct(SourceRepositoryInterface $sourceRepository)
    {
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptions();
    }

    protected function getOptions()
    {
        $collection = $this->sourceRepository->getList();

        $options = ['' => __('-- Empty --')];
        /** @var \Magento\Inventory\Model\Source $source */
        foreach ($collection->getItems() as $source) {
            $options[$source->getSourceCode()] = $source->getName();
        }

        return $options;
    }
}
