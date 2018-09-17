<?php

namespace MageSuite\Navigation\Block\Adminhtml\Category;

class AssignFeaturedProducts extends \Magento\Backend\Block\Template
{

    protected $_template = 'category/assign_featured_products.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \MageSuite\Navigation\Block\Adminhtml\Category\Tab\FeaturedProducts::class,
                'category.featured.products.grid'
            );
        }

        return $this->blockGrid;
    }

    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    public function getFeaturedProductsJson()
    {
        $featuredProducts = $this->getCategory()->getFeaturedProducts();

        if (!empty($featuredProducts)) {
            return $featuredProducts;
        }

        return '{}';
    }

    public function getCategory()
    {
        return $this->registry->registry('category');
    }
}
