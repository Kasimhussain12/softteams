<?php
/**
 * SoftTeams
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the SoftTeams.com license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    SoftTeams
 * @package     SoftTeams_SalePromotion
 * @copyright   Copyright (c) 2024
 */
namespace SoftTeams\SalePromotion\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SALE_PROMOTION = 'sale_promotion/';

    /**
     * @param $field
     * @param $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SALE_PROMOTION . 'general/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isEnabled($storeId = null): mixed
    {
        return $this->getConfigValue('enabled', $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getPromoProduct($storeId = null): mixed
    {
        return $this->getConfigValue('promo_product', $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getPromoByCartProductQty($storeId = null): mixed
    {
        return $this->getConfigValue('by_cart_qty', $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isEnabledForOrderTotal($storeId = null): mixed
    {
        return $this->getConfigValue('enable_order_total', $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getOrderTotal($storeId = null): mixed
    {
        return $this->getConfigValue('order_total', $storeId);
    }
}
