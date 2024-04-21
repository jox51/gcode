<?php

namespace App\Http\Utils;

trait PaypalApiCredentialsTrait {

  private $apiContext;

  public function setCredentials() {


    $this->apiContext =  new \PayPal\Rest\ApiContext(
      new \PayPal\Auth\OAuthTokenCredential(
        env('PAYPAL_LIVE_CLIENT_ID'),     // ClientID
        env('PAYPAL_LIVE_CLIENT_SECRET')      // ClientSecre
      )
    );

    $this->apiContext->setConfig(
      array(
        'mode' => 'LIVE',
        'log.LogEnabled' => true,
        'log.FileName' => '../PayPal.log',
        'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
      )
    );
  }
}
