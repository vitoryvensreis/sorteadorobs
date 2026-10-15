const csrf=document.querySelector('meta[name="csrf-token"]').content;
const el=id=>document.getElementById(id);
async function request(action,data=null){
 const options={credentials:'same-origin',cache:'no-store'};
 if(data!==null){options.method='POST';options.headers={'Content-Type':'application/json','X-CSRF-Token':csrf};options.body=JSON.stringify(data);}
 const response=await fetch('api.php?action='+action,options);
 const result=await response.json();
 if(!response.ok)throw Error(result.error||'Erro na requisicao.');
 return result;
}
const login=el('login-form');
if(login){
 login.addEventListener('submit',async event=>{
  event.preventDefault();const msg=el('admin-message');
  msg.textContent='Verificando acesso...';msg.className='';
  try{
   await request('login',{username:el('username').value,password:el('password').value});
   window.location.reload();
  }catch(e){msg.className='error';msg.textContent=e.message;}
 });
}else{
 let page=1, pages=1, previousSeq=-1, busy=false;
 const msg=el('admin-message');
 const say=(value,error=false)=>{msg.textContent=value;msg.className=error?'error':'success';};
 async function loadStatus(){
  try{
   const s=await request('status');
   el('count-total').textContent=s.counts.total;
   el('count-eligible').textContent=s.counts.eligible;
   el('count-winners').textContent=s.counts.winners;
   el('draw-btn').disabled=busy||s.counts.eligible===0;
   if(previousSeq!==s.state.draw_seq){
    previousSeq=s.state.draw_seq;
    el('winner-summary').textContent=s.state.winner?
     'Ultimo ganhador: #'+s.state.winner.id+' • '+s.state.winner.name:'Nenhum vencedor sorteado ainda.';
   }
  }catch(e){say(e.message,true);}
 }
 async function draw(){
  if(busy)return;
  busy=true;el('draw-btn').disabled=true;
  say('Sorteando... a animacao vai aparecer no OBS.');
  try{
   const data=await request('draw',{});
   say('✓ Sorteado: #'+data.winner.id+' • '+data.winner.name);
   await Promise.all([loadStatus(),loadHistory(),loadPeople()]);
  }catch(e){say(e.message,true);}
  finally{busy=false;loadStatus();}
 }
 el('draw-btn').addEventListener('click',draw);
 el('reset-btn').addEventListener('click',async()=>{
  if(!confirm('Reiniciar vencedores? Todos os inscritos voltarao a participar.'))return;
  try{await request('reset',{});say('Sorteio reiniciado. Cadastros preservados.');await Promise.all([loadStatus(),loadHistory(),loadPeople()]);}
  catch(e){say(e.message,true);}
 });
 el('clear-btn').addEventListener('click',async()=>{
  if(prompt('Exclusao definitiva de TODOS os inscritos e resultados. Digite APAGAR TUDO para confirmar:')!=='APAGAR TUDO')return;
  try{await request('clear',{confirmation:'APAGAR TUDO'});say('Todos os cadastros e resultados foram apagados.');await Promise.all([loadStatus(),loadHistory(),loadPeople()]);}
  catch(e){say(e.message,true);}
 });
 el('logout-btn').addEventListener('click',async()=>{try{await request('logout',{});location.reload();}catch(e){say(e.message,true);}});
 async function loadHistory(){
  try{
   const data=await request('history');const list=el('history-list');list.replaceChildren();
   if(!data.items.length){list.textContent='Nenhum sorteio realizado.';return;}
   data.items.forEach(item=>{
    const row=document.createElement('div');row.className='history-item';
    const num=document.createElement('b');num.textContent='#'+item.number;
    const name=document.createElement('span');name.textContent=item.name;
    row.append(num,name);list.append(row);
   });
  }catch(e){say(e.message,true);}
 }
 async function loadPeople(){
  try{
   const data=await request('participants&q='+encodeURIComponent(el('search').value)+'&page='+page);
   pages=data.pages;page=data.page;
   const tbody=el('participants-rows');tbody.replaceChildren();
   for(const p of data.items){
    const row=document.createElement('tr');
    for(const value of ['#'+p.id,p.name,p.phone,p.cpf,p.won?'GANHADOR':'ELEGIVEL']){
     const td=document.createElement('td');td.textContent=value;row.append(td);
    }
    if(p.won)row.className='row-winner';
    tbody.append(row);
   }
   if(!data.items.length){const tr=document.createElement('tr');const td=document.createElement('td');td.colSpan=5;td.textContent='Nenhum participante encontrado.';tr.append(td);tbody.append(tr);}
   el('page-info').textContent='Pagina '+page+' de '+pages+' • '+data.total+' registros';
   el('prev-page').disabled=page<=1;
   el('next-page').disabled=page>=pages;
  }catch(e){say(e.message,true);}
 }
 let debounce;
 el('search').addEventListener('input',()=>{clearTimeout(debounce);debounce=setTimeout(()=>{page=1;loadPeople();},250);});
 el('prev-page').addEventListener('click',()=>{if(page>1){page--;loadPeople();}});
 el('next-page').addEventListener('click',()=>{if(page<pages){page++;loadPeople();}});
 loadStatus();loadHistory();loadPeople();
 setInterval(loadStatus,2500);
 setInterval(loadHistory,7000);
}
