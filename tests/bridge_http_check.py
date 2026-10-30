"""Exercise cloud receiver using an isolated database and test credentials."""
import subprocess,uuid
from pathlib import Path
root=Path(__file__).resolve().parents[1]
name='energia_bridge_'+uuid.uuid4().hex[:8];container=name.replace('_','-')
def run(*args):
 result=subprocess.run(args,cwd=root,text=True,stdout=subprocess.PIPE)
 if result.returncode:print(result.stdout,flush=True)
 result.check_returncode()
 return result.stdout.strip()
def sql(s):return run('docker','compose','exec','-T','db','sh','-c','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -e "$1"','sh',s)
sql(f"CREATE DATABASE {name}; GRANT ALL ON {name}.* TO 'energia'@'%';")
try:
 run('docker','compose','run','-d','--no-deps','--name',container,'-e','DB_NAME='+name,'-e','APP_BASE_URL=http://localhost/','-e','CI_ENVIRONMENT=production','-e','DASHBOARD_USERNAME=test','-e','DASHBOARD_PASSWORD=test-only-password','-e','METER_TOKEN=test-only-token','app')
 run('python3','tools/wait_health.py','--container',container)
 run('docker','cp',str(root/'tests/http_check.php'),container+':/tmp/http_check.php')
 print(run('docker','exec',container,'php','/tmp/http_check.php'))
finally:
 subprocess.run(['docker','rm','-f',container],capture_output=True)
 sql(f'DROP DATABASE {name}')
