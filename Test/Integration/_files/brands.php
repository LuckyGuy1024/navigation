<?php
$brand = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Creativestyle\BrandManagementExtension\Model\Brands');
$brand
    ->setEntityId(600)
    ->setStoreId(1)
    ->setUrlKey('urlkey')
    ->setLayoutUpdateXml('layout update xml')
    ->setBrandName('test_brand_name')
    ->setEnabled(1)
    ->setIsFeatured(1)
    ->setBrandIcon('testimage.png')
    ->setShowInBrandCarousel(1);

$brandRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Creativestyle\BrandManagementExtension\Api\BrandsRepositoryInterface');
$brandRepository->save($brand);

$brand = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Creativestyle\BrandManagementExtension\Model\Brands');
$brand
    ->setEntityId(700)
    ->setStoreId(1)
    ->setUrlKey('urlkey2')
    ->setLayoutUpdateXml('layout update xml')
    ->setBrandName('test_brand_name2')
    ->setEnabled(1)
    ->setIsFeatured(1)
    ->setBrandIcon('testimage.png')
    ->setShowInBrandCarousel(1);

$brandRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Creativestyle\BrandManagementExtension\Api\BrandsRepositoryInterface');
$brandRepository->save($brand);

$brand = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Creativestyle\BrandManagementExtension\Model\Brands');
$brand
    ->setEntityId(900)
    ->setStoreId(1)
    ->setUrlKey('urlkey3')
    ->setLayoutUpdateXml('layout update xml')
    ->setBrandName('test_brand_name3')
    ->setEnabled(1)
    ->setIsFeatured(1)
    ->setBrandIcon('testimage.png')
    ->setShowInBrandCarousel(0);

$brandRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Creativestyle\BrandManagementExtension\Api\BrandsRepositoryInterface');
$brandRepository->save($brand);