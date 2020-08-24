<?php

namespace MageSuite\Navigation\Model\Navigation;

class ImageTeaser extends \MageSuite\ContentConstructorFrontend\Model\Component\ImageTeaser
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * @var \MageSuite\Navigation\Service\Mapper\CategoryMapper
     */
    protected $categoryMapper;

    public function __construct(
        \Magento\Catalog\Model\Category $category,
        \MageSuite\ContentConstructorFrontend\Model\Component\ImageTeaser\SlideFactory $slideFactory,
        \MageSuite\Navigation\Service\Mapper\CategoryMapper $categoryMapper,
        array $data = []
    ) {
        parent::__construct($slideFactory, $data);

        $this->category = $category;
        $this->categoryMapper = $categoryMapper;
    }

    public function getData($key = '', $index = null)
    {
        if ($key == 'items') {
            return $this->categoryMapper->mapCategory($this->getCategory());
        }

        return parent::getData($key, $index);
    }

    public function getCategory()
    {
        return $this->category;
    }
}
