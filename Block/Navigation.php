<?php

namespace MageSuite\Navigation\Block;

class Navigation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MageSuite\Navigation\Service\Navigation\Builder
     */
    protected $navigationBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template $context,
        \MageSuite\Navigation\Service\Navigation\Builder $navigationBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->navigationBuilder = $navigationBuilder;
        $this->storeManager = $storeManager;
    }

    public function getItems() {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        return $this->navigationBuilder->build($rootCategoryId);
    }
}