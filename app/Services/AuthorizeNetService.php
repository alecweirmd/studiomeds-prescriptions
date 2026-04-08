<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use DateTime;

class AuthorizeNetService {

    protected string $loginId;
    protected string $transactionKey;
    protected string $environment;

    public function __construct() {
        $this->loginId = config('services.authorize.login_id');
        $this->transactionKey = config('services.authorize.transaction_key');

        $this->environment = config('services.authorize.env') === 'production' ? \net\authorize\api\constants\ANetEnvironment::PRODUCTION : \net\authorize\api\constants\ANetEnvironment::SANDBOX;
    }

    /**
     * Entry point used by controller.
     * Creates a customer profile, payment profile, then subscription.
     */
    public function createSubscriptionFromRequest($user, $request) {
        // Raw card data from your blade <input>
        $card = [
            'number' => preg_replace('/\s+/', '', $request->card_number),
            'exp_month' => $request->exp_month,
            'exp_year' => $request->exp_year,
            'cvv' => $request->cvv,
        ];

        // 1️⃣ Create customer + payment profile
        $profileIds = $this->createCustomerProfile($user, $card);

        // 2️⃣ Create ARB subscription using profile
        return $this->createRecurringSubscriptionUsingProfile(
                        $user,
                        $profileIds['customerProfileId'],
                        $profileIds['paymentProfileId']
                );
    }

    /**
     * Step 1: Create Customer Profile + Payment Profile
     */
    public function createCustomerProfile($user, $card) {
        $merchantAuth = new AnetAPI\MerchantAuthenticationType();
        $merchantAuth->setName($this->loginId);
        $merchantAuth->setTransactionKey($this->transactionKey);

        /** ------------------------------
         * Fix EXPIRATION DATE formatting
         * ------------------------------ */
        $expMonth = str_pad($card['exp_month'], 2, '0', STR_PAD_LEFT);
        $expYear = strlen($card['exp_year']) === 2 ? "20" . $card['exp_year'] : $card['exp_year'];

        if (!preg_match('/^\d{4}-\d{2}$/', "$expYear-$expMonth")) {
            throw new Exception("Invalid expiration date format: $expYear-$expMonth");
        }

        // Card object
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($card['number']);
        $creditCard->setExpirationDate("$expYear-$expMonth");
        $creditCard->setCardCode($card['cvv']);

        // Wrap in payment object
        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);

        // Billing info
        $billTo = new AnetAPI\CustomerAddressType();
        $billTo->setFirstName($user->first_name);
        $billTo->setLastName($user->last_name);
        $billTo->setAddress($user->street_address);
        $billTo->setCity($user->city);
        $billTo->setState($user->state);
        $billTo->setZip($user->zip);
        $billTo->setCountry("USA");

        // Payment profile
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($payment);
        $paymentProfile->setCustomerType("individual");

        // Customer profile
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription("Artist {$user->id}");
        $customerProfile->setMerchantCustomerId("user_" . $user->id);
        $customerProfile->setEmail($user->email);
        $customerProfile->setPaymentProfiles([$paymentProfile]);

        // API Request
        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setProfile($customerProfile);
        $request->setValidationMode("none");

        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        // Validate response
        if (!$response || $response->getMessages()->getResultCode() !== "Ok") {
            $messages = $response?->getMessages()?->getMessage();
            $msg = ($messages && count($messages) > 0) ? $messages[0]->getText() : 'No response from payment gateway.';
            Log::error("AuthorizeNet createCustomerProfile error: $msg");
            throw new Exception("CreateCustomerProfile Error: $msg");
        }


        return [
            'customerProfileId' => $response->getCustomerProfileId(),
            'paymentProfileId' => $response->getCustomerPaymentProfileIdList()[0]
        ];
    }

    /**
     * Step 2: Create ARB monthly subscription
     */
    public function createRecurringSubscriptionUsingProfile($user, $customerProfileId, $paymentProfileId) {
        $merchantAuth = new AnetAPI\MerchantAuthenticationType();
        $merchantAuth->setName($this->loginId);
        $merchantAuth->setTransactionKey($this->transactionKey);

        // Monthly interval
        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength(1);
        $interval->setUnit("months");

        // Payment schedule
        $schedule = new AnetAPI\PaymentScheduleType();
        $schedule->setInterval($interval);
        $schedule->setStartDate(new \DateTime());
        $schedule->setTotalOccurrences(9999);

        // Subscription
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName("Monthly Artist Subscription");
        $subscription->setAmount(95.00);
        $subscription->setPaymentSchedule($schedule);

        // Link customer profile
        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($customerProfileId);
        $profile->setCustomerPaymentProfileId($paymentProfileId);

        $subscription->setProfile($profile);

        // API Request
        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setSubscription($subscription);

        // Authorize.Net sometimes needs 1s to finish the payment profile creation
        sleep(5);

        $controller = new AnetController\ARBCreateSubscriptionController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        if (!$response || $response->getMessages()->getResultCode() !== "Ok") {
            $messages = $response?->getMessages()?->getMessage();
            $msg = ($messages && count($messages) > 0) ? $messages[0]->getText() : 'No response from payment gateway.';
            Log::error("AuthorizeNet createSubscription error: $msg");
            throw new Exception("Subscription Error: $msg");
        }

        return $response->getSubscriptionId();
    }

    public function chargeOneTime($number, $expMonth, $expYear, $cvc, $amount) {
        try {
            if (empty($this->loginId) || empty($this->transactionKey)) {
                Log::error("AuthorizeNet chargeOneTime: credentials not configured.");
                return ['success' => false, 'message' => 'Payment gateway is not configured. Please contact support.'];
            }

            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
            $merchantAuthentication->setName($this->loginId);
            $merchantAuthentication->setTransactionKey($this->transactionKey);

            $expMonth = str_pad($expMonth, 2, '0', STR_PAD_LEFT);
            $expYear  = strlen($expYear) === 2 ? '20' . $expYear : $expYear;
            $expirationDate = $expYear . '-' . $expMonth;

            Log::info("AuthorizeNet chargeOneTime request", [
                'card_last4'      => substr($number, -4),
                'expiration_date' => $expirationDate,
                'amount'          => $amount,
                'environment'     => $this->environment,
            ]);

            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber($number);
            $creditCard->setExpirationDate($expirationDate);
            $creditCard->setCardCode($cvc);

            $paymentType = new AnetAPI\PaymentType();
            $paymentType->setCreditCard($creditCard);

            $transactionRequest = new AnetAPI\TransactionRequestType();
            $transactionRequest->setTransactionType("authCaptureTransaction");
            $transactionRequest->setAmount($amount);
            $transactionRequest->setPayment($paymentType);

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setTransactionRequest($transactionRequest);

            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->environment);

            $transactionResponse = $response?->getTransactionResponse();

            if (
                $response !== null &&
                $response->getMessages()->getResultCode() == "Ok" &&
                $transactionResponse !== null &&
                $transactionResponse->getResponseCode() == "1"
            ) {
                return [
                    'success'        => true,
                    'transaction_id' => $transactionResponse->getTransId(),
                ];
            }

            // Extract the clearest available error message
            $errors = $transactionResponse?->getErrors();
            if ($errors && count($errors) > 0) {
                $errorCode = $errors[0]->getErrorCode();
                $errorMsg  = $errors[0]->getErrorText();
            } else {
                $messages  = $response?->getMessages()?->getMessage();
                $errorCode = ($messages && count($messages) > 0) ? $messages[0]->getCode() : 'unknown';
                $errorMsg  = ($messages && count($messages) > 0) ? $messages[0]->getText() : 'No response from payment gateway.';
            }

            Log::error("AuthorizeNet chargeOneTime failed", [
                'error_code'      => $errorCode,
                'error_message'   => $errorMsg,
                'response_code'   => $transactionResponse?->getResponseCode(),
                'result_code'     => $response?->getMessages()?->getResultCode(),
            ]);

            return [
                'success' => false,
                'message' => $errorMsg,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
