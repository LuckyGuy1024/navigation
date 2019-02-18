<?php

namespace MageSuite\Navigation\Migration;

class AddIncludeInMobileDefaultValue
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;


    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->connection = $resourceConnection->getConnection();
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->metadataPool = $metadataPool;
    }

    public function execute($includeInMobileAttributeId, $includeInDesktopAttributeId)
    {
        $categoryEntityTable = $this->connection->getTableName('catalog_category_entity');
        $categoryEntityIntTable = $this->connection->getTableName('catalog_category_entity_int');

        $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\CategoryInterface::class)->getLinkField();

        $stores = $this->storeManager->getStores(true);

        $storeIds = array_keys($stores);
        sort($storeIds);

        foreach($storeIds as $storeId){
            $query = "INSERT IGNORE INTO $categoryEntityIntTable (attribute_id, store_id, $linkField, value)
              SELECT
                $includeInMobileAttributeId AS attribute_id,
                $storeId AS store_id,
                category.$linkField,
                category_int.value as value
              FROM $categoryEntityTable AS category
              LEFT JOIN $categoryEntityIntTable AS category_int ON category.$linkField = category_int.$linkField
              WHERE category_int.attribute_id = $includeInDesktopAttributeId AND store_id = $storeId;";

            try{
                $this->connection->query($query);
            }catch (Exception $e){
                $message = sprintf('Error during AddIncludeInMobileDefaultValue::execute(): %s, storeId: %s', $e->getMessage(), $storeId);
                $this->logger->warning($message);
            }
        }
    }
}