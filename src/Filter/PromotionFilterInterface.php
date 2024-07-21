<?php

namespace App\Filter;

use App\DTO\PromotionEnquiryInterface;

interface PromotionFilterInterface
{
    public function apply(PromotionEnquiryInterface $enquiry): PromotionEnquiryInterface;
}