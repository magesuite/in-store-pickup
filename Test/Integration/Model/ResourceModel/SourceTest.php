<?php

namespace MageSuite\InStorePickup\Test\Integration\Model\ResourceModel;

class SourceTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_SOURCE_CODE_QUANTITY = 5;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\InStorePickup\Test\Integration\OrderHelper
     */
    protected $orderHelper;

    /**
     * @var \MageSuite\InStorePickup\Model\ResourceModel\Source
     */
    protected $sourceResource;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->orderHelper = $this->objectManager->create(\MageSuite\InStorePickup\Test\Integration\OrderHelper::class);
        $this->sourceResource = $this->objectManager->get(\MageSuite\InStorePickup\Model\ResourceModel\Source::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadGuestQuote
     */
    public function testItReturnItemsAvailableIfNoReservationAdded()
    {
        $quoteItemsQuantities = [
            'simple' => 3
        ];

        $result = $this->sourceResource->getItemsAvailableInSources($quoteItemsQuantities);

        $this->assertCount(1, $result);
        $this->assertEquals(['default' => 1], $result);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadGuestQuote
     */
    public function testItReturnItemsAvailableIfLowReservationAdded()
    {
        $quoteItemsQuantities = [
            'simple' => 3
        ];

        $itemsToReserve = 2;
        $this->orderHelper->placeOrder('guest_quote', $itemsToReserve);

        $result = $this->sourceResource->getItemsAvailableInSources($quoteItemsQuantities);

        $this->assertCount(1, $result);
        $this->assertEquals(['default' => 1], $result);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadGuestQuote
     */
    public function testItReturnEmptyArrayIfManyReservationAdded()
    {
        $quoteItemsQuantities = [
            'simple' => 3
        ];

        $itemsToReserve = 4;
        $this->orderHelper->placeOrder('guest_quote', $itemsToReserve);

        $result = $this->sourceResource->getItemsAvailableInSources($quoteItemsQuantities);

        $this->assertEmpty($result);
    }

    public static function loadGuestQuote()
    {
        require __DIR__ . '/../../_files/guest_quote.php';
    }

    public static function loadGuestQuoteRollback()
    {
        require __DIR__ . '/../../_files/guest_quote_rollback.php';
    }
}
