<?php
namespace App\Helpers;
class SessionManager{
  public function sessionOn($menuResponse){
   echo 'CON'.$menuResponse ;
  }
  public function sessionEnded($menuResponse){
    echo 'END'.$menuResponse ;
  }
}
?>
