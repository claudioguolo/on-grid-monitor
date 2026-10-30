"""Validate the automatic commit version hook in an isolated Git repository."""
import shutil
import subprocess
import tempfile
from pathlib import Path
root=Path(__file__).resolve().parents[1]
with tempfile.TemporaryDirectory(prefix='energia-version-') as tmp:
    repo=Path(tmp)
    for name in ['tools/version.py','.githooks/pre-commit','VERSION_SERIES']:
        target=repo/name;target.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(root/name,target)
    def git(*args):
        return subprocess.check_output(['git',*args],cwd=repo,text=True).strip()
    git('init','-b','main');git('config','user.name','Version Test');git('config','user.email','version@example.invalid');git('config','core.hooksPath','.githooks')
    git('add','.');git('commit','-m','initial')
    assert git('show','HEAD:VERSION')=='1.0.1'
    (repo/'change.txt').write_text('adjustment');git('add','change.txt');git('commit','-m','adjustment')
    assert git('show','HEAD:VERSION')=='1.0.2'
    assert not git('status','--porcelain')
    git('switch','--orphan','initial-remote')
    (repo/'remote.txt').write_text('remote initialization');git('add','remote.txt');git('commit','--no-verify','-m','remote initial')
    git('switch','main');git('merge','--allow-unrelated-histories','--no-ff','--no-commit','initial-remote');git('commit','-m','merge remote')
    assert git('show','HEAD:VERSION')=='1.0.'+git('rev-list','--count','HEAD')
    print('Versao automatica: dois commits distintos, VERSION incluido e repositorio limpo: OK')
