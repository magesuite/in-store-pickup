<?php

namespace MageSuite\InStorePickup\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DISPLAY_ONLY_SOURCES_WITH_CART_ALL_ITEMS_IN_STOCK = 'in_store_pickup/general/display_only_sources_with_all_cart_items_in_stock';

    public function displayOnlySourcesWithAllCartItemsInStock()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DISPLAY_ONLY_SOURCES_WITH_CART_ALL_ITEMS_IN_STOCK);
    }
}
