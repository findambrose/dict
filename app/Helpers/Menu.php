<?php
namespace App\Helpers;
trait Menu{
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

  public function registerMenu(){$response = "Welcome to our Maana. A free dictionary.\n" ;
  $response .=  "1. Create an account\n";
  $response .= "2. Get information about us\n";
  $response .=  "3. Exit";
  echo $response;}
}


 ?>
