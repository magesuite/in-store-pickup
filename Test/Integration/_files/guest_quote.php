<?php

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/address_list.php');
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
$store = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId('simple')
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 5,
            'is_in_stock' => 1,
        ]
    )->save();

$addressData = [
    'telephone' => 3234676,
    'postcode' => 47676,
    'country_id' => 'US',
    'city' => 'CityX',
    'street' => ['Black str, 48'],
    'lastname' => 'Smith',
    'firstname' => 'John',
    'address_type' => 'shipping',
    'email' => 'some_email@mail.com',
    'region_id' => 1,
];

$billingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('guest_quote')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress);

$quoteRepository->save($quote);

$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
