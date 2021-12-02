<?php
/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.bizcommerce.com.br/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Biz_Core
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */


namespace Intelipost\Shipping\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallProductAttributes implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface  */
    private $moduleDataSetup;

    /** @var EavSetupFactory  */
    private $eavSetupFactory;

    /** @var Config  */
    private $eavConfig;

    /** @var \Magento\Eav\Setup\EavSetup */
    private $eavSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributesList = [
            'height' => __('Height'),
            'width' => __('Width'),
            'length' => __('Length')
        ];

        $sortOrder = 50;
        foreach ($attributesList as $attrCode => $attrLabel) {
            if (
                !$this->isProductAttributeExists('intelipost_product_' . $attrCode)
                && !$this->isProductAttributeExists($attrCode)
            ) {
                $this->addAttribute($attrCode, $attrLabel, $sortOrder);
                $sortOrder++;
            }
        }

    }

    /**
     * @param $attrCode
     * @param $attrLabel
     * @param $sortOrder
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function addAttribute($attrCode, $attrLabel, $sortOrder)
    {
        $this->eavSetup->addAttribute(
            Product::ENTITY,
            $attrCode,
            [
                'type' => 'decimal',
                'label' => $attrLabel,
                'input' => 'text',
                'sort_order' => $sortOrder,
                'global' => Attribute::SCOPE_STORE,
                'user_defined' => true,
                'required' => false,
                'used_in_product_listing' => true,
                'group' => 'General',
                'unique' => false,
                'visible_on_front' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible' => true
            ]
        );
    }

    /**
     * Returns true if attribute exists and false if it doesn't exist
     *
     * @param string $field
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isProductAttributeExists($field)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);
        return ($attr && $attr->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
