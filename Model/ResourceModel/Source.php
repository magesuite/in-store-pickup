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
        $skus = array_keys($quoteItemsQuantities);
        $reservedQuantitiesBySourceCode = $this->getReservedQuantitiesBySourceCode($skus);

        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from(['isi' => $this->resourceConnection->getTableName('inventory_source_item')])
            ->where('isi.sku IN (?)', $skus);

        try {
            $sourceItems = $this->resourceConnection->getConnection()->fetchAll($select);
        } catch (\Exception $e) {
            return [];
        }

        if (empty($sourceItems)) {
            return [];
        }

        $itemsAvailableInSource = [];

        foreach($sourceItems as $sourceItem) {
            $reservedQuantity = $reservedQuantitiesBySourceCode[$sourceItem['sku']][$sourceItem['source_code']] ?? 0;
            $sourceItemQtyWithReservations = $sourceItem['quantity'] + $reservedQuantity;


            if ($sourceItemQtyWithReservations < 1 || $sourceItem['status'] == 0) {
                continue;
            }

            if ($sourceItemQtyWithReservations < $quoteItemsQuantities[$sourceItem['sku']]) {
                continue;
            }

            if (!isset($itemsAvailableInSource[$sourceItem['source_code']])) {
                $itemsAvailableInSource[$sourceItem['source_code']] = 0;
            }

            $itemsAvailableInSource[$sourceItem['source_code']] += 1;
        }

        return $itemsAvailableInSource;
    }

    protected function getReservedQuantitiesBySourceCode($skus)
    {
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from(['reservation' => $this->resourceConnection->getTableName('inventory_reservation')])
            ->where('reservation.sku IN (?)', $skus);

        try {
            $reservations = $this->resourceConnection->getConnection()->fetchAll($select);
        } catch (\Exception $e) {
            return [];
        }

        $orderIncrementIdsWithPickupLocationCode = $this->getOrderIncrementIdsWithPickupLocationCode($reservations);

        if (empty($orderIncrementIdsWithPickupLocationCode)) {
            return [];
        }

        $reservedQuantitiesBySourceCode = [];

        foreach ($reservations as $key => $reservation) {
            $metadata = json_decode($reservation['metadata'], true);
            $pickupLocationCode = $orderIncrementIdsWithPickupLocationCode[$metadata['object_increment_id']] ?? null;

            if (!$pickupLocationCode) {
                continue;
            }

            $reservedQuantitiesBySourceCode = $this->addReservedQuantity($reservedQuantitiesBySourceCode, $pickupLocationCode, $reservation);
        }

        return $reservedQuantitiesBySourceCode;
    }

    protected function getOrderIncrementIdsWithPickupLocationCode($reservations)
    {
        $orderIncrementIds = [];

        foreach ($reservations as $reservation) {
            $metadata = json_decode($reservation['metadata'], true);
            $orderIncrementIds[] = $metadata['object_increment_id'];
        }

        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from($this->resourceConnection->getTableName('sales_order'), ['increment_id', 'pickup_location_code'])
            ->where('increment_id IN (?)', $orderIncrementIds)
            ->where('pickup_location_code IS NOT NULL');

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }

    private function addReservedQuantity($reservedQuantitiesBySourceCode, $pickupLocationCode, $reservation)
    {
        $currentQty = $reservedQuantitiesBySourceCode[$reservation['sku']][$pickupLocationCode] ?? 0;
        $reservedQuantitiesBySourceCode[$reservation['sku']][$pickupLocationCode] = $currentQty + $reservation['quantity'];

        return $reservedQuantitiesBySourceCode;
    }
}
