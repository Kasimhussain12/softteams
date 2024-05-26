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
namespace SoftTeams\SalePromotion\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use SoftTeams\SalePromotion\Helper\Data as SaleHelper;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Psr\Log\LoggerInterface;
class AddFreeProduct implements ObserverInterface
{
    protected ProductRepositoryInterface $productRepository;
    protected CartRepositoryInterface $cartRepository;
    protected SaleHelper $saleHelper;
    protected CheckoutSession $checkoutSession;
    protected LoggerInterface $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface    $cartRepository,
        SaleHelper                 $saleHelper,
        CheckoutSession            $checkoutSession,
        LoggerInterface            $logger
    )
    {
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->saleHelper = $saleHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        // Check if the sale promotion is enabled and a promo product SKU is set
        if (!$this->saleHelper->isEnabled() || !$this->saleHelper->getPromoProduct()) {
            return;
        }
        // Check if the free product is already in the cart
        $freeProductSku = $this->saleHelper->getPromoProduct();
        $session = $this->checkoutSession->create();
        $quote = $session->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        $itemCount = count($cartItems);
        $promoByCartProductQty = $this->saleHelper->getPromoByCartProductQty();

        foreach ($cartItems as $item) {
            $product = $item->getProduct();
            if ($product->getSku() == $freeProductSku) {
                return; // Exit if the free product is already in the cart
            }
        }
        // Add the free product to the cart if it's not already there
        try {
            $product = $this->productRepository->get($freeProductSku);
            $productId = $product->getId();
            $qty = 1;
            // Validate product ID
            if ($productId && $itemCount > $promoByCartProductQty) {
                // Add the product to the quote
                $quote->addProduct($product, 1);
                $this->cartRepository->save($quote);
                // Set the custom price for the free product
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getSku() == $freeProductSku) {
                        $item->setCustomPrice(0);
                        $item->setOriginalCustomPrice(0);
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
                $this->cartRepository->save($quote);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid product ID or quantity.')
                );
            }
        } catch (\Exception $e) {
            // Log the exception
            $this->logger->error('Exception occurred while adding free product to cart: ' . $e->getMessage());
        }
    }
}
