<?php

namespace MageSuite\Navigation\Model\Navigation;

class Item extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $category;

    /**
     * @var FeaturedProductsFactory
     */
    protected $featuredProductsFactory;

    /**
     * @var null|FeaturedProducts
     */
    protected $featuredProducts = null;

    /**
     * @var ImageTeaserFactory
     */
    protected $imageTeaserFactory;

    /**
     * @var null|ImageTeaser
     */
    protected $imageTeaser = null;

    /**
     * @var \MageSuite\Navigation\Service\Category\CustomUrlGenerator
     */
    protected $customUrlGenerator;

    public function __construct(
        \Magento\Catalog\Api\Data\CategoryInterface $category,
        FeaturedProductsFactory $featuredProductsFactory,
        ImageTeaserFactory $imageTeaserFactory,
        \MageSuite\Navigation\Service\Category\CustomUrlGenerator $customUrlGenerator,
        array $data = []
    )
    {
        parent::__construct($data);

        $this->category = $category;
        $this->featuredProductsFactory = $featuredProductsFactory;
        $this->imageTeaserFactory = $imageTeaserFactory;
        $this->customUrlGenerator = $customUrlGenerator;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->category->getId();
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->category->getName();
    }

    public function getIdentifier() {
        return $this->category->getCategoryIdentifier();
    }

    /**
     * @return int
     */
    public function getParentId() {
        return $this->category->getParentId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl() {
        $customUrl = $this->category->getCategoryCustomUrl();

        if(!$customUrl){
            return $this->category->getUrl();
        }

        return $this->customUrlGenerator->generate($customUrl);
    }

    /**
     * @return FeaturedProducts
     */
    public function getFeaturedProducts() {
        if(!$this->featuredProducts)  {
            $this->featuredProducts = $this->featuredProductsFactory->create(['category' => $this->category]);
        }

        return $this->featuredProducts;
    }

    /**
     * @return \MageSuite\Navigation\Model\Navigation\ImageTeaser
     */
    public function getImageTeaser() {
        if(!$this->imageTeaser)  {
            $this->imageTeaser = $this->imageTeaserFactory->create(['category' => $this->category]);
        }

        return $this->imageTeaser;
    }

    /**
     * @return bool
     */
    public function hasFeaturedProducts() {
        return !empty($this->getFeaturedProducts()->getProducts());
    }

    /**
     * @return bool
     */
    public function hasImageTeaser() {
        if($this->category->getLevel() > 2) {
            return false;
        }

        return !empty($this->getImageTeaser()->getImageUrl());
    }
}