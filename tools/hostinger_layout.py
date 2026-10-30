"""Install production files into a public_html/energia-compatible layout."""
from pathlib import Path
import json


def install(archive, destination, commit):
    destination=Path(destination)
    for name in archive.namelist():
        path=Path(name)
        if path.is_absolute() or '..' in path.parts:
            raise ValueError('Caminho invalido no pacote')
        if name.endswith('/'):
            continue
        target=destination/path.relative_to('public') if path.parts[0]=='public' else destination/'private'/path
        target.parent.mkdir(parents=True,exist_ok=True)
        content=archive.read(name)
        if name=='public/index.php':
            content=content.replace(b"require FCPATH . '../app/Config/Paths.php';",b"require FCPATH . 'private/app/Config/Paths.php';")
        target.write_bytes(content)
    private=destination/'private'
    private.mkdir(exist_ok=True)
    (private/'.htaccess').write_text('Require all denied\n')
    (private/'BUILD.json').write_text(json.dumps({'version':archive.read('VERSION').decode().strip(),'commit':commit},indent=2)+'\n')
    rules=destination/'.htaccess'
    content=rules.read_text()
    content=content.replace('RewriteEngine On','RewriteEngine On\n    RewriteRule ^private(?:/|$) - [F,END,NC]\n    RewriteRule (^|/)\\. - [F,END]',1)
    rules.write_text(content)
