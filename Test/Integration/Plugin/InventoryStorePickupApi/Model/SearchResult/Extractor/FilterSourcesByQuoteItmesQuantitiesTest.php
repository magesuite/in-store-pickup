<?php

namespace MageSuite\InStorePickup\Test\Integration\Plugin\InventoryInStorePickupApi\Model\SearchResult\Extractor;

class FilterSourcesByQuoteItmesQuantitiesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\InventoryInStorePickupApi\Model\SearchResult\Extractor
     */
    protected $extractor;

    /**
     * @var \MageSuite\InStorePickup\Helper\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \MageSuite\InStorePickup\Model\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceResourceMock;

    /**
     * @var \MageSuite\InStorePickup\Plugin\InventoryInStorePickupApi\Model\SearchResult\Extractor\FilterSourcesByQuoteItemsQuantities
     */
    protected $filterSourcesByQuoteItemsQuantities;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->cart = $this->objectManager->get(\Magento\Checkout\Model\Cart::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Model\ProductRepository::class);
        $this->quoteManagement = $this->objectManager->get(\Magento\Quote\Model\QuoteManagement::class);
        $this->extractor = $this->objectManager->get(\Magento\InventoryInStorePickupApi\Model\SearchResult\Extractor::class);

        $this->configurationMock = $this->getMockBuilder(\MageSuite\InStorePickup\Helper\Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sourceResourceMock = $this->getMockBuilder(\MageSuite\InStorePickup\Model\ResourceModel\Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterSourcesByQuoteItemsQuantities = $this->objectManager->create(
            \MageSuite\InStorePickup\Plugin\InventoryInStorePickupApi\Model\SearchResult\Extractor\FilterSourcesByQuoteItemsQuantities::class,
            [
                'configuration' => $this->configurationMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'sourceResource' => $this->sourceResourceMock
            ]
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     */
    public function testItReturnsAllSourcesWhenConfigurationIsDisabled()
    {
        $itemAvailableOnlyInOneSource = [
            'first' => 1,
            'second' => 0
        ];

        $this->checkoutSessionMock->method('getQuote')->willReturn($this->prepareQuote());
        $this->configurationMock->method('displayOnlyAvailableStores')->willReturn(false);
        $this->sourceResourceMock->method('getItemsAvailableInSources')->willReturn($itemAvailableOnlyInOneSource);

        $sources = $this->getSources();
        $processedSources = $this->filterSourcesByQuoteItemsQuantities->afterGetSources($this->extractor, $sources);

        $this->assertEquals(count($sources), count($processedSources));
        $this->assertArrayHasKey('first', $processedSources);
        $this->assertArrayHasKey('second', $processedSources);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     */
    public function testItReturnsOneSourceWhenProductIsOnlyInOneSource()
    {
        $itemAvailableOnlyInOneSource = [
            'first' => 1,
            'second' => 0
        ];

        $this->checkoutSessionMock->method('getQuote')->willReturn($this->prepareQuote());
        $this->configurationMock->method('displayOnlyAvailableStores')->willReturn(true);
        $this->sourceResourceMock->method('getItemsAvailableInSources')->willReturn($itemAvailableOnlyInOneSource);

        $sources = $this->getSources();
        $processedSources = $this->filterSourcesByQuoteItemsQuantities->afterGetSources($this->extractor, $sources);

        $this->assertEquals(1, count($processedSources));
        $this->assertArrayHasKey('first', $processedSources);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     */
    public function testItReturnsEmptyArrayIfProductIsNotAvailableIsSources()
    {
        $itemIsNotAvailableIsSources = [
            'first' => 0,
        ];

        $this->checkoutSessionMock->method('getQuote')->willReturn($this->prepareQuote());
        $this->configurationMock->method('displayOnlyAvailableStores')->willReturn(true);
        $this->sourceResourceMock->method('getItemsAvailableInSources')->willReturn($itemIsNotAvailableIsSources);

        $sources = $this->getSources();
        $processedSources = $this->filterSourcesByQuoteItemsQuantities->afterGetSources($this->extractor, $sources);

        $this->assertEquals(0, count($processedSources));
        $this->assertEmpty($processedSources);
    }

    protected function prepareQuote()
    {
        $productSku = 'simple';
        $product = $this->productRepository->get($productSku);

        $this->cart->addProduct($product, []);

        return $this->cart->getQuote();
    }

    protected function getSources()
    {
        return [
            'first' => ['source_code' => 'first'],
            'second' => ['source_code' => 'second'],
            'third' => ['source_code' => 'third']
        ];
    }

    public static function loadProduct()
    {
        require __DIR__ . '/../../../../../_files/product.php';
    }

    public static function loadProductRollback()
    {
        require __DIR__ . '/../../../../../_files/product_rollback.php';
    }
}
