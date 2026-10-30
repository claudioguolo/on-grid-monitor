"""Check append-only artifact publication against a disposable local remote."""
import subprocess
import tempfile
from pathlib import Path
from zipfile import ZipFile,ZIP_DEFLATED
root=Path(__file__).resolve().parents[1]
with tempfile.TemporaryDirectory(prefix='energia-publish-') as tmp:
    remote=Path(tmp)/'remote.git'
    subprocess.run(['git','init','--bare',str(remote)],check=True,capture_output=True)
    package=root/'dist/coletor_energia_php-hostinger.zip'
    def publish(path):
        subprocess.run(['python3','tools/publish_hostinger.py',str(path),'--remote',str(remote)],cwd=root,check=True,stdout=subprocess.DEVNULL)
    publish(package);publish(package)
    def git(*args):return subprocess.check_output(['git','--git-dir',str(remote),*args],text=True).strip()
    assert git('rev-list','--count','hostinger')=='1'
    other=Path(tmp)/'changed.zip'
    with ZipFile(package) as original,ZipFile(other,'w',ZIP_DEFLATED) as target:
        for name in original.namelist():target.writestr(name,b'1.0.999\n' if name=='VERSION' else original.read(name))
    publish(other)
    assert git('rev-list','--count','hostinger')=='2'
    files=git('ls-tree','-r','--name-only','hostinger').splitlines()
    assert '.env' not in files and 'BUILD.json' in files and any(n.startswith('vendor/') for n in files)
    print('Publicacao: criacao, repeticao idempotente e atualizacao sem force push: OK')
