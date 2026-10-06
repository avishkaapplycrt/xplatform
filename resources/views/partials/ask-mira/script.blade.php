const IS_LOGGED_IN = {{ $loggedIn ? 'true' : 'false' }};
// True only on the Premium Mira page (it includes this script with premium => true):
// website analyses then come back with recommendations. The free Ask Mira gets scores only.
const ASK_MIRA_PREMIUM = {{ !empty($premium) ? 'true' : 'false' }};
const CHAT_QUESTIONS_BY_INDUSTRY = @json($chatQuestionsByIndustry);
const DEFAULT_STARTER_QUESTIONS = ["What does X Platforms do?", "How much does it cost?"];

function renderStarterQuestions(industryId){
  const key = industryId ? String(industryId) : 'all';
  let questions = CHAT_QUESTIONS_BY_INDUSTRY[key];
  if(!questions || !questions.length) questions = CHAT_QUESTIONS_BY_INDUSTRY['all'] || DEFAULT_STARTER_QUESTIONS;
  const wrap = document.getElementById('sideStarters');
  if(!wrap) return;
  wrap.innerHTML = '';
  questions.slice(0, 6).forEach(function(q){
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'side-starter';
    btn.textContent = q;
    btn.addEventListener('click', function(){ askSuggested(q); });
    wrap.appendChild(btn);
  });
}

// ASK MIRA
const CHAT_GREETING = document.getElementById('chatMsgs').innerHTML;

// Bot replies may contain a real URL (e.g. the signup link). Render it as a
// clickable <a> using DOM APIs only — never innerHTML on reply text, since
// this is model-influenced content and must never be parsed as markup.
function renderBotMessage(el, text){
  const urlRe=/https?:\/\/[^\s]+/g;
  let lastIndex=0, match;
  while((match=urlRe.exec(text))!==null){
    if(match.index>lastIndex) el.appendChild(document.createTextNode(text.slice(lastIndex,match.index)));
    const trailing=match[0].match(/[.,;:!?)]+$/);
    const url=trailing?match[0].slice(0,-trailing[0].length):match[0];
    const a=document.createElement('a');
    a.href=url;a.textContent=url;a.target='_blank';a.rel='noopener noreferrer';
    el.appendChild(a);
    if(trailing) el.appendChild(document.createTextNode(trailing[0]));
    lastIndex=urlRe.lastIndex;
  }
  if(lastIndex<text.length) el.appendChild(document.createTextNode(text.slice(lastIndex)));
}

function askSuggested(q){
  document.getElementById('chatInput').value=q;
  sendChat();
}

let pendingAnalyzeUrl = null;
let pendingAnalyzeSource = null;

function looksLikeUrl(text){
  return /https?:\/\/\S+/i.test(text);
}

function openLeadModal(url, source){
  pendingAnalyzeUrl = url;
  pendingAnalyzeSource = source;
  document.getElementById('leadName').value = '';
  document.getElementById('leadEmail').value = '';
  document.getElementById('leadModalError').textContent = '';
  document.getElementById('leadModalOverlay').classList.add('show');
}

function closeLeadModal(){
  document.getElementById('leadModalOverlay').classList.remove('show');
  pendingAnalyzeUrl = null;
  pendingAnalyzeSource = null;
}

async function submitLeadAndAnalyze(){
  const name = document.getElementById('leadName').value.trim();
  const email = document.getElementById('leadEmail').value.trim();
  const errEl = document.getElementById('leadModalError');
  errEl.textContent = '';
  if(!name){ errEl.textContent = 'Please enter your name.'; return; }
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ errEl.textContent = 'Please enter a valid email.'; return; }

  const submitBtn = document.getElementById('leadModalSubmit');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Analyzing...';

  const url = pendingAnalyzeUrl;
  const source = pendingAnalyzeSource;

  try{
    const res = await fetch('{{ route("chat.analyze-lead") }}', {method:'POST',
      headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body: JSON.stringify({name:name, email:email, url:url, industry_id:(document.getElementById('industrySelect')||{}).value||null, premium:ASK_MIRA_PREMIUM})
    });
    const data = await res.json();

    if(!res.ok){
      errEl.textContent = data.error || 'Something went wrong. Please try again.';
      submitBtn.disabled = false;
      submitBtn.textContent = 'Continue';
      return;
    }

    closeLeadModal();

    if(source === 'analyze'){
      const status = document.getElementById('analyzeStatus');
      status.classList.remove('err');
      status.textContent = data.reply;
      if(data.report) renderAnalysisReport(data.report);
    } else {
      const msgs = document.getElementById('chatMsgs');
      const bEl = document.createElement('div');
      bEl.className = 'chat-msg bot';
      renderBotMessage(bEl, data.reply);
      msgs.appendChild(bEl);
      if(data.report) renderAnalysisReport(data.report);
      msgs.scrollTop = msgs.scrollHeight;
    }
  }catch(e){
    errEl.textContent = 'Could not reach the server. Check your connection and try again.';
  }finally{
    submitBtn.disabled = false;
    submitBtn.textContent = 'Continue';
  }
}

@include('partials.ask-mira.report-script')

function switchChatTab(tab){
  const isAsk=tab==='ask';
  document.getElementById('tabBoxAsk').classList.toggle('active',isAsk);
  document.getElementById('tabBoxAnalyze').classList.toggle('active',!isAsk);
  document.getElementById('panelAsk').style.display=isAsk?'flex':'none';
  document.getElementById('panelAnalyze').style.display=isAsk?'none':'flex';
}

// Shared by both tabs — one message endpoint, two front ends.
async function postToChat(message){
  const res=await fetch('{{ route("chat.send") }}',{method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body:JSON.stringify({message:message, industry_id:(document.getElementById('industrySelect')||{}).value||null, premium:ASK_MIRA_PREMIUM})});
  const data=await res.json();
  return {ok:res.ok, data:data};
}

async function sendChat(){
  const input=document.getElementById('chatInput'),msg=input.value.trim();if(!msg)return;
  if(!IS_LOGGED_IN && looksLikeUrl(msg)){
    input.value = '';
    openLeadModal(msg, 'chat');
    return;
  }
  const msgs=document.getElementById('chatMsgs');
  const uEl=document.createElement('div');uEl.className='chat-msg user';uEl.textContent=msg;msgs.appendChild(uEl);input.value='';
  const typing=document.createElement('div');typing.className='chat-msg bot typing';typing.textContent='Thinking...';msgs.appendChild(typing);msgs.scrollTop=msgs.scrollHeight;
  try{
    const {ok,data}=await postToChat(msg);
    typing.remove();
    const bEl=document.createElement('div');bEl.className='chat-msg bot';
    renderBotMessage(bEl, ok?data.reply:(data.error||'Something went wrong. Please try again.'));
    msgs.appendChild(bEl);
    if(ok && data.report) renderAnalysisReport(data.report);
  }catch(e){typing.remove();
    const bEl=document.createElement('div');bEl.className='chat-msg bot';bEl.textContent='Could not reach the server. Check your connection and try again.';msgs.appendChild(bEl)}
  msgs.scrollTop=msgs.scrollHeight
}

async function sendAnalyzeUrl(){
  const input=document.getElementById('analyzeInput'),url=input.value.trim();
  const status=document.getElementById('analyzeStatus');
  status.classList.remove('err');
  if(!url){status.textContent='Enter a website URL first.';status.classList.add('err');return;}
  if(!/^https?:\/\//i.test(url)){status.textContent='Include http:// or https:// at the start.';status.classList.add('err');return;}
  if(!IS_LOGGED_IN){
    openLeadModal(url, 'analyze');
    return;
  }
  status.textContent='Checking your site — this can take a moment...';
  try{
    const {ok,data}=await postToChat(url);
    if(ok){
      status.textContent=data.reply;
      if(data.report) renderAnalysisReport(data.report);
    }else{
      status.textContent=data.error||'Something went wrong. Please try again.';
      status.classList.add('err');
    }
  }catch(e){
    status.textContent='Could not reach the server. Check your connection and try again.';
    status.classList.add('err');
  }
}

async function resetChat(){
  await fetch('{{ route("chat.reset") }}',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
  document.getElementById('chatMsgs').innerHTML=CHAT_GREETING;
  document.getElementById('analysisPanel').classList.remove('show');
  document.getElementById('chatUpsellAside')?.classList.remove('show');
  document.getElementById('analyzeInput').value='';
  document.getElementById('analyzeStatus').textContent='';
  document.getElementById('analyzeStatus').classList.remove('err');
  switchChatTab('ask');
}
