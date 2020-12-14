<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productId = 100;

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->load($productId);

if ($product->getId()) {
    $product->delete();
}


$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
