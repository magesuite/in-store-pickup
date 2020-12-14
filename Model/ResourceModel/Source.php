<?php

namespace MageSuite\InStorePickup\Model\ResourceModel;

class Source
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getItemsAvailableInSources($quoteItemsQuantities)
    {
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from(['isi' => $this->resourceConnection->getTableName('inventory_source_item')])
            ->where('isi.sku IN (?)', array_keys($quoteItemsQuantities));

        $sourceItems = $this->resourceConnection->getConnection()->fetchAll($select);

        if (empty($sourceItems)) {
            return [];
        }

        $itemsAvailableInSource = [];

        foreach($sourceItems as $sourceItem) {
            if ($sourceItem['quantity'] < 1 || $sourceItem['status'] == 0) {
                continue;
            }

            if ($sourceItem['quantity'] < $quoteItemsQuantities[$sourceItem['sku']]) {
                continue;
            }

            if (!isset($itemsAvailableInSource[$sourceItem['source_code']])) {
                $itemsAvailableInSource[$sourceItem['source_code']] = 0;
            }

            $itemsAvailableInSource[$sourceItem['source_code']] += 1;
        }

        return $itemsAvailableInSource;
    }
}
