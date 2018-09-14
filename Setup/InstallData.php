<?php

namespace MageSuite\Navigation\Setup;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetupInterface;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetupInterface
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetupInterface = $moduleDataSetupInterface;

        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetupInterface]);
    }

    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'category_custom_url')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'category_custom_url',
                [
                    'type' => 'varchar',
                    'label' => 'Category Url',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 35,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General Information'
                ]
            );
        }

        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'image_teaser')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'image_teaser',
                [
                    'type' => 'varchar',
                    'label' => 'Image',
                    'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                    'input' => 'image',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Image Teaser'
                ]
            );
        }

        $attributes = [
            'image_teaser_headline' => ['label' => 'Headline', 'type' => 'varchar', 'input' => 'text', 'sort_order' => 20],
            'image_teaser_subheadline' => ['label' => 'Subheadline', 'type' => 'varchar', 'input' => 'text', 'sort_order' => 30],
            'image_teaser_paragraph' => ['label' => 'Paragraph', 'type' => 'text', 'input' => 'textarea', 'sort_order' => 40],
            'image_teaser_button_label' => ['label' => 'Button Label', 'type' => 'varchar', 'input' => 'text', 'sort_order' => 50],
            'image_teaser_button_link' => ['label' => 'Button Link', 'type' => 'varchar', 'input' => 'text', 'sort_order' => 60]
        ];

        foreach($attributes AS $attributeCode => $attribute){
            if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, $attributeCode)) {
                $this->eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    $attributeCode,
                    [
                        'type' => $attribute['type'],
                        'label' => $attribute['label'],
                        'input' => $attribute['input'],
                        'visible' => true,
                        'required' => false,
                        'sort_order' => $attribute['sort_order'],
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Image Teaser'
                    ]
                );
            }
        }

        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'featured_products_header')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'featured_products_header',
                [
                    'type' => 'varchar',
                    'label' => 'Header',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Featured Products'
                ]
            );
        }

        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'featured_products')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'featured_products',
                [
                    'type' => 'text',
                    'label' => 'Category Featured Products',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 20,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Featured Products'
                ]
            );
        }
    }
}