<?php

/// 使用副本集数据库
function transactioninsert(){

    $manager  = new  \MongoDB\Driver\Manager
( "mongodb://127.0.0.1:27017,127.0.0.1:27018,127.0.0.1:27019/?replicaSet=rsa" );    //mongodb必须是副本集数据库才可以使用事务，使用事务插入数据

    //mongodb必须是副本集数据库才可以使用事务，使用事务插入数据
    $ReadPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
    $session = $manager->startSession([
        'readPreference'=>$ReadPreference
    ]);
    try{
        // 显式开启事务
        $ReadConcern = new MongoDB\Driver\ReadConcern("snapshot");
        $WriteConcern = new MongoDB\Driver\WriteConcern('majority', 1000);
        $options = [
            'readConcern'=> $ReadConcern,
            'writeConcern'=> $WriteConcern,
        ];
        $session->startTransaction($options);
        $insert = [
            ['x' => 1, 'name'=>'菜鸟教程', 'value' => '5']
        ];
        /*
        $bulk  = new  MongoDB \ Driver \ BulkWrite ();

        $command = $bulk -> insert ([ 'name'  =>  1 ,  'stock'  =>  256 ]);
        $writeConcern  = new  MongoDB \ Driver \ WriteConcern ( MongoDB \ Driver \ WriteConcern :: MAJORITY ,  2000 );
        $result  =  $manager -> executeBulkWrite ( 'ushop.stock' ,  $bulk ,  $writeConcern );
        */        
        $insert = [
            ['x' => 1, 'name'=>'菜鸟教程', 'url' => 'http://www.runoob.com']
        ];
        $model = new model("stock'");
        $command = $model->insert($insert);
        $res = $manager->executeWriteCommand('ushop',$command,[
            'session'=>$session
        ]);
        $session->abortTransaction();
    }catch (\MongoDB\Driver\Exception\Exception $e){
        // 回滚事务
        $session->abortTransaction();
        dump($e->getMessage());
        exit;
    }
//// 提交事务
    $session->commitTransaction();

    return $manager;
}
var_dump(transactioninsert());
exit;
$bulk  = new  MongoDB \ Driver \ BulkWrite ();

$bulk -> insert ([ '_id'  =>  1 ,  'x'  =>  1 ]);
//$bulk -> insert ([ '_id'  =>  2 ,  'x'  =>  2 ]);

// $bulk -> update ([ 'x'  =>  2 ], [ '$set'  => [ 'x'  =>  1 ]], [ 'multi'  =>  false ,  'upsert'  =>  false ]);
// $bulk -> update ([ 'x'  =>  3 ], [ '$set'  => [ 'x'  =>  3 ]], [ 'multi'  =>  false ,  'upsert'  =>  true ]);
// $bulk -> update ([ '_id'  =>  3 ], [ '$set'  => [ 'x'  =>  3 ]], [ 'multi'  =>  false ,  'upsert'  =>  true ]);

//$bulk -> insert ([ '_id'  =>  4 ,  'x'  =>  2 ]);

//$bulk -> delete ([ 'x'  =>  1 ], [ 'limit'  =>  1 ]);
/*
$writeConcern  = new  MongoDB \ Driver \ WriteConcern ( MongoDB \ Driver \ WriteConcern :: MAJORITY ,  2000 );
$result  =  $manager -> executeBulkWrite ( 'ushop.dee' ,  $bulk ,  $writeConcern );
*/
$filter = ['x'=>1];
$options = [     'projection'  => [ '_id'  =>  0 ],
     'sort'  => [ 'x'  => - 1 ],     "modifiers"  => array(
         '$comment'    =>  "This is a query comment" ,
         '$maxTimeMS'  =>  100 ,
    ),];
$query = new  MongoDB \ Driver \ Query ( $filter ,  $options );
$cursor  =  $manager -> executeQuery ( 'ushop.dee' ,  $query );
var_dump($cursor);
/*
printf ( "Inserted %d document(s)\n" ,  $result -> getInsertedCount ());
printf ( "Matched  %d document(s)\n" ,  $result -> getMatchedCount ());
printf ( "Updated  %d document(s)\n" ,  $result -> getModifiedCount ());
printf ( "Upserted %d document(s)\n" ,  $result -> getUpsertedCount ());
printf ( "Deleted  %d document(s)\n" ,  $result -> getDeletedCount ());

foreach ( $result -> getUpsertedIds () as  $index  =>  $id ) {
     printf ( 'upsertedId[%d]: ' ,  $index );
     var_dump ( $id );
}


if ( $writeConcernError  =  $result -> getWriteConcernError ()) {
     printf ( "%s (%d): %s\n" ,  $writeConcernError -> getMessage (),  $writeConcernError -> getCode (),  var_export ( $writeConcernError -> getInfo (),  true ));
}


foreach ( $result -> getWriteErrors () as  $writeError ) {
     printf ( "Operation#%d: %s (%d)\n" ,  $writeError -> getIndex (),  $writeError -> getMessage (),  $writeError -> getCode ());
}
*/