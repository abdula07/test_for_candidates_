<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\Payment;
use App\Entity\PaymentDetails;
use App\Entity\Product;
use App\Entity\Tax;
use App\Helpers\TextHelper;
use App\PaymentProcessor\PaymentProcessors;
use App\PaymentProcessor\StripePaymentProcessor;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends AbstractController
{
    /**
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('purchase', name: 'purchase')]
   public function purchase(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse {
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
                return $this->json(['status'=> '400', 'error' => 'coupon with code ' . $bodyData['couponCode'] . ' not found']);
            }
        }

        if (!isset($bodyData['product'])) {
            return $this->json(['status'=> '400', 'error' => 'product field is required']);
        }
        if (!isset($bodyData['paymentProcessor'])) {
            return $this->json(['status'=> '400', 'error' => 'paymentProcessor field is required']);
        }

        $product = $entityManager->getRepository(Product::class)->find($bodyData['product']);
        if (is_null($product)) {
            return $this->json(['status'=> '400', 'error' => 'product with id ' . $bodyData['product'] . ' not found']);
        }

        $tax = $entityManager->getRepository(Tax::class)->findOneBy(['number'=> $bodyData['taxNumber']]);
        if (is_null($tax)) {
            return $this->json(['status'=> '400', 'error' => 'tax with number ' . $bodyData['taxNumber'] . ' not found']);
        }


        if (!in_array($bodyData['paymentProcessor'], PaymentProcessors::LIST)) {
            return $this->json(['status' => '400', 'error' => 'paymentProcessor not supported payment type']);
        }
        $resultPay = false;
        switch ($bodyData['paymentProcessor']) {
            case PaymentProcessors::STRIPE:
                $stripePaymentProcessor = new StripePaymentProcessor();
                $resultPay = $stripePaymentProcessor->processPayment($product->calculatePriceWithTaxAndCoupon($tax->getValue(), !is_null($coupon) ? $coupon->getValue() : null, !is_null($coupon) ? $coupon->getType(): null));
                break;
        }
        if (!$resultPay) {
            return $this->json(['status' => '400', 'error' => 'payment error']);
        }
        $payment = new Payment();
        if ($coupon) {
            $payment->setCouponId($coupon->getId());
        }
        $payment->setPaymentProcessor($bodyData['paymentProcessor']);
        $payment->setTaxId($tax->getId());
        $payment->setProductId($product->getId());
        $payment->setTxId(TextHelper::UniqStringId());
        $entityManager->persist($payment);
        $entityManager->flush();

        if (!$payment->getId()) {
            return $this->json(['status' => '400', 'error' => 'payment creation error']);
        }

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setProductPrice($product->getPrice());
        $paymentDetails->setTaxValue($tax->getValue());
        $paymentDetails->setPaymentId($payment->getId());
        $entityManager->persist($paymentDetails);
        $entityManager->flush();

        if (!$payment->getId()) {
            return $this->json(['status' => '400', 'error' => 'payment creation error']);
        }
        return $this->json(['status' => '200', 'message' => 'payment was successful']);
   }
}