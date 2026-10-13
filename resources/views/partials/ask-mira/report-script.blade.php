{{-- Ask Mira "Analyse my website" report: score cards with pie charts, the
     category pop-up (animated pie, checks, recommendations). Needs only the
     report/pop-up elements (#analysisPanel, #analysisUrl, #analysisPieGrid,
     #analysisModalOverlay, #modalPct/#modalTitle/#modalDesc/#modalChecks), so
     any page can use it: the classic homepage and Premium Mira via
     partials.ask-mira.script, the new homepage directly. --}}
function scoreColor(score){
  if(score===null||score===undefined) return 'var(--g500)';
  if(score>=90) return 'var(--emerald)';
  if(score>=50) return 'var(--amber)';
  return 'var(--rose)';
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
  richresults: "Which rich results (stars, FAQs, breadcrumbs, products) your markup qualifies for.",
  crawl: "Can search engines index this page and read its content without running JavaScript?",
  speed: "Core Web Vitals on mobile, from Google PageSpeed Insights.",
  trust: "Freshness and trust signals: dates, authors, company pages, contact details and reviews.",
  ai: "How easy it is for AI assistants and answer engines to read, understand and quote your site."
};

function openAnalysisModal(key, label, pct, checkRows, recommendations){
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

  renderModalRecommendations(checksEl, checkRows, recommendations);

  document.getElementById('analysisModalOverlay').classList.add('show');
  // After .show: a transition can only start on an element that is rendered.
  animateModalPie(pctEl, pct);
}

// "Recommendations" section under the checks in the category pop-up. Uses
// the given list (the Site Overview card passes the report's
// top_recommendations), otherwise the fixes attached to this category's
// fail/warn checks — failures first. Built once below #modalChecks, then
// refilled on each open. Text goes in via textContent only.
function renderModalRecommendations(checksEl, checkRows, recommendations){
  let box=document.getElementById('modalRecs');
  // Free Ask Mira: the report has no recommendations, so show no section at all.
  if(RECOMMENDATIONS_SOURCE==='none'){
    if(box) box.remove();
    return;
  }
  if(!box){
    box=document.createElement('div');
    box.id='modalRecs';
    box.className='a-modal-recs';
    checksEl.parentNode.insertBefore(box, checksEl.nextSibling);
  }
  box.innerHTML='';

  const recs=(recommendations && recommendations.length ? recommendations : checkRows.filter(function(r){ return r.recommendation; }))
    .slice().sort(function(a,b){ return (a.status==='fail'?0:1)-(b.status==='fail'?0:1); });

  const title=document.createElement('div');
  title.className='a-modal-recs-title';
  title.textContent='Recommendations';
  if(RECOMMENDATIONS_SOURCE==='ai' && recs.length){
    const tag=document.createElement('span');
    tag.className='a-modal-recs-ai';
    tag.textContent='AI-tailored';
    title.appendChild(tag);
  }
  box.appendChild(title);

  if(!recs.length){
    const ok=document.createElement('p');
    ok.className='a-modal-recs-ok';
    ok.textContent='Nothing to fix here — every check in this section passed.';
    box.appendChild(ok);
    return;
  }

  const list=document.createElement('ol');
  list.className='a-modal-recs-list';
  recs.forEach(function(r){
    const li=document.createElement('li');
    li.className='a-modal-rec '+(r.status==='fail'?'fail':'warn');
    const head=document.createElement('div');
    head.className='a-modal-rec-head';
    const badge=document.createElement('span');
    badge.className='a-modal-check-badge '+(r.status==='fail'?'fail':'warn');
    badge.textContent=r.status==='fail'?'FAIL':'WARN';
    const name=document.createElement('span');
    name.className='a-modal-rec-name';
    name.textContent=r.name;
    head.appendChild(badge);head.appendChild(name);
    const text=document.createElement('p');
    text.className='a-modal-rec-text';
    text.textContent=r.recommendation;
    li.appendChild(head);li.appendChild(text);
    // "Why it matters" / "Check with" (CheckGuide), when the report has them.
    [['Why it matters: ', r.why], ['Check with: ', r.check_with]].forEach(function(pair){
      if(!pair[1]) return;
      const line=document.createElement('p');
      line.className='a-modal-rec-meta';
      const b=document.createElement('b'); b.textContent=pair[0];
      line.appendChild(b); line.appendChild(document.createTextNode(pair[1]));
      li.appendChild(line);
    });
    list.appendChild(li);
  });
  box.appendChild(list);
}

// Small donut before the percentage in the category pop-up. Created once and
// reused; each time the pop-up opens it snaps to empty with transitions off,
// a forced reflow makes the browser register that state, then the score is
// set with transitions back on, so .a-modal-pie-fill's CSS transition
// animates it. Called after the pop-up is shown (see openAnalysisModal).
function animateModalPie(pctEl, pct){
  const r=40, circumference=2*Math.PI*r;
  let pie=document.getElementById('modalPie');
  if(!pie){
    pie=document.createElementNS('http://www.w3.org/2000/svg','svg');
    pie.id='modalPie';
    pie.setAttribute('class','a-modal-pie');
    pie.setAttribute('viewBox','0 0 100 100');
    pie.setAttribute('aria-hidden','true');
    pie.innerHTML='<circle class="a-modal-pie-track" cx="50" cy="50" r="'+r+'"></circle>'+
                  '<circle class="a-modal-pie-fill" cx="50" cy="50" r="'+r+'"></circle>';
    pctEl.parentNode.insertBefore(pie, pctEl);
  }
  const fill=pie.querySelector('.a-modal-pie-fill');
  const p=(pct===null||pct===undefined)?0:Math.max(0,Math.min(100,pct));
  fill.style.stroke=scoreColor(pct);
  fill.style.strokeDasharray=circumference;
  fill.style.transition='none';
  fill.style.strokeDashoffset=circumference;
  void fill.getBoundingClientRect();   // force reflow: commit the empty state
  fill.style.transition='';
  fill.style.strokeDashoffset=circumference*(1-p/100);
}

function closeAnalysisModal(){
  document.getElementById('analysisModalOverlay').classList.remove('show');
}

function buildPieCard(label, pct, checkRows, key, recommendations){
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
    openAnalysisModal(key, label, pct, checkRows, recommendations);
  });
  return card;
}

// ---- Report extras: Google preview, rich results, "Needs connected data" ----
// Shared with partials.ask-mira.compare-script. All text via textContent.
function askMiraEl(tag, cls, text){
  const el=document.createElement(tag);
  if(cls) el.className=cls;
  if(text!==undefined && text!==null) el.textContent=text;
  return el;
}

// A Google search result: URL breadcrumb, title link, description.
function askMiraSerpPreview(preview, caption){
  const box=askMiraEl('div','serp');
  if(caption) box.appendChild(askMiraEl('div','serp-caption',caption));
  if(!preview){ box.appendChild(askMiraEl('div','serp-desc','No preview available.')); return box; }
  box.appendChild(askMiraEl('div','serp-url',preview.display_url));
  box.appendChild(askMiraEl('div','serp-title',preview.title));
  box.appendChild(askMiraEl('div','serp-desc',preview.description || 'No description — Google will pick text from the page.'));
  if(preview.description_source==='page_text') box.appendChild(askMiraEl('div','serp-note','No meta description, so Google will likely show page text like this.'));
  return box;
}

// KPIs a scan can't measure: always "Not measured — connect <tool>", never a number.
function askMiraNeedsData(list){
  const box=askMiraEl('div','needs-data');
  box.appendChild(askMiraEl('div','needs-data-title','Needs connected data'));
  box.appendChild(askMiraEl('p','needs-data-sub',"These can't be measured by scanning a page. Connect the tool to track them."));
  const ul=askMiraEl('ul','needs-data-list');
  (list||[]).forEach(function(k){
    const li=askMiraEl('li');
    li.appendChild(askMiraEl('span','needs-data-kpi',k.kpi));
    li.appendChild(askMiraEl('span','needs-data-status',k.status || ('Not measured — connect '+k.tool)));
    ul.appendChild(li);
  });
  box.appendChild(ul);
  return box;
}

function renderReportExtras(panel, report){
  panel.querySelector('.a-extras')?.remove();
  const extras=askMiraEl('div','a-extras');
  if(report.serp_preview){
    const serpBox=askMiraEl('div','a-extras-block');
    serpBox.appendChild(askMiraEl('div','a-extras-title','How you look in Google'));
    serpBox.appendChild(askMiraSerpPreview(report.serp_preview));
    if(report.rich_result_types){
      serpBox.appendChild(askMiraEl('p','a-extras-note', report.rich_result_types.length
        ? 'Rich results this page qualifies for: '+report.rich_result_types.join(', ')
        : 'This page doesn’t qualify for any rich results yet.'));
    }
    extras.appendChild(serpBox);
  }
  if(report.needs_connected_data && report.needs_connected_data.length){
    extras.appendChild(askMiraNeedsData(report.needs_connected_data));
  }
  panel.appendChild(extras);
}

// Renders a WebsiteAnalyzerService report (real SEO/Technical checks)
// as a grid of per-category donut chart cards — never as a giant chat bubble.
// 'ai' when the report's recommendations were written by OpenAI, 'standard'
// for the fixed fallback advice — labels the pop-up's Recommendations section.
let RECOMMENDATIONS_SOURCE='none';

function renderAnalysisReport(report){
  // Pages with the comparison report (the homepage and Premium Mira) show a
  // single site in the same design; the classic homepage keeps the pie cards.
  if(typeof miraRenderSiteReport==='function'){
    miraRenderSiteReport(report);
    if(typeof onAnalysisReport==='function') onAnalysisReport(report);
    return;
  }
  RECOMMENDATIONS_SOURCE=report.recommendations_source||'none';
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
  ], 'overview', report.top_recommendations || []));

  report.categories.forEach(function(cat){
    gridEl.appendChild(buildPieCard(cat.label, cat.score, (cat.checks||[]).map(function(ch){
      return {name:ch.name, detail:ch.detail, status:ch.status, recommendation:ch.recommendation||null, why:ch.why||null, check_with:ch.check_with||null};
    }), cat.key));
  });

  renderReportExtras(panel, report);
  panel.classList.add('show');
  panel.scrollIntoView({behavior:'smooth',block:'nearest'});
  // Optional page hook — Premium Mira uses it to show the site in its "Your website" card.
  if(typeof onAnalysisReport==='function') onAnalysisReport(report);
  document.getElementById('chatUpsellAside')?.classList.add('show');   // absent on Premium Mira
}

// Spinner on the Analyse tab's status line while Mira is working. Rather than
// every caller toggling it, this watches the status text: a "working" message
// ("…this can take…", "Checking your site", "Comparing both websites") adds
// .loading, which draws the spinner (partials.ask-mira.styles); any other text
// (the result or an error) removes it.
(function(){
  var working = /this can take|^checking your site|^comparing both/i;
  document.querySelectorAll('.analyze-status,.mira-analyze-status').forEach(function(el){
    var update = function(){
      var busy = working.test(el.textContent.trim());
      el.classList.toggle('loading', busy);
      el.setAttribute('aria-busy', busy ? 'true' : 'false');
    };
    new MutationObserver(update).observe(el, {childList:true, characterData:true, subtree:true});
    update();
  });
})();
