<?php

namespace MageSuite\Navigation\Service\Navigation;

interface BuilderInterface
{
    /**
     * Build navigation tree from specified root category id
     * @param int $rootCategoryId
     * @return \MageSuite\Navigation\Model\Navigation\Item[]
     */
    public function build($rootCategoryId);
}
