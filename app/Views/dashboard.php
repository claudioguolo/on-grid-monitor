<?php $appPath = rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/'); ?>
<!doctype html>
<html lang="pt-BR">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>PY9MT Energia</title>
<style>
:root{--blue:#8bbaff;--green:#60dfbe;--orange:#ffbc65;--purple:#d4a5ff;--border:#29394f}*{box-sizing:border-box}body{margin:0;background:#0c111a;color:#e8eff9;font:14px system-ui}a{color:#8dd9ff}p{color:#b9c8da;line-height:1.5;margin:8px 0}small{color:#9aacc1}h1{font-size:24px;letter-spacing:-.7px;margin:0}h2{font-size:15px;margin:0 0 10px}.dashboard{max-width:1680px;margin:auto;padding:18px 24px;min-width:0}.app-header,.header-actions,.status-strip,.section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.app-header{margin-bottom:12px}.app-header p{font-size:12px;margin:3px 0 0}.header-actions{justify-content:flex-end;font-size:12px}.release-badge{padding:4px 7px;border:1px solid var(--border);border-radius:5px;color:#9aacc1;font:11px ui-monospace,monospace}.logout-form{margin:0}button,select{font:inherit;color:#e8eff9;background:#18273b;border:1px solid #40516a;border-radius:6px;padding:6px 10px;cursor:pointer}a:focus-visible,button:focus-visible,summary:focus-visible,select:focus-visible{outline:2px solid var(--green);outline-offset:3px}.status-strip{justify-content:flex-start;column-gap:28px;padding:9px 12px;background:#111d2b;border:1px solid var(--border);border-radius:8px;font-size:12px;color:#9aacc1}.status-strip strong{color:#e8eff9;font-weight:550;margin-left:5px}.tabs{display:flex;gap:7px;margin:12px 0}.tabs button{padding:7px 16px}.tabs button[aria-selected="true"]{background:var(--green);color:#0c1726;border-color:var(--green);font-weight:650}article{background:#141f2e;border:1px solid var(--border);border-radius:9px;padding:14px;min-width:0}section{min-width:0}.live-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px}.phase{--phase:var(--blue);border-top:2px solid var(--phase)}.phase.b{--phase:var(--purple)}.phase h2{color:var(--phase)}.phase-body{display:grid;grid-template-columns:minmax(120px,1fr) minmax(100px,1fr);align-items:center;gap:14px}.gauge{display:block;width:100%;max-width:210px;margin:auto;overflow:visible}.gauge-track,.gauge-fill{fill:none;stroke-width:12;stroke-linecap:round}.gauge-track{stroke:#29394f}.gauge-fill{stroke:var(--phase)}.gauge-value{fill:#e8eff9;font-size:22px;font-weight:650;text-anchor:middle}.gauge-label{fill:#9aacc1;font-size:10px;text-anchor:middle}.phase-values{display:grid;gap:8px}.phase-values small{display:block;font-size:11px}.phase-values strong{font-size:18px;font-weight:600;font-variant-numeric:tabular-nums}.flow-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.flow-grid small{display:block;font-size:11px}.value{font-size:22px;font-weight:650;font-variant-numeric:tabular-nums;margin-top:3px}.flow-meta{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;padding-top:10px;margin-top:10px;border-top:1px solid var(--border);font-size:12px}.flow-note{font-size:10px;margin:8px 0 0}.consumed{color:var(--orange)}.injected{color:var(--green)}.overview-grid{display:grid;grid-template-columns:1fr 1fr;align-items:start;gap:12px;margin-bottom:12px}.footer-note,.legend,#daily-range,#daily-info,#chart-info,#technical-info{font-size:11px}.energy-scroll{max-width:100%;overflow-x:auto}.energy-summary{width:100%;min-width:410px;border-collapse:collapse;font-size:12px}.energy-summary th,.energy-summary td{text-align:right;padding:10px 6px;border-bottom:1px solid var(--border);font-variant-numeric:tabular-nums}.energy-summary th:first-child,.energy-summary td:first-child{text-align:left;padding-left:0;width:32%}.energy-summary td:not(:first-child){font-size:15px;white-space:nowrap}.coverage{display:block;margin-top:3px;color:#9aacc1;font-size:10px;font-weight:400}.energy-summary th{font-weight:550}.chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:12px}.chart-grid>div{min-width:0}.chart-grid p{min-height:32px}.chart-grid svg,#daily-chart svg{display:block;width:100%;height:auto;min-height:150px!important}.section-head h2{margin:0}.section-head label{font-size:12px}.technical-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.technical-grid svg{min-height:140px!important}.technical-grid table{width:100%;border-collapse:collapse;font-size:11px}.technical-grid td,.technical-grid th{padding:5px 2px;text-align:right;border-bottom:1px solid var(--border)}.technical-grid th:first-child,.technical-grid td:first-child{text-align:left}details{border-bottom:1px solid var(--border);font-size:12px}summary{cursor:pointer;padding:9px 0;color:#b9c8da}pre{white-space:pre-wrap;overflow-wrap:anywhere;font-size:12px;background:#0c1726;padding:12px;border-radius:6px}#error{color:#ffb9a8}[hidden]{display:none!important}@media(min-width:1300px){.overview-grid{grid-template-columns:1fr 1.15fr}}@media(max-width:1100px){.live-grid{grid-template-columns:1fr 1fr}.flow-card{grid-column:1/-1}.flow-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.technical-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.overview-grid{grid-template-columns:1fr}}@media(max-width:650px){.dashboard{padding:12px}.app-header{align-items:flex-start}.header-actions{justify-content:flex-start;gap:10px}.live-grid,.chart-grid,.technical-grid{grid-template-columns:1fr}.flow-card{grid-column:auto}.flow-grid{grid-template-columns:1fr 1fr}.status-strip{gap:7px 16px}.status-strip>span:last-child{flex-basis:100%}.phase-body{grid-template-columns:minmax(120px,1fr) minmax(100px,1fr)}.value{font-size:20px}.chart-grid{gap:16px}.energy-summary td:not(:first-child){font-size:13px}h1{font-size:22px}}

.flow-gauge{max-width:210px}.flow-injection,.flow-consumption{fill:none;stroke-width:12;opacity:.7}.flow-injection{stroke:var(--green)}.flow-consumption{stroke:var(--orange)}.flow-card .flow-note{text-align:center;font-size:10px}.flow-gauge .gauge-value{font-size:21px}

/* Equal tracks keep every visible panel proportional at each breakpoint. */
.live-grid{grid-template-columns:repeat(var(--live-columns,3),minmax(0,1fr))}.phase.c{--phase:#f3d06b}.phase-body{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:10px}.flow-card{grid-column:auto}.gauge-value{font-size:clamp(16px,1.6vw,22px)}.phase-values strong{font-size:clamp(15px,1.35vw,18px)}
@media(max-width:1100px){.live-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.flow-card{grid-column:auto}}
@media(max-width:650px){.live-grid{grid-template-columns:1fr}.gauge-value{font-size:22px}.phase-values strong{font-size:18px}}
</style>
<main class="dashboard">
<header class="app-header"><div><h1>PY9MT Energia</h1><p>IE Tecnologia · SM-3W Lite · <span id="circuit-description">Carregando fases…</span></p></div><div class="header-actions">
<?php if(\App\Libraries\PanelAuth::isAdmin()): ?><a href="<?= esc($appPath.'/history') ?>">Importar histórico</a><a href="<?= esc($appPath.'/admin') ?>">Administração</a><?php endif ?>
<span class="release-badge" title="Versão instalada da aplicação">v<?= esc(trim(file_get_contents(ROOTPATH.'VERSION'))) ?></span>
<form class="logout-form" method="post" action="<?= esc($appPath.'/logout') ?>"><?= csrf_field() ?><button type="submit">Sair</button></form></div></header>
<div class="status-strip" aria-label="Estado da coleta"><span>Conexão <strong id="status">Carregando…</strong></span><span>Envios <strong id="total">—</strong></span><span>Último envio <strong id="last">—</strong></span></div>
<nav class="tabs" role="tablist" aria-label="Visões do monitor"><button id="overview-tab" role="tab" aria-selected="true" aria-controls="overview">Tempo real</button><button id="technical-tab" role="tab" aria-selected="false" aria-controls="technical" tabindex="-1">Técnica</button></nav>
<section id="overview" role="tabpanel" aria-labelledby="overview-tab">
<div class="live-grid"><article class="phase" data-phase="a"><h2>Fase A</h2><div class="phase-body">
<svg class="gauge" id="gauge-a" viewBox="0 0 180 115" role="meter" aria-label="Tensão da fase A" aria-valuemin="0" aria-valuemax="260" aria-valuetext="Sem medição recente">
<path class="gauge-track" d="M20 85 A70 70 0 0 1 160 85"/><path class="gauge-fill" id="meter-a" d="M20 85 A70 70 0 0 1 160 85" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"/>
<text class="gauge-value" id="voltage-a" x="90" y="77">—</text><text class="gauge-label" x="90" y="95">Tensão</text><text class="gauge-label" x="20" y="110">0 V</text><text class="gauge-label" x="160" y="110">260 V</text></svg>
<div class="phase-values"><div><small>Corrente</small><strong id="current-a">—</strong></div><div><small>Potência ativa</small><strong id="power-a">—</strong></div><div><small>Fator de potência</small><strong id="pf-a">—</strong></div></div></div></article>
<article class="phase b" data-phase="b"><h2>Fase B</h2><div class="phase-body">
<svg class="gauge" id="gauge-b" viewBox="0 0 180 115" role="meter" aria-label="Tensão da fase B" aria-valuemin="0" aria-valuemax="260" aria-valuetext="Sem medição recente">
<path class="gauge-track" d="M20 85 A70 70 0 0 1 160 85"/><path class="gauge-fill" id="meter-b" d="M20 85 A70 70 0 0 1 160 85" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"/>
<text class="gauge-value" id="voltage-b" x="90" y="77">—</text><text class="gauge-label" x="90" y="95">Tensão</text><text class="gauge-label" x="20" y="110">0 V</text><text class="gauge-label" x="160" y="110">260 V</text></svg>
<div class="phase-values"><div><small>Corrente</small><strong id="current-b">—</strong></div><div><small>Potência ativa</small><strong id="power-b">—</strong></div><div><small>Fator de potência</small><strong id="pf-b">—</strong></div></div></div></article>
<article class="phase c" data-phase="c" hidden><h2>Fase C</h2><div class="phase-body">
<svg class="gauge" id="gauge-c" viewBox="0 0 180 115" role="meter" aria-label="Tensão da fase C" aria-valuemin="0" aria-valuemax="260" aria-valuetext="Sem medição recente">
<path class="gauge-track" d="M20 85 A70 70 0 0 1 160 85"/><path class="gauge-fill" id="meter-c" d="M20 85 A70 70 0 0 1 160 85" pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"/>
<text class="gauge-value" id="voltage-c" x="90" y="77">—</text><text class="gauge-label" x="90" y="95">Tensão</text><text class="gauge-label" x="20" y="110">0 V</text><text class="gauge-label" x="160" y="110">260 V</text></svg>
<div class="phase-values"><div><small>Corrente</small><strong id="current-c">—</strong></div><div><small>Potência ativa</small><strong id="power-c">—</strong></div><div><small>Fator de potência</small><strong id="pf-c">—</strong></div></div></div></article>
<article class="flow-card"><h2>Fluxo com a rede</h2><div class="phase-body">
<svg class="gauge flow-gauge" id="gauge-net" viewBox="0 0 180 115" role="meter" aria-label="Potência líquida da rede" aria-valuemin="-1000" aria-valuemax="1000" aria-valuetext="Sem medição recente">
<path d="M20 85 A70 70 0 0 1 90 15" class="flow-injection"/><path d="M90 15 A70 70 0 0 1 160 85" class="flow-consumption"/>
<text class="gauge-label" x="90" y="7">0</text><path id="net-needle" d="M90 50 L90 22" stroke="#e8eff9" stroke-width="3" stroke-linecap="round" visibility="hidden"/>
<text class="gauge-value" id="net" x="90" y="78">—</text><text class="gauge-label" id="net-direction" x="90" y="94">Sem medição recente</text>
<text class="gauge-label" id="net-min" x="24" y="110">−1 kW</text><text class="gauge-label" id="net-max" x="156" y="110">+1 kW</text></svg>
<div class="phase-values"><div><small>Consumida da rede</small><strong class="consumed" id="consumption">—</strong></div><div><small>Injetada na rede</small><strong class="injected" id="injection">—</strong></div><div><small>Frequência</small><strong id="frequency">—</strong></div></div></div>
<p class="flow-note">Potência líquida <span class="phase-list">A + B</span> · escala automática · verde: injeção; laranja: consumo.</p></article></div>
<div class="overview-grid">
<article><h2>Geração/Consumo</h2>
<p class="footer-note">Gerada: energia injetada na rede. Total = gerada − consumida; positivo indica excedente injetado.</p>
<div class="energy-scroll" role="region" aria-label="Geração e consumo por período" tabindex="0"><table class="energy-summary"><thead><tr><th scope="col">Período</th><th scope="col" class="consumed">Consumida</th><th scope="col" class="injected">Gerada</th><th scope="col">Total</th></tr></thead><tbody id="energy-summary"></tbody></table></div><details><summary>Como os totais são calculados</summary><p class="footer-note">Energia em kWh: soma das variações dos contadores das fases <span class="phase-list">A + B</span>, dentro de cada período. Totais parciais consideram apenas o histórico disponível. Calendário: <span id="timezone">—</span>, semana iniciando na segunda-feira. Injeção é o excedente enviado à rede, não a geração solar total.</p>
</details></article>
<article><h2>Comparativo diário — últimos 7 dias</h2>
<p id="daily-range">Incluindo hoje, em andamento.</p>
<p><span class="consumed">● Consumida</span> · <span class="injected">● Gerada / injetada</span> · energia em kWh</p>
<div id="daily-chart"></div>
<details><summary>Ver valores por dia e cobertura do histórico</summary><div class="energy-scroll" role="region" aria-label="Comparativo diário de geração e consumo" tabindex="0"><table class="energy-summary"><thead><tr><th scope="col">Dia</th><th scope="col" class="consumed">Consumida</th><th scope="col" class="injected">Gerada</th><th scope="col">Total</th></tr></thead><tbody id="daily-summary"></tbody></table></div>
<p id="daily-info" role="status"></p><small>Gerada corresponde à energia injetada na rede. Total = gerada − consumida. Dias sem histórico não são tratados como zero; dias parciais e reinícios de contador são sinalizados. Os valores usam as variações dos contadores entre as amostras disponíveis de cada dia.</small></details></article></div>
<article>
<div class="section-head"><h2>Histórico de potência</h2><div><label for="period">Período: </label><select id="period"><option value="1">Última hora</option><option value="6">Últimas 6 horas</option><option value="24" selected>Últimas 24 horas</option><option value="168">Últimos 7 dias</option></select></div></div>
<div class="chart-grid"><div><h2>Potência consumida e injetada</h2>
<p>Saldo das fases <span class="phase-list">A + B</span>, em watts (W). <span style="color:#ffbc65">● Consumo da rede</span> · <span style="color:#60dfbe">● Injeção na rede</span></p>
<div id="power-chart"></div></div><div>
<h2>Potência por fase</h2><p><span id="phase-legend"></span> · Positivo: consumo; negativo: injeção.</p>
<div id="phase-chart"></div></div></div>
<p id="chart-info" role="status"></p>
<small>Os gráficos mostram potência instantânea, não energia acumulada em kWh nem geração solar total. Intervalos sem envio por mais de 3 minutos aparecem como interrupções.</small>
</article>
<details><summary>Inspecionar últimos envios recebidos</summary><h2>Últimos envios</h2><p>Atualização automática a cada 10 segundos. Histórico completo preservado no banco; esta tela mostra até 100 envios.</p>
<p id="error" role="status"></p><div id="readings"></div></details>
</section>
<section id="technical" role="tabpanel" aria-labelledby="technical-tab" hidden>
<div class="section-head"><h2>Análise técnica · fases <span class="phase-list">A + B</span></h2><div><label for="technical-period">Período: </label><select id="technical-period"><option value="1">Última hora</option><option value="6">Últimas 6 horas</option><option value="24" selected>Últimas 24 horas</option><option value="168">Últimos 7 dias</option></select></div></div>
<p>Valores enviados pelo monitor, sem conversão de escala. Apenas fases ativas.</p>
<p id="technical-info" role="status">Aguardando carregamento.</p>
<div id="technical-grid" class="technical-grid"></div>
<p class="footer-note">Mínimo, máximo e média amostral calculados sobre as medições válidas do período exibido. A média não é ponderada pelo tempo. Dados ausentes e interrupções de comunicação deixam lacunas nos gráficos. Potência ativa negativa indica injeção na rede.</p>
</section>
</main>
<script>
const API_PREFIX = <?= json_encode($appPath, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let settings;
let activePhases=['a','b'];
const phaseColors={a:'#8bbaff',b:'#d4a5ff',c:'#f3d06b'};
function configurePhases(){
 activePhases=settings.active_phases??['a','b'];
 for(const node of document.querySelectorAll('[data-phase]'))node.hidden=!activePhases.includes(node.dataset.phase);
 document.querySelector('.live-grid').style.setProperty('--live-columns',activePhases.length+1);
 const label=activePhases.map(p=>p.toUpperCase()).join(' + ');
 for(const node of document.querySelectorAll('.phase-list'))node.textContent=label;
 const off=['a','b','c'].filter(p=>!activePhases.includes(p));
 el('circuit-description').textContent=({1:'Monofásico',2:'Bifásico',3:'Trifásico'})[activePhases.length]+' · fases '+label+(off.length?' · desativadas: '+off.map(p=>p.toUpperCase()).join(', '):'');
 const legend=el('phase-legend');legend.replaceChildren();
 for(const phase of activePhases){if(legend.childNodes.length)legend.append(' · ');const span=document.createElement('span');span.style.color=phaseColors[phase];span.textContent='● Fase '+phase.toUpperCase();legend.append(span);}
 buildTechnicalCards();
}
const el=id=>document.getElementById(id);
const fmt=v=>v.toLocaleString('pt-BR',{maximumFractionDigits:2});
function updateLive(payload,recent){
 for(const phase of activePhases){
  for(const [id,key,unit] of [['voltage','u'+phase+'rms',' V'],['current','i'+phase+'rms',' A'],['power','p'+phase,' W'],['pf','pf'+phase,'']]){
   const raw=payload?.[key],value=raw===undefined||raw===null||raw===''?NaN:Number(raw);
   el(id+'-'+phase).textContent=recent&&Number.isFinite(value)?fmt(value)+unit:'—';
   if(id==='voltage'){
    const valid=recent&&Number.isFinite(value),gauge=el('gauge-'+phase);
    el('meter-'+phase).setAttribute('stroke-dashoffset',valid?100-Math.max(0,Math.min(260,value))/260*100:100);
    gauge.setAttribute('aria-valuetext',valid?fmt(value)+' volts':'Sem medição recente');
    if(valid)gauge.setAttribute('aria-valuenow',Math.max(0,Math.min(260,value)));else gauge.removeAttribute('aria-valuenow');
   }
   if(id==='power')el(id+'-'+phase).className=recent&&Number.isFinite(value)?value<0?'injected':value>0?'consumed':'':'';
  }
 }
 const number=key=>payload?.[key]===undefined||payload?.[key]===null||payload?.[key]===''?NaN:Number(payload[key]);
 const powers=activePhases.map(phase=>number('p'+phase)),net=powers.every(Number.isFinite)?powers.reduce((sum,power)=>sum+power,0):NaN,freq=number('freq'),valid=recent&&Number.isFinite(net);
 const limit=Math.max(1000,Math.ceil(Math.abs(valid?net:0)/1000)*1000),gauge=el('gauge-net'),needle=el('net-needle');
 el('net').textContent=valid?fmt(net)+' W':'—';
 el('net-direction').textContent=valid?(net<0?'Injetando na rede':net>0?'Consumindo da rede':'Sem fluxo líquido'):'Sem medição recente';
 el('net').style.fill=valid?(net<0?'#60dfbe':net>0?'#ffbc65':'#e8eff9'):'#e8eff9';
 needle.setAttribute('visibility',valid?'visible':'hidden');needle.setAttribute('transform',`rotate(${valid?net/limit*90:0} 90 85)`);
 gauge.setAttribute('aria-valuemin',-limit);gauge.setAttribute('aria-valuemax',limit);
 gauge.setAttribute('aria-valuetext',valid?fmt(Math.abs(net))+' watts · '+el('net-direction').textContent:'Sem medição recente');
 if(valid)gauge.setAttribute('aria-valuenow',net);else gauge.removeAttribute('aria-valuenow');
 el('net-min').textContent='−'+fmt(limit/1000)+' kW';el('net-max').textContent='+'+fmt(limit/1000)+' kW';
 el('consumption').textContent=valid?fmt(Math.max(0,net))+' W':'—';el('injection').textContent=valid?fmt(Math.max(0,-net))+' W':'—';
 el('frequency').textContent=recent&&Number.isFinite(freq)?fmt(freq)+' Hz':'—';
}
async function refreshEnergy(){try{
 const response=await fetch(API_PREFIX+'/api/summary');if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);const data=await response.json();
 const root=el('energy-summary');root.replaceChildren();
 for(const [key,label] of [['today','Hoje'],['week','Semana atual'],['month','Mês atual'],['previous_month','Mês anterior']]){
  const item=data.energy[key],row=document.createElement('tr'),name=document.createElement('th');name.scope='row';name.style.fontWeight='400';name.textContent=label;
  const note=document.createElement('small');note.className='coverage';
  if(item.from&&item.to){const options={timeZone:settings.timezone,day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'};note.textContent=(item.partial?'Parcial · ':'')+new Date(item.from).toLocaleString('pt-BR',options)+' a '+new Date(item.to).toLocaleString('pt-BR',options)+(item.resets?' · reinício de contador detectado':'');}else note.textContent='Sem histórico neste período';
  name.append(note);row.append(name);
  for(const type of ['consumption','injection','balance']){const value=document.createElement('td');
  const amount=type==='balance'?(item.consumption===null||item.injection===null?null:Math.round((item.injection-item.consumption)*1e6)/1e6):item[type];
  value.textContent=amount===null?'—':amount.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:3,signDisplay:type==='balance'?'exceptZero':'auto'})+' kWh';
  value.className=type==='balance'?(amount>0?'injected':amount<0?'consumed':''):type==='consumption'?'consumed':'injected';
  row.append(value);
  }root.append(row);
 }
 }catch(e){const root=el('energy-summary');root.replaceChildren();const row=document.createElement('tr'),cell=document.createElement('td');cell.colSpan=4;cell.textContent='Não foi possível atualizar: '+e.message;row.append(cell);root.append(row);}}

const ns='http://www.w3.org/2000/svg';
function svgNode(tag,attrs,text){const n=document.createElementNS(ns,tag);for(const [k,v] of Object.entries(attrs))n.setAttribute(k,v);if(text!==undefined)n.textContent=text;return n;}
function chart(id,points,series,unit="W",label="Potência",maxGapMs=settings.stale_seconds*1000,dateOnly=false,kind="line"){
 const root=el(id);root.replaceChildren();if(!points.length||!points.some(p=>series.some(s=>Number.isFinite(p[s.key])))){root.textContent='Nenhuma medição válida neste período.';return;}
 const w=1000,h=290,l=80,r=20,t=20,b=50;
 const times=points.map(p=>Date.parse(p.time));let minT=times[0],maxT=times.at(-1);if(minT===maxT){minT-=30000;maxT+=30000;}
 let low=0,high=0;for(const p of points)for(const s of series){if(Number.isFinite(p[s.key])){low=Math.min(low,p[s.key]);high=Math.max(high,p[s.key]);}}if(low===high)high=low+1;
 const pad=(high-low)*.08;high+=pad;if(low<0)low-=pad;
 const x=v=>kind==='bars'?l+(times.indexOf(v)+.5)*(w-l-r)/points.length:l+(v-minT)/(maxT-minT)*(w-l-r),y=v=>t+(high-v)/(high-low)*(h-t-b);
 const svg=svgNode('svg',{viewBox:`0 0 ${w} ${h}`,role:'img','aria-label':label+' '+unit,style:'width:100%;height:auto;min-height:180px'});
 for(let i=0;i<=4;i++){const v=low+(high-low)*i/4;svg.append(svgNode('line',{x1:l,x2:w-r,y1:y(v),y2:y(v),stroke:'#31435c'}));svg.append(svgNode('text',{x:l-8,y:y(v)+4,fill:'#b9c8da','font-size':12,'text-anchor':'end'},fmt(v)+(unit?' '+unit:'')));}
 svg.append(svgNode('line',{x1:l,x2:w-r,y1:y(0),y2:y(0),stroke:'#8396ad'}));
 const ticks=dateOnly?times:Array.from({length:5},(_,i)=>minT+(maxT-minT)*i/4);
 for(const time of ticks){const options={timeZone:settings.timezone,day:'2-digit',month:'2-digit',...(dateOnly?{}:{hour:'2-digit',minute:'2-digit'})};svg.append(svgNode('text',{x:x(time),y:h-18,fill:'#b9c8da','font-size':12,'text-anchor':'middle'},new Date(time).toLocaleString('pt-BR',options)));}
 if(kind==='bars'){
  const groupWidth=(w-l-r)/points.length,barWidth=Math.min(32,groupWidth*.7/series.length);
  points.forEach((point,i)=>series.forEach((line,index)=>{const value=point[line.key];if(!Number.isFinite(value))return;const bar=svgNode('rect',{x:x(times[i])+(index-series.length/2)*barWidth+2,y:Math.min(y(value),y(0)),width:barWidth-4,height:Math.abs(y(value)-y(0)),fill:line.color,rx:2});bar.append(svgNode('title',{},new Date(point.time).toLocaleDateString('pt-BR',{timeZone:settings.timezone})+' · '+line.name+': '+fmt(value)+' '+unit));svg.append(bar);}));
 }else{
 for(const s of series){let d='',previous=null;points.forEach((p,i)=>{if(!Number.isFinite(p[s.key])){previous=null;return;}d+=(previous===null||times[i]-previous>maxGapMs?'M':'L')+x(times[i])+','+y(p[s.key])+' ';previous=times[i];});svg.append(svgNode('path',{d,fill:'none',stroke:s.color,'stroke-width':2}));
 }
 }
 root.append(svg);
}
let dailyBusy=false;
async function refreshDaily(){if(dailyBusy)return;dailyBusy=true;try{
 const response=await fetch(API_PREFIX+'/api/daily');if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);const data=await response.json();
 const root=el('daily-summary');root.replaceChildren();
 const dateOptions={timeZone:data.timezone,day:'2-digit',month:'2-digit'};
 el('daily-range').textContent=new Date(data.days[0].time).toLocaleDateString('pt-BR',dateOptions)+' a '+new Date(data.days.at(-1).time).toLocaleDateString('pt-BR',dateOptions)+' · '+data.timezone+' · incluindo hoje, em andamento';
 for(const day of data.days){const row=document.createElement('tr'),name=document.createElement('th');name.scope='row';name.textContent=new Date(day.time).toLocaleDateString('pt-BR',{...dateOptions,weekday:'short'});const note=document.createElement('small');note.className='coverage';
 let coverage=day.samples===0?'Sem histórico':day.samples<2?'Amostras insuficientes':day.partial?'Parcial':'Histórico disponível';
 if(day.in_progress)coverage='Hoje · em andamento · '+coverage;
 if(day.resets)coverage+=' · reinício de contador';
 if(day.from&&day.to)coverage+=' · '+new Date(day.from).toLocaleTimeString('pt-BR',{timeZone:data.timezone,hour:'2-digit',minute:'2-digit'})+'–'+new Date(day.to).toLocaleTimeString('pt-BR',{timeZone:data.timezone,hour:'2-digit',minute:'2-digit'});
 note.textContent=coverage;name.append(note);row.append(name);
 for(const key of ['consumption','injection','balance']){const cell=document.createElement('td'),value=day[key];cell.textContent=value===null?'—':value.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:3,signDisplay:key==='balance'?'exceptZero':'auto'})+' kWh';cell.className=key==='balance'?(value>0?'injected':value<0?'consumed':''):key==='consumption'?'consumed':'injected';row.append(cell);}root.append(row);}
 chart('daily-chart',data.days,[{key:'consumption',color:'#ffbc65',name:'Consumida'},{key:'injection',color:'#60dfbe',name:'Gerada / injetada'}],'kWh','Comparativo diário',36*3600000,true,'bars');
 el('daily-info').textContent='Atualizado em '+new Date().toLocaleTimeString('pt-BR',{timeZone:data.timezone})+'.';
 }catch(e){el('daily-info').textContent='Não foi possível atualizar o comparativo: '+e.message;}finally{dailyBusy=false;}}
const technicalMetrics=[
 {id:'active',title:'Potência ativa',unit:'W',format:p=>'p'+p},
 {id:'reactive',title:'Potência reativa',unit:'var',format:p=>'q'+p},
 {id:'apparent',title:'Potência aparente',unit:'VA',format:p=>'s'+p},
 {id:'voltage',title:'Tensão',unit:'V',format:p=>'u'+p+'rms'},
 {id:'current',title:'Corrente',unit:'A',format:p=>'i'+p+'rms'},
 {id:'factor',title:'Fator de potência',unit:'',format:p=>'pf'+p},
 {id:'frequency',title:'Frequência',unit:'Hz'}
];
function metricSeries(metric){return metric.id==='frequency'?[{key:'freq',color:'#8bbaff',name:'Rede'}]:activePhases.map(p=>({key:metric.format(p),color:phaseColors[p],name:'Fase '+p.toUpperCase()}));}
function buildTechnicalCards(){
 el('technical-grid').replaceChildren();
 for(const metric of technicalMetrics){const card=document.createElement('article'),heading=document.createElement('h2'),legend=document.createElement('p'),graph=document.createElement('div'),stats=document.createElement('div');heading.textContent=metric.title+(metric.unit?' ['+metric.unit+']':'');legend.className='legend';legend.textContent=metricSeries(metric).map(s=>s.name).join(' · ');graph.id='technical-'+metric.id;stats.id=graph.id+'-stats';card.append(heading,legend,graph,stats);el('technical-grid').append(card);}
}
let technicalBusy=false;
async function refreshTechnical(){if(!settings||el('technical').hidden||technicalBusy)return;technicalBusy=true;const period=el('technical-period').value;try{
 const response=await fetch(API_PREFIX+'/api/technical?hours='+period);if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);const data=await response.json();if(period!==el('technical-period').value)return;
 for(const metric of technicalMetrics){const series=metricSeries(metric);chart('technical-'+metric.id,data.points,series,metric.unit,metric.title);
 const root=el('technical-'+metric.id+'-stats');root.replaceChildren();const table=document.createElement('table');table.setAttribute('aria-label','Estatísticas de '+metric.title);const head=document.createElement('tr');for(const text of ['Série','Mín.','Máx.','Média']){const cell=document.createElement('th');cell.textContent=text;head.append(cell);}table.append(head);
 for(const line of series){const values=data.points.map(p=>p[line.key]).filter(Number.isFinite),row=document.createElement('tr');let min=Infinity,max=-Infinity,sum=0;for(const v of values){min=Math.min(min,v);max=Math.max(max,v);sum+=v;}for(const text of [line.name,...(values.length?[min,max,sum/values.length].map(v=>fmt(v)+(metric.unit?' '+metric.unit:'')):['—','—','—'])]){const cell=document.createElement('td');cell.textContent=text;row.append(cell);}table.append(row);}root.append(table);}
 const latest=data.points.at(-1);el('technical-info').textContent=data.points.length+' medições no período.'+(data.truncated?' Exibindo as '+settings.history_limit+' mais recentes.':'')+(latest?' Última: '+new Date(latest.time).toLocaleString('pt-BR',{timeZone:settings.timezone})+'.':' Nenhum dado disponível.')+(latest&&Date.now()-Date.parse(latest.time)>settings.stale_seconds*1000?' Sem envio recente.':'');
 }catch(e){el('technical-info').textContent='Falha ao atualizar gráficos técnicos: '+e.message;}finally{technicalBusy=false;if(period!==el('technical-period').value)refreshTechnical();}}
el('technical-period').addEventListener('change',refreshTechnical);
function selectTab(name){for(const tab of ['overview','technical']){el(tab).hidden=tab!==name;el(tab+'-tab').setAttribute('aria-selected',String(tab===name));el(tab+'-tab').tabIndex=tab===name?0:-1;}if(name==='technical')refreshTechnical();}
for(const name of ['overview','technical']){el(name+'-tab').addEventListener('click',()=>selectTab(name));el(name+'-tab').addEventListener('keydown',e=>{if(['ArrowLeft','ArrowRight','Home','End'].includes(e.key)){e.preventDefault();const target=e.key==='Home'?'overview':e.key==='End'?'technical':name==='overview'?'technical':'overview';selectTab(target);el(target+'-tab').focus();}});}
let chartBusy=false;
async function refreshCharts(){if(chartBusy)return;chartBusy=true;try{
 const period=el('period').value,response=await fetch(API_PREFIX+'/api/power?hours='+period);if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);const data=await response.json();if(period!==el('period').value)return;
 chart('power-chart',data.points,[{key:'consumption',color:'#ffbc65',name:'Consumo'},{key:'injection',color:'#60dfbe',name:'Injeção'}]);
 chart('phase-chart',data.points,activePhases.map(phase=>({key:phase,color:phaseColors[phase],name:'Fase '+phase.toUpperCase()})));
 const latest=data.points.at(-1),recent=latest&&Date.now()-Date.parse(latest.time)<settings.stale_seconds*1000;
 el('chart-info').textContent=`${data.points.length} medições no período.`+(data.truncated?` Exibindo as ${settings.history_limit} mais recentes.`:'')+(!recent?' Aguardando medição recente.':'');
 }catch(e){el('chart-info').textContent='Não foi possível atualizar os gráficos: '+e.message;}finally{chartBusy=false;}}
el('period').addEventListener('change',()=>{if(!settings)return;if(chartBusy)setTimeout(refreshCharts,500);else refreshCharts();});

async function refresh(){try{
 const response=await fetch(API_PREFIX+'/api/readings');if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);
 const data=await response.json();el('error').textContent='';el('total').textContent=data.total;
 const latest=data.readings[0];el('status').textContent=latest?(Date.now()-Date.parse(latest.received_at)<settings.stale_seconds*1000?'Recebendo':'Sem envio recente'):'Aguardando monitor';
 el('last').textContent=latest?new Date(latest.received_at).toLocaleString('pt-BR'):'—';
 updateLive(latest?.parsed?.payload,latest&&Date.now()-Date.parse(latest.received_at)<settings.stale_seconds*1000);
 const root=el('readings');
 if(root.dataset.latest===String(latest?.id??'empty'))return;
 root.dataset.latest=String(latest?.id??'empty');root.replaceChildren();
 if(!latest){root.textContent='Nenhum envio recebido. Configure e habilite a transmissão no monitor.';return;}
 for(const r of data.readings){const box=document.createElement('details');const summary=document.createElement('summary');summary.textContent=`#${r.id} · ${new Date(r.received_at).toLocaleString('pt-BR')} · ${r.method} · ${r.source}`;box.append(summary);const pre=document.createElement('pre');pre.textContent=JSON.stringify(r.parsed,null,2);box.append(pre);const raw=document.createElement('pre');raw.textContent='Content-Type: '+(r.content_type||'(ausente)')+'\nQuery: '+r.query+'\nCorpo recebido:\n'+r.body;box.append(raw);root.append(box);}
}catch(e){updateLive(null,false);el('error').textContent='Não foi possível atualizar: '+e.message;el('status').textContent='Falha de conexão';}}
async function start(){try{
 const response=await fetch(API_PREFIX+'/api/settings');if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);settings=await response.json();
 configurePhases();
 el('timezone').textContent=settings.timezone;el('period').value=String(settings.default_chart_hours);el('technical-period').value=String(settings.default_chart_hours);
 refresh();refreshCharts();refreshEnergy();refreshDaily();
 setInterval(refreshDaily,Math.max(30,settings.refresh_seconds)*1000);setInterval(refreshTechnical,settings.refresh_seconds*1000);setInterval(refresh,settings.refresh_seconds*1000);setInterval(refreshCharts,settings.refresh_seconds*1000);setInterval(refreshEnergy,settings.refresh_seconds*1000);
}catch(e){el('error').textContent='Falha ao carregar parâmetros: '+e.message;}}
start();
</script>
</html>
