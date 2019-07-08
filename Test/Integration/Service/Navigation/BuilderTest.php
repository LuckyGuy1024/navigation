<?php

namespace MageSuite\Navigation\Test\Integration\Service\Navigation;

/**
 * @magentoAppArea frontend
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    const FIXTURE_DIRECTORY = __DIR__ . '/../../_files/';
    const ROOT_CATEGORY_ID = 2;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Navigation\Service\Navigation\Builder
     */
    protected $builder;


    public function setUp() {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->builder = $this->objectManager->get(\MageSuite\Navigation\Service\Navigation\Builder::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture loadCategoriesNotIncludedInMenu
     * @magentoCache all disabled
     */
    public function testItReturnsNavigationCorrectStructure() {
        $navigation = $this->builder->build(self::ROOT_CATEGORY_ID);
        $this->assertCount(7, $navigation);
        $this->assertCount(1, $navigation[0]->getSubItems());
        $this->assertEquals('Category 1', $navigation[0]->getLabel());
        $this->assertEquals('Category 2', $navigation[1]->getLabel());
        $this->assertEquals('Category 1.1', $navigation[0]->getSubItems()[0]->getLabel());
        $this->assertEquals('Category 1.1.1', $navigation[0]->getSubItems()[0]->getSubItems()[0]->getLabel());
        $this->assertEquals(2, $navigation[0]->getParentId());
        $this->assertEquals(2, $navigation[0]->getProductCount());
        $this->assertEquals(0, $navigation[1]->getProductCount());
        $this->assertEquals('http://localhost/index.php/category-1.html', $navigation[0]->getUrl());
        $this->assertEquals('http://localhost/index.php/category-1/category-1-1.html', $navigation[0]->getSubItems()[0]->getUrl());
        $this->assertEquals('http://localhost/index.php/category-1/category-1-1/category-1-1-1.html', $navigation[0]->getSubItems()[0]->getSubItems()[0]->getUrl());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture loadCategoriesNotIncludedInMenu
     */
    public function testItReturnsOnlyItemsForMobileNavigation() {
        $navigation = $this->builder->build(
            self::ROOT_CATEGORY_ID,
            \MageSuite\Navigation\Service\Navigation\Builder::TYPE_MOBILE
        );

        $this->assertCount(9, $navigation);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoAdminConfigFixture cc_frontend_extension/configuration/sort_alphabetically 1
     * @magentoDataFixture loadCategoriesWithProducts
     */
    public function testItReturnsNavigationCorrectSorting()
    {
        $sortedCategories = $this->builder->build(3331);
        $this->assertEquals($sortedCategories[0]->getLabel(), 'Ä Fourth subcategory');
        $this->assertEquals($sortedCategories[1]->getLabel(), 'A Second subcategory');
        $this->assertEquals($sortedCategories[2]->getLabel(), 'B Third subcategory');
        $this->assertEquals($sortedCategories[3]->getLabel(), 'C First subcategory');
    }


    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture loadCategoriesWithCustomAttributes
     */
    public function testItReturnsCategoriesWithCorrectAttributes() {
        $result = $this->builder->build(self::ROOT_CATEGORY_ID);
        $this->assertCount(10, $result);
        $this->assertEquals('http://localhost/index.php/testurl.html', $result[8]->getUrl());
        $this->assertEquals('http://localhost/index.php/testurl.html', $result[9]->getUrl());
        $this->assertEquals('cat14', $result[8]->getIdentifier());
        $this->assertEquals('cat15', $result[9]->getIdentifier());

        $featuredProducts = $result[9]->getFeaturedProducts();
        $this->assertEquals('Featured Products Header', $featuredProducts->getHeader());
        $this->assertCount(2, $featuredProducts->getProducts());
        $this->assertEquals('Second product', $featuredProducts->getProducts()[556]->getName());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture loadCategoriesWithCustomAttributes
     */
    public function testItReturnsNavigationWithImageTeaser() {
        $result = $this->builder->build(2);
        $navigationItem = $result[9];
        $this->assertEquals(15, $navigationItem->getId());

        $imageTeaser = $navigationItem->getImageTeaser();
        $this->assertTrue($navigationItem->hasImageTeaser());
        $this->assertCount(1, $navigationItem->getImageTeaser()->getSlides());

        $slide = $navigationItem->getImageTeaser()->getSlides()[0];

        $this->assertEquals('{{media url="http://localhost/pub/media/catalog/category/teaser.png"}}', $slide->getImage()['decoded']);
        $this->assertEquals('Image Teaser Headline', $slide->getImage()['headline']);
        $this->assertEquals('Image Teaser Paragraph', $slide->getDescription());
        $this->assertEquals('http://localhost/index.php/url', $slide->getCta()['href']);

        $navigationItem = $navigationItem->getSubItems()[0];
        $this->assertEquals(16, $navigationItem->getId());
        $this->assertFalse($navigationItem->hasImageTeaser());
    }

    public static function loadCategoriesNotIncludedInMenu() {
        include self::FIXTURE_DIRECTORY.'categories_not_included_in_menu.php';

        /** @var \Magento\Framework\App\CacheInterface $cache */
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\App\CacheInterface::class);

        $cache->remove(\MageSuite\Category\Model\ResourceModel\Category::CACHE_TAG);
    }

    public static function loadCategoriesWithCustomAttributes() {
        include self::FIXTURE_DIRECTORY.'categories_with_custom_attributes.php';

        /** @var \Magento\Framework\App\CacheInterface $cache */
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\App\CacheInterface::class);

        $cache->remove(\MageSuite\Category\Model\ResourceModel\Category::CACHE_TAG);
    }

    public static function loadCategoriesWithProducts() {
        include self::FIXTURE_DIRECTORY.'categories_sorted_with_products.php';

        /** @var \Magento\Framework\App\CacheInterface $cache */
        $cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\App\CacheInterface::class);

        $cache->remove(\MageSuite\Category\Model\ResourceModel\Category::CACHE_TAG);
    }
}
