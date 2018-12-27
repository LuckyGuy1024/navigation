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

    /**
     * @var string[]
     */
    private $identities;

    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \MageSuite\Navigation\Model\Navigation\ItemFactory $itemFactory
    )
    {
        $this->itemFactory = $itemFactory;
        $this->categoryRepository = $categoryRepository;
        $this->identities = [];
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
        $this->addIdentities($category->getIdentities());

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
            $this->addIdentities($childCategory->getIdentities());
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
        $categories->addAttributeToSelect('*');
        $categories->load();

        return $categories;
    }

    protected function isVisible($category, $navigationType = self::TYPE_DESKTOP) {
        if($navigationType == self::TYPE_MOBILE) {
            return $category->getIncludeInMobileNavigation();
        }

        return $category->getIncludeInMenu();
    }

    /**
     * @return string[]
     */
    public function getIdentities(){
        return $this->identities;
    }

    private function addIdentities(array $identities) {
        foreach($identities as $identity) {
            if(in_array($identity, $this->identities)){
                continue;
            }

            $this->identities[] = $identity;
        }
    }
}