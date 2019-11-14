<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use \Curl\Curl;
use Illuminate\Support\Collection;
use App\Jobs\queueJob;

class queueTest2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:queueTest2 {num}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $item;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        DB::disconnect('queue');
        $num = $this->argument('num');
        $curl = new Curl();
        

        $curl->get("http://train.rd6/?start=2019-11-10T10:11:11&end=2019-11-10T10:12:00&from={$num}");

        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            echo 'Response:' . "\n";
            echo "起始筆數: {$num} \n";

            $data = json_decode($curl->response,true);
            // $aaa = stripcslashes(json_encode($data['hits']['hits'][0]['_source']));
            // $bbb = json_decode($aaa,true);
            // dd($bbb);
            foreach($data['hits']['hits'] as $i => $value){
                $data['hits']['hits'][$i]['_source'] = stripcslashes(json_encode($value['_source']));
                $data['hits']['hits'][$i]['sort'] = stripcslashes(json_encode($value['sort']));
            }
            // 
            $arrayLength = count($data['hits']['hits']);
            echo "資料量: $arrayLength \n";

            // collect($data['hits']['hits'])->chunk(500)->each(function ($chunk) { 
            //     DB::transaction(function () use ($chunk) {
            //         DB::table('queues')->insert($chunk->toArray());
            //     });
            // });

            $count = 0;

            while($count <= 8000){
                echo "$count \n";
                $this->item = array_slice($data['hits']['hits'],$count,2000);

                queueJob::dispatch($this->item);
              
                // DB::beginTransaction();
                
                // DB::table('queues')->insert($this->item);

                // DB::commit();

                $count += 2000;
                
            }
            
        }

        $curl->close();
        
        if($num == 90000){
            
            die();
        }

        $num += 10000;
        sleep(3);
        $this->call('command:queueTest2', [
            'num' => $num
        ]);
    
    }
}
