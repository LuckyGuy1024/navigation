<?php

namespace MageSuite\Navigation\Plugin\MediaStorage\Model\File\Validator\NotProtectedExtension;

class AllowSvgFileTypeImageForCategory
{
    public function afterGetProtectedFileExtensions(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $subject, $result, $store = null)
    {
        if (is_array($result) && isset($result['svg'])) {
            unset($result['svg']);
        }

        return $result;
    }
}
