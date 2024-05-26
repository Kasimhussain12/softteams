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
namespace SoftTeams\SalePromotion\Block\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use SoftTeams\SalePromotion\Helper\Data as Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Customer\Model\SessionFactory as CustomerSession;

class Sale extends Template
{
    protected Helper $saleHelper;
    protected ProductRepositoryInterface $productRepository;
    protected CheckoutSession $checkoutSession;
    protected CustomerSession $customerSession;

    public function __construct(
        Template\Context $context,
        Helper $saleHelper,
        ProductRepositoryInterface $productRepository,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->saleHelper = $saleHelper;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function isEnabled(): mixed
    {
        return $this->saleHelper->isEnabled();
    }

    /**
     * @return mixed
     */
    public function getPromoProduct(): mixed
    {
        return $this->saleHelper->getPromoProduct();
    }

    /**
     * @return ProductInterface|null
     */
    public function getPromoProductData()
    {
        $sku = $this->getPromoProduct();
        try {
            return $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function showPromo(): bool
    {
        // Retrieve common values once and store them in variables
        $isEnabled = $this->isEnabled();
        $promoProductData = $this->getPromoProductData();
        $promoProductAvailable = $promoProductData ? $promoProductData->isAvailable() : false;
        $items = $this->checkoutSession->create()->getQuote()->getAllVisibleItems();
        $itemCount = count($items);
        $promoByCartProductQty = $this->saleHelper->getPromoByCartProductQty();
        $isEnabledForOrderTotal = $this->saleHelper->isEnabledForOrderTotal();
        $orderTotal = $this->getOrderTotal();
        $requiredOrderTotal = $this->saleHelper->getOrderTotal();
        // Check conditions in a single sequence
        if ($isEnabled && $promoProductAvailable) {
            if ($itemCount == $promoByCartProductQty) {
                return true;
            }
//            if ($isEnabledForOrderTotal && $orderTotal >= $requiredOrderTotal) {
//                return true;
//            }
        }
        return false;
    }

    /**
     * Check if the cart is not empty and contains only one item
     * @return bool
     *@throws NoSuchEntityException|LocalizedException
     */
    public function isSingleItemInCart(): bool
    {
        $items = $this->checkoutSession->create()->getQuote()->getAllVisibleItems();
        return count($items) == 1 && $items[0]->getQty() == 1;
    }

    /**
     * Get the order total from the session
     * @return float
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getOrderTotal(): float
    {
        return $this->checkoutSession->create()->getQuote()->getGrandTotal();
    }
}
