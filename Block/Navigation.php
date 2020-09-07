<?php

namespace MageSuite\Navigation\Block;

class Navigation extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_KEY_PREFIX = 'NAVIGATION_';
    const CACHE_GROUP = \MageSuite\Navigation\Model\Cache\Type::TYPE_IDENTIFIER;

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

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Cache\LockGuardedCacheLoader
     */
    protected $lockGuardedCacheLoader;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \MageSuite\Navigation\Service\Navigation\Builder $navigationBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->httpContext = $httpContext;
        $this->navigationBuilder = $navigationBuilder;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->session = $session;
        $this->lockGuardedCacheLoader = $context->getLockGuardedCacheLoader();
    }

    /**
     * @return \MageSuite\Navigation\Model\Navigation\Item[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        $buildedNavigation = $this->navigationBuilder->build($rootCategoryId, $this->getNavigationType());

        return $buildedNavigation;
    }

    public function getCacheTags()
    {
        return [\MageSuite\Navigation\Model\Cache\Type::CACHE_TAG];
    }

    /**
     * @return string
     */
    public function getNavigationType()
    {
        if ($this->getType() == \MageSuite\Navigation\Service\Navigation\Builder::TYPE_MOBILE) {
            return \MageSuite\Navigation\Service\Navigation\Builder::TYPE_MOBILE;
        }

        return \MageSuite\Navigation\Service\Navigation\Builder::TYPE_DESKTOP;
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeyInfo()
    {
        $store = $this->storeManager->getStore();
        return [
            $this->getNameInLayout(),
            $this->getNavigationType(),
            $store->getId(),
            $store->getCurrentCurrency()->getCode(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
        ];
    }
    
    public function getMobileNavigationEndpointUrl()
    {
        return $this->getUrl('navigation/mobile/index');
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
    public function getIdentities()
    {
        return [\MageSuite\Navigation\Model\Cache\Type::CACHE_TAG];
    }

    /**
     * Following methods had to be overriden to modify values for
     * CACHE_KEY_PREFIX and CACHE_GROUP constants
     */
    public function getCacheKey()
    {
        if ($this->hasData('cache_key')) {
            return self::CACHE_KEY_PREFIX . $this->getData('cache_key');
        }

        $key = $this->getCacheKeyInfo();
        $key = array_values($key);
        $key = implode('|', $key);
        $key = hash('sha1', $key);

        return self::CACHE_KEY_PREFIX . $key;
    }

    protected function _loadCache()
    {
        $collectAction = function () {
            if ($this->hasData('translate_inline')) {
                $this->inlineTranslation->suspend($this->getData('translate_inline'));
            }

            $this->_beforeToHtml();
            return $this->_toHtml();
        };

        if ($this->getCacheLifetime() === null || !$this->_cacheState->isEnabled(self::CACHE_GROUP)) {
            $html = $collectAction();
            if ($this->hasData('translate_inline')) {
                $this->inlineTranslation->resume();
            }
            return $html;
        }
        $loadAction = function () {
            return $this->_cache->load($this->getCacheKey());
        };

        $saveAction = function ($data) {
            $this->_saveCache($data);
            if ($this->hasData('translate_inline')) {
                $this->inlineTranslation->resume();
            }
        };

        return (string)$this->lockGuardedCacheLoader->lockedLoadData(
            $this->getCacheKey(),
            $loadAction,
            $collectAction,
            $saveAction
        );
    }

    protected function _saveCache($data)
    {
        if (!$this->getCacheLifetime() || !$this->_cacheState->isEnabled(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $this->getCacheKey();

        $this->_cache->save($data, $cacheKey, array_unique($this->getCacheTags()), $this->getCacheLifetime());
        return $this;
    }
}
