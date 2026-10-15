const form=document.getElementById('register-form');
const csrf=document.querySelector('meta[name="csrf-token"]').content;
const digits=value=>value.replace(/\D/g,'');
const phone=document.getElementById('phone'), cpf=document.getElementById('cpf');
phone.addEventListener('input',()=>{
 const n=digits(phone.value).slice(0,11);
 phone.value=n.length>10?'('+n.slice(0,2)+') '+n.slice(2,7)+'-'+n.slice(7):
 n.length>2?'('+n.slice(0,2)+') '+n.slice(2,6)+(n.length>6?'-'+n.slice(6):''):n;
});
cpf.addEventListener('input',()=>{
 const n=digits(cpf.value).slice(0,11);
 cpf.value=n.slice(0,3)+(n.length>3?'.'+n.slice(3,6):'')+
 (n.length>6?'.'+n.slice(6,9):'')+(n.length>9?'-'+n.slice(9):'');
});
form.addEventListener('submit',async ev=>{
 ev.preventDefault();
 const message=document.getElementById('register-message');
 const btn=document.getElementById('register-submit');
 btn.disabled=true;message.className='';message.textContent='Registrando sua participacao...';
 try {
  const response=await fetch('api.php?action=register',{method:'POST',credentials:'same-origin',
   headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},
   body:JSON.stringify({name:document.getElementById('name').value,phone:phone.value,cpf:cpf.value,consent:document.getElementById('consent').checked})});
  const data=await response.json();
  if(!response.ok)throw Error(data.error||'Nao foi possivel cadastrar.');
  message.className='success';message.textContent='✓ Cadastro realizado! Seu numero de inscricao é #'+data.number+'. Boa sorte!';
  form.reset();loadCount();
 }catch(err){message.className='error';message.textContent=err.message;}
 finally{btn.disabled=false;}
});
async function loadCount(){
 try{
  const response=await fetch('api.php?action=status',{cache:'no-store'});
  if(!response.ok)return;
  const data=await response.json();
  document.getElementById('total-count').textContent=Number(data.counts.total).toLocaleString('pt-BR');
 }catch(e){}
}
loadCount();
