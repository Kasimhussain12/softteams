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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use SoftTeams\SalePromotion\Helper\Data as SaleHelper;
use Psr\Log\LoggerInterface;

class CheckCartOnRemove implements ObserverInterface
{
    protected CartRepositoryInterface $cartRepository;
    protected CheckoutSession $checkoutSession;
    protected SaleHelper $saleHelper;
    protected LoggerInterface $logger;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CheckoutSession $checkoutSession,
        SaleHelper $saleHelper,
        LoggerInterface $logger
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->saleHelper = $saleHelper;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $quote = $this->checkoutSession->create()->getQuote();
            $cartItems = $quote->getAllVisibleItems();
            $freeProductSku = $this->saleHelper->getPromoProduct();
            $isFreeProductOnly = true;
            foreach ($cartItems as $item) {
                if ($item->getSku() != $freeProductSku) {
                    $isFreeProductOnly = false;
                    break;
                }
            }
            if ($isFreeProductOnly) {
                foreach ($cartItems as $item) {
                    $quote->removeItem($item->getItemId());
                }
                $this->cartRepository->save($quote);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception occurred while checking cart on item remove: ' . $e->getMessage());
        }
    }
}
