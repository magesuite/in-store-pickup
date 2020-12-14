<?php

namespace MageSuite\InStorePickup\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DISPLAY_ONLY_AVAILABLE_STORES = 'in_store_pickup/general/display_only_available_stores';

    public function displayOnlyAvailableStores()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DISPLAY_ONLY_AVAILABLE_STORES);
    }
}
