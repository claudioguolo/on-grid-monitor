<?php
// CLI only, using environment variables from the local app container.
if(PHP_SAPI!=='cli')exit(1);
$path=$argv[1]??null;if(!$path)throw new InvalidArgumentException('Informe o arquivo NDJSON');
$db=new PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME').';charset=utf8mb4',getenv('DB_USER'),getenv('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$db->beginTransaction();
$file=fopen($path,'wb');$count=0;
foreach($db->query('SELECT id,received_at,source,method,content_type,query,body,parsed FROM readings ORDER BY received_at,id') as $row){
    $record=['source_id'=>(string)$row['id']];
    foreach(['received_at','source','method','content_type','query','parsed'] as $key)$record[$key]=$row[$key];
    $record['body_base64']=base64_encode($row['body']);fwrite($file,json_encode($record,JSON_THROW_ON_ERROR)."\n");$count++;
}
fclose($file);$db->commit();echo 'Exportados: '.$count.PHP_EOL;
