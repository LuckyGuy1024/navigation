<?php

namespace MageSuite\Navigation\Block\Adminhtml\Category\Tab;

class FeaturedProducts extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonDecoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        array $data = []
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->jsonDecoder = $jsonDecoder;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_featured_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    public function getCategory()
    {
        $category = $this->coreRegistry->registry('category');
        if(!$category){
            $category = $this->categoryRepository->get(
                $this->getRequest()->getParam('id', 2),
                $this->getRequest()->getParam('store', 0)
            );

            $this->coreRegistry->register('category', $category);
        }

        return $category;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedFeaturedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        );

        $this->setCollection($collection);


        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_category',
            [
                'type' => 'checkbox',
                'name' => 'in_category',
                'values' => $this->_getSelectedFeaturedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => !$this->getCategory()->getProductsReadonly()
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('navigation/category_featuredProducts/grid', ['_current' => true]);
    }

    protected function _getSelectedFeaturedProducts()
    {
        $featuredProducts = $this->getRequest()->getPost('selected_featured_products');

        if ($featuredProducts === null) {
            $featuredProducts = $this->getCategory()->getFeaturedProducts();
        }

        if ($featuredProducts){
            if(is_string($featuredProducts)) {
                $featuredProducts = $this->jsonDecoder->decode($featuredProducts);
            }

            if (is_array($featuredProducts)) {
                return array_keys($featuredProducts);
            }
        }

        return $featuredProducts;
    }
}
