<?php $appPath = rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/'); ?>
<!doctype html>
<html lang="pt-BR">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>PY9MT Energia</title>
<style>
body{font:16px system-ui;background:#0c1726;color:#e8eff9;max-width:1100px;margin:auto;padding:32px}h1{margin-bottom:8px}p{color:#b9c8da;line-height:1.6}.cards{display:flex;gap:16px;flex-wrap:wrap}.card,article{background:#17263a;border:1px solid #31435c;border-radius:12px;padding:22px;margin:16px 0}.card{flex:1;min-width:180px}.value{font-size:26px;font-weight:650;margin-top:12px}pre{white-space:pre-wrap;overflow-wrap:anywhere;font-size:14px;background:#0c1726;padding:16px;border-radius:8px}summary{cursor:pointer;padding:12px 0}small{color:#b9c8da}#error{color:#ffb9a8}a{color:#8dd9ff}

:root{--blue:#8bbaff;--green:#60dfbe;--orange:#ffbc65}*{box-sizing:border-box}body{max-width:1440px;padding:28px 36px 28px 220px;background:#0c111a}.card,article{background:#141f2e;border-color:#29394f;box-shadow:0 8px 28px #0002}.card{min-width:160px}.value{font-variant-numeric:tabular-nums}h1{font-size:30px;letter-spacing:-1px}h2{font-size:19px}.eyebrow{color:#60dfbe;font-size:12px;letter-spacing:2px;text-transform:uppercase}.phase-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.phase{border-top:3px solid var(--blue)}.phase.b{border-color:#d4a5ff}.phase h2{color:var(--blue)}.phase.b h2{color:#d4a5ff}.phase-values{display:grid;grid-template-columns:1fr 1fr;gap:18px}.phase-values small{display:block}.phase-values strong{display:block;font-size:26px;margin:7px 0}meter{width:100%;height:12px;accent-color:var(--blue)}.energy-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.energy-table{width:100%;border-collapse:collapse}.energy-table td{padding:16px 0;border-bottom:1px solid #29394f}.energy-table td:last-child{text-align:right;font-size:23px;font-variant-numeric:tabular-nums}.coverage{display:block;font-size:11px;color:#9aacc1;max-width:240px}.consumed{color:var(--orange)}.injected{color:var(--green)}select{background:#1b2b40;color:#e8eff9;padding:10px;border:1px solid #526781;border-radius:6px}details{border-bottom:1px solid #29394f}#last{font-size:20px}.footer-note{font-size:13px}.section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}@media(max-width:900px){body{padding:20px}}@media(max-width:600px){.phase-grid,.energy-grid{grid-template-columns:1fr}.card{margin:4px 0}.value{font-size:22px}.energy-table td:last-child{font-size:20px}}

.tabs{display:flex;gap:12px;margin:20px 0}.tabs button{background:#18273b;color:#b9c8da;border:1px solid #354b66;border-radius:7px;padding:12px 24px;cursor:pointer;font:inherit}.tabs button[aria-selected="true"]{background:#60dfbe;color:#0c1726;border-color:#60dfbe;font-weight:650}.technical-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.technical-grid article{margin:0;padding:16px;min-width:0}.technical-grid svg{min-height:150px!important}.technical-grid table{width:100%;border-collapse:collapse;font-size:12px}.technical-grid td,.technical-grid th{padding:7px 3px;text-align:right;border-bottom:1px solid #29394f}.technical-grid th:first-child,.technical-grid td:first-child{text-align:left}.legend{font-size:13px}[hidden]{display:none!important}@media(max-width:750px){.technical-grid{grid-template-columns:1fr}}

/* Keep the sidebar independent of the centered dashboard width. */
:root{--sidebar-width:200px}body{margin:0;padding:0;max-width:none}.sidebar{position:fixed;inset:0 auto 0 0;width:var(--sidebar-width);padding:32px 18px;background:#131c2a;border-right:1px solid #263447;overflow-y:auto}.sidebar-brand{display:block;color:#60dfbe;font-size:14px;font-weight:750;letter-spacing:1px;white-space:nowrap}.sidebar small{display:block;margin-top:12px;line-height:1.5}.dashboard{width:calc(100% - var(--sidebar-width));max-width:1440px;margin-left:var(--sidebar-width);padding:28px clamp(16px,2.5vw,36px);min-width:0}.dashboard section,.dashboard article,.dashboard .card{min-width:0;overflow-wrap:anywhere}.phase-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.energy-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.energy-grid h2{font-size:18px}.energy-grid .energy-table td:last-child{font-size:clamp(18px,1.6vw,23px)}.energy-balance{border-top:3px solid var(--blue)}.phase-values{grid-template-columns:repeat(2,minmax(0,1fr))}.phase-values strong{font-size:clamp(20px,2vw,26px)}.tabs{flex-wrap:wrap}.energy-table{table-layout:fixed}.energy-table td:last-child{width:42%}.coverage{max-width:100%}select{max-width:100%}pre{max-width:100%}
@media(max-width:1100px){.energy-grid{grid-template-columns:1fr}}
@media(max-width:900px){.sidebar{position:static;width:100%;padding:16px 20px;border-right:0;border-bottom:1px solid #263447}.sidebar small{margin-top:4px}.dashboard{width:100%;margin-left:0;padding:20px}}
@media(max-width:600px){.phase-grid{grid-template-columns:1fr}.dashboard{padding:16px}.cards{gap:12px}.card{flex-basis:100%}.tabs button{flex:1;padding:12px 16px}}

.energy-scroll{max-width:100%;overflow-x:auto}.energy-summary{width:100%;min-width:560px;border-collapse:collapse}.energy-summary th,.energy-summary td{text-align:right;padding:16px 12px;border-bottom:1px solid #29394f;font-variant-numeric:tabular-nums}.energy-summary th:first-child,.energy-summary td:first-child{text-align:left;width:34%;padding-left:0}.energy-summary td:not(:first-child){font-size:clamp(16px,1.6vw,23px);white-space:nowrap}.energy-summary .coverage{margin-top:5px;font-size:11px}.energy-summary th{font-weight:600}
.release-badge{float:right;margin:0 0 12px 16px;padding:5px 9px;border:1px solid #29394f;border-radius:6px;color:#9aacc1;font:12px ui-monospace,monospace;letter-spacing:.3px}
.logout-form{float:right;margin:0 0 12px 12px}.logout-form button{padding:5px 10px;background:transparent;color:#b9c8da;border:1px solid #40516a;border-radius:6px;font:13px system-ui;cursor:pointer}
</style>
<aside class="sidebar" aria-label="Identificação da aplicação"><span class="sidebar-brand">PY9MT Energia</span><small>Monitor bifásico<br>IE Tecnologia · SM-3W Lite</small></aside>
<main class="dashboard">
<form class="logout-form" method="post" action="<?= esc($appPath . '/logout') ?>"><?= csrf_field() ?><button type="submit">Sair</button></form>
<div class="release-badge" title="Versão instalada da aplicação">v<?= esc(trim(file_get_contents(ROOTPATH . 'VERSION'))) ?></div>
<?php if(\App\Libraries\PanelAuth::isAdmin()): ?><p><a href="<?= esc($appPath . '/history') ?>">Importar histórico TXT</a> · <a href="<?= esc($appPath.'/admin') ?>">Administração</a></p><?php endif ?><div class="eyebrow">Monitoramento de energia · rede local</div><h1>PY9MT Energia</h1>
<p>IE Tecnologia · SM-3W Lite · instalação com duas fases</p>
<div class="cards">
<div class="card">Conexão<div class="value" id="status">Carregando…</div></div>
<div class="card">Envios armazenados<div class="value" id="total">—</div></div>
<div class="card">Último envio<div class="value" id="last">—</div></div>
</div>
<nav class="tabs" role="tablist" aria-label="Visões do monitor"><button id="overview-tab" role="tab" aria-selected="true" aria-controls="overview">Tempo real</button><button id="technical-tab" role="tab" aria-selected="false" aria-controls="technical" tabindex="-1">Técnica</button></nav>
<section id="overview" role="tabpanel" aria-labelledby="overview-tab">
<div class="cards">
<div class="card">Consumo da rede agora<div class="value" id="consumption">—</div></div>
<div class="card">Injeção na rede agora<div class="value" id="injection">—</div></div>
</div>
<div class="phase-grid">
<article class="phase"><h2>Fase A</h2><div class="phase-values"><div><small>Tensão</small><strong id="voltage-a">—</strong><meter id="meter-a" min="0" max="260" value="0" aria-label="Tensão da fase A"></meter></div><div><small>Corrente</small><strong id="current-a">—</strong></div><div><small>Potência ativa</small><strong id="power-a">—</strong></div><div><small>Fator de potência</small><strong id="pf-a">—</strong></div></div></article>
<article class="phase b"><h2>Fase B</h2><div class="phase-values"><div><small>Tensão</small><strong id="voltage-b">—</strong><meter id="meter-b" min="0" max="260" value="0" aria-label="Tensão da fase B"></meter></div><div><small>Corrente</small><strong id="current-b">—</strong></div><div><small>Potência ativa</small><strong id="power-b">—</strong></div><div><small>Fator de potência</small><strong id="pf-b">—</strong></div></div></article>
</div>
<div class="cards"><div class="card">Saldo total A + B<div class="value" id="net">—</div></div><div class="card">Frequência<div class="value" id="frequency">—</div></div><div class="card">Circuito<div class="value">Bifásico</div><small>Fase C desconectada</small></div></div>
<article><h2>Geração/Consumo</h2>
<p class="footer-note">Gerada: energia injetada na rede. Total = gerada − consumida; positivo indica excedente injetado.</p>
<div class="energy-scroll" role="region" aria-label="Geração e consumo por período" tabindex="0"><table class="energy-summary"><thead><tr><th scope="col">Período</th><th scope="col" class="consumed">Consumida</th><th scope="col" class="injected">Gerada</th><th scope="col">Total</th></tr></thead><tbody id="energy-summary"></tbody></table></div></article>
<p class="footer-note">Energia em kWh: soma das variações dos contadores das fases A e B, dentro de cada período. Totais parciais consideram apenas o histórico disponível. Calendário: <span id="timezone">—</span>, semana iniciando na segunda-feira. Injeção é o excedente enviado à rede, não a geração solar total.</p>
<article><h2>Comparativo diário — últimos 7 dias</h2>
<p id="daily-range">Incluindo hoje, em andamento.</p>
<p><span class="consumed">● Consumida</span> · <span class="injected">● Gerada / injetada</span> · energia em kWh</p>
<div id="daily-chart"></div>
<div class="energy-scroll" role="region" aria-label="Comparativo diário de geração e consumo" tabindex="0"><table class="energy-summary"><thead><tr><th scope="col">Dia</th><th scope="col" class="consumed">Consumida</th><th scope="col" class="injected">Gerada</th><th scope="col">Total</th></tr></thead><tbody id="daily-summary"></tbody></table></div>
<p id="daily-info" role="status"></p><small>Gerada corresponde à energia injetada na rede. Total = gerada − consumida. Dias sem histórico não são tratados como zero; dias parciais e reinícios de contador são sinalizados. Os valores usam as variações dos contadores entre as amostras disponíveis de cada dia.</small></article>
<article>
<label for="period">Período: </label><select id="period"><option value="1">Última hora</option><option value="6">Últimas 6 horas</option><option value="24" selected>Últimas 24 horas</option><option value="168">Últimos 7 dias</option></select>
<h2>Potência consumida e injetada</h2>
<p>Saldo das fases A + B, em watts (W). <span style="color:#ffbc65">● Consumo da rede</span> · <span style="color:#60dfbe">● Injeção na rede</span></p>
<div id="power-chart"></div>
<h2>Potência por fase</h2><p><span style="color:#8bbaff">● Fase A</span> · <span style="color:#d4a5ff">● Fase B</span> · Positivo: consumo; negativo: injeção. Fase C desconectada.</p>
<div id="phase-chart"></div>
<p id="chart-info" role="status"></p>
<small>Os gráficos mostram potência instantânea, não energia acumulada em kWh nem geração solar total. Intervalos sem envio por mais de 3 minutos aparecem como interrupções.</small>
</article>
<details><summary>Inspecionar últimos envios recebidos</summary><h2>Últimos envios</h2><p>Atualização automática a cada 10 segundos. Histórico completo preservado no banco; esta tela mostra até 100 envios.</p>
<p id="error" role="status"></p><div id="readings"></div></details>
</section>
<section id="technical" role="tabpanel" aria-labelledby="technical-tab" hidden>
<div class="section-head"><h2>Análise técnica · fases A e B</h2><div><label for="technical-period">Período: </label><select id="technical-period"><option value="1">Última hora</option><option value="6">Últimas 6 horas</option><option value="24" selected>Últimas 24 horas</option><option value="168">Últimos 7 dias</option></select></div></div>
<p>Valores enviados pelo monitor, sem conversão de escala. Fase C desconectada.</p>
<p id="technical-info" role="status">Aguardando carregamento.</p>
<div id="technical-grid" class="technical-grid"></div>
<p class="footer-note">Mínimo, máximo e média amostral calculados sobre as medições válidas do período exibido. A média não é ponderada pelo tempo. Dados ausentes e interrupções de comunicação deixam lacunas nos gráficos. Potência ativa negativa indica injeção na rede.</p>
</section>
</main>
<script>
const API_PREFIX = <?= json_encode($appPath, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let settings;
const el=id=>document.getElementById(id);
const fmt=v=>v.toLocaleString('pt-BR',{maximumFractionDigits:2});
function updateLive(payload,recent){
 for(const phase of ['a','b']){
  for(const [id,key,unit] of [['voltage','u'+phase+'rms',' V'],['current','i'+phase+'rms',' A'],['power','p'+phase,' W'],['pf','pf'+phase,'']]){
   const raw=payload?.[key],value=raw===undefined||raw===null||raw===''?NaN:Number(raw);
   el(id+'-'+phase).textContent=recent&&Number.isFinite(value)?fmt(value)+unit:'—';
   if(id==='voltage')el('meter-'+phase).value=recent&&Number.isFinite(value)?value:0;
  }
 }
 const a=Number(payload?.pa),b=Number(payload?.pb),net=a+b,freq=Number(payload?.freq);
 el('net').textContent=recent&&Number.isFinite(net)?fmt(net)+' W':'—';
 el('frequency').textContent=recent&&payload?.freq!==undefined&&Number.isFinite(freq)?fmt(freq)+' Hz':'—';
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
 {id:'active',title:'Potência ativa',unit:'W',keys:['pa','pb']},
 {id:'reactive',title:'Potência reativa',unit:'var',keys:['qa','qb']},
 {id:'apparent',title:'Potência aparente',unit:'VA',keys:['sa','sb']},
 {id:'voltage',title:'Tensão',unit:'V',keys:['uarms','ubrms']},
 {id:'current',title:'Corrente',unit:'A',keys:['iarms','ibrms']},
 {id:'factor',title:'Fator de potência',unit:'',keys:['pfa','pfb']},
 {id:'frequency',title:'Frequência',unit:'Hz',keys:['freq']}
];
for(const metric of technicalMetrics){const card=document.createElement('article'),heading=document.createElement('h2'),legend=document.createElement('p'),graph=document.createElement('div'),stats=document.createElement('div');heading.textContent=metric.title+(metric.unit?' ['+metric.unit+']':'');legend.className='legend';legend.textContent=metric.keys.length===2?'● Fase A (azul) · ● Fase B (lilás)':'● Frequência da rede';graph.id='technical-'+metric.id;stats.id=graph.id+'-stats';card.append(heading,legend,graph,stats);el('technical-grid').append(card);}
let technicalBusy=false;
async function refreshTechnical(){if(!settings||el('technical').hidden||technicalBusy)return;technicalBusy=true;const period=el('technical-period').value;try{
 const response=await fetch(API_PREFIX+'/api/technical?hours='+period);if(response.status===401){window.location.assign(API_PREFIX+'/login');return;}if(!response.ok)throw new Error('HTTP '+response.status);const data=await response.json();if(period!==el('technical-period').value)return;
 for(const metric of technicalMetrics){const series=metric.keys.map((key,i)=>({key,color:i?'#d4a5ff':'#8bbaff',name:metric.keys.length===1?'Rede':'Fase '+(i?'B':'A')}));chart('technical-'+metric.id,data.points,series,metric.unit,metric.title);
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
 chart('phase-chart',data.points,[{key:'a',color:'#8bbaff',name:'Fase A'},{key:'b',color:'#d4a5ff',name:'Fase B'}]);
 const latest=data.points.at(-1),recent=latest&&Date.now()-Date.parse(latest.time)<settings.stale_seconds*1000;
 el('consumption').textContent=recent?fmt(latest.consumption)+' W':'—';el('injection').textContent=recent?fmt(latest.injection)+' W':'—';
 el('chart-info').textContent=`${data.points.length} medições no período.`+(data.truncated?` Exibindo as ${settings.history_limit} mais recentes.`:'')+(!recent?' Aguardando medição recente.':'');
 }catch(e){el('chart-info').textContent='Não foi possível atualizar os gráficos: '+e.message;el('consumption').textContent='—';el('injection').textContent='—';}finally{chartBusy=false;}}
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
 el('timezone').textContent=settings.timezone;el('period').value=String(settings.default_chart_hours);el('technical-period').value=String(settings.default_chart_hours);
 refresh();refreshCharts();refreshEnergy();refreshDaily();
 setInterval(refreshDaily,Math.max(30,settings.refresh_seconds)*1000);setInterval(refreshTechnical,settings.refresh_seconds*1000);setInterval(refresh,settings.refresh_seconds*1000);setInterval(refreshCharts,settings.refresh_seconds*1000);setInterval(refreshEnergy,settings.refresh_seconds*1000);
}catch(e){el('error').textContent='Falha ao carregar parâmetros: '+e.message;}}
start();
</script>
</html>
