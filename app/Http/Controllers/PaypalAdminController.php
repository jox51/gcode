<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Inertia\Inertia;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Utils\PaypalApiCredentialsTrait;
use App\Http\Utils\UpdateSubscriptionTrait;
use App\Mail\SubscriptionUpdatedEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaypalAdminController extends Controller {
  use PayPalApiCredentialsTrait;
  use UpdateSubscriptionTrait;

  public function __construct() {

    $this->setCredentials();
  }


  public function createPlan() {


    $plan = new \PayPal\Api\Plan();
    $plan->setName('Pro Pick Plan')
      ->setDescription('All sports access')
      ->setType('INFINITE');

    $paymentDefinition = new \PayPal\Api\PaymentDefinition();
    $paymentDefinition->setName('Regular Payments')
      ->setType('REGULAR')
      ->setFrequency('Month')
      ->setFrequencyInterval(1)
      ->setCycles(0)
      ->setAmount(new \PayPal\Api\Currency(array('value' => 8000, 'currency' => 'USD')));

    $merchantPreferences = new \PayPal\Api\MerchantPreferences();
    $merchantPreferences->setReturnUrl(route('agreement', ['success' => 'true']))
      ->setCancelUrl(route('cancel', ['success' => 'false']))
      ->setAutoBillAmount('yes')
      ->setInitialFailAmountAction('CONTINUE')
      ->setMaxFailAttempts('0')
      ->setSetupFee(new \PayPal\Api\Currency(array('value' => 8000, 'currency' => 'USD')));


    $plan->setPaymentDefinitions(array($paymentDefinition));
    $plan->setMerchantPreferences($merchantPreferences);

    try {


      $createdPlan = $plan->create($this->apiContext);
    } catch (\Exception $ex) {
      dd($ex);
    }
    $this->activatePlan($createdPlan);
  }

  public function activatePlan($createdPlan) {


    try {
      $patch = new \PayPal\Api\Patch();
      $value = new \PayPal\Common\PayPalModel('{
                "state": "ACTIVE"
            }');
      $patch->setOp('replace')
        ->setPath('/')
        ->setValue($value);

      $patchRequest = new \PayPal\Api\PatchRequest();
      $patchRequest->addPatch($patch);
      $createdPlan->update($patchRequest, $this->apiContext);
    } catch (\Exception $ex) {
      Log::info($ex);
    }
  }

  public function showPlans() {

    $params = array('page_size' => 10, 'status' => 'ALL');

    $planList = \PayPal\Api\Plan::all($params, $this->apiContext);

    return Inertia::render('Admin', ['plans' => $planList->toArray()]);
  }

  public function main() {
    return Inertia::render('Admin');
  }

  public function webhook() {

    $bodyReceived = file_get_contents('php://input');


    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_UPPER);

    $signatureVerification = new \PayPal\Api\VerifyWebhookSignature();
    $signatureVerification->setWebhookId(env('PAYPAL_WEBHOOK_ID'));
    $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
    $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
    $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
    $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
    $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

    $webhookEvent = new \PayPal\Api\WebhookEvent();
    $webhookEvent->fromJson($bodyReceived);

    $signatureVerification->setWebhookEvent($webhookEvent);
    $request = clone $signatureVerification;

    try {
      $output = $signatureVerification->post($this->apiContext);
    } catch (\Exception $ex) {
      Log::info($ex);
      exit(1);
    }

    $verificationStatus = $output->getVerificationStatus();
    $responseArray = json_decode($request->toJSON(), true);

    $event = $responseArray['webhook_event']['event_type'];

    if ($verificationStatus === 'SUCCESS') {
      switch ($event) {

        case 'BILLING.SUBSCRIPTION.CANCELLED':

        case 'BILLING.SUBSCRIPTION.SUSPENDED':

        case 'BILLING.SUBSCRIPTION.EXPIRED':

        case 'BILLING_AGREEMENTS_AGREEMENT_CANCELLED':

          $user = User::where('payer_id', $responseArray['webhook_event']['resource']['payer']['payer_info']['payer_id'])->first();
          $this->updateStatus($user, false);
          Mail::to($user->email)->send(new SubscriptionUpdatedEmail($user));

          break;
      }
    }

    echo $verificationStatus;
    exit(0);
  }

  public function updateStatus($user, $status) {

    $user->subscription_status = $status;
    $user->save();
  }
}
