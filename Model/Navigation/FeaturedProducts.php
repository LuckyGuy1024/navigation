<?php

namespace MageSuite\Navigation\Model\Navigation;

class FeaturedProducts
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * @var \Magento\Catalog\Model\Product[]
     */
    protected $products = null;

    /**
     * @var ResourceModel\FeaturedProducts
     */
    protected $resourceModel;

    public function __construct(
        \Magento\Catalog\Model\Category $category,
        \MageSuite\Navigation\Model\Navigation\ResourceModel\FeaturedProducts $resourceModel
    )
    {
        $this->category = $category;
        $this->resourceModel = $resourceModel;
    }

    public function getHeader() {
        return $this->category->getFeaturedProductsHeader();
    }

    public function getProducts() {
        if(!$this->products) {
            $this->products = $this->resourceModel->getProducts($this->category);
        }

        return $this->products;
    }
}