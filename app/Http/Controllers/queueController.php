<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use \Curl\Curl;
use App\Jobs\QueueJob;


class queueController extends Controller
{
    //
    function index(){
        $curl = new Curl();

        $date = date("Y-m-d");

        $hour = date("H");
        
        $min = date("i");

        if ($hour == "00" && $min == "00") {
            $hour = "23";
            $min = "59";
            $date = new DateTime('now');
            date_modify($date, '-1 day');
            $date = $date->format('Y-m-d');
        } elseif ($min == "00") {
            $hour = substr(($hour-1)+100,1,2);
            $min = 59;
        } else {
            $min = substr(($min-1)+100,1,2);
        }     

        $url = "http://train.rd6/?start={$date}T{$hour}:{$min}:00&end={$date}T{$hour}:{$min}:59&from=10000";
        echo $url. "\n";
        
        $curl->get($url);

        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            echo 'Response:' . "\n";
            echo "起始筆數: 0 \n";

            $data = json_decode($curl->response,true);

            $array_key = array_keys($data);
            $value = $data['hits']['total']['value'];

            if ($array_key[0] == "error" || $value == 0) {
                $this->error('發生錯誤或無資料!');
                die();
            }

            foreach($data['hits']['hits'] as $i => $value){
                $data['hits']['hits'][$i]['_source'] = stripcslashes(json_encode($value['_source']));
                $data['hits']['hits'][$i]['sort'] = stripcslashes(json_encode($value['sort']));
            }

            $arrayLength = count($data['hits']['hits']);
            echo "資料量: $arrayLength \n";
            

            $items = array_chunk($data['hits']['hits'],1000);
            foreach($items as $item){
                queueJob::dispatch($item);
            }
                
                // DB::table('queues')->insert($this->item);
        }
        $curl->close();

    }
}
