<?php

namespace MageSuite\InStorePickup\Plugin\InventoryInStorePickupApi\Model\SearchResult\Extractor;

class FilterSourcesByQuoteItemsQuantities
{
    /**
     * @var \MageSuite\InStorePickup\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \MageSuite\InStorePickup\Model\ResourceModel\Source
     */
    protected $sourceResource;

    public function __construct(
        \MageSuite\InStorePickup\Helper\Configuration $configuration,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \MageSuite\InStorePickup\Model\ResourceModel\Source $sourceResource
    ) {
        $this->configuration = $configuration;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->sourceResource = $sourceResource;
    }

    public function afterGetSources(\Magento\InventoryInStorePickupApi\Model\SearchResult\Extractor $subject, $result)
    {
        if (empty($result) || !$this->configuration->displayOnlySourcesWithAllCartItemsInStock()) {
            return $result;
        }

        $quoteId = $this->checkoutSession->getQuoteId();

        if (empty($quoteId)) {
            return $result;
        }

        $quote = $this->quoteRepository->get($quoteId);
        $items = $quote->getAllItems();

        if (empty($items)) {
            return $result;
        }

        $quoteItemsQuantities = [];

        foreach ($items as $item) {
            $quoteItemsQuantities[$item->getSku()] = $item->getQty();
        }

        return $this->filterSources($result, $quoteItemsQuantities);
    }

    protected function filterSources($sources, $quoteItemsQuantities)
    {
        $quoteItemsCount = count($quoteItemsQuantities);
        $itemsAvailableInSources = $this->sourceResource->getItemsAvailableInSources($quoteItemsQuantities);

        foreach ($sources as $sourceCode => $source) {
            $itemsAvailableInSource = $itemsAvailableInSources[$sourceCode] ?? 0;

            if (!$itemsAvailableInSource || $itemsAvailableInSource < $quoteItemsCount) {
                unset($sources[$sourceCode]);
            }
        }

        return $sources;
    }
}
