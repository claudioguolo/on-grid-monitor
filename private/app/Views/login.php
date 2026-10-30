<?php $appPath=\App\Libraries\PanelAuth::appPath(); ?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Entrar · PY9MT Energia</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#0c111a;color:#e8eff9;font:16px system-ui}main{width:100%;max-width:420px;background:#141f2e;border:1px solid #29394f;border-radius:16px;padding:36px;box-shadow:0 20px 60px #0003}.brand{color:#60dfbe;font-size:13px;letter-spacing:2px;font-weight:700}h1{font-size:28px;margin:16px 0 8px}p{color:#9aacc1;line-height:1.6;font-size:14px}label{display:block;margin:22px 0 8px;font-size:14px}input{width:100%;padding:13px;border:1px solid #40516a;border-radius:7px;background:#0c1726;color:#e8eff9;font:inherit}input:focus{outline:2px solid #60dfbe;outline-offset:2px}button{width:100%;padding:14px;margin-top:28px;border:0;border-radius:7px;background:#60dfbe;color:#0c1726;font:inherit;font-weight:700;cursor:pointer}button:disabled{opacity:.5;cursor:not-allowed}.error{color:#ffc1b5;background:#3b252b;border-radius:6px;padding:12px}.version{margin-top:28px;font:12px ui-monospace,monospace;text-align:center;color:#9aacc1}@media(max-width:400px){main{padding:24px}}
</style></head><body><main><div class="brand">PY9MT ENERGIA</div><h1>Acesse seu painel</h1><p>Acompanhe o consumo e a energia injetada na rede.</p>
<?php if($error): ?><p class="error" role="alert"><?= esc($error) ?></p><?php endif ?>
<form method="post" action="<?= esc($appPath.'/login') ?>"><?= csrf_field() ?>
<label for="username">Usuário</label><input id="username" name="username" type="text" autocomplete="username" maxlength="255" required autofocus>
<label for="password">Senha</label><input id="password" name="password" type="password" autocomplete="current-password" maxlength="1024" required>
<button type="submit" <?= $configured?'':'disabled' ?>>Entrar</button></form>
<div class="version">v<?= esc(trim(file_get_contents(ROOTPATH.'VERSION'))) ?></div></main></body></html>
