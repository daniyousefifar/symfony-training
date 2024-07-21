<?php

namespace App\Controller;

use App\DTO\LowestPriceEnquiry;
use App\Service\Serializer\DTOSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    #[Route('/products/{id}/lowest-price', name: 'lowest-price', methods: 'POST')]
    public function lowestPrice(Request $request, int $id, DTOSerializer $serializer): Response
    {
        if ($request->headers->has('force-fail')) {
            return new JsonResponse([
                'error' => 'Promotions Engine failure message'
            ], $request->headers->get('force-fail'));
        }

        // 1. Deserialize json data into EnquiryDTO
        /** @var LowestPriceEnquiry $lowestPriceEnquiry */
        $lowestPriceEnquiry = $serializer->deserialize($request->getContent(), LowestPriceEnquiry::class, 'json');

        // 2. Pass the Enquiry into a promotions filter the appropriate promotion will be applied
        $lowestPriceEnquiry->setPrice(100);
        $lowestPriceEnquiry->setDiscountedPrice(50);
        $lowestPriceEnquiry->setPromotionId(3);
        $lowestPriceEnquiry->setPromotionName('Black Friday half price sale');

        // 3. Return the modified Enquiry
        $responseContent = $serializer->serialize($lowestPriceEnquiry, 'json');

        return new Response($responseContent, 200);
    }
}