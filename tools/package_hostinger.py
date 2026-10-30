"""Package production CodeIgniter files and vendor; never credentials or readings."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
root = Path(__file__).resolve().parents[1]
target = root / 'dist/coletor_energia_php-hostinger.zip'
target.parent.mkdir(exist_ok=True)
if not (root / 'vendor/codeigniter4/framework/system/Boot.php').exists():
    raise SystemExit('Execute composer install --no-dev antes de empacotar')
with ZipFile(target, 'w', ZIP_DEFLATED) as archive:
    for name in ['app', 'public', 'vendor', 'config', 'database', 'docs']:
        for path in (root / name).rglob('*'):
            if path.is_file():
                archive.write(path, path.relative_to(root))
    for name in ['VERSION','README.md','LICENSE','composer.json','composer.lock','spark','env.hostinger.example']:
        archive.write(root / name, name)
    for name in ['cache','logs','session','uploads']:
        archive.writestr('writable/' + name + '/.gitkeep', '')
    archive.writestr('writable/.htaccess', 'Require all denied\n')
print(target)

# Ready-to-upload layout for Hostinger's Git public_html restriction.
import tempfile
import subprocess
from hostinger_layout import install
public_target = root / 'dist/py9mt-energia-public_html.zip'
commit = subprocess.check_output(['git','rev-parse','HEAD'],cwd=root,text=True).strip()
with tempfile.TemporaryDirectory() as temp:
    with ZipFile(target) as original:
        install(original,temp,commit)
    with ZipFile(public_target,'w',ZIP_DEFLATED) as output:
        for file in Path(temp).rglob('*'):
            if file.is_file(): output.write(file,file.relative_to(temp))
print(public_target)
