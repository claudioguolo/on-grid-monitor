"""Append a production artifact to hostinger branch; no force push or credentials."""
import argparse
import subprocess
import tempfile
from pathlib import Path
from zipfile import ZipFile

root = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('package')
parser.add_argument('--remote', default='origin')
args = parser.parse_args()
package = Path(args.package).resolve()
def git(*values, cwd=root):
    return subprocess.check_output(['git', *values], cwd=cwd, text=True).strip()
# Resolve source before switching worktrees.
source = git('rev-parse', 'HEAD')
with ZipFile(package) as archive:
    version = archive.read('VERSION').decode().strip()
    members = archive.namelist()
    if any(Path(n).is_absolute() or '..' in Path(n).parts for n in members):
        raise SystemExit('Pacote invalido')
    existing = git('ls-remote', '--heads', args.remote, 'refs/heads/hostinger')
    with tempfile.TemporaryDirectory(prefix='energia-hostinger-') as temp:
        work = Path(temp) / 'release'
        temporary_branch = None
        if existing:
            git('fetch', args.remote, 'hostinger')
            git('worktree', 'add', '--detach', str(work), 'FETCH_HEAD')
            git('rm', '-r', '--ignore-unmatch', '.', cwd=work)
        else:
            git('worktree', 'add', '--detach', str(work), source)
            temporary_branch = 'hostinger-build-' + __import__('uuid').uuid4().hex[:10]
            git('switch', '--orphan', temporary_branch, cwd=work)
        try:
            archive.extractall(work)
            (work / 'BUILD.json').write_text(__import__('json').dumps({'version':version,'commit':source},indent=2)+'\n')
            git('add', '--force', '--all', cwd=work)
            changed = subprocess.run(['git','diff','--cached','--quiet'],cwd=work).returncode != 0
            if changed:
                git('-c','core.hooksPath=/dev/null','-c','user.name=github-actions[bot]','-c','user.email=41898282+github-actions[bot]@users.noreply.github.com',
                    'commit','-m',f'deploy: v{version} ({source[:7]})',cwd=work)
                git('push', args.remote, 'HEAD:refs/heads/hostinger', cwd=work)
            else:
                print('Pacote ja publicado; nenhuma alteracao.')
        finally:
            git('worktree','remove','--force',str(work))
            if temporary_branch:
                git('branch','-D',temporary_branch)
