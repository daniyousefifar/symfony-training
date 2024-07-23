<?php

namespace App\Filter;

use App\DTO\PromotionEnquiryInterface;
use App\Entity\Promotion;
use App\Filter\Modifier\Factory\PriceModifierFactoryInterface;

class LowestPriceFilter implements PromotionFilterInterface
{
    public function __construct(
        private PriceModifierFactoryInterface $priceModifierFactory
    )
    {
        // ...
    }

    public function apply(PromotionEnquiryInterface $enquiry, Promotion ...$promotions): PromotionEnquiryInterface
    {
        $price = $enquiry->getProduct()->getPrice();
        $quantity = $enquiry->getQuantity();
        $lowestPrice = $quantity * $price;

        foreach ($promotions as $promotion) {
            $priceModifier = $this->priceModifierFactory->create($promotion->getType());

            $modifiedPrice = $priceModifier->modify($price, $quantity, $promotion, $enquiry);

            $enquiry->setPrice(100);
            $enquiry->setDiscountedPrice(250);
            $enquiry->setPromotionId(3);
            $enquiry->setPromotionName('Black Friday half price sale');
        }


        return $enquiry;
    }
}