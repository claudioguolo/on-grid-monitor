"""Test public_html/energia layout and private HTTP denial in disposable Apache/MySQL."""
import json
import os
import subprocess
import tempfile
import uuid
from pathlib import Path
from zipfile import ZipFile
import sys
root=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(root/'tools'))
from hostinger_layout import install
name='energia_layout_'+uuid.uuid4().hex[:8]
container=name.replace('_','-')
def run(*args):return subprocess.check_output(args,text=True,cwd=root).strip()
def sql(statement):return run('docker','compose','exec','-T','db','sh','-c','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -e "$1"','sh',statement)
sql(f"CREATE DATABASE {name}; GRANT ALL ON {name}.* TO 'energia'@'%';")
try:
 with tempfile.TemporaryDirectory(prefix='energia-layout-') as temp:
  directory=Path(temp)
  (directory/'config').mkdir()
  (directory/'config/software.json').write_text((root/'config/software.json').read_text())
  with ZipFile(root/'dist/coletor_energia_php-hostinger.zip') as archive:install(archive,directory/'public/energia','test')
  runtime=directory/'energia_runtime';runtime.mkdir();(runtime/'.env').write_text('CI_ENVIRONMENT = production\n')
  for folder in ['cache','logs','session','uploads']:(runtime/'writable'/folder).mkdir(parents=True)
  # Disposable fixture must be writable by the Apache uid inside the container.
  for path in [directory,*directory.rglob('*')]:os.chmod(path,0o777 if path.is_dir() else 0o644)
  run('docker','compose','run','-d','--no-deps','--name',container,'-v',f'{directory}:/var/www/html','-e','DB_NAME='+name,'-e','APP_BASE_URL=http://localhost/energia/','-e','DASHBOARD_USERNAME=test','-e','DASHBOARD_PASSWORD=test-password','app')
  run('python3','tools/wait_health.py','--container',container,'--socket-only')
  code='''$c=stream_context_create(['http'=>['ignore_errors'=>true,'header'=>'Authorization: Basic '.base64_encode('test:test-password')]]);foreach(['/energia/','/energia/api/settings','/energia/private/config/software.json','/energia/private/VERSION','/energia/private/vendor/autoload.php','/energia/private/.htaccess'] as $p){$body=file_get_contents('http://localhost'.$p,false,$c);preg_match('/\\s(\\d{3})\\s/',$http_response_header[0],$m);echo json_encode(['path'=>$p,'status'=>(int)$m[1],'body'=>$p==='/energia/api/settings'?json_decode($body,true):null])."\\n";}'''
  result=run('docker','exec',container,'php','-r',code)
  rows=[json.loads(line) for line in result.splitlines()]
  assert all(row['status']==(403 if '/private/' in row['path'] else 200) for row in rows), rows
  assert rows[1]['body']['version']
  print('Hostinger: painel/API na subpasta funcionam e arquivos internos retornam 403: OK')
finally:
 subprocess.run(['docker','rm','-f',container],capture_output=True)
 sql(f'DROP DATABASE {name}')
