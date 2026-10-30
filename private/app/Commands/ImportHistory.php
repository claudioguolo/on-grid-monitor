<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\History;
class ImportHistory extends BaseCommand
{
    protected $group='Energia'; protected $name='energia:import-txt';
    protected $description='Importa arquivos DDMMAAAA.txt do medidor sem duplicar.';
    public function run(array $params)
    {
        if(!$params)throw new \InvalidArgumentException('Informe os arquivos TXT');
        $records=[];
        foreach($params as $path){if(!is_file($path))throw new \InvalidArgumentException('Arquivo nao encontrado');$records=array_merge($records,History::parse(file_get_contents($path),basename($path)));}
        CLI::write(json_encode(History::merge($records)));
    }
}
