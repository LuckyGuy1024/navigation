<?php

namespace MageSuite\Navigation\Plugin\Service\Navigation\Builder;

class SortItemsAlphabetically
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    const XML_SORT_ALPHABETICALLY_CONFIG_PATH = 'cc_frontend_extension/configuration/sort_alphabetically';

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterBuild(\MageSuite\Navigation\Service\Navigation\Builder $subject, $result)
    {
        if (!$this->scopeConfig->getValue(self::XML_SORT_ALPHABETICALLY_CONFIG_PATH)) {
            return $result;
        }

        return $this->sortItems($result);
    }

    /**
     * @param \MageSuite\Navigation\Model\Navigation\Item[] $subItems
     * @return \MageSuite\Navigation\Model\Navigation\Item[]
     */
    protected function sortItems($subItems)
    {
        if (empty($subItems)) {
            return [];
        }

        usort($subItems, function (\MageSuite\Navigation\Model\Navigation\Item $a, \MageSuite\Navigation\Model\Navigation\Item $b) {
            setlocale(LC_ALL, 'en_GB');
            $c = iconv('UTF-8', 'ASCII//TRANSLIT', $a->getLabel());
            $d = iconv('UTF-8', 'ASCII//TRANSLIT', $b->getLabel());
            return ($c <=> $d);
        });

        /** @var \MageSuite\Navigation\Model\Navigation\Item $item */
        foreach ($subItems as $item) {
            if (!$item->hasSubItems()) {
                continue;
            }

            $sortedItems = $this->sortItems($item->getSubItems());

            $item->setSubItems($sortedItems);
        }

        return $subItems;
    }
}
