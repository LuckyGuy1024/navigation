<?php

namespace MageSuite\Navigation\Block\Navigation;

class ActiveCategory extends \Magento\Framework\View\Element\Template
{
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    public function getActiveCategoryPath()
    {
        /** @var \Magento\Catalog\Model\Category $currentCategory */
        $currentCategory = $this->registry->registry('current_category');

        if ($currentCategory == null) {
            return 0;
        }

        $categoryPath = $currentCategory->getPath();

        return $this->removeRootCategoryFromPath($categoryPath);
    }

    private function removeRootCategoryFromPath($categoryPath)
    {
        return substr($categoryPath, strpos($categoryPath, '/') + 1);
    }
}
