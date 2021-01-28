<?php
namespace MageSuite\InStorePickup\Observer;

class SavePickupLocationCodeInOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $extensionAttributes = $order->getExtensionAttributes();

        if (empty($extensionAttributes)) {
            return $this;
        }

        $pickupLocationCode = $extensionAttributes->getPickupLocationCode();

        if (empty($pickupLocationCode)) {
            return $this;
        }

        $order->setPickupLocationCode($pickupLocationCode);
        return $this;
    }
}
