<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use \Curl\Curl;

class queueController extends Controller
{
    //
    function index(){
        $curl = new Curl();

        $curl->get('http://train.rd6/?start=2019-11-10T10:11:11&end=2019-11-10T10:12:00&from=0');

        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            echo 'Response:' . "\n";
            
            $data = json_decode($curl->response,true);
            // $aaa = stripcslashes(json_encode($data['hits']['hits'][0]['_source']));
            // $bbb = json_decode($aaa,true);
            // dd($bbb);
            // dd(array_splice($data['hits']['hits'],0,10));
            
            foreach($data['hits']['hits'] as $i => $value){
                $data['hits']['hits'][$i]['_source'] = stripcslashes(json_encode($value['_source']));
                $data['hits']['hits'][$i]['sort'] = stripcslashes(json_encode($value['sort']));
            }
            
            $count = 0;
            dd($data['hits']['hits']);
            while($count <= 8000){
                echo $count;
                $item = array_slice($data['hits']['hits'],$count,2000);
                
                DB::table('queues')->insert($item);

                $count += 2000;
                
            }

            // dd($data['hits']['hits']);
            
            // $collection = collect($data['hits']['hits']);   //塞入collection 準備分頁
            
            // $count = $collection->count();

            
            
            // $page = 1;           
            // $perPage = 250;
            // $totalPage = (int)ceil($count/$perPage);
            
            // while($page <= $totalPage){
            //     echo "Page:$page \n";
            //     $chunks = $collection->forPage($page,$perPage);  //分頁 每頁250筆資料

            //     foreach($chunks as $item){
            //         queueTest::create($item);
            //     }

            //     $page += 1;
            // }
            
            // dd(array_splice($data['hits']['hits'],0,10));
        }

        $curl->close();
    }
}
