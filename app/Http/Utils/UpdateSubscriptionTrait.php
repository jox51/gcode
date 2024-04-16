<?php

namespace App\Http\Utils;

trait UpdateSubscriptionTrait {

  private function updateStatus($user, $value) {
    $user->subscription_status = $value;
    $user->save();
  }
}
