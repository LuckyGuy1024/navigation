<?php

namespace MageSuite\Navigation\Block;

class Navigation extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    const ONE_DAY = 86400;

    /**
     * @var \MageSuite\Navigation\Service\Navigation\Builder
     */
    protected $navigationBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \MageSuite\Navigation\Service\Navigation\Builder $navigationBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->httpContext = $httpContext;
        $this->navigationBuilder = $navigationBuilder;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }
    
    /**
     * @return \MageSuite\Navigation\Model\Navigation\Item[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems() {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        $buildedNavigation = $this->navigationBuilder->build($rootCategoryId, $this->getNavigationType());

        $cacheTags = $this->getNavigationTags($buildedNavigation);

        $this->_cache->save($this->serializer->serialize($cacheTags), 'navigation_'.$rootCategoryId.'_'.$this->getNavigationType(), $cacheTags);

        return $buildedNavigation;
    }

    /**
     * @return string
     */
    public function getNavigationType() {
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
            $this->storeManager->getStore()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCacheLifetime()
    {
        return self::ONE_DAY;
    }

    /**
     * @return string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdentities(){
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        $cachedTags = $this->_cache->load('navigation_'.$rootCategoryId.'_'.$this->getNavigationType());

        if($cachedTags) {
            return $this->serializer->unserialize($cachedTags);
        }

        $navigationItems = $this->getItems();

        return $this->getNavigationTags($navigationItems);
    }

    /**
     * @param \MageSuite\Navigation\Model\Navigation\Item[] $navigationItems
     */
    protected function getNavigationTags(array $navigationItems, $aggregatedTags = [\Magento\Catalog\Model\Category::CACHE_TAG])
    {
        if(empty($navigationItems)) {
            return $aggregatedTags;
        }

        foreach($navigationItems as $navigationItem) {
            if($navigationItem->hasSubItems()) {
                $aggregatedTags = array_merge($aggregatedTags, $this->getNavigationTags($navigationItem->getSubItems(), $aggregatedTags));
            }

            $aggregatedTags = array_merge($aggregatedTags, $navigationItem->getIdentities());
        }

        return array_unique($aggregatedTags);
    }
}