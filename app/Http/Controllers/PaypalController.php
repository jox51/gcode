<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Payment;
use Inertia\Inertia;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Utils\PaypalApiCredentialsTrait;
use App\Http\Utils\UpdateSubscriptionTrait;
use App\Mail\UserSubscribedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaypalController extends Controller {
    use PayPalApiCredentialsTrait;
    use UpdateSubscriptionTrait;

    public function __construct() {
        $this->setCredentials();
    }


    public function agreement(Request $request) {
        if (!empty($request->input('success'))) {

            $success = $request->input('success');

            if ($success && !empty($request->input('token'))) {

                $token = $request->input('token');
                $agreement = new \PayPal\Api\Agreement();

                try {

                    $agreement = $agreement->execute($token, $this->apiContext);
                } catch (\Exception $ex) {
                    exit(1);
                }

                $agreement = $agreement->toArray();
                $request->user()->agreement_id = $agreement['id'];
                $request->user()->payer_id = $agreement['payer']['payer_info']['payer_id'];
                // $request->user()->subscription_status = true;
                $request->user()->save();
                $this->updateStatus($request->user(), true);
                Mail::to($request->user()->email)->send(new UserSubscribedEmail($request->user()));

                return Inertia::location(route('picks'));
            } else {
            }
        }
    }

    public function subscribe(Request $request) {
        $agreement = new \PayPal\Api\Agreement();
        $agreement->setName('Base Agreement')
            ->setDescription('Billed on a monthly basis')
            ->setStartDate(date('c', strtotime('+1 day')));
        $planId = env('PAYPAL_PLAN_ID');

        $plan = new \PayPal\Api\Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            $agreement = $agreement->create($this->apiContext);
            $approvalUrl = $agreement->getApprovalLink();
            return Inertia::location($approvalUrl);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function getAgreement(Request $request) {
        $createdAgreement = $request->user()->agreement_id;
        $agreement =  \PayPal\Api\Agreement::get($createdAgreement, $this->apiContext);
        dd($agreement->toArray());
    }
}
