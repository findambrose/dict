<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use AfricasTalking\SDK\AfricasTalking;

class DictionaryController extends Controller
{
    //
    function session()
    {
      // code...
      $text = request('text');
      $phone = request('phone');
      $networkCode =  request('networkCode');
      $sessionId =  request('sessionId');
      $serviceCode =  request('serviceCode');
      $level =  explode("*", $text);
      $levelCount = count($level);
      $users = new User();

//first is to check if user is registered or not
 if ($users->where('phone', $phone)->exists()) {
   $this->handleReturnUsers($level, $levelCount, $users, $phone);
 }else {
   $this->handleNewUsers($level, $levelCount, $phone, $users);
 }
     //  header("content-type: text/string");
} //end of mainsession Function


function sendSMS($message, $phone){

  $username = 'sandbox';
  $key = '1c69c9a7a5bf058bcaf59a5f695a1e915a0351e6d5c55e8edc398d81084dab6e';
  $africasTalking = new AfricasTalking($username, $key);
  $smsService = $africasTalking->sms();
  $result = $smsService->send([
    'to'=> $phone,
    'message' => $message,
    'from' => 'Maana Free'
  ]);
  return $result;
}
//start of support functions
    public function handleNewUsers($level, $levelCount, $phone, $users){
      //sign them up
      //take display services screen
      if ($levelCount == 1 && $level[0] == 1 ) {
        $response = "Please enter your full name and pin, \nseparated by a comma";
         echo $response;
         return;
      }
      if ($levelCount == 1 && $level[0] == 2 ) { //a switch better, to be done

        $response = "You will receive an SMS with more info about us.";
        //end connection
        //send sms logic


        try {
          $result =  $this->sendSMS("We are a totally free USSD dictionary. Meaning that \n
          you do not need any internet connection to look a word'/s' \n
          meaning.", $phone);
        } catch (\Exception $e) {
          echo "AfricasTalking Error: ". $e->message;
        }


        echo $response;

      }

      if ($levelCount == 1 && $level[0] == 3 ) { //a switch better, to be done
        $response = "Bye. Thank you for visiting";
         echo $response;
         return;
      }

      if ($levelCount == 2 && isset($level[1]) ) {
        //save user to db
        $userDetails = explode("," , $level[1]);

       $this->register($userDetails[0], $phone, $userDetails[1], $users);
       return;
      }

      $response = "Welcome to our Maana. A free dictionary.\n" ;
      $response .=  "1. Create an account\n";
      $response .= "2. Get information about us";
      $response .=  "3. Exit";
      echo $response;

    }

    public function handleReturnUsers($level, $levelCount, $users, $phoneNumber){
      //display mainMenu
      //1. Explode all text to an array
      //2. Grab the last element
      //3. Add * to it to make new text
      //4. Make new level



      if ($levelCount == 1 && $level[0] == 1) {
        echo "Please enter your PIN to continue";
        return;
      }

      if ($levelCount == 2 && $level[0] == 1) {
        //get user input
        $userInput = $level[1];
        // login user
        $this->login($userInput, $users, $phoneNumber);
        return;

      }

      if ($levelCount == 3 && isset($level[2])) {
        if ($level[2] == 2) {
          // End session
          echo "Bye. Thank you for using the app.";
        } else {
          // Search for meaning
          //1. Get search word
          $term = $level[2];
          //2. Make api request usign the word
          // TODO:

          echo "Response from Api call";
          echo $term;
        }

        return;

      }

      if ($levelCount == 1 && $level[0] == 2) {
        //end connection. exit option selected

      return;
      }
      $this->mainMenu();

      //login
      //take to display services screen
    }
    public function mainMenu(){ //for registered users
      $response = "Welcome to our Maana. A free dictionary.\n" ;
      $response .=  "1. Login\n";
      $response .=  "2. Exit";
      echo $response;
    }
    public function servicesMenu(){
      $response = "Welcome to our Maana. A free dictionary.\n" ;
      $response .=  "1. Enter Word To Search\n";
      $response .=  "2. Exit";
      echo $response;
    }
    public function login($usersEnteredPin, $users, $phoneNumber){
      //login user with pin

      $usersActualPin = $users->where('phone', $phoneNumber)->first()->pin;

      if ($usersActualPin == $usersEnteredPin) {
        // login user in. i.e show services menu
        $this->servicesMenu();
        return 'success';
      }
      else {
        //end connection
        echo 'END Wrong Pin, try again';
      }

    }
    public function register($name, $phoneNumber, $pin, $users){

        //register user then take to services menu
        $users->name = $name;
        $users->pin = $pin;
        $users->phone = $phoneNumber;
        $users->save();

        try {
          $result =  $this->sendSMS("Hello, ".$name. ". Your Account has been successfully created. You will recieve free promotional
          as well as educative messages from us occassionally.", $phoneNumber);
        } catch (\Exception $e) {
          echo "AfricasTalking Error: ". $e->getMessage();
        }

      $this->servicesMenu();
    }



    public function sessionOn($menuResponse){
     echo 'CON'.$menuResponse ;
    }
    public function sessionEnded($menuResponse){
      echo 'END'.$menuResponse ;
    }


}
