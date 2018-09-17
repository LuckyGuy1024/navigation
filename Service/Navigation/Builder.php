<?php

namespace MageSuite\Navigation\Service\Navigation;

class Builder implements BuilderInterface
{
    const TYPE_DESKTOP = 'desktop';
    const TYPE_MOBILE = 'mobile';

    /**
     * @var \MageSuite\Navigation\Model\Navigation\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \MageSuite\Navigation\Model\Navigation\ItemFactory $itemFactory
    )
    {
        $this->itemFactory = $itemFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function build($rootCategoryId, $navigationType = self::TYPE_DESKTOP)
    {
        $navigationItems = [];

        $rootCategory = $this->categoryRepository->get($rootCategoryId);
        $childCategories = $this->getChildrenCategories($rootCategory);

        /** @var \Magento\Catalog\Model\Category $category */
        foreach($childCategories as $category) {
            if(!$this->isVisible($category, $navigationType)) {
                continue;
            }

            $navigationItems[] = $this->buildNavigationItemsTree($category, $navigationType);
        }

        return $navigationItems;
    }

    protected function buildNavigationItemsTree(\Magento\Catalog\Model\Category $category, $navigationType = self::TYPE_DESKTOP) {
        $navigationItem = $this->itemFactory->create(['category' => $category]);

        if(!$category->hasChildren()) {
            $navigationItem->setSubItems([]);

            return $navigationItem;
        }

        $subItems = [];

        foreach($this->getChildrenCategories($category) as $childCategory) {
            if(!$this->isVisible($childCategory, $navigationType)) {
                continue;
            }

            $subItems[] = $this->buildNavigationItemsTree($childCategory);
        }

        $navigationItem->setSubItems($subItems);

        return $navigationItem;
    }

    /**
     * Standard Category collection does not return include_in_menu attribute value. It must be added.
     * @param \Magento\Catalog\Model\Category $category
     * @return mixed
     */
    protected function getChildrenCategories($category) {
        $categories = $category->getChildrenCategories();

        $categories->clear();
        $categories->addAttributeToSelect([
            'parent_id',
            'include_in_menu',
            'include_in_mobile_navigation',
            'do_not_expand_flyout',
            'category_custom_url',
            'category_identifier',
            'featured_products_header',
            'featured_products',
            'category_icon',
            'image_teaser_headline',
            'image_teaser_subheadline',
            'image_teaser_paragraph',
            'image_teaser_button_label',
            'image_teaser_button_link',
            'image_teaser'
        ]);
        $categories->load();

        return $categories;
    }

    protected function isVisible($category, $navigationType = self::TYPE_DESKTOP) {
        if($navigationType == self::TYPE_MOBILE) {
            return $category->getIncludeInMobileNavigation();
        }

        return $category->getIncludeInMenu();
    }
}