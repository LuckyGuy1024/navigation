<?php

namespace MageSuite\Navigation\Block;

class Navigation extends \Magento\Framework\View\Element\Template
{
    const ONE_DAY = 86400;

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

    /**
     * @return \MageSuite\Navigation\Model\Navigation\Item[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems() {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        return $this->navigationBuilder->build($rootCategoryId, $this->getNavigationType());
    }

    /**
     * @return string
     */
    protected function getNavigationType() {
        if($this->getType() == \MageSuite\Navigation\Service\Navigation\Builder::TYPE_MOBILE) {
            return \MageSuite\Navigation\Service\Navigation\Builder::TYPE_MOBILE;
        }

        return \MageSuite\Navigation\Service\Navigation\Builder::TYPE_DESKTOP;
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeyInfo()
    {
        return [
            $this->getNameInLayout(),
            $this->getNavigationType(),
            $this->storeManager->getStore()->getId()
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCacheLifetime()
    {
        return self::ONE_DAY;
    }
}