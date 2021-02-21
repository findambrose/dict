<?php

namespace App\Http\Controllers;
use App\Models\User;
use AfricasTalking\SDK\AfricasTalking;
use App\Helpers\SMS;
use App\Helpers\Search;
use App\Helpers\SessionManager as Sesh;
use App\Helpers\Menu as Menu;

class DictionaryController extends Controller
{
public $menu;
 public $sessionManager;

  public function __construct(){
    $this->menu = new Menu();
    $this->sessionManager = new Sesh();
  }

    function session()
    {
      // code...
      $text = request('text');
      $phone = request('phoneNumber');
      $networkCode =  request('networkCode');
      $sessionId =  request('sessionId');
      $serviceCode =  request('serviceCode');
      $level =  explode("*", $text);
      $levelCount = count($level);
      $users = new User();




//first is to check if user is registered or not
 if ($users->where('phone', $phone)->exists()) {
   $this->handleReturnUsers($level, $text, $levelCount, $users, $phone);
 }else {
   $this->handleNewUsers($level, $text, $levelCount, $phone, $users);
 }
     //  header("content-type: text/string");
} //end of mainsession Function



//start of support functions
    public function handleNewUsers($level, $text, $levelCount, $phone, $users){
      //sign them up
      //take display services screen

      if ($text == "") {
        $this->menu->registerMenu();
      }
        switch ($levelCount) {

          case 1:
          if ($level[0] == 1 ) {
            $response = "Please enter your full name and pin, \nseparated by a comma";
            $this->sessionManager->sessionOn($response);
          }
          if ($level[0] == 2 ) {
            $response = "You will receive an SMS with more info about us.";
            //end connection
            //send sms logic
              $sms = new SMS();
              $sms->sendSMS("We are a totally free USSD dictionary. Meaning that \n
              you do not need any internet connection to look a word'/s' \n
              meaning.", $phone);
              $this->sessionManager->sessionEnded($response);
          }
          if($level[0] == 3 ){

                $response = "Bye. Thank you for visiting";
                $this->sessionManager->sessionEnded($response);
              }
            break;
          case 2:
          if (isset($level[1]) ) {
            //save user to db
            $userDetails = explode("," , $level[1]);

           $this->register($userDetails[0], $phone, $userDetails[1], $users);
           return;

          }
            break;
          default:
            // code...
            break;
        }

    }

    public function handleReturnUsers($level, $text, $levelCount, $users, $phoneNumber){


      if ($text == "") {
        $this->menu->mainMenu();
      }
      switch($levelCount){
        case 1:
        if ($level[0] == 1) {
          $this->sessionManager->sessionOn("Please enter your PIN to continue");
        }else if ($level[0] == 2) {

            //end connection. exit option selected
            $this->sessionManager->sessionEnded("Bye.Thank you for using our app");

          return;
          }
          break;
        case 2:
          if ($level[0] == 1) {
            //get user input
            $userInput = $level[1];
            // login user
            $this->login($userInput, $users, $phoneNumber);
            return;
          }
            break;
        case 3:
        if (isset($level[2])) {
            if ($level[2] == 2) {
              // End session
              $this->sessionManager->sessionEnded("Bye. Thank you for using the app.");

            } else {
              // Search for meaning
              //1. Get search word
              $term = $level[2];
              //2. Make api request usign the word
              $this->getMeaning($term);
            }
          }
          break;
          //End
         }


}


    public function login($usersEnteredPin, $users, $phoneNumber){
      //login user with pin
      $usersActualPin = $users->where('phone', $phoneNumber)->first()->pin;
      if ($usersActualPin == $usersEnteredPin) {
        // login user in. i.e show services menu
        $this->menu->servicesMenu();
        return 'success';
      }
      else {
        //end connection
        $this->sessionManager->sessionEnded("END Wrong Pin, try again");
      }

    }
    public function register($name, $phoneNumber, $pin, $users){
        //register user then take to services menu
        $users->name = $name;
        $users->pin = $pin;
        $users->phone = $phoneNumber;
        $users->save();


        echo "CON User saved";
          $sms = new SMS();
          $sms->sendSMS("Hello, ".$name. ". Your Account has been successfully created. You will recieve free promotional
          as well as educative messages from us occassionally.", $phoneNumber);

      $this->menu->servicesMenu();
    }
    function getMeaning($searchTerm){
     //Get word meaning using Oxford dixtionary api
     $searchInstance = new Search();
     $resultObject = $searchInstance->searchMeaning($searchTerm);

      $myResults =  $resultObject->results;
      $entriesObj =  $myResults[0]->lexicalEntries[0]->entries[0];
      $pronounciation = $entriesObj->pronunciations[0]->phoneticSpelling;
      $meaning = $entriesObj->senses[0]->definitions[0];
      $example = $entriesObj->senses[0]->examples[0]->text;
      if (!empty($entriesObj->senses[0]->synonyms)) {
        $synonymsListofObjects = $entriesObj->senses[0]->synonyms;
      }
      $synonymsString = "";
      if (!empty($synonymsListofObjects)) {
        $counter = 0;
        foreach ($synonymsListofObjects as $value) {
          $arrayCount = count($synonymsListofObjects);

          if ($arrayCount - 1 > $counter) {
            // Append a full stop on the last value
            $synonymsString .= $value->text.", ";
          }
          if (count($synonymsListofObjects) == ++$counter) {
            // Append a full stop on the last value
            $synonymsString .= $value->text.".";
          }
        }
      }
      else {
        $synonymsString = "No synonyms found for the word.";

      }

      $responseToUser =  "1. Meaning: ".ucfirst($meaning).". \n";
      $responseToUser .= "2. Pronounciation: ".$pronounciation.". \n";
      $responseToUser .= "3. Synonyms: ".ucfirst($synonymsString)." \n";
      $responseToUser .= "4. Example: ".ucfirst($example).".";

      $this->sessionManager->sessionOn($responseToUser);
    }
}
