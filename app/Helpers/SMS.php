<?php
/**
 *
 */
namespace App\Helpers;
use AfricasTalking\SDK\AfricasTalking;
class SMS {

  function sendSMS($message, $phone){

    $username = config('africastalking.username');
    $key = config('africastalking.key');
    $africasTalking = new AfricasTalking($username, $key);
    $smsService = $africasTalking->sms();

    try {
      $result = $smsService->send([
        'to'=> $phone,
        'message' => $message,
        'from' => 'Maana Free'
      ]);
    } catch (\Exception $e) {
     \Log::error("Error is : ".$e->getMessage()); 
    }
  //  echo json_encode($result);
  }
}

 ?>
