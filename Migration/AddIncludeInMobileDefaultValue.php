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


    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->connection = $resourceConnection->getConnection();
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute($includeInMobileAttributeId, $includeInDesktopAttributeId)
    {
        $categoryEntityTable = $this->connection->getTableName('catalog_category_entity');
        $categoryEntityIntTable = $this->connection->getTableName('catalog_category_entity_int');

        $stores = $this->storeManager->getStores(true);

        $storeIds = array_keys($stores);
        sort($storeIds);

        foreach($storeIds as $storeId){
            $query = "INSERT IGNORE INTO $categoryEntityIntTable (attribute_id, store_id, entity_id, value)
              SELECT
                $includeInMobileAttributeId AS attribute_id,
                $storeId AS store_id,
                e.entity_id,
                i.value as value
              FROM $categoryEntityTable AS e
              LEFT JOIN $categoryEntityIntTable AS i ON e.entity_id = i.entity_id
              WHERE i.attribute_id = $includeInDesktopAttributeId AND store_id = $storeId;";

            try{
                $this->connection->query($query);
            }catch (Exception $e){
                $message = sprintf('Error during AddIncludeInMobileDefaultValue::execute(): %s, storeId: %s', $e->getMessage(), $storeId);
                $this->logger->warning($message);
            }
        }
    }
}