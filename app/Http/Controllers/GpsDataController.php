<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GpsDataController extends Controller
{
    public function gpsData(Request $request)
    {
        $gpsId = $request->query('gpsId');
        // //이거를 이제 mongoDB에 보내서 요청
        // return $match_gps_id = $random_match_post->gps_id;

        //Node에서 GPS_data_id를 받아와서 활동에 저장
        $response = Http::get(env('NODE_SERVER_URL') . "/api/gpsdata/$gpsId");

        $gpsData = json_decode($response->getBody(), true);

        if ($gpsData) {
            return response([
                'gpsData' => $gpsData
            ], 200);
        } else {
            return response('', 204);
        }
    }

    public function gpsDataCheck(Request $request)
    {
        $gpsId = $request->query('gpsId');

        $response = Http::get(env('NODE_SERVER_URL') . "/api/gpsdata/$gpsId/check");
        $gpsData = json_decode($response->getBody(), true);

        return $gpsData;
    }
}
