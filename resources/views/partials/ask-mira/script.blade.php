const IS_LOGGED_IN = {{ $loggedIn ? 'true' : 'false' }};
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

function scoreColor(score){
  if(score===null||score===undefined) return 'var(--g500)';
  if(score>=90) return 'var(--emerald)';
  if(score>=50) return 'var(--amber)';
  return 'var(--rose)';
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
      body: JSON.stringify({name:name, email:email, url:url, industry_id:(document.getElementById('industrySelect')||{}).value||null})
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

const CATEGORY_INFO = {
  overview: "Your overall SEO health score at a glance.",
  titles: "How your page shows up in Google search results.",
  structure: "Is your content organized the way Google likes?",
  images: "Are your images helping or hurting your SEO?",
  security: "Is your site safe and trusted by browsers?",
  mobile: "Does your site work well on phones?",
  links: "Are your links helping visitors and Google understand your site?",
  discoverability: "Can Google actually find and index your pages?",
  social: "How your page looks when shared on Facebook/Twitter.",
  richresults: "Can your site show up with stars, prices, etc. in Google?"
};

function openAnalysisModal(key, label, pct, checkRows){
  document.getElementById('modalTitle').textContent = label;
  document.getElementById('modalDesc').textContent = CATEGORY_INFO[key] || '';
  const pctEl = document.getElementById('modalPct');
  pctEl.textContent = (pct===null||pct===undefined) ? '—' : pct+'%';
  pctEl.style.color = scoreColor(pct);

  const checksEl = document.getElementById('modalChecks');
  checksEl.innerHTML = '';
  checkRows.forEach(function(row){
    const rowEl = document.createElement('div');
    rowEl.className = 'a-modal-check-row';
    const left = document.createElement('div');
    left.className = 'a-modal-check-left';
    const badge = document.createElement('span');
    badge.className = 'a-modal-check-badge ' + (row.status || 'info');
    badge.textContent = (row.status || 'info').toUpperCase();
    const n = document.createElement('span'); n.className = 'a-modal-check-name'; n.textContent = row.name;
    left.appendChild(badge); left.appendChild(n);
    const d = document.createElement('span'); d.className = 'a-modal-check-detail'; d.textContent = row.detail;
    rowEl.appendChild(left); rowEl.appendChild(d);
    checksEl.appendChild(rowEl);
  });

  document.getElementById('analysisModalOverlay').classList.add('show');
}

function closeAnalysisModal(){
  document.getElementById('analysisModalOverlay').classList.remove('show');
}

function buildPieCard(label, pct, checkRows, key){
  const r=42, circumference=2*Math.PI*r;
  const p=(pct===null||pct===undefined)?0:pct;
  const color=scoreColor(pct);
  const card=document.createElement('div');
  card.className='a-pie-card';
  card.innerHTML =
    '<svg class="a-pie-svg" viewBox="0 0 100 100">'+
      '<circle class="a-pie-track" cx="50" cy="50" r="'+r+'"></circle>'+
      '<circle class="a-pie-fill" cx="50" cy="50" r="'+r+'" style="stroke:'+color+';stroke-dasharray:'+circumference+';stroke-dashoffset:'+(circumference*(1-p/100))+'"></circle>'+
      '<text class="a-pie-pct" x="50" y="50" text-anchor="middle" dominant-baseline="central">'+((pct===null||pct===undefined)?'—':pct+'%')+'</text>'+
    '</svg>'+
    '<div class="a-pie-label"></div>';
  card.querySelector('.a-pie-label').textContent=label;
  const checksEl=document.createElement('div');
  checksEl.className='a-pie-checks';
  checkRows.forEach(function(row){
    const rowEl=document.createElement('div');
    rowEl.className='a-pie-check-row';
    const n=document.createElement('span');n.className='a-pie-check-name';n.textContent=row.name;
    const d=document.createElement('span');d.className='a-pie-check-detail';d.textContent=row.detail;
    d.title=row.detail;
    rowEl.appendChild(n);rowEl.appendChild(d);
    checksEl.appendChild(rowEl);
  });
  card.appendChild(checksEl);
  card.addEventListener('click', function(){
    openAnalysisModal(key, label, pct, checkRows);
  });
  return card;
}

// Renders a WebsiteAnalyzerService report (real SEO/Technical checks)
// as a grid of per-category donut chart cards — never as a giant chat bubble.
function renderAnalysisReport(report){
  const panel=document.getElementById('analysisPanel');
  document.getElementById('analysisUrl').textContent=report.url;

  const gridEl=document.getElementById('analysisPieGrid');
  gridEl.innerHTML='';

  const c=report.counts;
  gridEl.appendChild(buildPieCard('Site Overview', report.overall, [
    {name:'Grade', detail:report.grade, status:'info'},
    {name:'Pass', detail:String(c.pass), status:'pass'},
    {name:'Warn', detail:String(c.warn), status:'warn'},
    {name:'Fail', detail:String(c.fail), status:'fail'}
  ], 'overview'));

  report.categories.forEach(function(cat){
    gridEl.appendChild(buildPieCard(cat.label, cat.score, (cat.checks||[]).map(function(ch){
      return {name:ch.name, detail:ch.detail, status:ch.status};
    }), cat.key));
  });

  panel.classList.add('show');
  panel.scrollIntoView({behavior:'smooth',block:'nearest'});
  document.getElementById('chatUpsellAside').classList.add('show');
}

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
    body:JSON.stringify({message:message, industry_id:(document.getElementById('industrySelect')||{}).value||null})});
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
  document.getElementById('chatUpsellAside').classList.remove('show');
  document.getElementById('analyzeInput').value='';
  document.getElementById('analyzeStatus').textContent='';
  document.getElementById('analyzeStatus').classList.remove('err');
  switchChatTab('ask');
}
