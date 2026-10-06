{{-- resources/views/client/mock-master-helper.blade.php --}}
@extends('layouts.platform')

@section('title', 'Mock Master Helper')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')

@php
$client     = auth('client')->user();
$clientName = $client?->company_name ?? 'Acme Retail';
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $clientName), 0, 2))));

// Real data (mkStudents/mkKpis/mkSegments/mkInsights/slProspects/slClose/
// chAtRisk/chWatchlist/chRootCauses) is computed by MockMasterDataService
// and passed in from the route, querying only the mm_* tables. A/B test
// tabs stay an honest "no data source yet" placeholder — there is no A/B
// test table in the mm_* schema, so nothing is fabricated there. Scripts/
// objections/offers below are advisory copy (not data claims), kept static.

// ── Marketing ──────────────────────────────────────────────────────────
$mkSteps = [
    ['key' => 'campaign',    'label' => 'Campaign'],
    ['key' => 'performance', 'label' => 'Performance'],
    ['key' => 'audience',    'label' => 'Audience'],
    ['key' => 'insights',    'label' => 'Insights'],
    ['key' => 'abtest',      'label' => 'A/B test'],
];

// $mkPrompts is passed in from the route — sourced from agents_pre_defined_prompts
// where is_mock_master = 1 (see routes/web.php).

// ── Sales ──────────────────────────────────────────────────────────────
$slSteps = [
    ['key' => 'today',      'label' => 'Today'],
    ['key' => 'accounts',   'label' => 'Accounts'],
    ['key' => 'scripts',    'label' => 'Scripts'],
    ['key' => 'objections', 'label' => 'Objections'],
    ['key' => 'close',      'label' => 'Close & grow'],
];

// Sales · Scripts — a ready-to-use library the rep can read on a call or
// copy into an email/WhatsApp. Advisory copy (not data claims). Words in
// [square brackets] are placeholders the rep fills in before using.
$slScriptLibrary = [
    'call' => [
        ['title' => 'First call after a free mock test', 'when' => 'Student took a free mock test in the last few days',
         'body' => "Opener: \"Hi [Student name], this is [Your name] from Mock Master. I saw you took a free PTE mock test on [date] — do you have two minutes to go through how it went?\"\nDiscover: \"What overall score are you aiming for, and when is your exam booked?\"\nInsight: \"Your mock scored [score]. The gap to your target of [target score] is mostly in [weakest section].\"\nRecommend: \"The [package name] gives you [number] full mock tests with score reports, so you can practise that section and track progress before [exam date].\"\nClose: \"Shall I send you the link now so you can start your next test today?\""],
        ['title' => 'Strong free score — ready to convert', 'when' => 'Free mock score is close to or above the target', 'body' => "Opener: \"Hi [Student name], [Your name] from Mock Master. Congratulations — your free mock scored [score], which is really close to your [target score] target.\"\nDiscover: \"How confident are you feeling about exam day?\"\nInsight: \"At this stage, most students lose points through timing and nerves, not knowledge. More full-length practice under exam conditions is what locks the score in.\"\nRecommend: \"The [package name] gives you [number] more full tests before [exam date], so exam day feels familiar.\"\nClose: \"Would you like to start today so you have a buffer before the exam?\""],
        ['title' => 'Low score — build a practice plan', 'when' => 'Free mock score is well below the target', 'body' => "Opener: \"Hi [Student name], [Your name] from Mock Master. Thanks for taking the free mock — I wanted to help you make sense of the result.\"\nReassure: \"A first score of [score] is a common starting point. The useful part is that it shows exactly where to focus.\"\nInsight: \"Your biggest opportunity is [weakest section]. Improving that alone moves your overall score the most.\"\nRecommend: \"With [package name] you can take a test every [week/few days], see your section scores, and watch the gap close before [exam date].\"\nClose: \"Shall I set you up so you can take your next mock this week?\""],
        ['title' => 'Registered but never took a test', 'when' => 'Student signed up but has no mock test yet', 'body' => "Opener: \"Hi [Student name], this is [Your name] from Mock Master. You signed up on [date] — I'm calling to make sure you got access to your free mock test.\"\nDiscover: \"Is your PTE exam booked yet? What score do you need?\"\nValue: \"The free test gives you a real score and shows which section needs the most work. It takes about [duration].\"\nHelp: \"Is anything stopping you from starting — time, login, not sure where to begin?\"\nClose: \"Could you take it today or tomorrow? I'll call you after to go through the result.\""],
        ['title' => 'Exam date coming up soon', 'when' => 'Exam is within the next 2–4 weeks', 'body' => "Opener: \"Hi [Student name], [Your name] from Mock Master. You mentioned your PTE exam is on [exam date] — that's only [number] weeks away.\"\nDiscover: \"How many full mock tests have you done so far?\"\nInsight: \"In the last few weeks, full-length tests under exam timing make the biggest difference, because they build pacing and stamina.\"\nRecommend: \"With [package name] you could fit in [number] full tests before the exam — about [number] a week.\"\nClose: \"Let's get the first one booked for this week. Can I send the link now?\""],
        ['title' => 'Price-sensitive / comparing options', 'when' => 'Student says it is expensive or is comparing providers', 'body' => "Acknowledge: \"That's completely fair, [Student name] — it's important to spend wisely on exam prep.\"\nReframe: \"The [package name] works out to about [price per test] per mock test. Compare that to the cost of re-sitting the PTE if the score falls short.\"\nDiscover: \"What matters most to you — the number of tests, the score reports, or flexibility on dates?\"\nOption: \"If budget is the concern, [smaller package / current offer] still gives you [number] full tests.\"\nClose: \"Which option feels right for you?\""],
        ['title' => 'Package expiring — renewal call', 'when' => 'Active package expires in the next 7–14 days', 'body' => "Opener: \"Hi [Student name], [Your name] from Mock Master. A quick call — your [package name] expires on [expiry date] and I didn't want you to lose access before your exam.\"\nDiscover: \"How has your practice been going? Is your exam date still [exam date]?\"\nInsight: \"Your recent mock scores went from [first score] to [latest score] — renewing keeps that momentum going.\"\nRecommend: \"Renewing now keeps your progress and history in one place, and you can continue straight away.\"\nClose: \"Shall I arrange the renewal so there's no gap?\""],
        ['title' => 'Inactive student — check-in', 'when' => 'No mock test or login for 14+ days', 'body' => "Opener: \"Hi [Student name], [Your name] from Mock Master. I noticed you haven't taken a mock test in a couple of weeks — just checking everything's okay.\"\nDiscover: \"Has your exam date changed, or has it been hard to find the time?\"\nHelp: \"Even one mock test a week keeps your pacing sharp. Would a set day each week help?\"\nOffer: \"You still have [number] tests left on your package until [expiry date].\"\nClose: \"Could you fit one in this week? I'll check your result with you afterwards.\""],
        ['title' => 'Voicemail / no answer', 'when' => 'Student did not pick up', 'body' => "\"Hi [Student name], this is [Your name] from Mock Master about your PTE preparation. I'd love to help you plan your practice before your exam. I'll send you a WhatsApp as well — or call me back on [your number] whenever suits you. Speak soon!\"\nAfter the call: send the \"Couldn't reach you\" WhatsApp message."],
    ],
    'email' => [
        ['title' => 'Your free mock test result — next steps', 'when' => 'Within 24 hours of a free mock test', 'subject' => 'Your PTE mock result: [score] — here\'s how to reach [target score]',
         'body' => "Hi [Student name],\n\nThanks for taking your free PTE mock test with Mock Master. Your overall score was [score].\n\nThe good news: your result shows exactly where to focus. Your biggest opportunity is [weakest section], which has the most impact on your overall score.\n\nOur [package name] includes [number] full-length mock tests with score reports, so you can practise, track your section scores and see your progress before [exam date].\n\nStart your next mock here: [link]\n\nAny questions? Just reply to this email.\n\nBest regards,\n[Your name]\nMock Master"],
        ['title' => 'Welcome — take your first free test', 'when' => 'Registered but no mock test yet', 'subject' => 'Your free PTE mock test is ready, [Student name]',
         'body' => "Hi [Student name],\n\nWelcome to Mock Master! Your free PTE mock test is ready whenever you are.\n\nIt takes about [duration] and gives you:\n• A real overall score\n• A breakdown of where you're strongest and weakest\n• A clear starting point for your preparation\n\nTake your free test: [link]\n\nIf you'd like help planning your practice, just reply and I'll get in touch.\n\nBest regards,\n[Your name]\nMock Master"],
        ['title' => 'You\'re close to your target', 'when' => 'Free score is near the target', 'subject' => 'You\'re only [points] points from your target score',
         'body' => "Hi [Student name],\n\nYour mock test score of [score] puts you just [points] points away from your target of [target score]. That's a great position to be in.\n\nAt this stage, regular full-length practice under exam conditions is what turns \"close\" into \"done\" — it builds timing, confidence and consistency.\n\nThe [package name] gives you [number] more full mock tests before [exam date].\n\nContinue your preparation: [link]\n\nBest regards,\n[Your name]\nMock Master"],
        ['title' => 'Exam soon — a practice plan', 'when' => 'Exam is within the next few weeks', 'subject' => '[number] weeks to your PTE exam — your practice plan',
         'body' => "Hi [Student name],\n\nYour PTE exam is on [exam date], about [number] weeks away. Here's a simple plan for the time you have left:\n\n• Week 1: one full mock test, then review your weakest section\n• Week 2: two full mock tests under exam timing\n• Final week: one last full mock, then rest before exam day\n\nThe [package name] covers all of this, with score reports after every test.\n\nGet started: [link]\n\nGood luck — you've got this!\n[Your name]\nMock Master"],
        ['title' => 'Follow-up after a call', 'when' => 'Same day as a sales call', 'subject' => 'Great speaking with you, [Student name]',
         'body' => "Hi [Student name],\n\nThanks for your time on the phone today. As promised, here's a quick summary:\n\n• Your target score: [target score]\n• Your exam date: [exam date]\n• Recommended package: [package name] — [number] full mock tests\n\nYou can get started here: [link]\n\nI'll check in after your next mock test to go through the result with you.\n\nBest regards,\n[Your name]\nMock Master\n[your number]"],
        ['title' => 'Limited-time offer', 'when' => 'Only when an approved offer or coupon is running', 'subject' => 'A special offer on your PTE preparation',
         'body' => "Hi [Student name],\n\nFor a limited time, you can get [offer details] on [package name] with the code [coupon code]. The offer ends on [end date].\n\nIt's a good moment to lock in your practice before your exam on [exam date].\n\nClaim the offer: [link]\n\nBest regards,\n[Your name]\nMock Master"],
        ['title' => 'Package expiring in 7 days', 'when' => 'Active package expires within a week', 'subject' => 'Your Mock Master package expires on [expiry date]',
         'body' => "Hi [Student name],\n\nA quick reminder that your [package name] expires on [expiry date].\n\nYou've made real progress — your mock scores went from [first score] to [latest score]. Renewing keeps your access, score history and momentum going right up to your exam.\n\nRenew in one step: [link]\n\nIf your exam date has changed, reply and I'll suggest the best option.\n\nBest regards,\n[Your name]\nMock Master"],
        ['title' => 'We miss you — come back', 'when' => 'No activity for 14+ days', 'subject' => 'Is your PTE preparation still on track, [Student name]?',
         'body' => "Hi [Student name],\n\nWe noticed you haven't taken a mock test in a little while. Life gets busy — that's completely normal.\n\nEven one mock test a week keeps your skills and timing sharp. You still have [number] tests available until [expiry date].\n\nPick up where you left off: [link]\n\nIf something's getting in the way, reply and let me know — I'm happy to help.\n\nBest regards,\n[Your name]\nMock Master"],
        ['title' => 'Feedback and referral request', 'when' => 'After a good score or a finished package', 'subject' => 'How did your preparation go, [Student name]?',
         'body' => "Hi [Student name],\n\nCongratulations on your progress with Mock Master! We'd love to hear how your preparation went — your feedback helps us improve: [feedback link]\n\nAnd if you have friends preparing for the PTE, feel free to share Mock Master with them: [referral link]\n\nThank you for practising with us, and best of luck!\n\n[Your name]\nMock Master"],
    ],
    'whatsapp' => [
        ['title' => 'After a free mock test', 'when' => 'Same day or next day after a free test',
         'body' => "Hi [Student name] 👋 It's [Your name] from Mock Master. Well done on your free PTE mock — you scored [score]! Want me to show you the quickest way to reach [target score]? I can send your next test link here."],
        ['title' => 'Not started yet', 'when' => 'Registered, no test taken', 'body' => "Hi [Student name], [Your name] from Mock Master here 😊 Your free PTE mock test is ready and takes about [duration]. It'll show your score and what to focus on. Start here: [link]"],
        ['title' => 'High score — upgrade nudge', 'when' => 'Free score is near the target', 'body' => "Hi [Student name]! Your mock score of [score] is really close to your [target score] target 🎯 A few more full tests before [exam date] can lock it in. Shall I share the [package name] details?"],
        ['title' => 'Exam coming up', 'when' => 'Exam within 2–4 weeks', 'body' => "Hi [Student name], your PTE exam is on [exam date] — [number] weeks to go! ⏳ Full mock tests now are the best way to build timing and confidence. Want me to set you up with a plan?"],
        ['title' => 'Couldn\'t reach you', 'when' => 'After a missed call', 'body' => "Hi [Student name], I just tried calling about your PTE preparation 📞 When's a good time for a quick 2-minute chat? Or feel free to message me here."],
        ['title' => 'Limited-time offer', 'when' => 'Only when an approved offer is running', 'body' => "Hi [Student name]! 🎉 For a limited time, get [offer details] on [package name] with code [coupon code]. Valid until [end date]. Want the link?"],
        ['title' => 'Renewal reminder', 'when' => 'Package expires within 7 days', 'body' => "Hi [Student name], a quick heads-up: your [package name] expires on [expiry date]. You've improved from [first score] to [latest score] 👏 Want me to renew it so you keep practising without a break?"],
        ['title' => 'Inactive check-in', 'when' => 'No activity for 14+ days', 'body' => "Hi [Student name], hope you're doing well! It's been a little while since your last mock test. You still have [number] tests until [expiry date] — fancy taking one this week? 💪"],
        ['title' => 'Referral ask', 'when' => 'After a good score or a finished package', 'body' => "Hi [Student name]! 🎉 Congrats on your progress with Mock Master. If any friends are preparing for the PTE, feel free to share this with them: [referral link]. Thank you!"],
    ],
];

// Sales · Objections — common student objections grouped by theme, each with
// what it usually means and a ready-to-say response. Advisory copy (not data
// claims). Words in [square brackets] are placeholders the rep fills in.
$slObjectionGroups = [
    'price' => [
        'label' => 'Price & value',
        'items' => [
            ['q' => "It's too expensive.", 'means' => "They don't yet see the value, or the cost of failing the exam isn't top of mind.",
             'a' => "Acknowledge: \"I understand — exam prep is a real investment, and you want to spend wisely.\"\nReframe: \"The [package name] works out to about [price per test] per full mock test. A PTE re-sit costs [exam fee] plus weeks of waiting.\"\nRespond: \"Each mock shows exactly which section is costing you points, so you only practise what moves your score.\"\nAsk: \"If the price fitted your budget, would this be the right package for your target of [target score]?\""],
            ['q' => "I can find free mock tests online.", 'means' => "They doubt that paid tests are better than free ones.",
             'a' => "Acknowledge: \"You're right, there are free materials out there, and some are useful.\"\nReframe: \"The difference is how close the test feels to the real exam, and whether you get a score you can trust.\"\nRespond: \"Our full mock tests follow the PTE format and timing and give you a score report after every test, so you can track real progress instead of guessing.\"\nAsk: \"Your free mock with us scored [score]. Would it help to see how that changes after two or three more full tests?\""],
            ['q' => "I'll just buy one test, not a package.", 'means' => "They want to limit risk or spend less up front.",
             'a' => "Acknowledge: \"Starting small makes sense if you're unsure.\"\nReframe: \"One test tells you where you are. Improvement comes from testing, fixing a weak area, and testing again.\"\nRespond: \"Most students need [number] full tests to see a steady rise. A package costs less per test than buying them one by one.\"\nAsk: \"How many weeks do you have before [exam date]? Let's work out how many tests fit that time.\""],
            ['q' => "Can I get a discount?", 'means' => "They're interested but price-sensitive, or just testing for a better deal.",
             'a' => "Acknowledge: \"Fair question — everyone likes a good deal.\"\nRespond: \"[If an approved offer is running:] Right now there's [offer details] with code [coupon code] until [end date]. [If not:] Our current price is already the best rate, but the [smaller package] is a lower-cost way to start.\"\nReframe: \"The bigger saving is passing the first time and not paying for a re-sit.\"\nAsk: \"Shall I apply that and send you the link?\""],
            ['q' => "Is paying for practice tests even worth it?", 'means' => "They're unsure that mock tests make a real difference to the result.",
             'a' => "Acknowledge: \"It's smart to ask that before spending anything.\"\nReframe: \"The PTE is very timed and format-driven. Students often lose points from pacing and unfamiliar question types, not from lack of English.\"\nRespond: \"Full mock tests let you practise exactly that, and each score report shows what to fix next.\"\nAsk: \"What happened in your free mock — did you run short of time in any section?\""],
        ],
    ],
    'timing' => [
        'label' => 'Timing & readiness',
        'items' => [
            ['q' => "My exam date isn't confirmed yet.", 'means' => "They don't want to pay for something they might not use in time.",
             'a' => "Acknowledge: \"That's sensible — you don't want a package to run out before your exam.\"\nRespond: \"Taking a mock now shows how much preparation you need, which helps you pick the right exam date.\"\nOption: \"[Package with a longer validity / flexible package] gives you time until [expiry], so you're covered even if the date moves.\"\nAsk: \"Roughly which month are you aiming for? Let's choose the package that covers it.\""],
            ['q' => "I want to study first, then take mock tests.", 'means' => "They think mocks are only for the end of preparation.",
             'a' => "Acknowledge: \"Studying first feels natural.\"\nReframe: \"Without a mock, it's hard to know what to study. Many students spend weeks on sections that were already fine.\"\nRespond: \"A mock now gives you a baseline and a focus list. Then you study smarter and test again to see the gain.\"\nAsk: \"Could you take one mock this week, just to set your starting point?\""],
            ['q' => "My exam is months away — I'll buy later.", 'means' => "They don't feel urgency yet.",
             'a' => "Acknowledge: \"Great that you're planning ahead — that's an advantage.\"\nReframe: \"The students who improve the most usually test early, so there's time to fix weak areas without cramming.\"\nRespond: \"One mock every [week/two weeks] keeps steady progress and stops last-minute panic.\"\nAsk: \"Would a light plan — one mock every couple of weeks — work for you?\""],
            ['q' => "My exam is in a few days — it's too late.", 'means' => "They think practice can't help now.",
             'a' => "Acknowledge: \"With only a few days left, every hour counts.\"\nReframe: \"This is exactly when one or two full mocks help most — they settle your timing and nerves for exam day.\"\nRespond: \"You don't need a big package. [Short package / single test] gets you full exam-condition practice this week.\"\nAsk: \"Can you do a full mock tomorrow, so exam day feels familiar?\""],
            ['q' => "I don't have time to practise right now.", 'means' => "They're busy with work or study, or overwhelmed.",
             'a' => "Acknowledge: \"That's completely understandable — balancing everything is hard.\"\nReframe: \"You don't need hours every day. One full mock a week keeps you on track.\"\nRespond: \"You can take tests whenever suits you, [early morning / weekends], and the score report shows where to spend your limited time.\"\nAsk: \"Which day of the week is usually quietest for you?\""],
        ],
    ],
    'trust' => [
        'label' => 'Trust & results',
        'items' => [
            ['q' => "Are your scores accurate compared to the real PTE?", 'means' => "They worry the mock score will mislead them.",
             'a' => "Acknowledge: \"Good question — a practice score is only useful if you can trust it.\"\nRespond: \"Our mock tests follow the PTE format, timing and scoring scale, so the result gives a realistic picture of where you stand.\"\nReframe: \"The most useful part is the trend: if your mock scores rise test after test, your real readiness is rising too.\"\nAsk: \"Would you like to compare your next mock with your first score of [score]?\""],
            ['q' => "My free mock score was low — I'm discouraged.", 'means' => "Confidence has dropped, and they may give up.",
             'a' => "Acknowledge: \"I hear you. A low first score can feel disappointing.\"\nReframe: \"A first mock is a starting point, not a verdict. It shows exactly where the quickest gains are.\"\nRespond: \"Your biggest opportunity is [weakest section]. Students who focus there and test again usually see their score move.\"\nAsk: \"Shall we set a realistic next target — say [next score] — for your next mock?\""],
            ['q' => "I failed the PTE before — practice didn't help.", 'means' => "Past effort didn't pay off, so they doubt it will now.",
             'a' => "Acknowledge: \"That's frustrating, and I appreciate you sharing it.\"\nDiscover: \"What score did you get, and which section held you back?\"\nRespond: \"Repeating general practice often isn't enough. What helps is a full mock, fixing the specific weak section, then re-testing to confirm it improved.\"\nAsk: \"Would you try one full mock so we can pinpoint what changed since your last attempt?\""],
            ['q' => "How do I know it'll actually improve my score?", 'means' => "They want proof before committing.",
             'a' => "Acknowledge: \"Totally fair — you want to see results, not promises.\"\nRespond: \"Each mock gives you a score, so you'll see your own progress in numbers, not just take our word for it.\"\nReframe: \"No practice can guarantee a score, but regular full tests are how you find and fix the points you're losing.\"\nAsk: \"How about we track your next [number] mocks and review the trend together?\""],
        ],
    ],
    'alternatives' => [
        'label' => 'Alternatives',
        'items' => [
            ['q' => "I already use another platform or coaching.", 'means' => "They don't want to pay twice or switch.",
             'a' => "Acknowledge: \"That's great — it means you're serious about your preparation.\"\nReframe: \"Many students use us alongside coaching, because extra full-length mocks are where coaching often runs short.\"\nRespond: \"Our tests give you a second, independent score check before exam day.\"\nAsk: \"How many full mock tests does your current option include?\""],
            ['q' => "I'll practise with YouTube and free material.", 'means' => "They think content alone is enough.",
             'a' => "Acknowledge: \"There's some great free content, and it's useful for learning techniques.\"\nReframe: \"Videos teach the strategy. A full timed test shows whether you can apply it under pressure.\"\nRespond: \"Use the free material to learn, and our mocks to measure. That combination is what moves the score.\"\nAsk: \"When did you last do a full test under real exam timing?\""],
            ['q' => "My friend has an account — I'll use theirs.", 'means' => "They want to avoid paying.",
             'a' => "Acknowledge: \"I get it — saving money matters.\"\nRespond: \"Accounts are personal, so your scores and history would mix with your friend's, and you couldn't track your own progress.\"\nReframe: \"Your own account keeps your results separate, so every report reflects only your preparation.\"\nAsk: \"Shall I find the most affordable package that still covers your exam date?\""],
        ],
    ],
    'decision' => [
        'label' => 'Decision & logistics',
        'items' => [
            ['q' => "I need to ask my parents or partner first.", 'means' => "Someone else shares the decision or the cost.",
             'a' => "Acknowledge: \"Of course — it makes sense to decide together.\"\nRespond: \"Shall I send you a short summary — your current score, your target, and the package that fits — so it's easy to explain?\"\nReframe: \"The main points are: it targets your weak sections, and it helps avoid paying for a re-sit.\"\nAsk: \"When would be a good time for me to follow up — tomorrow evening?\""],
            ['q' => "Send me the details — I'll think about it.", 'means' => "Polite delay; there is often an unspoken concern.",
             'a' => "Acknowledge: \"Happy to send everything over.\"\nDiscover: \"Just so I send the right information — is it more about the price, the timing, or whether it'll help your score?\"\nRespond: Answer that concern directly, then send the follow-up email from the Scripts tab.\nAsk: \"I'll check in on [day]. Does that work?\""],
            ['q' => "I'm not comfortable paying online.", 'means' => "Concern about payment safety, or a past failed payment.",
             'a' => "Acknowledge: \"That's a fair concern — you should feel safe paying online.\"\nRespond: \"Payments go through [payment provider], and you'll get a confirmation as soon as it goes through.\"\nHelp: \"If a payment failed before, I can stay on the line while you try again, or share [alternative payment option].\"\nAsk: \"Would you like to do it together now?\""],
            ['q' => "I'm not sure PTE is the right exam for me.", 'means' => "They're still comparing tests (for example IELTS) or are unsure about the requirement.",
             'a' => "Acknowledge: \"It's worth getting that right before you start.\"\nDiscover: \"Which university, visa or employer is the score for? Do they accept PTE?\"\nRespond: \"If PTE is accepted, a free mock is the quickest way to see how comfortable you are with the format.\"\nAsk: \"Would you like to take the free mock first, and then decide?\""],
        ],
    ],
];

// $slPrompts is passed in from the route — sourced from agents_pre_defined_prompts
// where is_mock_master = 1 (see routes/web.php).

// ── Customer Retention ────────────────────────────────────────────────
$chSteps = [
    ['key' => 'savefirst', 'label' => 'Save first'],
    ['key' => 'rootcause', 'label' => 'Root cause'],
    ['key' => 'offers',    'label' => 'Offers'],
    ['key' => 'watchlist', 'label' => 'Watchlist'],
    ['key' => 'renew',     'label' => 'Renew & win back'],
    ['key' => 'abtest',    'label' => 'A/B test'],
];

$chOffers = [
    ['name' => 'One free extra mock test',        'meta' => 'For students inactive 10-20 days'],
    ['name' => '15% renewal discount',              'meta' => 'For high-value students at risk'],
    ['name' => 'Free 15-min coaching call',          'meta' => 'For students with declining scores'],
];

// $chPrompts is passed in from the route — sourced from agents_pre_defined_prompts
// where is_mock_master = 1 (see routes/web.php).

$agents = [
    ['key' => 'mk', 'letter' => 'M', 'label' => 'Marketing'],
    ['key' => 'sl', 'letter' => 'S', 'label' => 'Sales'],
    ['key' => 'ch', 'letter' => 'R', 'label' => 'Customer Retention'],
];

// Presentation-only helpers for the tables: an initial avatar whose colour
// comes from the name, and a pill style picked from a stage label.
$mmAvColors = ['#3b5bdb', '#7c5cfc', '#f97316', '#1e3a8a', '#0284c7', '#334155', '#16a34a', '#db2777', '#0d9488'];
$mmAvColor  = fn ($name) => $mmAvColors[crc32((string) $name) % count($mmAvColors)];
$mmInitial  = fn ($name) => mb_strtoupper(mb_substr(trim((string) $name), 0, 1)) ?: '?';
$mmStageKind = fn ($label) => preg_match('/won|active|renew/i', (string) $label) ? 'good'
    : (preg_match('/lost|expired|fail/i', (string) $label) ? 'bad'
    : (preg_match('/decision|bought/i', (string) $label) ? 'violet' : 'info'));
@endphp

<div class="bh-page flex flex-col h-full overflow-hidden">

    {{-- Page Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Mock Master Helper</h1>
            <p class="text-xs text-gray-400 mt-0.5">Guided playbook for your Mock Master student data</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="mmSyncData(this)" id="mmSyncBtn" title="Re-fetch the latest Mock Master data"
               class="flex items-center gap-2 px-3 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors text-xs font-medium">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-2.64-6.36M21 4v6h-6"/></svg>
                <span>Sync Data</span>
            </button>
            <button type="button" onclick="toggleSidebarCollapse()" id="mmFullBtn" title="Collapse sidebar"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
            </button>
            <a href="{{ route('client.dashboard') }}"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
               title="Dashboard">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <div class="relative" id="mmAvatarWrap">
                <button onclick="var d=document.getElementById('mmDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold hover:bg-cyan-600 transition-colors">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="mmDropdown" class="hidden absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-50">
                        <p class="text-xs font-semibold text-gray-900 truncate">{{ $clientName }}</p>
                        <p class="text-[10px] text-gray-400">Client Account</p>
                    </div>
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile Settings
                    </a>
                    <form method="POST" action="{{ route('client.logout') }}" class="border-t border-gray-50">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-500 hover:bg-red-50 transition-colors text-left">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Helper Interface Card --}}
    <div class="flex-1 overflow-hidden p-6">
    <div id="bhRoot" class="h-full flex flex-col overflow-hidden" data-agent="mk">

        {{-- Selector bar: agent tabs --}}
        <div class="bar">
            <div class="atabs">
                @foreach($agents as $i => $a)
                <button type="button" class="atab {{ $i === 0 ? 'on' : '' }}" id="mmAgentTab-{{ $a['key'] }}" onclick="mmSetAgent('{{ $a['key'] }}')">
                    <span class="amono">
                        @if($a['key'] === 'mk')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l15-6v14L3 13z"/><path d="M3 11v2"/><path d="M7.5 13.8V18a2 2 0 0 0 4 0v-3"/><path d="M21 9v6"/></svg>
                        @elseif($a['key'] === 'sl')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20v-6"/><path d="M10 20V10"/><path d="M14 20V4"/><path d="M18 20v-9"/></svg>
                        @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"/><path d="M18 14.2a6.5 6.5 0 0 1 3.5 5.8"/></svg>
                        @endif
                    </span><span class="a2">{{ $a['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- MARKETING --}}
        <div class="dash on" id="mmDash-mk" data-dash="mk">
            <aside class="dash-left">
                <div class="left-resize" role="separator" aria-orientation="vertical" aria-label="Resize steps panel (arrow keys)" tabindex="0" title="Drag to resize — drag left to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmLeft('restore')" title="Expand steps" aria-label="Expand steps panel">&raquo;</button>
                    <span class="col-rail-label">Steps</span>
                </div>
                <div class="g-body">
                    @foreach($mkSteps as $i => $step)
                    <div class="flowst {{ $i === 0 ? 'cur' : '' }}" data-step="mk-{{ $step['key'] }}" onclick="mmSelectStep('mk','{{ $step['key'] }}')">
                        <div class="flowst-dot">{{ $i + 1 }}</div>
                        <div class="flowst-t">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs">
                    @foreach($mkSteps as $i => $step)
                    <button type="button" class="dvt {{ $i === 0 ? 'on' : '' }}" data-tab="mk-{{ $step['key'] }}" onclick="mmSelectStep('mk','{{ $step['key'] }}')">{{ strtoupper($step['label']) }}</button>
                    @endforeach
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="mk-campaign">
                        <div class="stack-intro">
                            <div class="si-h">WHAT YOU'RE LOOKING AT</div>
                            <div class="si-p">Your real renewal-ready students, each with the outreach call that matters most for them: whether to lead with <b>proof</b> or an <b>offer</b>, based on their own real trust score — not the pool average. Sorted by package value, biggest first.</div>
                        </div>
                        <form id="mmCampaignFilter" method="GET" action="{{ route('client.mock-master-helper') }}" onsubmit="return mmCampaignSubmit(event)" class="mm-filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:12px 14px;border-bottom:1px solid var(--ln);background:var(--p1)">
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">Subscription
                                <select name="subscription" style="min-width:220px;padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
                                    <option value="">All subscriptions</option>
                                    @foreach($mkSubscriptions as $sub)
                                        <option value="{{ $sub }}" {{ ($mkFilters['subscription'] ?? '') === $sub ? 'selected' : '' }}>{{ $sub }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">Payment from
                                <input type="date" name="from" value="{{ $mkFilters['from'] ?? '' }}" style="padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
                            </label>
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">Payment to
                                <input type="date" name="to" value="{{ $mkFilters['to'] ?? '' }}" style="padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
                            </label>
                            <button type="submit" style="padding:7px 14px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">Apply</button>
                            <button type="button" onclick="mmCampaignReset()" style="padding:7px 14px;border-radius:6px;border:1px solid var(--ln);color:var(--g3);font-size:12px;font-weight:600;cursor:pointer;background:#fff">Reset</button>
                        </form>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th data-sort-key="value" onclick="mmCampaignSortBy('value')" data-tip="Amount paid for the selected Course" class="mm-sortable" style="cursor:pointer">Package value <span class="mm-sort-ind" data-for="value"></span></th><th data-sort-key="payment" onclick="mmCampaignSortBy('payment')" class="mm-sortable" style="cursor:pointer">Payment date <span class="mm-sort-ind" data-for="payment"></span></th><th data-sort-key="stage" onclick="mmCampaignSortBy('stage')" data-tip="Active, Renewal Due (expired within the last 14 days), or Expired." class="mm-sortable" style="cursor:pointer">Stage <span class="mm-sort-ind" data-for="stage"></span></th><th data-sort-key="readiness" onclick="mmCampaignSortBy('readiness')" data-tip="Average score across all mock tests. 0 = no results" class="mm-sortable" style="cursor:pointer">Readiness <span class="mm-sort-ind" data-for="readiness"></span></th><th data-sort-key="trust" onclick="mmCampaignSortBy('trust')" data-tip="Based on the percentage of payments completed. 50 = no payment history." class="mm-sortable" style="cursor:pointer">Trust <span class="mm-sort-ind" data-for="trust"></span></th><th data-sort-key="approach" onclick="mmCampaignSortBy('approach')" data-tip="Proof-led when Trust is below 65; otherwise Offer-led." class="mm-sortable" style="cursor:pointer">Approach <span class="mm-sort-ind" data-for="approach"></span></th><th data-sort-key="last_active" onclick="mmCampaignSortBy('last_active')" class="mm-sortable" style="cursor:pointer">Last active <span class="mm-sort-ind" data-for="last_active"></span></th><th>Action</th></tr></thead>
                            <tbody id="mmCampaignBody">
                                @forelse($mkStudents as $s)
                                <tr>
                                    <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($s['name']) }}">{{ $mmInitial($s['name']) }}</span><div><div class="bh-acct-n">{{ $s['name'] }}</div><div class="bh-acct-c">({{ $s['sub'] }})</div></div></div></td>
                                    <td>{{ $s['value'] }}</td>
                                    <td>{{ $s['paymentDate'] }}</td>
                                    <td><span class="bh-pill {{ $mmStageKind($s['stage']) }}">{{ $s['stage'] }}</span></td>
                                    <td>{{ $s['readiness'] }}</td>
                                    <td>{{ $s['trust'] }}</td>
                                    <td>@if($s['approach'] === 'Offer-led')<span class="bh-pill good">Offer-led</span>@else<span class="bh-pill warn">Proof-led</span>@endif</td>
                                    <td>{{ $s['lastActive'] }}</td><td><button type="button" onclick="mmStudentOpen({{ $loop->index }})" style="padding:5px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">View</button></td>
                                </tr>
                                @empty
                                <tr><td colspan="9" style="color:var(--g3);padding:20px">No renewal-ready students found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div id="mmCampaignPager" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;border-top:1px solid var(--ln);font-size:12px;color:var(--g3)">
                            <span id="mmCampaignPageInfo">Page {{ $mkPaged['page'] }} of {{ $mkPaged['last_page'] }} · {{ number_format($mkPaged['total']) }} students</span>
                            <div style="display:flex;gap:6px">
                                <button type="button" id="mmCampaignPrev" onclick="mmCampaignGo({{ max(1, $mkPaged['page'] - 1) }})" {{ $mkPaged['page'] <= 1 ? 'disabled' : '' }} style="padding:5px 12px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;cursor:pointer">Previous</button>
                                <button type="button" id="mmCampaignNext" onclick="mmCampaignGo({{ min($mkPaged['last_page'], $mkPaged['page'] + 1) }})" {{ $mkPaged['page'] >= $mkPaged['last_page'] ? 'disabled' : '' }} style="padding:5px 12px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;cursor:pointer">Next</button>
                            </div>
                        </div>
                    </div>

                    <div class="mm-panel" data-panel="mk-performance" style="display:none">
                        <div class="stack-intro"><div class="si-h">PLATFORM PERFORMANCE</div><div class="si-p">Headline numbers across your Mock Master student base.</div></div>
                        <div class="mg-grid">
                            @foreach($mkKpis as $k)
                            <div class="mg-cell" onclick="mmKpiOpen('{{ $k['key'] }}', this.querySelector('.mg-h').textContent)" style="cursor:pointer" title="Click to see the details"><div class="mg-h">{{ $k['label'] }}</div><div class="mg-kpi">{{ $k['value'] }} <small>{{ $k['sub'] }}</small></div></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mm-panel" data-panel="mk-audience" style="display:none">
                        <div class="stack-intro"><div class="si-h">STUDENT SEGMENTS</div><div class="si-p">Grouped by behavior and lifecycle stage.</div></div>
                        @forelse($mkSegments as $seg)
                        <div class="act" onclick="mmKpiOpen('{{ $seg['key'] }}', '{{ $seg['name'] }}')" style="cursor:pointer" title="Click to see the students in this segment"><div><div class="act-t">{{ $seg['name'] }}</div><div class="act-d">{{ $seg['meta'] }}</div></div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">No segment data available.</div></div>
                        @endforelse
                    </div>

                    <div class="mm-panel" data-panel="mk-insights" style="display:none">
                        <div class="stack-intro"><div class="si-h">KEY INSIGHTS</div><div class="si-p">Patterns worth acting on.</div></div>
                        @forelse($mkInsights as $ins)
                        <div class="act" onclick="mmKpiOpen('{{ $ins['key'] }}', '{{ $ins['text'] }}')" style="cursor:pointer" title="Click to see the records behind this insight"><div class="act-t">{{ $ins['text'] }}</div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">Not enough data yet to compute insights.</div></div>
                        @endforelse
                    </div>

                    <div class="mm-panel" data-panel="mk-abtest" style="display:none">
                        <div class="stack-intro">
                            <div class="si-h">A/B TESTS</div>
                            <div class="si-p">Not available yet — there is no experiment/results table in the Mock Master data source, so no numbers are shown here rather than inventing them.</div>
                        </div>
                    </div>

                </div>
            </div>
            <aside class="dash-mira">
                <div class="mira-resize" role="separator" aria-orientation="vertical" aria-label="Resize helper panel (arrow keys)" tabindex="0" title="Drag to resize — drag right to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-hd">
                    <span class="dm-spark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 2.5l1.9 5.6 5.6 1.9-5.6 1.9L10 17.5l-1.9-5.6L2.5 10l5.6-1.9z"/><path d="M18.5 13l.95 2.55L22 16.5l-2.55.95L18.5 20l-.95-2.55L15 16.5l2.55-.95z"/></svg></span>
                    <div><div class="dm-t">Marketing helper</div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                    <div class="mira-tools">
                        <button type="button" class="mira-btn" onclick="mmMira('min')" title="Minimise" aria-label="Minimise helper panel">&minus;</button>
                        <button type="button" class="mira-btn" data-act="max" onclick="mmMira('max')" title="Maximise" aria-label="Maximise helper panel">&#9974;</button>
                    </div>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmMira('restore')" title="Expand helper" aria-label="Expand helper panel">&laquo;</button>
                    <span class="col-rail-label">Helper</span>
                </div>
                <div class="dm-chat" id="mmChat-mk"></div>
                <div class="row-resize" role="separator" aria-orientation="horizontal" aria-label="Resize suggestions panel (arrow keys)" tabindex="0" title="Drag to resize — drag down to hide">
                    <span class="row-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-quick-hd">
                    <span id="mmQuickHd-mk">ASK MIRA · CAMPAIGN</span>
                    <button type="button" class="dm-quick-min" onclick="mmCollapseQuick()" title="Hide suggestions" aria-label="Hide suggestions">&#9660;</button>
                </div>
                <div class="dm-quick" id="mmQuick-mk"></div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-mk" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('mk', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('mk', document.getElementById('mmInput-mk').value); document.getElementById('mmInput-mk').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

        {{-- SALES --}}
        <div class="dash" id="mmDash-sl" data-dash="sl">
            <aside class="dash-left">
                <div class="left-resize" role="separator" aria-orientation="vertical" aria-label="Resize steps panel (arrow keys)" tabindex="0" title="Drag to resize — drag left to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmLeft('restore')" title="Expand steps" aria-label="Expand steps panel">&raquo;</button>
                    <span class="col-rail-label">Steps</span>
                </div>
                <div class="g-body">
                    @foreach($slSteps as $i => $step)
                    <div class="flowst {{ $i === 0 ? 'cur' : '' }}" data-step="sl-{{ $step['key'] }}" onclick="mmSelectStep('sl','{{ $step['key'] }}')">
                        <div class="flowst-dot">{{ $i + 1 }}</div>
                        <div class="flowst-t">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs">
                    @foreach($slSteps as $i => $step)
                    <button type="button" class="dvt {{ $i === 0 ? 'on' : '' }}" data-tab="sl-{{ $step['key'] }}" onclick="mmSelectStep('sl','{{ $step['key'] }}')">{{ strtoupper($step['label']) }}</button>
                    @endforeach
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="sl-today">
                        <div class="stack-intro">
                            <div class="si-h">WHAT YOU'RE LOOKING AT</div>
                            <div class="si-p">The prospects that need your attention right now, ranked by <b>buying readiness</b> and <b>intent</b>. Use this to decide who to call today versus who to nurture.</div>
                        </div>
                        <table class="dtbl">
                            <thead><tr><th>Prospect</th><th>Readiness</th><th>Intent</th><th>Trust</th><th>Play</th></tr></thead>
                            <tbody>
                                @forelse($slProspects as $p)
                                <tr>
                                    <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($p['name']) }}">{{ $mmInitial($p['name']) }}</span><div><div class="bh-acct-n">{{ $p['name'] }}</div><div class="bh-acct-c">({{ $p['sub'] }})</div></div></div></td>
                                    <td>{{ $p['readiness'] }}</td>
                                    <td>{{ $p['intent'] }}</td>
                                    <td>{{ $p['trust'] }}</td>
                                    <td><span class="stk-play {{ $p['play'] === 'Call' ? 'call' : 'onboarding' }}">{{ $p['play'] }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" style="color:var(--g3);padding:20px">No trial-only prospects found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="sl-accounts" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Every prospect in your pipeline, in one place.</div></div>
                        <table class="dtbl">
                            <thead><tr><th>Prospect</th><th>Readiness</th><th>Intent</th><th>Trust</th><th>Play</th></tr></thead>
                            <tbody>
                                @forelse($slProspects as $p)
                                <tr><td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($p['name']) }}">{{ $mmInitial($p['name']) }}</span><div class="bh-acct-n">{{ $p['name'] }}</div></div></td><td>{{ $p['readiness'] }}</td><td>{{ $p['intent'] }}</td><td>{{ $p['trust'] }}</td><td>{{ $p['play'] }}</td></tr>
                                @empty
                                <tr><td colspan="5" style="color:var(--g3);padding:20px">No prospects found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="sl-scripts" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Ready-to-use call, email and WhatsApp scripts for each sales situation. Pick a channel, choose the script that fits the student, and replace the words in <b>[square brackets]</b> before you use it.</div></div>
                        @php $slChannelLabels = ['call' => 'Call', 'email' => 'Email', 'whatsapp' => 'WhatsApp']; @endphp
                        <div class="mm-scr-tabs" role="tablist" aria-label="Script channel">
                            @foreach($slChannelLabels as $ch => $chLabel)
                            <button type="button" role="tab" class="mm-scr-tab {{ $loop->first ? 'on' : '' }}" data-ch="{{ $ch }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" onclick="mmScriptChannel(this)">{{ $chLabel }} <span class="mm-scr-count">{{ count($slScriptLibrary[$ch]) }}</span></button>
                            @endforeach
                        </div>
                        @foreach($slScriptLibrary as $ch => $scripts)
                        <div class="mm-scr-list" data-ch="{{ $ch }}" @if(!$loop->first) style="display:none" @endif>
                            @foreach($scripts as $i => $sc)
                            <div class="mm-scr">
                                <div class="mm-scr-hd">
                                    <span class="mm-scr-n">{{ $i + 1 }}</span>
                                    <div class="mm-scr-meta">
                                        <div class="mm-scr-t">{{ $sc['title'] }}</div>
                                        <div class="mm-scr-when">Use when: {{ $sc['when'] }}</div>
                                    </div>
                                    <button type="button" class="mm-scr-copy" onclick="mmCopyScript(this)" title="Copy this script">Copy</button>
                                </div>
                                @if(!empty($sc['subject']))
                                <div class="mm-scr-subject"><b>Subject:</b> <span>{{ $sc['subject'] }}</span></div>
                                @endif
                                <div class="mm-scr-body">{{ $sc['body'] }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <div class="mm-panel" data-panel="sl-objections" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">The objections students raise most often, grouped by theme. Each one shows what the student usually means and a ready-to-say answer: <b>acknowledge</b> the concern, <b>reframe</b> it, <b>respond</b>, then <b>ask</b> a question to keep the conversation moving. Replace the words in <b>[square brackets]</b> before you use it.</div></div>
                        <div class="mm-scr-tabs" role="tablist" aria-label="Objection category">
                            @foreach($slObjectionGroups as $gk => $group)
                            <button type="button" role="tab" class="mm-scr-tab {{ $loop->first ? 'on' : '' }}" data-ch="{{ $gk }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" onclick="mmScriptChannel(this)">{{ $group['label'] }} <span class="mm-scr-count">{{ count($group['items']) }}</span></button>
                            @endforeach
                        </div>
                        @foreach($slObjectionGroups as $gk => $group)
                        <div class="mm-scr-list" data-ch="{{ $gk }}" @if(!$loop->first) style="display:none" @endif>
                            @foreach($group['items'] as $i => $o)
                            <div class="mm-scr">
                                <div class="mm-scr-hd">
                                    <span class="mm-scr-n">{{ $i + 1 }}</span>
                                    <div class="mm-scr-meta">
                                        <div class="mm-scr-t">&ldquo;{{ $o['q'] }}&rdquo;</div>
                                        <div class="mm-scr-when">What it usually means: {{ $o['means'] }}</div>
                                    </div>
                                    <button type="button" class="mm-scr-copy" onclick="mmCopyScript(this)" title="Copy this answer">Copy</button>
                                </div>
                                <div class="mm-scr-body">{{ $o['a'] }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <div class="mm-panel" data-panel="sl-close" style="display:none">
                        @php
                            $cg = $slCloseSummary ?? ['convert' => 0, 'abandoned' => 0, 'abandoned_value' => 0];
                        @endphp
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">New revenue you can close this week, from live Mock Master data: free-trial students who are practising now, and students who started a checkout but didn't pay. Each row shows the evidence and a suggested next step. Renewals and lapsed plans are in <b>Customer Retention › Renew &amp; win back</b>.</div></div>
                        <div class="mg-grid cg-kpis">
                            <div class="mg-cell"><div class="mg-h">Ready to convert</div><div class="mg-kpi">{{ number_format($cg['convert']) }} <small>free-trial students active in the last 14 days</small></div></div>
                            <div class="mg-cell"><div class="mg-h">Open checkouts</div><div class="mg-kpi">{{ number_format($cg['abandoned']) }} <small>${{ number_format($cg['abandoned_value']) }} not yet paid (30 days)</small></div></div>
                        </div>
                        @include('client.partials.mm-list-tabs', [
                            'ariaLabel' => 'Close and grow list',
                            'tabs' => [
                                'convert'   => ['label' => 'Ready to convert', 'count' => $cg['convert'],   'rows' => $slClose ?? [],     'dataset' => 'slClose'],
                                'abandoned' => ['label' => 'Open checkouts',   'count' => $cg['abandoned'], 'rows' => $slAbandoned ?? [], 'dataset' => 'slAbandoned'],
                            ],
                        ])
                    </div>

                </div>
            </div>
            <aside class="dash-mira">
                <div class="mira-resize" role="separator" aria-orientation="vertical" aria-label="Resize helper panel (arrow keys)" tabindex="0" title="Drag to resize — drag right to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-hd">
                    <span class="dm-spark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 2.5l1.9 5.6 5.6 1.9-5.6 1.9L10 17.5l-1.9-5.6L2.5 10l5.6-1.9z"/><path d="M18.5 13l.95 2.55L22 16.5l-2.55.95L18.5 20l-.95-2.55L15 16.5l2.55-.95z"/></svg></span>
                    <div><div class="dm-t">Sales helper</div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                    <div class="mira-tools">
                        <button type="button" class="mira-btn" onclick="mmMira('min')" title="Minimise" aria-label="Minimise helper panel">&minus;</button>
                        <button type="button" class="mira-btn" data-act="max" onclick="mmMira('max')" title="Maximise" aria-label="Maximise helper panel">&#9974;</button>
                    </div>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmMira('restore')" title="Expand helper" aria-label="Expand helper panel">&laquo;</button>
                    <span class="col-rail-label">Helper</span>
                </div>
                <div class="dm-chat" id="mmChat-sl"></div>
                <div class="row-resize" role="separator" aria-orientation="horizontal" aria-label="Resize suggestions panel (arrow keys)" tabindex="0" title="Drag to resize — drag down to hide">
                    <span class="row-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-quick-hd">
                    <span id="mmQuickHd-sl">ASK MIRA · TODAY</span>
                    <button type="button" class="dm-quick-min" onclick="mmCollapseQuick()" title="Hide suggestions" aria-label="Hide suggestions">&#9660;</button>
                </div>
                <div class="dm-quick" id="mmQuick-sl"></div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-sl" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('sl', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('sl', document.getElementById('mmInput-sl').value); document.getElementById('mmInput-sl').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

        {{-- CUSTOMER RETENTION --}}
        <div class="dash" id="mmDash-ch" data-dash="ch">
            <aside class="dash-left">
                <div class="left-resize" role="separator" aria-orientation="vertical" aria-label="Resize steps panel (arrow keys)" tabindex="0" title="Drag to resize — drag left to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmLeft('restore')" title="Expand steps" aria-label="Expand steps panel">&raquo;</button>
                    <span class="col-rail-label">Steps</span>
                </div>
                <div class="g-body">
                    @foreach($chSteps as $i => $step)
                    <div class="flowst {{ $i === 0 ? 'cur' : '' }}" data-step="ch-{{ $step['key'] }}" onclick="mmSelectStep('ch','{{ $step['key'] }}')">
                        <div class="flowst-dot">{{ $i + 1 }}</div>
                        <div class="flowst-t">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs">
                    @foreach($chSteps as $i => $step)
                    <button type="button" class="dvt {{ $i === 0 ? 'on' : '' }}" data-tab="ch-{{ $step['key'] }}" onclick="mmSelectStep('ch','{{ $step['key'] }}')">{{ strtoupper($step['label']) }}</button>
                    @endforeach
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="ch-savefirst">
                        <div class="stack-intro">
                            <div class="si-h">RANKED STACK — WHO TO SAVE, IN ORDER</div>
                            <div class="si-p">Sorted by <b>churn risk × value at stake</b>. Click through and reach out before they lapse.</div>
                        </div>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th>Inactive for</th><th>Value at risk</th><th>Risk score</th></tr></thead>
                            <tbody>
                                @forelse($chAtRisk as $r)
                                <tr>
                                    <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div><div class="bh-acct-n">{{ $r['name'] }}</div><div class="bh-acct-c">({{ $r['sub'] }})</div></div></div></td>
                                    <td>{{ $r['inactiveDays'] }}d</td>
                                    <td>{{ $r['valueAtRisk'] }}</td>
                                    <td><span style="color:var(--crit);font-weight:600">{{ $r['risk'] }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" style="color:var(--g3);padding:20px">Nothing urgent right now — no packages expiring in the next 7 days.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="ch-rootcause" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Why students are actually leaving, ranked by how many it affects.</div></div>
                        @forelse($chRootCauses as $rc)
                        <div class="act"><div><div class="act-t">{{ $rc['name'] }}</div><div class="act-d">{{ $rc['meta'] }}</div></div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">No root-cause data available.</div></div>
                        @endforelse
                    </div>

                    <div class="mm-panel" data-panel="ch-offers" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Offers that have worked to win students back.</div></div>
                        @foreach($chOffers as $o)
                        <div class="act"><div><div class="act-t">{{ $o['name'] }}</div><div class="act-d">{{ $o['meta'] }}</div></div></div>
                        @endforeach
                    </div>

                    <div class="mm-panel" data-panel="ch-watchlist" style="display:none">
                        <div class="stack-intro"><div class="si-h">WATCH — CHURN CREEPING UP</div><div class="si-p">Not urgent yet, but trending the wrong way.</div></div>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th>Inactive for</th><th>Value at risk</th><th>Risk score</th></tr></thead>
                            <tbody>
                                @forelse($chWatchlist as $r)
                                <tr><td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div class="bh-acct-n">{{ $r['name'] }}</div></div></td><td>{{ $r['inactiveDays'] }}d</td><td>{{ $r['valueAtRisk'] }}</td><td>{{ $r['risk'] }}</td></tr>
                                @empty
                                <tr><td colspan="4" style="color:var(--g3);padding:20px">Nothing trending toward churn right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="ch-renew" style="display:none">
                        @php
                            $rw = $chRenewSummary ?? ['renewals' => 0, 'renewals_value' => 0, 'winback' => 0, 'winback_value' => 0];
                        @endphp
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Keep paying students paying. <b>Renewals due</b>: paid plans that expire in the next 30 days and haven't been renewed. <b>Win-back</b>: paid plans that lapsed in the last 60 days, where the student still logs in or practises. Each row shows their recent activity and a suggested next step. Unlike Save first and Watchlist, this covers paid plans only, not enrolled coaching access.</div></div>
                        <div class="mg-grid cg-kpis">
                            <div class="mg-cell"><div class="mg-h">Renewals due</div><div class="mg-kpi">{{ number_format($rw['renewals']) }} <small>${{ number_format($rw['renewals_value']) }} expiring in 30 days</small></div></div>
                            <div class="mg-cell"><div class="mg-h">Win-back</div><div class="mg-kpi">{{ number_format($rw['winback']) }} <small>${{ number_format($rw['winback_value']) }} in lapsed plans, still active</small></div></div>
                        </div>
                        @include('client.partials.mm-list-tabs', [
                            'ariaLabel' => 'Renew and win back list',
                            'tabs' => [
                                'renewals' => ['label' => 'Renewals due', 'count' => $rw['renewals'], 'rows' => $chRenewals ?? [], 'dataset' => 'chRenewals'],
                                'winback'  => ['label' => 'Win-back',     'count' => $rw['winback'],  'rows' => $chWinBack ?? [],  'dataset' => 'chWinBack'],
                            ],
                        ])
                    </div>

                    <div class="mm-panel" data-panel="ch-abtest" style="display:none">
                        <div class="stack-intro">
                            <div class="si-h">A/B TESTS</div>
                            <div class="si-p">Not available yet — there is no experiment/results table in the Mock Master data source, so no numbers are shown here rather than inventing them.</div>
                        </div>
                    </div>

                </div>
            </div>
            <aside class="dash-mira">
                <div class="mira-resize" role="separator" aria-orientation="vertical" aria-label="Resize helper panel (arrow keys)" tabindex="0" title="Drag to resize — drag right to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-hd">
                    <span class="dm-spark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 2.5l1.9 5.6 5.6 1.9-5.6 1.9L10 17.5l-1.9-5.6L2.5 10l5.6-1.9z"/><path d="M18.5 13l.95 2.55L22 16.5l-2.55.95L18.5 20l-.95-2.55L15 16.5l2.55-.95z"/></svg></span>
                    <div><div class="dm-t">Retention helper</div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                    <div class="mira-tools">
                        <button type="button" class="mira-btn" onclick="mmMira('min')" title="Minimise" aria-label="Minimise helper panel">&minus;</button>
                        <button type="button" class="mira-btn" data-act="max" onclick="mmMira('max')" title="Maximise" aria-label="Maximise helper panel">&#9974;</button>
                    </div>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmMira('restore')" title="Expand helper" aria-label="Expand helper panel">&laquo;</button>
                    <span class="col-rail-label">Helper</span>
                </div>
                <div class="dm-chat" id="mmChat-ch"></div>
                <div class="row-resize" role="separator" aria-orientation="horizontal" aria-label="Resize suggestions panel (arrow keys)" tabindex="0" title="Drag to resize — drag down to hide">
                    <span class="row-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-quick-hd">
                    <span id="mmQuickHd-ch">ASK MIRA · SAVE FIRST</span>
                    <button type="button" class="dm-quick-min" onclick="mmCollapseQuick()" title="Hide suggestions" aria-label="Hide suggestions">&#9660;</button>
                </div>
                <div class="dm-quick" id="mmQuick-ch"></div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-ch" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('ch', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('ch', document.getElementById('mmInput-ch').value); document.getElementById('mmInput-ch').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

        {{-- Popup for "which/who" style answers — a clean table of the real
             students behind the answer, instead of a run-on paragraph. --}}
        <div class="mm-list-modal-overlay" id="mmListModalOverlay" onclick="if(event.target===this) closeMmListModal()">
            <div class="mm-list-modal">
                <div class="mm-list-modal-hd">
                    <span id="mmListModalTitle"></span>
                    <button type="button" onclick="closeMmListModal()" aria-label="Close">✕</button>
                </div>
                <div class="mm-list-modal-body" id="mmListModalBody"></div>
            </div>
        </div>

    </div>
    </div>
</div>

<style>
/* Reuses the same visual language as the Business Helpers "dash" layout. */
#bhRoot{
    --f1:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    --fm:'IBM Plex Mono',ui-monospace,monospace;
    --p1:#f9fafb;--p2:#f3f4f6;--ink:#111827;--g2:#6b7280;--g3:#9ca3af;--g4:#d1d5db;
    --ln:#e5e7eb;--ln2:#d1d5db;--sig:#059669;--warn:#d97706;--crit:#dc2626;
    --ac:#7c3aed;--ac-l:#f5f3ff;--ac-m:#ddd6fe;--ac-d:#6d28d9;
    font-family:var(--f1);color:var(--ink);
}
#bhRoot[data-agent="sl"]{--ac:#2563eb;--ac-l:#eff6ff;--ac-m:#bfdbfe;--ac-d:#1d4ed8}
#bhRoot[data-agent="ch"]{--ac:#e11d48;--ac-l:#fff1f2;--ac-m:#fecdd3;--ac-d:#be123c}

#bhRoot .bar{display:flex;align-items:stretch;gap:1px;background:var(--ln);border-bottom:1px solid var(--ln);flex-wrap:wrap;flex-shrink:0}
#bhRoot .atabs{display:flex;gap:1px;background:var(--ln);flex:1}
#bhRoot .atab{background:#fff;padding:0 18px;cursor:pointer;transition:all .15s;text-align:left;border:none;font-family:var(--f1);display:flex;align-items:center;justify-content:center;gap:10px;min-height:56px;flex:1;position:relative}
#bhRoot .atab:hover{background:var(--p1)}
#bhRoot .atab.on{background:var(--ac-l)}
#bhRoot .atab.on::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;background:var(--ac)}
#bhRoot .amono{width:28px;height:28px;flex-shrink:0;display:grid;place-items:center;font-size:12px;font-weight:700;background:var(--p2);color:var(--g2);border-radius:8px;transition:all .15s}
#bhRoot .atab.on .amono{background:var(--ac);color:#fff}
#bhRoot .atab .a2{font-size:13px;font-weight:600;color:var(--ink)}
#bhRoot .atab.on .a2{color:var(--ac-d)}

#bhRoot .dash{display:none;grid-template-columns:var(--bh-left-w,220px) 1fr var(--bh-mira-w,320px);gap:1px;background:var(--ln);flex:1;min-height:0;overflow:hidden}
#bhRoot .dash.on{display:grid}
@media(max-width:1180px){#bhRoot .dash{grid-template-columns:190px 1fr}}
@media(max-width:820px){#bhRoot .dash{grid-template-columns:1fr;overflow-y:auto}}

#bhRoot .dash-left{background:#fff;display:flex;flex-direction:column;overflow-y:auto;min-height:0;padding-top:8px;position:relative}
#bhRoot .flowst{display:flex;align-items:center;gap:13px;padding:12px 20px;cursor:pointer}
#bhRoot .flowst:hover{background:var(--p1)}
#bhRoot .flowst.cur{background:var(--ac-l);border-left:2px solid var(--ac);padding-left:18px}
#bhRoot .flowst-dot{width:27px;height:27px;border-radius:50%;border:1.5px solid var(--ln2);background:#fff;display:grid;place-items:center;font-family:var(--fm);font-size:11px;font-weight:700;color:var(--g3);flex-shrink:0}
#bhRoot .flowst.cur .flowst-dot{background:var(--ac);border-color:var(--ac);color:#fff}
#bhRoot .flowst-t{font-size:13.5px;font-weight:600;color:var(--ink)}
@media(max-width:820px){#bhRoot .dash-left{max-height:280px}}

#bhRoot .dash-main{background:#fff;display:flex;flex-direction:column;overflow:hidden;min-height:0}
#bhRoot .dash-vtabs{display:flex;gap:1px;background:var(--ln);border-bottom:1px solid var(--ln);flex-shrink:0;flex-wrap:wrap}
#bhRoot .dvt{flex:1;min-width:78px;font-family:var(--fm);font-size:10.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g2);padding:13px 6px;background:#fff;border:none;cursor:pointer;text-align:center;transition:all .15s}
#bhRoot .dvt.on{background:var(--ac);color:#fff}
#bhRoot .dvt:hover:not(.on){background:var(--p1);color:var(--ink)}
#bhRoot .dash-view{flex:1;overflow-y:auto}

#bhRoot .stack-intro{padding:20px 20px 18px}
#bhRoot .si-h{font-family:var(--fm);font-size:11px;font-weight:700;letter-spacing:2px;color:var(--ink);margin-bottom:10px}
#bhRoot .si-p{font-size:12.5px;color:var(--g2);line-height:1.75;max-width:640px}
#bhRoot .si-p b{color:var(--ink);font-weight:600}

#bhRoot .dtbl{width:100%;border-collapse:collapse;font-size:12px}
#bhRoot .dtbl th{font-size:10px;letter-spacing:.5px;text-transform:uppercase;color:var(--g3);text-align:left;padding:9px 14px;border-bottom:1px solid var(--ln);background:var(--p1);white-space:nowrap;font-weight:700}
#bhRoot .dtbl td{padding:9px 14px;border-bottom:1px solid var(--p2);vertical-align:middle}
#bhRoot .dtbl tr:hover td{background:var(--p1)}
#bhRoot .dtbl .acctn{font-weight:600;color:var(--ink)}

#bhRoot .stk-play{font-size:9.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:2px 8px;border-radius:99px;display:inline-block}
#bhRoot .stk-play.call{background:var(--ac-l);color:var(--ac-d);border:1px solid var(--ac-m)}
#bhRoot .stk-play.onboarding{background:#f3eefc;color:#6d28d9;border:1px solid #e2d5f7}

#bhRoot .act{border:1px solid var(--ln);border-left:3px solid var(--ac);background:#fff;padding:12px 20px;margin:0 20px 10px;display:flex;gap:10px;align-items:center;border-radius:8px;transition:background .15s,border-color .15s}
#bhRoot .act[onclick]:hover{background:#f5f3ff;border-color:#c4b5fd;border-left-color:#7c3aed}
#bhRoot .act-t{font-size:12.5px;font-weight:600;color:var(--ink);line-height:1.5}
#bhRoot .act-d{font-size:11px;color:var(--g2);margin-top:2px}

#bhRoot .mg-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--ln)}
#bhRoot .mg-cell{background:#fff;padding:16px 20px}
#bhRoot .mg-h{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--g3);text-transform:uppercase;margin-bottom:10px}
#bhRoot .mg-kpi{font-size:20px;font-weight:700;color:var(--ink)}
#bhRoot .mg-kpi small{font-size:10px;color:var(--g3);font-weight:500;margin-left:4px}
@media(max-width:900px){#bhRoot .mg-grid{grid-template-columns:1fr}}

#bhRoot .dash-mira{background:#fff;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;min-height:0;position:relative}
#bhRoot .dm-hd{display:flex;align-items:center;gap:11px;padding:16px 18px;border-bottom:1px solid var(--ln);background:var(--p1);flex-shrink:0}
#bhRoot .dm-dot{width:7px;height:7px;border-radius:50%;background:var(--ac);flex-shrink:0;animation:mmblink 1.8s infinite}
@keyframes mmblink{0%,100%{opacity:1}50%{opacity:.2}}
@keyframes mmspin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

#mmTip{position:fixed;z-index:20000;display:none;max-width:300px;background:#16a34a;color:#fff;font-size:14px;line-height:1.45;font-weight:500;padding:10px 14px;border-radius:10px;box-shadow:0 10px 25px rgba(22,163,74,.35);pointer-events:none}
#mmTip::after{content:"";position:absolute;left:50%;top:100%;transform:translateX(-50%);border:7px solid transparent;border-top-color:#16a34a}
.risk-modal-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:10000;align-items:center;justify-content:center;padding:24px}
.risk-modal-overlay.show{display:flex}
.risk-modal{background:#fff;border-radius:12px;max-width:820px;width:100%;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.risk-modal-hd{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:13px;color:#111827}
.risk-modal-hd button{border:none;background:none;font-size:16px;color:#9ca3af;cursor:pointer;line-height:1;padding:4px}
.risk-modal-hd button:hover{color:#111827}
.risk-modal-body{overflow:auto;padding:12px 18px 18px}
.risk-modal-body table{width:100%;border-collapse:collapse;font-size:12px}
.risk-modal-body th,.risk-modal-body td{padding:7px 10px;border-bottom:1px solid #f0f0f0;text-align:left;white-space:nowrap}
.risk-modal-body th{font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;background:#f9fafb}

/* ── Sync overlay — blurs the whole interface while a sync is running so
   nothing looks interactive mid-copy, and lifts the moment it finishes. ── */
body.mm-syncing > *:not(#mmSyncOverlay){filter:blur(5px);pointer-events:none;user-select:none;transition:filter .25s ease}
#mmSyncOverlay{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(15,15,20,.22)}
body.mm-syncing #mmSyncOverlay{display:flex}
#mmSyncOverlay .mm-sync-card{background:#fff;border-radius:14px;padding:26px 40px;display:flex;flex-direction:column;align-items:center;gap:10px;box-shadow:0 20px 60px rgba(0,0,0,.28)}
#mmSyncOverlay .mm-sync-spinner{width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#4f46e5;border-radius:50%;animation:mmspin .8s linear infinite}
#mmSyncOverlay .mm-sync-spinner.done{border:none;animation:none}
#mmSyncOverlay .mm-sync-text{font-size:13px;font-weight:700;color:#111827}
#mmSyncOverlay .mm-sync-sub{font-size:11.5px;color:#6b7280}
#bhRoot .dm-t{font-size:13px;font-weight:700;letter-spacing:.2px;color:var(--ink)}
#bhRoot .dm-s{font-family:var(--fm);font-size:8.5px;letter-spacing:.5px;color:var(--g3);margin-top:3px}
#bhRoot .dm-ready{margin-left:auto;font-family:var(--fm);font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--sig);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:99px;padding:3px 10px;flex-shrink:0}
#bhRoot .dm-chat{flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:13px;min-height:120px}
#bhRoot .dm-quick-hd{padding:10px 16px 4px;border-top:1px solid var(--ln);font-family:var(--fm);font-size:9.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g3);flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:8px}
#bhRoot .dm-quick-min{width:20px;height:20px;padding:0;border:1px solid var(--ln2);background:#fff;border-radius:6px;cursor:pointer;display:grid;place-items:center;color:var(--g3);flex-shrink:0}
#bhRoot .dm-quick-min:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .dm-quick{padding:6px 16px 14px;display:flex;flex-direction:column;gap:7px;flex-shrink:0;max-height:var(--bh-quick-h,220px);overflow-y:auto}
#bhRoot .qk{font-size:11px;font-weight:600;color:var(--g2);padding:7px 12px;border:1px solid var(--ln);cursor:pointer;background:#fff;transition:all .15s;border-radius:99px}
#bhRoot .qk:hover{border-color:var(--ac-m);background:var(--ac-l);color:var(--ac-d)}
#bhRoot .qk:disabled{opacity:.6;cursor:default}
#bhRoot .dm-quick .qk{width:100%;text-align:left;padding:10px 12px;font-size:12px;white-space:normal;line-height:1.35;border-radius:8px;background:#fff;border:1px solid var(--ln);cursor:pointer}
#bhRoot .dm-quick .qk:hover{border-color:var(--ac-m);background:var(--ac-l);color:var(--ac-d)}
#bhRoot .dm-quick-hd.dm-quick-collapsed,#bhRoot .dm-quick.dm-quick-collapsed{display:none}
#bhRoot .dm-quick-reopen{display:none;align-items:center;justify-content:center;gap:5px;padding:7px 16px;border-top:1px solid var(--ln);font-family:var(--fm);font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--g3);cursor:pointer;flex-shrink:0;background:#fff}
#bhRoot .dm-quick-reopen:hover{color:var(--ac-d);background:var(--ac-l)}
#bhRoot .dm-quick-reopen.dm-quick-reopen-show{display:flex}
#bhRoot .dm-inbar{display:flex;gap:1px;border-top:1px solid var(--ln);background:var(--ln);flex-shrink:0;position:sticky;bottom:0;z-index:2}

/* Resize handles, minimise/maximise, collapse rails — ported from Business Helpers */
#bhRoot .mira-resize{position:absolute;left:0;top:0;bottom:0;width:12px;z-index:20;cursor:col-resize;display:flex;align-items:center;justify-content:center;touch-action:none}
#bhRoot .mira-resize::before{content:'';position:absolute;left:0;top:0;bottom:0;width:1px;background:var(--ln2);transition:background .15s}
#bhRoot .mira-resize:hover::before,#bhRoot .mira-resize.dragging::before{background:var(--ac)}
#bhRoot .mira-grip{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;width:12px;height:22px;border:1px solid var(--ln2);border-radius:4px;background:#fff;color:var(--g3);transition:all .15s}
#bhRoot .mira-resize:hover .mira-grip,#bhRoot .mira-resize.dragging .mira-grip{color:var(--ac-d);border-color:var(--ac-m)}
#bhRoot .mira-resize:focus-visible{outline:2px solid var(--ac);outline-offset:-1px}
#bhRoot .mira-grip svg{width:12px;height:12px;display:block}
#bhRoot .mira-tools{display:flex;gap:4px;flex-shrink:0}
#bhRoot .mira-btn{width:24px;height:24px;padding:0;border:1px solid var(--ln2);background:#fff;border-radius:7px;cursor:pointer;display:grid;place-items:center;font-size:12px;line-height:1;color:var(--g2);font-family:var(--fm);transition:all .15s}
#bhRoot .mira-btn:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .col-rail{display:none;flex:1;flex-direction:column;align-items:center;gap:14px;padding:12px 0;background:#fff;overflow:hidden}
#bhRoot .col-rail .col-toggle{width:26px;height:26px;flex-shrink:0;border:1px solid var(--ln2);background:#fff;border-radius:7px;cursor:pointer;display:grid;place-items:center;font-size:13px;line-height:1;color:var(--g2);font-family:var(--fm);transition:all .15s;padding:0}
#bhRoot .col-rail .col-toggle:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .col-rail-label{writing-mode:vertical-rl;font-family:var(--fm);font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--g3)}
#bhRoot.bh-mira-min .dash-mira > *:not(.col-rail):not(.mira-resize){display:none}
#bhRoot.bh-mira-min .dash-mira > .col-rail{display:flex}
#bhRoot.bh-left-min .dash-left > *:not(.col-rail):not(.left-resize){display:none}
#bhRoot.bh-left-min .dash-left > .col-rail{display:flex}
@media(min-width:1181px){
  #bhRoot.bh-mira-min .dash{grid-template-columns:var(--bh-left-w,220px) 1fr 40px}
  #bhRoot.bh-left-min .dash{grid-template-columns:40px 1fr var(--bh-mira-w,320px)}
  #bhRoot.bh-left-min.bh-mira-min .dash{grid-template-columns:40px 1fr 40px}
}
@media(max-width:1180px){
  #bhRoot .mira-resize{display:none}
  #bhRoot .left-resize{display:none}
}
#bhRoot .left-resize{position:absolute;right:0;top:0;bottom:0;width:12px;z-index:20;cursor:col-resize;display:flex;align-items:center;justify-content:center;touch-action:none;transform:translateX(50%)}
#bhRoot .left-resize::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:1px;background:var(--ln2);transition:background .15s}
#bhRoot .left-resize:hover::before,#bhRoot .left-resize.dragging::before{background:var(--ac)}
#bhRoot .left-resize:focus-visible{outline:2px solid var(--ac);outline-offset:-1px}
#bhRoot .row-resize{height:10px;flex-shrink:0;cursor:row-resize;display:flex;align-items:center;justify-content:center;touch-action:none;position:relative}
#bhRoot .row-resize::before{content:'';position:absolute;left:0;right:0;top:50%;height:1px;background:var(--ln2);transition:background .15s}
#bhRoot .row-resize:hover::before,#bhRoot .row-resize.dragging::before{background:var(--ac)}
#bhRoot .row-resize:focus-visible{outline:2px solid var(--ac);outline-offset:-1px}
#bhRoot .row-grip{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;width:22px;height:12px;border:1px solid var(--ln2);border-radius:4px;background:#fff;color:var(--g3);transition:all .15s}
#bhRoot .row-resize:hover .row-grip,#bhRoot .row-resize.dragging .row-grip{color:var(--ac-d);border-color:var(--ac-m)}
#bhRoot .row-grip svg{width:12px;height:12px;display:block;transform:rotate(90deg)}
#bhRoot .in{flex:1;border:none;padding:12px 14px;font-family:var(--f1);font-size:12.5px;outline:none;min-width:0;background:#fff}
#bhRoot .send{width:44px;border:none;background:var(--ac);color:#fff;cursor:pointer;display:grid;place-items:center;transition:background .15s;flex-shrink:0}
#bhRoot .send:hover{background:var(--ac-d)}
#bhRoot .send svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round}

#bhRoot .msg{max-width:94%;padding:11px 13px;font-size:12.5px;line-height:1.65;border-radius:10px}
#bhRoot .msg.user{background:var(--ink);color:#fff;align-self:flex-end}
#bhRoot .msg.bot{background:var(--p1);border:1px solid var(--ln);align-self:flex-start;color:var(--ink)}
#bhRoot .msg.bot p{margin:0 0 8px}
#bhRoot .msg.bot p:last-child{margin-bottom:0}

/* "View list" popup — same pattern as Business Helpers' risk modal, so a
   list-shaped answer (who/which questions) is a clean table, not a run-on
   paragraph. */
.mm-list-modal-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:200;align-items:center;justify-content:center;padding:24px}
.mm-list-modal-overlay.show{display:flex}
.mm-list-modal{background:#fff;border-radius:12px;max-width:820px;width:100%;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.mm-list-modal-hd{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:13px;color:#111827}
.mm-list-modal-hd button{border:none;background:none;font-size:16px;color:#9ca3af;cursor:pointer;line-height:1;padding:4px}
.mm-list-modal-hd button:hover{color:#111827}
.mm-list-modal-body{overflow:auto;padding:12px 18px 18px}
.mm-list-modal-body table{width:100%;border-collapse:collapse;font-size:12px}
.mm-list-modal-body th,.mm-list-modal-body td{padding:7px 10px;border-bottom:1px solid #f0f0f0;text-align:left;white-space:nowrap}
.mm-list-modal-body th{font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;background:#f9fafb}
.mm-list-modal-body #mmListMoreBtn{margin-top:14px}

/* Student picker for the Sales · Accounts prompts — a real name list via
   <datalist>, same pattern as Business Helpers' Retention name form. */
#bhRoot .nmform{display:flex;gap:6px;margin-top:8px;align-items:stretch}
#bhRoot .nmin{flex:1;min-width:0;border:1px solid var(--ln2);border-radius:8px;padding:8px 10px;font-family:var(--f1);font-size:12px;color:var(--ink);outline:none;background:#fff}
#bhRoot .nmin:focus{border-color:var(--ac-m);box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .nmin:disabled{background:var(--p2);color:var(--g3)}
#bhRoot .nmform .qk{flex-shrink:0;align-self:center}

/* ══════════════════════════════════════════════════════════════════════
   THEME — same floating-card layout as Business Helpers. Visual overrides
   only: every class, id and handler above is unchanged.
   ══════════════════════════════════════════════════════════════════════ */
.bh-page{background:#f5f7fb}

#bhRoot{--ln:#e6e9f0;--ln2:#d9dee8;--p1:#f7f8fb;--p2:#f1f3f8;--g2:#5b6475;--card-r:14px;--card-sh:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.04)}

/* Agent tabs — one white strip, the active agent is a solid accent block */
#bhRoot .bar{background:transparent;border:none;gap:0;margin-bottom:16px}
#bhRoot .atabs{background:#fff;gap:0;border:1px solid var(--ln);border-radius:var(--card-r);box-shadow:var(--card-sh);padding:0;overflow:hidden}
#bhRoot .atab{min-height:48px;background:#fff;gap:10px;border-radius:0}
#bhRoot .atab + .atab{border-left:1px solid var(--ln)}
#bhRoot .atab:hover{background:var(--p1)}
#bhRoot .atab.on,#bhRoot .atab.on:hover{background:var(--ac);border-radius:var(--card-r);border-left-color:transparent}
#bhRoot .atab.on + .atab{border-left-color:transparent}
#bhRoot .atab.on::after{display:none}
#bhRoot .amono{width:auto;height:auto;background:none;border-radius:0;color:#475569}
#bhRoot .amono svg{width:17px;height:17px;display:block}
#bhRoot .atab.on .amono{background:none;color:#fff}
#bhRoot .atab .a2{font-size:13px;font-weight:600;color:#0f172a}
#bhRoot .atab.on .a2{color:#fff}

/* Three floating cards instead of one card split by hairlines */
#bhRoot .dash{gap:16px;background:transparent}
#bhRoot .dash-left,#bhRoot .dash-main,#bhRoot .dash-mira{background:#fff;border:1px solid var(--ln);border-radius:var(--card-r);box-shadow:var(--card-sh)}
#bhRoot .dash-main{overflow:hidden}
#bhRoot .left-resize::before,#bhRoot .mira-resize::before,#bhRoot .row-resize::before{background:transparent}
#bhRoot .left-resize{transform:translateX(0)}
#bhRoot .mira-grip{height:26px;border-radius:6px;box-shadow:0 1px 2px rgba(16,24,40,.06)}

/* Steps list */
#bhRoot .dash-left{padding:10px 0}
#bhRoot .flowst{margin:2px 10px;padding:10px 10px;border-radius:10px;gap:12px;transition:background .15s}
#bhRoot .flowst.cur{background:var(--ac-l);border-left:none;padding-left:10px}
#bhRoot .flowst-dot{width:27px;height:27px;border:1.5px solid var(--ln2);font-family:'Inter',sans-serif;font-size:11px;font-weight:600;color:#64748b}
#bhRoot .flowst.cur .flowst-dot{box-shadow:0 0 0 4px var(--ac-l)}
#bhRoot .flowst-t{font-size:13.5px;font-weight:600;color:#0f172a}
#bhRoot .col-rail{background:transparent}

/* View tabs across the middle card (active tab: tint + underline) */
#bhRoot .dash-vtabs{gap:0;background:#fff;padding:6px 6px 0}
#bhRoot .dvt{font-family:'Inter',sans-serif;font-size:10.5px;font-weight:600;letter-spacing:1.5px;color:#475569;padding:12px 6px;background:#fff;border-radius:8px 8px 0 0}
#bhRoot .dvt + .dvt{box-shadow:inset 1px 0 0 var(--ln)}
#bhRoot .dvt.on{background:var(--ac-l);color:var(--ac-d);box-shadow:inset 0 -2.5px 0 var(--ac)}
#bhRoot .dvt.on + .dvt{box-shadow:none}
#bhRoot .dvt:hover:not(.on){background:var(--p1);color:#0f172a}

/* "What you're looking at" intro */
#bhRoot .stack-intro{padding:20px 20px 16px}
#bhRoot .si-h{font-family:'Inter',sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;color:#0f172a;margin-bottom:8px}
#bhRoot .si-p{font-size:12.5px;color:#475569;line-height:1.75;max-width:680px}
#bhRoot .si-p b{color:#0f172a}

/* Data tables — a rounded inset table inside the card */
#bhRoot .dtbl{width:calc(100% - 40px);margin:0 20px 20px;border-collapse:separate;border-spacing:0;border:1px solid var(--ln);border-radius:10px;overflow:hidden;font-size:12px}
#bhRoot .dtbl th{font-size:10px;font-weight:700;letter-spacing:.5px;color:#64748b;background:var(--p1);padding:9px 14px;border-bottom:1px solid var(--ln)}
#bhRoot .dtbl td{padding:9px 14px;border-bottom:1px solid var(--ln);color:#1e293b;font-variant-numeric:tabular-nums}
#bhRoot .dtbl tbody tr:last-child td{border-bottom:none}
#bhRoot .dtbl tr:hover td{background:#fafbfd}
#bhRoot .bh-acct{display:flex;align-items:center;gap:10px;min-width:160px}
#bhRoot .bh-av{width:28px;height:28px;border-radius:7px;display:grid;place-items:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0}
#bhRoot .bh-acct-n{font-weight:600;color:#0f172a;line-height:1.3}
#bhRoot .bh-acct-c{font-size:11px;font-weight:400;color:#64748b;line-height:1.35;margin-top:1px}
#bhRoot .bh-pill{display:inline-block;font-size:11px;font-weight:600;line-height:1.3;padding:3px 8px;border-radius:6px;white-space:normal;max-width:130px}
#bhRoot .bh-pill.good{background:#e8f7ee;color:#15803d}
#bhRoot .bh-pill.warn{background:#fff1e6;color:#c2410c}
#bhRoot .bh-pill.bad{background:#fdecec;color:#b91c1c}
#bhRoot .bh-pill.info{background:#e8f0fe;color:#1d4ed8}
#bhRoot .bh-pill.violet{background:#f1ecfe;color:#6d28d9}
#bhRoot .stk-play{font-size:9.5px;padding:3px 8px;border-radius:6px;border:none}

/* Segment / insight / offer cards and KPI grid */
#bhRoot .act{border:1px solid var(--ln);border-left:3px solid var(--ac);border-radius:10px;padding:12px 18px;margin:0 20px 10px}
#bhRoot .act-t{font-size:12.5px}
#bhRoot .act-d{font-size:11px;margin-top:2px}
#bhRoot .mg-grid{gap:12px;background:transparent;padding:0 20px 20px}
#bhRoot .mg-cell{border:1px solid var(--ln);border-radius:12px;padding:16px 18px;transition:background .15s,border-color .15s}
#bhRoot .mg-cell[onclick]:hover{background:#f5f3ff;border-color:#c4b5fd}
#bhRoot .mg-h{font-size:10px;letter-spacing:1px;color:#64748b}
#bhRoot .mg-kpi{font-size:20px;color:#0f172a}

/* Helper panel (right card) */
#bhRoot .dm-hd{background:#fff;padding:14px 16px;gap:10px}
#bhRoot .dm-spark{width:18px;height:18px;color:var(--ac);flex-shrink:0;display:grid;place-items:center}
#bhRoot .dm-spark svg{width:17px;height:17px}
#bhRoot .dm-t{font-size:13px;font-weight:700;letter-spacing:.2px;color:#0f172a}
#bhRoot .dm-s{font-family:'Inter',sans-serif;font-size:8.5px;font-weight:600;letter-spacing:.5px;color:#64748b;margin-top:3px}
#bhRoot .dm-ready{font-family:'Inter',sans-serif;font-size:9.5px;letter-spacing:1px;color:#15803d;background:#e8f7ee;border:none;padding:3px 10px}
#bhRoot .mira-tools{gap:4px}
#bhRoot .mira-btn{width:24px;height:24px;border-radius:7px;font-size:12px;color:#334155;border-color:var(--ln2)}
#bhRoot .dm-chat{padding:18px}

/* Friendly greeting while a chat is empty (pure CSS, disappears on first message) */
#bhRoot .dm-chat:empty{flex-direction:row;align-items:flex-start;gap:10px}
#bhRoot .dm-chat:empty::before{content:'\1F44B';width:30px;height:30px;flex-shrink:0;border-radius:50%;background:#fff;border:1px solid var(--ln);display:grid;place-items:center;font-size:14px;box-shadow:var(--card-sh)}
#bhRoot .dm-chat:empty::after{background:var(--p2);border-radius:10px;padding:11px 13px;font-size:12.5px;line-height:1.65;color:#1e293b;white-space:pre-line;max-width:300px}
#bhRoot #mmChat-mk:empty::after{content:"Hi! I'm your Marketing helper.\A I can help you with campaigns, student segments, renewal copy, and more."}
#bhRoot #mmChat-sl:empty::after{content:"Hi! I'm your Sales helper.\A I can help you decide which students to contact, what to say, and how to convert them."}
#bhRoot #mmChat-ch:empty::after{content:"Hi! I'm your Customer Retention helper.\A I can help you spot at-risk students, plan saves, and choose offers."}

/* Chat bubbles */
#bhRoot .dm-chat .msg{font-size:12.5px;border-radius:10px;padding:11px 13px}
#bhRoot .dm-chat .msg.bot{background:var(--p2);border:none}
#bhRoot .dm-chat .msg.user{background:var(--ac);color:#fff}

/* Suggested prompts */
#bhRoot .dm-quick-hd{font-family:'Inter',sans-serif;font-size:9.5px;font-weight:700;letter-spacing:1.5px;color:#4c5a8a;padding:10px 16px 4px;border-top:1px solid var(--ln)}
#bhRoot .dm-quick-min{width:20px;height:20px;border-radius:6px;color:#475569;font-size:8px}
#bhRoot .dm-quick{padding:6px 16px 14px;gap:7px}
#bhRoot .dm-quick .qk{position:relative;padding:10px 30px 10px 12px;font-size:12px;font-weight:500;color:#1e293b;border:1px solid var(--ln2);border-radius:8px}
#bhRoot .dm-quick .qk::after{content:'';position:absolute;right:12px;top:50%;width:6px;height:6px;border-right:1.8px solid #64748b;border-top:1.8px solid #64748b;transform:translateY(-50%) rotate(45deg)}
#bhRoot .dm-quick .qk:hover::after{border-color:var(--ac-d)}
#bhRoot .dm-quick-reopen{font-family:'Inter',sans-serif;font-size:10px;letter-spacing:.5px}

/* Composer — rounded input with a separate square send button */
#bhRoot .dm-inbar{gap:8px;padding:10px 16px 14px;background:#fff;border-top:none}
#bhRoot .dm-inbar .in{border:1px solid var(--ln2);border-radius:10px;padding:0 14px;min-height:42px;font-size:12.5px;transition:border-color .15s,box-shadow .15s}
#bhRoot .dm-inbar .in:focus{border-color:var(--ac-m);box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .dm-inbar .send{width:42px;border-radius:10px;box-shadow:0 4px 12px rgba(16,24,40,.18)}
#bhRoot .dm-inbar .send svg{width:14px;height:14px;fill:#fff;stroke:#fff;stroke-width:1.5}

/* Sales · Scripts library — channel switcher + one card per script */
#bhRoot .mm-scr-tabs{display:flex;gap:6px;padding:0 20px 14px;flex-wrap:wrap}
#bhRoot .mm-scr-tab{display:inline-flex;align-items:center;gap:7px;font-family:var(--f1);font-size:12px;font-weight:600;color:var(--g2);background:#fff;border:1px solid var(--ln2);border-radius:999px;padding:6px 14px;cursor:pointer;transition:all .15s}
#bhRoot .mm-scr-tab:hover{border-color:var(--ac-m);color:var(--ac-d)}
#bhRoot .mm-scr-tab.on{background:var(--ac);border-color:var(--ac);color:#fff}
#bhRoot .mm-scr-count{font-size:10.5px;font-weight:700;background:var(--p2);color:var(--g2);border-radius:999px;padding:0 7px;line-height:18px}
#bhRoot .mm-scr-tab.on .mm-scr-count{background:rgba(255,255,255,.22);color:#fff}
#bhRoot .mm-scr-list{display:flex;flex-direction:column;gap:10px;padding:0 20px 20px}
#bhRoot .mm-scr{border:1px solid var(--ln);border-radius:10px;background:#fff;overflow:hidden}
#bhRoot .mm-scr-hd{display:flex;align-items:flex-start;gap:12px;padding:12px 14px;border-bottom:1px solid var(--ln);background:var(--p1)}
#bhRoot .mm-scr-n{width:22px;height:22px;flex-shrink:0;border-radius:50%;background:var(--ac-l);color:var(--ac-d);font-size:11px;font-weight:700;display:grid;place-items:center;margin-top:1px}
#bhRoot .mm-scr-meta{flex:1;min-width:0}
#bhRoot .mm-scr-t{font-size:13px;font-weight:600;color:var(--ink);line-height:1.4}
#bhRoot .mm-scr-when{font-size:11px;color:var(--g2);margin-top:2px}
#bhRoot .mm-scr-copy{flex-shrink:0;font-family:var(--f1);font-size:11px;font-weight:600;color:var(--ac-d);background:#fff;border:1px solid var(--ac-m);border-radius:7px;padding:5px 11px;cursor:pointer;transition:all .15s}
#bhRoot .mm-scr-copy:hover{background:var(--ac-l)}
#bhRoot .mm-scr-copy.done{color:#15803d;border-color:#a7f3d0;background:#ecfdf5}
#bhRoot .mm-scr-subject{padding:10px 14px 0;font-size:12.5px;color:var(--ink)}
#bhRoot .mm-scr-subject b{color:var(--g2);font-weight:600}
#bhRoot .mm-scr-body{padding:10px 14px 14px;font-size:12.5px;line-height:1.7;color:#1e293b;white-space:pre-line;overflow-wrap:anywhere}

/* Sales · Close & grow — summary strip + list tables */
#bhRoot .mg-grid.cg-kpis{grid-template-columns:repeat(auto-fit,minmax(200px,1fr))}
#bhRoot .cg-kpis .mg-kpi small{display:block;margin:4px 0 0;font-size:10.5px;line-height:1.4}
@media(max-width:1100px){#bhRoot .mg-grid.cg-kpis{grid-template-columns:1fr 1fr}}
#bhRoot .mm-scr-list > .dtbl{width:100%;margin:0}
#bhRoot .mm-scr-list > .act{margin:0}
#bhRoot .dtbl td.cg-why{font-size:11px;color:var(--g2);line-height:1.5;min-width:180px}
#bhRoot .dtbl td.cg-next{font-size:11.5px;font-weight:600;color:var(--ac-d);min-width:150px}
#bhRoot .cg-all{align-self:flex-start;margin-top:4px}

/* ══ Compact tiers for laptops ══
   At 100% browser zoom, laptops with Windows display scaling (125% / 150%)
   have a narrower CSS viewport (~1536px / ~1280px), so everything looks
   bigger. These tiers step the sizes down as the viewport narrows; wide
   screens keep the sizes above. Visual only. */
@media (max-width:1600px){
  .bh-page > .p-6{padding:16px}
  #bhRoot .bar{margin-bottom:12px}
  #bhRoot .dash{gap:12px}
  #bhRoot .atab{min-height:44px;gap:8px}
  #bhRoot .amono svg{width:16px;height:16px}
  #bhRoot .atab .a2{font-size:12.5px}
  #bhRoot .dash-left{padding:8px 0}
  #bhRoot .flowst{margin:1px 8px;padding:8px 8px;gap:10px}
  #bhRoot .flowst.cur{padding-left:8px}
  #bhRoot .flowst-dot{width:24px;height:24px;font-size:10.5px}
  #bhRoot .flowst-t{font-size:12.5px}
  #bhRoot .dvt{font-size:10px;letter-spacing:1.2px;padding:10px 4px;min-width:64px}
  #bhRoot .stack-intro{padding:16px 16px 12px}
  #bhRoot .si-h{font-size:10.5px;letter-spacing:1.6px;margin-bottom:6px}
  #bhRoot .si-p{font-size:12px;line-height:1.65}
  #bhRoot .dtbl{width:calc(100% - 32px);margin:0 16px 16px;font-size:11.5px}
  #bhRoot .dtbl th{font-size:9.5px;padding:8px 10px}
  #bhRoot .dtbl td{padding:7px 10px}
  #bhRoot .bh-acct{gap:8px;min-width:140px}
  #bhRoot .bh-av{width:24px;height:24px;font-size:11px;border-radius:6px}
  #bhRoot .bh-acct-c{font-size:10.5px}
  #bhRoot .bh-pill{font-size:10.5px;padding:2px 7px}
  #bhRoot .act{padding:10px 14px;margin:0 16px 8px}
  #bhRoot .act-t{font-size:12px}
  #bhRoot .mg-grid{gap:10px;padding:0 16px 16px}
  #bhRoot .mg-kpi{font-size:18px}
  #bhRoot .dm-hd{padding:12px 14px;gap:8px}
  #bhRoot .dm-t{font-size:12.5px}
  #bhRoot .dm-chat{padding:14px}
  #bhRoot .dm-chat .msg{font-size:12px;padding:10px 12px}
  #bhRoot .dm-chat:empty::after{font-size:12px;padding:10px 12px}
  #bhRoot .dm-quick-hd{padding:8px 14px 4px}
  #bhRoot .dm-quick{padding:4px 14px 10px;gap:6px}
  #bhRoot .dm-quick .qk{font-size:11.5px;padding:8px 26px 8px 10px}
  #bhRoot .dm-inbar{padding:8px 14px 12px}
  #bhRoot .dm-inbar .in{min-height:38px;font-size:12px;padding:0 12px}
  #bhRoot .dm-inbar .send{width:38px}
}
@media (max-width:1366px){
  .bh-page > .p-6{padding:12px}
  #bhRoot .dash{gap:10px}
  #bhRoot .atab{min-height:40px}
  #bhRoot .atab .a2{font-size:12px}
  #bhRoot .flowst-t{font-size:12px}
  #bhRoot .flowst-dot{width:22px;height:22px;font-size:10px}
  #bhRoot .dvt{font-size:9.5px;letter-spacing:1px;padding:9px 3px;min-width:56px}
  #bhRoot .si-p{font-size:11.5px}
  #bhRoot .dtbl{font-size:11px}
  #bhRoot .dtbl th{font-size:9px}
  #bhRoot .dm-t{font-size:12px}
  #bhRoot .dm-chat .msg{font-size:11.5px}
  #bhRoot .dm-chat:empty::after{font-size:11.5px}
  #bhRoot .dm-quick .qk{font-size:11px}
  #bhRoot .dm-inbar .in{min-height:36px;font-size:11.5px}
  #bhRoot .dm-inbar .send{width:36px}
}
@media(max-width:1180px){#bhRoot .dash{gap:12px}}
</style>

<script>
function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
}

var MM_STEP_LABELS = {
    'mk-campaign':'CAMPAIGN','mk-performance':'PERFORMANCE','mk-audience':'AUDIENCE','mk-insights':'INSIGHTS','mk-abtest':'A/B TEST',
    'sl-today':'TODAY','sl-accounts':'ACCOUNTS','sl-scripts':'SCRIPTS','sl-objections':'OBJECTIONS','sl-close':'CLOSE & GROW',
    'ch-savefirst':'SAVE FIRST','ch-rootcause':'ROOT CAUSE','ch-offers':'OFFERS','ch-watchlist':'WATCHLIST','ch-renew':'RENEW & WIN BACK','ch-abtest':'A/B TEST'
};

/* Real, step-grouped "Ask Mira" prompts from agents_pre_defined_prompts
   (is_mock_master = 1) — see routes/web.php's mock-master-helper route and
   database/seeders/MockMasterPredefinedPromptsSeeder. Keyed by the exact
   step_title stored in the DB, which is why MM_STEP_TITLE below must match
   those titles precisely (not the uppercase header labels). A/B test has no
   rows on purpose — no A/B-testing data source exists for Mock Master. */
var MM_PROMPTS_BY_AGENT = {
    mk: @json($mkPrompts),
    sl: @json($slPrompts),
    ch: @json($chPrompts)
};
var MM_STEP_TITLE = {
    'mk-campaign':'Campaign','mk-performance':'Performance','mk-audience':'Audience','mk-insights':'Insights','mk-abtest':'A/B test',
    'sl-today':'Today','sl-accounts':'Accounts','sl-scripts':'Scripts','sl-objections':'Objections','sl-close':'Close & grow',
    'ch-savefirst':'Save first','ch-rootcause':'Root cause','ch-offers':'Offers','ch-watchlist':'Watchlist','ch-renew':'Renew & win back','ch-abtest':'A/B test'
};
/* Prompts that name a specific student — a real name is picked from a
   list (see MM_CONTACT_NAMES) via mmNameForm(), then substituted for the
   literal "[name]" placeholder before the question is sent. */
var MM_NAME_PROMPT_SLUGS = {
    'mm-sl-acct-lookup-status': 1, 'mm-sl-acct-lookup-lastactive': 1, 'mm-sl-acct-lookup-contact': 1
};
var MM_CONTACT_NAMES = @json($mmContactNames ?? []);

/* Prompts that are answerable straight from data we've already computed
   server-side (see routes/web.php) — clicking these opens the "view list"
   popup with the real rows instantly, no AI round-trip and nothing
   paraphrased into a paragraph. Every other prompt still goes through
   mmAsk() (the free-text AI chat, grounded in MockMasterChatService). */
var MM_LISTS = {
    chAtRisk: @json($chAtRisk),
    chWatchlist: @json($chWatchlist),
    slProspects: @json($slProspects),
    slClose: @json($slClose),
    slAbandoned: @json($slAbandoned ?? []),
    chRenewals: @json($chRenewals ?? []),
    chWinBack: @json($chWinBack ?? []),
    mkTopScorers: @json($mkTopScorers),
    mkNewStudents: @json($mkNewStudents)
};
var MM_LIST_SLUGS = {
    'mm-mk-aud-highscorers':        { list: 'mkTopScorers', noun: 'student' },
    'mm-mk-aud-expiring-7d':        { list: 'chAtRisk',     noun: 'student' },
    'mm-mk-aud-renewal-watch-30d':  { list: 'chWatchlist',  noun: 'student' },
    'mm-mk-aud-new-14d':            { list: 'mkNewStudents',noun: 'student' },
    'mm-sl-today-who-call':         { list: 'slProspects',  noun: 'prospect' },
    'mm-sl-today-ready-upgrade':    { list: 'slClose',       noun: 'student' },
    'mm-sl-today-active-no-package':{ list: 'slClose',       noun: 'student' },
    'mm-sl-close-trial-convert':    { list: 'slClose',       noun: 'student' },
    'mm-sl-close-most-tests-no-upgrade': { list: 'slClose',  noun: 'student' },
    'mm-sl-close-renewal-upsell':   { list: 'chRenewals',   noun: 'student' },
    'mm-ch-renew-due':              { list: 'chRenewals',   noun: 'student' },
    'mm-ch-renew-winback':          { list: 'chWinBack',    noun: 'student' },
    'mm-ch-save-who-churn':         { list: 'chAtRisk',     noun: 'student' },
    'mm-ch-save-inactive-highrisk': { list: 'chAtRisk',     noun: 'student' },
    'mm-ch-watch-drifting':         { list: 'chWatchlist',  noun: 'student' },
    'mm-ch-watch-highvalue':        { list: 'chWatchlist',  noun: 'student' }
};
/* Column labels for the "view list" popup — only keys actually present on
   the rows are shown, so one table works for every dataset above. */
var MM_LIST_COL_LABELS = {
    name: 'Student', sub: 'Package / interest', value: 'Package value', stage: 'Stage',
    readiness: 'Readiness', trust: 'Trust', approach: 'Approach', lastActive: 'Last active',
    intent: 'Intent', play: 'Play', detail: 'Detail', avg_score: 'Avg score', joined: 'Joined',
    inactiveDays: 'Days inactive', valueAtRisk: 'Value at risk', risk: 'Risk',
    email: 'Email', phone: 'Mobile number',
    score: 'Score', signals: 'Signals', package: 'Package', amount: 'Amount', attempted: 'Attempted',
    expires: 'Expires', expired: 'Expired', action: 'Next step'
};
/* Columns always shown first (contact info), regardless of where they fall
   in MM_LIST_COL_LABELS above — every list here is a list of people to
   actually reach out to, so email/phone should never be scrolled out of
   view in a wide table. */
var MM_LIST_COL_PRIORITY = ['name', 'email', 'phone'];
var MM_LIST_MORE_BASE = '{{ url('/app/mock-master-helper/more') }}';

function mmRenderQuick(agent, key) {
    var full = agent + '-' + key;
    var title = MM_STEP_TITLE[full];
    var list = (MM_PROMPTS_BY_AGENT[agent] && MM_PROMPTS_BY_AGENT[agent][title]) || [];
    var box = document.getElementById('mmQuick-' + agent);
    if (!box) return;
    if (!list.length) {
        box.innerHTML = '<div style="padding:14px 4px;color:var(--g3);font-size:12px">No suggested questions for this step yet.</div>';
        return;
    }
    box.innerHTML = list.map(function (p) {
        return '<button type="button" class="qk" onclick="mmAskPrompt(\'' + agent + '\',\'' + p.slug + '\')">' + p.label.replace(/</g, '&lt;') + '</button>';
    }).join('');
}

function mmFindPrompt(agent, slug) {
    var list = [].concat.apply([], Object.values(MM_PROMPTS_BY_AGENT[agent] || {}));
    return list.find(function (p) { return p.slug === slug; });
}

function mmAskPrompt(agent, slug) {
    var prompt_ = mmFindPrompt(agent, slug);
    if (!prompt_) return;

    if (MM_NAME_PROMPT_SLUGS[slug]) {
        return mmNameForm(agent, slug, prompt_.label);
    }
    if (MM_LIST_SLUGS[slug]) {
        return mmShowList(agent, slug, prompt_.label);
    }
    mmAsk(agent, prompt_.label);
}

/* "Which student?" picker — a real name list (MM_CONTACT_NAMES) via
   <datalist>, same pattern as Business Helpers' Retention name form.
   Replaces a free-text prompt() dialog with a pick-from-list input right
   inside the chat. */
function mmNameForm(agent, slug, label) {
    var chat = document.getElementById('mmChat-' + agent);
    var fid = 'mmnm' + Math.random().toString(36).slice(2, 8);
    var opts = MM_CONTACT_NAMES.map(function (n) { return '<option value="' + n.replace(/"/g, '&quot;') + '"></option>'; }).join('');

    var wrap = document.createElement('div');
    wrap.className = 'msg bot';
    wrap.innerHTML = '<p>Which student?</p>' +
        '<div class="nmform">' +
            '<input class="nmin" id="' + fid + '" list="' + fid + '-l" placeholder="Start typing a student name…" autocomplete="off">' +
            '<datalist id="' + fid + '-l">' + opts + '</datalist>' +
            '<button type="button" class="qk" onclick="mmNameSubmit(\'' + agent + '\',\'' + fid + '\',\'' + slug + '\')">Ask</button>' +
        '</div>';
    chat.appendChild(wrap);
    chat.scrollTop = chat.scrollHeight;

    var input = document.getElementById(fid);
    input.focus();
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); mmNameSubmit(agent, fid, slug); }
    });
}

function mmNameSubmit(agent, fid, slug) {
    var input = document.getElementById(fid);
    var name = input ? input.value.trim() : '';
    if (!name) { if (input) input.focus(); return; }
    if (input) input.disabled = true;
    var prompt_ = mmFindPrompt(agent, slug);
    var label = (prompt_ ? prompt_.label : '').replace(/\[name\]/i, name);
    mmAsk(agent, label);
}

/* "View list" popup — the real rows behind a who/which question, shown as
   a clean table instead of a paragraph. No AI call: this data is already
   computed server-side (see routes/web.php's mock-master-helper route). */
function mmShowList(agent, slug, label) {
    var chat = document.getElementById('mmChat-' + agent);
    var cfg = MM_LIST_SLUGS[slug];
    var rows = MM_LISTS[cfg.list] || [];

    var userBubble = document.createElement('div');
    userBubble.className = 'msg user';
    userBubble.textContent = label;
    chat.appendChild(userBubble);

    var botBubble = document.createElement('div');
    botBubble.className = 'msg bot';
    if (!rows.length) {
        botBubble.innerHTML = '<p>No ' + cfg.noun + 's match this right now.</p>';
    } else {
        var id = 'mmlist' + Math.random().toString(36).slice(2, 9);
        // offset picks up where the page's initial embed left off — the
        // "Load more" button fetches from here onward, straight from the DB.
        MM_LIST_CACHE[id] = { rows: rows.slice(), noun: cfg.noun, label: label, dataset: cfg.list, offset: rows.length, hasMore: true };
        botBubble.innerHTML = '<p>' + rows.length + ' ' + cfg.noun + (rows.length === 1 ? '' : 's') + ' found.</p>' +
            '<button type="button" class="qk" onclick="openMmListModal(\'' + id + '\')">View the list →</button>';
    }
    chat.appendChild(botBubble);
    chat.scrollTop = chat.scrollHeight;
}

var MM_LIST_CACHE = {};
/* Sales · Close & grow "View all" — opens the same list popup (with Load
   more from the database) for one of the Close & grow datasets. */
function mmOpenDataset(dataset, label) {
    var rows = MM_LISTS[dataset] || [];
    if (!rows.length) return;
    var id = 'mmds-' + dataset;
    MM_LIST_CACHE[id] = { rows: rows.slice(), noun: 'student', label: label, dataset: dataset, offset: rows.length, hasMore: true };
    openMmListModal(id);
}
function mmListCols(rows) {
    var present = Object.keys(MM_LIST_COL_LABELS).filter(function (k) { return k in rows[0]; });
    if (!present.length) present = Object.keys(rows[0]);
    var priority = MM_LIST_COL_PRIORITY.filter(function (k) { return present.indexOf(k) > -1; });
    var rest = present.filter(function (k) { return priority.indexOf(k) === -1; });
    return priority.concat(rest);
}
function mmRenderListTable(entry) {
    var rows = entry.rows;
    var cols = mmListCols(rows);
    var head = '<tr><th>#</th>' + cols.map(function (k) {
        var label = MM_LIST_COL_LABELS[k] || k.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        return '<th>' + label + '</th>';
    }).join('') + '</tr>';
    var body = rows.map(function (r, i) {
        return '<tr><td>' + (i + 1) + '</td>' + cols.map(function (k) {
            var v = r[k];
            return '<td>' + (v === null || v === undefined || v === '' ? '—' : escapeHtml(v)) + '</td>';
        }).join('') + '</tr>';
    }).join('');

    var footer = entry.hasMore
        ? '<button type="button" class="qk" id="mmListMoreBtn" onclick="loadMoreMmList(\'' + entry._id + '\')">Load more →</button>'
        : '<p style="color:var(--g3);font-size:11.5px;margin-top:10px">That\'s everyone — no more results.</p>';

    document.getElementById('mmListModalBody').innerHTML = '<table>' + head + body + '</table>' + footer;
}
function openMmListModal(id) {
    var entry = MM_LIST_CACHE[id];
    if (!entry || !entry.rows.length) return;
    entry._id = id;

    document.getElementById('mmListModalTitle').textContent = entry.label;
    mmRenderListTable(entry);
    document.getElementById('mmListModalOverlay').classList.add('show');
}
function loadMoreMmList(id) {
    var entry = MM_LIST_CACHE[id];
    if (!entry) return;
    var btn = document.getElementById('mmListMoreBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Loading…'; }

    fetch(MM_LIST_MORE_BASE + '/' + encodeURIComponent(entry.dataset) + '?offset=' + entry.offset)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var newRows = data.rows || [];
            entry.rows = entry.rows.concat(newRows);
            entry.offset += newRows.length;
            entry.hasMore = !!data.hasMore;
            mmRenderListTable(entry);
        })
        .catch(function () {
            if (btn) { btn.disabled = false; btn.textContent = 'Load more → (try again)'; }
        });
}
function closeMmListModal() {
    var overlay = document.getElementById('mmListModalOverlay');
    if (overlay) overlay.classList.remove('show');
}

/* Sales · Scripts — switch between the Call / Email / WhatsApp lists, and
   copy one script (with its email subject) to the clipboard. */
function mmScriptChannel(btn) {
    var panel = btn.closest('.mm-panel');
    var ch = btn.getAttribute('data-ch');
    panel.querySelectorAll('.mm-scr-tab').forEach(function (t) {
        var on = t === btn;
        t.classList.toggle('on', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    panel.querySelectorAll('.mm-scr-list').forEach(function (l) {
        l.style.display = l.getAttribute('data-ch') === ch ? '' : 'none';
    });
}
function mmCopyScript(btn) {
    var card = btn.closest('.mm-scr');
    var subject = card.querySelector('.mm-scr-subject span');
    var text = (subject ? 'Subject: ' + subject.textContent + '\n\n' : '') + card.querySelector('.mm-scr-body').textContent;
    function done(ok) {
        btn.textContent = ok ? 'Copied' : 'Select & copy';
        btn.classList.toggle('done', ok);
        setTimeout(function () { btn.textContent = 'Copy'; btn.classList.remove('done'); }, 1800);
    }
    function fallback() {
        var range = document.createRange();
        range.selectNodeContents(card.querySelector('.mm-scr-body'));
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) {}
        done(ok);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () { done(true); }, fallback);
    } else {
        fallback();
    }
}

function mmSetAgent(agent) {
    document.getElementById('bhRoot').setAttribute('data-agent', agent);
    document.querySelectorAll('#bhRoot .atab').forEach(function (n) { n.classList.remove('on'); });
    document.getElementById('mmAgentTab-' + agent).classList.add('on');
    document.querySelectorAll('#bhRoot .dash').forEach(function (n) { n.classList.remove('on'); });
    document.getElementById('mmDash-' + agent).classList.add('on');
}

function mmSelectStep(agent, key) {
    var full = agent + '-' + key;
    var dash = document.getElementById('mmDash-' + agent);
    dash.querySelectorAll('.flowst').forEach(function (n) { n.classList.remove('cur'); });
    dash.querySelectorAll('.dvt').forEach(function (n) { n.classList.remove('on'); });
    dash.querySelectorAll('[data-step="' + full + '"]').forEach(function (n) { n.classList.add('cur'); });
    dash.querySelectorAll('[data-tab="' + full + '"]').forEach(function (n) { n.classList.add('on'); });
    dash.querySelectorAll('.mm-panel').forEach(function (n) { n.style.display = 'none'; });
    var panel = dash.querySelector('.mm-panel[data-panel="' + full + '"]');
    if (panel) panel.style.display = '';
    var hd = document.getElementById('mmQuickHd-' + agent);
    if (hd) hd.textContent = 'ASK MIRA · ' + (MM_STEP_LABELS[full] || key.toUpperCase());
    mmRenderQuick(agent, key);
}

var MM_AGENT_KEY = { mk: 'marketing', sl: 'sales', ch: 'retention' };

function mmAsk(agent, text) {
    text = (text || '').trim();
    if (!text) return;
    var chat = document.getElementById('mmChat-' + agent);

    var userBubble = document.createElement('div');
    userBubble.className = 'msg user';
    userBubble.textContent = text;
    chat.appendChild(userBubble);

    var botBubble = document.createElement('div');
    botBubble.className = 'msg bot';
    botBubble.innerHTML = '<span style="color:var(--g3)">Thinking…</span>';
    chat.appendChild(botBubble);
    chat.scrollTop = chat.scrollHeight;

    fetch('{{ route('client.mock-master-helper.ask') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ agent: MM_AGENT_KEY[agent], question: text })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        var answer = data.answer || "I couldn't get an answer just now.";
        botBubble.innerHTML = '<p>' + escapeHtml(answer).replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
        if (data.results && data.results.payments_list && data.results.payments_list.length) {
            mmAddResultsButton(botBubble, data.results);
        }
        chat.scrollTop = chat.scrollHeight;
    })
    .catch(function () {
        botBubble.innerHTML = '<p>I couldn\'t reach the AI just now — try again in a moment.</p>';
        chat.scrollTop = chat.scrollHeight;
    });
}

/* ── Performance KPI drill-down — click a headline card to see the real
   rows its number was counted from. ── */
var MM_KPI_URL = '{{ route('client.mock-master-helper.kpi', ['key' => '__KEY__']) }}';

function mmKpiOpen(key, label) {
    var overlay = document.getElementById('mmKpiModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'mmKpiModal';
        overlay.className = 'risk-modal-overlay';
        overlay.onclick = function (e) { if (e.target === overlay) mmKpiClose(); };
        overlay.innerHTML =
            '<div class="risk-modal">' +
                '<div class="risk-modal-hd"><span id="mmKpiTitle"></span>' +
                '<button type="button" onclick="mmKpiClose()" aria-label="Close">✕</button></div>' +
                '<div class="risk-modal-body" id="mmKpiBody"></div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    document.getElementById('mmKpiTitle').textContent = label || 'Details';
    document.getElementById('mmKpiBody').innerHTML = '<p style="color:#6b7280;font-size:12px;padding:8px 0">Loading…</p>';
    overlay.classList.add('show');

    fetch(MM_KPI_URL.replace('__KEY__', encodeURIComponent(key)), { headers: { 'Accept': 'application/json' } })
    .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
    .then(function (data) {
        document.getElementById('mmKpiTitle').textContent = data.title + ' · ' + Number(data.total).toLocaleString() + ' total';
        mmKpiRender(data, 1);
    })
    .catch(function () {
        document.getElementById('mmKpiBody').innerHTML = '<p style="color:#b91c1c;font-size:12px">Couldn\'t load the details — please try again.</p>';
    });
}

var MM_KPI_PAGE_SIZE = 10;

function mmKpiRender(data, page) {
    var rows = data.rows || [];
    var pages = Math.max(1, Math.ceil(rows.length / MM_KPI_PAGE_SIZE));
    page = Math.min(Math.max(1, page), pages);
    var start = (page - 1) * MM_KPI_PAGE_SIZE;
    var slice = rows.slice(start, start + MM_KPI_PAGE_SIZE);

    var head = '<tr><th>#</th>' + data.columns.map(function (c) { return '<th>' + escapeHtml(c.label) + '</th>'; }).join('') + '</tr>';
    var body = slice.map(function (r, i) {
        return '<tr><td>' + (start + i + 1) + '</td>' + data.columns.map(function (c) {
            return '<td>' + escapeHtml(r[c.key] === null || r[c.key] === undefined || r[c.key] === '' ? '—' : String(r[c.key])) + '</td>';
        }).join('') + '</tr>';
    }).join('');

    var truncated = rows.length < data.total
        ? ' · first ' + rows.length + ' of ' + Number(data.total).toLocaleString() + ' loaded'
        : '';
    var nav = rows.length > MM_KPI_PAGE_SIZE
        ? '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:12px;font-size:12px;color:#6b7280">' +
            '<span>Page ' + page + ' of ' + pages + truncated + '</span>' +
            '<div style="display:flex;gap:6px">' +
                '<button type="button" onclick="mmKpiRender(MM_KPI_DATA,' + (page - 1) + ')" ' + (page <= 1 ? 'disabled' : '') + ' style="padding:5px 12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;font-size:12px;font-weight:600;cursor:pointer">Previous</button>' +
                '<button type="button" onclick="mmKpiRender(MM_KPI_DATA,' + (page + 1) + ')" ' + (page >= pages ? 'disabled' : '') + ' style="padding:5px 12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;font-size:12px;font-weight:600;cursor:pointer">Next</button>' +
            '</div></div>'
        : (truncated ? '<p style="font-size:11px;color:#6b7280;margin:8px 0 0">' + truncated.replace(' · ', '') + '</p>' : '');

    MM_KPI_DATA = data;
    document.getElementById('mmKpiBody').innerHTML =
        '<table><thead>' + head + '</thead><tbody>' + (body || '<tr><td colspan="' + (data.columns.length + 1) + '">No rows.</td></tr>') + '</tbody></table>' + nav;
}

var MM_KPI_DATA = null;

function mmKpiClose() {
    var overlay = document.getElementById('mmKpiModal');
    if (overlay) overlay.classList.remove('show');
}

/* ── Chat results — "View results" button under an answer built from a
   date-range lookup, opening the full real list in a modal (same pattern
   as Business Helpers' "accounts behind this" modal). ── */
function mmAddResultsButton(bubble, results) {
    var btn = document.createElement('button');
    btn.type = 'button';
    var shown = results.requested_count ? results.payments_list.length : results.paid_payments_count;
    btn.textContent = 'View the ' + Number(shown).toLocaleString() + ' payment' + (shown === 1 ? '' : 's') + ' behind this →';
    btn.style.cssText = 'margin-top:10px;padding:6px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer';
    btn.onclick = function () { mmShowResultsModal(results); };
    bubble.appendChild(btn);
}

function mmShowResultsModal(results) {
    var overlay = document.getElementById('mmResultsModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'mmResultsModal';
        overlay.className = 'risk-modal-overlay';
        overlay.onclick = function (e) { if (e.target === overlay) mmCloseResultsModal(); };
        overlay.innerHTML =
            '<div class="risk-modal">' +
                '<div class="risk-modal-hd"><span id="mmResultsTitle"></span>' +
                '<button type="button" onclick="mmCloseResultsModal()" aria-label="Close">✕</button></div>' +
                '<div class="risk-modal-body" id="mmResultsBody"></div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var rows = results.payments_list.map(function (p, i) {
        return '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + escapeHtml(p.paid_on) + '</td>' +
            '<td>' + escapeHtml(p.student || '—') + '</td>' +
            '<td>' + escapeHtml(p.email || '—') + '</td>' +
            '<td>' + escapeHtml(p.product || '—') + '</td>' +
            '<td>$' + Number(p.amount).toLocaleString() + '</td>' +
            '</tr>';
    }).join('');

    var note = results.payments_list_truncated
        ? '<p style="font-size:11px;color:#6b7280;margin:8px 0 0">Showing the first ' + results.payments_list.length + ' of ' + Number(results.paid_payments_count).toLocaleString() + ' payments.</p>'
        : '';

    document.getElementById('mmResultsTitle').textContent = 'Paid subscriptions — ' + (results.period_label || '') + ' · ' + Number(results.paid_payments_count).toLocaleString() + ' payments · $' + Number(results.total_paid_amount).toLocaleString() + ' total';
    document.getElementById('mmResultsBody').innerHTML =
        '<table><thead><tr><th>#</th><th>Paid on</th><th>Student</th><th>Email</th><th>Subscription</th><th>Amount</th></tr></thead><tbody>' + rows + '</tbody></table>' + note;
    overlay.classList.add('show');
}

function mmCloseResultsModal() {
    var overlay = document.getElementById('mmResultsModal');
    if (overlay) overlay.classList.remove('show');
}

/* ── Campaign filter — re-renders only the Campaign table rows from the
   JSON endpoint, so the rest of the page never reloads. ── */
var MM_CAMPAIGN_URL = '{{ route('client.mock-master-helper.campaign-students') }}';

function mmEsc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function mmCampaignPager(data) {
    var info = document.getElementById('mmCampaignPageInfo');
    var prev = document.getElementById('mmCampaignPrev');
    var next = document.getElementById('mmCampaignNext');
    if (info) info.textContent = 'Page ' + data.page + ' of ' + data.last_page + ' · ' + Number(data.total).toLocaleString() + ' students';
    if (prev) { prev.disabled = data.page <= 1; prev.onclick = function () { mmCampaignGo(data.page - 1); }; }
    if (next) { next.disabled = data.page >= data.last_page; next.onclick = function () { mmCampaignGo(data.page + 1); }; }
}

function mmCampaignRender(data) {
    var body = document.getElementById('mmCampaignBody');
    if (!body) return;
    var students = data.students;
    mmCampaignPager(data);
    if (!students.length) {
        body.innerHTML = '<tr><td colspan="9" style="color:var(--g3);padding:20px">No renewal-ready students match these filters.</td></tr>';
        return;
    }
    MM_CAMPAIGN_ROWS = students;
    body.innerHTML = students.map(function (s, i) {
        var approach = s.approach === 'Offer-led'
            ? '<span class="bh-pill good">Offer-led</span>'
            : '<span class="bh-pill warn">Proof-led</span>';
        return '<tr>' +
            '<td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:' + mmEsc(s.color) + '">' + mmEsc(s.initial) + '</span>' +
                '<div><div class="bh-acct-n">' + mmEsc(s.name) + '</div><div class="bh-acct-c">(' + mmEsc(s.sub) + ')</div></div></div></td>' +
            '<td>' + mmEsc(s.value) + '</td>' +
            '<td>' + mmEsc(s.paymentDate) + '</td>' +
            '<td><span class="bh-pill ' + mmEsc(s.stageKind) + '">' + mmEsc(s.stage) + '</span></td>' +
            '<td>' + mmEsc(s.readiness) + '</td>' +
            '<td>' + mmEsc(s.trust) + '</td>' +
            '<td>' + approach + '</td>' +
            '<td>' + mmEsc(s.lastActive) + '</td>' +
            '<td><button type="button" onclick="mmStudentOpen(' + i + ')" style="padding:5px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">View</button></td>' +
            '</tr>';
    }).join('');
}

function mmCampaignLoad(query) {
    var body = document.getElementById('mmCampaignBody');
    if (body) body.innerHTML = '<tr><td colspan="9" style="color:var(--g3);padding:20px">Loading…</td></tr>';
    fetch(MM_CAMPAIGN_URL + (query ? '?' + query : ''), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
    .then(mmCampaignRender)
    .catch(function () {
        if (body) body.innerHTML = '<tr><td colspan="9" style="color:#b91c1c;padding:20px">Couldn\'t load students — please try again.</td></tr>';
    });
}

(function () {
    var tip = null;
    function show(el) {
        if (!tip) { tip = document.createElement('div'); tip.id = 'mmTip'; document.body.appendChild(tip); }
        tip.textContent = el.getAttribute('data-tip');
        tip.style.display = 'block';
        var r = el.getBoundingClientRect();
        var t = tip.getBoundingClientRect();
        var left = Math.min(Math.max(8, r.left + r.width / 2 - t.width / 2), window.innerWidth - t.width - 8);
        tip.style.left = left + 'px';
        tip.style.top = (r.top - t.height - 12) + 'px';
    }
    function hide() { if (tip) tip.style.display = 'none'; }
    document.addEventListener('mouseover', function (e) {
        var el = e.target.closest && e.target.closest('[data-tip]');
        if (el) show(el); else hide();
    });
    document.addEventListener('scroll', hide, true);
})();

var MM_CAMPAIGN_ROWS = @json($mkStudents);

var MM_MESSAGE_TEMPLATES = [
    { key: 'renewal', label: 'Renewal offer', hint: 'Renewal Due students', subject: 'Your {package} plan has ended, {first}',
      body: 'Hi {first}, your {package} plan has just ended. Renew this week and keep your practice momentum going. Reply RENEW and I\'ll send your link.' },
    { key: 'proof', label: 'Progress proof', hint: 'Proof-led students', subject: 'Your mock test progress, {first}',
      body: 'Hi {first}, you\'ve averaged {readiness} across your mock tests. Renew your {package} to keep building on that.' },
    { key: 'reengage', label: 'Re-engage', hint: 'Not logged in recently', subject: 'We miss you, {first}',
      body: 'Hi {first}, we miss you. Your {package} is still active. Log in today and take your next mock test.' },
    { key: 'thanks', label: 'Thank you + discount', hint: 'High-value packages', subject: 'Thanks for choosing us, {first}',
      body: 'Hi {first}, thanks for your {value} {package} purchase. Here\'s a discount on your next renewal, just reply to claim it.' },
    { key: 'upgrade', label: 'Upgrade from free', hint: 'Free package students', subject: 'Unlock full practice, {first}',
      body: 'Hi {first}, you\'re on the free {package}. Upgrade to unlock full practice tests and class links.' }
];

var MM_STUDENT_SELECTED = null;

function mmFillTemplate(text, s) {
    var first = String(s.name || '').split(' ')[0] || 'there';
    return String(text)
        .replace(/\{first\}/g, first)
        .replace(/\{name\}/g, s.name || 'there')
        .replace(/\{package\}/g, s.sub || 'package')
        .replace(/\{value\}/g, s.value || '')
        .replace(/\{readiness\}/g, String(s.readiness));
}

function mmStudentOpen(index) {
    var s = MM_CAMPAIGN_ROWS[index];
    if (!s) return;
    MM_STUDENT_SELECTED = s;

    var overlay = document.getElementById('mmStudentModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'mmStudentModal';
        overlay.className = 'risk-modal-overlay';
        overlay.onclick = function (e) { if (e.target === overlay) mmStudentClose(); };
        overlay.innerHTML =
            '<div class="risk-modal" style="max-width:920px;width:100%">' +
                '<div class="risk-modal-hd"><span id="mmStudentTitle"></span>' +
                '<button type="button" onclick="mmStudentClose()" aria-label="Close">✕</button></div>' +
                '<div class="risk-modal-body" id="mmStudentBody" style="padding:18px 22px 22px"></div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var chips = MM_MESSAGE_TEMPLATES.map(function (t) {
        return '<label style="display:block;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;cursor:pointer;background:#fff">' +
            '<input type="radio" name="mmTpl" value="' + t.key + '" onchange="mmPickTemplate(\'' + t.key + '\')" style="margin-right:6px">' +
            '<b style="font-size:12.5px">' + escapeHtml(t.label) + '</b>' +
            '<div style="font-size:11px;color:#6b7280;margin:4px 0 0 20px">' + escapeHtml(t.hint) + '</div></label>';
    }).join('');

    document.getElementById('mmStudentTitle').textContent = s.name;
    document.getElementById('mmStudentBody').innerHTML =
        '<div id="mmMsgWrap" style="display:grid;grid-template-columns:1fr;gap:20px;align-items:start">' +
            '<div><div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;margin-bottom:8px">Choose a message</div>' +
                '<div style="display:grid;gap:8px">' + chips + '</div></div>' +
            '<div id="mmMsgArea" style="display:none">' +
                '<div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;margin-bottom:8px">Edit and send</div>' +
                '<label style="font-size:11px;font-weight:700;color:#6b7280">Email subject <span style="font-weight:400">(email only)</span></label>' +
                '<input id="mmMsgSubject" type="text" style="width:100%;margin:4px 0 10px;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px">' +
                '<label style="font-size:11px;font-weight:700;color:#6b7280">Message <span style="font-weight:400">(used for both Email and WhatsApp; WhatsApp has no subject line)</span></label>' +
                '<textarea id="mmMsgBody" rows="8" style="width:100%;margin-top:4px;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-family:inherit"></textarea>' +
                '<div style="display:flex;gap:10px;margin-top:14px;justify-content:flex-end">' +
                    '<button type="button" id="mmSendEmail" disabled onclick="mmSendEmail()"></button>' +
                    '<button type="button" id="mmSendWa" disabled onclick="mmSendWhatsApp()"></button>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<p id="mmMsgHint" style="font-size:11.5px;color:#6b7280;margin:10px 0 0;text-align:right">Pick a message above to enable sending.</p>';
    mmSetSendEnabled(false);
    overlay.classList.add('show');
}

function mmPickTemplate(key) {
    var t = MM_MESSAGE_TEMPLATES.find(function (x) { return x.key === key; });
    if (!t || !MM_STUDENT_SELECTED) return;
    document.getElementById('mmMsgArea').style.display = 'block';
    document.getElementById('mmMsgWrap').style.gridTemplateColumns = '1fr 1.4fr';
    document.getElementById('mmMsgSubject').value = mmFillTemplate(t.subject, MM_STUDENT_SELECTED);
    document.getElementById('mmMsgBody').value = mmFillTemplate(t.body, MM_STUDENT_SELECTED);
    mmSetSendEnabled(true);
}

function mmSetSendEnabled(on) {
    var base = 'display:inline-flex;align-items:center;justify-content:center;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;border:none';
    var email = document.getElementById('mmSendEmail');
    var wa = document.getElementById('mmSendWa');
    var s = MM_STUDENT_SELECTED || {};
    var canEmail = on && !!s.email;
    var canWa = on && !!String(s.phone || '').replace(/\D/g, '');
    email.textContent = 'Email';
    wa.textContent = 'WhatsApp';
    email.disabled = !canEmail;
    email.style.cssText = base + (canEmail ? ';background:#2563eb;color:#fff;cursor:pointer' : ';background:#e5e7eb;color:#9ca3af;cursor:not-allowed');
    wa.disabled = !canWa;
    wa.style.cssText = base + (canWa ? ';background:#16a34a;color:#fff;cursor:pointer' : ';background:#e5e7eb;color:#9ca3af;cursor:not-allowed');
    document.getElementById('mmMsgHint').textContent = on
        ? (canEmail || canWa ? 'Edit the message if you like, then send it.' : 'This student has no email or phone on file.')
        : 'Pick a message above to enable sending.';
}

var MM_SEND_EMAIL_URL = '{{ route('client.mock-master-helper.send-email') }}';

function mmSendEmail() {
    var s = MM_STUDENT_SELECTED;
    if (!s || !s.email) return;
    var btn = document.getElementById('mmSendEmail');
    var hint = document.getElementById('mmMsgHint');
    var subject = document.getElementById('mmMsgSubject').value;
    var body = document.getElementById('mmMsgBody').value;

    btn.disabled = true;
    btn.textContent = 'Sending…';
    hint.style.color = '#6b7280';
    hint.textContent = 'Sending to ' + s.email + '…';

    fetch(MM_SEND_EMAIL_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ student_id: s.studentId, subject: subject, body: body })
    })
    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
    .then(function (res) {
        btn.textContent = 'Email';
        btn.disabled = false;
        hint.style.color = res.ok ? '#16a34a' : '#b91c1c';
        hint.textContent = res.data.message || (res.ok ? 'Email sent.' : 'The email could not be sent.');
    })
    .catch(function () {
        btn.textContent = 'Email';
        btn.disabled = false;
        hint.style.color = '#b91c1c';
        hint.textContent = "Couldn't reach the server — please try again.";
    });
}

function mmSendWhatsApp() {
    var s = MM_STUDENT_SELECTED;
    var digits = s ? String(s.phone || '').replace(/\D/g, '') : '';
    if (!digits) return;
    var body = document.getElementById('mmMsgBody').value;
    window.open('https://wa.me/' + digits + '?text=' + encodeURIComponent(body), '_blank', 'noopener');
}

function mmStudentClose() {
    var overlay = document.getElementById('mmStudentModal');
    if (overlay) overlay.classList.remove('show');
}

var MM_CAMPAIGN_SORT = { key: null, dir: 'asc' };

function mmCampaignSortBy(key) {
    if (MM_CAMPAIGN_SORT.key === key) {
        if (MM_CAMPAIGN_SORT.dir === 'asc') { MM_CAMPAIGN_SORT.dir = 'desc'; }
        else { MM_CAMPAIGN_SORT.key = null; MM_CAMPAIGN_SORT.dir = 'asc'; }
    } else {
        MM_CAMPAIGN_SORT.key = key;
        MM_CAMPAIGN_SORT.dir = 'asc';
    }
    mmCampaignSortIndicators();
    mmCampaignGo(1);
}

if (document.readyState !== 'loading') { mmCampaignSortIndicators(); }
else { document.addEventListener('DOMContentLoaded', function () { mmCampaignSortIndicators(); }); }

function mmCampaignSortIndicators() {
    document.querySelectorAll('.mm-sort-ind').forEach(function (el) {
        var active = MM_CAMPAIGN_SORT.key === el.getAttribute('data-for');
        el.textContent = active ? (MM_CAMPAIGN_SORT.dir === 'asc' ? ' ▲' : ' ▼') : ' ⇅';
        el.style.color = active ? '#7c3aed' : '#9ca3af';
    });
}

function mmCampaignParams(page) {
    var form = document.getElementById('mmCampaignFilter');
    var params = new URLSearchParams();
    new FormData(form).forEach(function (v, k) { if (v) params.append(k, v); });
    if (page && page > 1) params.set('page', page);
    if (MM_CAMPAIGN_SORT.key) {
        params.set('sort', MM_CAMPAIGN_SORT.key);
        params.set('dir', MM_CAMPAIGN_SORT.dir);
    }
    return params;
}

function mmCampaignGo(page) {
    var params = mmCampaignParams(page);
    mmCampaignLoad(params.toString());
    if (window.history && history.replaceState) {
        history.replaceState(null, '', '?' + params.toString());
    }
}

function mmCampaignSubmit(e) {
    e.preventDefault();
    mmCampaignGo(1);
    return false;
}

function mmCampaignReset() {
    var form = document.getElementById('mmCampaignFilter');
    if (form) form.querySelectorAll('select, input').forEach(function (el) { el.value = ''; });
    MM_CAMPAIGN_SORT = { key: null, dir: 'asc' };
    mmCampaignSortIndicators();
    mmCampaignLoad('');
    if (window.history && history.replaceState) {
        history.replaceState(null, '', window.location.pathname);
    }
}

/* ── Sync Data — pulls the 14 live Mock Master source tables from the
   remote PTE Portal database into their local mm_* mirrors (see
   App\Services\MockMaster\MockMasterSyncService), then reloads the page
   so every panel reflects the freshly-synced data. While it runs, the
   whole interface is blurred out via #mmSyncOverlay so nothing looks
   clickable mid-copy. ── */
function mmSyncOverlay() {
    var el = document.getElementById('mmSyncOverlay');
    if (!el) {
        el = document.createElement('div');
        el.id = 'mmSyncOverlay';
        el.innerHTML =
            '<div class="mm-sync-card">' +
                '<div class="mm-sync-spinner" id="mmSyncSpinner"></div>' +
                '<div class="mm-sync-text" id="mmSyncText">Syncing Mock Master data…</div>' +
                '<div class="mm-sync-sub" id="mmSyncSub">Pulling the latest 14 tables from the live server</div>' +
            '</div>';
        document.body.appendChild(el);
    }
    return el;
}

function mmSyncData(btn) {
    btn.disabled = true;
    var label = btn.querySelector('span');
    var icon = btn.querySelector('svg');
    if (label) label.textContent = 'Syncing…';
    if (icon) icon.style.animation = 'mmspin 0.8s linear infinite';

    mmSyncOverlay();
    document.body.classList.add('mm-syncing');

    fetch('{{ route('client.mock-master-helper.sync') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            if (label) label.textContent = 'Synced';
            var spinner = document.getElementById('mmSyncSpinner');
            var text = document.getElementById('mmSyncText');
            var sub = document.getElementById('mmSyncSub');
            if (spinner) { spinner.classList.add('done'); spinner.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'; }
            if (text) text.textContent = 'Sync complete';
            if (sub) sub.textContent = data.total_rows ? data.total_rows.toLocaleString() + ' rows updated' : 'Reloading…';
            // Brief pause so the "complete" state is actually visible before
            // the reload clears it, rather than blur-to-blank in one frame.
            setTimeout(function () { window.location.reload(); }, 700);
        } else {
            document.body.classList.remove('mm-syncing');
            if (label) label.textContent = 'Sync failed';
            if (icon) icon.style.animation = '';
            btn.disabled = false;
            alert(data.message || 'Sync failed — please try again.');
        }
    })
    .catch(function () {
        document.body.classList.remove('mm-syncing');
        if (label) label.textContent = 'Sync failed';
        if (icon) icon.style.animation = '';
        btn.disabled = false;
        alert("Couldn't reach the server — please try again.");
    });
}

/* ── Collapse the whole left sidebar (same icon/behavior as Business Helpers) ── */
var MM_ICON_EXPAND = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
var MM_ICON_COMPRESS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3v3a2 2 0 0 1-2 2H4"/><path d="M15 3v3a2 2 0 0 0 2 2h3"/><path d="M9 21v-3a2 2 0 0 0-2-2H4"/><path d="M15 21v-3a2 2 0 0 1 2-2h3"/></svg>';
function toggleSidebarCollapse() {
    var sidebar = document.getElementById('platformSidebar');
    if (!sidebar) return;
    var collapsed = sidebar.classList.toggle('bh-sidebar-collapsed');
    if (collapsed) {
        sidebar.style.width = '0px'; sidebar.style.minWidth = '0px';
        sidebar.style.overflow = 'hidden'; sidebar.style.borderRightWidth = '0px';
    } else {
        sidebar.style.width = ''; sidebar.style.minWidth = '';
        sidebar.style.overflow = ''; sidebar.style.borderRightWidth = '';
    }
    var btn = document.getElementById('mmFullBtn');
    btn.innerHTML = collapsed ? MM_ICON_COMPRESS : MM_ICON_EXPAND;
    btn.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
}

/* ── Suggestions panel collapse/reopen — one shared state, applied to
   whichever agent's panel is currently visible (only one .dash is ever
   showing at a time, so operating on every match is harmless). ── */
function mmCollapseQuick(){
    document.querySelectorAll('#bhRoot .dm-quick-hd').forEach(function (n) { n.classList.add('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick').forEach(function (n) { n.classList.add('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick-reopen').forEach(function (n) { n.classList.add('dm-quick-reopen-show'); });
}
function mmExpandQuick(){
    document.querySelectorAll('#bhRoot .dm-quick-hd').forEach(function (n) { n.classList.remove('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick').forEach(function (n) { n.classList.remove('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick-reopen').forEach(function (n) { n.classList.remove('dm-quick-reopen-show'); });
}

/* ── Helper (Mira) panel: drag-resize, minimise, maximise. Shared state on
   #bhRoot's --bh-mira-w custom property, so it applies to whichever agent
   dash is currently visible. ── */
var root = document.getElementById('bhRoot');
var BH_MIRA_MIN = 250, BH_MIRA_DEFAULT = 320;
var bhMiraState = { w: BH_MIRA_DEFAULT, min: false, maxed: false, prev: null };
function bhMiraCap(){
    var dash = document.querySelector('#bhRoot .dash.on');
    var total = dash ? dash.clientWidth : 1200;
    return Math.max(BH_MIRA_MIN + 40, total - 460);
}
function bhMiraApply(){
    var w = Math.min(Math.max(bhMiraState.w, BH_MIRA_MIN), bhMiraCap());
    root.style.setProperty('--bh-mira-w', w + 'px');
    root.classList.toggle('bh-mira-min', bhMiraState.min);
    document.querySelectorAll('#bhRoot .mira-btn[data-act="max"]').forEach(function (btn) {
        btn.innerHTML = bhMiraState.maxed ? '&#10005;' : '&#9974;';
        btn.title = bhMiraState.maxed ? 'Restore' : 'Maximise';
    });
}
function mmMira(action){
    if (action === 'min'){ bhMiraState.min = true; bhMiraState.maxed = false; }
    else if (action === 'restore'){ bhMiraState.min = false; }
    else if (action === 'max'){
        if (bhMiraState.maxed){ bhMiraState.maxed = false; bhMiraState.w = bhMiraState.prev || BH_MIRA_DEFAULT; }
        else { bhMiraState.prev = bhMiraState.w; bhMiraState.maxed = true; bhMiraState.min = false; bhMiraState.w = bhMiraCap(); }
    }
    bhMiraApply();
}
document.querySelectorAll('#bhRoot .mira-resize').forEach(function (h) {
    var dragging = false, startX = 0, startW = 0;
    h.addEventListener('pointerdown', function (e){
        dragging = true; startX = e.clientX;
        startW = parseFloat(getComputedStyle(root).getPropertyValue('--bh-mira-w')) || BH_MIRA_DEFAULT;
        h.classList.add('dragging'); try { h.setPointerCapture(e.pointerId); } catch (_) {}
        document.body.style.userSelect = 'none'; e.preventDefault();
    });
    h.addEventListener('pointermove', function (e){
        if (!dragging) return;
        var w = startW + (startX - e.clientX);
        bhMiraState.maxed = false;
        if (w < BH_MIRA_MIN){ bhMiraState.min = true; root.classList.add('bh-mira-min'); }
        else {
            bhMiraState.min = false;
            bhMiraState.w = Math.min(Math.max(w, BH_MIRA_MIN), bhMiraCap());
            root.classList.remove('bh-mira-min');
            root.style.setProperty('--bh-mira-w', bhMiraState.w + 'px');
        }
    });
    function stop(e){
        if (!dragging) return;
        dragging = false; h.classList.remove('dragging'); document.body.style.userSelect = '';
        try { h.releasePointerCapture(e.pointerId); } catch (_) {}
        bhMiraApply();
    }
    h.addEventListener('pointerup', stop);
    h.addEventListener('pointercancel', stop);
    h.addEventListener('dblclick', function (){ bhMiraState.w = BH_MIRA_DEFAULT; bhMiraState.maxed = false; bhMiraApply(); });
    h.addEventListener('keydown', function (e){
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        e.preventDefault();
        var step = (e.shiftKey ? 48 : 16) * (e.key === 'ArrowLeft' ? 1 : -1);
        bhMiraState.w = Math.min(Math.max(bhMiraState.w + step, BH_MIRA_MIN), bhMiraCap());
        bhMiraState.maxed = false; bhMiraApply();
    });
});
window.addEventListener('resize', function (){ bhMiraApply(); });

/* ── Steps panel: drag-resize + collapse. Same pattern as the helper panel,
   mirrored on the other side, sharing --bh-left-w. ── */
var BH_LEFT_MIN = 170, BH_LEFT_DEFAULT = 220;
var bhLeftState = { w: BH_LEFT_DEFAULT, min: false };
function bhLeftCap(){
    var dash = document.querySelector('#bhRoot .dash.on');
    var total = dash ? dash.clientWidth : 1200;
    return Math.max(BH_LEFT_MIN + 40, total - 460);
}
function bhLeftApply(){
    var w = Math.min(Math.max(bhLeftState.w, BH_LEFT_MIN), bhLeftCap());
    root.style.setProperty('--bh-left-w', w + 'px');
    root.classList.toggle('bh-left-min', bhLeftState.min);
}
function mmLeft(action){
    if (action === 'min') bhLeftState.min = true;
    else if (action === 'restore') bhLeftState.min = false;
    bhLeftApply();
}
document.querySelectorAll('#bhRoot .left-resize').forEach(function (h) {
    var dragging = false, startX = 0, startW = 0;
    h.addEventListener('pointerdown', function (e){
        dragging = true; startX = e.clientX;
        startW = parseFloat(getComputedStyle(root).getPropertyValue('--bh-left-w')) || BH_LEFT_DEFAULT;
        h.classList.add('dragging'); try { h.setPointerCapture(e.pointerId); } catch (_) {}
        document.body.style.userSelect = 'none'; e.preventDefault();
    });
    h.addEventListener('pointermove', function (e){
        if (!dragging) return;
        var w = startW + (e.clientX - startX);
        if (w < BH_LEFT_MIN){ bhLeftState.min = true; root.classList.add('bh-left-min'); }
        else {
            bhLeftState.min = false;
            bhLeftState.w = Math.min(Math.max(w, BH_LEFT_MIN), bhLeftCap());
            root.classList.remove('bh-left-min');
            root.style.setProperty('--bh-left-w', bhLeftState.w + 'px');
        }
    });
    function stop(e){
        if (!dragging) return;
        dragging = false; h.classList.remove('dragging'); document.body.style.userSelect = '';
        try { h.releasePointerCapture(e.pointerId); } catch (_) {}
        bhLeftApply();
    }
    h.addEventListener('pointerup', stop);
    h.addEventListener('pointercancel', stop);
    h.addEventListener('dblclick', function (){ bhLeftState.w = BH_LEFT_DEFAULT; bhLeftState.min = false; bhLeftApply(); });
    h.addEventListener('keydown', function (e){
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        e.preventDefault();
        var step = (e.shiftKey ? 48 : 16) * (e.key === 'ArrowLeft' ? -1 : 1);
        bhLeftState.w = Math.min(Math.max(bhLeftState.w + step, BH_LEFT_MIN), bhLeftCap());
        bhLeftApply();
    });
});
window.addEventListener('resize', function (){ bhLeftApply(); });

/* ── Suggestions row height: drag-resize, collapsing past a threshold. ── */
var BH_QUICK_MIN = 90, BH_QUICK_DEFAULT = 220;
var bhQuickState = { h: BH_QUICK_DEFAULT };
function bhQuickApply(){
    root.style.setProperty('--bh-quick-h', Math.max(bhQuickState.h, BH_QUICK_MIN) + 'px');
}
document.querySelectorAll('#bhRoot .row-resize').forEach(function (h) {
    var dragging = false, startY = 0, startH = 0;
    function isCollapsed(){
        var q = h.parentElement.querySelector('.dm-quick');
        return !q || q.classList.contains('dm-quick-collapsed');
    }
    h.addEventListener('pointerdown', function (e){
        dragging = true; startY = e.clientY;
        var wasCollapsed = isCollapsed();
        var q = h.parentElement.querySelector('.dm-quick');
        startH = wasCollapsed ? BH_QUICK_MIN : Math.max((q || {}).offsetHeight || BH_QUICK_DEFAULT, BH_QUICK_MIN);
        h.classList.add('dragging'); try { h.setPointerCapture(e.pointerId); } catch (_) {}
        document.body.style.userSelect = 'none'; e.preventDefault();
    });
    h.addEventListener('pointermove', function (e){
        if (!dragging) return;
        var newH = startH + (startY - e.clientY);
        if (newH < BH_QUICK_MIN) { mmCollapseQuick(); }
        else { mmExpandQuick(); bhQuickState.h = Math.max(newH, BH_QUICK_MIN); root.style.setProperty('--bh-quick-h', bhQuickState.h + 'px'); }
    });
    function stop(e){
        if (!dragging) return;
        dragging = false; h.classList.remove('dragging'); document.body.style.userSelect = '';
        try { h.releasePointerCapture(e.pointerId); } catch (_) {}
        if (!isCollapsed()) bhQuickApply();
    }
    h.addEventListener('pointerup', stop);
    h.addEventListener('pointercancel', stop);
    h.addEventListener('dblclick', function (){ mmExpandQuick(); bhQuickState.h = BH_QUICK_DEFAULT; bhQuickApply(); });
});

mmRenderQuick('mk', 'campaign');
mmRenderQuick('sl', 'today');
mmRenderQuick('ch', 'savefirst');
bhMiraApply(); bhLeftApply(); bhQuickApply();
</script>

@endsection
