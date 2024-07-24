<?php

namespace App\Filter;

use App\Entity\Promotion;
use App\DTO\PriceEnquiryInterface;

interface PriceFilterInterface extends PromotionFilterInterface
{
    public function apply(PriceEnquiryInterface $enquiry, Promotion ...$promotion): PriceEnquiryInterface;
}