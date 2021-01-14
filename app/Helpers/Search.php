<?php
namespace App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Search{

public function searchMeaning($searchTerm){
  $apiKey = config('oxford.key');
  $appId = config('oxford.app_id');
  $url = 'https://od-api.oxforddictionaries.com/api/v2/entries/en/'.$searchTerm;
  $response = Http::withHeaders(
    ['app_id' => $appId,
     'app_key' => $apiKey
    ]
    )->get($url);

  $resultObject = json_decode($response->body());
  return $resultObject;

 }
}

 ?>
