<?php

namespace App\Controller;
use App\Entity\Coupon;
use App\Entity\Product;
use App\Entity\Tax;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{

    /**
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('calculate-price', name: 'calculate-price')]
    public function calculatePrice(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        if (!$request->isMethod('post')) {
            return $this->json(['status'=> '400', 'error' => 'not supported request type']);
        }
        $bodyData = $request->toArray();
        $errorsValidateTaxNumber = $validator->validate((new Tax())->setNumber($bodyData['taxNumber'] ?? null));
        if (count($errorsValidateTaxNumber) > 0) {
            return $this->json(['status'=> '400', 'errors' => (string) $errorsValidateTaxNumber]);
        }
        $entityManager = $doctrine->getManager();
        $coupon = null;
        if (isset($bodyData['couponCode'])) {
            $errorsValidateCouponCode = $validator->validate((new Coupon())->setCode($bodyData['couponCode'] ?? null));
            if (count($errorsValidateCouponCode) > 0) {
                return $this->json(['status' => '400', 'errors' => (string)$errorsValidateCouponCode]);
            }
            $coupon = $entityManager->getRepository(Coupon::class)->findOneBy(['code' => $bodyData['couponCode']]);
            if (is_null($coupon)) {
                return $this->json(['status'=> '400', 'message' => 'coupon with code ' . $bodyData['couponCode'] . ' not found']);
            }
        }
        if (!isset($bodyData['product'])) {
            return $this->json(['status'=> '400', 'error' => 'product field is required']);
        }

        $product = $entityManager->getRepository(Product::class)->find($bodyData['product']);
        if (is_null($product)) {
            return $this->json(['status'=> '400', 'message' => 'product with id ' . $bodyData['product'] . ' not found']);
        }

        $tax = $entityManager->getRepository(Tax::class)->findOneBy(['number'=> $bodyData['taxNumber']]);
        if (is_null($tax)) {
            return $this->json(['status'=> '400', 'message' => 'tax with number ' . $bodyData['taxNumber'] . ' not found']);
        }

        return $this->json(['status'=> '200', 'price' => $product->calculatePriceWithTaxAndCoupon($tax->getValue(), !is_null($coupon) ? $coupon->getValue() : null, !is_null($coupon) ? $coupon->getType(): null)]);
    }
}