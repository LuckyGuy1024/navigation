<?php

namespace MageSuite\Navigation\Service\Category;

class CustomUrlGenerator
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function generate($url) {
        if(strpos($url, 'http') !== false){
            return $url;
        }

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        return $baseUrl . ltrim($url, '/');
    }
}