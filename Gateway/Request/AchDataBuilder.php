<?php
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Request;

use Braintree\PaymentMethod;
use Braintree\Result\UsBankAccountVerification;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AchDataBuilder
 */
class AchDataBuilder implements BuilderInterface
{
    const OPTIONS = 'options';
    const VERIFICATION_METHOD = 'usBankAccountVerificationMethod';
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * AchDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $customerId = '218686684';

        $payment = $paymentDO->getPayment();
        $nonce = $payment->getAdditionalInformation(
            DataAssignObserver::PAYMENT_METHOD_NONCE
        );

        $result = PaymentMethod::create([
            'customerId' => $customerId,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'usBankAccountVerificationMethod' => 'network_check'
            ]
        ]);

        if ($result->success) {
            return [
                'paymentMethodNonce' => null,
                'paymentMethodToken' => $result->paymentMethod->token
            ];
        }

        throw new LocalizedException(__('Failed to create payment token.'));
    }
}
