const root=document.getElementById('overlay-app');
const el=id=>document.getElementById(id);
const csrf=document.querySelector('meta[name="csrf-token"]').content;
let lastSeq=null,lastReset=null,fingerprint='',people=[],animating=false,animationTimer=null,rollTimer=null;
const toggle=(id,visible)=>el(id).classList.toggle('hidden',!visible);
function showIdle(){
 animating=false;clearTimeout(animationTimer);clearInterval(rollTimer);
 toggle('idle-state',true);toggle('rolling-state',false);toggle('winner-state',false);
 el('confetti-layer').replaceChildren();
}
function confetti(){
 const layer=el('confetti-layer');layer.replaceChildren();
 for(let i=0;i<32;i++){const piece=document.createElement('span');piece.className='confetti-piece';layer.append(piece);}
 setTimeout(()=>layer.replaceChildren(),3500);
}
function reveal(winner){
 animating=false;clearInterval(rollTimer);
 toggle('idle-state',false);toggle('rolling-state',false);toggle('winner-state',true);
 el('winner-number').textContent=String(winner.id).padStart(4,'0');
 el('winner-name').textContent=winner.name;
 confetti();
}
function animate(winner){
 animating=true;clearTimeout(animationTimer);clearInterval(rollTimer);
 toggle('idle-state',false);toggle('winner-state',false);toggle('rolling-state',true);
 el('rolling-name').textContent='PREPARA...';
 const names=people.length?people.map(p=>p.name):['BOA SORTE!'];
 const start=performance.now();
 function cycle(){
  const elapsed=performance.now()-start;
  el('rolling-name').textContent=names[Math.floor(Math.random()*names.length)];
  const next=Math.min(240,45+(elapsed/4000)**2*190);
  rollTimer=setTimeout(cycle,next);
 }
 cycle();
 animationTimer=setTimeout(()=>reveal(winner),4000);
}
function renderTicker(list){
 const track=el('ticker-track');track.replaceChildren();
 if(!list.length){track.textContent='Aguardando inscricoes...';return;}
 for(let duplicate=0;duplicate<2;duplicate++){
  const group=document.createElement('div');group.className='ticker-group';
  for(const p of list){
   const pill=document.createElement('span');pill.className='participant-pill'+(p.won?' has-won':'');
   pill.textContent='#'+String(p.id).padStart(4,'0')+'  '+p.name+(p.won?' ★ GANHOU':'');
   group.append(pill);
  }
  track.append(group);
 }
 track.style.setProperty('--duration',Math.max(20,Math.min(360,list.length*3.5))+'s');
}
async function poll(){
 try{
  const response=await fetch('api.php?action=status',{cache:'no-store'});
  if(!response.ok)throw Error('Erro ao carregar sorteio');
  const data=await response.json(), state=data.state;
  if(lastSeq!==null && state.draw_seq<lastSeq)return;
  people=data.participants;
  el('overlay-counter').textContent=data.counts.total+' INSCRITOS';
  const signature=people.map(p=>p.id+':'+Number(p.won)).join(',');
  if(signature!==fingerprint){fingerprint=signature;renderTicker(people);}
  if(lastSeq===null){
   lastSeq=state.draw_seq;lastReset=state.reset_seq;
   if(state.winner){
    if(state.draw_age_ms!==null&&state.draw_age_ms<5000)animate(state.winner);
    else reveal(state.winner);
   }else showIdle();
  }else if(state.reset_seq!==lastReset){
   lastReset=state.reset_seq;lastSeq=state.draw_seq;showIdle();
  }else if(state.draw_seq>lastSeq){
   lastSeq=state.draw_seq;
   if(state.winner)animate(state.winner);
   else showIdle();
  }
 }catch(err){
  const message=el('overlay-message');if(message)message.textContent='Conexao interrompida. Reconectando...';
 }finally{setTimeout(poll,1200);}
}
const button=el('overlay-draw');
if(button)button.addEventListener('click',async()=>{
 button.disabled=true;const message=el('overlay-message');message.textContent='Sorteando...';
 try{
  const response=await fetch('api.php?action=draw',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:'{}'});
  const data=await response.json();
  if(!response.ok)throw Error(data.error||'Erro ao sortear');
  message.textContent='✓ Sorteio realizado!'; 
 }catch(e){message.textContent=e.message;}
 finally{button.disabled=false;}
});
poll();
