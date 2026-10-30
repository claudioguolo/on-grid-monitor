"""Integration check: disposable MySQL database and unpublished app containers only."""
import json
import subprocess
import uuid
from concurrent.futures import ThreadPoolExecutor

suffix = uuid.uuid4().hex[:10]
database = 'energia_bootstrap_' + suffix
container = 'energia-bootstrap-' + suffix


def run(*args):
    return subprocess.check_output(args, text=True).strip()


def sql(statement):
    return run('docker', 'compose', 'exec', '-T', 'db', 'sh', '-c',
               'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -N -e "$1"', 'sh', statement)


def start(*environment):
    args = ['docker', 'compose', 'run', '-d', '--no-deps', '--name', container,
            '-e', 'DB_NAME=' + database, '-e', 'APP_BASE_URL=http://localhost/',
            '-e', 'CI_ENVIRONMENT=production']
    for value in environment:
        args += ['-e', value]
    run(*args, 'app')


def stop():
    subprocess.run(['docker', 'rm', '-f', container], capture_output=True)


def health():
    code = '''$c=stream_context_create(['http'=>['ignore_errors'=>true,'timeout'=>15]]);
    $body=file_get_contents('http://localhost/health',false,$c);
    echo json_encode(['status'=>$http_response_header[0], 'body'=>json_decode($body,true)]);'''
    return json.loads(run('docker', 'exec', container, 'php', '-r', code))


sql(f"CREATE DATABASE {database}; GRANT ALL ON {database}.* TO 'energia'@'%';")
try:
    start('DB_AUTO_MIGRATE=false')
    assert '200' in health()['status']
    assert sql(f"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{database}'") == '0'
    stop()
    start()
    with ThreadPoolExecutor(max_workers=4) as pool:
        results = list(pool.map(lambda _: health(), range(4)))
    assert all('200' in result['status'] and result['body']['ok'] for result in results), results
    assert sql(f'SELECT COUNT(*) FROM {database}.migrations') == '1'
    sql(f'''INSERT INTO {database}.readings
        (received_at,source,method,content_type,query,body,parsed)
        VALUES ('2026-10-30T00:00:00+00:00','validation','IMPORT','text/plain','','keep','{{}}')''')
    assert '200' in health()['status']
    assert sql(f'SELECT COUNT(*) FROM {database}.readings') == '1'
    stop()
    start('DB_PASSWORD=deliberately-invalid-test-password')
    result = health()
    assert '503' in result['status'], result
    assert 'deliberately-invalid' not in json.dumps(result)
    print('Bootstrap: empty DB, concurrency, preserved records, disable flag and safe failure: OK')
finally:
    stop()
    sql(f'DROP DATABASE {database}')
