# BEHAVR Intelligence Platform — Professor Demo Guide

## What This System Is

BEHAVR is a B2B AI decision platform. It ingests raw user behavioural data (page visits, clicks, purchases, inactivity) and runs it through a multi-layer processing stack that ends in real-time, AI-driven business decisions — revenue opportunities, churn interventions, compliance alerts.

The platform is multi-tenant: each client company sees their own data under their own brand name.

---

## Before the Demo

1. Start XAMPP (Apache + MySQL)
2. Open browser to `http://localhost/xplatform/public`
3. Log in with client credentials
4. You should land on the main dashboard

---

## The 8-Layer Architecture (What to Explain to Your Professor)

The platform models data flowing through 8 conceptual layers — like a signal pipeline from raw event to business action:

| Layer | URL | What it does |
|-------|-----|--------------|
| L1 | `/layers/l1` | Raw event signals — cart abandonment, rage clicks, pricing views |
| L2 | `/layers/l2` | Identity resolution — who are these users? Known vs anonymous |
| L3 | `/layers/l3` | Data processing — feature freshness, ML pipeline health |
| L4 | `/layers/l4` | Behavioural scoring — hot buyers, at-risk, loyal, reactivation |
| L5 | `/layers/l5` | Decision engine — expected value, revenue estimation, offer selection |
| L6 | `/layers/l6` | Decision routing — signal conflicts, frequency caps, budget ceilings |
| L7 | `/layers/l7` | Application layer — executive alerts, 1-click offers, CSM queue |
| L8 | `/layers/l8` | Governance & security — GDPR, access violations, audit trail |

---

## The 3-Panel Decision Centre (How Every Layer Works)

Every layer uses the same interaction pattern. Walk your professor through this once on any layer:

**Left panel — Scenario list**
- Shows 4 business scenarios for that layer
- Each has a colour-coded urgency bar (red = critical, amber = warning, green = opportunity)
- Click any scenario to load it

**Centre panel — Decision detail**
- KPI grid: 4 key metrics with progress bars, all pulled from the live database
- Alert banner: explains what the AI detected and why it matters
- Decision section: recommended action vs alternative, with predicted outcome
- Outcome section: what will happen if you take the recommended action
- Action buttons: CTAs to trigger the action — click one to load the right panel

**Right panel — Affected user records**
- Real user profiles from the `BehavioralProfile` table
- Each card shows: name, company, industry, tier (Enterprise/Business/Growth/Starter), score
- Tags explain why this user is in this cohort
- Click a card to expand it: full field details + event timeline + action buttons

---

## Recommended Demo Flow (10–15 minutes)

### Step 1 — Context (2 min)
Open the main dashboard. Point out:
- The BEHAVR logo and tenant name (top left)
- "● Live" indicator — all data is from a real database, not static
- Navigation menu showing all 8 layers

### Step 2 — L4 Behavioural Scoring (3 min)
Go to `/layers/l4` — this is the core of the system.

1. Click **Hot Buyers** scenario (left panel)
2. Show the KPI grid — these are real averages from the database
3. Read the banner: "X users show buying intent ≥80 and readiness ≥75"
4. Click **"Send personalised offer now"** CTA
5. Right panel loads with real user cards — click one to expand
6. Show the fields: Intent score, Buying readiness, Price sensitivity, Offer assigned
7. Explain: "The AI computed all of this from behavioural signals — no manual tagging"

Switch to **At-Risk** scenario:
- Show churn probability and ARR at risk (real calculated values)
- Explain: "This tells us exactly how much revenue we'll lose if we don't act"

### Step 3 — L2 Identity Resolution (2 min)
Go to `/layers/l2`

1. Click **Low-Confidence Profiles** scenario
2. Show: "X profiles have trust score < 50 — the system doesn't fully know who they are"
3. Click **Anon → Known** scenario
4. Explain: "These anonymous sessions have high engagement — the system wants to capture their identity at the right moment"

Key talking point: *"Before you can personalise, you need to know who you're personalising for. L2 solves that."*

### Step 4 — L6 Decision Routing (2 min)
Go to `/layers/l6`

1. Click **Signal Conflicts** scenario
2. Explain: "This user has high intent AND high churn risk at the same time — which signal wins?"
3. Show the Decision section: "AI priority rule: churn intervention takes precedence"
4. Click **Frequency Cap Suppressions**
5. Explain: "These users are frustrated — sending another message would accelerate their exit"

Key talking point: *"This is the layer that prevents the AI from doing the wrong thing at the right time."*

### Step 5 — L8 Governance (2 min)
Go to `/layers/l8`

1. Click **GDPR Issues** scenario
2. Show: "X profiles have been inactive for 30+ days — GDPR data minimisation applies"
3. Click **Access Violations**
4. Show: "X profiles have trust scores below 25 — potential compromised accounts"
5. Click **AI Audit Trail**
6. Explain: "Every AI decision is logged. GDPR Article 22 requires explainable automated decisions — this is how we comply."

Key talking point: *"AI without governance is a liability. L8 makes every decision auditable and compliant."*

### Step 6 — Architecture summary (1 min)
Bring up any layer and point to the header: "L2 / L3 / etc."

Explain: "Data flows through all 8 layers in sequence — from raw event capture at L1 to governance logging at L8. Each layer adds intelligence. The Decision Centre visualises every decision the AI is making, in real time, with the reasoning visible."

---

## Key Technical Points to Mention

- **Database**: MySQL via Laravel Eloquent ORM. The `behavioral_profiles` table holds all scores. Every number on screen is a live query result, not hard-coded.
- **Multi-tenant**: Each client login shows their own data. Auth is handled by Laravel's guard system.
- **Score fields**: `intent_score`, `churn_score`, `engagement_score`, `loyalty_score`, `trust_score`, `buying_readiness`, `dropoff_risk`, `reactivation_potential`, `overall_score` — all computed from behavioural events.
- **AI logic**: The Decision Engine (L5) computes expected value per user using a weighted model. L6 applies priority rules when signals conflict.
- **Security**: All user data rendered via the `h()` function (XSS escaping). Laravel CSRF protection on all forms.

---

## If Something Looks Empty

If a scenario shows "0 records" or no user cards appear:
- The filter for that cohort returned no results from the database
- This can happen with small seeded datasets
- Simply switch to a different scenario — the other cohorts will have data
- L4 (Hot Buyers, At-Risk) always has the most data as it uses the widest filters

---

## Questions Your Professor Might Ask

**Q: Is this real-time?**
A: Yes — every page load queries the live database. If a new user profile was added 1 second ago, it appears immediately.

**Q: Where does the behavioural data come from?**
A: In production, from the client's website via event tracking (JavaScript SDK). In this demo, from seeded data in the `behavioral_profiles` table.

**Q: How is the scoring computed?**
A: Scores are stored as pre-computed values updated by a scoring pipeline. The Decision Centre reads those values and applies business rules on top to produce the scenarios and recommendations.

**Q: What makes this different from a regular dashboard?**
A: A dashboard shows you what happened. This shows you what to do about it — with a recommended action, predicted outcome, and affected user list pre-loaded. It's a decision support tool, not just a reporting tool.

**Q: Is this GDPR compliant?**
A: L8 is specifically built for this. Every AI decision is logged with an audit trail (GDPR Art.22), data retention policies are enforced, and right-to-erasure requests are flagged for review.
