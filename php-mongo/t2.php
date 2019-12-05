<?php
	require_once('./phalapi-mongo/src/Lite.php');
	$config = array(
	    'mongo' => array(
	        'host' => '127.0.0.1',
	        'port' => '27017',
	        'db_name' => 'ushop',          // 数据库名
	        'username' => '',
	        'password' => '',
	        'connect_timeout_ms' => '', // 连接超时时间
	        'socket_timeout_ms' => '',
	        'type'=>'mongodb://127.0.0.1:27017,127.0.0.1:27018,127.0.0.1:27019/?replicaSet=rsa',
	        'collection_ids'=>'collection_ids',

	        'persist' => 'x',           // x 表示保持连接
	    ),

	    'wkuser' => 'wkuser',  // MongoDB 的库中的集合 collection，类似于数据库的一张表
	);
	$mongo = new PhalApi\Mongo\Lite($config['mongo']);
	try{

        $mdb = $config['mongo']["db_name"];  // MongoDB数据库
        $coll = "dee";    // MongoDB 要操作的集合，即类似于MySQL的表
        $mdbcoll = $mdb.".".$coll;		
	    //$msg = $mongo->insert($mdbcoll,['x'=>9]);//'_id'=>new \MongoDB\BSON\ObjectID('5de7af701a797513e04af5a4') ,
	    //$msg = $mongo->findAndModify($mdb, 'collection_ids',['_id'=>$coll],array('$inc' =>array('id'=>1)));var_dump($msg);exit;
	    //测试事务
        /*
        $updates=[
                [
                    'q'=>['x'=>2],
                    'u'=>['$set'=>['name'=>123456]]
                ]
            ];
	    $command[] = $mongo->getCommand(['update'=>'stock','updates'=>$updates]);
	    $command[] = $mongo->getCommand(['insert'=>'stock','documents'=>[['x'=>555,'name'=>'insert1']]]);
	    */
        $delete=[
                [
                    'q'=>['x'=>3],
                    'limit'=>0
                ]
            ];
	    $command[] = $mongo->getCommand(['delete'=>'stock','deletes'=>$delete]);
	    $msg = $mongo->transactioninsert($command);
	    var_dump($msg);exit;
	} catch (Exception $e){
		echo 'sdfs<br>';
	    $es = $e->getMessage();
	    var_dump($es);
	}