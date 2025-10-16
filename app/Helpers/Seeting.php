<?php

namespace App\Helpers;

class Seeting {
    public function address($from_lat, $from_lng)
    {
        $ch = curl_init();
        $url =  "https://maps.googleapis.com/maps/api/distancematrix/json";
        $dataArray = [
            'units' => "km",
            'origins' => $from_lat . "," . $from_lng,
            'departure_time=now',
            'key' => 'AIzaSyB-uADMlF6PqwccIr3q6Vpyl0wJgJNsxOM'
        ];
        $data = http_build_query($dataArray);
        $getUrl = $url . "?" . $data;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80);
        $response = curl_exec($ch);
        $map = json_decode($response);
        dd($response,$from_lat,$from_lng);

        $origin_address = $map->origin_addresses[0];
         dd($from_lat);
        // $vehicles = fractal($vehicles, new VehiclesTransformer(['km' => $km]));
        $data = array();
        $data['origin_address'] = $origin_address;
        return $data;
    }

}
