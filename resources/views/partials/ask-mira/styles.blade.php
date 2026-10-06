/* Accent colours use fallbacks (the landing page blue) so a page can set
   --acc-rgb / --on-acc to re-theme the chat, e.g. mira-premium.blade.php. */
/* ASK MIRA */
#ask-mira .sh span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.chat-page-wrap{position:relative;z-index:1;max-width:1020px;margin:0 auto}
.chat-layout{display:flex;gap:16px;align-items:flex-start}
.chat-sidebar{width:230px;flex-shrink:0;background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:18px;display:flex;flex-direction:column;gap:18px;max-height:70vh;overflow-y:auto}
.side-lbl{font-family:var(--fm);font-size:10.5px;letter-spacing:1px;text-transform:uppercase;color:var(--g400);margin-bottom:8px}
.side-select{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:8px;padding:9px 10px;color:var(--white);font-family:var(--f1);font-size:13px;outline:none}
.side-select:focus{border-color:var(--blue)}
.side-premium{display:block;text-align:center;padding:10px 12px;background:linear-gradient(135deg,#d4a843,#f3d98b);color:#1a1306;border-radius:8px;font-family:var(--f1);font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 0 24px rgba(212,168,67,.25);transition:box-shadow .25s}
.side-premium:hover{box-shadow:0 0 36px rgba(212,168,67,.4)}
.side-starters{display:flex;flex-direction:column;gap:8px}
.side-starter{background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:10px 12px;color:var(--g100);font-family:var(--f1);font-size:12.5px;line-height:1.5;text-align:left;cursor:pointer;transition:all .2s}
.side-starter:hover{border-color:var(--brd2);color:var(--white);background:rgba(var(--acc-rgb,79,143,255),.08)}
.chat-card{flex:1;min-width:0;background:var(--card);border:1px solid var(--brd2);border-radius:16px;box-shadow:0 0 60px rgba(var(--acc-rgb,79,143,255),.12);display:flex;flex-direction:column;height:70vh;min-height:480px;overflow:hidden}
.chat-tabs{display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--brd);flex-shrink:0;padding:12px 16px}
.chat-tab-box{display:flex;align-items:center;flex:1;min-width:0;background:var(--bg3);border:1px solid var(--brd2);border-radius:10px;padding:2px 6px 2px 4px;transition:all .2s}
.chat-tab-box.active{background:rgba(var(--acc-rgb,79,143,255),.1);border-color:rgba(var(--acc-rgb,79,143,255),.35)}
.chat-tab-box:not(.active):hover{border-color:var(--g500)}
.chat-tab{display:flex;align-items:center;gap:7px;flex:1;min-width:0;background:none;border:none;color:var(--g400);font-family:var(--f1);font-size:13px;font-weight:600;padding:9px 10px;cursor:pointer;transition:color .2s;text-align:left}
.chat-tab svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;flex-shrink:0}
.chat-tab-box.active .chat-tab{color:var(--blue)}
.chat-tab-box:not(.active):hover .chat-tab{color:var(--g200)}
.chat-reset{background:none;border:none;color:var(--g400);cursor:pointer;padding:8px;border-radius:8px;transition:all .2s;flex-shrink:0}
.chat-reset:hover{color:var(--white);background:rgba(var(--acc-rgb,79,143,255),.12)}
.chat-reset svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;display:block}
.chat-tab-panel{flex:1;display:flex;flex-direction:column;min-height:0}
.chat-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 20px;border-top:1px solid var(--brd);font-size:11px;color:var(--g400);flex-shrink:0;flex-wrap:wrap}
.mira-status{display:inline-flex;align-items:center;gap:6px;font-weight:600;padding:3px 10px;border-radius:20px;background:var(--bg3);margin-left:auto}
.mira-status.live{color:var(--emerald)}
.mira-status.basic{color:var(--amber)}
.status-dot{width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 6px currentColor}
.analyze-form{flex:1;display:flex;flex-direction:column;justify-content:center;padding:24px 22px;gap:14px}
.analyze-hint{font-size:13.5px;color:var(--g300);line-height:1.6;text-align:center;max-width:420px;margin:0 auto}
.analyze-status{font-size:12.5px;color:var(--g400);text-align:center;min-height:1em}
.analyze-status.err{color:var(--rose)}
.chat-messages{flex:1;overflow-y:auto;padding:20px 22px;display:flex;flex-direction:column;gap:14px}
.chat-msg{max-width:80%;padding:12px 16px;border-radius:12px;font-size:14px;line-height:1.65;white-space:pre-wrap;word-wrap:break-word}
.chat-msg.bot{background:var(--bg3);color:var(--g100);align-self:flex-start;border-bottom-left-radius:4px}
.chat-msg.user{background:var(--blue);color:var(--on-acc,#fff);align-self:flex-end;border-bottom-right-radius:4px}
.chat-msg.typing{color:var(--g400);font-style:italic}
.chat-msg a{color:var(--cyan);text-decoration:underline}
.chat-input-wrap{padding:16px 20px;border-top:1px solid var(--brd);display:flex;gap:10px;flex-shrink:0}
.chat-input{flex:1;background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:12px 16px;color:var(--white);font-family:var(--f1);font-size:14px;outline:none;resize:none;max-height:140px}
.chat-input:focus{border-color:var(--blue)}
.chat-send{width:42px;height:42px;border-radius:10px;background:var(--blue);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;align-self:flex-end;transition:opacity .2s}
.chat-send:hover{opacity:.9}
.chat-send svg{width:18px;height:18px;fill:var(--on-acc,#fff)}
.analysis-panel{display:none;margin-top:16px;background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:24px}
.analysis-panel.show{display:block}
.a-summary{padding-bottom:16px;border-bottom:1px solid var(--brd)}
.analysis-url{font-size:13px;color:var(--g300);word-break:break-all}
.a-pie-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;padding-top:16px}
.a-pie-card{background:var(--bg3);border:1px solid var(--g600);border-radius:14px;padding:16px;display:flex;flex-direction:column;align-items:center;gap:10px}
.a-pie-svg{width:96px;height:96px}
.a-pie-track{fill:none;stroke:var(--g600);stroke-width:9}
.a-pie-fill{fill:none;stroke-width:9;stroke-linecap:round;transform:rotate(-90deg);transform-origin:50px 50px;transition:stroke-dashoffset .6s var(--ease)}
.a-pie-pct{font-family:var(--fm);font-size:17px;font-weight:800;fill:var(--white)}
.a-pie-label{font-size:13px;font-weight:700;color:var(--white);text-align:center}
.a-pie-checks{width:100%;display:flex;flex-direction:column;gap:4px;padding-top:8px;border-top:1px solid var(--brd)}
.a-pie-check-row{display:flex;justify-content:space-between;gap:8px;font-size:11px}
.a-pie-check-name{color:var(--g300)}
.a-pie-check-detail{color:var(--g100);font-weight:600;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px}
.a-pie-card{cursor:pointer;transition:transform .15s,border-color .15s}
.a-pie-card:hover{transform:translateY(-2px);border-color:var(--blue)}
.chat-upsell-aside{display:none;width:230px;flex-shrink:0;flex-direction:column;justify-content:center;gap:16px;background:linear-gradient(135deg,rgba(var(--acc-rgb,79,143,255),.1),rgba(56,189,248,.06));border:1px solid rgba(var(--acc-rgb,79,143,255),.3);border-radius:16px;padding:20px}
.chat-upsell-aside.show{display:flex}
.a-upsell-text{font-size:13px;color:var(--g200);line-height:1.5}
.a-upsell-text strong{color:var(--white);display:block;margin-bottom:4px;font-size:14.5px}
.a-upsell-btn{background:linear-gradient(135deg,var(--blue),var(--blue2));color:var(--on-acc,#fff);font-weight:600;font-size:13px;padding:10px 16px;border-radius:10px;white-space:nowrap;text-align:center;transition:transform .2s,box-shadow .2s}
.a-upsell-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(var(--acc-rgb,79,143,255),.35)}
.a-modal-overlay{display:none;position:fixed;inset:0;background:rgba(6,10,20,.75);backdrop-filter:blur(4px);z-index:200;align-items:center;justify-content:center;padding:20px}
.a-modal-overlay.show{display:flex}
.a-modal{background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:28px;max-width:440px;width:100%;max-height:80vh;overflow-y:auto;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.a-modal-close{position:absolute;top:14px;right:14px;background:none;border:none;color:var(--g400);font-size:22px;line-height:1;cursor:pointer;padding:4px 8px;border-radius:8px}
.a-modal-close:hover{color:var(--white);background:rgba(var(--acc-rgb,79,143,255),.08)}
.a-modal-header{display:flex;align-items:center;gap:16px;padding:0 28px 16px 0;border-bottom:1px solid var(--brd);margin-bottom:14px}
.a-modal-pct{font-family:var(--fm);font-size:26px;font-weight:800;flex-shrink:0;line-height:1}
/* Small donut before the percentage — built and animated by animateModalPie(). */
.a-modal-pie{width:46px;height:46px;flex-shrink:0;margin-right:-6px;transform:rotate(-90deg)}
.a-modal-pie-track{fill:none;stroke:var(--g600);stroke-width:12}
.a-modal-pie-fill{fill:none;stroke-width:12;stroke-linecap:round;transition:stroke-dashoffset .9s var(--ease) .05s}
@media(prefers-reduced-motion:reduce){.a-modal-pie-fill{transition:none}}
/* Recommendations under the checks in the category pop-up (renderModalRecommendations). */
.a-modal-recs{margin-top:16px;padding-top:14px;border-top:1px solid var(--brd)}
.a-modal-recs-title{font-family:var(--fm);font-size:10.5px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-bottom:10px}
.a-modal-recs-ok{font-size:13px;color:var(--emerald)}
.a-modal-recs-ai{margin-left:8px;padding:1px 7px;border-radius:5px;background:rgba(var(--acc-rgb,79,143,255),.14);color:var(--blue);letter-spacing:.5px;text-transform:none;font-size:10px}
.a-modal-recs-list{list-style:none;display:flex;flex-direction:column;gap:8px}
.a-modal-rec{padding:10px 12px;border-radius:10px;background:var(--bg3);border:1px solid var(--brd);border-left:3px solid var(--rec-c)}
.a-modal-rec.fail{--rec-c:var(--rose)}
.a-modal-rec.warn{--rec-c:var(--amber)}
.a-modal-rec-head{display:flex;align-items:center;gap:8px;margin-bottom:4px}
.a-modal-rec-name{font-size:12.5px;font-weight:600;color:var(--white)}
.a-modal-rec-text{font-size:12.5px;line-height:1.55;color:var(--g200)}
.a-modal-title{font-size:17px;font-weight:700;color:var(--white)}
.a-modal-desc{font-size:12.5px;color:var(--g300);margin-top:4px;line-height:1.5}
.a-modal-checks{display:flex;flex-direction:column;gap:10px}
.a-modal-check-row{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;font-size:13px;padding:6px 0;border-bottom:1px solid var(--brd)}
.a-modal-check-row:last-child{border-bottom:none}
.a-modal-check-left{display:flex;align-items:center;gap:8px;flex-shrink:0;max-width:55%}
.a-modal-check-badge{font-family:var(--fm);font-size:9px;font-weight:700;letter-spacing:.5px;padding:2px 6px;border-radius:5px;flex-shrink:0;white-space:nowrap}
.a-modal-check-badge.pass{background:rgba(52,211,153,.15);color:var(--emerald)}
.a-modal-check-badge.warn{background:rgba(251,191,36,.15);color:var(--amber)}
.a-modal-check-badge.fail{background:rgba(244,114,182,.15);color:var(--rose)}
.a-modal-check-badge.info{background:rgba(148,163,184,.15);color:var(--g300)}
.a-modal-check-name{color:var(--g200)}
.a-modal-check-detail{color:var(--g100);font-weight:600;text-align:right;flex:1 1 0;min-width:0;line-height:1.45;overflow-wrap:anywhere}
.lead-modal-overlay{display:none;position:fixed;inset:0;background:rgba(6,10,20,.75);backdrop-filter:blur(4px);z-index:210;align-items:center;justify-content:center;padding:20px}
.lead-modal-overlay.show{display:flex}
.lead-modal{background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:28px;max-width:380px;width:100%;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.lead-modal-close{position:absolute;top:14px;right:14px;background:none;border:none;color:var(--g400);font-size:22px;line-height:1;cursor:pointer;padding:4px 8px;border-radius:8px}
.lead-modal-close:hover{color:var(--white);background:rgba(var(--acc-rgb,79,143,255),.08)}
.lead-modal-title{font-size:17px;font-weight:700;color:var(--white);margin-bottom:6px}
.lead-modal-sub{font-size:12.5px;color:var(--g300);line-height:1.5;margin-bottom:18px}
.lead-modal-field{margin-bottom:14px}
.lead-modal-field label{display:block;font-size:12px;font-weight:600;color:var(--g200);margin-bottom:6px}
.lead-modal-field input{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:9px;padding:11px 14px;color:var(--white);font-family:var(--f1);font-size:13.5px;outline:none;transition:border-color .2s}
.lead-modal-field input:focus{border-color:var(--blue)}
.lead-modal-error{font-size:12px;color:var(--rose);min-height:1em;margin-bottom:8px}
.lead-modal-submit{width:100%;padding:12px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:var(--on-acc,#fff);border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;font-family:var(--f1);transition:opacity .2s}
.lead-modal-submit:hover{opacity:.9}
.lead-modal-submit:disabled{opacity:.6;cursor:not-allowed}
@media(max-width:768px){
  .chat-layout{flex-direction:column}
  .chat-sidebar{width:100%;max-height:none}
  .chat-upsell-aside{width:100%}
}
@media(max-width:640px){
  .chat-card{height:65vh}
}
@media(max-width:480px){
  .a-pie-grid{grid-template-columns:1fr}
  .a-modal{padding:20px}
  .a-upsell{flex-direction:column;align-items:flex-start}
  .a-upsell-btn{width:100%;text-align:center}
}
