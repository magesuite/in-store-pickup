<?php

namespace MageSuite\InStorePickup\Test\Integration;

class OrderHelper
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    public function __construct()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(\Magento\Quote\Model\QuoteIdMaskFactory::class);
    }

    public function placeOrder($quoteIdentifier, $productQty)
    {
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load($quoteIdentifier, 'reserved_order_id');

        $checkoutSession = $this->objectManager->get(\Magento\Checkout\Model\Session::class);
        $checkoutSession->setQuoteId($quote->getId());

        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->load($quote->getId(), 'quote_id');
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->productRepository->get('simple');
        $quote->addProduct($product, $productQty);

        $quote->getPayment()->setMethod('checkmo');
        $quote->getShippingAddress()->setShippingMethod('flatrate_flatrate')->setCollectShippingRates(true);
        $quote->collectTotals();

        $quoteRepository = $this->objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quoteRepository->save($quote);

        $cartManagement = $this->objectManager->get(\Magento\Quote\Api\GuestCartManagementInterface::class);
        $orderId = $cartManagement->placeOrder($cartId);

        $order = $this->objectManager->get(\Magento\Sales\Model\OrderRepository::class)->get($orderId);
        $order->setPickupLocationCode('default');
        $order->save();

        return $order;
    }
}
