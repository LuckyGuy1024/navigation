<?php


namespace MageSuite\Navigation\Model\Navigation\ResourceModel;

class FeaturedProducts
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productsCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Catalog\Model\Config $catalogConfig
    )
    {
        $this->productsCollectionFactory = $productsCollectionFactory;
        $this->categoryResource = $categoryResource;
        $this->jsonDecoder = $jsonDecoder;
        $this->catalogConfig = $catalogConfig;
    }

    public function getProducts($category)
    {
        $featuredProductsIds = $this->getFeaturedProductsIds($category);

        if (empty($featuredProductsIds)) {
            return [];
        }

        return $this->productsCollectionFactory->create()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addIdFilter($featuredProductsIds)
            ->getItems();
    }

    protected function getFeaturedProductsIds($category)
    {
        $featuredProducts = $category->getFeaturedProducts();

        if ($featuredProducts == '{}') {
            $featuredProducts = $this->categoryResource
                ->getAttributeRawValue($category->getId(), 'featured_products', 0);
        }

        if (!$featuredProducts OR $featuredProducts == '{}') {
            return [];
        }

        return array_keys($this->jsonDecoder->decode($featuredProducts));
    }
}