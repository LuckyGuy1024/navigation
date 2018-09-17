<?php

namespace MageSuite\Navigation\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetupInterface;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetupInterface,
        \Magento\Eav\Model\Config $eavConfig

    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetupInterface = $moduleDataSetupInterface;
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetupInterface]);
        $this->eavConfig = $eavConfig;
    }

    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addIncludeInMobileNavigationAttribute();
        }

        $setup->endSetup();
    }

    protected function addIncludeInMobileNavigationAttribute()
    {
        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'include_in_mobile_navigation')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'include_in_mobile_navigation',
                [
                    'type' => 'int',
                    'label' => 'Include in mobile navigation',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => '1',
                    'sort_order' => 10,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General Information',
                ]
            );
        }
    }
}
