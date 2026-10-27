<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pricing &ndash; X Platforms</title>
<meta name="description" content="Transparent X Platforms pricing: Starter, Growth and Enterprise. Every plan includes a 30-day proof of concept and Mira Premium for AI search visibility.">
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Instrument+Serif:ital@1&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
@php
  /*
   * Pricing, in the homepage (Figma) design system: black canvas, Space Grotesk
   * headlines, Instrument Serif italic gradient accents, glass cards, pill buttons.
   *
   * Plans match sign-up (ClientAuthController::PLANS — starter / professional
   * "Growth" / enterprise). The same plan key unlocks Mira Premium, so each
   * card's Mira Premium limits are read from config/mira_premium.php and can
   * never drift from what the product enforces.
   */
  $mira = config('mira_premium.plans');
  $lim = fn (string $plan, string $metric) => $mira[$plan]['limits'][$metric][0] ?? null;
  $n = fn ($v) => $v === null ? '—' : number_format($v);
  $signedIn = auth('client')->check();
  $planUrl = fn (string $plan) => $signedIn ? route('client.plan') : route('client.register');
  $plans = [
    'starter' => [
      'name' => 'Starter', 'tier' => 'Foundation', 'monthly' => 499, 'annual' => 399,
      'blurb' => 'For growing businesses ready to unify their data and get their first AI predictions.',
      'cap' => 'Up to 25,000 customer profiles', 'cta' => 'Start free trial',
      'lead' => "What's included",
      'features' => ['Up to 25,000 customer profiles', 'Up to 5 data sources', 'Layers 1–5 (Ingest → Predict)', 'Churn prediction & CLV scoring', '1 industry model', 'Standard dashboard & alerts', 'Email support (48-hour response)'],
    ],
    'professional' => [
      'name' => 'Growth', 'tier' => 'Intelligence', 'monthly' => 1499, 'annual' => 1199, 'featured' => true,
      'blurb' => 'For scaling businesses that need the full 8-layer engine with automated execution.',
      'cap' => 'Up to 250,000 customer profiles', 'cta' => 'Start free trial',
      'lead' => 'Everything in Starter, plus',
      'features' => ['Up to 250,000 customer profiles', 'Up to 20 data sources', 'All 8 layers fully active', 'Strategy Intelligence & playbooks', 'Autonomous Execution engine', 'Up to 3 industry models', 'A/B testing & ROI tracking', 'Priority support (4-hour response)'],
    ],
    'enterprise' => [
      'name' => 'Enterprise', 'tier' => 'Intelligence+', 'monthly' => null, 'annual' => null,
      'blurb' => 'For large organisations with complex data environments and mission-critical SLAs.',
      'cap' => 'Unlimited customer profiles', 'cta' => 'Talk to sales',
      'lead' => 'Everything in Growth, plus',
      'features' => ['Unlimited profiles & data sources', 'All 15 industry models', 'Dedicated infrastructure', 'Custom model training on your data', 'Named Customer Success Manager', '99.99% uptime SLA, 1-hour response', 'Private cloud or on-premise option'],
    ],
  ];
  $comparison = [
    'Capacity' => [
      ['Customer profiles', '25K', '250K', 'Unlimited'],
      ['Data sources', '5', '20', 'Unlimited'],
      ['Industry models', '1', '3', 'All 15'],
      ['Data history ingested', '12 months', '36 months', 'Unlimited'],
    ],
    '8-layer architecture' => [
      ['L1–L5 Ingest, Unify, Map, Detect, Predict', true, true, true],
      ['L6 Strategy Intelligence', false, true, true],
      ['L7 Autonomous Execution', false, true, true],
      ['L8 Continuous Learning', true, true, true],
    ],
    'AI predictions' => [
      ['Churn risk & lifetime value', true, true, true],
      ['Purchase propensity & next-best-action', false, true, true],
      ['Revenue projections', false, true, true],
      ['Custom prediction models', false, false, true],
    ],
    'Mira Premium — AI search & visibility' => [
      ['Projects (websites)', $n($lim('starter', 'projects')), $n($lim('professional', 'projects')), $n($lim('enterprise', 'projects'))],
      ['AI visibility runs / month', $n($lim('starter', 'visibility_runs')), $n($lim('professional', 'visibility_runs')), $n($lim('enterprise', 'visibility_runs'))],
      ['Tracked AI prompts', $n($lim('starter', 'prompts')), $n($lim('professional', 'prompts')), $n($lim('enterprise', 'prompts'))],
      ['Competitors per project', $n($lim('starter', 'competitors')), $n($lim('professional', 'competitors')), $n($lim('enterprise', 'competitors'))],
      ['Rank-tracked keywords', $n($lim('starter', 'tracked_keywords')), $n($lim('professional', 'tracked_keywords')), $n($lim('enterprise', 'tracked_keywords'))],
      ['Site audits / month', $n($lim('starter', 'scans')), $n($lim('professional', 'scans')), $n($lim('enterprise', 'scans'))],
      ['Site Explorer, Backlinks & Search Console', true, true, true],
    ],
    'Execution & automation' => [
      ['Email, push & CRM triggers', false, true, true],
      ['A/B testing engine', false, true, true],
      ['Webhook integrations', '5', '50', 'Unlimited'],
    ],
    'Security & compliance' => [
      ['SOC 2 Type II & GDPR', true, true, true],
      ['HIPAA', false, false, true],
      ['Private cloud / on-premise', false, false, true],
      ['Custom DPA', false, false, true],
    ],
    'Support' => [
      ['30-day proof of concept', true, true, true],
      ['Support response', '48 hours', '4 hours', '1 hour'],
      ['Dedicated success manager', false, false, true],
      ['Uptime SLA', '99.9%', '99.95%', '99.99%'],
    ],
  ];
  $addons = [
    ['Extra data sources', '$49', '/ source', 'Connect platforms beyond your plan limit, with real-time streaming and full historical backfill.'],
    ['Extra industry model', '$299', '/ model / mo', 'A pre-trained model for any of our 15 verticals, with playbooks and benchmark data.'],
    ['Profile volume boost', '$99', '/ 25K profiles', 'Scale capacity in blocks of 25,000 — upgrade instantly from your dashboard.'],
    ['Historical data boost', '$149', '/ year of history', 'Extend the training window. Older data sharpens predictions for seasonal businesses.'],
    ['White-label dashboard', '$499', '/ mo', 'Serve the dashboard under your own brand to stakeholders or clients.'],
    ['Advanced API access', '$199', '/ mo', 'REST & GraphQL with higher rate limits, custom endpoints and full documentation.'],
  ];
  $faqs = [
    ['Is there a free trial?', 'Yes — every plan starts with a 30-day proof of concept on your real data. We connect your sources, run the AI engine and show you real predictions. No credit card needed.'],
    ['What counts as a customer profile?', 'One unified identity created by X Platforms: one real person, however many times they appear across your systems.'],
    ['What is Mira Premium, and is it included?', 'Mira Premium is our AI search workspace: site audits, Site Explorer, backlinks, Google Search Console, rank tracking and AI visibility in ChatGPT. It is included in every plan, with the limits shown in the comparison table.'],
    ['Can I start on Starter and upgrade later?', 'Yes. Upgrade any time from your dashboard; the difference is prorated to your current billing cycle.'],
    ['How does annual billing work?', 'Annual plans are billed upfront for 12 months and save 20%: Starter is $399/mo ($4,788/year) and Growth is $1,199/mo ($14,388/year).'],
    ['What if I go over my profile limit?', 'We notify you at 80%. Add capacity in blocks of 25,000 for $99, or move up a tier — no contract changes needed.'],
    ['Is my data safe?', 'All plans are SOC 2 Type II and GDPR compliant, encrypted with AES-256 at rest and in transit. We never share, sell or train on your data for anyone else.'],
  ];
  $check = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>';
  $cross = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7l10 10M17 7L7 17"/></svg>';
@endphp
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth;overflow-x:hidden}
body{background:#000;color:#fff;font-family:'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
img,svg{display:block}
button{font-family:inherit}
.wrap{max-width:1200px;margin:0 auto;padding:0 60px}
@media (max-width:900px){.wrap{padding:0 24px}}
.accent{font-family:'Instrument Serif',Georgia,serif;font-style:italic;font-weight:400;
  background:linear-gradient(90deg,#F6F9FC,#93CEFF,#FFBFFF,#00EAF4,#F6F9FC);
  -webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent}
.eyebrow{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.12);
  background:rgba(255,255,255,.04);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);font-size:12.5px;color:#c7c9d1}
.eyebrow i{width:6px;height:6px;border-radius:50%;background:#6ee7b7;box-shadow:0 0 10px #6ee7b7}

/* ============ NAV (same as the homepage) ============ */
.nav{display:flex;align-items:center;justify-content:space-between;padding:22px 60px;position:relative;z-index:20}
@media (max-width:900px){.nav{padding:18px 24px}}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:18px}
.logo-mark{width:22px;height:22px;border-radius:5px}
.nav-links{display:flex;align-items:center;gap:36px;list-style:none}
.nav-links a{font-size:14px;color:#c7c9d1;transition:color .2s}
.nav-links a:hover,.nav-links a[aria-current="page"]{color:#fff}
.nav-right{display:flex;align-items:center;gap:14px}
.nav-burger{display:none;width:38px;height:38px;border-radius:8px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);align-items:center;justify-content:center;flex-shrink:0;cursor:pointer}
.nav-burger span,.nav-burger span::before,.nav-burger span::after{content:'';display:block;width:16px;height:2px;background:#fff;border-radius:2px;position:relative;transition:transform .2s}
.nav-burger span::before{position:absolute;top:-5px}
.nav-burger span::after{position:absolute;top:5px}
.nav-burger.open span{background:transparent}
.nav-burger.open span::before{transform:translateY(5px) rotate(45deg)}
.nav-burger.open span::after{transform:translateY(-5px) rotate(-45deg)}
.book-demo{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:1px solid rgba(255,255,255,.18);border-radius:999px;font-size:13.5px;font-weight:500;background:rgba(255,255,255,.04);transition:background .2s;white-space:nowrap}
.book-demo:hover{background:rgba(255,255,255,.1)}
.nav-signin{font-size:14px;font-weight:500;color:#c7c9d1;padding:9px 6px;transition:color .2s;white-space:nowrap}
.nav-signin:hover{color:#fff}
.nav-signup{display:inline-flex;align-items:center;padding:9px 18px;border-radius:999px;font-size:13.5px;font-weight:600;color:#0b0b0e;white-space:nowrap;
  background:linear-gradient(90deg,#F6F9FC,#93CEFF 45%,#FFBFFF);box-shadow:0 4px 18px rgba(147,206,255,.25);transition:transform .15s,box-shadow .2s}
.nav-signup:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(147,206,255,.4)}
.nav-logout{margin:0;display:inline-flex}
.nav-logout button{padding:9px 16px;border-radius:999px;border:1px solid rgba(255,255,255,.18);background:transparent;font-size:13.5px;font-weight:500;color:#c7c9d1;cursor:pointer}
.nav-logout button:hover{color:#fff;background:rgba(255,255,255,.06)}
.nav-links .nav-account{display:none}
.nav-links .nav-account form{margin:0}
.nav-links .nav-account button{display:block;width:100%;text-align:left;padding:16px 24px;border:0;background:transparent;font-size:14px;color:#c7c9d1;cursor:pointer}
@media (max-width:900px){
  .nav-links{position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:0;background:#0a0a0d;border-bottom:1px solid rgba(255,255,255,.08);max-height:0;overflow:hidden;transition:max-height .25s ease}
  .nav-links.open{max-height:460px}
  .nav-links li{width:100%}
  .nav-links a{display:block;width:100%;padding:16px 24px;border-bottom:1px solid rgba(255,255,255,.05)}
  .nav-burger{display:flex}
  .nav-right .nav-signin,.nav-right .nav-signup,.nav-right .nav-logout{display:none}
  .nav-links .nav-account{display:list-item}
}
@media (max-width:480px){.book-demo .label{display:none}}

/* ============ HERO — the homepage earth, dimmed, with its horizon glow ============ */
.hero{position:relative;padding:56px 60px 120px;text-align:center;overflow:hidden}
@media (max-width:900px){.hero{padding:36px 24px 80px}}
.hero-bg{position:absolute;inset:0 0 2px;z-index:0;overflow:hidden;pointer-events:none;
  -webkit-mask-image:linear-gradient(to bottom,#000 70%,transparent 98%);mask-image:linear-gradient(to bottom,#000 70%,transparent 98%);-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat}
.hero-bg img{position:absolute;left:50%;top:-170px;width:max(1500px,115%);translate:-50% 0;opacity:.9;animation:drift 30s ease-in-out infinite alternate}
.hero-bg::after{content:"";position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.55),rgba(0,0,0,.25) 45%,rgba(0,0,0,.05) 86%,#000 99%)}
@keyframes drift{to{transform:scale(1.05) translateY(-1.2%)}}
.hero-inner{position:relative;z-index:1}
.hero h1{margin-top:26px;font-family:'Space Grotesk',sans-serif;font-size:112px;line-height:.9;font-weight:500;letter-spacing:-4px;color:#F6F9FC;animation:rise 1.1s cubic-bezier(.16,1,.3,1) both}
.hero h1 .accent{display:block;letter-spacing:-.03em}
.hero-sub{margin:24px auto 0;max-width:620px;font-size:19px;line-height:1.55;color:#9aa0ac;animation:rise 1.1s .2s cubic-bezier(.16,1,.3,1) both}
@media (max-width:1100px){.hero h1{font-size:84px;letter-spacing:-3px}}
@media (max-width:900px){.hero h1{font-size:56px;letter-spacing:-2px}.hero-sub{font-size:16px}}
@media (max-width:480px){.hero h1{font-size:42px}}
@keyframes rise{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}

/* Billing switch */
.billing{display:inline-flex;align-items:center;gap:4px;margin-top:38px;padding:5px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);animation:rise 1.1s .35s cubic-bezier(.16,1,.3,1) both}
.billing button{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:999px;border:0;background:transparent;color:#c7c9d1;font-size:14px;font-weight:500;cursor:pointer;transition:background .25s,color .25s}
.billing button[aria-pressed="true"]{background:#F6F9FC;color:#0b0b0e}
.billing .save{font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;background:rgba(110,231,183,.14);color:#6ee7b7}
.billing button[aria-pressed="true"] .save{background:#0b0b0e;color:#6ee7b7}

/* ============ PLANS ============ */
.plans{position:relative;z-index:2;margin-top:-40px}
.plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:stretch}
@media (max-width:980px){.plan-grid{grid-template-columns:1fr;max-width:520px;margin:0 auto}}
.plan{position:relative;display:flex;flex-direction:column;padding:32px 28px 28px;border-radius:20px;background:rgba(10,10,13,.88);border:1px solid rgba(255,255,255,.08);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);overflow:hidden;transition:transform .3s cubic-bezier(.16,1,.3,1),border-color .3s}
.plan:hover{transform:translateY(-4px);border-color:rgba(255,255,255,.16)}
.plan::before{content:'';position:absolute;top:0;right:0;width:75%;height:50%;pointer-events:none;background:radial-gradient(ellipse at top right,var(--glow),transparent 70%)}
.plan>*{position:relative}
.plan.p-starter{--glow:rgba(125,211,252,.13)}
.plan.p-professional{--glow:rgba(255,191,255,.16)}
.plan.p-enterprise{--glow:rgba(242,193,78,.14)}
.plan.featured{background:linear-gradient(180deg,rgba(18,26,46,.95),rgba(6,8,13,.95));border-color:transparent;box-shadow:0 30px 80px -30px rgba(147,206,255,.35)}
.plan.featured::after{content:'';position:absolute;inset:0;border-radius:20px;padding:1px;pointer-events:none;
  background:linear-gradient(140deg,#93CEFF,rgba(255,191,255,.7) 40%,rgba(255,255,255,.06) 70%,#00EAF4);
  -webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude}
.plan-badge{position:absolute;top:22px;right:22px;padding:5px 11px;border-radius:999px;font-size:11px;font-weight:700;color:#0b0b0e;background:linear-gradient(90deg,#F6F9FC,#93CEFF 45%,#FFBFFF)}
.plan-tier{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#6a6e77}
.plan h3{margin-top:10px;font-family:'Space Grotesk',sans-serif;font-size:30px;font-weight:500;letter-spacing:-.8px}
.plan-blurb{margin-top:8px;font-size:13.5px;line-height:1.55;color:#9aa0ac;min-height:42px}
.plan-price{display:flex;align-items:baseline;gap:4px;margin-top:26px;font-family:'Space Grotesk',sans-serif}
.plan-price .cur{font-size:26px;font-weight:500;color:#c7c9d1;align-self:flex-start;margin-top:8px}
.plan-price .amt{font-size:62px;font-weight:500;letter-spacing:-2.5px;line-height:1}
.plan-price .per{font-size:14px;color:#8a8f99;font-family:'Inter',sans-serif}
.plan-price .custom{font-size:52px;font-weight:500;letter-spacing:-2px;line-height:1.1}
.plan-note{margin-top:8px;font-size:12.5px;color:#8a8f99;min-height:18px}
.plan-cap{display:inline-flex;margin-top:16px;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);font-size:12px;color:#c7c9d1;align-self:flex-start}
.plan-cta{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:22px;padding:14px;border-radius:999px;font-size:14px;font-weight:600;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.05);transition:background .2s,transform .15s}
.plan-cta:hover{background:rgba(255,255,255,.1);transform:translateY(-1px)}
.plan.featured .plan-cta{border:0;color:#0b0b0e;background:linear-gradient(90deg,#F6F9FC,#93CEFF 45%,#FFBFFF);box-shadow:0 8px 24px rgba(147,206,255,.3)}
.plan-feats{margin-top:26px;padding-top:22px;border-top:1px solid rgba(255,255,255,.07);flex:1}
.plan-feats .lead{font-size:12px;font-weight:600;color:#9aa0ac;margin-bottom:12px}
.plan-feats ul{list-style:none;display:grid;gap:10px}
.plan-feats li{display:flex;gap:10px;align-items:flex-start;font-size:13.5px;color:#d7d9df;line-height:1.4}
.plan-feats li svg{width:16px;height:16px;flex:none;margin-top:1px;fill:none;stroke:#6ee7b7;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
/* Mira Premium block inside each plan */
.plan-mira{margin-top:22px;padding:16px;border-radius:14px;background:linear-gradient(135deg,rgba(242,193,78,.1),rgba(242,193,78,.02));border:1px solid rgba(242,193,78,.2)}
.plan-mira .hd{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:#f2c14e;letter-spacing:.02em}
.plan-mira .hd svg{width:14px;height:14px;fill:#f2c14e}
.plan-mira dl{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}
.plan-mira dl>div{display:flex;flex-direction:column;justify-content:space-between}
.plan-mira dt{font-size:10.5px;color:#9aa0ac;line-height:1.3}
.plan-mira dd{font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:500;color:#F6F9FC;margin-top:2px}
.trial-line{margin-top:30px;text-align:center;font-size:13.5px;color:#8a8f99}
.trial-line b{color:#c7c9d1;font-weight:500}

/* ============ SECTIONS ============ */
.sec{padding:120px 60px 0}
@media (max-width:900px){.sec{padding:80px 24px 0}}
.sec-hd h2{font-family:'Space Grotesk',sans-serif;font-size:68px;font-weight:500;line-height:.94;letter-spacing:-3.5px;color:#F6F9FC}
.sec-hd .accent{display:block;letter-spacing:-.05em}
.sec-hd p{margin-top:18px;max-width:560px;color:#9aa0ac;font-size:15.5px;line-height:1.65}
.sec-hd.center{text-align:center}.sec-hd.center p{margin-left:auto;margin-right:auto}
@media (max-width:1200px){.sec-hd h2{font-size:52px}}
@media (max-width:900px){.sec-hd h2{font-size:38px;letter-spacing:-1.5px}}
.reveal{opacity:0;transform:translateY(26px);transition:opacity .9s cubic-bezier(.16,1,.3,1),transform .9s cubic-bezier(.16,1,.3,1)}
.reveal.in{opacity:1;transform:none}

/* Mira Premium showcase */
.mira{display:grid;grid-template-columns:1fr 1.05fr;gap:48px;align-items:center;margin-top:56px;padding:44px;border-radius:24px;border:1px solid rgba(255,255,255,.08);
  background:radial-gradient(80% 90% at 100% 0%,rgba(242,193,78,.12),transparent 60%),linear-gradient(160deg,#0d0d10,#050507)}
@media (max-width:980px){.mira{grid-template-columns:1fr;padding:28px}}
.mira-kicker{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#f2c14e}
.mira h3{margin-top:14px;font-family:'Space Grotesk',sans-serif;font-size:40px;font-weight:500;line-height:1.02;letter-spacing:-1.6px}
.mira p{margin-top:14px;color:#9aa0ac;font-size:15px;line-height:1.65}
.mira-list{list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:10px 18px;margin-top:22px}
.mira-list li{display:flex;gap:10px;font-size:13.5px;color:#d7d9df;line-height:1.4}
.mira-list li svg{width:16px;height:16px;flex:none;margin-top:1px;fill:none;stroke:#f2c14e;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
.mira-link{display:inline-flex;align-items:center;gap:8px;margin-top:26px;padding:12px 20px;border-radius:999px;font-size:14px;font-weight:600;color:#1a1406;
  background:linear-gradient(135deg,#f0d27a,#d4af37 55%,#b8902f);box-shadow:0 6px 20px rgba(212,175,55,.3);transition:transform .15s}
.mira-link:hover{transform:translateY(-1px)}
/* the little product window */
.mwin{border-radius:16px;background:#f7f5f0;color:#141417;box-shadow:0 40px 90px -30px rgba(0,0,0,.8);overflow:hidden}
.mwin-bar{display:flex;align-items:center;gap:6px;padding:11px 14px;background:#fff;border-bottom:1px solid #e6e2d8}
.mwin-bar i{width:8px;height:8px;border-radius:50%;background:#e6e2d8}
.mwin-bar span{margin-left:8px;font-size:11px;color:#6b6a72}
.mwin-body{padding:16px;display:grid;gap:12px}
.mwin-tabs{display:flex;gap:6px;flex-wrap:wrap}
.mwin-tabs b{padding:5px 10px;border-radius:7px;font-size:10.5px;font-weight:600;background:#fff;border:1px solid #e6e2d8}
.mwin-tabs b.on{background:#141417;color:#fff;border-color:#141417}
.mwin-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mcard{background:#fff;border:1px solid #e6e2d8;border-radius:12px;padding:12px}
.mcard .l{display:flex;justify-content:space-between;font-size:10.5px;font-weight:700}
.mcard .l em{font-style:normal;font-weight:500;color:#9a98a0}
.mcard .v{margin-top:8px;font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:600;letter-spacing:-1px}
.mcard .v small{font-family:'Inter',sans-serif;font-size:11px;font-weight:700;color:#1f8a5b;margin-left:6px;letter-spacing:0}
.mcard svg{width:100%;height:34px;margin-top:6px}
.sov{display:grid;gap:7px;margin-top:8px}
.sov div{display:grid;grid-template-columns:70px 1fr 34px;align-items:center;gap:8px;font-size:10.5px}
.sov s{display:block;height:7px;border-radius:4px;background:#ece9e2;text-decoration:none;overflow:hidden}
.sov s i{display:block;height:100%;border-radius:4px;background:#141417}
.sov div.you s i{background:#c9a13a}
.sov b{text-align:right;font-weight:600}

/* Comparison */
.compare{margin-top:56px;border-radius:20px;border:1px solid rgba(255,255,255,.08);background:#07080b;overflow:hidden}
.compare-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
.ctable{width:100%;min-width:720px;border-collapse:collapse}
.ctable thead th{position:sticky;top:0;z-index:1;background:#0b0c10;padding:22px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);vertical-align:bottom}
.ctable thead th:first-child{text-align:left;font-size:12px;font-weight:600;color:#6a6e77;letter-spacing:.06em;text-transform:uppercase}
.ctable thead .pn{display:block;font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:500;color:#F6F9FC}
.ctable thead .pp{display:block;margin-top:4px;font-size:12.5px;font-weight:500;color:#8a8f99}
.ctable thead th.hl{background:linear-gradient(180deg,rgba(147,206,255,.1),#0b0c10)}
.ctable thead th.hl .pn{background:linear-gradient(90deg,#F6F9FC,#93CEFF,#FFBFFF);-webkit-background-clip:text;background-clip:text;color:transparent}
.ctable .grp td{padding:26px 20px 10px;font-size:11.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9aa0ac;border-bottom:1px solid rgba(255,255,255,.06)}
.ctable .grp.mira-grp td{color:#f2c14e}
.ctable tbody td{padding:14px 20px;font-size:13.5px;color:#d7d9df;border-bottom:1px solid rgba(255,255,255,.04);text-align:center}
.ctable tbody td:first-child{text-align:left;color:#c7c9d1}
.ctable tbody td.hl{background:rgba(147,206,255,.035)}
.ctable td svg{width:18px;height:18px;margin:0 auto;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
.ctable td svg.yes{stroke:#6ee7b7}
.ctable td svg.no{stroke:#3a3d45}
.ctable tr.more{display:none}
.compare.open tr.more{display:table-row}
.compare-foot{display:flex;justify-content:center;padding:18px;border-top:1px solid rgba(255,255,255,.06)}
.ghost-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:999px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.04);color:#fff;font-size:13.5px;font-weight:500;cursor:pointer;transition:background .2s}
.ghost-btn:hover{background:rgba(255,255,255,.09)}

/* Add-ons */
.addons{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:56px}
@media (max-width:980px){.addons{grid-template-columns:1fr 1fr}}
@media (max-width:620px){.addons{grid-template-columns:1fr}}
.addon{padding:24px;border-radius:16px;background:#0a0a0d;border:1px solid rgba(255,255,255,.07);transition:border-color .25s,transform .25s}
.addon:hover{border-color:rgba(255,255,255,.16);transform:translateY(-3px)}
.addon h4{font-family:'Space Grotesk',sans-serif;font-size:19px;font-weight:500}
.addon .ap{margin-top:12px;font-family:'Space Grotesk',sans-serif;font-size:30px;font-weight:500;letter-spacing:-1px}
.addon .ap small{font-family:'Inter',sans-serif;font-size:12.5px;font-weight:400;color:#8a8f99;letter-spacing:0;margin-left:4px}
.addon p{margin-top:10px;font-size:13px;line-height:1.6;color:#9aa0ac}

/* ROI */
.roi{display:grid;grid-template-columns:1.1fr 1fr;gap:40px;align-items:center;margin-top:56px;padding:44px;border-radius:24px;border:1px solid rgba(255,255,255,.08);
  background:radial-gradient(70% 90% at 0% 0%,rgba(125,211,252,.12),transparent 60%),linear-gradient(160deg,#0c1520,#05070c)}
@media (max-width:980px){.roi{grid-template-columns:1fr;padding:28px}}
.roi .k{font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#7dd3fc}
.roi h3{margin-top:14px;font-family:'Space Grotesk',sans-serif;font-size:38px;font-weight:500;line-height:1.08;letter-spacing:-1.4px}
.roi h3 b{font-weight:500;background:linear-gradient(90deg,#b9e3ff,#7dd3fc);-webkit-background-clip:text;background-clip:text;color:transparent}
.roi p{margin-top:14px;color:#9aa0ac;font-size:14.5px;line-height:1.65}
.roi-stats{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.roi-stats div{padding:22px;border-radius:14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07)}
.roi-stats b{display:block;font-family:'Space Grotesk',sans-serif;font-size:40px;font-weight:500;letter-spacing:-1.5px}
.roi-stats span{font-size:12.5px;color:#9aa0ac}
.roi-stats .c1 b{color:#7dd3fc}.roi-stats .c2 b{color:#6ee7b7}.roi-stats .c3 b{color:#f2c14e}.roi-stats .c4 b{color:#FFBFFF}

/* Testimonials */
.quotes{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:56px}
@media (max-width:980px){.quotes{grid-template-columns:1fr}}
.quote{display:flex;flex-direction:column;padding:28px;border-radius:18px;background:#0a0a0d;border:1px solid rgba(255,255,255,.07)}
.quote .plan-tag{align-self:flex-start;padding:5px 11px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#c7c9d1}
.quote blockquote{margin-top:18px;font-size:15px;line-height:1.65;color:#e7e9ee;flex:1}
.quote .who{display:flex;align-items:center;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,.07)}
.quote .av{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#0b0b0e}
.quote .who b{display:block;font-size:14px}
.quote .who span{font-size:12px;color:#8a8f99}
.quote .res{margin-left:auto;text-align:right;font-family:'Space Grotesk',sans-serif;font-size:13px;color:#6ee7b7}

/* Trust strip */
.trust{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:40px}
@media (max-width:980px){.trust{grid-template-columns:repeat(2,1fr)}}
.trust>div{display:flex;align-items:center;gap:12px;padding:16px 18px;border-radius:14px;background:#0a0a0d;border:1px solid rgba(255,255,255,.07)}
.trust svg{width:20px;height:20px;flex:none;fill:none;stroke:#93CEFF;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.trust b{display:block;font-size:13px;font-weight:600}
.trust span{font-size:11.5px;color:#8a8f99}

/* FAQ (homepage layout) */
.faq-grid{display:grid;grid-template-columns:340px 1fr;gap:24px;margin-top:50px}
@media (max-width:900px){.faq-grid{grid-template-columns:1fr}}
.faq-help{align-self:start;background:linear-gradient(160deg,#12203a,#05070c);border-radius:18px;padding:28px;border:1px solid rgba(255,255,255,.06)}
.faq-help h4{font-size:18px;font-weight:700}
.faq-help p{margin-top:8px;font-size:13.5px;color:#9aa0ac;line-height:1.6}
.faq-help a{display:inline-flex;margin-top:18px;padding:11px 18px;border-radius:999px;border:1px solid rgba(255,255,255,.18);font-size:13.5px;font-weight:600;transition:background .2s}
.faq-help a:hover{background:rgba(255,255,255,.08)}
.faq-help a.mail{display:block;margin-top:14px;padding:0;border:0;font-size:12.5px;font-weight:500;color:#93CEFF}
.faq-help a.mail:hover{background:none;text-decoration:underline}
.faq-item{border:1px solid rgba(255,255,255,.07);border-radius:14px;margin-bottom:12px;background:#0a0a0d;transition:background .25s}
.faq-item.open{background:#0d1420}
.faq-q{display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%;padding:20px 22px;border:0;background:none;color:#fff;font-size:15px;font-weight:600;text-align:left;cursor:pointer}
.faq-q .ic{width:28px;height:28px;border-radius:50%;border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:transform .3s}
.faq-q .ic svg{width:12px;height:12px;fill:none;stroke:#fff;stroke-width:2.2;stroke-linecap:round}
.faq-item.open .ic{transform:rotate(45deg)}
.faq-a{display:grid;grid-template-rows:0fr;transition:grid-template-rows .35s cubic-bezier(.16,1,.3,1)}
.faq-a>div{overflow:hidden}
.faq-a p{padding:0 22px 20px;font-size:14px;color:#9aa0ac;line-height:1.65}
.faq-item.open .faq-a{grid-template-rows:1fr}

/* Final CTA */
.cta{position:relative;margin-top:120px;padding:90px 40px;border-radius:28px;text-align:center;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:#050507}
.cta::before{content:'';position:absolute;left:50%;top:100%;width:1400px;height:1400px;translate:-50% -14%;border-radius:50%;
  background:radial-gradient(closest-side,rgba(40,70,160,.55),rgba(10,20,60,.2) 60%,transparent);box-shadow:0 -10px 60px rgba(147,206,255,.25)}
.cta::after{content:'';position:absolute;left:50%;top:100%;width:1400px;height:1400px;translate:-50% -14%;border-radius:50%;
  background:conic-gradient(from -22deg,transparent 0deg,rgba(255,200,140,.4) 12deg,rgba(255,224,180,.95) 17deg,rgba(170,215,255,.95) 19.5deg,transparent 23deg,transparent 360deg);
  -webkit-mask:radial-gradient(farthest-side,transparent 99%,#000 99.6%);mask:radial-gradient(farthest-side,transparent 99%,#000 99.6%);
  filter:blur(2px);opacity:0;animation:sweep 5s cubic-bezier(.45,.05,.35,1) infinite}
@keyframes sweep{0%{rotate:-36deg;opacity:0}12%{opacity:1}66%{opacity:1}78%{rotate:38deg;opacity:0}100%{rotate:38deg;opacity:0}}
.cta>*{position:relative;z-index:1}
.cta h2{font-family:'Space Grotesk',sans-serif;font-size:72px;font-weight:500;line-height:.94;letter-spacing:-3.5px}
.cta h2 .accent{display:block;letter-spacing:-.04em}
.cta p{margin:20px auto 0;max-width:520px;color:#9aa0ac;font-size:16px;line-height:1.6}
.cta-row{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:32px}
.btn-glow{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;border-radius:999px;font-size:14.5px;font-weight:600;color:#0b0b0e;
  background:linear-gradient(90deg,#F6F9FC,#93CEFF 45%,#FFBFFF);box-shadow:0 10px 30px rgba(147,206,255,.35);transition:transform .15s}
.btn-glow:hover{transform:translateY(-1px)}
@media (max-width:900px){.cta{padding:60px 22px}.cta h2{font-size:42px;letter-spacing:-1.6px}}

/* ============ FOOTER (same as the homepage) ============ */
.footer{position:relative;padding:110px 0 0;overflow:hidden}
.footer-word{padding-left:60px}
.footer-grid{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:30px}
@media (max-width:900px){.footer-grid{grid-template-columns:1fr 1fr}}
.footer-brand h4{font-size:16px;font-weight:700}
.footer-brand p{margin-top:10px;font-size:13px;color:#8a8f99;line-height:1.6;max-width:260px}
.footer-social{display:flex;gap:12px;margin-top:16px}
.footer-social span{width:30px;height:30px;border-radius:8px;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:13px}
.footer-col .ttl{font-size:11px;color:#6a6e77;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px}
.footer-col ul{list-style:none;display:flex;flex-direction:column;gap:11px}
.footer-col a{font-size:13px;color:#c7c9d1}
.footer-col a:hover{color:#fff}
.footer-bottom{margin-top:70px;padding:22px 0;border-top:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#6a6e77}
.footer-bottom .links{display:flex;gap:22px}
.footer-word{font-size:min(20vw,240px);font-weight:800;color:rgba(255,255,255,.03);line-height:1;letter-spacing:-.03em;white-space:nowrap;margin-top:20px;user-select:none}
@media (max-width:620px){.footer-bottom{flex-direction:column;align-items:flex-start;gap:12px}}
@media (max-width:480px){.sec{padding:64px 18px 0}.wrap{padding:0 18px}.nav{padding:14px 18px}.plan{padding:26px 20px}.mira-list{grid-template-columns:1fr}.roi-stats b{font-size:32px}}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}.reveal{opacity:1;transform:none}}
</style>
</head>
<body id="top">

{{-- ============ NAV ============ --}}
<nav class="nav">
  <a href="{{ route('home') }}" class="brand">
    <img src="{{ asset('images/homepage/logo-mark-colored.png') }}" class="logo-mark" alt="">
    XPlatforms
  </a>
  <ul class="nav-links" id="navLinks">
    <li><a href="{{ route('home') }}">Home</a></li>
    <li><a href="{{ route('industries') }}">Industries</a></li>
    <li><a href="{{ route('simulator') }}">Simulator</a></li>
    <li><a href="{{ route('pricing') }}" aria-current="page">Pricing</a></li>
    @auth('client')
      <li class="nav-account"><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
      <li class="nav-account"><form method="POST" action="{{ route('client.logout') }}">@csrf<input type="hidden" name="redirect" value="home"><button type="submit">Log out</button></form></li>
    @else
      <li class="nav-account"><a href="{{ route('client.login') }}">Sign in</a></li>
      <li class="nav-account"><a href="{{ route('client.register') }}">Sign up</a></li>
    @endauth
  </ul>
  <div class="nav-right">
    @auth('client')
      <a href="{{ route('client.dashboard') }}" class="nav-signin">Dashboard</a>
    @else
      <a href="{{ route('client.login') }}" class="nav-signin">Sign in</a>
      <a href="{{ route('client.register') }}" class="nav-signup">Sign up</a>
    @endauth
    <a href="{{ route('book-demo') }}" class="book-demo"><span class="label">Book Demo</span> ↗</a>
    @auth('client')
      <form method="POST" action="{{ route('client.logout') }}" class="nav-logout">@csrf<input type="hidden" name="redirect" value="home"><button type="submit">Log out</button></form>
    @endauth
    <button type="button" class="nav-burger" id="navBurger" aria-label="Toggle menu" aria-expanded="false" aria-controls="navLinks"><span></span></button>
  </div>
</nav>

{{-- ============ HERO ============ --}}
<header class="hero">
  <div class="hero-bg" aria-hidden="true"><img src="{{ asset('images/homepage/hero-earth.jpg') }}" alt=""></div>
  <div class="hero-inner">
    <span class="eyebrow"><i></i>Every plan starts with a 30-day proof of concept</span>
    <h1>Pricing that<span class="accent">pays for itself.</span></h1>
    <p class="hero-sub">Transparent plans for every stage of growth — from your first 25,000 customers to millions. Mira Premium for AI search is included in every plan.</p>
    <div class="billing" role="group" aria-label="Billing period">
      <button type="button" data-billing="monthly" aria-pressed="true">Monthly</button>
      <button type="button" data-billing="annual" aria-pressed="false">Annual <span class="save">Save 20%</span></button>
    </div>
  </div>
</header>

{{-- ============ PLANS ============ --}}
<section class="plans">
  <div class="wrap">
    <div class="plan-grid">
      @foreach($plans as $key => $p)
        <article class="plan p-{{ $key }} {{ !empty($p['featured']) ? 'featured' : '' }}">
          @if(!empty($p['featured']))<span class="plan-badge">Most popular</span>@endif
          <div class="plan-tier">{{ $p['tier'] }}</div>
          <h3>{{ $p['name'] }}</h3>
          <p class="plan-blurb">{{ $p['blurb'] }}</p>
          @if($p['monthly'])
            <div class="plan-price">
              <span class="cur">$</span>
              <span class="amt" data-monthly="{{ $p['monthly'] }}" data-annual="{{ $p['annual'] }}">{{ number_format($p['monthly']) }}</span>
              <span class="per">/ month</span>
            </div>
            <div class="plan-note" data-monthly-note="Billed monthly · cancel anytime" data-annual-note="Billed annually — ${{ number_format($p['annual'] * 12) }}/year">Billed monthly · cancel anytime</div>
          @else
            <div class="plan-price"><span class="custom">Custom</span></div>
            <div class="plan-note">Tailored to your volume and requirements</div>
          @endif
          <span class="plan-cap">{{ $p['cap'] }}</span>
          <a href="{{ $key === 'enterprise' ? route('book-demo') : $planUrl($key) }}" class="plan-cta">{{ $p['cta'] }} <span aria-hidden="true">→</span></a>
          <div class="plan-feats">
            <div class="lead">{{ $p['lead'] }}</div>
            <ul>
              @foreach($p['features'] as $f)<li>{!! $check !!}{{ $f }}</li>@endforeach
            </ul>
          </div>
          <div class="plan-mira">
            <div class="hd"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.4 6.6L21 11l-6.6 2.4L12 20l-2.4-6.6L3 11l6.6-2.4z"/></svg>Mira Premium included</div>
            <dl>
              <div><dt>Projects</dt><dd>{{ $n($lim($key, 'projects')) }}</dd></div>
              <div><dt>AI visibility runs / mo</dt><dd>{{ $n($lim($key, 'visibility_runs')) }}</dd></div>
              <div><dt>Tracked prompts</dt><dd>{{ $n($lim($key, 'prompts')) }}</dd></div>
            </dl>
          </div>
        </article>
      @endforeach
    </div>
    <p class="trial-line"><b>No credit card required.</b> Connect your platforms, see real predictions on your data, then decide.</p>
  </div>
</section>

{{-- ============ MIRA PREMIUM ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd reveal">
      <h2>Win the AI answer,<span class="accent">not just the click.</span></h2>
      <p>Every plan includes Mira Premium — the workspace that shows how Google and ChatGPT see your brand, and exactly what to fix.</p>
    </div>
    <div class="mira reveal">
      <div>
        <span class="mira-kicker">Mira Premium · included</span>
        <h3>SEO and AI visibility, in one premium workspace.</h3>
        <p>Live search data, your own Google Search Console and Analytics, and weekly checks of how ChatGPT answers the questions your customers ask.</p>
        <ul class="mira-list">
          <li>{!! $check !!}AI visibility in ChatGPT</li>
          <li>{!! $check !!}Share of voice vs competitors</li>
          <li>{!! $check !!}Site audits with AI-written fixes</li>
          <li>{!! $check !!}Site Explorer &amp; backlinks</li>
          <li>{!! $check !!}Google Search Console &amp; GA4</li>
          <li>{!! $check !!}Daily rank tracking</li>
        </ul>
        <a href="{{ route('mira-premium') }}" class="mira-link">Explore Mira Premium <span aria-hidden="true">→</span></a>
      </div>
      <div class="mwin" aria-hidden="true">
        <div class="mwin-bar"><i></i><i></i><i></i><span>xplatforms · Mira Premium</span></div>
        <div class="mwin-body">
          <div class="mwin-tabs"><b class="on">Overview</b><b>Organic</b><b>Backlinks</b><b>AI visibility</b></div>
          <div class="mwin-grid">
            <div class="mcard"><div class="l">Authority <em>Live</em></div><div class="v">88<small>▲ 2</small></div>
              <svg viewBox="0 0 120 34" preserveAspectRatio="none"><polyline points="0,30 20,27 40,28 60,20 80,17 100,12 120,6" fill="none" stroke="#141417" stroke-width="2"/></svg></div>
            <div class="mcard"><div class="l">AI mention rate <em>ChatGPT</em></div><div class="v">62%<small>▲ 8 pts</small></div>
              <svg viewBox="0 0 120 34" preserveAspectRatio="none"><polyline points="0,31 20,30 40,27 60,26 80,22 100,23 120,20" fill="none" stroke="#b9b6ae" stroke-width="1.6" stroke-dasharray="4 4"/><polyline points="0,29 20,26 40,22 60,18 80,14 100,10 120,5" fill="none" stroke="#141417" stroke-width="2"/></svg></div>
          </div>
          <div class="mcard"><div class="l">Share of voice in AI answers <em>latest run</em></div>
            <div class="sov">
              <div class="you"><span>You</span><s><i style="width:46%"></i></s><b>46%</b></div>
              <div><span>Rival A</span><s><i style="width:31%"></i></s><b>31%</b></div>
              <div><span>Rival B</span><s><i style="width:23%"></i></s><b>23%</b></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============ COMPARISON ============ --}}
<section class="sec" id="compare">
  <div class="wrap">
    <div class="sec-hd reveal">
      <h2>Compare<span class="accent">every feature.</span></h2>
      <p>Everything in each plan, side by side — including Mira Premium limits, straight from the product.</p>
    </div>
    <div class="compare reveal" id="compareBox">
      <div class="compare-scroll">
        <table class="ctable">
          <thead>
            <tr>
              <th scope="col">Feature</th>
              <th scope="col"><span class="pn">Starter</span><span class="pp" data-price-label="starter">$499 / mo</span></th>
              <th scope="col" class="hl"><span class="pn">Growth</span><span class="pp" data-price-label="professional">$1,499 / mo</span></th>
              <th scope="col"><span class="pn">Enterprise</span><span class="pp">Custom</span></th>
            </tr>
          </thead>
          <tbody>
            @php $row = 0; @endphp
            @foreach($comparison as $group => $rows)
              @php $hidden = $loop->index >= 4; @endphp
              <tr class="grp {{ str_starts_with($group, 'Mira') ? 'mira-grp' : '' }} {{ $hidden ? 'more' : '' }}"><td colspan="4">{{ $group }}</td></tr>
              @foreach($rows as $r)
                <tr class="{{ $hidden ? 'more' : '' }}">
                  <td>{{ $r[0] }}</td>
                  @foreach([1, 2, 3] as $i)
                    <td class="{{ $i === 2 ? 'hl' : '' }}">
                      @if($r[$i] === true)<svg class="yes" viewBox="0 0 24 24" role="img" aria-label="Included"><path d="M5 12.5l4.2 4.2L19 7"/></svg>
                      @elseif($r[$i] === false)<svg class="no" viewBox="0 0 24 24" role="img" aria-label="Not included"><path d="M7 7l10 10M17 7L7 17"/></svg>
                      @else{{ $r[$i] }}@endif
                    </td>
                  @endforeach
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="compare-foot"><button type="button" class="ghost-btn" id="compareToggle" aria-expanded="false">Show all features <span aria-hidden="true">↓</span></button></div>
    </div>
  </div>
</section>

{{-- ============ ADD-ONS ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd reveal">
      <h2>Extend<span class="accent">your plan.</span></h2>
      <p>Available on any plan. Add exactly what you need, remove what you don't.</p>
    </div>
    <div class="addons">
      @foreach($addons as $a)
        <div class="addon reveal"><h4>{{ $a[0] }}</h4><div class="ap">{{ $a[1] }}<small>{{ $a[2] }}</small></div><p>{{ $a[3] }}</p></div>
      @endforeach
    </div>
  </div>
</section>

{{-- ============ ROI ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="roi reveal">
      <div>
        <span class="k">ROI reality check</span>
        <h3>The average Growth customer sees <b>$280K</b> in year-one revenue impact.</h3>
        <p>From churn reduction, upsell conversion and acquisition efficiency. At $1,499 a month, that is a 15.6× return in the first year.</p>
      </div>
      <div class="roi-stats">
        <div class="c1"><b>15.6×</b><span>Average ROI, year 1</span></div>
        <div class="c2"><b>82 days</b><span>Average time to ROI</span></div>
        <div class="c3"><b>$280K</b><span>Average revenue impact</span></div>
        <div class="c4"><b>97%</b><span>Renew after year 1</span></div>
      </div>
    </div>
  </div>
</section>

{{-- ============ TESTIMONIALS + TRUST ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd reveal">
      <h2>Pricing that makes sense<span class="accent">when you see the returns.</span></h2>
    </div>
    <div class="quotes">
      <figure class="quote reveal">
        <span class="plan-tag">Starter → Growth</span>
        <blockquote>“We started on Starter to test the water. Within 60 days the predictions were so accurate we upgraded to Growth. The upgrade paid for itself in the first month.”</blockquote>
        <figcaption class="who"><span class="av" style="background:#93CEFF">PK</span><div><b>Priya Kapoor</b><span>CPO, MockMaster</span></div><span class="res">↓ 41% churn</span></figcaption>
      </figure>
      <figure class="quote reveal">
        <span class="plan-tag">Growth</span>
        <blockquote>“$1,499 a month felt like a lot until our first expansion revenue report came in. We made back the annual cost in six weeks from upsells our team had missed.”</blockquote>
        <figcaption class="who"><span class="av" style="background:#FFBFFF">AT</span><div><b>Alexandra Tan</b><span>VP Revenue, ScoreMentor</span></div><span class="res">↑ 3.4× MRR</span></figcaption>
      </figure>
      <figure class="quote reveal">
        <span class="plan-tag">Enterprise</span>
        <blockquote>“Our requirements were complex. The team built exactly what we needed, and the first-year ROI justified a three-year commitment.”</blockquote>
        <figcaption class="who"><span class="av" style="background:#f2c14e">DW</span><div><b>David Wong</b><span>CEO, OneAustralia</span></div><span class="res">$5.4M impact</span></figcaption>
      </figure>
    </div>
    <div class="trust reveal">
      <div><svg viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/></svg><div><b>SOC 2 Type II</b><span>Audited annually</span></div></div>
      <div><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.8 2.5 15.2 0 18M12 3c-2.5 2.8-2.5 15.2 0 18"/></svg><div><b>GDPR compliant</b><span>EU data protection</span></div></div>
      <div><svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg><div><b>AES-256</b><span>At rest and in transit</span></div></div>
      <div><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><div><b>30-day POC</b><span>No credit card</span></div></div>
      <div><svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0114-5.3M20 12a8 8 0 01-14 5.3"/><path d="M18 3v4h-4M6 21v-4h4"/></svg><div><b>Cancel anytime</b><span>No lock-in contracts</span></div></div>
    </div>
  </div>
</section>

{{-- ============ FAQ ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd reveal">
      <h2>Pricing questions,<span class="accent">answered.</span></h2>
    </div>
    <div class="faq-grid">
      <aside class="faq-help reveal">
        <h4>Still have questions?</h4>
        <p>Talk to our team about volumes, compliance or a tailored Enterprise plan.</p>
        <a href="{{ route('book-demo') }}">Talk to sales</a>
        <a class="mail" href="mailto:pricing@xplatforms.ai">pricing@xplatforms.ai</a>
      </aside>
      <div class="faq-list reveal">
        @foreach($faqs as $f)
          <div class="faq-item {{ $loop->first ? 'open' : '' }}">
            <button type="button" class="faq-q" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">{{ $f[0] }}<span class="ic"><svg viewBox="0 0 12 12"><path d="M6 1v10M1 6h10"/></svg></span></button>
            <div class="faq-a"><div><p>{{ $f[1] }}</p></div></div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- ============ FINAL CTA ============ --}}
    <div class="cta reveal">
      <h2>Start with your data.<span class="accent">See results in 30 days.</span></h2>
      <p>No credit card. No commitment. Connect your platforms today and get your first predictions before the month is out.</p>
      <div class="cta-row">
        <a href="{{ $signedIn ? route('client.plan') : route('client.register') }}" class="btn-glow">Start your free 30-day trial <span aria-hidden="true">→</span></a>
        <a href="{{ route('simulator') }}" class="ghost-btn">Try the simulator first</a>
      </div>
    </div>
  </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <h4>XPlatforms</h4>
        <p>AI customer intelligence for businesses that want to understand, predict, and act on every customer signal.</p>
        <div class="footer-social"><span>𝕏</span><span>◎</span><span>in</span></div>
      </div>
      <div class="footer-col"><div class="ttl">Product</div><ul><li><a href="{{ route('platform.architecture') }}">Architecture</a></li><li><a href="{{ route('industries') }}">Industries</a></li><li><a href="{{ route('mira-premium') }}">Mira Premium</a></li><li><a href="{{ route('pricing') }}">Pricing</a></li></ul></div>
      <div class="footer-col"><div class="ttl">Company</div><ul><li><a href="{{ route('about') }}">About</a></li><li><a href="{{ route('careers') }}">Careers</a></li><li><a href="{{ route('blog') }}">Blogs</a></li><li><a href="{{ route('contact') }}">Contact</a></li></ul></div>
      <div class="footer-col"><div class="ttl">Resources</div><ul><li><a href="{{ route('case-studies') }}">Case Studies</a></li><li><a href="{{ route('simulator') }}">Simulator</a></li><li><a href="{{ route('security') }}">Security</a></li></ul></div>
      <div class="footer-col"><div class="ttl">Legal</div><ul><li><a href="{{ route('privacy') }}">Privacy</a></li><li><a href="{{ route('terms') }}">Terms</a></li></ul></div>
    </div>
    <div class="footer-bottom">
      <span>© {{ date('Y') }} XPlatforms. All rights reserved.</span>
      <span class="links"><a href="{{ route('privacy') }}">Privacy Policy</a><a href="{{ route('terms') }}">Terms of Service</a><a href="#top">↑ Back to top</a></span>
    </div>
  </div>
  <div class="footer-word">xplatforms</div>
</footer>

<script>
(function () {
  // Mobile menu
  var burger = document.getElementById('navBurger'), links = document.getElementById('navLinks');
  burger.addEventListener('click', function () {
    var open = links.classList.toggle('open');
    burger.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', String(open));
  });

  // Monthly / annual: prices, notes and the comparison header follow the switch.
  var fmt = function (v) { return Number(v).toLocaleString('en-US'); };
  document.querySelectorAll('[data-billing]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var annual = btn.dataset.billing === 'annual';
      document.querySelectorAll('[data-billing]').forEach(function (b) { b.setAttribute('aria-pressed', String(b === btn)); });
      document.querySelectorAll('.amt').forEach(function (el) { el.textContent = fmt(annual ? el.dataset.annual : el.dataset.monthly); });
      document.querySelectorAll('[data-monthly-note]').forEach(function (el) { el.textContent = annual ? el.dataset.annualNote : el.dataset.monthlyNote; });
      var labels = { starter: annual ? 399 : 499, professional: annual ? 1199 : 1499 };
      document.querySelectorAll('[data-price-label]').forEach(function (el) { el.textContent = '$' + fmt(labels[el.dataset.priceLabel]) + ' / mo'; });
    });
  });

  // Comparison: first groups shown, the rest on demand.
  var box = document.getElementById('compareBox'), toggle = document.getElementById('compareToggle');
  toggle.addEventListener('click', function () {
    var open = box.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(open));
    toggle.innerHTML = open ? 'Show fewer features <span aria-hidden="true">↑</span>' : 'Show all features <span aria-hidden="true">↓</span>';
    if (!open) box.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // FAQ accordion: one open at a time.
  document.querySelectorAll('.faq-q').forEach(function (q) {
    q.addEventListener('click', function () {
      var item = q.parentElement, wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(function (i) { i.classList.remove('open'); i.querySelector('.faq-q').setAttribute('aria-expanded', 'false'); });
      if (!wasOpen) { item.classList.add('open'); q.setAttribute('aria-expanded', 'true'); }
    });
  });

  // Reveal on scroll.
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
  }
})();
</script>
</body>
</html>
