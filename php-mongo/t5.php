<?php
//phpinfo();
//exit;
function dump($a){
    echo '<pre>';
    var_dump($a);
    echo '</pre>';
}

///使用单独一个数据库
function justinsert(){
    $manager = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");

    // 直接插入数据
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert(['x' => 1, 'name'=>'菜鸟教程', 'url' => 'http://www.runoob.com']);
    $bulk->insert(['x' => 2, 'name'=>'Google', 'url' => 'http://www.google.com']);
    $bulk->insert(['x' => 3, 'name'=>'taobao', 'url' => 'http://www.taobao.com']);
    $manager->executeBulkWrite('test.sites', $bulk);

    return $manager;
}


/// 使用副本集数据库
function transactioninsert(){
    $manager = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017,127.0.0.1:27018,127.0.0.1:27019/?replicaSet=rsa");

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
            ['x' => 1, 'name'=>'菜鸟教程', 'url' => 'http://www.runoob.com']
        ];
        $model = new model("stock");
        $command = $model->insert($insert);
        $model = new model("stock");
        $command = $model->update();
        $res = $manager->executeWriteCommand('ushop',$command,[
            'session'=>$session
        ]);//var_dump($res);exit;
        //$session->abortTransaction();
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
transactioninsert();
/*
$manager = justinsert();

$filter = ['x' => ['$gt' => 1]];
$options = [
    'projection' => ['_id' => 0],
    'sort' => ['x' => -1],
];

// 查询数据
$query = new MongoDB\Driver\Query($filter, $options);
$cursor = $manager->executeQuery('test.sites', $query);

foreach ($cursor as $document) {
    $document = json_encode($document);
    $document = json_decode($document,true);
    dump($document);
}
*/

//$bulk = new MongoDB\Driver\BulkWrite();
//$bulk->delete(['x' => ['$gt'=>1]], ['limit' => 0]);   // limit 为 0 时，删除所有匹配数据
//$manager->executeBulkWrite('test.sites', $bulk);


/// 创建集合的类 ，给command使用
class model {
    protected $cmd = array();
    protected $tables = '';

    function __construct($tables) {
        $this->tables = (string)$tables;
        return $this;
    }
    function getCommand() {
        return new MongoDB\Driver\Command($this->cmd);
    }

    function insert($data){
        $this->cmd['insert'] = $this->tables;
        $this->cmd['documents'] = $data;
        return $this->getCommand();
    }
    function array2object($array)
    {
        if (is_array($array)) {
            $obj = new StdClass();
            foreach ($array as $key => $val) {
                $obj->$key = $val;
            }
        } else {
            $obj = $array;
        }
        return $obj;
    }

    function update($data=[]){
        //$this->cmd = [];
        $this->cmd = [
            'update'=>$this->tables,
            'updates'=>[
                [
                    'q'=>['x'=>1],
                    'u'=>['$set'=>['x'=>2]]
                ]
            ]
        ];
        return $this->getCommand();
    }
}