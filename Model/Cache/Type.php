<?php

namespace MageSuite\Navigation\Model\Cache;

class Type extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    const TYPE_IDENTIFIER = 'navigation';
    const CACHE_TAG = 'navigation';

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $cacheContext;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Indexer\CacheContext $cacheContext
    )
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
        $this->eventManager = $eventManager;
        $this->cacheContext = $cacheContext;
    }

    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        if($mode == \Zend_Cache::CLEANING_MODE_ALL) {
            $this->clearFullPageCache();
        }

        return parent::clean($mode, $tags);
    }

    public function clearFullPageCache(): void
    {
        $this->cacheContext->registerTags([self::CACHE_TAG]);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
