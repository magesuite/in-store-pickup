<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);

$quote->load('guest_quote', 'reserved_order_id');

$quoteId = $quote->getId();
if (null !== $quoteId) {
    $quote->delete();
    $quoteIdMask->delete($quoteId);
}

$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('simple');
    $productRepository->delete($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

$stockRegistryStorage = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);
$stockRegistryStorage->clean();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
