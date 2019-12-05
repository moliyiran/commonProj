<?php
/*
 * For a replica set, include the replica set name and a seedlist of the members in the URI string; e.g.
 * uriString = 'mongodb://mongodb0.example.com:27017,mongodb1.example.com:27017/?replicaSet=myRepl'
 * For a sharded cluster, connect to the mongos instances; e.g.
 * uriString = 'mongodb://mongos0.example.com:27017,mongos1.example.com:27017/'
 */
$uriString = "mongodb://127.0.0.1:27017,127.0.0.1:27018,127.0.0.1:27019/?replicaSet=rsa";
$client = new  \MongoDB\Driver\Manager
( "mongodb://127.0.0.1:27017,127.0.0.1:27018,127.0.0.1:27019/?replicaSet=rsa" ); 

// Prerequisite: Create collections. CRUD operations in transactions must be on existing collections.
/*
$client->selectCollection(
    'ushop',
    'stock',
    [
        'writeConcern' => new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000),
    ]
)->insertOne(['name'=>'aaa', 'stock' => '5']);

$client->selectCollection(
    'ushop',
    'stock',
    [
        'writeConcern' => new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000),
    ]
)->insertOne(['name'=>'aaah', 'stock' => '5']);
*/

// Step 1: Define the callback that specifies the sequence of operations to perform inside the transactions.

$callback = function (\MongoDB\Driver\Session $session) use ($client) {
    $client
        ->selectCollection('ushop', 'stock')
        ->insertOne(['name'=>'a1', 'stock' => '1'], ['session' => $session]);

    $client
        ->selectCollection('ushop', 'stock')
        ->insertOne(['name'=>'b1', 'stock' => '1'], ['session' => $session]);
};

// Step 2: Start a client session.

$session = $client->startSession();

// Step 3: Use with_transaction to start a transaction, execute the callback, and commit (or abort on error).

$transactionOptions = [
    'readConcern' => new \MongoDB\Driver\ReadConcern(\MongoDB\Driver\ReadConcern::LOCAL),
    'writeConcern' => new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000),
    'readPreference' => new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY),
];

\MongoDB\with_transaction($session, $callback, $transactionOptions);