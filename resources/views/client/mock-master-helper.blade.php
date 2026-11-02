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

// key: the live audience behind each offer (counted in MockMasterDataService::offerQuery()).
// play / tag: advisory copy for the open card (Part 7).
$chOffers = [
    ['name' => 'One free extra mock test',        'meta' => 'For students inactive 10-20 days', 'key' => 'offer_inactive', 'tag' => 'Re-engage', 'icon' => 'gift',
     'play' => 'Send a friendly check-in with their last score and a free extra mock test to use this week. If there is no reply in 3 days, follow up with a short call.'],
    ['name' => '15% renewal discount',              'meta' => 'For high-value students at risk', 'key' => 'offer_highvalue', 'tag' => 'Renewal', 'icon' => 'discount',
     'play' => 'Lead with their progress first (their own score trend), then offer 15% off renewing before the package ends. Remind them again 2 days before the end date.'],
    ['name' => 'Free 15-min coaching call',          'meta' => 'For students with declining scores', 'key' => 'offer_declining', 'tag' => 'Coaching', 'icon' => 'call',
     'play' => 'Offer a free 15-minute call to review their weakest section from the last mock and agree one thing to practise before their next test.'],
];
// Small line icons for the Part 7 cards (Insights, Root cause, Offers, Audience).
$mmxIcons = [
    'score' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    'expired' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    'tests' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    'payment' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    'quiet' => '<path d="M17 18a5 5 0 0 0-10 0"/><path d="M12 9V2M4.2 10.2l1.4 1.4M1 18h2M21 18h2M18.4 11.6l1.4-1.4M23 22H1"/>',
    'gift' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
    'discount' => '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1.5"/>',
    'call' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
    'segment' => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 11l2 2 4-4"/>',
];
$mmxIco = fn ($k) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($mmxIcons[$k] ?? $mmxIcons['score']) . '</svg>';
$mmxChev = '<svg class="mmx-acc-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';

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
// Plan column pill class: "Coaching + Paid" -> "mm-plan coaching-paid" (see MockMasterDataService::planFor()).
$mmPlanClass = fn ($plan) => 'mm-plan ' . \Illuminate\Support\Str::slug((string) $plan);
$mmStageKind = fn ($label) => preg_match('/won|active|renew/i', (string) $label) ? 'good'
    : (preg_match('/lost|expired|fail/i', (string) $label) ? 'bad'
    : (preg_match('/decision|bought/i', (string) $label) ? 'violet' : 'info'));
// Campaign cells added in Nov 2026 (Expires, Readiness ring, Last active bar, Next best action).
// Same markup as mmxExpCell / mmxRing / mmxLastCell / mmxNbaCell in the script below.
$mmNbaIcons = ['Win back' => '🔁', 'Renew now' => '⏰', 'Renewal reminder' => '🔔', 'Re-engage' => '💤', 'Book a mock test' => '🎯', 'Lead with proof' => '🏅', 'Lead with offer' => '🏷️'];
$mmExpCell = function ($s) {
    $n = $s['expiresIn'] ?? null;
    if ($n === null) return '<span class="mmx-muted">—</span>';
    if (($s['stage'] ?? '') === 'Expired' || $n < 0) return '<span class="bh-pill bad">Expired</span>' . ($n < 0 && $n > -366 ? '<div class="mmx-sub">' . (-$n) . 'd ago</div>' : '');
    if ($n === 0) return '<span class="bh-pill warn">Today</span>';
    if ($n <= 7) return '<span class="bh-pill warn">in ' . $n . 'd</span>';
    if ($n <= 365) return '<span class="mmx-exp">in ' . $n . 'd</span>';
    return '<span class="mmx-muted">' . e($s['expireDate'] ?? '—') . '</span>';
};
$mmRing = function ($v) {
    $v = max(0, min(100, (int) $v));
    $c = $v >= 70 ? '#16a34a' : ($v >= 50 ? '#d97706' : '#dc2626');
    $len = 69.12; // 2πr, r = 11
    return '<span class="mmx-ring"><svg width="26" height="26" viewBox="0 0 26 26" aria-hidden="true"><circle cx="13" cy="13" r="11" fill="none" stroke="#eceef3" stroke-width="3"/>'
        . ($v > 0 ? '<circle cx="13" cy="13" r="11" fill="none" stroke="' . $c . '" stroke-width="3" stroke-linecap="round" stroke-dasharray="' . round($len * $v / 100, 2) . ' ' . $len . '" transform="rotate(-90 13 13)"/>' : '')
        . '</svg><b>' . $v . '</b></span>';
};
$mmLastCell = function ($s) {
    $d = $s['lastDays'] ?? null;
    $w = $d === null ? 0 : max(6, 100 - $d * 3.3);
    $c = $d === null ? '#d1d5db' : ($d < 7 ? '#16a34a' : ($d < 14 ? '#d97706' : '#dc2626'));
    return '<div class="mmx-last">' . e($s['lastActive'] ?? '—') . '<i class="mmx-bar"><b style="width:' . round($w) . '%;background:' . $c . '"></b></i></div>';
};
$mmNbaCell = fn ($s) => empty($s['nextAction']) ? '<span class="mmx-muted">—</span>'
    : '<span class="bh-pill ' . e($s['nextKind'] ?? 'info') . ' mmx-nba">' . ($mmNbaIcons[$s['nextAction']] ?? '') . ' ' . e($s['nextAction']) . '</span>';
@endphp

<div class="bh-page flex flex-col h-full overflow-hidden">

    {{-- Page Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Mock Master Helper</h1>
            <p class="text-xs text-gray-400 mt-0.5">Guided playbook for your Mock Master student data</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Table / Board for every agent (the Board covers the whole student list) --}}
            <div class="mmx-viewtg" id="mmViewTg" role="group" aria-label="Show as">
                <button type="button" class="on" data-v="table" onclick="mmViewSet('table')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>Table</button>
                <button type="button" data-v="board" onclick="mmViewSet('board')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="12" rx="1"/><rect x="17" y="3" width="4" height="15" rx="1"/></svg>Board</button>
            </div>
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

        {{-- Overview cards for the selected agent (live, collapsible) — filled by mmOvLoad() --}}
        <div class="mmx-ov" id="mmOverview">
            <div class="mmx-ov-hd">
                <span class="mmx-ov-t"><span data-ov-t="mk">Marketing</span><span data-ov-t="sl">Sales</span><span data-ov-t="ch">Customer Retention</span> overview</span>
                <span class="mmx-live" title="Refreshes every 2 minutes and after Sync Data"><i></i><span id="mmOvTime">Loading…</span></span>
                <button type="button" class="mmx-collapse" onclick="mmOvToggle()" aria-expanded="true" aria-controls="mmOverview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg><span>Hide</span>
                </button>
            </div>
            @foreach(['mk', 'sl', 'ch'] as $ovAgent)
            <div class="mmx-kpis" data-ov="{{ $ovAgent }}">
                @for($k = 0; $k < 5; $k++)
                <div class="mmx-kpi"><div class="mmx-kpi-l">&nbsp;</div><div class="mmx-kpi-r"><span class="mmx-skel"></span></div><div class="mmx-kpi-h">&nbsp;</div></div>
                @endfor
            </div>
            @endforeach
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
                    <button type="button" class="mmx-expand" onclick="mmFocusToggle()" title="Expand the table" aria-label="Expand the table"><svg class="mmx-ico-max" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg><svg class="mmx-ico-min" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg></button>
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="mk-campaign">
                        <div class="stack-intro">
                            <div class="si-h">WHAT YOU'RE LOOKING AT</div>
                            <div class="si-p">Your real renewal-ready students, each with the outreach call that matters most for them: whether to lead with <b>proof</b> or an <b>offer</b>, based on their own real trust score — not the pool average. Sorted by package value, biggest first.</div>
                        </div>
                        {{-- Quick segments (collapsible) — click a card to filter the table, the Board and Send to all --}}
                        <div class="mmx-seg-wrap" id="mmSegWrap">
                            <div class="mmx-seg-hd">
                                <b>Quick segments</b><span>Click a card to filter the list</span>
                                <button type="button" class="mmx-collapse" onclick="mmSegToggle()" aria-expanded="true" aria-controls="mmSegs">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg><span>Hide</span>
                                </button>
                            </div>
                            <div class="mmx-segs" id="mmSegs"></div>
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
                            <button type="button" onclick="mmBulkOpen()" data-tip="Email or WhatsApp every student that matches these filters (including the Plan and number filters on the columns)." style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:6px;border:1px solid #7c3aed;color:#7c3aed;font-size:12px;font-weight:600;cursor:pointer;background:#fff">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send to all</button>
                        </form>
                        <table class="dtbl">
                            <thead><tr><th data-tip="Renewal-ready students with a package. Click a name for contact details.">Student</th><th data-tip="Plan history. Free trial: only the free mock test. Paid: has bought a paid package. Coaching: Enrolled or Coaching access, never a paid package. Coaching + Paid: both. No purchase: nothing bought." data-mm-plan-filter>Plan</th><th data-sort-key="value" data-mm-num-filter onclick="mmCampaignSortBy('value')" data-tip="Amount paid for the selected Course" class="mm-sortable" style="cursor:pointer">Package value <span class="mm-sort-ind" data-for="value"></span></th><th data-mm-col-filter="payment" data-sort-key="payment" onclick="mmCampaignSortBy('payment')" data-tip="Date of the payment linked to this package. Shows — if none is linked." class="mm-sortable" style="cursor:pointer">Payment date <span class="mm-sort-ind" data-for="payment"></span></th><th data-mm-col-filter="expires" data-sort-key="expires" onclick="mmCampaignSortBy('expires')" data-tip="Days until the package ends. Orange = ends within 7 days; Expired = already ended." class="mm-sortable" style="cursor:pointer">Expires <span class="mm-sort-ind" data-for="expires"></span></th><th data-mm-col-filter="stage" data-sort-key="stage" onclick="mmCampaignSortBy('stage')" data-tip="Active, Renewal Due (expired within the last 14 days), or Expired." class="mm-sortable" style="cursor:pointer">Stage <span class="mm-sort-ind" data-for="stage"></span></th><th data-sort-key="readiness" data-mm-num-filter onclick="mmCampaignSortBy('readiness')" data-tip="Average score across all mock tests. 0 = no results" class="mm-sortable" style="cursor:pointer">Readiness <span class="mm-sort-ind" data-for="readiness"></span></th><th data-sort-key="trust" data-mm-num-filter onclick="mmCampaignSortBy('trust')" data-tip="Based on the percentage of payments completed. 50 = no payment history." class="mm-sortable" style="cursor:pointer">Trust <span class="mm-sort-ind" data-for="trust"></span></th><th data-mm-col-filter="approach" data-sort-key="approach" onclick="mmCampaignSortBy('approach')" data-tip="Proof-led when Trust is below 65; otherwise Offer-led." class="mm-sortable" style="cursor:pointer">Approach <span class="mm-sort-ind" data-for="approach"></span></th><th data-mm-col-filter="last" data-sort-key="last_active" onclick="mmCampaignSortBy('last_active')" data-tip="When they last logged in." class="mm-sortable" style="cursor:pointer">Last active <span class="mm-sort-ind" data-for="last_active"></span></th><th data-mm-col-filter="nba" data-tip="Suggested next step, most urgent first: Win back (expired), Renew now (renewal due), Renewal reminder (ends within 7 days), Re-engage (no login in 14+ days), Book a mock test (no results yet), then Lead with proof (Trust below 65) or Lead with offer.">Next best action</th><th data-tip="Open a message composer to email or WhatsApp this student.">Action</th></tr></thead>
                            <tbody id="mmCampaignBody">
                                @forelse($mkStudents as $s)
                                <tr>
                                    <td class="acctn" data-mm-stu data-sid="{{ $s['sid'] ?? '' }}" data-email="{{ $s['email'] ?? '' }}"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($s['name']) }}">{{ $mmInitial($s['name']) }}</span><div><div class="bh-acct-n">{{ $s['name'] }}</div><div class="bh-acct-c">({{ $s['sub'] }})</div></div></div></td><td><span class="{{ $mmPlanClass($s['plan'] ?? '') }}">{{ $s['plan'] ?? '—' }}</span></td>
                                    <td>{{ $s['value'] }}</td>
                                    <td>{{ $s['paymentDate'] }}</td>
                                    <td>{!! $mmExpCell($s) !!}</td>
                                    <td><span class="bh-pill {{ $mmStageKind($s['stage']) }}">{{ $s['stage'] }}</span></td>
                                    <td>{!! $mmRing($s['readiness']) !!}</td>
                                    <td>{{ $s['trust'] }}</td>
                                    <td>@if($s['approach'] === 'Offer-led')<span class="bh-pill good">Offer-led</span>@else<span class="bh-pill warn">Proof-led</span>@endif</td>
                                    <td>{!! $mmLastCell($s) !!}</td>
                                    <td>{!! $mmNbaCell($s) !!}</td><td><button type="button" onclick="mmStudentOpen({{ $loop->index }})" style="padding:5px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">View</button></td>
                                </tr>
                                @empty
                                <tr><td colspan="12" style="color:var(--g3);padding:20px">No renewal-ready students found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div id="mmCampaignPager" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:10px 14px;border-top:1px solid var(--ln);font-size:12px;color:var(--g3)">
                            <span id="mmCampaignPageInfo">Page {{ $mkPaged['page'] }} of {{ $mkPaged['last_page'] }} · {{ number_format($mkPaged['total']) }} students</span>
                            <div class="mmx-pg">
                                {{-- Rows per page, page numbers and "Go to page" (drawn by mmCampaignPager) --}}
                                <label class="mmx-pg-rows">Rows
                                    <select id="mmCampaignPer" onchange="MMX_MANUAL_PER = true; mmCampaignPerSet(this.value)">
                                        @foreach([10, 25, 50, 100] as $pp)<option value="{{ $pp }}" {{ ($mkPaged['per_page'] ?? 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>@endforeach
                                    </select>
                                </label>
                                <button type="button" id="mmCampaignPrev" onclick="mmCampaignGo({{ max(1, $mkPaged['page'] - 1) }})" {{ $mkPaged['page'] <= 1 ? 'disabled' : '' }} style="padding:5px 12px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;cursor:pointer">Previous</button>
                                <span class="mmx-pg-nums" id="mmCampaignPages"></span>
                                <button type="button" id="mmCampaignNext" onclick="mmCampaignGo({{ min($mkPaged['last_page'], $mkPaged['page'] + 1) }})" {{ $mkPaged['page'] >= $mkPaged['last_page'] ? 'disabled' : '' }} style="padding:5px 12px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;cursor:pointer">Next</button>
                                <form class="mmx-pg-go" novalidate onsubmit="return mmCampaignJump(event)">
                                    <label for="mmCampaignJumpIn">Go to page</label>
                                    <input id="mmCampaignJumpIn" type="number" min="1" max="{{ $mkPaged['last_page'] }}" placeholder="{{ $mkPaged['page'] }}" inputmode="numeric">
                                    <button type="submit">Go</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mm-panel" data-panel="mk-performance" style="display:none">
                        <div class="stack-intro"><div class="si-h">PLATFORM PERFORMANCE</div><div class="si-p">Headline numbers across your Mock Master student base.</div></div>
                        <div class="mg-grid mmx-perf-kpis">
                            @foreach($mkKpis as $k)
                            <div class="mg-cell" onclick="mmKpiOpen('{{ $k['key'] }}', this.querySelector('.mg-h').textContent)" style="cursor:pointer" title="Click to see the details"><div class="mg-h">{{ $k['label'] }}</div><div class="mg-kpi">{{ $k['value'] }} <small>{{ $k['sub'] }}</small></div></div>
                            @endforeach
                        </div>
                        {{-- Weekly score chart, section averages and highlights (Part 7) — filled by mmxTabLoad('performance') --}}
                        <div class="mmx-td" data-tab-data="performance"><div class="mmx-td-wait">Loading charts…</div></div>
                    </div>

                    <div class="mm-panel" data-panel="mk-audience" style="display:none">
                        <div class="stack-intro"><div class="si-h">STUDENT SEGMENTS</div><div class="si-p">Grouped by behavior and lifecycle stage. Click any card or bar to see the students in it.</div></div>
                        <div class="mmx-tiles">
                        @forelse($mkSegments as $seg)
                            @php [$segN, $segRest] = array_pad(explode(' students', $seg['meta'], 2), 2, ''); @endphp
                            <button type="button" class="mmx-tile2" onclick="mmKpiOpen('{{ $seg['key'] }}', '{{ $seg['name'] }}')" title="Click to see the students in this segment">
                                <span class="mmx-ins-ic">{!! $mmxIco('segment') !!}</span>
                                <span class="mmx-tile2-t">{{ $seg['name'] }}</span>
                                <b class="mmx-tile2-n">{{ $segN }}</b>
                                <span class="mmx-tile2-s">students{{ $segRest }}</span>
                                <span class="mmx-go">View students →</span>
                            </button>
                        @empty
                            <div class="act"><div class="act-t" style="color:var(--g3)">No segment data available.</div></div>
                        @endforelse
                        </div>
                        {{-- Breakdowns by course, package status, readiness and sign-up month (Part 7) — filled by mmxTabLoad('audience') --}}
                        <div class="mmx-td" data-tab-data="audience"><div class="mmx-td-wait">Loading breakdowns…</div></div>
                    </div>

                    <div class="mm-panel" data-panel="mk-insights" style="display:none">
                        <div class="stack-intro"><div class="si-h">KEY INSIGHTS</div><div class="si-p">Patterns worth acting on. Click a card to see the records behind it.</div></div>
                        @if(count($mkInsights))
                        <div class="mmx-ins-grid">
                            @foreach($mkInsights as $ins)
                            <button type="button" class="mmx-ins" onclick="mmKpiOpen('{{ $ins['key'] }}', '{{ $ins['text'] }}')" title="Click to see the records behind this insight">
                                <span class="mmx-ins-ic">{!! $mmxIco($ins['icon'] ?? 'score') !!}</span>
                                <b>{{ $ins['title'] ?? $ins['text'] }}</b>
                                <p>{{ $ins['text'] }}</p>
                                <span class="mmx-ins-big">{{ $ins['big'] ?? '' }}</span>
                                <span class="mmx-go">View the records →</span>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="act"><div class="act-t" style="color:var(--g3)">Not enough data yet to compute insights.</div></div>
                        @endif
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
                    <button type="button" class="mmx-expand" onclick="mmFocusToggle()" title="Expand the table" aria-label="Expand the table"><svg class="mmx-ico-max" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg><svg class="mmx-ico-min" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg></button>
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="sl-today">
                        <div class="stack-intro">
                            <div class="si-h">WHAT YOU'RE LOOKING AT</div>
                            <div class="si-p">The prospects that need your attention right now, ranked by <b>buying readiness</b> and <b>intent</b>. Use this to decide who to call today versus who to nurture.</div>
                        </div>
                        @include('client.partials.mm-list-filter', ['dataset' => 'slProspects', 'kind' => 'today', 'subLabel' => 'Course interest', 'dateLabel' => 'Joined'])
                        <table class="dtbl">
                            <thead><tr><th data-tip="Students whose packages are all $0 (free trial or coaching access) and who have never bought a paid package. Click a name for contact details.">Prospect</th><th data-tip="Plan history. Free trial: only the free mock test. Paid: has bought a paid package. Coaching: Enrolled or Coaching access, never a paid package. Coaching + Paid: both. No purchase: nothing bought." data-mm-plan-filter>Plan</th><th data-tip="Average overall mock test score (PTE scale, about 10 to 90). 0 = no scored tests yet." data-mm-num-filter>Readiness</th><th data-tip="Readiness + 20 if their profile is complete, otherwise Readiness - 20 (minimum 10)." data-mm-num-filter>Intent</th><th data-tip="70 if they verified their phone number (OTP), otherwise 40." data-mm-num-filter>Trust</th><th data-tip="Call when Readiness is 50 or more, otherwise Nurture.">Play</th><th data-tip="Open a message composer to email or WhatsApp this student.">Action</th></tr></thead>
                            <tbody>
                                @forelse($slProspects as $p)
                                <tr>
                                    <td class="acctn" data-mm-stu data-sid="{{ $p['sid'] ?? '' }}" data-email="{{ $p['email'] ?? '' }}"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($p['name']) }}">{{ $mmInitial($p['name']) }}</span><div><div class="bh-acct-n">{{ $p['name'] }}</div><div class="bh-acct-c">({{ $p['sub'] }})</div></div></div></td><td><span class="{{ $mmPlanClass($p['plan'] ?? '') }}">{{ $p['plan'] ?? '—' }}</span></td>
                                    <td>{{ $p['readiness'] }}</td>
                                    <td>{{ $p['intent'] }}</td>
                                    <td>{{ $p['trust'] }}</td>
                                    <td><span class="stk-play {{ $p['play'] === 'Call' ? 'call' : 'onboarding' }}">{{ $p['play'] }}</span></td>
                                    <td><button type="button" class="mm-act-btn" data-mm-compose="prospect" data-row="{{ json_encode($p) }}">View</button></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" style="color:var(--g3);padding:20px">No trial-only prospects found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="sl-accounts" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Every prospect in your pipeline, in one place.</div></div>
                        @include('client.partials.mm-list-filter', ['dataset' => 'slProspects', 'kind' => 'accounts', 'subLabel' => 'Course interest', 'dateLabel' => 'Joined'])
                        <table class="dtbl">
                            <thead><tr><th data-tip="Students whose packages are all $0 (free trial or coaching access) and who have never bought a paid package. Click a name for contact details.">Prospect</th><th data-tip="Plan history. Free trial: only the free mock test. Paid: has bought a paid package. Coaching: Enrolled or Coaching access, never a paid package. Coaching + Paid: both. No purchase: nothing bought." data-mm-plan-filter>Plan</th><th data-tip="Average overall mock test score (PTE scale, about 10 to 90). 0 = no scored tests yet." data-mm-num-filter>Readiness</th><th data-tip="Readiness + 20 if their profile is complete, otherwise Readiness - 20 (minimum 10)." data-mm-num-filter>Intent</th><th data-tip="70 if they verified their phone number (OTP), otherwise 40." data-mm-num-filter>Trust</th><th data-tip="Call when Readiness is 50 or more, otherwise Nurture.">Play</th><th data-tip="Open a message composer to email or WhatsApp this student.">Action</th></tr></thead>
                            <tbody>
                                @forelse($slProspects as $p)
                                <tr><td class="acctn" data-mm-stu data-sid="{{ $p['sid'] ?? '' }}" data-email="{{ $p['email'] ?? '' }}"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($p['name']) }}">{{ $mmInitial($p['name']) }}</span><div class="bh-acct-n">{{ $p['name'] }}</div></div></td><td><span class="{{ $mmPlanClass($p['plan'] ?? '') }}">{{ $p['plan'] ?? '—' }}</span></td><td>{{ $p['readiness'] }}</td><td>{{ $p['intent'] }}</td><td>{{ $p['trust'] }}</td><td>{{ $p['play'] }}</td><td><button type="button" class="mm-act-btn" data-mm-compose="prospect" data-row="{{ json_encode($p) }}">View</button></td></tr>
                                @empty
                                <tr><td colspan="7" style="color:var(--g3);padding:20px">No prospects found right now.</td></tr>
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
                            <div class="mm-scr mmx-acc {{ $i === 0 ? 'open' : '' }}">
                                <div class="mm-scr-hd mmx-acc-hd" onclick="mmxAccToggle(event, this)" role="button" tabindex="0" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                    <span class="mm-scr-n">{{ $i + 1 }}</span>
                                    <div class="mm-scr-meta">
                                        <div class="mm-scr-t">{{ $sc['title'] }}</div>
                                        <div class="mm-scr-when">Use when: {{ $sc['when'] }}</div>
                                    </div>
                                    <span class="mmx-tag">{{ $slChannelLabels[$ch] }}</span>
                                    <button type="button" class="mm-scr-copy" onclick="mmCopyScript(this)" title="Copy this script">Copy</button>
                                    {!! $mmxChev !!}
                                </div>
                                <div class="mmx-acc-bd"><div>
                                @if(!empty($sc['subject']))
                                <div class="mm-scr-subject"><b>Subject:</b> <span>{{ $sc['subject'] }}</span></div>
                                @endif
                                <div class="mm-scr-body">{{ $sc['body'] }}</div>
                                </div></div>
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
                            <div class="mm-scr mmx-acc {{ $i === 0 ? 'open' : '' }}">
                                <div class="mm-scr-hd mmx-acc-hd" onclick="mmxAccToggle(event, this)" role="button" tabindex="0" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                    <span class="mm-scr-n">{{ $i + 1 }}</span>
                                    <div class="mm-scr-meta">
                                        <div class="mm-scr-t">&ldquo;{{ $o['q'] }}&rdquo;</div>
                                        <div class="mm-scr-when">What it usually means: {{ $o['means'] }}</div>
                                    </div>
                                    <span class="mmx-tag">{{ $group['label'] }}</span>
                                    <button type="button" class="mm-scr-copy" onclick="mmCopyScript(this)" title="Copy this answer">Copy</button>
                                    {!! $mmxChev !!}
                                </div>
                                <div class="mmx-acc-bd"><div>
                                <div class="mm-scr-body">{{ $o['a'] }}</div>
                                </div></div>
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
                    <button type="button" class="mmx-expand" onclick="mmFocusToggle()" title="Expand the table" aria-label="Expand the table"><svg class="mmx-ico-max" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg><svg class="mmx-ico-min" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg></button>
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="ch-savefirst">
                        <div class="stack-intro">
                            <div class="si-h">RANKED STACK — WHO TO SAVE, IN ORDER</div>
                            <div class="si-p">Sorted by <b>churn risk × value at stake</b>. Click through and reach out before they lapse.</div>
                        </div>
                        @include('client.partials.mm-list-filter', ['dataset' => 'chAtRisk', 'kind' => 'savefirst', 'subLabel' => 'Subscription', 'dateLabel' => 'Expires'])
                        <table class="dtbl">
                            <thead><tr><th data-tip="Students whose package expires in the next 7 days. Click a name for contact details.">Student</th><th data-tip="Plan history. Free trial: only the free mock test. Paid: has bought a paid package. Coaching: Enrolled or Coaching access, never a paid package. Coaching + Paid: both. No purchase: nothing bought." data-mm-plan-filter>Plan</th><th data-tip="Days since their last login (999 = never logged in)." data-mm-num-filter>Inactive for</th><th data-tip="Largest payment linked to this package. $0 for free or granted packages." data-mm-num-filter>Value at risk</th><th data-tip="Days since last login plus days until the package expires, kept between 10 and 100." data-mm-num-filter>Risk score</th><th>Action</th></tr></thead>
                            <tbody>
                                @forelse($chAtRisk as $r)
                                <tr>
                                    <td class="acctn" data-mm-stu data-sid="{{ $r['sid'] ?? '' }}" data-email="{{ $r['email'] ?? '' }}"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div><div class="bh-acct-n">{{ $r['name'] }}</div><div class="bh-acct-c">({{ $r['sub'] }})</div></div></div></td><td><span class="{{ $mmPlanClass($r['plan'] ?? '') }}">{{ $r['plan'] ?? '—' }}</span></td>
                                    <td>{{ $r['inactiveDays'] }}d</td>
                                    <td>{{ $r['valueAtRisk'] }}</td>
                                    <td><span style="color:var(--crit);font-weight:600">{{ $r['risk'] }}</span></td>
                                    <td><button type="button" class="mm-act-btn" data-mm-compose="chAtRisk" data-row="{{ json_encode($r) }}">View</button></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" style="color:var(--g3);padding:20px">Nothing urgent right now — no packages expiring in the next 7 days.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="ch-rootcause" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Why students are actually leaving, ranked by how many it affects. Click a card to see who is affected.</div></div>
                        @if(count($chRootCauses))
                        <div class="mmx-ins-grid mmx-rc-grid">
                            @foreach($chRootCauses as $i => $rc)
                            @php $rcPct = !empty($rc['base']) ? min(100, round($rc['count'] / $rc['base'] * 100)) : null; @endphp
                            <button type="button" class="mmx-ins mmx-rc" @if(!empty($rc['key'])) onclick="mmKpiOpen('{{ $rc['key'] }}', '{{ $rc['name'] }}')" title="Click to see who is affected" @endif>
                                <span class="mmx-ins-ic">{!! $mmxIco($rc['icon'] ?? 'score') !!}</span>
                                <span class="mmx-rank">#{{ $i + 1 }}</span>
                                <b>{{ $rc['name'] }}</b>
                                <p>{{ $rc['meta'] }}</p>
                                <span class="mmx-ins-big">{{ number_format($rc['count'] ?? 0) }}</span>
                                @if($rcPct !== null)
                                <span class="mmx-share"><em><i style="width:{{ max(2, $rcPct) }}%"></i></em><small>{{ $rcPct }}% {{ $rc['baseLabel'] }}</small></span>
                                @endif
                                @if(!empty($rc['key']))<span class="mmx-go">See who is affected →</span>@endif
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="act"><div class="act-t" style="color:var(--g3)">No root-cause data available.</div></div>
                        @endif
                    </div>

                    <div class="mm-panel" data-panel="ch-offers" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Offers that have worked to win students back. Each card shows how many students it fits right now — open a card for how to use it and the list of students.</div></div>
                        <div class="mmx-offers" data-tab-data="offers">
                        @foreach($chOffers as $i => $o)
                        <div class="mmx-acc mmx-offer {{ $i === 0 ? 'open' : '' }}" data-offer="{{ $o['key'] }}">
                            <div class="mmx-acc-hd" onclick="mmxAccToggle(event, this)" role="button" tabindex="0" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">
                                <span class="mmx-ins-ic">{!! $mmxIco($o['icon']) !!}</span>
                                <div class="mmx-offer-t"><b>{{ $o['name'] }}</b><small>{{ $o['meta'] }}</small></div>
                                <span class="mmx-tag">{{ $o['tag'] }}</span>
                                <span class="mmx-offer-n" data-offer-n title="Students this offer fits right now">…</span>
                                {!! $mmxChev !!}
                            </div>
                            <div class="mmx-acc-bd"><div><div class="mmx-offer-bd">
                                <p class="mmx-offer-who"><b>Who it fits now:</b> <span data-offer-meta>Loading…</span></p>
                                <p><b>How to use it:</b> {{ $o['play'] }}</p>
                                <button type="button" class="mmx-offer-go" onclick="mmKpiOpen('{{ $o['key'] }}', '{{ $o['name'] }}')">View the students <span data-offer-n2></span></button>
                            </div></div></div>
                        </div>
                        @endforeach
                        </div>
                    </div>

                    <div class="mm-panel" data-panel="ch-watchlist" style="display:none">
                        <div class="stack-intro"><div class="si-h">WATCH — CHURN CREEPING UP</div><div class="si-p">Not urgent yet, but trending the wrong way.</div></div>
                        @include('client.partials.mm-list-filter', ['dataset' => 'chWatchlist', 'kind' => 'watchlist', 'subLabel' => 'Subscription', 'dateLabel' => 'Expires'])
                        <table class="dtbl">
                            <thead><tr><th data-tip="Students whose package expires in 8 to 30 days. Click a name for contact details.">Student</th><th data-tip="Plan history. Free trial: only the free mock test. Paid: has bought a paid package. Coaching: Enrolled or Coaching access, never a paid package. Coaching + Paid: both. No purchase: nothing bought." data-mm-plan-filter>Plan</th><th data-tip="Days since their last login (999 = never logged in)." data-mm-num-filter>Inactive for</th><th data-tip="Largest payment linked to this package. $0 for free or granted packages." data-mm-num-filter>Value at risk</th><th data-tip="Days since last login plus days until the package expires, kept between 10 and 100." data-mm-num-filter>Risk score</th><th>Action</th></tr></thead>
                            <tbody>
                                @forelse($chWatchlist as $r)
                                <tr><td class="acctn" data-mm-stu data-sid="{{ $r['sid'] ?? '' }}" data-email="{{ $r['email'] ?? '' }}"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div class="bh-acct-n">{{ $r['name'] }}</div></div></td><td><span class="{{ $mmPlanClass($r['plan'] ?? '') }}">{{ $r['plan'] ?? '—' }}</span></td><td>{{ $r['inactiveDays'] }}d</td><td>{{ $r['valueAtRisk'] }}</td><td>{{ $r['risk'] }}</td><td><button type="button" class="mm-act-btn" data-mm-compose="chWatchlist" data-row="{{ json_encode($r) }}">View</button></td></tr>
                                @empty
                                <tr><td colspan="6" style="color:var(--g3);padding:20px">Nothing trending toward churn right now.</td></tr>
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
        {{-- Board view (header Table | Board switch): every student matching the Marketing › Campaign
             filters and segment, in columns (mmBoardLoad). Replaces the agent panels while it is on. --}}
        <div class="mmx-board-panel" id="mmCampaignBoard" style="display:none">
            <div class="mmx-board-hd">
                <div class="mmx-board-title">Student board<span>All renewal-ready students · follows the Marketing › Campaign filters and segment</span></div>
                <span>Group by</span>
                <div class="mmx-viewtg" id="mmBoardGroup" role="group" aria-label="Group by">
                    <button type="button" class="on" data-g="stage" onclick="mmBoardGroup('stage')">Stage</button>
                    <button type="button" data-g="action" onclick="mmBoardGroup('action')">Next best action</button>
                </div>
                <span id="mmBoardInfo"></span>
            </div>
            <div class="mmx-board" id="mmBoardCols"></div>
        </div>

        {{-- Student profile popup — opened by clicking a student's name in any list (see mmProfileOpen) --}}
        <div class="mm-stu-overlay" id="mmStuOverlay" onclick="if(event.target===this) mmProfileClose()" role="dialog" aria-modal="true" aria-labelledby="mmStuName">
            <div class="mm-stu-card">
                <button type="button" class="mm-stu-x" onclick="mmProfileClose()" aria-label="Close">✕</button>
                <div class="mm-stu-top">
                    <div class="mm-stu-pic" id="mmStuPic"></div>
                    <div class="mm-stu-id">
                        <div class="mm-stu-name" id="mmStuName">Loading…</div>
                        <div class="mm-stu-course" id="mmStuCourse"></div>
                    </div>
                </div>
                <dl class="mm-stu-dl" id="mmStuBody"></dl>
                <button type="button" class="mm-stu-more" id="mmStuMore" onclick="mmxProfileToggle('pop')" hidden title="Open readiness, mock scores, package and timeline on the side">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                    <span>View full profile</span>
                    <small>Scores, package &amp; activity</small>
                    <svg class="mm-stu-more-arr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>
        </div>

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

.mm-btn-spinner{display:inline-block;width:13px;height:13px;margin-right:8px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:mmspin .7s linear infinite;vertical-align:-2px}
#mmTip{position:fixed;z-index:20000;display:none;max-width:300px;background:#16a34a;color:#fff;font-size:14px;line-height:1.45;font-weight:500;padding:10px 14px;border-radius:10px;box-shadow:0 8px 20px rgba(22,163,74,.3);pointer-events:none;text-transform:none;letter-spacing:normal}
#mmTip::after{content:"";position:absolute;left:50%;top:100%;transform:translateX(-50%);border:7px solid transparent;border-top-color:#16a34a}
/* Table headers with a hover explanation show the help cursor */
th[data-tip]{cursor:help}
.risk-modal-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:10000;align-items:center;justify-content:center;padding:24px}
.risk-modal-overlay.show{display:flex}
.risk-modal{background:#fff;border-radius:12px;max-width:820px;width:100%;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.risk-modal-hd{display:flex;align-items:flex-start;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:13px;color:#111827}
.risk-modal-hd button{flex-shrink:0;margin-left:14px}
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

/* Clickable student names (any element with data-mm-stu) + profile popup */
[data-mm-stu] .bh-acct-n,[data-mm-stu] .bh-av{cursor:pointer}
[data-mm-stu] .bh-acct-n:hover{color:#4f46e5;text-decoration:underline;text-underline-offset:2px}
.mm-stu-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.5);z-index:10050;align-items:center;justify-content:center;padding:24px}
.mm-stu-overlay.show{display:flex}
.mm-stu-card{position:relative;background:#fff;border-radius:14px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.25);padding:22px 22px 18px;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.mm-stu-x{position:absolute;top:12px;right:12px;border:none;background:none;font-size:15px;color:#9ca3af;cursor:pointer;padding:4px;line-height:1}
.mm-stu-x:hover{color:#111827}
.mm-stu-top{display:flex;align-items:center;gap:14px;margin-bottom:16px;padding-right:20px}
.mm-stu-pic{width:64px;height:64px;border-radius:50%;flex-shrink:0;overflow:hidden;background:#eef2ff;display:grid;place-items:center}
.mm-stu-pic img{width:100%;height:100%;object-fit:cover;display:block}
.mm-stu-ini{font-size:24px;font-weight:700;color:#4f46e5}
.mm-stu-name{font-size:16px;font-weight:700;color:#111827;line-height:1.3}
.mm-stu-course{display:inline-block;margin-top:5px;font-size:11px;font-weight:600;color:#4338ca;background:#eef2ff;border-radius:6px;padding:2px 8px}
.mm-stu-course:empty{display:none}
.mm-stu-dl{display:grid;grid-template-columns:auto 1fr;gap:0;margin:0;border:1px solid #e6e9f0;border-radius:10px;overflow:hidden}
.mm-stu-dl dt,.mm-stu-dl dd{margin:0;padding:9px 12px;border-top:1px solid #e6e9f0;font-size:12.5px}
.mm-stu-dl dt:first-of-type,.mm-stu-dl dt:first-of-type + dd{border-top:none}
.mm-stu-dl dt{color:#64748b;font-weight:600;background:#f7f8fb;white-space:nowrap}
.mm-stu-dl dd{color:#111827;display:flex;align-items:center;justify-content:space-between;gap:8px;min-width:0}
.mm-stu-dl dd span{overflow-wrap:anywhere}
.mm-stu-none{color:#9ca3af;font-style:italic}
.mm-stu-copy{flex-shrink:0;font-size:10.5px;font-weight:600;color:#4338ca;background:#fff;border:1px solid #c7d2fe;border-radius:6px;padding:2px 8px;cursor:pointer}
.mm-stu-copy:hover{background:#eef2ff}
.mm-stu-err{grid-column:1 / -1;margin:0;padding:12px;font-size:12.5px;color:#64748b}

/* Plan column pill (mmPlanClass / mmPlanCell) */
.mm-plan{display:inline-block;font-size:11px;font-weight:600;line-height:1.3;padding:3px 8px;border-radius:6px;white-space:nowrap;background:#f3f4f6;color:#6b7280}
.mm-plan.free-trial{background:#eef2f7;color:#475569}
.mm-plan.paid{background:#e8f7ee;color:#15803d}
.mm-plan.coaching{background:#f1ecfe;color:#6d28d9}
.mm-plan.coaching-paid{background:#e3f6f3;color:#0f766e}
.mm-plan.no-purchase,.mm-plan.other{background:#f3f4f6;color:#9ca3af}

/* Plan filter — arrow in the Plan header + dropdown menu (mmPlanFilterInit) */
th[data-mm-plan-filter]{white-space:nowrap}
.mm-pf-btn{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;margin-left:5px;padding:0;border:1px solid #e2e5ee;border-radius:5px;background:#fff;color:#6b7280;cursor:pointer;vertical-align:-4px;transition:background .15s,color .15s,border-color .15s}
.mm-pf-btn svg{width:11px;height:11px;transition:transform .2s cubic-bezier(.4,0,.2,1)}
.mm-pf-btn:hover{background:#f5f3ff;border-color:#c4b5fd;color:#6d28d9}
.mm-pf-btn:focus-visible{outline:2px solid #c4b5fd;outline-offset:1px}
.mm-pf-btn.open svg{transform:rotate(180deg)}
.mm-pf-btn.on{background:#7c3aed;border-color:#7c3aed;color:#fff}
.mm-pf-menu{position:fixed;z-index:20050;min-width:250px;max-width:300px;background:#fff;border:1px solid #e6e9f0;border-radius:12px;box-shadow:0 14px 34px rgba(17,24,39,.16),0 2px 6px rgba(17,24,39,.06);padding:6px;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;opacity:0;transform:translateY(-6px);transition:opacity .15s ease,transform .15s ease;text-transform:none;letter-spacing:normal}
.mm-pf-menu.show{opacity:1;transform:translateY(0)}
.mm-pf-menu:not(.show){pointer-events:none}
.mm-pf-opt{display:flex;align-items:center;gap:9px;width:100%;padding:8px 10px;border:none;border-radius:8px;background:none;text-align:left;cursor:pointer;font-family:inherit;color:#111827;transition:background .12s}
.mm-pf-opt:hover:not(:disabled),.mm-pf-opt:focus-visible{background:#f5f3ff;outline:none}
.mm-pf-opt.sel{background:#f5f3ff}
.mm-pf-opt:disabled{opacity:.45;cursor:default}
.mm-pf-txt{flex:1;min-width:0;display:flex;flex-direction:column;gap:1px}
.mm-pf-txt b{font-size:12.5px;font-weight:600}
.mm-pf-txt small{font-size:11px;color:#6b7280}
.mm-pf-n{font-size:11px;font-weight:600;color:#6b7280;background:#f3f4f6;border-radius:999px;padding:1px 7px;font-variant-numeric:tabular-nums}
.mm-pf-check{width:14px;height:14px;color:#7c3aed;flex-shrink:0;display:inline-flex}
.mm-pf-check svg{width:14px;height:14px}
.mm-pf-dot{width:9px;height:9px;padding:0;border-radius:50%;flex-shrink:0}
.mm-pf-dot.mm-plan.free-trial{background:#94a3b8}
.mm-pf-dot.mm-plan.paid{background:#16a34a}
.mm-pf-dot.mm-plan.coaching{background:#7c3aed}
.mm-pf-dot.mm-plan.coaching-paid{background:#0d9488}
.mm-pf-dot.mm-plan.no-purchase,.mm-pf-dot.mm-plan.other{background:#d1d5db}
.mm-pf-sep{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#9ca3af;padding:8px 10px 4px;margin-top:4px;border-top:1px solid #f0f1f5}
tr.mm-pf-empty td{color:#9ca3af;padding:18px 14px;font-size:12.5px;text-align:center}
/* Number filter menu (mmNumMenuOpen): compact — sort toggles + Min/Max steppers */
th[data-mm-num-filter]{white-space:nowrap}
.mm-pf-menu.mm-nf-menu{min-width:0;width:214px;padding:8px;display:flex;flex-direction:column;gap:6px}
.mm-nf-sort{display:flex;gap:4px}
.mm-nf-dir{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:4px;height:28px;padding:0 6px;border:1px solid #e2e5ee;border-radius:7px;background:#fff;color:#374151;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .12s,border-color .12s,color .12s}
.mm-nf-dir svg{width:12px;height:12px}
.mm-nf-dir:hover{background:#f5f3ff;border-color:#c4b5fd;color:#6d28d9}
.mm-nf-dir.on{background:#7c3aed;border-color:#7c3aed;color:#fff}
.mm-nf-dir:focus-visible,.mm-nf-step:focus-visible{outline:2px solid #c4b5fd;outline-offset:1px}
.mm-nf-row{display:flex;align-items:center;gap:4px}
.mm-nf-row label{width:28px;font-size:11.5px;font-weight:600;color:#6b7280}
.mm-nf-step{width:24px;height:26px;flex-shrink:0;padding:0;border:1px solid #e2e5ee;border-radius:6px;background:#fff;color:#374151;font-size:14px;font-weight:600;line-height:1;cursor:pointer;font-family:inherit;transition:background .12s,border-color .12s,color .12s}
.mm-nf-step:hover{background:#f5f3ff;border-color:#c4b5fd;color:#6d28d9}
.mm-nf-step:active{background:#ede9fe}
.mm-nf-row input{flex:1;min-width:0;height:26px;padding:0 6px;border:1px solid #e2e5ee;border-radius:6px;font-size:12px;font-family:inherit;color:#111827;text-align:center;font-variant-numeric:tabular-nums;-moz-appearance:textfield}
.mm-nf-row input::-webkit-outer-spin-button,.mm-nf-row input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.mm-nf-row input:focus{outline:none;border-color:#c4b5fd;box-shadow:0 0 0 2px #f5f3ff}
.mm-nf-row input::placeholder{color:#c0c4d0}
.mm-nf-clear{align-self:flex-end;border:none;background:none;padding:0 2px;color:#6b7280;font-size:11px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:underline;text-underline-offset:2px}
.mm-nf-clear:hover{color:#6d28d9}

/* Action column "View" button + message composer (mmComposeOpen) */
.mm-act-btn{padding:5px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:inherit}
.mm-act-btn:hover{background:#6d28d9}
.mm-act-btn:focus-visible{outline:2px solid #c4b5fd;outline-offset:2px}
.mm-cmp-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:10040;align-items:center;justify-content:center;padding:24px}
.mm-cmp-overlay.show{display:flex}
.mm-cmp{background:#fff;border-radius:12px;width:100%;max-width:880px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.mm-cmp-hd{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:14px 18px;border-bottom:1px solid #e5e7eb}
.mm-cmp-title{font-size:14px;font-weight:700;color:#111827}
.mm-cmp-contact{font-size:12px;color:#6b7280;margin-top:2px;overflow-wrap:anywhere}
.mm-cmp-x{border:none;background:none;font-size:15px;color:#9ca3af;cursor:pointer;padding:4px;line-height:1}
.mm-cmp-x:hover{color:#111827}
.mm-cmp-body{display:grid;grid-template-columns:1fr 1.4fr;gap:20px;padding:16px 18px;overflow:auto}
.mm-cmp-body:has(#mmCmpEdit[hidden]){grid-template-columns:1fr}
@media(max-width:720px){.mm-cmp-body{grid-template-columns:1fr}}
.mm-cmp-lbl{font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;margin-bottom:8px}
.mm-cmp-tpls{display:grid;gap:8px}
.mm-cmp-tpl{display:block;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;cursor:pointer;background:#fff;transition:border-color .15s}
.mm-cmp-tpl:hover{border-color:#c4b5fd}
.mm-cmp-tpl:has(input:checked){border-color:#7c3aed;background:#f5f3ff}
.mm-cmp-tpl input{margin-right:6px;accent-color:#7c3aed}
.mm-cmp-tpl b{font-size:12.5px;color:#111827}
.mm-cmp-tpl span{display:block;font-size:11px;color:#6b7280;margin:3px 0 0 20px}
.mm-cmp-flbl{display:block;font-size:11px;font-weight:700;color:#6b7280}
.mm-cmp-flbl span{font-weight:400}
.mm-cmp-in{width:100%;margin:4px 0 10px;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-family:inherit;color:#111827}
.mm-cmp-in:focus{outline:none;border-color:#c4b5fd;box-shadow:0 0 0 3px #f5f3ff}
textarea.mm-cmp-in{resize:vertical;line-height:1.55}
.mm-cmp-send{display:flex;gap:10px;justify-content:flex-end;margin-top:4px}
.mm-cmp-send button{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;border:none;background:#e5e7eb;color:#9ca3af;cursor:not-allowed;font-family:inherit}
#mmCmpEmail.on{background:#2563eb;color:#fff;cursor:pointer}
#mmCmpWa.on{background:#16a34a;color:#fff;cursor:pointer}
.mm-cmp-hint{font-size:11.5px;color:#6b7280;margin:0;padding:0 18px 14px;text-align:right}
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

/* ── Dashboard additions (Nov 2026): overview cards per agent, Campaign quick segments,
      richer Campaign columns and the Board view. All collapsible; the table stays the focus. ── */
#bhRoot .mmx-collapse{display:inline-flex;align-items:center;gap:5px;border:1px solid var(--ln);background:#fff;border-radius:8px;padding:3px 9px 3px 7px;font-size:11px;font-weight:600;color:var(--g2);cursor:pointer;font-family:var(--f1)}
#bhRoot .mmx-collapse:hover{color:var(--ink);border-color:var(--ln2)}
#bhRoot .mmx-collapse svg{width:12px;height:12px;transition:transform .2s}
#bhRoot .collapsed .mmx-collapse svg{transform:rotate(-90deg)}

#bhRoot .mmx-ov{flex-shrink:0;margin-bottom:16px}
#bhRoot .mmx-ov-hd{display:flex;align-items:center;gap:10px;margin:0 2px 8px}
#bhRoot .mmx-ov-t{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#64748b}
#bhRoot .mmx-ov-t [data-ov-t]{display:none}
#bhRoot[data-agent="mk"] [data-ov-t="mk"],#bhRoot[data-agent="sl"] [data-ov-t="sl"],#bhRoot[data-agent="ch"] [data-ov-t="ch"]{display:inline}
#bhRoot .mmx-live{display:inline-flex;align-items:center;gap:6px;font-size:11px;color:var(--g3)}
#bhRoot .mmx-live i{width:7px;height:7px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 3px #dcfce7}
#bhRoot .mmx-ov-hd .mmx-collapse{margin-left:auto}
#bhRoot .mmx-ov.collapsed .mmx-ov-hd{margin-bottom:0}
#bhRoot .mmx-ov.collapsed .mmx-kpis{display:none!important}
#bhRoot .mmx-kpis{display:none;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
#bhRoot[data-agent="mk"] .mmx-kpis[data-ov="mk"],#bhRoot[data-agent="sl"] .mmx-kpis[data-ov="sl"],#bhRoot[data-agent="ch"] .mmx-kpis[data-ov="ch"]{display:grid}
#bhRoot .mmx-kpi{background:#fff;border:1px solid var(--ln);border-radius:var(--card-r,14px);box-shadow:var(--card-sh);padding:12px 14px 10px;min-width:0}
#bhRoot .mmx-kpi-l{font-size:11.5px;font-weight:600;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-kpi-r{display:flex;align-items:flex-end;justify-content:space-between;gap:8px;margin-top:4px}
#bhRoot .mmx-kpi-v{font-size:22px;font-weight:700;letter-spacing:-.02em;color:#0f172a;line-height:1.15;font-variant-numeric:tabular-nums}
#bhRoot .mmx-delta{display:inline-block;font-size:10.5px;font-weight:700;padding:1px 7px;border-radius:20px;margin-top:4px;background:#f1f5f9;color:#64748b}
#bhRoot .mmx-delta.good{background:#dcfce7;color:#15803d}
#bhRoot .mmx-delta.bad{background:#fee2e2;color:#b91c1c}
#bhRoot .mmx-kpi-h{font-size:11px;color:var(--g3);margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-kpi svg{flex:0 0 auto}
#bhRoot .mmx-skel{display:block;height:22px;width:60%;border-radius:6px;background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:mmxShimmer 1.2s infinite}
@keyframes mmxShimmer{to{background-position:-200% 0}}
@media(max-width:1280px){#bhRoot .mmx-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}#bhRoot .mmx-kpi:nth-child(n+4){display:none}}
@media(max-width:700px){#bhRoot .mmx-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}

#bhRoot .mmx-seg-wrap{padding:0 20px 14px}
#bhRoot .mmx-seg-hd{display:flex;align-items:center;gap:10px;margin-bottom:10px}
#bhRoot .mmx-seg-hd b{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#64748b}
#bhRoot .mmx-seg-hd span{font-size:11px;color:var(--g3)}
#bhRoot .mmx-seg-hd .mmx-collapse{margin-left:auto}
#bhRoot .mmx-seg-wrap.collapsed .mmx-segs{display:none}
#bhRoot .mmx-seg-wrap.collapsed .mmx-seg-hd{margin-bottom:0}
#bhRoot .mmx-segs{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(156px,1fr);gap:10px;overflow-x:auto;padding-bottom:2px}
#bhRoot .mmx-seg{border:1px solid var(--ln);background:var(--p1);border-radius:12px;padding:9px 12px;text-align:left;cursor:pointer;font-family:var(--f1);min-width:0;transition:border-color .15s,background .15s}
#bhRoot .mmx-seg:hover{border-color:#c4b5fd}
#bhRoot .mmx-seg.on{background:#f5f3ff;border-color:#7c3aed;box-shadow:0 0 0 1px #7c3aed inset}
#bhRoot .mmx-seg-t{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-seg.on .mmx-seg-t{color:#6d28d9}
#bhRoot .mmx-seg-t i{width:8px;height:8px;border-radius:3px;flex:0 0 auto}
#bhRoot .mmx-seg-n{font-size:19px;font-weight:700;color:#0f172a;margin-top:2px;font-variant-numeric:tabular-nums}
#bhRoot .mmx-seg-s{font-size:10.5px;color:var(--g3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

#bhRoot .mmx-muted{color:var(--g3)}
#bhRoot .mmx-sub{font-size:10.5px;color:var(--g3);margin-top:2px}
#bhRoot .mmx-exp{color:#475569;white-space:nowrap}
#bhRoot .mmx-ring{display:inline-flex;align-items:center;gap:7px;font-variant-numeric:tabular-nums}
#bhRoot .mmx-ring b{font-weight:600}
#bhRoot .mmx-last{white-space:nowrap}
#bhRoot .mmx-bar{display:block;height:4px;width:58px;border-radius:9px;background:#eceef3;margin-top:5px;overflow:hidden}
#bhRoot .mmx-bar b{display:block;height:100%;border-radius:9px}
#bhRoot .bh-pill.mmx-nba{white-space:nowrap;max-width:none}

#bhRoot .mmx-viewtg{display:inline-flex;margin-left:auto;border:1px solid var(--ln);border-radius:8px;overflow:hidden;background:#fff}
#bhRoot .mmx-viewtg button{border:none;background:transparent;padding:6px 11px;font-size:12px;font-weight:600;color:var(--g2);cursor:pointer;display:inline-flex;align-items:center;gap:5px;font-family:var(--f1)}
#bhRoot .mmx-viewtg button + button{border-left:1px solid var(--ln)}
#bhRoot .mmx-viewtg button.on{background:#0f172a;color:#fff}
#bhRoot .mmx-viewtg svg{width:13px;height:13px}
#bhRoot .mmx-board-wrap{padding:12px 20px 20px}
#bhRoot .mmx-board-hd{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;font-size:12px;color:var(--g3)}
#bhRoot .mmx-board-hd .mmx-viewtg{margin-left:0}
#bhRoot .mmx-board{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(230px,1fr);gap:12px;overflow-x:auto;padding-bottom:6px}
#bhRoot .mmx-col{background:var(--p1);border:1px solid var(--ln);border-radius:12px;padding:10px;min-height:160px;display:flex;flex-direction:column}
#bhRoot .mmx-col h4{margin:2px 4px 10px;font-size:12.5px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:7px}
#bhRoot .mmx-col h4 i{width:8px;height:8px;border-radius:3px}
#bhRoot .mmx-col h4 span{margin-left:auto;color:var(--g3);font-weight:600}
#bhRoot .mmx-col-body{overflow-y:auto;max-height:560px;padding-right:2px}
#bhRoot .mmx-tile{background:#fff;border:1px solid var(--ln);border-radius:10px;padding:9px 10px;margin-bottom:8px;cursor:pointer;transition:border-color .15s,box-shadow .15s}
#bhRoot .mmx-tile:hover{border-color:#c4b5fd;box-shadow:0 2px 8px -4px rgba(124,58,237,.35)}
#bhRoot .mmx-tile-a{display:flex;align-items:center;gap:9px;min-width:0}
#bhRoot .mmx-tile-a .bh-av{width:28px;height:28px;font-size:11px;flex:0 0 auto}
#bhRoot .mmx-tile-n{font-size:12.5px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-tile-c{font-size:11px;color:var(--g3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-tile-b{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-top:8px;font-size:12px}
#bhRoot .mmx-tile-b .mmx-ring b{font-size:12px}
#bhRoot .mmx-more{font-size:11.5px;color:var(--g3);text-align:center;padding:6px 4px}

/* ── Part 2 (Nov 2026): expand the table, Board in the page header, Profile side panel ── */
/* Expand button at the end of each agent's step tabs */
#bhRoot .dash-vtabs .mmx-expand{flex:0 0 auto;align-self:center;width:32px;height:32px;margin:0 6px 4px 8px;display:grid;place-items:center;border:1px solid var(--ln);border-radius:8px;background:#fff;color:#94a3b8;cursor:pointer;padding:0}
#bhRoot .dash-vtabs .mmx-expand:hover{color:#475569;border-color:var(--ln2);background:var(--p1)}
#bhRoot .mmx-expand svg{width:16px;height:16px}
#bhRoot .mmx-expand .mmx-ico-min,#bhRoot.mm-focus .mmx-expand .mmx-ico-max{display:none}
#bhRoot.mm-focus .mmx-expand .mmx-ico-min{display:block}
#bhRoot.mm-focus .mmx-expand{color:#7c3aed;border-color:#c4b5fd;background:#f5f3ff}
/* Expanded: the middle table fills the screen; steps, helper, overview, intro and segment cards are hidden */
#bhRoot.mm-focus{position:fixed;inset:0;z-index:9000;background:#f5f6f8;padding:14px}
#bhRoot.mm-focus .bar,#bhRoot.mm-focus .mmx-ov,#bhRoot.mm-focus .dash-left,#bhRoot.mm-focus .dash-mira,
#bhRoot.mm-focus .stack-intro,#bhRoot.mm-focus .mmx-seg-wrap{display:none!important}
#bhRoot.mm-focus .dash.on{grid-template-columns:1fr!important}

/* Table | Board switch in the page header (outside #bhRoot) */
#mmViewTg{display:inline-flex;height:32px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff}
#mmViewTg button{border:none;background:transparent;padding:0 11px;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;display:inline-flex;align-items:center;gap:5px}
#mmViewTg button + button{border-left:1px solid #e5e7eb}
#mmViewTg button.on{background:#0f172a;color:#fff}
#mmViewTg svg{width:13px;height:13px}
/* Board panel replaces the agent panels while it is on */
#bhRoot.mm-board-on .dash{display:none!important}
#bhRoot .mmx-board-panel{flex:1;min-height:0;overflow:auto;background:#fff;border:1px solid var(--ln);border-radius:var(--card-r,14px);box-shadow:var(--card-sh);padding:16px 20px 20px}
#bhRoot .mmx-board-title{font-size:15px;font-weight:700;color:#0f172a;margin-right:auto;display:flex;flex-direction:column}
#bhRoot .mmx-board-title span{font-size:11.5px;font-weight:500;color:var(--g3);margin-top:2px}

/* Profile side panel next to the message windows (Campaign "View" and Sales / Retention "View") */
.mmx-prof-btn{display:inline-flex;align-items:center;gap:6px;border:1px solid #ddd6fe!important;background:#f5f3ff!important;color:#6d28d9!important;font-size:12px!important;font-weight:600;border-radius:8px;padding:5px 11px!important;cursor:pointer;line-height:1.2!important;vertical-align:middle;font-family:inherit}
.mmx-prof-btn:hover{background:#ede9fe!important}
.mmx-prof-btn.on{background:#6d28d9!important;color:#fff!important;border-color:#6d28d9!important}
.mmx-prof-btn svg{width:13px;height:13px}
body.mmx-prof-open .risk-modal-overlay,body.mmx-prof-open .mm-cmp-overlay{padding-right:476px}
/* Part 4 (Nov 2026): "View full profile" button in the student details popup */
body.mmx-prof-open .mm-stu-overlay{padding-right:476px}
.mm-stu-more{display:flex;align-items:center;gap:10px;width:100%;margin-top:14px;padding:10px 12px;border:1px solid #c7d2fe;border-radius:10px;background:linear-gradient(135deg,#eef2ff,#f5f3ff);color:#4338ca;font:600 13px/1.2 inherit;font-family:inherit;cursor:pointer;text-align:left;transition:background .15s,border-color .15s}
.mm-stu-more[hidden]{display:none}
.mm-stu-more:hover{background:linear-gradient(135deg,#e0e7ff,#ede9fe);border-color:#a5b4fc}
.mm-stu-more svg{width:16px;height:16px;flex-shrink:0}
.mm-stu-more small{flex:1;font-size:11px;font-weight:500;color:#6366f1;text-align:right}
.mm-stu-more .mm-stu-more-arr{width:14px;height:14px;transition:transform .15s}
.mm-stu-more.on{background:#4f46e5;border-color:#4f46e5;color:#fff}
.mm-stu-more.on small{color:#e0e7ff}
.mm-stu-more.on .mm-stu-more-arr{transform:rotate(180deg)}
.mmx-prof{position:fixed;top:0;right:0;height:100vh;width:min(452px,100vw);background:#fff;border-left:1px solid #e5e7eb;z-index:10060;transform:translateX(100%);transition:transform .22s ease;display:flex;flex-direction:column;box-shadow:-18px 0 40px -22px rgba(15,23,42,.35);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#0f172a}
.mmx-prof.show{transform:none}
.mmx-prof-hd{display:flex;gap:14px;align-items:center;padding:18px 20px;border-bottom:1px solid #f1f5f9}
.mmx-prof-pic{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;color:#fff;font-weight:700;font-size:17px;flex:0 0 auto;overflow:hidden;background-size:cover;background-position:center}
.mmx-prof-hd h3{margin:0;font-size:17px;font-weight:700;line-height:1.25}
.mmx-prof-hd .c{font-size:12px;color:#64748b;margin-top:2px}
.mmx-prof-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
.mmx-prof-x{margin-left:auto;align-self:flex-start;border:1px solid #e5e7eb;background:#fff;border-radius:9px;width:32px;height:32px;cursor:pointer;color:#64748b;font-size:15px;flex:0 0 auto}
.mmx-prof-x:hover{color:#0f172a}
.mmx-prof-b{padding:18px 20px 24px;overflow-y:auto;display:flex;flex-direction:column;gap:20px}
.mmx-prof-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.mmx-prof-stats div{border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;background:#fafafa}
.mmx-prof-stats small{display:block;font-size:11px;color:#94a3b8}
.mmx-prof-stats b{display:block;font-size:18px;margin-top:2px}
.mmx-prof-sec{font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#94a3b8;margin-bottom:10px}
.mmx-prof-sec span{text-transform:none;letter-spacing:0;font-weight:500;margin-left:6px}
.mmx-skill{display:grid;grid-template-columns:76px 1fr 30px;gap:10px;align-items:center;font-size:12.5px;margin-bottom:8px}
.mmx-skill i{display:block;height:8px;border-radius:9px;background:#f1f5f9;overflow:hidden}
.mmx-skill i b{display:block;height:100%;border-radius:9px;background:#7c3aed}
.mmx-skill > b{text-align:right;font-variant-numeric:tabular-nums}
.mmx-prof .bh-pill{display:inline-block;font-size:11px;font-weight:600;line-height:1.3;padding:3px 8px;border-radius:6px}
.mmx-prof .bh-pill.good{background:#e8f7ee;color:#15803d}
.mmx-prof .bh-pill.warn{background:#fff1e6;color:#c2410c}
.mmx-prof .bh-pill.bad{background:#fdecec;color:#b91c1c}
.mmx-prof .bh-pill.info{background:#e8f0fe;color:#1d4ed8}
.mmx-prof .bh-pill.violet{background:#f1ecfe;color:#6d28d9}
.mmx-prof-draft{border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;background:#fafafa;font-size:13px;line-height:1.5;white-space:pre-line;margin-top:10px}
.mmx-prof-use{margin-top:10px;border:none;background:#7c3aed;color:#fff;border-radius:8px;padding:7px 14px;font-size:12.5px;font-weight:600;cursor:pointer}
.mmx-prof-use:hover{background:#6d28d9}
.mmx-prof-kv{display:grid;grid-template-columns:auto 1fr;gap:6px 14px;font-size:12.5px}
.mmx-prof-kv dt{color:#94a3b8}
.mmx-prof-kv dd{margin:0;font-weight:500;word-break:break-word}
.mmx-tl{border-left:2px solid #e5e7eb;margin-left:6px;padding-left:16px;display:flex;flex-direction:column;gap:13px}
.mmx-tl div{position:relative;font-size:13px}
.mmx-tl div::before{content:"";position:absolute;left:-22px;top:4px;width:10px;height:10px;border-radius:50%;background:#fff;border:2px solid #7c3aed}
.mmx-tl div.bad::before{border-color:#dc2626}
.mmx-tl small{display:block;color:#94a3b8;font-size:11.5px}
.mmx-prof-empty{color:#94a3b8;font-size:12.5px}
@media(max-width:1100px){body.mmx-prof-open .risk-modal-overlay,body.mmx-prof-open .mm-cmp-overlay{padding-right:24px}}

/* ── Part 3 (Nov 2026): Campaign pager (page numbers, Go to page, rows per page) and filled lists ── */
#bhRoot #mmCampaignPageInfo b{color:#334155;font-weight:600}
#bhRoot .mmx-pg{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
#bhRoot .mmx-pg-rows{display:inline-flex;align-items:center;gap:6px;margin-right:6px;font-weight:600;color:var(--g2)}
#bhRoot .mmx-pg-rows select{padding:4px 6px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff;color:var(--ink);cursor:pointer}
#bhRoot .mmx-pg button:disabled{opacity:.45;cursor:default}
#bhRoot .mmx-pg-nums{display:inline-flex;align-items:center;gap:4px}
#bhRoot .mmx-pg-nums button{min-width:30px;height:28px;padding:0 8px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;color:#334155;cursor:pointer;font-variant-numeric:tabular-nums}
#bhRoot .mmx-pg-nums button:hover{border-color:#c4b5fd;color:#6d28d9}
#bhRoot .mmx-pg-nums button.on{background:#7c3aed;border-color:#7c3aed;color:#fff;cursor:default}
#bhRoot .mmx-pg-gap{padding:0 2px;color:var(--g3)}
#bhRoot .mmx-pg-go{display:inline-flex;align-items:center;gap:6px;margin-left:6px;font-weight:600;color:var(--g2)}
#bhRoot .mmx-pg-go input{width:62px;padding:4px 6px;border:1px solid var(--ln);border-radius:6px;font-size:12px}
#bhRoot .mmx-pg-go button{padding:5px 10px;border-radius:6px;border:1px solid #7c3aed;background:#fff;color:#7c3aed;font-size:12px;font-weight:600;cursor:pointer}
#bhRoot .mmx-fill-ft{display:flex;align-items:center;justify-content:center;gap:10px;margin:-8px 20px 20px;padding:10px;font-size:12px;color:var(--g3)}
#bhRoot .mmx-fill-ft b{color:#334155}
#bhRoot .mmx-fill-ft button{padding:5px 12px;border-radius:6px;border:1px solid #7c3aed;background:#fff;color:#7c3aed;font-size:12px;font-weight:600;cursor:pointer}

/* ── Part 5 (Nov 2026): animations and designs from the concept page, plus filter chips.
   Looks only — every button, filter and list works as before. The sidebar rules apply on this page only. ── */
:root{--mmx-ease:cubic-bezier(.22,.8,.26,1);--mmx-spring:cubic-bezier(.34,1.56,.64,1)}
@keyframes mmxRise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes mmxRowIn{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:none}}
@keyframes mmxSlideR{from{opacity:0;transform:translateX(-14px)}to{opacity:1;transform:none}}
@keyframes mmxSlideL{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:none}}
@keyframes mmxViewIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
@keyframes mmxCardIn{from{opacity:0;transform:translateY(12px) scale(.95)}to{opacity:1;transform:none}}
@keyframes mmxBubIn{from{opacity:0;transform:translateY(8px) scale(.94)}to{opacity:1;transform:none}}
@keyframes mmxPop{from{opacity:0;transform:scale(.94) translateY(8px)}to{opacity:1;transform:none}}
@keyframes mmxChipIn{from{opacity:0;transform:scale(.6)}to{opacity:1;transform:none}}
@keyframes mmxFade{from{opacity:0}to{opacity:1}}
@keyframes mmxArea{from{opacity:0}to{opacity:.12}}
@keyframes mmxGrowX{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes mmxGrowY{from{transform:scaleY(0)}to{transform:scaleY(1)}}
@keyframes mmxDraw{from{stroke-dashoffset:1}to{stroke-dashoffset:0}}
@keyframes mmxRingIn{from{stroke-dasharray:0 70}}
@keyframes mmxZoomIn{from{opacity:.5;transform:scale(.975)}to{opacity:1;transform:none}}
@keyframes mmxPulse{0%{box-shadow:0 0 0 0 rgba(22,163,74,.5)}70%{box-shadow:0 0 0 6px rgba(22,163,74,0)}100%{box-shadow:0 0 0 0 rgba(22,163,74,0)}}
@keyframes mmxBlink{0%,80%,100%{opacity:.25;transform:translateY(0)}40%{opacity:1;transform:translateY(-3px)}}
@keyframes mmxRipple{to{transform:scale(4);opacity:0}}
@keyframes mmxBump{50%{transform:scale(1.25)}}
@keyframes mmxTwinkle{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(.82);opacity:.75}}

/* Header buttons: lift on hover; Table | Board pill slides */
.bh-page > header button,.bh-page > header a{transition:transform .18s var(--mmx-ease),box-shadow .2s,background-color .2s,color .2s}
.bh-page > header button:hover:not(:disabled),.bh-page > header a:hover{transform:translateY(-1px);box-shadow:0 4px 12px -6px rgba(15,23,42,.3)}
.bh-page > header button:active:not(:disabled){transform:scale(.97)}
#mmViewTg{position:relative}
#mmViewTg button{position:relative;z-index:1;transition:color .25s}
#mmViewTg button + button{border-left-color:transparent}
#mmViewTg.mmx-pilled button.on{background:transparent}
#mmViewTg button:hover{transform:none!important;box-shadow:none!important}
#mmViewTg .mmx-vt-pill{position:absolute;top:0;bottom:0;left:0;border-radius:7px;background:#0f172a;transition:transform .38s var(--mmx-spring),width .38s var(--mmx-spring);box-shadow:0 4px 10px -4px rgba(0,0,0,.45)}

/* Agent tabs: coloured background slides to the chosen agent */
#bhRoot .atabs{position:relative}
#bhRoot .atabs.mmx-slid .atab,#bhRoot .atabs.mmx-slid .atab.on,#bhRoot .atabs.mmx-slid .atab.on:hover{background:transparent}
#bhRoot .atabs.mmx-slid .atab:hover:not(.on){background:var(--p1)}
#bhRoot .atab{z-index:1;transition:color .3s}
#bhRoot .atab .amono svg{transition:transform .4s var(--mmx-spring)}
#bhRoot .atab.on .amono svg{transform:scale(1.15) rotate(-8deg)}
#bhRoot .mmx-at-slider{position:absolute;top:0;bottom:0;left:0;z-index:0;border-radius:var(--card-r,14px);background-color:var(--ac);background-image:linear-gradient(135deg,rgba(255,255,255,0) 30%,rgba(255,255,255,.28));box-shadow:0 8px 20px -10px var(--ac);transition:transform .45s var(--mmx-spring),width .45s var(--mmx-spring),background-color .4s,box-shadow .4s;pointer-events:none}
#bhRoot .mmx-ab{font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:20px;background:#f1f5f9;color:#475569;font-variant-numeric:tabular-nums;transition:background .3s,color .3s}
#bhRoot .atab.on .mmx-ab{background:rgba(255,255,255,.25);color:#fff}
#bhRoot .mmx-ab:empty{display:none}

/* Collapse buttons */
#bhRoot .mmx-collapse{transition:background .2s,color .2s,border-color .2s}
#bhRoot .mmx-collapse svg{transition:transform .35s var(--mmx-ease)}

/* Overview cards: rise one after another, coloured top bar grows, numbers count up, spark lines draw */
#bhRoot .mmx-kpi{position:relative;overflow:hidden;transition:transform .25s var(--mmx-ease),box-shadow .25s}
#bhRoot .mmx-kpi:hover{transform:translateY(-3px);box-shadow:0 14px 30px -16px rgba(15,23,42,.3)}
#bhRoot .mmx-kpi.mmx-in{animation:mmxRise .55s var(--mmx-ease) both;animation-delay:calc(var(--i,0) * 70ms)}
#bhRoot .mmx-kpi.mmx-bartop::before{content:"";position:absolute;left:0;right:0;top:0;height:3px;background:var(--c,var(--ac));transform-origin:left}
#bhRoot .mmx-kpi.mmx-bartop.mmx-in::before{animation:mmxGrowX .8s var(--mmx-ease) both;animation-delay:calc(var(--i,0) * 70ms + 120ms)}
#bhRoot .mmx-kpi.mmx-in path.mmx-line{stroke-dasharray:1;stroke-dashoffset:1;animation:mmxDraw 1.3s var(--mmx-ease) forwards;animation-delay:calc(var(--i,0) * 70ms + 250ms)}
#bhRoot .mmx-kpi.mmx-in path.mmx-area{opacity:0;animation:mmxArea .8s ease forwards;animation-delay:calc(var(--i,0) * 70ms + 900ms)}
#bhRoot .mmx-live i{animation:mmxPulse 2s infinite}

/* Steps on the left: slide in, active number springs, earlier steps get a tick, progress bar */
#bhRoot .dash.on .flowst{animation:mmxSlideR .45s var(--mmx-ease) both}
#bhRoot .dash.on .flowst:nth-child(2){animation-delay:.05s}#bhRoot .dash.on .flowst:nth-child(3){animation-delay:.1s}#bhRoot .dash.on .flowst:nth-child(4){animation-delay:.15s}#bhRoot .dash.on .flowst:nth-child(5){animation-delay:.2s}#bhRoot .dash.on .flowst:nth-child(6){animation-delay:.25s}
#bhRoot .flowst{transition:background .25s,transform .25s var(--mmx-ease)}
#bhRoot .flowst:hover{transform:translateX(3px)}
#bhRoot .flowst-dot{position:relative;transition:background .3s,border-color .3s,color .3s,transform .35s var(--mmx-spring),box-shadow .3s}
#bhRoot .flowst.cur .flowst-dot{transform:scale(1.1)}
#bhRoot .flowst.mmx-done .flowst-dot{color:transparent;background:#e8f7ee;border-color:#bbf7d0}
#bhRoot .flowst.mmx-done .flowst-dot::after{content:"✓";position:absolute;inset:0;display:grid;place-items:center;color:#16a34a;font-size:12px;font-weight:700;animation:mmxChipIn .35s var(--mmx-spring)}
#bhRoot .mmx-step-sub{display:block;font-size:10.5px;font-weight:500;color:#94a3b8;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-prog{margin:14px 16px 8px;font-size:11px;color:#64748b}
#bhRoot .mmx-prog em{display:block;height:6px;border-radius:6px;background:#f1f3f8;margin-top:6px;overflow:hidden}
#bhRoot .mmx-prog em i{display:block;height:100%;border-radius:6px;background:var(--ac);transition:width .6s var(--mmx-ease),background-color .4s}

/* Step tabs in the middle: underline slides to the chosen tab; the panel fades in */
#bhRoot .dash-vtabs{position:relative}
#bhRoot .dash-vtabs.mmx-ul-on .dvt.on{box-shadow:none}
#bhRoot .dvt{transition:background .25s,color .25s}
#bhRoot .mmx-ul{position:absolute;bottom:0;left:0;height:3px;border-radius:3px 3px 0 0;background:var(--ac);transition:transform .4s var(--mmx-spring),width .4s var(--mmx-spring),background-color .4s;pointer-events:none;z-index:2}
#bhRoot .mm-panel{animation:mmxViewIn .4s var(--mmx-ease) both}
#bhRoot .mmx-board-panel{animation:mmxViewIn .4s var(--mmx-ease) both}

/* Quick segment cards: rise in, lift on hover, ripple on click */
#bhRoot .mmx-seg{position:relative;overflow:hidden;transition:transform .25s var(--mmx-spring),border-color .25s,background .25s,box-shadow .25s}
#bhRoot .mmx-seg:hover{transform:translateY(-3px);box-shadow:0 10px 24px -14px rgba(15,23,42,.35)}
#bhRoot .mmx-seg.on{box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .mmx-segs.mmx-in .mmx-seg{animation:mmxRise .5s var(--mmx-ease) both}
#bhRoot .mmx-segs.mmx-in .mmx-seg:nth-child(2){animation-delay:.05s}#bhRoot .mmx-segs.mmx-in .mmx-seg:nth-child(3){animation-delay:.1s}#bhRoot .mmx-segs.mmx-in .mmx-seg:nth-child(4){animation-delay:.15s}#bhRoot .mmx-segs.mmx-in .mmx-seg:nth-child(5){animation-delay:.2s}#bhRoot .mmx-segs.mmx-in .mmx-seg:nth-child(6){animation-delay:.25s}
#bhRoot .mmx-rip{position:absolute;border-radius:50%;background:var(--ac);opacity:.25;transform:scale(0);animation:mmxRipple .6s ease-out forwards;pointer-events:none}

/* Applied-filter chips above each table: click a chip to change it, × to remove it */
#bhRoot .mmx-chips{display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:8px 14px;border-bottom:1px solid var(--ln);background:#fff}
#bhRoot .mmx-chips[hidden]{display:none}
#bhRoot .mmx-chips-l{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#94a3b8;margin-right:2px}
#bhRoot .mmx-chip{display:inline-flex;align-items:center;gap:4px;background:var(--ac-l);color:var(--ac-d);border:1px solid var(--ac-m);border-radius:20px;padding:3px 4px 3px 10px;font-size:11.5px;font-weight:600;line-height:1.3;font-family:var(--f1);transition:transform .2s var(--mmx-spring),box-shadow .2s,background .2s}
#bhRoot .mmx-chip.new{animation:mmxChipIn .35s var(--mmx-spring)}
#bhRoot .mmx-chip:hover{transform:translateY(-1px);box-shadow:0 4px 10px -6px var(--ac)}
#bhRoot .mmx-chip-e{border:none;background:none;color:inherit;font:inherit;padding:0;cursor:pointer}
#bhRoot .mmx-chip-e b{font-weight:700}
#bhRoot .mmx-chip-x{width:18px;height:18px;border:none;border-radius:50%;background:transparent;color:inherit;font-size:13px;line-height:1;cursor:pointer;display:grid;place-items:center;padding:0;transition:background .2s,transform .2s}
#bhRoot .mmx-chip-x:hover{background:var(--ac-m);transform:rotate(90deg)}
#bhRoot .mmx-chip.all{background:#fff;color:#64748b;border-color:var(--ln);padding-right:10px;cursor:pointer}
#bhRoot .mmx-chip.all:hover{color:#b91c1c;border-color:#fecaca;background:#fef2f2;box-shadow:none}

/* Tables: rows slide in, readiness rings fill, last-active bars grow, hover details */
#bhRoot .dtbl tbody tr{animation:mmxRowIn .4s var(--mmx-ease) both}
#bhRoot .dtbl tbody tr:nth-child(2){animation-delay:.03s}#bhRoot .dtbl tbody tr:nth-child(3){animation-delay:.06s}#bhRoot .dtbl tbody tr:nth-child(4){animation-delay:.09s}#bhRoot .dtbl tbody tr:nth-child(5){animation-delay:.12s}#bhRoot .dtbl tbody tr:nth-child(6){animation-delay:.15s}#bhRoot .dtbl tbody tr:nth-child(7){animation-delay:.18s}#bhRoot .dtbl tbody tr:nth-child(8){animation-delay:.21s}#bhRoot .dtbl tbody tr:nth-child(9){animation-delay:.24s}#bhRoot .dtbl tbody tr:nth-child(n+10){animation-delay:.27s}
#bhRoot .dtbl td{transition:background .2s}
#bhRoot .mmx-ring circle + circle{animation:mmxRingIn 1s var(--mmx-ease) both .15s}
#bhRoot .mmx-bar b{transform-origin:left;animation:mmxGrowX .8s var(--mmx-ease) both .15s}
#bhRoot .bh-av{transition:transform .3s var(--mmx-spring)}
#bhRoot .bh-acct:hover .bh-av{transform:scale(1.12) rotate(-6deg)}
#bhRoot .dtbl td button{transition:transform .2s var(--mmx-spring),box-shadow .2s,filter .2s}
#bhRoot .dtbl td button:hover{transform:scale(1.06);box-shadow:0 6px 14px -6px var(--ac)}
#bhRoot .mm-sort-ind{display:inline-block;transition:transform .3s var(--mmx-spring)}
#bhRoot .mmx-pg-nums button,#bhRoot .mmx-pg > button,#bhRoot .mmx-pg-go button{transition:transform .25s var(--mmx-spring),background .2s,border-color .2s,color .2s}
#bhRoot .mmx-pg-nums button:hover:not(.on),#bhRoot .mmx-pg > button:hover:not(:disabled){transform:translateY(-2px)}
#bhRoot .mmx-pg-nums button.on{transform:scale(1.08)}

/* Board: columns rise, cards pop in one after another and lift on hover */
#bhRoot .mmx-col{animation:mmxRise .5s var(--mmx-ease) both}
#bhRoot .mmx-col:nth-child(2){animation-delay:.07s}#bhRoot .mmx-col:nth-child(3){animation-delay:.14s}#bhRoot .mmx-col:nth-child(4){animation-delay:.21s}#bhRoot .mmx-col:nth-child(n+5){animation-delay:.28s}
#bhRoot .mmx-tile{animation:mmxCardIn .45s var(--mmx-spring) both;transition:transform .25s var(--mmx-spring),box-shadow .25s,border-color .15s}
#bhRoot .mmx-tile:nth-child(2){animation-delay:.04s}#bhRoot .mmx-tile:nth-child(3){animation-delay:.08s}#bhRoot .mmx-tile:nth-child(4){animation-delay:.12s}#bhRoot .mmx-tile:nth-child(5){animation-delay:.16s}#bhRoot .mmx-tile:nth-child(6){animation-delay:.2s}#bhRoot .mmx-tile:nth-child(n+7){animation-delay:.24s}
#bhRoot .mmx-tile:hover{transform:translateY(-2px) rotate(-.4deg);box-shadow:0 10px 22px -14px rgba(15,23,42,.4)}
#bhRoot .mmx-col h4 span{display:inline-block;animation:mmxBump .45s var(--mmx-spring) .3s}
#mmBoardGroup button{transition:background .25s,color .25s}

/* Expanded table zooms in; panels open and close smoothly */
#bhRoot.mm-focus{animation:mmxZoomIn .35s var(--mmx-ease)}
#bhRoot.mmx-gridanim .dash{transition:grid-template-columns .4s var(--mmx-ease)}

/* Mira helper: spinning ring, pulsing Ready, questions slide in, messages pop, typing dots */
#bhRoot .dm-spark{position:relative}
#bhRoot .dm-spark svg{animation:mmxTwinkle 3s ease-in-out infinite}
#bhRoot .dm-spark::before{content:"";position:absolute;inset:-5px;border-radius:50%;background:conic-gradient(from 0deg,var(--ac),transparent 55%,var(--ac));-webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 2px),#000 calc(100% - 1.5px));mask:radial-gradient(farthest-side,transparent calc(100% - 2px),#000 calc(100% - 1.5px));animation:mmspin 4s linear infinite;opacity:.8}
#bhRoot .dm-ready{display:inline-flex;align-items:center;gap:5px}
#bhRoot .dm-ready::before{content:"";width:6px;height:6px;border-radius:50%;background:#16a34a;animation:mmxPulse 2s infinite}
#bhRoot .dm-quick .qk{animation:mmxSlideL .45s var(--mmx-ease) both;transition:transform .25s var(--mmx-ease),box-shadow .25s,border-color .2s,background .2s,color .2s}
#bhRoot .dm-quick .qk:nth-child(2){animation-delay:.07s}#bhRoot .dm-quick .qk:nth-child(3){animation-delay:.14s}#bhRoot .dm-quick .qk:nth-child(4){animation-delay:.21s}#bhRoot .dm-quick .qk:nth-child(5){animation-delay:.28s}#bhRoot .dm-quick .qk:nth-child(n+6){animation-delay:.35s}
#bhRoot .dm-quick .qk:hover{transform:translateX(-3px);box-shadow:0 6px 16px -10px var(--ac)}
#bhRoot .dm-quick .qk::after{transition:transform .25s}
#bhRoot .dm-quick .qk:hover::after{transform:translateY(-50%) translateX(3px) rotate(45deg)}
#bhRoot .dm-chat .msg{animation:mmxBubIn .4s var(--mmx-spring) both}
#bhRoot .dm-chat:empty::after,#bhRoot .dm-chat:empty::before{animation:mmxBubIn .45s var(--mmx-spring) both}
#bhRoot .dm-chat .msg.mmx-reveal > *{animation:mmxViewIn .45s var(--mmx-ease) both}
#bhRoot .mmx-typing{display:inline-flex;gap:4px;padding:4px 2px}
#bhRoot .mmx-typing i{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:mmxBlink 1.2s infinite}
#bhRoot .mmx-typing i:nth-child(2){animation-delay:.15s}#bhRoot .mmx-typing i:nth-child(3){animation-delay:.3s}
#bhRoot .dm-inbar .in{transition:border-color .2s,box-shadow .2s}
#bhRoot .dm-inbar .in:focus{border-color:var(--ac);box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .dm-inbar .send{transition:transform .2s var(--mmx-spring)}
#bhRoot .dm-inbar .send:hover{transform:scale(1.08) rotate(-10deg)}
#bhRoot .mira-btn{transition:transform .2s var(--mmx-spring),background .2s}
#bhRoot .mira-btn:hover{transform:scale(1.1)}

/* Windows: message composer, popups and list windows scale in; Profile panel sections rise */
.risk-modal-overlay.show,.mm-cmp-overlay.show,.mm-stu-overlay.show,.mm-list-modal-overlay.show{animation:mmxFade .25s ease}
.risk-modal-overlay.show .risk-modal,.mm-cmp-overlay.show .mm-cmp,.mm-stu-overlay.show .mm-stu-card,.mm-list-modal-overlay.show .mm-list-modal{animation:mmxPop .35s var(--mmx-spring)}
.mm-cmp-tpl{animation:mmxRise .4s var(--mmx-ease) both;transition:border-color .2s,background .2s,transform .2s var(--mmx-ease)}
.mm-cmp-tpl:nth-child(2){animation-delay:.05s}.mm-cmp-tpl:nth-child(3){animation-delay:.1s}.mm-cmp-tpl:nth-child(4){animation-delay:.15s}.mm-cmp-tpl:nth-child(n+5){animation-delay:.2s}
.mm-cmp-tpl:hover{transform:translateX(2px)}
.mm-cmp-send button{transition:background .25s,color .25s,transform .2s var(--mmx-spring)}
.mm-stu-more,.mmx-prof-btn{transition:transform .2s var(--mmx-spring),background .2s,border-color .2s,color .2s}
.mm-stu-more:hover,.mmx-prof-btn:hover{transform:translateY(-1px)}
.mmx-prof{transition:transform .42s var(--mmx-ease)}
.mmx-prof.show .mmx-prof-b > *{animation:mmxRise .5s var(--mmx-ease) both}
.mmx-prof.show .mmx-prof-b > :nth-child(2){animation-delay:.06s}.mmx-prof.show .mmx-prof-b > :nth-child(3){animation-delay:.12s}.mmx-prof.show .mmx-prof-b > :nth-child(4){animation-delay:.18s}.mmx-prof.show .mmx-prof-b > :nth-child(n+5){animation-delay:.24s}
.mmx-prof .mmx-skill i b{transform-origin:left;animation:mmxGrowX .8s var(--mmx-ease) both .25s}
.mmx-prof-pic{animation:mmxChipIn .45s var(--mmx-spring)}
.mmx-tl div{animation:mmxSlideR .4s var(--mmx-ease) both}
.mmx-tl div:nth-child(2){animation-delay:.07s}.mmx-tl div:nth-child(3){animation-delay:.14s}.mmx-tl div:nth-child(n+4){animation-delay:.21s}
.mm-pf-menu{transition:opacity .16s,transform .2s var(--mmx-spring)}

/* Sidebar (this page only): items slide in and nudge on hover, groups open smoothly, data-sources card */
#platformSidebar nav > *{animation:mmxSlideR .4s var(--mmx-ease) both}
#platformSidebar nav > :nth-child(2){animation-delay:.04s}#platformSidebar nav > :nth-child(3){animation-delay:.08s}#platformSidebar nav > :nth-child(4){animation-delay:.12s}#platformSidebar nav > :nth-child(5){animation-delay:.16s}#platformSidebar nav > :nth-child(6){animation-delay:.2s}#platformSidebar nav > :nth-child(7){animation-delay:.24s}#platformSidebar nav > :nth-child(n+8){animation-delay:.28s}
#platformSidebar nav a,#platformSidebar nav button,#platformSidebar a[href]{transition:background-color .2s,color .2s,transform .2s var(--mmx-ease)}
#platformSidebar nav a:hover,#platformSidebar nav button:hover,#platformSidebar > div > a[href]:hover{transform:translateX(2px)}
#platformSidebar a.border-l-2{animation:mmxSlideR .45s var(--mmx-ease) both}
#platformSidebar svg[id$="Chevron"]{transition:transform .3s var(--mmx-ease)}
#platformSidebar [id$="Submenu"]:not(.hidden) > *{animation:mmxSlideR .3s var(--mmx-ease) both}
#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(2){animation-delay:.03s}#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(3){animation-delay:.06s}#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(4){animation-delay:.09s}#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(n+5){animation-delay:.12s}
#platformSidebar [data-dd-menu]{animation:mmxPop .25s var(--mmx-spring)}
#platformSidebar .mmx-src-host > :not(.mmx-src){display:none}
#platformSidebar .mmx-src-host{background:transparent;border-top:none;padding:10px}
.mmx-src{background:#fafafb;border:1px solid #ececf2;border-radius:12px;padding:10px 10px 9px;animation:mmxRise .5s var(--mmx-ease) both .3s}
.mmx-src-t{font-size:11px;color:#64748b;line-height:1.35}
.mmx-src-t b{color:#0f172a;font-weight:700}
.mmx-src-bar{height:6px;border-radius:6px;background:#ececf2;margin:8px 0 7px;overflow:hidden}
.mmx-src-bar i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,#16a34a,#34d399);transform-origin:left;animation:mmxGrowX 1s var(--mmx-ease) both .5s}
.mmx-src-s{font-size:10.5px;color:#94a3b8;line-height:1.4}
.mmx-src a{display:inline-block;margin-top:4px;font-size:10.5px;color:#0d9488}

/* ── Part 6 (Nov 2026): narrow windows (for example the browser snapped to half the screen).
   Up to 1180px wide the page scrolls up and down instead of squeezing everything into one screen:
   the header wraps, and the steps, the middle panel and the helper stack one under another.
   Wider windows and the expanded (full-screen) table are unchanged. ── */
@media (max-width:1180px){
  .bh-page{overflow-y:auto!important;overflow-x:hidden!important}
  .bh-page > header{flex-wrap:wrap;gap:10px 12px;padding:14px 16px}
  .bh-page > header > div:first-child{flex:1 1 220px;min-width:0}
  .bh-page > header > div:last-child{flex-wrap:wrap;gap:8px}
  .bh-page > .flex-1{flex:none!important;overflow:visible!important;padding:16px!important}
  #bhRoot:not(.mm-focus){height:auto!important;overflow:visible!important}
  #bhRoot:not(.mm-focus) .dash.on{display:flex!important;flex-direction:column;flex:none;overflow:visible;gap:12px}
  #bhRoot:not(.mm-focus) .dash-left{flex:none;max-height:none;overflow:visible}
  #bhRoot:not(.mm-focus) .dash-left .g-body{display:flex;flex-wrap:wrap;gap:2px 0}
  #bhRoot:not(.mm-focus) .dash-left .flowst{flex:1 1 170px;min-width:0}
  #bhRoot:not(.mm-focus) .dash-left .mmx-prog{flex:1 1 100%}
  #bhRoot:not(.mm-focus) .dash-main{flex:none;overflow:hidden}
  #bhRoot:not(.mm-focus) .dash-view{flex:none;overflow-x:auto}
  #bhRoot:not(.mm-focus) .dash-mira{flex:none;height:560px}
  #bhRoot.bh-mira-min:not(.mm-focus) .dash-mira,#bhRoot.bh-left-min:not(.mm-focus) .dash-left{height:auto}
  #bhRoot:not(.mm-focus) .mmx-board-panel{flex:none;height:min(80vh,900px)}
  #bhRoot .mmx-kpis{grid-template-columns:repeat(auto-fill,minmax(190px,1fr))}
  #bhRoot .mmx-kpi:nth-child(n+4){display:block}
  #bhRoot .mmx-segs{grid-auto-flow:row;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));overflow-x:visible}
}
@media (max-width:760px){
  .bh-page > .flex-1{padding:12px!important}
  #bhRoot .atab{padding:0 8px;gap:6px}
  #bhRoot .atab .a2{white-space:normal;line-height:1.2;text-align:center}
  #bhRoot .mmx-ab{display:none}
  #bhRoot .mmx-kpis{grid-template-columns:repeat(auto-fill,minmax(150px,1fr))}
}

/* ── Part 7 (Nov 2026): concept-page design for Performance, Audience, Insights (Marketing),
   Scripts, Objections (Sales), Root cause, Offers (Retention), and the agent tabs. Real data; same colours. ── */
/* Agent tabs: padded white bar, rounded sliding background with a soft gradient, no dividers */
#bhRoot .atabs{padding:5px;gap:4px;border-radius:var(--card-r,14px)}
#bhRoot .atab,#bhRoot .atab + .atab{border-left:none!important;border-radius:10px;min-height:46px}
#bhRoot .atab .a2{transition:color .3s}
#bhRoot .mmx-at-slider{top:5px;bottom:5px;border-radius:10px;background-image:linear-gradient(135deg,var(--ac) 0%,color-mix(in srgb,var(--ac) 58%,#fff) 100%);box-shadow:0 8px 20px -10px var(--ac)}
#bhRoot .mmx-ab{background:rgba(15,23,42,.06)}

/* Shared: icon chip, "view" hint, small tag */
#bhRoot .mmx-ins-ic{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;background:var(--ac-l);color:var(--ac-d);flex-shrink:0;transition:transform .3s var(--mmx-spring)}
#bhRoot .mmx-ins-ic svg{width:16px;height:16px}
#bhRoot .mmx-go{display:inline-block;margin-top:10px;font-size:11.5px;font-weight:600;color:var(--ac-d);opacity:0;transform:translateX(-4px);transition:opacity .25s,transform .25s}
#bhRoot .mmx-tag{flex-shrink:0;align-self:center;font-size:10.5px;font-weight:600;padding:3px 8px;border-radius:6px;background:#e8f0fe;color:#1d4ed8}
#bhRoot .mmx-td-wait{padding:24px 20px;color:var(--g3);font-size:12.5px}

/* Insight / root-cause cards */
#bhRoot .mmx-ins-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:12px;padding:0 20px 20px}
#bhRoot .mmx-ins{position:relative;display:flex;flex-direction:column;align-items:flex-start;text-align:left;font-family:var(--f1);background:#fff;border:1px solid var(--ln);border-radius:12px;padding:14px;cursor:pointer;overflow:hidden;animation:mmxRise .5s var(--mmx-ease) both;transition:transform .25s var(--mmx-ease),box-shadow .25s,border-color .25s}
#bhRoot .mmx-ins:nth-child(2){animation-delay:.06s}#bhRoot .mmx-ins:nth-child(3){animation-delay:.12s}#bhRoot .mmx-ins:nth-child(n+4){animation-delay:.18s}
#bhRoot .mmx-ins:not([onclick]){cursor:default}
#bhRoot .mmx-ins:hover{transform:translateY(-3px);box-shadow:0 12px 26px -16px rgba(15,23,42,.35);border-color:var(--ac-m)}
#bhRoot .mmx-ins:hover .mmx-go,#bhRoot .mmx-tile2:hover .mmx-go{opacity:1;transform:none}
#bhRoot .mmx-ins:hover .mmx-ins-ic,#bhRoot .mmx-tile2:hover .mmx-ins-ic{transform:scale(1.1) rotate(-6deg)}
#bhRoot .mmx-ins .mmx-ins-ic{margin-bottom:10px}
#bhRoot .mmx-ins b{display:block;font-size:13px;color:#0f172a;margin-bottom:4px}
#bhRoot .mmx-ins p{margin:0;color:#64748b;line-height:1.55;font-size:12px}
#bhRoot .mmx-ins-big{font-size:22px;font-weight:800;color:var(--ac);margin-top:8px;font-variant-numeric:tabular-nums}
#bhRoot .mmx-rank{position:absolute;top:14px;right:14px;font-size:11px;font-weight:700;color:var(--g3)}
#bhRoot .mmx-share{display:block;width:100%;margin-top:6px}
#bhRoot .mmx-share em{display:block;height:6px;border-radius:6px;background:#eef0f6;overflow:hidden}
#bhRoot .mmx-share em i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,var(--ac),color-mix(in srgb,var(--ac) 55%,#fff));transform-origin:left;animation:mmxGrowX 1s var(--mmx-ease) both .2s}
#bhRoot .mmx-share small{display:block;font-size:11px;color:var(--g3);margin-top:5px}

/* Audience segment tiles */
#bhRoot .mmx-tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;padding:0 20px 14px}
#bhRoot .mmx-tile2{display:flex;flex-direction:column;align-items:flex-start;text-align:left;font-family:var(--f1);background:#fff;border:1px solid var(--ln);border-radius:12px;padding:12px 14px;cursor:pointer;animation:mmxRise .5s var(--mmx-ease) both;transition:transform .25s var(--mmx-spring),box-shadow .25s,border-color .25s}
#bhRoot .mmx-tile2:nth-child(2){animation-delay:.05s}#bhRoot .mmx-tile2:nth-child(3){animation-delay:.1s}#bhRoot .mmx-tile2:nth-child(n+4){animation-delay:.15s}
#bhRoot .mmx-tile2:hover{transform:translateY(-3px);box-shadow:0 10px 24px -14px rgba(15,23,42,.35);border-color:var(--ac-m)}
#bhRoot .mmx-tile2-t{font-size:12px;font-weight:600;color:#334155;margin-top:8px}
#bhRoot .mmx-tile2-n{font-size:22px;font-weight:800;color:#0f172a;margin-top:2px;font-variant-numeric:tabular-nums}
#bhRoot .mmx-tile2-s{font-size:11px;color:var(--g3)}

/* Panels: charts, breakdown bars, section bars, highlights */
#bhRoot .mmx-td{padding:0 20px 20px}
#bhRoot .mmx-grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;margin-top:12px}
#bhRoot .mmx-panel{background:#fff;border:1px solid var(--ln);border-radius:12px;padding:14px 16px;animation:mmxRise .55s var(--mmx-ease) both}
#bhRoot .mmx-panel h3{display:flex;align-items:center;gap:6px;margin:0 0 10px;font-size:12px;font-weight:700;color:#334155}
#bhRoot .mmx-panel h3 span{margin-left:auto;font-weight:500;color:var(--g3);font-size:11px}
#bhRoot .mmx-chart{cursor:pointer}
#bhRoot .mmx-chart svg{display:block;width:100%;height:auto;overflow:visible}
#bhRoot .mmx-chart .l{fill:none;stroke:var(--ac);stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:1;stroke-dashoffset:1;animation:mmxDraw 1.6s var(--mmx-ease) forwards}
#bhRoot .mmx-chart .a{fill:var(--ac);opacity:0;animation:mmxArea 1s ease forwards .8s}
#bhRoot .mmx-chart .bar{fill:var(--ac-m);transform-box:fill-box;transform-origin:bottom;animation:mmxGrowY .8s var(--mmx-spring) both}
#bhRoot .mmx-chart circle{fill:#fff;stroke:var(--ac);stroke-width:2;transform-box:fill-box;transform-origin:center;animation:mmxChipIn .4s var(--mmx-spring) both;transition:r .2s}
#bhRoot .mmx-chart circle:hover{r:6}
#bhRoot .mmx-chart text{font-size:10px;fill:#94a3b8;font-family:var(--f1)}
#bhRoot .mmx-chart-k{display:flex;gap:14px;font-size:11px;color:var(--g3);margin-top:6px}
#bhRoot .mmx-chart-k i{display:inline-block;width:10px;height:3px;border-radius:2px;background:var(--ac);vertical-align:middle;margin-right:5px}
#bhRoot .mmx-chart-k i.b{height:8px;background:var(--ac-m)}
#bhRoot .mmx-bars{display:flex;flex-direction:column;gap:6px}
#bhRoot .mmx-bar-row{display:grid;grid-template-columns:minmax(90px,130px) 1fr auto;align-items:center;gap:10px;width:100%;border:none;background:none;padding:4px 6px;margin:0 -6px;border-radius:8px;font-family:var(--f1);font-size:12px;color:#475569;text-align:left;cursor:pointer;transition:background .2s}
#bhRoot .mmx-bar-row:hover{background:var(--ac-l)}
#bhRoot .mmx-bar-row:disabled{cursor:default;background:none}
#bhRoot .mmx-bar-row span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bhRoot .mmx-bar-row em{height:14px;border-radius:5px;background:#f1f3f8;overflow:hidden;font-style:normal}
#bhRoot .mmx-bar-row em i{display:block;height:100%;border-radius:5px;background:linear-gradient(90deg,var(--ac),color-mix(in srgb,var(--ac) 55%,#fff));transform-origin:left;animation:mmxGrowX 1s var(--mmx-ease) both}
#bhRoot .mmx-bar-row b{font-variant-numeric:tabular-nums;color:#0f172a;font-weight:600;text-align:right;min-width:64px}
#bhRoot .mmx-bar-row b small{display:block;font-weight:500;color:var(--g3);font-size:10.5px}
#bhRoot .mmx-hl{display:flex;flex-direction:column;gap:10px;border-left:2px solid var(--ln);padding-left:14px}
#bhRoot .mmx-hl button{position:relative;border:none;background:none;padding:0;text-align:left;font-family:var(--f1);font-size:12.5px;color:#0f172a;cursor:pointer;animation:mmxSlideR .4s var(--mmx-ease) both}
#bhRoot .mmx-hl button:hover{color:var(--ac-d)}
#bhRoot .mmx-hl button::before{content:"";position:absolute;left:-20px;top:4px;width:8px;height:8px;border-radius:50%;background:#fff;border:2px solid var(--ac)}
#bhRoot .mmx-hl button.bad::before{border-color:#dc2626}
#bhRoot .mmx-hl small{display:block;color:var(--g3);font-size:11px;margin-top:1px}

/* Open / close cards (Scripts, Objections, Offers) */
#bhRoot .mmx-acc{border:1px solid var(--ln);border-radius:12px;background:#fff;overflow:hidden;animation:mmxRise .45s var(--mmx-ease) both;transition:box-shadow .25s,border-color .25s}
#bhRoot .mmx-acc:nth-child(2){animation-delay:.05s}#bhRoot .mmx-acc:nth-child(3){animation-delay:.1s}#bhRoot .mmx-acc:nth-child(n+4){animation-delay:.15s}
#bhRoot .mmx-acc.open{box-shadow:0 10px 24px -16px rgba(15,23,42,.35);border-color:var(--ac-m)}
#bhRoot .mmx-acc-hd{display:flex;align-items:center;gap:12px;padding:12px 14px;cursor:pointer;background:#fff;border-bottom:1px solid transparent;transition:background .2s,border-color .2s}
#bhRoot .mmx-acc-hd:hover{background:var(--p1)}
#bhRoot .mmx-acc.open .mmx-acc-hd{border-bottom-color:var(--ln);background:var(--p1)}
#bhRoot .mmx-acc-hd:focus-visible{outline:2px solid var(--ac);outline-offset:-2px}
#bhRoot .mmx-acc-chev{width:15px;height:15px;flex-shrink:0;color:var(--g3);transition:transform .35s var(--mmx-ease),color .2s}
#bhRoot .mmx-acc.open .mmx-acc-chev{transform:rotate(180deg);color:var(--ac)}
#bhRoot .mmx-acc-bd{display:grid;grid-template-rows:0fr;transition:grid-template-rows .4s var(--mmx-ease)}
#bhRoot .mmx-acc.open .mmx-acc-bd{grid-template-rows:1fr}
#bhRoot .mmx-acc-bd > div{overflow:hidden;min-height:0}
#bhRoot .mm-scr.mmx-acc .mm-scr-hd{align-items:center}
#bhRoot .mm-scr.mmx-acc .mm-scr-n{margin-top:0}
#bhRoot .mmx-offers{display:flex;flex-direction:column;gap:10px;padding:0 20px 20px}
#bhRoot .mmx-offer-t{flex:1;min-width:0}
#bhRoot .mmx-offer-t b{display:block;font-size:13px;color:#0f172a}
#bhRoot .mmx-offer-t small{display:block;font-size:11px;color:var(--g2);margin-top:2px}
#bhRoot .mmx-offer-n{flex-shrink:0;min-width:44px;text-align:center;font-size:14px;font-weight:800;color:var(--ac-d);background:var(--ac-l);border-radius:20px;padding:3px 10px;font-variant-numeric:tabular-nums}
#bhRoot .mmx-offer-bd{padding:12px 14px 14px;font-size:12.5px;line-height:1.65;color:#1e293b}
#bhRoot .mmx-offer-bd p{margin:0 0 8px}
#bhRoot .mmx-offer-bd p b{color:#475569}
#bhRoot .mmx-offer-go{margin-top:4px;border:none;background:var(--ac);color:#fff;border-radius:8px;padding:7px 14px;font-size:12.5px;font-weight:600;font-family:var(--f1);cursor:pointer;transition:transform .2s var(--mmx-spring),box-shadow .2s}
#bhRoot .mmx-offer-go:hover{transform:scale(1.04);box-shadow:0 6px 14px -6px var(--ac)}
#bhRoot .mmx-perf-kpis .mg-cell{animation:mmxRise .5s var(--mmx-ease) both;transition:transform .25s var(--mmx-ease),box-shadow .25s,background .15s,border-color .15s}
#bhRoot .mmx-perf-kpis .mg-cell:nth-child(2){animation-delay:.05s}#bhRoot .mmx-perf-kpis .mg-cell:nth-child(3){animation-delay:.1s}#bhRoot .mmx-perf-kpis .mg-cell:nth-child(4){animation-delay:.15s}
#bhRoot .mmx-perf-kpis .mg-cell:hover{transform:translateY(-3px);box-shadow:0 12px 26px -16px rgba(15,23,42,.35)}
@media (max-width:760px){#bhRoot .mmx-acc-hd .mmx-tag{display:none}}

/* ── Part 8 (Nov 2026): dropdown filters on Payment date / Expires / Stage / Approach / Last active / Next best action,
   and a maximise / minimise button on every popup that lists students or records ── */
th[data-mm-col-filter]{white-space:nowrap}
.mmx-cf-btn{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;margin-left:5px;padding:0;border:1px solid #e2e5ee;border-radius:5px;background:#fff;color:#6b7280;cursor:pointer;vertical-align:middle;transition:background .15s,border-color .15s,color .15s}
.mmx-cf-btn svg{width:11px;height:11px;transition:transform .2s cubic-bezier(.4,0,.2,1)}
.mmx-cf-btn:hover{background:#f5f3ff;border-color:#c4b5fd;color:#6d28d9}
.mmx-cf-btn:focus-visible{outline:2px solid #c4b5fd;outline-offset:1px}
.mmx-cf-btn.open svg{transform:rotate(180deg)}
.mmx-cf-btn.on{background:#7c3aed;border-color:#7c3aed;color:#fff}
.mmx-cf-menu .mm-pf-opt:disabled{opacity:1}
.mmx-max-btn{display:inline-grid;place-items:center;width:28px;height:28px;margin-left:auto!important;border:1px solid #e5e7eb!important;border-radius:8px;background:#fff!important;color:#64748b!important;padding:0!important;cursor:pointer;flex-shrink:0;transition:background .15s,border-color .15s,color .15s,transform .2s}
.mmx-max-btn + button{margin-left:8px!important}
.mmx-max-btn:hover{background:#f5f3ff!important;border-color:#c4b5fd!important;color:#6d28d9!important;transform:scale(1.06)}
.mmx-max-btn svg{width:15px;height:15px}
.mmx-max-btn .mmx-i-min,.mmx-max .mmx-max-btn .mmx-i-max{display:none}
.mmx-max .mmx-max-btn .mmx-i-min{display:block}
.risk-modal,.mm-list-modal{transition:max-width .3s cubic-bezier(.22,.8,.26,1),max-height .3s cubic-bezier(.22,.8,.26,1)}
.risk-modal.mmx-max,.mm-list-modal.mmx-max{width:96vw;max-width:1500px;height:92vh;max-height:92vh;animation:mmxPop .3s var(--mmx-spring,ease)}
.risk-modal.mmx-max .risk-modal-body,.mm-list-modal.mmx-max .mm-list-modal-body{flex:1;min-height:0}
.mmx-max .risk-modal-hd,.mmx-max .mm-list-modal-hd{font-size:14.5px}
.mmx-max table{font-size:13px!important}
.mmx-max th,.mmx-max td{padding:9px 12px!important}
.risk-modal-hd,.mm-list-modal-hd{user-select:none}

/* ── Part 10 (Nov 2026): Board column expand button, and Send to all in the list popups ── */
#bhRoot .mmx-col-exp{display:inline-grid;place-items:center;width:24px;height:24px;margin-left:2px;padding:0;border:1px solid var(--ln);border-radius:7px;background:#fff;color:#64748b;cursor:pointer;flex-shrink:0;transition:background .15s,border-color .15s,color .15s,transform .2s var(--mmx-spring,ease)}
#bhRoot .mmx-col-exp:hover{background:#f5f3ff;border-color:#c4b5fd;color:#6d28d9;transform:scale(1.08)}
#bhRoot .mmx-col-exp svg{width:13px;height:13px}
.mmx-send-btn{display:inline-flex;align-items:center;gap:6px;margin-left:auto!important;height:28px;border:1px solid #7c3aed!important;border-radius:8px;background:#fff!important;color:#7c3aed!important;padding:0 11px!important;font:600 12px Inter,-apple-system,'Segoe UI',sans-serif!important;line-height:1!important;cursor:pointer;flex-shrink:0;white-space:nowrap;transition:background .15s,color .15s,transform .2s}
.mmx-send-btn:hover{background:#7c3aed!important;color:#fff!important;transform:translateY(-1px)}
.mmx-send-btn:disabled{opacity:.6;cursor:wait}
.mmx-send-btn[hidden]{display:none}
.mmx-send-btn:not([hidden]) + .mmx-max-btn{margin-left:8px!important}

@media (prefers-reduced-motion:reduce){
  #bhRoot *,#bhRoot *::before,#bhRoot *::after,#platformSidebar *,#platformSidebar *::before,.bh-page > header *,.mmx-prof *,.mm-cmp-overlay *,.risk-modal-overlay *,.mm-stu-overlay *,.mm-list-modal-overlay *{animation-duration:.01ms!important;animation-delay:0s!important;animation-iteration-count:1!important;transition-duration:.01ms!important}
}
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
/* ── Plan filter: dropdown arrow inside every "Plan" header ──
   Filters the rows already in that table by plan. The choice is kept on
   the table (data-plan-filter) and re-applied when the table's rows are
   re-rendered (Campaign paging/filters, popup Load more). */
var MM_PLAN_ORDER = ['Free trial', 'Paid', 'Coaching', 'Coaching + Paid', 'No purchase', 'Other'];
var MM_PLAN_DESC = {
    'Free trial': 'Only the free mock test', 'Paid': 'Bought a paid package', 'Coaching': 'Coaching access, never paid',
    'Coaching + Paid': 'Coaching and a paid package', 'No purchase': 'Nothing bought yet', 'Other': 'Testing or special access'
};
var MM_PLAN_GROUPS = {
    all:    { label: 'All plans',     desc: 'Show every student',           plans: null },
    paid:   { label: 'Any paid plan', desc: 'Paid, Coaching + Paid',         plans: ['Paid', 'Coaching + Paid'] },
    unpaid: { label: 'Never paid',    desc: 'Free trial, Coaching, No purchase', plans: ['Free trial', 'Coaching', 'No purchase'] }
};
var MM_LIST_CURRENT = null;
var MM_PF_MENU = null;
// Campaign table: its Plan filter runs on the server across all candidates (like its sort).
var MM_CAMPAIGN_PLAN = (function () { try { return new URLSearchParams(location.search).get('plan'); } catch (e) { return null; } })();

var MM_PF_CHEVRON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
var MM_PF_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';

/* Header row, Plan column (idx, -1 if none) and the data rows of a table.
   "Nothing found" placeholder rows (a single colspan cell) are skipped. */
function mmPfParts(table) {
    var headRow = Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
    if (!headRow) return null;
    var th = headRow.querySelector('th[data-mm-plan-filter]');
    var idx = th ? Array.prototype.indexOf.call(headRow.children, th) : -1;
    var rows = Array.prototype.filter.call(table.querySelectorAll('tr'), function (tr) {
        if (tr === headRow || tr.classList.contains('mm-pf-empty')) return false;
        return !(tr.children.length === 1 && tr.children[0].hasAttribute('colspan'));
    });
    return { th: th, headRow: headRow, idx: idx, rows: rows, cols: headRow.children.length };
}
/* Number in a cell: "$1,299" → 1299, "21d" → 21, "in 5 days" → 5, "today" → 0, "—" → null. */
function mmNumOf(td) {
    if (!td) return null;
    var t = (td.textContent || '').trim().toLowerCase();
    if (!t || t === '—' || t === '-') return null;
    if (/\btoday\b/.test(t)) return 0;
    var m = t.replace(/,/g, '').match(/-?\d+(\.\d+)?/);
    return m ? parseFloat(m[0]) : null;
}
function mmNumActive(f) { return !!f && (f.min !== null && f.min !== undefined || f.max !== null && f.max !== undefined); }
function mmTableFiltersActive(table) {
    var v = table.getAttribute('data-plan-filter');
    var num = table.__mmNum || {};
    return (v && v !== 'all') || !!table.__mmSort || Object.keys(num).some(function (k) { return mmNumActive(num[k]); });
}
function mmPfPlanOf(tr, idx) { return (tr.children[idx].textContent || '').trim(); }
function mmPfMatches(plan, value) {
    if (!value || value === 'all') return true;
    if (MM_PLAN_GROUPS[value]) return MM_PLAN_GROUPS[value].plans.indexOf(plan) > -1;
    return plan === value;
}
function mmPfLabel(value) {
    return MM_PLAN_GROUPS[value] ? MM_PLAN_GROUPS[value].label : value;
}
/* Applies every filter on a table together: Plan + number ranges (a row must
   pass all of them) and the number sort. Shared by the Plan and number menus. */
function mmPlanFilterApply(table) {
    var p = mmPfParts(table);
    if (!p) return;
    var value = table.getAttribute('data-plan-filter') || 'all';
    var num = table.__mmNum || {};
    var ranges = Object.keys(num).filter(function (k) { return mmNumActive(num[k]); });
    var shown = 0;
    p.rows.forEach(function (tr) {
        var ok = p.idx < 0 || !tr.children[p.idx] || mmPfMatches(mmPfPlanOf(tr, p.idx), value);
        for (var i = 0; ok && i < ranges.length; i++) {
            var f = num[ranges[i]], v = mmNumOf(tr.children[ranges[i]]);
            if (v === null || (f.min !== null && f.min !== undefined && v < f.min) || (f.max !== null && f.max !== undefined && v > f.max)) ok = false;
        }
        if (tr.hidden === ok) tr.hidden = !ok;
        if (ok) shown++;
    });

    // Order: remember each row's original position, then sort (or restore it).
    p.rows.forEach(function (tr) {
        if (!tr.hasAttribute('data-mm-ord')) tr.setAttribute('data-mm-ord', table.__mmOrdSeq = (table.__mmOrdSeq || 0) + 1);
    });
    var s = table.__mmSort;
    var ordOf = function (tr) { return +tr.getAttribute('data-mm-ord'); };
    var want = p.rows.slice().sort(function (a, b) {
        if (s) {
            var va = mmNumOf(a.children[s.idx]), vb = mmNumOf(b.children[s.idx]);
            if (va === null && vb !== null) return 1;
            if (vb === null && va !== null) return -1;
            if (va !== vb) return s.dir === 'desc' ? vb - va : va - vb;
        }
        return ordOf(a) - ordOf(b);
    });
    if (want.some(function (tr, i) { return tr !== p.rows[i]; })) {
        want.forEach(function (tr) { tr.parentElement.appendChild(tr); });
    }

    // "Nothing found" row — only when the filters hide everything.
    var empty = table.querySelector('tr.mm-pf-empty');
    var needEmpty = (value !== 'all' || ranges.length > 0) && shown === 0 && p.rows.length > 0;
    if (needEmpty && !empty) {
        empty = document.createElement('tr');
        empty.className = 'mm-pf-empty';
        empty.innerHTML = '<td colspan="' + p.cols + '">No students match these filters in this list.</td>';
        (p.rows[p.rows.length - 1] || p.headRow).parentElement.appendChild(empty);
    } else if (needEmpty && empty && empty.nextElementSibling) {
        // Keep it last after re-sorting — only move it when it isn't already,
        // otherwise the move itself re-triggers the observer in a loop.
        empty.parentElement.appendChild(empty);
    } else if (!needEmpty && empty) {
        empty.remove();
    }

    var btn = p.th && p.th.querySelector('.mm-pf-btn:not(.mm-nf-btn)');
    if (btn) {
        var on = value !== 'all';
        btn.classList.toggle('on', on);
        btn.setAttribute('aria-label', on ? 'Plan filter: ' + mmPfLabel(value) + ' (change)' : 'Filter by plan');
        btn.title = on ? 'Showing: ' + mmPfLabel(value) : 'Filter by plan';
    }
    p.headRow.querySelectorAll('th[data-mm-num-filter]').forEach(function (th) {
        var nb = th.querySelector('.mm-nf-btn');
        if (!nb) return;
        var i = Array.prototype.indexOf.call(p.headRow.children, th), f = num[i];
        var parts = [];
        if (mmNumActive(f)) parts.push(f.min !== null && f.max !== null ? f.min + ' to ' + f.max : (f.min !== null ? '≥ ' + f.min : '≤ ' + f.max));
        if (s && s.idx === i) parts.push(s.dir === 'desc' ? 'decreasing' : 'increasing');
        var sk = th.classList.contains('mm-sortable') ? th.getAttribute('data-sort-key') : null;
        if (sk && typeof MM_CAMPAIGN_SORT !== 'undefined' && MM_CAMPAIGN_SORT.key === sk) parts.push(MM_CAMPAIGN_SORT.dir === 'desc' ? 'decreasing' : 'increasing');
        nb.classList.toggle('on', parts.length > 0);
        nb.title = parts.length ? 'Filtered: ' + parts.join(', ') : 'Sort or filter by number';
        nb.setAttribute('aria-label', nb.title);
    });
}
/* Adds the arrow to Plan headers inside scope; optional initial value. */
function mmPlanFilterInit(scope, initial) {
    if (!scope) return;
    var tables = [];
    scope.querySelectorAll('th[data-mm-plan-filter], th[data-mm-num-filter]').forEach(function (th) {
        var isNum = th.hasAttribute('data-mm-num-filter');
        var table = th.closest('table');
        if (!th.querySelector('.mm-pf-btn')) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = isNum ? 'mm-pf-btn mm-nf-btn' : 'mm-pf-btn';
            btn.setAttribute('aria-haspopup', isNum ? 'dialog' : 'listbox');
            btn.setAttribute('aria-expanded', 'false');
            btn.title = isNum ? 'Sort or filter by number' : 'Filter by plan';
            btn.setAttribute('aria-label', btn.title);
            btn.innerHTML = MM_PF_CHEVRON;
            th.appendChild(btn);
        }
        if (tables.indexOf(table) === -1) tables.push(table);
    });
    tables.forEach(function (table) {
        if (initial && !table.hasAttribute('data-plan-filter')) table.setAttribute('data-plan-filter', initial);
        if (mmTableFiltersActive(table)) mmPlanFilterApply(table);
        // Re-apply when the rows are replaced (Campaign paging, filters, sorting).
        if (!table.__mmPfObs) {
            table.__mmPfObs = new MutationObserver(function () {
                if (mmTableFiltersActive(table)) mmPlanFilterApply(table);
            });
            table.__mmPfObs.observe(table, { childList: true, subtree: true });
        }
    });
}

/* Number menu for one column: sort (low/high/default) + Min/Max with − / + steppers. */
function mmNumMenuOpen(btn) {
    var table = btn.closest('table');
    var p = mmPfParts(table);
    if (!p) return;
    var th = btn.closest('th');
    var idx = Array.prototype.indexOf.call(p.headRow.children, th);
    var label = (th.childNodes[0] && th.childNodes[0].textContent || th.textContent).trim();
    var vals = p.rows.map(function (tr) { return mmNumOf(tr.children[idx]); }).filter(function (v) { return v !== null; });
    var lo = vals.length ? Math.min.apply(null, vals) : 0, hi = vals.length ? Math.max.apply(null, vals) : 0;
    var step = hi - lo > 200 ? 10 : 1;
    var f = (table.__mmNum || {})[idx] || { min: null, max: null };
    // The Campaign table sorts on the server (its header click); its menu drives that same sort.
    var serverKey = th.classList.contains('mm-sortable') ? th.getAttribute('data-sort-key') : null;
    var s = serverKey
        ? (typeof MM_CAMPAIGN_SORT !== 'undefined' && MM_CAMPAIGN_SORT.key === serverKey ? MM_CAMPAIGN_SORT.dir : 'none')
        : (table.__mmSort && table.__mmSort.idx === idx ? table.__mmSort.dir : 'none');

    var dirBtn = function (dir, text, icon) {
        var on = s === dir;
        return '<button type="button" class="mm-nf-dir' + (on ? ' on' : '') + '" data-kind="sort" data-dir="' + dir + '" aria-pressed="' + on + '">' + icon + text + '</button>';
    };
    var stepper = function (which, text) {
        var v = f[which];
        return '<div class="mm-nf-row"><label for="mmNf-' + which + '">' + text + '</label>' +
            '<button type="button" class="mm-nf-step" data-nf-step="-1" data-for="' + which + '" aria-label="Decrease ' + text.toLowerCase() + '">−</button>' +
            '<input id="mmNf-' + which + '" type="number" inputmode="decimal" step="' + step + '" data-for="' + which + '" placeholder="' + (which === 'min' ? lo : hi) + '" value="' + (v === null || v === undefined ? '' : v) + '">' +
            '<button type="button" class="mm-nf-step" data-nf-step="1" data-for="' + which + '" aria-label="Increase ' + text.toLowerCase() + '">+</button></div>';
    };
    var up = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5"/><path d="M6 11l6-6 6 6"/></svg>';
    var down = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M6 13l6 6 6-6"/></svg>';
    var html = '<div class="mm-nf-sort">' + dirBtn('asc', 'Increasing', up) + dirBtn('desc', 'Decreasing', down) + '</div>' +
        stepper('min', 'Min') + stepper('max', 'Max') +
        '<button type="button" class="mm-nf-clear">Reset</button>';

    var el = document.createElement('div');
    el.className = 'mm-pf-menu mm-nf-menu';
    el.setAttribute('role', 'dialog');
    el.setAttribute('aria-label', 'Sort or filter ' + label);
    el.innerHTML = html;
    document.body.appendChild(el);
    var r = btn.getBoundingClientRect(), w = el.offsetWidth, h = el.offsetHeight;
    el.style.left = Math.min(Math.max(8, r.left - 8), window.innerWidth - w - 8) + 'px';
    el.style.top = (r.bottom + 6 + h > window.innerHeight - 8 ? Math.max(8, r.top - h - 6) : r.bottom + 6) + 'px';
    MM_PF_MENU = { el: el, btn: btn, table: table, idx: idx, step: step, lo: lo, hi: hi, serverKey: serverKey };
    var tip = document.getElementById('mmTip');
    if (tip) tip.style.display = 'none';
    btn.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    mmNumMenuCount();
    requestAnimationFrame(function () { el.classList.add('show'); });
    var first = el.querySelector('.mm-nf-dir');
    if (first) first.focus({ preventScroll: true });
}
/* Reads the Min/Max boxes into the table's filters and re-applies. */
function mmNumMenuSync() {
    var m = MM_PF_MENU;
    if (!m || m.idx === undefined) return;
    var read = function (which) {
        var raw = m.el.querySelector('input[data-for="' + which + '"]').value.trim();
        return raw === '' || isNaN(+raw) ? null : +raw;
    };
    m.table.__mmNum = m.table.__mmNum || {};
    m.table.__mmNum[m.idx] = { min: read('min'), max: read('max') };
    mmPlanFilterApply(m.table);
    mmNumMenuCount();
}
function mmNumMenuCount() {
    var m = MM_PF_MENU;
    if (!m || m.idx === undefined) return;
    var p = mmPfParts(m.table);
    var shown = p.rows.filter(function (tr) { return !tr.hidden; }).length;
    var c = m.el.querySelector('.mm-nf-count');
    if (c) c.textContent = shown + ' of ' + p.rows.length + ' shown';
}
function mmPlanMenuClose() {
    if (!MM_PF_MENU) return;
    var m = MM_PF_MENU;
    MM_PF_MENU = null;
    if (m.btn) { m.btn.classList.remove('open'); m.btn.setAttribute('aria-expanded', 'false'); }
    m.el.classList.remove('show');
    setTimeout(function () { m.el.remove(); }, 160);
}
function mmPlanMenuOpen(btn) {
    var table = btn.closest('table');
    var p = mmPfParts(table);
    if (!p) return;
    var current = table.getAttribute('data-plan-filter') || 'all';
    // Campaign table: filtered on the server across every candidate, so
    // per-page counts would mislead — show all options without counts.
    var server = !!table.querySelector('#mmCampaignBody');
    var counts = {};
    p.rows.forEach(function (tr) { var pl = mmPfPlanOf(tr, p.idx); counts[pl] = (counts[pl] || 0) + 1; });
    var countOf = function (value) {
        if (value === 'all') return p.rows.length;
        if (MM_PLAN_GROUPS[value]) return MM_PLAN_GROUPS[value].plans.reduce(function (s, pl) { return s + (counts[pl] || 0); }, 0);
        return counts[value] || 0;
    };
    var item = function (value, label, desc, cls) {
        var n = countOf(value), sel = value === current;
        return '<button type="button" role="option" class="mm-pf-opt' + (sel ? ' sel' : '') + '" aria-selected="' + sel + '" data-value="' + mmEsc(value) + '"' + (!server && n === 0 && value !== 'all' && !sel ? ' disabled' : '') + '>' +
            (cls ? '<span class="mm-pf-dot ' + cls + '"></span>' : '') +
            '<span class="mm-pf-txt"><b>' + mmEsc(label) + '</b><small>' + mmEsc(desc) + '</small></span>' +
            (server ? '' : '<span class="mm-pf-n">' + n + '</span>') + '<span class="mm-pf-check">' + (sel ? MM_PF_CHECK : '') + '</span></button>';
    };
    var html = item('all', MM_PLAN_GROUPS.all.label, MM_PLAN_GROUPS.all.desc) +
        item('paid', MM_PLAN_GROUPS.paid.label, MM_PLAN_GROUPS.paid.desc) +
        item('unpaid', MM_PLAN_GROUPS.unpaid.label, MM_PLAN_GROUPS.unpaid.desc) +
        '<div class="mm-pf-sep" role="presentation">By plan' + (server ? ' · all students' : '') + '</div>' +
        // Every plan is always listed (0 = none in this list), so none ever "goes missing".
        MM_PLAN_ORDER.filter(function (pl) { return pl !== 'Other' || counts[pl] || current === pl; }).map(function (pl) {
            return item(pl, pl, MM_PLAN_DESC[pl] || '', 'mm-plan ' + pl.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
        }).join('');

    var el = document.createElement('div');
    el.className = 'mm-pf-menu';
    el.setAttribute('role', 'listbox');
    el.setAttribute('aria-label', 'Filter by plan');
    el.innerHTML = html;
    document.body.appendChild(el);
    var r = btn.getBoundingClientRect(), w = el.offsetWidth, h = el.offsetHeight;
    var left = Math.min(Math.max(8, r.left - 8), window.innerWidth - w - 8);
    var top = r.bottom + 6 + h > window.innerHeight - 8 ? Math.max(8, r.top - h - 6) : r.bottom + 6;
    el.style.left = left + 'px';
    el.style.top = top + 'px';
    MM_PF_MENU = { el: el, btn: btn, table: table };
    var tip = document.getElementById('mmTip');
    if (tip) tip.style.display = 'none'; // the header's hover tooltip would sit on top of the menu
    btn.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    requestAnimationFrame(function () { el.classList.add('show'); });
    var first = el.querySelector('.mm-pf-opt.sel') || el.querySelector('.mm-pf-opt');
    if (first) first.focus({ preventScroll: true });
}
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.mm-pf-btn');
    if (btn) {
        e.stopPropagation();
        var reopen = !(MM_PF_MENU && MM_PF_MENU.btn === btn);
        mmPlanMenuClose();
        if (reopen) { if (btn.classList.contains('mm-nf-btn')) mmNumMenuOpen(btn); else mmPlanMenuOpen(btn); }
        return;
    }
    // Number menu: sort options, − / + steppers, Clear.
    if (MM_PF_MENU && MM_PF_MENU.idx !== undefined && e.target.closest('.mm-nf-menu')) {
        var m = MM_PF_MENU;
        var so = e.target.closest('[data-kind="sort"]');
        if (so) {
            // Toggle: clicking the active direction turns sorting off again.
            var dir = so.classList.contains('on') ? 'none' : so.getAttribute('data-dir');
            if (m.serverKey) {
                MM_CAMPAIGN_SORT.key = dir === 'none' ? null : m.serverKey;
                MM_CAMPAIGN_SORT.dir = dir === 'none' ? 'asc' : dir;
                mmCampaignSortIndicators();
                mmCampaignGo(1);
            } else {
                m.table.__mmSort = dir === 'none' ? null : { idx: m.idx, dir: dir };
                mmPlanFilterApply(m.table);
            }
            m.el.querySelectorAll('.mm-nf-dir').forEach(function (b) {
                var on = b.getAttribute('data-dir') === dir;
                b.classList.toggle('on', on);
                b.setAttribute('aria-pressed', on);
            });
            return;
        }
        var st = e.target.closest('[data-nf-step]');
        if (st) {
            var input = m.el.querySelector('input[data-for="' + st.getAttribute('data-for') + '"]');
            var cur = input.value.trim() === '' ? (st.getAttribute('data-for') === 'min' ? m.lo : m.hi) : +input.value;
            input.value = Math.round((cur + (+st.getAttribute('data-nf-step')) * m.step) * 100) / 100;
            mmNumMenuSync();
            return;
        }
        if (e.target.closest('.mm-nf-clear')) {
            if (m.table.__mmNum) delete m.table.__mmNum[m.idx];
            if (m.table.__mmSort && m.table.__mmSort.idx === m.idx) m.table.__mmSort = null;
            if (m.serverKey && MM_CAMPAIGN_SORT.key === m.serverKey) {
                MM_CAMPAIGN_SORT.key = null; MM_CAMPAIGN_SORT.dir = 'asc';
                mmCampaignSortIndicators();
                mmCampaignGo(1);
            }
            mmPlanFilterApply(m.table);
            mmPlanMenuClose();
            return;
        }
        return;
    }
    var opt = e.target.closest('.mm-pf-opt');
    if (opt && MM_PF_MENU) {
        var table = MM_PF_MENU.table, value = opt.getAttribute('data-value');
        table.setAttribute('data-plan-filter', value);
        mmPlanFilterApply(table);
        if (table.querySelector('#mmCampaignBody')) {
            // Campaign: re-query the server so the filter covers every page.
            MM_CAMPAIGN_PLAN = value === 'all' ? null : value;
            mmCampaignGo(1);
        }
        if (table.closest('#mmListModalBody') && MM_LIST_CURRENT) MM_LIST_CURRENT.planFilter = value;
        mmPlanMenuClose();
        return;
    }
    if (MM_PF_MENU && !e.target.closest('.mm-pf-menu')) mmPlanMenuClose();
}, true);
document.addEventListener('keydown', function (e) {
    if (!MM_PF_MENU) return;
    if (e.key === 'Escape') { e.stopPropagation(); var b = MM_PF_MENU.btn; mmPlanMenuClose(); if (b) b.focus(); return; }
    if (document.activeElement && document.activeElement.matches('.mm-nf-menu input')) {
        if (e.key === 'Enter') { e.preventDefault(); mmNumMenuSync(); mmPlanMenuClose(); }
        return; // arrow keys step the number natively
    }
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        var opts = Array.prototype.filter.call(MM_PF_MENU.el.querySelectorAll('.mm-pf-opt'), function (o) { return !o.disabled; });
        var i = opts.indexOf(document.activeElement);
        var next = opts[(i + (e.key === 'ArrowDown' ? 1 : -1) + opts.length) % opts.length];
        if (next) next.focus();
    }
}, true);
document.addEventListener('input', function (e) {
    if (e.target.matches && e.target.matches('.mm-nf-menu input[data-for]')) mmNumMenuSync();
});
window.addEventListener('resize', mmPlanMenuClose);
mmPlanFilterInit(document);
// Campaign opened with ?plan=… (e.g. after a reload): show that filter as active.
(function () {
    var body = document.getElementById('mmCampaignBody');
    if (body && MM_CAMPAIGN_PLAN) {
        body.closest('table').setAttribute('data-plan-filter', MM_CAMPAIGN_PLAN);
        mmPlanFilterApply(body.closest('table'));
    }
})();
/* On scroll the open menu follows its arrow (filtering can shrink the table
   and nudge the scroll position); it only closes once the arrow is out of view. */
document.addEventListener('scroll', function (e) {
    if (!MM_PF_MENU || (e.target.closest && e.target.closest('.mm-pf-menu'))) return;
    var m = MM_PF_MENU, r = m.btn.getBoundingClientRect();
    if (!m.btn.isConnected || r.bottom < 0 || r.top > window.innerHeight || (r.width === 0 && r.height === 0)) { mmPlanMenuClose(); return; }
    var h = m.el.offsetHeight, w = m.el.offsetWidth;
    m.el.style.left = Math.min(Math.max(8, r.left - 8), window.innerWidth - w - 8) + 'px';
    m.el.style.top = (r.bottom + 6 + h > window.innerHeight - 8 ? Math.max(8, r.top - h - 6) : r.bottom + 6) + 'px';
}, true);

/* Plan pill (Free trial / Paid / Coaching / Coaching + Paid / No purchase) — see MockMasterDataService::planFor(). */
function mmPlanCell(r) {
    var p = r && r.plan ? String(r.plan) : '';
    if (!p) return '<td>—</td>';
    var cls = p.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    return '<td><span class="mm-plan ' + cls + '">' + mmEsc(p) + '</span></td>';
}
var MM_LIST_COL_LABELS = {
    name: 'Student', plan: 'Plan', sub: 'Package / interest', value: 'Package value', stage: 'Stage',
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
        return '<th' + (k === 'plan' ? ' data-mm-plan-filter' : '') + '>' + label + '</th>';
    }).join('') + '<th>Action</th></tr>';
    var ctx = MM_CMP_DATASET_CTX[entry.dataset] || 'marketing';
    var body = rows.map(function (r, i) {
        return '<tr><td>' + (i + 1) + '</td>' + cols.map(function (k) {
            var v = r[k];
            var text = (v === null || v === undefined || v === '' ? '—' : escapeHtml(v));
            if (k === 'plan') return mmPlanCell(r);
            return k === 'name' ? '<td ' + mmStuAttrs(r) + '><span class="bh-acct-n">' + text + '</span></td>' : '<td>' + text + '</td>';
        }).join('') + '<td>' + mmCmpBtn(ctx, r) + '</td></tr>';
    }).join('');

    var footer = entry.hasMore
        ? '<button type="button" class="qk" id="mmListMoreBtn" onclick="loadMoreMmList(\'' + entry._id + '\')">Load more →</button>'
        : '<p style="color:var(--g3);font-size:11.5px;margin-top:10px">That\'s everyone — no more results.</p>';

    document.getElementById('mmListModalBody').innerHTML = '<table>' + head + body + '</table>' + footer;
    // Keep the Plan filter through re-renders (e.g. Load more).
    MM_LIST_CURRENT = entry;
    mmPlanFilterInit(document.getElementById('mmListModalBody'), entry.planFilter);
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

/* ── Student profile popup ──
   Any name cell carrying data-mm-stu opens the student's details. Rows
   give a student id (sid) where available, otherwise an email. */
var MM_STUDENT_URL = '{{ route('client.mock-master-helper.student') }}';
var MM_POP_ROW = null; // student shown in the popup, for "View full profile"
function mmStuAttrs(r) {
    var sid = r && r.sid ? String(r.sid) : '';
    var email = r && r.email ? String(r.email) : '';
    if (!sid && !email) return '';
    return 'data-mm-stu data-sid="' + mmEsc(sid) + '" data-email="' + mmEsc(email) + '"';
}
function mmStuRow(label, value, copy) {
    var v = value ? escapeHtml(value) : '<span class="mm-stu-none">Not on file</span>';
    var btn = (value && copy) ? '<button type="button" class="mm-stu-copy" data-copy="' + escapeHtml(value) + '">Copy</button>' : '';
    return '<dt>' + label + '</dt><dd><span>' + v + '</span>' + btn + '</dd>';
}
function mmProfileOpen(sid, email) {
    if (!sid && !email) return;
    var overlay = document.getElementById('mmStuOverlay');
    document.getElementById('mmStuName').textContent = 'Loading…';
    document.getElementById('mmStuCourse').textContent = '';
    document.getElementById('mmStuPic').innerHTML = '';
    document.getElementById('mmStuBody').innerHTML = '';
    document.getElementById('mmStuMore').hidden = true;
    if (MMX_PROF && MMX_PROF.src === 'pop') mmxProfileClose();
    MM_POP_ROW = { sid: sid || '', email: email || '', name: '' };
    overlay.classList.add('show');

    var qs = sid ? 'sid=' + encodeURIComponent(sid) : 'email=' + encodeURIComponent(email);
    fetch(MM_STUDENT_URL + '?' + qs, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw r.status; return r.json(); })
        .then(function (p) {
            var full = [p.first_name, p.last_name].filter(Boolean).join(' ') || 'Unnamed student';
            document.getElementById('mmStuName').textContent = full;
            document.getElementById('mmStuCourse').textContent = p.course_type || '';
            var initial = escapeHtml((p.first_name || full).trim().charAt(0).toUpperCase() || '?');
            var pic = document.getElementById('mmStuPic');
            pic.innerHTML = '<span class="mm-stu-ini">' + initial + '</span>';
            if (p.profile_picture) {
                var img = new Image();
                img.alt = full;
                img.referrerPolicy = 'no-referrer';
                img.onload = function () { pic.innerHTML = ''; pic.appendChild(img); };
                img.src = p.profile_picture;
            }
            document.getElementById('mmStuBody').innerHTML =
                mmStuRow('First name', p.first_name) +
                mmStuRow('Last name', p.last_name) +
                mmStuRow('Course type', p.course_type) +
                mmStuRow('Mobile number', p.phone, true) +
                mmStuRow('Email', p.email, true);
            MM_POP_ROW.name = full;
            if (p.email) MM_POP_ROW.email = p.email;
            document.getElementById('mmStuMore').hidden = false;
        })
        .catch(function (status) {
            document.getElementById('mmStuName').textContent = status === 404 ? 'Student not found' : 'Could not load details';
            document.getElementById('mmStuBody').innerHTML = '<p class="mm-stu-err">' +
                (status === 404 ? 'This student may have been removed from Mock Master.' : 'Please try again in a moment.') + '</p>';
        });
}
function mmProfileClose() {
    document.getElementById('mmStuOverlay').classList.remove('show');
    if (MMX_PROF && MMX_PROF.src === 'pop') mmxProfileClose(); // side profile goes with the popup
}
document.addEventListener('click', function (e) {
    var copy = e.target.closest('.mm-stu-copy');
    if (copy) {
        var text = copy.getAttribute('data-copy');
        var done = function () { copy.textContent = 'Copied'; setTimeout(function () { copy.textContent = 'Copy'; }, 1500); };
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(text).then(done, function () {});
        return;
    }
    var cell = e.target.closest('[data-mm-stu]');
    if (!cell || !e.target.closest('.bh-acct-n, .bh-av')) return;
    mmProfileOpen(cell.getAttribute('data-sid'), cell.getAttribute('data-email'));
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && document.getElementById('mmStuOverlay').classList.contains('show')) mmProfileClose();
});

/* ── Action column: "View" → message composer ──
   One composer for every Sales, Marketing and Retention list. Each list
   passes its context (which template set fits) and its row; the student's
   real email and mobile are loaded from the profile endpoint. Separate from
   the Campaign table's own composer (mmStudentOpen), which is unchanged. */
var MM_CMP_TEMPLATES = {
    prospect: [
        { key: 'next-mock', label: 'Take your next mock test', hint: 'Free-trial student, early stage', subject: 'Your next PTE mock test, {first}',
          body: 'Hi {first}, thanks for starting your {course} preparation with Mock Master. Taking a full scored mock test next will show exactly where to focus. Want me to send you the link?' },
        { key: 'call', label: 'Book a quick call', hint: 'Plan their preparation together', subject: 'Quick call about your {course} preparation?',
          body: "Hi {first}, I'd love to help you plan your {course} preparation. When's a good time for a quick 5-minute call this week?" },
        { key: 'upgrade', label: 'Upgrade from free', hint: 'Show what paid plans unlock', subject: 'Unlock full mock tests, {first}',
          body: "Hi {first}, you're on the free plan. Our paid plans unlock full mock tests with score reports, so you can track your progress before exam day. Shall I send you the options?" }
    ],
    convert: [
        { key: 'plan', label: 'Recommend a plan', hint: 'Active free-trial student', subject: 'The right plan for you, {first}',
          body: "Hi {first}, you've been practising well on Mock Master. A paid plan gives you full mock tests and score reports to reach your target. Shall I send you the best option for you?" },
        { key: 'scored-mock', label: 'Take a full scored mock', hint: 'No scored test yet', subject: 'Get your real score, {first}',
          body: 'Hi {first}, a full scored mock test will show your real score and which section to focus on. Want me to send you the link?' },
        { key: 'checkout', label: 'Help finish checkout', hint: 'Started paying but stopped', subject: 'Need a hand finishing sign-up, {first}?',
          body: 'Hi {first}, I noticed you started signing up for a plan. Can I help you finish? Reply here and I\'ll send you the link.' }
    ],
    abandoned: [
        { key: 'finish', label: 'Finish your checkout', hint: 'Payment started, not completed', subject: 'Your {package} is almost ready, {first}',
          body: "Hi {first}, you started checking out {package} ({amount}) but didn't finish. It's still available. Reply and I'll send you the payment link." },
        { key: 'help', label: 'Offer payment help', hint: 'Payment may have failed', subject: 'Did your payment go through, {first}?',
          body: "Hi {first}, it looks like your payment for {package} didn't go through. Did something go wrong? I'm happy to help you complete it." },
        { key: 'question', label: 'Any questions?', hint: 'Still deciding', subject: 'Any questions about {package}?',
          body: "Hi {first}, before you decide on {package}, do you have any questions I can answer? I'm here to help." }
    ],
    renewals: [
        { key: 'renew', label: 'Renewal reminder', hint: 'Plan expiring soon', subject: 'Your {package} expires {expires}',
          body: 'Hi {first}, your {package} expires {expires}. Renew now to keep your access and progress without a break. Reply RENEW and I\'ll send your link.' },
        { key: 'longer', label: 'Upgrade to a longer plan', hint: 'Still practising actively', subject: 'Stay covered until exam day, {first}',
          body: "Hi {first}, you've been practising consistently. A longer plan keeps you covered until exam day and costs less per month. Want the details?" },
        { key: 'checkin', label: 'Check-in', hint: 'Activity has slowed', subject: 'How is your preparation going, {first}?',
          body: "Hi {first}, how's your preparation going? Your {package} expires {expires}. Is your exam date still on track?" }
    ],
    winback: [
        { key: 'comeback', label: 'Come back offer', hint: 'Lapsed, still logging in', subject: "We'd love to have you back, {first}",
          body: "Hi {first}, your {package} ended {expired}, but we noticed you're still logging in. Renew today and pick up right where you left off." },
        { key: 'progress', label: 'Keep your progress', hint: 'Practice history saved', subject: 'Your practice history is waiting, {first}',
          body: 'Hi {first}, your practice history is still saved. Renew your {package} to keep building on it before your exam.' },
        { key: 'ask', label: 'Ask what they need', hint: 'Find out why they left', subject: 'Can we help, {first}?',
          body: 'Hi {first}, I saw your plan ended recently. Is there anything we could do better, or a plan that would suit you more?' }
    ],
    retention: [
        { key: 'renew', label: 'Renewal reminder', hint: 'Package expiring', subject: 'Your {package} is expiring soon, {first}',
          body: 'Hi {first}, your {package} is expiring soon. Renew now to keep your access and progress without a break. Reply RENEW and I\'ll send your link.' },
        { key: 'reengage', label: 'We miss you', hint: 'Not active recently', subject: 'We miss you, {first}',
          body: 'Hi {first}, we noticed you haven\'t practised in a while. Log in today and take your next mock test to stay on track.' },
        { key: 'call', label: 'Offer a quick call', hint: 'Help plan next steps', subject: 'Can I help with your preparation, {first}?',
          body: 'Hi {first}, would a quick call help you plan your next steps before your exam? Reply with a time that suits you.' }
    ],
    marketing: [
        { key: 'checkin', label: 'Friendly check-in', hint: 'Any student', subject: 'How is your preparation going, {first}?',
          body: "Hi {first}, just checking in from Mock Master. How's your preparation going? Reply if there's anything we can help with." },
        { key: 'progress', label: 'Celebrate progress', hint: 'Active or high-scoring', subject: 'Great work on your mock tests, {first}',
          body: 'Hi {first}, great work on your recent mock tests. Keep it up: regular full tests are the fastest way to your target score.' },
        { key: 'offer', label: 'Plan offer', hint: 'Keep momentum going', subject: 'Keep your momentum going, {first}',
          body: 'Hi {first}, want to keep your momentum going? Our plans include full mock tests with score reports. Shall I send you the options?' },
        { key: 'referral', label: 'Feedback and referral', hint: 'Happy students', subject: 'How was your experience, {first}?',
          body: "Hi {first}, we'd love to hear how your preparation with Mock Master is going. And if friends are preparing for the PTE, feel free to share us with them!" }
    ]
};
/* Which template set each popup dataset uses. */
var MM_CMP_DATASET_CTX = {
    slProspects: 'prospect', slClose: 'convert', slAbandoned: 'abandoned',
    chRenewals: 'renewals', chWinBack: 'winback', chAtRisk: 'retention', chWatchlist: 'retention',
    mkTopScorers: 'marketing', mkNewStudents: 'marketing'
};
var MM_CMP = null;

/* Action-column button for one row; ctx picks the template set. */
function mmCmpBtn(ctx, row) {
    return '<button type="button" class="mm-act-btn" data-mm-compose="' + mmEsc(ctx) + '" data-row="' + mmEsc(JSON.stringify(row || {})) + '">View</button>';
}
function mmCmpVars(row, contact) {
    var name = String(row.name || '').trim();
    var sub = String(row.sub || '');
    var course = /^Interested in /.test(sub) ? sub.replace(/^Interested in /, '') : '';
    return {
        first: (contact.first_name || name.split(' ')[0] || 'there'),
        name: name || 'there',
        course: contact.course_type || course || 'PTE',
        package: row.package || row.product || (course ? '' : sub) || 'your plan',
        amount: row.amount || row.value || '',
        expires: row.expires || 'soon',
        expired: row.expired || 'recently'
    };
}
function mmCmpFill(text, v) {
    return String(text).replace(/\{(\w+)\}/g, function (m, k) { return v[k] !== undefined && v[k] !== '' ? v[k] : m; })
        .replace(/ \(\{amount\}\)/g, '').replace(/\{amount\}/g, '');
}
function mmComposeOpen(ctx, row) {
    // ctx may be a template set (e.g. "retention") or a list name (e.g. "chAtRisk").
    var templates = MM_CMP_TEMPLATES[ctx] || MM_CMP_TEMPLATES[MM_CMP_DATASET_CTX[ctx]] || MM_CMP_TEMPLATES.marketing;
    MM_CMP = { ctx: ctx, row: row, templates: templates, contact: { email: row.email || null, phone: row.phone || null } };

    var overlay = document.getElementById('mmCmpOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'mmCmpOverlay';
        overlay.className = 'mm-cmp-overlay';
        overlay.onclick = function (e) { if (e.target === overlay) mmComposeClose(); };
        overlay.innerHTML =
            '<div class="mm-cmp" role="dialog" aria-modal="true" aria-labelledby="mmCmpTitle">' +
                '<div class="mm-cmp-hd"><div><div class="mm-cmp-title" id="mmCmpTitle"></div><div class="mm-cmp-contact" id="mmCmpContact"></div></div>' +
                '<button type="button" class="mm-cmp-x" onclick="mmComposeClose()" aria-label="Close">✕</button></div>' +
                '<div class="mm-cmp-body">' +
                    '<div><div class="mm-cmp-lbl">Choose a message</div><div class="mm-cmp-tpls" id="mmCmpTpls"></div></div>' +
                    '<div class="mm-cmp-edit" id="mmCmpEdit" hidden>' +
                        '<div class="mm-cmp-lbl">Edit and send</div>' +
                        '<label class="mm-cmp-flbl" for="mmCmpSubject">Email subject <span>(email only)</span></label>' +
                        '<input id="mmCmpSubject" type="text" class="mm-cmp-in">' +
                        '<label class="mm-cmp-flbl" for="mmCmpMsg">Message <span>(used for Email and WhatsApp)</span></label>' +
                        '<textarea id="mmCmpMsg" rows="7" class="mm-cmp-in"></textarea>' +
                        '<div class="mm-cmp-send"><button type="button" id="mmCmpEmail" onclick="mmComposeSend(\'email\')">Email</button>' +
                        '<button type="button" id="mmCmpWa" onclick="mmComposeSend(\'wa\')">WhatsApp</button></div>' +
                    '</div>' +
                '</div>' +
                '<p class="mm-cmp-hint" id="mmCmpHint">Pick a message to enable sending.</p>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    document.getElementById('mmCmpTitle').innerHTML = mmEsc(row.name || 'Student') + ' &nbsp;' + mmxProfBtn('cmp');
    document.getElementById('mmCmpContact').textContent = 'Loading contact details…';
    document.getElementById('mmCmpEdit').hidden = true;
    document.getElementById('mmCmpTpls').innerHTML = templates.map(function (t) {
        return '<label class="mm-cmp-tpl"><input type="radio" name="mmCmpTpl" value="' + mmEsc(t.key) + '" onchange="mmComposePick(\'' + mmEsc(t.key) + '\')">' +
            '<b>' + mmEsc(t.label) + '</b><span>' + mmEsc(t.hint) + '</span></label>';
    }).join('');
    mmComposeButtons(false);
    overlay.classList.add('show');

    // Load the real email and mobile number (rows don't always carry a phone).
    var qs = row.sid ? 'sid=' + encodeURIComponent(row.sid) : (row.email ? 'email=' + encodeURIComponent(row.email) : '');
    var showContact = function () {
        var c = MM_CMP.contact, parts = [];
        if (c.email) parts.push(c.email);
        if (c.phone) parts.push(c.phone);
        document.getElementById('mmCmpContact').textContent = parts.length ? parts.join(' · ') : 'No email or mobile number on file';
        var picked = document.querySelector('input[name="mmCmpTpl"]:checked');
        mmComposeButtons(!!picked);
    };
    if (!qs) { showContact(); return; }
    var forRow = row;
    fetch(MM_STUDENT_URL + '?' + qs, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (p) {
            if (!MM_CMP || MM_CMP.row !== forRow) return; // composer was reopened for someone else
            if (p) MM_CMP.contact = { email: p.email || MM_CMP.contact.email, phone: p.phone || MM_CMP.contact.phone, first_name: p.first_name, course_type: p.course_type };
            showContact();
        })
        .catch(function () { if (MM_CMP && MM_CMP.row === forRow) showContact(); });
}
function mmComposePick(key) {
    if (!MM_CMP) return;
    var t = MM_CMP.templates.find(function (x) { return x.key === key; });
    if (!t) return;
    var v = mmCmpVars(MM_CMP.row, MM_CMP.contact);
    document.getElementById('mmCmpEdit').hidden = false;
    document.getElementById('mmCmpSubject').value = mmCmpFill(t.subject, v);
    document.getElementById('mmCmpMsg').value = mmCmpFill(t.body, v);
    mmComposeButtons(true);
}
function mmComposeButtons(on) {
    var c = (MM_CMP && MM_CMP.contact) || {};
    var canEmail = on && !!c.email;
    var canWa = on && !!String(c.phone || '').replace(/\D/g, '');
    var email = document.getElementById('mmCmpEmail'), wa = document.getElementById('mmCmpWa');
    email.disabled = !canEmail; email.classList.toggle('on', canEmail);
    wa.disabled = !canWa; wa.classList.toggle('on', canWa);
    document.getElementById('mmCmpHint').textContent = !on ? 'Pick a message to enable sending.'
        : (canEmail || canWa ? 'Edit the message if you like, then send it.' : 'This student has no email or mobile number on file.');
}
function mmComposeSend(channel) {
    if (!MM_CMP) return;
    var c = MM_CMP.contact, body = document.getElementById('mmCmpMsg').value;
    if (channel === 'email' && c.email) {
        window.location.href = 'mailto:' + encodeURIComponent(c.email) + '?subject=' + encodeURIComponent(document.getElementById('mmCmpSubject').value) + '&body=' + encodeURIComponent(body);
    } else if (channel === 'wa') {
        var digits = String(c.phone || '').replace(/\D/g, '');
        if (digits) window.open('https://wa.me/' + digits + '?text=' + encodeURIComponent(body), '_blank', 'noopener');
    }
}
function mmComposeClose() {
    var o = document.getElementById('mmCmpOverlay');
    if (o) o.classList.remove('show');
    mmxProfileClose(); // side Profile panel goes with the window
}
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-mm-compose]');
    if (!btn) return;
    var row = {};
    try { row = JSON.parse(btn.getAttribute('data-row') || '{}'); } catch (err) {}
    mmComposeOpen(btn.getAttribute('data-mm-compose'), row);
});
document.addEventListener('keydown', function (e) {
    var o = document.getElementById('mmCmpOverlay');
    if (e.key === 'Escape' && o && o.classList.contains('show') && !document.getElementById('mmStuOverlay').classList.contains('show')) mmComposeClose();
});

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

    // Marketing detail lists of students get an Action column (message composer).
    var hasStudents = data.columns.some(function (c) { return c.key === 'name'; }) && rows.some(function (r) { return r.sid || r.email; });
    var head = '<tr><th>#</th>' + data.columns.map(function (c) { return '<th>' + escapeHtml(c.label) + '</th>'; }).join('') + (hasStudents ? '<th>Action</th>' : '') + '</tr>';
    var body = slice.map(function (r, i) {
        return '<tr><td>' + (start + i + 1) + '</td>' + data.columns.map(function (c) {
            var text = escapeHtml(r[c.key] === null || r[c.key] === undefined || r[c.key] === '' ? '—' : String(r[c.key]));
            return c.key === 'name' ? '<td ' + mmStuAttrs(r) + '><span class="bh-acct-n">' + text + '</span></td>' : '<td>' + text + '</td>';
        }).join('') + (hasStudents ? '<td>' + ((r.sid || r.email) ? mmCmpBtn('marketing', r) : '') + '</td>' : '') + '</tr>';
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

/* Pager: "Showing 11–20 of 1,336 students · Page 2 of 134 · 132 more pages", page numbers,
   rows per page and Go to page. */
function mmCampaignPager(data) {
    var info = document.getElementById('mmCampaignPageInfo');
    var prev = document.getElementById('mmCampaignPrev');
    var next = document.getElementById('mmCampaignNext');
    var per = data.per_page || MM_CAMPAIGN_PER, total = Number(data.total) || 0;
    MM_CAMPAIGN_PAGE = data.page;
    MM_CAMPAIGN_PER = per;
    var from = total ? (data.page - 1) * per + 1 : 0, to = Math.min(total, data.page * per), left = data.last_page - data.page;
    if (info) info.innerHTML = 'Showing <b>' + from.toLocaleString() + '–' + to.toLocaleString() + '</b> of <b>' + total.toLocaleString() + '</b> students' +
        ' · Page <b>' + data.page + '</b> of <b>' + Number(data.last_page).toLocaleString() + '</b>' +
        (left > 0 ? ' · ' + left.toLocaleString() + ' more page' + (left === 1 ? '' : 's') + ' (' + (total - to).toLocaleString() + ' students) to go' : ' · last page');
    if (prev) { prev.disabled = data.page <= 1; prev.onclick = function () { mmCampaignGo(data.page - 1); }; }
    if (next) { next.disabled = data.page >= data.last_page; next.onclick = function () { mmCampaignGo(data.page + 1); }; }
    var nums = document.getElementById('mmCampaignPages');
    if (nums) {
        // First, last, and two either side of the current page, with … for the gaps.
        var pages = [], last = data.last_page, cur = data.page;
        for (var n = 1; n <= last; n++) {
            if (n === 1 || n === last || Math.abs(n - cur) <= 2) pages.push(n);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }
        nums.innerHTML = pages.map(function (n) {
            return n === '…' ? '<span class="mmx-pg-gap">…</span>'
                : '<button type="button"' + (n === cur ? ' class="on" aria-current="page"' : '') + ' onclick="mmCampaignGo(' + n + ')">' + n + '</button>';
        }).join('');
    }
    var jump = document.getElementById('mmCampaignJumpIn');
    if (jump) { jump.max = data.last_page; jump.placeholder = data.page; jump.value = ''; }
    var sel = document.getElementById('mmCampaignPer');
    if (sel) {
        if (!sel.querySelector('option[value="' + per + '"]')) { // a fitted size while the table is expanded
            var extra = sel.querySelector('option[data-fit]') || sel.appendChild(document.createElement('option'));
            extra.setAttribute('data-fit', '1');
            extra.value = per;
            extra.textContent = per + ' (fits screen)';
        }
        sel.value = String(per);
    }
}
var MM_CAMPAIGN_PAGE = {{ (int) $mkPaged['page'] }};
var MM_CAMPAIGN_PER = {{ (int) ($mkPaged['per_page'] ?? 10) }};
var MM_CAMPAIGN_LAST = {{ (int) $mkPaged['last_page'] }};
if (document.readyState !== 'loading') mmCampaignPagerInit(); else document.addEventListener('DOMContentLoaded', mmCampaignPagerInit);
function mmCampaignPagerInit() {
    mmCampaignPager({ page: MM_CAMPAIGN_PAGE, last_page: MM_CAMPAIGN_LAST, total: {{ (int) $mkPaged['total'] }}, per_page: MM_CAMPAIGN_PER });
}

function mmCampaignJump(e) {
    e.preventDefault();
    var input = document.getElementById('mmCampaignJumpIn');
    var n = parseInt(input.value, 10);
    var last = parseInt(input.max, 10) || 1;
    if (!n) return false;
    mmCampaignGo(Math.min(Math.max(1, n), last));
    return false;
}
/* Changing rows per page keeps the first student on screen in view. */
function mmCampaignPerSet(per) {
    per = parseInt(per, 10) || 10;
    var firstIndex = (MM_CAMPAIGN_PAGE - 1) * MM_CAMPAIGN_PER;
    MM_CAMPAIGN_PER = per;
    mmCampaignGo(Math.floor(firstIndex / per) + 1);
}

function mmCampaignRender(data) {
    var body = document.getElementById('mmCampaignBody');
    if (!body) return;
    var students = data.students;
    mmCampaignPager(data);
    if (data.segments) { MM_SEG_COUNTS = data.segments; mmSegRender(); } // quick segment counts
    if (MM_VIEW === 'board') mmBoardLoad();
    if (!students.length) {
        body.innerHTML = '<tr><td colspan="12" style="color:var(--g3);padding:20px">No renewal-ready students match these filters.</td></tr>';
        return;
    }
    MM_CAMPAIGN_ROWS = students;
    body.innerHTML = students.map(function (s, i) {
        var approach = s.approach === 'Offer-led'
            ? '<span class="bh-pill good">Offer-led</span>'
            : '<span class="bh-pill warn">Proof-led</span>';
        return '<tr>' +
            '<td class="acctn" ' + mmStuAttrs(s) + '><div class="bh-acct"><span class="bh-av" style="background:' + mmEsc(s.color) + '">' + mmEsc(s.initial) + '</span>' +
                '<div><div class="bh-acct-n">' + mmEsc(s.name) + '</div><div class="bh-acct-c">(' + mmEsc(s.sub) + ')</div></div></div></td>' + mmPlanCell(s) +
            '<td>' + mmEsc(s.value) + '</td>' +
            '<td>' + mmEsc(s.paymentDate) + '</td>' +
            '<td>' + mmxExpCell(s) + '</td>' +
            '<td><span class="bh-pill ' + mmEsc(s.stageKind) + '">' + mmEsc(s.stage) + '</span></td>' +
            '<td>' + mmxRing(s.readiness) + '</td>' +
            '<td>' + mmEsc(s.trust) + '</td>' +
            '<td>' + approach + '</td>' +
            '<td>' + mmxLastCell(s) + '</td>' +
            '<td>' + mmxNbaCell(s) + '</td>' +
            '<td><button type="button" onclick="mmStudentOpen(' + i + ')" style="padding:5px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">View</button></td>' +
            '</tr>';
    }).join('');
}

var MM_CAMPAIGN_REQ = 0; // only the newest request may draw the table (a slower, older one is ignored)
function mmCampaignLoad(query) {
    var body = document.getElementById('mmCampaignBody');
    var req = ++MM_CAMPAIGN_REQ;
    if (body) body.innerHTML = '<tr><td colspan="12" style="color:var(--g3);padding:20px">Loading…</td></tr>';
    fetch(MM_CAMPAIGN_URL + (query ? '?' + query : ''), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
    .then(function (data) { if (req === MM_CAMPAIGN_REQ) mmCampaignRender(data); })
    .catch(function () {
        if (req === MM_CAMPAIGN_REQ && body) body.innerHTML = '<tr><td colspan="12" style="color:#b91c1c;padding:20px">Couldn\'t load students — please try again.</td></tr>';
    });
}

/* ── Dashboard additions (Nov 2026) ─────────────────────────────────────────
   1. Overview cards per agent (live from mock-master-helper/overview, collapsible)
   2. Quick segment cards over the Campaign table (filter table, Board and Send to all)
   3. Campaign cells: Expires, Readiness ring, Last active bar, Next best action
      (same markup as $mmExpCell / $mmRing / $mmLastCell / $mmNbaCell in the Blade @php block)
   4. Board view of every Campaign student matching the filters, grouped by Stage or Next best action */
var MM_NBA_ICONS = { 'Win back': '🔁', 'Renew now': '⏰', 'Renewal reminder': '🔔', 'Re-engage': '💤', 'Book a mock test': '🎯', 'Lead with proof': '🏅', 'Lead with offer': '🏷️' };

function mmxExpCell(s) {
    var n = s.expiresIn;
    if (n === null || n === undefined) return '<span class="mmx-muted">—</span>';
    if (s.stage === 'Expired' || n < 0) return '<span class="bh-pill bad">Expired</span>' + (n < 0 && n > -366 ? '<div class="mmx-sub">' + (-n) + 'd ago</div>' : '');
    if (n === 0) return '<span class="bh-pill warn">Today</span>';
    if (n <= 7) return '<span class="bh-pill warn">in ' + n + 'd</span>';
    if (n <= 365) return '<span class="mmx-exp">in ' + n + 'd</span>';
    return '<span class="mmx-muted">' + mmEsc(s.expireDate || '—') + '</span>';
}
function mmxRing(v) {
    v = Math.max(0, Math.min(100, parseInt(v, 10) || 0));
    var c = v >= 70 ? '#16a34a' : (v >= 50 ? '#d97706' : '#dc2626'), len = 69.12;
    return '<span class="mmx-ring"><svg width="26" height="26" viewBox="0 0 26 26" aria-hidden="true"><circle cx="13" cy="13" r="11" fill="none" stroke="#eceef3" stroke-width="3"/>' +
        (v > 0 ? '<circle cx="13" cy="13" r="11" fill="none" stroke="' + c + '" stroke-width="3" stroke-linecap="round" stroke-dasharray="' + (Math.round(len * v) / 100) + ' ' + len + '" transform="rotate(-90 13 13)"/>' : '') +
        '</svg><b>' + v + '</b></span>';
}
function mmxLastCell(s) {
    var d = s.lastDays;
    var none = d === null || d === undefined;
    var w = none ? 0 : Math.max(6, 100 - d * 3.3);
    var c = none ? '#d1d5db' : (d < 7 ? '#16a34a' : (d < 14 ? '#d97706' : '#dc2626'));
    return '<div class="mmx-last">' + mmEsc(s.lastActive || '—') + '<i class="mmx-bar"><b style="width:' + Math.round(w) + '%;background:' + c + '"></b></i></div>';
}
function mmxNbaCell(s) {
    if (!s.nextAction) return '<span class="mmx-muted">—</span>';
    return '<span class="bh-pill ' + mmEsc(s.nextKind || 'info') + ' mmx-nba">' + (MM_NBA_ICONS[s.nextAction] || '') + ' ' + mmEsc(s.nextAction) + '</span>';
}

/* Collapsed / expanded state is remembered per browser (falls back to expanded). */
function mmxPref(key, val) {
    try { if (val === undefined) return localStorage.getItem(key); localStorage.setItem(key, val); } catch (e) { return null; }
}
function mmxCollapse(el, collapsed, prefKey) {
    if (!el) return;
    el.classList.toggle('collapsed', collapsed);
    var btn = el.querySelector('.mmx-collapse');
    if (btn) { btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true'); btn.querySelector('span').textContent = collapsed ? 'Show' : 'Hide'; }
    mmxPref(prefKey, collapsed ? '1' : '0');
}

/* 1 · Overview cards */
var MM_OVERVIEW_URL = '{{ route('client.mock-master-helper.overview') }}';
var MM_OV_TONES = { violet: '#7c3aed', blue: '#2563eb', green: '#16a34a', amber: '#d97706', rose: '#e11d48' };

function mmOvToggle() {
    var el = document.getElementById('mmOverview');
    mmxCollapse(el, !el.classList.contains('collapsed'), 'mmOvCollapsed');
}
function mmxSpark(arr, color) {
    var w = 84, h = 28, mx = Math.max.apply(null, arr), mn = Math.min.apply(null, arr);
    var pts = arr.map(function (v, i) { return [i * (w / (arr.length - 1)), h - 3 - ((v - mn) / ((mx - mn) || 1)) * (h - 6)]; });
    var d = pts.map(function (p, i) { return (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join(' ');
    return '<svg width="' + w + '" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '" aria-hidden="true"><path d="' + d + ' L' + w + ' ' + h + ' L0 ' + h + 'Z" fill="' + color + '" opacity=".12"/>' +
        '<path d="' + d + '" fill="none" stroke="' + color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
function mmOvRender(data) {
    ['mk', 'sl', 'ch'].forEach(function (agent) {
        var box = document.querySelector('#mmOverview .mmx-kpis[data-ov="' + agent + '"]');
        if (!box || !data[agent]) return;
        box.innerHTML = data[agent].map(function (c) {
            var color = MM_OV_TONES[c.tone] || MM_OV_TONES.violet;
            var delta = c.delta ? '<span class="mmx-delta ' + (c.good === true ? 'good' : (c.good === false ? 'bad' : '')) + '" title="This week compared with last week">' + mmEsc(c.delta) + '</span>' : '';
            return '<div class="mmx-kpi" style="border-top:3px solid ' + color + '"><div class="mmx-kpi-l" title="' + mmEsc(c.label) + '">' + mmEsc(c.label) + '</div>' +
                '<div class="mmx-kpi-r"><div><div class="mmx-kpi-v">' + mmEsc(c.value) + '</div>' + delta + '</div>' + (c.spark ? mmxSpark(c.spark, color) : '') + '</div>' +
                '<div class="mmx-kpi-h" title="' + mmEsc(c.hint) + '">' + mmEsc(c.hint) + '</div></div>';
        }).join('');
    });
    var t = document.getElementById('mmOvTime');
    var at = new Date(data.updated);
    if (t) t.textContent = 'Live · updated ' + (isNaN(at) ? '' : at.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
}
function mmOvLoad() {
    fetch(MM_OVERVIEW_URL, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
        .then(mmOvRender)
        .catch(function () { var t = document.getElementById('mmOvTime'); if (t) t.textContent = 'Couldn\'t refresh — will retry'; });
}

/* 2 · Quick segments (counts follow the Subscription / Payment / Plan filters) */
var MM_SEG_COUNTS = @json($mkPaged['segments'] ?? null);
var MM_CAMPAIGN_SEGMENT = @json($mkFilters['segment'] ?? 'all');
var MM_SEGMENTS = [
    { k: 'all',      t: 'All students',       s: 'Everyone in this list',       c: '#64748b' },
    { k: 'expiring', t: 'Expiring this week', s: 'Package ends in ≤ 7 days',    c: '#d97706' },
    { k: 'nomock',   t: 'No mock test yet',   s: 'Readiness 0 — never tested',  c: '#2563eb' },
    { k: 'ready',    t: 'Exam-ready',         s: 'Readiness 70+',               c: '#16a34a' },
    { k: 'quiet',    t: 'Going quiet',        s: 'No login in 14+ days',        c: '#dc2626' },
    { k: 'high',     t: 'High value',         s: 'Package ≥ average paid',      c: '#7c3aed' }
];
function mmSegLabel(k) {
    var seg = MM_SEGMENTS.find(function (x) { return x.k === k; });
    return seg ? seg.t : k;
}
function mmSegRender() {
    var box = document.getElementById('mmSegs');
    if (!box) return;
    var counts = MM_SEG_COUNTS || {};
    box.innerHTML = MM_SEGMENTS.map(function (g) {
        var sub = g.k === 'high' && counts.highMin ? 'Package ≥ $' + Math.round(counts.highMin) + ' (avg paid)' : g.s;
        var n = counts[g.k];
        return '<button type="button" class="mmx-seg' + (MM_CAMPAIGN_SEGMENT === g.k ? ' on' : '') + '" onclick="mmSegPick(\'' + g.k + '\')" aria-pressed="' + (MM_CAMPAIGN_SEGMENT === g.k) + '">' +
            '<div class="mmx-seg-t"><i style="background:' + g.c + '"></i>' + g.t + '</div>' +
            '<div class="mmx-seg-n">' + (n === undefined || n === null ? '—' : Number(n).toLocaleString()) + '</div>' +
            '<div class="mmx-seg-s" title="' + mmEsc(sub) + '">' + mmEsc(sub) + '</div></button>';
    }).join('');
}
function mmSegPick(k) {
    MM_CAMPAIGN_SEGMENT = (k === MM_CAMPAIGN_SEGMENT && k !== 'all') ? 'all' : k; // click the active card again to clear it
    mmSegRender();
    mmCampaignGo(1);
}
function mmSegToggle() {
    var el = document.getElementById('mmSegWrap');
    mmxCollapse(el, !el.classList.contains('collapsed'), 'mmSegCollapsed');
}

/* 4 · Board view */
var MM_VIEW = 'table';
var MM_BOARD_GROUP = 'stage';
var MM_BOARD_ROWS = [];
var MM_BOARD_REQ = 0;
var MM_BOARD_COLS = {
    stage: [['Active', '#16a34a'], ['Renewal Due', '#d97706'], ['Expired', '#dc2626']],
    action: [['Renew now', '#d97706'], ['Renewal reminder', '#f59e0b'], ['Win back', '#dc2626'], ['Re-engage', '#e11d48'], ['Book a mock test', '#2563eb'], ['Lead with proof', '#16a34a'], ['Lead with offer', '#7c3aed']]
};
var MM_BOARD_PER_COL = 40;

/* Header Table | Board switch: the Board panel takes the place of the agent panels (any agent). */
function mmViewSet(v) {
    MM_VIEW = v;
    document.querySelectorAll('#mmViewTg button').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-v') === v); });
    document.getElementById('bhRoot').classList.toggle('mm-board-on', v === 'board');
    document.getElementById('mmCampaignBoard').style.display = v === 'board' ? '' : 'none';
    if (v === 'board') mmBoardLoad();
}
function mmBoardGroup(g) {
    MM_BOARD_GROUP = g;
    document.querySelectorAll('#mmBoardGroup button').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-g') === g); });
    mmBoardRender();
}
/* Same audience as "Send to all": form filters, segment, Plan column and number ranges. */
function mmBoardLoad() {
    var table = document.getElementById('mmCampaignBody').closest('table');
    var params = mmCampaignParams(1);
    params.delete('page');
    mmTableRanges(table).forEach(function (f) {
        if (['value', 'readiness', 'trust'].indexOf(f.key) === -1) return;
        if (f.range.min !== null && f.range.min !== undefined) params.set(f.key + '_min', f.range.min);
        if (f.range.max !== null && f.range.max !== undefined) params.set(f.key + '_max', f.range.max);
    });
    var req = ++MM_BOARD_REQ;
    document.getElementById('mmBoardInfo').textContent = 'Loading students…';
    fetch(MM_AUDIENCE_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
        .then(function (d) {
            if (req !== MM_BOARD_REQ) return; // a newer filter change is already loading
            MM_BOARD_ROWS = d.students || [];
            mmBoardRender();
        })
        .catch(function () { if (req === MM_BOARD_REQ) document.getElementById('mmBoardInfo').textContent = 'Couldn\'t load students — please try again.'; });
}
function mmxAvColor(name) {
    var pal = ['#3b5bdb', '#7c5cfc', '#f97316', '#1e3a8a', '#0284c7', '#334155', '#16a34a', '#db2777', '#0d9488'], h = 0;
    String(name || '').split('').forEach(function (ch) { h = (h * 31 + ch.charCodeAt(0)) >>> 0; });
    return pal[h % pal.length];
}
function mmBoardRender() {
    var cols = MM_BOARD_COLS[MM_BOARD_GROUP];
    var field = MM_BOARD_GROUP === 'stage' ? 'stage' : 'nextAction';
    if (MM_BOARD_GROUP === 'action') { // only the actions someone actually has
        cols = cols.filter(function (col) { return MM_BOARD_ROWS.some(function (s) { return s.nextAction === col[0]; }); });
    }
    var html = cols.map(function (col) {
        var items = [];
        MM_BOARD_ROWS.forEach(function (s, i) { if (s[field] === col[0]) items.push(i); });
        var tiles = items.slice(0, MM_BOARD_PER_COL).map(function (i) {
            var s = MM_BOARD_ROWS[i];
            var pill = MM_BOARD_GROUP === 'stage' ? mmxNbaCell(s) : '<span class="bh-pill ' + (s.stage === 'Expired' ? 'bad' : (s.stage === 'Renewal Due' ? 'warn' : 'good')) + '">' + mmEsc(s.stage) + '</span>';
            var initial = (String(s.name || '?').trim().charAt(0) || '?').toUpperCase();
            return '<div class="mmx-tile" onclick="mmBoardOpen(' + i + ')" title="Open message composer">' +
                '<div class="mmx-tile-a"><span class="bh-av" style="background:' + mmxAvColor(s.name) + '">' + mmEsc(initial) + '</span>' +
                    '<div style="min-width:0"><div class="mmx-tile-n">' + mmEsc(s.name) + '</div><div class="mmx-tile-c">' + mmEsc(s.plan || '—') + ' · ' + mmEsc(s.sub) + '</div></div></div>' +
                '<div class="mmx-tile-b"><b>' + mmEsc(s.value) + '</b>' + pill + mmxRing(s.readiness) + '</div></div>';
        }).join('');
        var more = items.length > MM_BOARD_PER_COL ? '<div class="mmx-more">+' + (items.length - MM_BOARD_PER_COL).toLocaleString() + ' more — use the table or a segment to narrow down</div>' : '';
        return '<div class="mmx-col"><h4><i style="background:' + col[1] + '"></i>' + mmEsc(col[0]) + '<span>' + items.length.toLocaleString() + '</span></h4>' +
            '<div class="mmx-col-body">' + (tiles || '<div class="mmx-more">Nobody here</div>') + more + '</div></div>';
    }).join('');
    document.getElementById('mmBoardCols').innerHTML = html;
    document.getElementById('mmBoardInfo').textContent = MM_BOARD_ROWS.length.toLocaleString() + ' students match the current filters' +
        (MM_CAMPAIGN_SEGMENT !== 'all' ? ' · segment: ' + mmSegLabel(MM_CAMPAIGN_SEGMENT) : '');
}
/* Opens the same message window as the table's View button. */
function mmBoardOpen(i) {
    var keep = MM_CAMPAIGN_ROWS;
    MM_CAMPAIGN_ROWS = MM_BOARD_ROWS;
    try { mmStudentOpen(i); } finally { MM_CAMPAIGN_ROWS = keep; }
}

/* ── Part 2 (Nov 2026) ──────────────────────────────────────────────────────
   5. Expand button on every agent's step tabs: the middle table fills the screen
   6. Profile button in the message windows: side panel with the student's data */
function mmFocusToggle(force) {
    var root = document.getElementById('bhRoot');
    var was = root.classList.contains('mm-focus');
    var on = force === undefined ? !was : !!force;
    if (on === was) return;
    root.classList.toggle('mm-focus', on);
    document.querySelectorAll('#bhRoot .mmx-expand').forEach(function (b) {
        var label = on ? 'Minimise the table' : 'Expand the table';
        b.title = label; b.setAttribute('aria-label', label);
    });
    if (on) {
        MMX_PRE_FOCUS_PER = MM_CAMPAIGN_PER;
        MMX_MANUAL_PER = false;
        requestAnimationFrame(mmxFillAll);
    } else {
        mmxUnfillLists();
        MMX_ROW_H = null;
        if (MM_CAMPAIGN_PER !== MMX_PRE_FOCUS_PER) mmCampaignPerSet(MMX_PRE_FOCUS_PER); // back to the rows per page from before
    }
}

/* Expanded view fills the screen: the Campaign table shows as many rows per page as fit, and the
   Sales / Retention lists (normally a short top list) load more of their full list, with
   "Show 25 more". Minimising puts everything back as it was. */
var MMX_PRE_FOCUS_PER = 10;
var MMX_ROW_H = null;
var MMX_MANUAL_PER = false; // rows per page picked by hand while expanded: stop fitting
function mmxFocusOn() { return document.getElementById('bhRoot').classList.contains('mm-focus'); }
function mmxRowsThatFit(tbody, below) {
    var view = tbody.closest('.dash-view');
    if (!view) return 10;
    var rows = Array.prototype.slice.call(tbody.rows, 0, 10);
    var h = MMX_ROW_H || (rows.length ? rows.reduce(function (a, r) { return a + r.offsetHeight; }, 0) / rows.length : 46) || 46;
    MMX_ROW_H = Math.max(38, h);
    var avail = view.getBoundingClientRect().bottom - tbody.getBoundingClientRect().top - (below || 0) - 12;
    return Math.floor(avail / MMX_ROW_H);
}
function mmxFillAll() {
    if (!mmxFocusOn() || MM_VIEW === 'board') return;
    var dash = document.querySelector('#bhRoot .dash.on');
    if (!dash) return;
    var body = document.getElementById('mmCampaignBody');
    if (body && dash.contains(body) && body.offsetParent) mmxFitCampaign(body);
    dash.querySelectorAll('.mm-lf').forEach(function (bar) {
        var scope = mmLfScope(bar);
        var table = scope && scope.querySelector('table.dtbl');
        if (table && table.offsetParent) mmxFillList(bar, table);
    });
}
function mmxFitCampaign(body) {
    var view = body.closest('.dash-view');
    if (MMX_MANUAL_PER) return;
    if (view) view.scrollTop = 0;
    var pager = document.getElementById('mmCampaignPager');
    var n = Math.min(100, Math.max(10, mmxRowsThatFit(body, pager ? pager.offsetHeight : 0)));
    if (n !== MM_CAMPAIGN_PER) mmCampaignPerSet(n);
}
function mmxIsFilled(tbody) { return !!tbody.querySelector('tr[data-mmx-fill]'); }
function mmxFillList(bar, table) {
    var tbody = table.tBodies[0];
    if (!tbody) return;
    var active = bar.__active || '';
    if (bar.__fill && bar.__fill.active === active && mmxIsFilled(tbody)) return; // already filled for these filters
    var want = Math.max(mmxRowsThatFit(tbody, 44), tbody.rows.length);
    if (bar.__orig === undefined) { bar.__orig = tbody.innerHTML; bar.__size = Math.max(tbody.rows.length, 10); } // same as mmListFilterApply
    var fill = bar.__fill = { active: active, orig: mmxIsFilled(tbody) ? (bar.__fill && bar.__fill.orig) : tbody.innerHTML, rows: null, shown: want, table: table };
    fetch(mmLfUrl(bar, new URLSearchParams(active)), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
        .then(function (d) {
            if (bar.__fill !== fill || !mmxFocusOn()) return;
            fill.rows = d.rows || [];
            mmxListRender(bar);
        })
        .catch(function () {});
}
function mmxListRender(bar) {
    var fill = bar.__fill, table = fill.table, tbody = table.tBodies[0];
    var rows = fill.rows, shown = Math.min(fill.shown, rows.length);
    if (!rows.length) return;
    tbody.innerHTML = rows.slice(0, shown).map(function (r) { return mmLfRow(bar.getAttribute('data-kind'), r); }).join('');
    if (tbody.rows[0]) tbody.rows[0].setAttribute('data-mmx-fill', '1'); // marks the list as filled (a list filter redraw drops it)
    if (typeof mmPlanFilterApply === 'function') mmPlanFilterApply(table); // keep the Plan / number column filters
    var ft = table.nextElementSibling && table.nextElementSibling.classList.contains('mmx-fill-ft') ? table.nextElementSibling : null;
    if (!ft) { ft = document.createElement('div'); ft.className = 'mmx-fill-ft'; table.insertAdjacentElement('afterend', ft); }
    ft.innerHTML = 'Showing <b>' + shown.toLocaleString() + '</b> of <b>' + rows.length.toLocaleString() + '</b> students in this list' +
        (shown < rows.length ? ' <button type="button">Show ' + Math.min(25, rows.length - shown) + ' more</button>' : '');
    var more = ft.querySelector('button');
    if (more) more.onclick = function () { fill.shown = shown + 25; mmxListRender(bar); };
}
function mmxUnfillLists() {
    document.querySelectorAll('#bhRoot .mm-lf').forEach(function (bar) {
        var fill = bar.__fill;
        if (!fill) return;
        var tbody = fill.table.tBodies[0];
        if (tbody && mmxIsFilled(tbody)) {
            tbody.innerHTML = fill.orig;
            if (typeof mmPlanFilterApply === 'function') mmPlanFilterApply(fill.table);
        }
        var ft = fill.table.nextElementSibling;
        if (ft && ft.classList.contains('mmx-fill-ft')) ft.remove();
        bar.__fill = null;
    });
}
// While expanded: switching step / list tabs, or applying a list filter, fills the newly shown list too.
document.addEventListener('click', function (e) {
    if (!mmxFocusOn() || !e.target.closest('#bhRoot')) return;
    if (e.target.closest('.mmx-fill-ft')) return;
    setTimeout(mmxFillAll, 60);
    setTimeout(mmxFillAll, 1500); // after a list filter's own reload has finished
});
window.addEventListener('resize', function () {
    if (!mmxFocusOn()) return;
    clearTimeout(window.__mmxResize);
    window.__mmxResize = setTimeout(function () { MMX_ROW_H = null; mmxFillAll(); }, 300);
});
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (document.querySelector('.risk-modal-overlay.show, .mm-cmp-overlay.show, .mm-stu-overlay.show, .mm-list-modal-overlay.show')) return; // Esc closes the window first
    if (document.getElementById('mmxProf') && document.getElementById('mmxProf').classList.contains('show')) { mmxProfileClose(); return; }
    if (document.getElementById('bhRoot').classList.contains('mm-focus')) mmFocusToggle(false);
});

var MM_INSIGHT_URL = '{{ route('client.mock-master-helper.student-insight') }}';
var MMX_PROF = null; // { src: 'stu' | 'cmp', row }
/* Campaign message templates that fit each Next best action (Free trial students get "Upgrade from free"). */
var MM_NBA_TEMPLATE = { 'Win back': 'renewal', 'Renew now': 'renewal', 'Renewal reminder': 'renewal', 'Re-engage': 'reengage', 'Book a mock test': 'reengage', 'Lead with proof': 'proof', 'Lead with offer': 'thanks' };
var MMX_PROF_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>';

function mmxProfBtn(src) {
    return '<button type="button" class="mmx-prof-btn" data-prof-src="' + src + '" onclick="mmxProfileToggle(\'' + src + '\')" title="Show this student\'s profile on the side">' + MMX_PROF_ICON + 'Profile</button>';
}
function mmxProfileToggle(src) {
    var panel = document.getElementById('mmxProf');
    if (panel && panel.classList.contains('show') && MMX_PROF && MMX_PROF.src === src) { mmxProfileClose(); return; }
    var row = src === 'stu' ? MM_STUDENT_SELECTED : (src === 'pop' ? MM_POP_ROW : (MM_CMP && MM_CMP.row));
    if (row) mmxProfileOpen(src, row);
}
function mmxProfileClose() {
    var panel = document.getElementById('mmxProf');
    if (panel) panel.classList.remove('show');
    document.body.classList.remove('mmx-prof-open');
    document.querySelectorAll('.mmx-prof-btn, .mm-stu-more').forEach(function (b) { b.classList.remove('on'); });
    MMX_PROF = null;
}
function mmxProfileOpen(src, row) {
    var panel = document.getElementById('mmxProf');
    if (!panel) {
        panel = document.createElement('aside');
        panel.id = 'mmxProf';
        panel.className = 'mmx-prof';
        panel.setAttribute('aria-label', 'Student profile');
        document.body.appendChild(panel);
    }
    MMX_PROF = { src: src, row: row };
    document.querySelectorAll('.mmx-prof-btn').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-prof-src') === src); });
    document.getElementById('mmStuMore').classList.toggle('on', src === 'pop');
    panel.innerHTML = '<div class="mmx-prof-hd"><div><h3>' + mmEsc(row.name || 'Student') + '</h3><div class="c">Loading profile…</div></div>' +
        '<button type="button" class="mmx-prof-x" onclick="mmxProfileClose()" aria-label="Close profile">✕</button></div>';
    panel.classList.add('show');
    document.body.classList.add('mmx-prof-open');

    var sid = row.sid || row.studentId;
    var qs = sid ? 'sid=' + encodeURIComponent(sid) : (row.email ? 'email=' + encodeURIComponent(row.email) : '');
    if (!qs) { mmxProfileRender(null, row, src); return; }
    var mine = MMX_PROF;
    fetch(MM_INSIGHT_URL + '?' + qs, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) { if (MMX_PROF === mine) mmxProfileRender(d, row, src); })
        .catch(function () { if (MMX_PROF === mine) mmxProfileRender(null, row, src); });
}
function mmxProfileRender(d, row, src) {
    var panel = document.getElementById('mmxProf');
    if (!panel) return;
    if (!d) {
        panel.innerHTML = '<div class="mmx-prof-hd"><div><h3>' + mmEsc(row.name || 'Student') + '</h3><div class="c">Couldn\'t load this student\'s profile.</div></div>' +
            '<button type="button" class="mmx-prof-x" onclick="mmxProfileClose()" aria-label="Close profile">✕</button></div>';
        return;
    }
    var initial = (String(d.name || '?').trim().charAt(0) || '?').toUpperCase();
    var pic = d.profile_picture
        ? '<div class="mmx-prof-pic" style="background-image:url(\'' + encodeURI(d.profile_picture).replace(/'/g, '%27') + '\')"></div>'
        : '<div class="mmx-prof-pic" style="background:' + mmxAvColor(d.name) + '">' + mmEsc(initial) + '</div>';
    var pkg = d.package;
    var tags = [];
    if (row.plan) tags.push('<span class="' + mmPlanClassJs(row.plan) + '">' + mmEsc(row.plan) + '</span>');
    if (pkg && pkg.stage) tags.push('<span class="bh-pill ' + (pkg.stage === 'Expired' ? 'bad' : (pkg.stage === 'Renewal Due' ? 'warn' : 'good')) + '">' + mmEsc(pkg.stage) + '</span>');

    var lm = d.lastMock;
    var skills = lm && lm.sections && Object.keys(lm.sections).length
        ? Object.keys(lm.sections).map(function (k) {
            var v = Math.round(lm.sections[k]);
            return '<div class="mmx-skill"><span>' + k + '</span><i><b style="width:' + Math.max(2, Math.min(100, v / 90 * 100)) + '%"></b></i><b>' + v + '</b></div>';
        }).join('')
        : '<div class="mmx-prof-empty">No mock test results yet.</div>';
    var trend = d.trend && d.trend.length > 1
        ? '<div style="margin-top:10px;display:flex;align-items:center;gap:10px;font-size:12px;color:#64748b">' + mmxSpark(d.trend, '#7c3aed') + 'Overall score, last ' + d.trend.length + ' tests</div>' : '';

    // Recommended next step; in the Campaign window it can fill in the matching message.
    var next = '<div class="mmx-prof-empty">No package on record.</div>';
    if (d.nextAction) {
        next = mmxNbaCell(d);
        var key = row.plan === 'Free trial' ? 'upgrade' : MM_NBA_TEMPLATE[d.nextAction];
        var tpl = src === 'stu' && MM_MESSAGE_TEMPLATES.find(function (t) { return t.key === key; });
        if (tpl) {
            next += '<div class="mmx-prof-draft">' + mmEsc(mmFillTemplate(tpl.body, row)) + '</div>' +
                '<button type="button" class="mmx-prof-use" onclick="mmxProfileUse(\'' + tpl.key + '\')">Use this message — ' + mmEsc(tpl.label) + '</button>';
        }
    }

    var kv = [['Email', d.email || '—'], ['Phone', d.phone || '—'], ['Course', d.course_type || '—'], ['Target score', d.target || '—'],
              ['Package', pkg ? pkg.name : '—'], ['Expires', pkg && pkg.expires ? pkg.expires + (pkg.expiresIn !== null && pkg.expiresIn >= 0 ? ' (in ' + pkg.expiresIn + 'd)' : '') : '—'],
              ['Total paid', d.totalPaid], ['Trust', d.trust + (d.trust < 65 ? ' · lead with proof' : ' · lead with offer')], ['Joined', d.joined || '—'], ['Last active', d.lastActive]];
    var tl = (d.timeline || []).map(function (t) {
        return '<div' + (t.bad ? ' class="bad"' : '') + '>' + mmEsc(t.text) + '<small>' + mmEsc(t.sub || '') + '</small></div>';
    }).join('');

    panel.innerHTML =
        '<div class="mmx-prof-hd">' + pic + '<div style="min-width:0"><h3>' + mmEsc(d.name) + '</h3><div class="c">' + mmEsc(pkg ? pkg.name : (d.course_type || '')) + '</div>' +
            (tags.length ? '<div class="mmx-prof-tags">' + tags.join('') + '</div>' : '') + '</div>' +
            '<button type="button" class="mmx-prof-x" onclick="mmxProfileClose()" aria-label="Close profile">✕</button></div>' +
        '<div class="mmx-prof-b">' +
            '<div class="mmx-prof-stats"><div><small>Readiness</small><b>' + d.readiness + '%</b></div><div><small>Mock tests</small><b>' + d.mockCount + '</b></div><div><small>Package</small><b>' + mmEsc(pkg ? pkg.value : '—') + '</b></div></div>' +
            '<div><div class="mmx-prof-sec">Skill breakdown (last mock)' + (lm ? '<span>' + mmEsc(lm.date || '') + (lm.overall !== null ? ' · overall ' + lm.overall : '') + (d.bestScore !== null ? ' · best ' + d.bestScore : '') + '</span>' : '') + '</div>' + skills + trend + '</div>' +
            '<div><div class="mmx-prof-sec">Recommended next step</div>' + next + '</div>' +
            '<div><div class="mmx-prof-sec">Details</div><dl class="mmx-prof-kv">' + kv.map(function (p) { return '<dt>' + p[0] + '</dt><dd>' + mmEsc(p[1]) + '</dd>'; }).join('') + '</dl></div>' +
            '<div><div class="mmx-prof-sec">Timeline</div>' + (tl ? '<div class="mmx-tl">' + tl + '</div>' : '<div class="mmx-prof-empty">No activity on record.</div>') + '</div>' +
        '</div>';
}
/* "Use this message": ticks the matching template in the Campaign message window and fills it in. */
function mmxProfileUse(key) {
    var radio = document.querySelector('input[name="mmTpl"][value="' + key + '"]');
    if (radio) radio.checked = true;
    mmPickTemplate(key);
}

(function () {
    function init() {
        // Picking an agent tab while the Board is open goes back to that agent's table.
        document.querySelectorAll('#bhRoot .atab').forEach(function (b) {
            b.addEventListener('click', function () { if (MM_VIEW === 'board') mmViewSet('table'); });
        });
        mmxCollapse(document.getElementById('mmOverview'), mmxPref('mmOvCollapsed') === '1', 'mmOvCollapsed');
        mmxCollapse(document.getElementById('mmSegWrap'), mmxPref('mmSegCollapsed') === '1', 'mmSegCollapsed');
        mmSegRender();
        mmOvLoad();
        setInterval(function () { if (!document.hidden) mmOvLoad(); }, 120000);
    }
    if (document.readyState !== 'loading') init(); else document.addEventListener('DOMContentLoaded', init);
})();

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

    document.getElementById('mmStudentTitle').innerHTML =
        escapeHtml(s.name) + ' &nbsp;' + mmxProfBtn('stu') +
        '<div style="font-size:11.5px;font-weight:500;color:#6b7280;margin-top:3px">' +
            (s.email ? escapeHtml(s.email) : '<span style="color:#9ca3af">No email on file</span>') +
            ' &nbsp;-&nbsp; ' +
            (s.phone ? escapeHtml(s.phone) : '<span style="color:#9ca3af">No phone on file</span>') +
        '</div>';
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
    btn.innerHTML = '<span class="mm-btn-spinner"></span>Sending…';
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

var MM_SEND_WHATSAPP_URL = '{{ route('client.mock-master-helper.send-whatsapp') }}';

function mmSendWhatsApp() {
    var s = MM_STUDENT_SELECTED;
    if (!s || !String(s.phone || '').replace(/\D/g, '')) return;
    var btn = document.getElementById('mmSendWa');
    var hint = document.getElementById('mmMsgHint');
    var body = document.getElementById('mmMsgBody').value;

    btn.disabled = true;
    btn.innerHTML = '<span class="mm-btn-spinner"></span>Sending…';
    hint.style.color = '#6b7280';
    hint.textContent = 'Sending WhatsApp to ' + s.phone + '…';

    fetch(MM_SEND_WHATSAPP_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ student_id: s.studentId, body: body })
    })
    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
    .then(function (res) {
        btn.textContent = 'WhatsApp';
        btn.disabled = false;
        hint.style.color = res.ok ? '#16a34a' : '#b91c1c';
        hint.textContent = res.data.message || (res.ok ? 'WhatsApp sent.' : 'WhatsApp could not be sent.');
    })
    .catch(function () {
        btn.textContent = 'WhatsApp';
        btn.disabled = false;
        hint.style.color = '#b91c1c';
        hint.textContent = "Couldn't reach the server — please try again.";
    });
}

function mmStudentClose() {
    var overlay = document.getElementById('mmStudentModal');
    if (overlay) overlay.classList.remove('show');
    mmxProfileClose(); // side Profile panel goes with the window
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
    if (typeof MM_CAMPAIGN_PLAN !== 'undefined' && MM_CAMPAIGN_PLAN && MM_CAMPAIGN_PLAN !== 'all') params.set('plan', MM_CAMPAIGN_PLAN); // Plan column dropdown
    if (typeof MM_CAMPAIGN_SEGMENT !== 'undefined' && MM_CAMPAIGN_SEGMENT && MM_CAMPAIGN_SEGMENT !== 'all') params.set('segment', MM_CAMPAIGN_SEGMENT); // quick segment card
    if (typeof MM_CAMPAIGN_PER !== 'undefined' && MM_CAMPAIGN_PER && MM_CAMPAIGN_PER !== 10) params.set('per_page', MM_CAMPAIGN_PER); // rows per page
    if (typeof MM_CAMPAIGN_CF !== 'undefined') Object.keys(MM_CAMPAIGN_CF).forEach(function (k) { if (MM_CAMPAIGN_CF[k]) params.set('f_' + k, MM_CAMPAIGN_CF[k]); }); // column dropdowns
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
    MM_CAMPAIGN_PLAN = null; // also clear the Plan column filter
    MM_CAMPAIGN_SEGMENT = 'all'; // and the quick segment card
    if (typeof mmxCfClearAll === 'function') mmxCfClearAll(); // and the column dropdowns
    mmSegRender();
    var ct = document.getElementById('mmCampaignBody');
    if (ct) { ct.closest('table').removeAttribute('data-plan-filter'); mmPlanFilterApply(ct.closest('table')); }
    mmCampaignLoad('');
    if (window.history && history.replaceState) {
        history.replaceState(null, '', window.location.pathname);
    }
}

/* ── "Send to all" (shared window: partials/bulk-send) ──
   Campaign: every renewal-ready student matching the form filters and number
   ranges, all plans loaded so other plans can be added in the window.
   Sales / Retention lists: everyone in that list (up to 2,000), pre-ticked by
   the table's Plan and number filters. Messages go through send-bulk, which
   looks each student's address up on the server. */
var MM_AUDIENCE_URL = '{{ route('client.mock-master-helper.campaign-audience') }}';
var MM_LIST_AUDIENCE_URL = '{{ route('client.mock-master-helper.list-audience', ['dataset' => '__DS__']) }}';
var MM_SEND_BULK_URL = '{{ route('client.mock-master-helper.send-bulk') }}';

function mmNumVal(v) { return mmNumOf({ textContent: v == null ? '' : String(v) }); }
function mmRangeText(r) { return r.min !== null && r.max !== null ? r.min + ' to ' + r.max : (r.min !== null ? '≥ ' + r.min : '≤ ' + r.max); }
function mmPlanClassJs(plan) {
    var m = /class="([^"]*)"/.exec(mmPlanCell({ plan: plan }));
    return m ? m[1] : 'xb-g';
}

/* Number-range filters set from a table's column menus: [{label, idx, range}]. */
function mmTableRanges(table) {
    var num = (table && table.__mmNum) || {};
    var head = table && Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
    var out = [];
    if (!head) return out;
    Array.prototype.forEach.call(head.children, function (th, i) {
        if (!mmNumActive(num[i])) return;
        var label = (th.childNodes[0] && th.childNodes[0].textContent || th.textContent).trim();
        out.push({ label: label, key: th.getAttribute('data-sort-key'), range: num[i] });
    });
    return out;
}

function mmBulkSendBatch(channel, items, total) {
    return fetch(MM_SEND_BULK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ channel: channel, total: total, messages: items.map(function (it) {
            return { student_id: it.row.id, subject: channel === 'email' ? it.subject : null, body: it.body };
        }) })
    })
    .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
    .then(function (d) {
        return (d.results || []).map(function (x) { return { id: x.student_id, status: x.status, to: x.to, error: x.error }; });
    });
}

function mmBulkOpen() {
    var form = document.getElementById('mmCampaignFilter');
    var table = document.getElementById('mmCampaignBody').closest('table');
    var params = mmCampaignParams(1);
    params.delete('page');
    params.delete('plan'); // all plans load; the Plan filter only decides who starts ticked
    var filters = [];
    var sub = form.querySelector('[name="subscription"]').value;
    var from = form.querySelector('[name="from"]').value, to = form.querySelector('[name="to"]').value;
    if (sub) filters.push({ label: 'Subscription: ' + sub, test: function (r) { return r.raw.sub === sub; } });
    if (from || to) filters.push({ label: 'Payment ' + (from ? 'from ' + from : '') + (from && to ? ' ' : '') + (to ? 'to ' + to : ''), test: function () { return true; } });
    if (MM_CAMPAIGN_SEGMENT && MM_CAMPAIGN_SEGMENT !== 'all') filters.push({ label: 'Segment: ' + mmSegLabel(MM_CAMPAIGN_SEGMENT), test: function () { return true; } }); // applied on the server
    if (typeof MM_CAMPAIGN_CF !== 'undefined') Object.keys(MM_CAMPAIGN_CF).forEach(function (k) { // column dropdowns, applied on the server
        if (MM_CAMPAIGN_CF[k]) filters.push({ label: MMX_CF[k].label + ': ' + mmxCfLabel(k, MM_CAMPAIGN_CF[k]), test: function () { return true; } });
    });
    if (MM_CAMPAIGN_PLAN && MM_CAMPAIGN_PLAN !== 'all') {
        var plan = MM_CAMPAIGN_PLAN;
        filters.push({ label: 'Plan: ' + mmPfLabel(plan), test: function (r) { return mmPfMatches(r.group, plan); } });
    }
    mmTableRanges(table).forEach(function (f) {
        if (['value', 'readiness', 'trust'].indexOf(f.key) === -1) return;
        if (f.range.min !== null && f.range.min !== undefined) params.set(f.key + '_min', f.range.min);
        if (f.range.max !== null && f.range.max !== undefined) params.set(f.key + '_max', f.range.max);
        filters.push({ label: f.label + ' ' + mmRangeText(f.range), test: function () { return true; } }); // applied on the server
    });

    XBulk.open(mmBulkPlanCfg({
        title: 'Send to all — Campaign',
        reportTitle: 'Mock Master — Send to all report (Campaign)',
        fileBase: 'mock-master-campaign',
        channels: ['email', 'whatsapp'],
        groupLabel: 'Plan',
        groupClass: mmPlanClassJs,
        filters: filters,
        templates: MM_MESSAGE_TEMPLATES,
        fill: function (text, r) { return mmFillTemplate(text, r.raw); },
        note: '{first}, {name}, {package}, {value} and {readiness} are filled in for each student.',
        send: mmBulkSendBatch,
        load: function () {
            return fetch(MM_AUDIENCE_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
                .then(function (d) {
                    return (d.students || []).map(function (s) {
                        return { id: s.sid, name: s.name, email: s.email, phone: s.phone, group: s.plan || '—',
                                 date: s.paymentDate, dateSub: s.lastActive, search: [s.sub, s.stage].join(' '), raw: s };
                    });
                });
        }
    }));
}

/* Sales / Retention list headers → row field, for the number-range filters. */
var MM_BULK_NUM_FIELDS = {
    'Readiness': 'readiness', 'Intent': 'intent', 'Trust': 'trust',
    'Inactive for': 'inactiveDays', 'Value at risk': 'valueAtRisk', 'Risk score': 'risk',
    'Score': 'score', 'Amount': 'amount', 'Expires': 'expires', 'Expired': 'expired', 'Value': 'amount'
};
var MM_BULK_LIST_NAMES = {
    slProspects: 'Prospects', slClose: 'Ready to convert', slAbandoned: 'Open checkouts',
    chAtRisk: 'Save first', chWatchlist: 'Watchlist', chRenewals: 'Renewals due', chWinBack: 'Win-back'
};

/* Plan picker in the Send to all window: same groups, order and wording as the Plan column menu. */
function mmBulkPlanCfg(cfg) {
    cfg.groupPresets = ['all', 'paid', 'unpaid'].map(function (k) {
        return { label: MM_PLAN_GROUPS[k].label, desc: MM_PLAN_GROUPS[k].desc, groups: MM_PLAN_GROUPS[k].plans };
    });
    cfg.groupDesc = MM_PLAN_DESC;
    cfg.groupOrder = MM_PLAN_ORDER;
    return cfg;
}

function mmBulkOpenList(dataset, btn) {
    var scope = btn.closest('.mm-scr-list') || btn.closest('.mm-panel');
    var table = scope && scope.querySelector('table.dtbl');
    var bar = scope && scope.querySelector('.mm-lf');
    var barParams = new URLSearchParams(bar && bar.__active ? bar.__active : '');
    var filters = [];
    if (barParams.get('subscription')) filters.push({ label: bar.getAttribute('data-sub-label') + ': ' + barParams.get('subscription'), test: function () { return true; } });
    if (barParams.get('from') || barParams.get('to')) {
        filters.push({ label: bar.getAttribute('data-date-label') + (barParams.get('from') ? ' from ' + barParams.get('from') : '') + (barParams.get('to') ? ' to ' + barParams.get('to') : ''), test: function () { return true; } }); // applied on the server
    }
    var plan = table && table.getAttribute('data-plan-filter');
    if (plan && plan !== 'all') filters.push({ label: 'Plan: ' + mmPfLabel(plan), test: function (r) { return mmPfMatches(r.group, plan); } });
    mmTableRanges(table).forEach(function (f) {
        var field = MM_BULK_NUM_FIELDS[f.label];
        if (!field) return;
        var rg = f.range;
        filters.push({ label: f.label + ' ' + mmRangeText(rg), test: function (r) {
            var v = mmNumVal(r.raw[field]);
            return v !== null && (rg.min === null || rg.min === undefined || v >= rg.min) && (rg.max === null || rg.max === undefined || v <= rg.max);
        } });
    });
    var name = MM_BULK_LIST_NAMES[dataset] || 'this list';

    XBulk.open(mmBulkPlanCfg({
        title: 'Send to all — ' + name,
        reportTitle: 'Mock Master — Send to all report (' + name + ')',
        fileBase: 'mock-master-' + name,
        channels: ['email', 'whatsapp'],
        groupLabel: 'Plan',
        groupClass: mmPlanClassJs,
        filters: filters,
        templates: MM_CMP_TEMPLATES[MM_CMP_DATASET_CTX[dataset]] || MM_CMP_TEMPLATES.marketing,
        fill: function (text, r) { return mmCmpFill(text, mmCmpVars(r.raw, {})); },
        note: '{first}, {name}, {course}, {package}, {amount}, {expires} and {expired} are filled in for each student where known.',
        send: mmBulkSendBatch,
        load: function () {
            var q = barParams.toString();
            return fetch(MM_LIST_AUDIENCE_URL.replace('__DS__', encodeURIComponent(dataset)) + (q ? '?' + q : ''), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
                .then(function (d) {
                    return (d.rows || []).filter(function (r) { return r.sid; }).map(function (r) {
                        var when = r.lastActive || r.expires || r.expired || r.attempted || (r.inactiveDays !== undefined ? r.inactiveDays + 'd inactive' : '');
                        return { id: r.sid, name: r.name, email: r.email, phone: r.phone, group: r.plan || '—',
                                 date: when, dateSub: r.package || r.sub || '', search: [r.sub, r.package, r.signals, r.action].join(' '), raw: r };
                    });
                });
        }
    }));
}

/* ── Sales / Retention list filter bars (partials/mm-list-filter): Subscription + date range
   over the whole list (server side), then the table is re-drawn in its own layout. Reset puts
   back the rows the page loaded with. Plan / number column filters keep working on top. ── */
function mmLfScope(bar) { return bar.closest('.mm-scr-list') || bar.closest('.mm-panel'); }
function mmLfParams(bar) {
    var p = new URLSearchParams();
    bar.querySelectorAll('select, input').forEach(function (x) { if (x.value) p.set(x.name, x.value); });
    return p;
}
function mmLfUrl(bar, params) {
    var q = params && params.toString();
    return MM_LIST_AUDIENCE_URL.replace('__DS__', encodeURIComponent(bar.getAttribute('data-dataset'))) + (q ? '?' + q : '');
}
/* Fills the Subscription select once, from the whole (unfiltered) list. */
function mmLfOptions(bar) {
    if (bar.__optsLoading) return;
    bar.__optsLoading = true;
    var sel = bar.querySelector('select[name="subscription"]');
    sel.insertAdjacentHTML('beforeend', '<option value="" disabled data-lf-loading>Loading…</option>');
    fetch(mmLfUrl(bar), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
        .then(function (d) {
            var cur = sel.value, opts = d.options || [];
            sel.innerHTML = '<option value="">All</option>' + opts.map(function (o) {
                return '<option value="' + mmEsc(o) + '"' + (o === cur ? ' selected' : '') + '>' + mmEsc(o) + '</option>';
            }).join('');
            if (!opts.length) { sel.innerHTML = '<option value="">None in this list</option>'; sel.disabled = true; }
        })
        .catch(function () { bar.__optsLoading = false; var l = sel.querySelector('[data-lf-loading]'); if (l) l.remove(); });
}
function mmLfAcct(r, line) {
    var av = '<span class="bh-av" style="background:' + mmEsc(r.color) + '">' + mmEsc(r.initial) + '</span>';
    var inner = line === null
        ? '<div class="bh-acct-n">' + mmEsc(r.name) + '</div>'
        : '<div><div class="bh-acct-n">' + mmEsc(r.name) + '</div>' + (line ? '<div class="bh-acct-c">' + mmEsc(line) + '</div>' : '') + '</div>';
    return '<td class="acctn" ' + mmStuAttrs(r) + '><div class="bh-acct">' + av + inner + '</div></td>';
}
function mmLfScoreKind(v) { return v >= 70 ? 'good' : (v >= 50 ? 'info' : 'warn'); }
/* One table row, matching the Blade markup of each list. */
function mmLfRow(kind, r) {
    var td = function (v) { return '<td>' + mmEsc(v) + '</td>'; };
    var view = function (ctx) { return '<td>' + mmCmpBtn(ctx, r) + '</td>'; };
    switch (kind) {
        case 'today':
            return '<tr>' + mmLfAcct(r, '(' + r.sub + ')') + mmPlanCell(r) + td(r.readiness) + td(r.intent) + td(r.trust) +
                '<td><span class="stk-play ' + (r.play === 'Call' ? 'call' : 'onboarding') + '">' + mmEsc(r.play) + '</span></td>' + view('prospect') + '</tr>';
        case 'accounts':
            return '<tr>' + mmLfAcct(r, null) + mmPlanCell(r) + td(r.readiness) + td(r.intent) + td(r.trust) + td(r.play) + view('prospect') + '</tr>';
        case 'savefirst':
            return '<tr>' + mmLfAcct(r, '(' + r.sub + ')') + mmPlanCell(r) + td(r.inactiveDays + 'd') + td(r.valueAtRisk) +
                '<td><span style="color:var(--crit);font-weight:600">' + mmEsc(r.risk) + '</span></td>' + view('chAtRisk') + '</tr>';
        case 'watchlist':
            return '<tr>' + mmLfAcct(r, null) + mmPlanCell(r) + td(r.inactiveDays + 'd') + td(r.valueAtRisk) + td(r.risk) + view('chWatchlist') + '</tr>';
        case 'convert':
            return '<tr>' + mmLfAcct(r, '') + mmPlanCell(r) + '<td><span class="bh-pill ' + mmLfScoreKind(r.score) + '">' + mmEsc(r.score) + '</span></td>' +
                '<td class="cg-why">' + mmEsc(r.signals) + '</td>' + td(r.lastActive) + '<td class="cg-next">' + mmEsc(r.action) + '</td>' + view('convert') + '</tr>';
        default: // abandoned | renewals | winback
            var mid = kind === 'abandoned' ? td(r.package) + td(r.amount) + td(r.attempted)
                : kind === 'renewals' ? td(r.package) + td(r.expires) + td(r.amount)
                : td(r.package) + td(r.expired) + td(r.amount);
            return '<tr>' + mmLfAcct(r, r.signals || '') + mmPlanCell(r) + mid + '<td class="cg-next">' + mmEsc(r.action) + '</td>' + view(kind) + '</tr>';
    }
}
function mmListFilterApply(btn) {
    var bar = btn.closest('.mm-lf');
    var table = mmLfScope(bar).querySelector('table.dtbl');
    var tbody = table && table.tBodies[0];
    var info = bar.querySelector('.mm-lf-info');
    var params = mmLfParams(bar);
    if (!tbody) return;
    if (!params.toString()) return mmListFilterReset(btn);
    if (bar.__orig === undefined) { bar.__orig = tbody.innerHTML; bar.__size = Math.max(tbody.rows.length, 10); }
    var cols = table.tHead ? table.tHead.rows[0].cells.length : 7;
    tbody.innerHTML = '<tr><td colspan="' + cols + '" style="color:var(--g3);padding:20px">Loading…</td></tr>';
    info.textContent = '';
    fetch(mmLfUrl(bar, params), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
            if (!res.ok) throw new Error((res.d && res.d.message) || 'bad status');
            var rows = res.d.rows || [];
            bar.__active = params.toString();
            tbody.innerHTML = rows.length
                ? rows.slice(0, bar.__size).map(function (r) { return mmLfRow(bar.getAttribute('data-kind'), r); }).join('')
                : '<tr><td colspan="' + cols + '" style="color:var(--g3);padding:20px">No students match these filters.</td></tr>';
            info.textContent = rows.length > bar.__size
                ? 'Showing ' + bar.__size + ' of ' + rows.length.toLocaleString() + ' matching · Send to all includes all ' + rows.length.toLocaleString()
                : rows.length.toLocaleString() + ' matching';
        })
        .catch(function (e) {
            tbody.innerHTML = bar.__orig;
            info.style.color = '#b91c1c';
            info.textContent = /after_or_equal|must be a date/.test(e.message) ? 'The "to" date must be on or after the "from" date.' : 'Couldn\'t apply the filters — please try again.';
            setTimeout(function () { info.style.color = ''; }, 4000);
        });
}
function mmListFilterReset(btn) {
    var bar = btn.closest('.mm-lf');
    bar.querySelectorAll('select, input').forEach(function (x) { if (!x.disabled) x.value = ''; });
    bar.querySelector('.mm-lf-info').textContent = '';
    bar.__active = '';
    var table = mmLfScope(bar).querySelector('table.dtbl');
    if (bar.__orig !== undefined && table && table.tBodies[0]) table.tBodies[0].innerHTML = bar.__orig;
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

/* ── Part 5 (Nov 2026): animations, sliding indicators, applied-filter chips and the sidebar card ──
   The existing functions are wrapped, not changed: each still runs exactly as before and these only
   add the motion on top. Filter chips read the filters that are already applied and remove them
   through the same controls (form fields, segment cards, Plan / number menus, sort). */
(function () {
    var REDUCE = !!(window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches);
    var READY = false; // no open/close animations while the page is still setting itself up
    var EASE = 'cubic-bezier(.22,.8,.26,1)';
    var root = document.getElementById('bhRoot');
    if (!root) return;

    function wrap(name, after, before) {
        var orig = window[name];
        if (typeof orig !== 'function') return;
        window[name] = function () {
            if (before) { try { before.apply(this, arguments); } catch (e) {} }
            var r = orig.apply(this, arguments);
            if (after) { try { after.apply(this, arguments); } catch (e) {} }
            return r;
        };
    }
    function replay(el, cls) { if (!el) return; el.classList.remove(cls); void el.offsetWidth; el.classList.add(cls); }
    /* First placement of a sliding indicator happens without its transition. */
    function place(el, fresh, apply) {
        if (fresh) el.style.transition = 'none';
        apply();
        if (fresh) { void el.offsetWidth; el.style.transition = ''; }
    }

    /* Smooth open / close of a block that the existing code shows and hides. */
    function slide(el, open, apply) {
        if (!el || REDUCE || !READY || !el.animate) { apply(); return; }
        if (open) {
            apply();
            var h = el.scrollHeight;
            if (!h) return;
            el.style.overflow = 'hidden';
            var a = el.animate([{ height: '0px', opacity: 0 }, { height: h + 'px', opacity: 1 }], { duration: 380, easing: EASE });
            a.onfinish = a.oncancel = function () { el.style.overflow = ''; };
        } else {
            var h2 = el.offsetHeight;
            if (!h2) { apply(); return; }
            el.style.overflow = 'hidden';
            var b = el.animate([{ height: h2 + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 300, easing: EASE, fill: 'forwards' });
            b.onfinish = function () { apply(); b.cancel(); el.style.overflow = ''; };
        }
    }

    /* Numbers count up to their value, keeping symbols: "61,900", "$5,478", "45%", "58.6". */
    function countUp(el, text) {
        text = String(text);
        var m = text.match(/^([^\d]*?)(\d[\d,]*(?:\.\d+)?)(.*)$/);
        if (!m || REDUCE) { el.textContent = text; return; }
        var to = parseFloat(m[2].replace(/,/g, '')), dec = (m[2].split('.')[1] || '').length, grp = m[2].indexOf(',') > -1;
        var t0 = performance.now(), run = (el.__mmxRun = {});
        (function f(t) {
            if (el.__mmxRun !== run) return;
            var p = Math.min(1, (t - t0) / 1000), v = to * (1 - Math.pow(1 - p, 3));
            el.textContent = p < 1 ? m[1] + v.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec, useGrouping: grp }) + m[3] : text;
            if (p < 1) requestAnimationFrame(f);
        })(t0);
    }

    /* ── Header: Table | Board pill ── */
    function vtPill() {
        var tg = document.getElementById('mmViewTg');
        if (!tg) return;
        var pill = tg.querySelector('.mmx-vt-pill'), fresh = !pill;
        if (fresh) { pill = document.createElement('span'); pill.className = 'mmx-vt-pill'; tg.insertBefore(pill, tg.firstChild); tg.classList.add('mmx-pilled'); }
        var on = tg.querySelector('button.on');
        if (on) place(pill, fresh, function () { pill.style.width = on.offsetWidth + 'px'; pill.style.transform = 'translateX(' + on.offsetLeft + 'px)'; });
    }
    wrap('mmViewSet', vtPill);

    /* ── Agent tabs: sliding background and a live count on each tab ── */
    function atSlider() {
        var tabs = root.querySelector('.atabs');
        if (!tabs) return;
        var s = tabs.querySelector('.mmx-at-slider'), fresh = !s;
        if (fresh) { s = document.createElement('span'); s.className = 'mmx-at-slider'; tabs.insertBefore(s, tabs.firstChild); tabs.classList.add('mmx-slid'); }
        var on = tabs.querySelector('.atab.on');
        if (on) place(s, fresh, function () { s.style.width = on.offsetWidth + 'px'; s.style.transform = 'translateX(' + on.offsetLeft + 'px)'; });
    }
    function badges(data) {
        ['mk', 'sl', 'ch'].forEach(function (a) {
            var tab = document.getElementById('mmAgentTab-' + a), c = data[a] && data[a][0];
            if (!tab || !c) return;
            var b = tab.querySelector('.mmx-ab');
            if (!b) { b = document.createElement('span'); b.className = 'mmx-ab'; tab.appendChild(b); }
            b.textContent = c.value;
            b.title = c.label;
        });
        atSlider();
    }

    /* ── Step tabs: sliding underline ── */
    function moveUl(vt) {
        if (!vt) return;
        var ul = vt.querySelector('.mmx-ul'), fresh = !ul;
        if (fresh) { ul = document.createElement('span'); ul.className = 'mmx-ul'; vt.appendChild(ul); vt.classList.add('mmx-ul-on'); }
        var on = vt.querySelector('.dvt.on');
        if (!on || !on.offsetWidth) { ul.style.width = '0px'; return; }
        place(ul, fresh, function () {
            ul.style.width = on.offsetWidth + 'px';
            ul.style.transform = 'translate(' + on.offsetLeft + 'px,' + (on.offsetTop + on.offsetHeight - vt.clientHeight) + 'px)';
        });
    }
    function moveAllUl() { root.querySelectorAll('.dash.on .dash-vtabs').forEach(moveUl); }

    /* ── Steps on the left: short subtitle, ticks on earlier steps, progress bar ── */
    var STEP_SUBS = {
        'mk-campaign': 'Pick who to contact', 'mk-performance': 'Headline numbers', 'mk-audience': 'Who your students are',
        'mk-insights': 'Patterns worth acting on', 'mk-abtest': 'Which message wins',
        'sl-today': 'Who to call first', 'sl-accounts': 'All prospects', 'sl-scripts': 'What to say',
        'sl-objections': 'Handle push-back', 'sl-close': 'Ready to buy',
        'ch-savefirst': 'Highest risk first', 'ch-rootcause': 'Why they leave', 'ch-offers': 'Save offers',
        'ch-watchlist': 'Slipping quietly', 'ch-renew': 'Ending or ended', 'ch-abtest': 'Which message wins'
    };
    function stepsDecor(dash) {
        if (!dash) return;
        var steps = dash.querySelectorAll('.flowst'), cur = -1;
        steps.forEach(function (s, i) {
            var t = s.querySelector('.flowst-t'), sub = STEP_SUBS[s.getAttribute('data-step')];
            if (t && sub && !t.querySelector('.mmx-step-sub')) {
                var sm = document.createElement('small');
                sm.className = 'mmx-step-sub';
                sm.textContent = sub;
                t.appendChild(sm);
            }
            if (s.classList.contains('cur')) cur = i;
        });
        steps.forEach(function (s, i) { s.classList.toggle('mmx-done', cur > -1 && i < cur); });
        var body = dash.querySelector('.g-body');
        if (!body || !steps.length) return;
        var p = body.querySelector('.mmx-prog');
        if (!p) { p = document.createElement('div'); p.className = 'mmx-prog'; p.innerHTML = 'Playbook progress<em><i></i></em>'; body.appendChild(p); }
        p.querySelector('i').style.width = ((cur + 1) / steps.length * 100) + '%';
    }
    wrap('mmSelectStep', function (agent) {
        var dash = document.getElementById('mmDash-' + agent);
        stepsDecor(dash);
        if (dash) moveUl(dash.querySelector('.dash-vtabs'));
    });

    /* ── Overview cards: rise in, coloured bar grows, numbers count up, spark lines draw ── */
    var OV_PREV = {}, OV_FIRST = true;
    wrap('mmOvRender', function (data) {
        ['mk', 'sl', 'ch'].forEach(function (a) {
            var box = document.querySelector('#mmOverview .mmx-kpis[data-ov="' + a + '"]');
            if (!box || !data[a]) return;
            box.querySelectorAll('.mmx-kpi').forEach(function (k, i) {
                var c = data[a][i] || {};
                k.style.borderTop = '';
                k.style.setProperty('--c', MM_OV_TONES[c.tone] || MM_OV_TONES.violet);
                k.style.setProperty('--i', i);
                k.classList.add('mmx-bartop');
                var paths = k.querySelectorAll('svg path');
                if (paths[0]) paths[0].classList.add('mmx-area');
                if (paths[1]) { paths[1].setAttribute('pathLength', '1'); paths[1].classList.add('mmx-line'); }
                var v = k.querySelector('.mmx-kpi-v'), key = a + i;
                if (v) {
                    v.setAttribute('data-v', c.value);
                    if (OV_PREV[key] !== c.value) countUp(v, c.value);
                }
                OV_PREV[key] = c.value;
                if (OV_FIRST) k.classList.add('mmx-in');
            });
        });
        OV_FIRST = false;
        badges(data);
        syncCard(data);
    });
    wrap('mmSetAgent', function (agent) {
        atSlider();
        document.querySelectorAll('#mmOverview .mmx-kpis[data-ov="' + agent + '"] .mmx-kpi').forEach(function (k) {
            replay(k, 'mmx-in');
            var v = k.querySelector('.mmx-kpi-v');
            if (v && v.getAttribute('data-v')) countUp(v, v.getAttribute('data-v'));
        });
        stepsDecor(document.getElementById('mmDash-' + agent));
        requestAnimationFrame(moveAllUl);
    });

    /* ── Open / close animations: overview, segments, Ask Mira suggestions, side panels ── */
    var origCollapse = window.mmxCollapse;
    window.mmxCollapse = function (el, collapsed, prefKey) {
        if (!el) return origCollapse(el, collapsed, prefKey);
        var changing = el.classList.contains('collapsed') !== !!collapsed;
        var body = el.id === 'mmOverview'
            ? el.querySelector('.mmx-kpis[data-ov="' + root.getAttribute('data-agent') + '"]')
            : el.querySelector('.mmx-segs');
        if (!changing || !body) return origCollapse(el, collapsed, prefKey);
        if (collapsed) { var btn = el.querySelector('.mmx-collapse svg'); if (btn) btn.style.transform = 'rotate(-90deg)'; }
        slide(body, !collapsed, function () {
            origCollapse(el, collapsed, prefKey);
            var svg = el.querySelector('.mmx-collapse svg');
            if (svg) svg.style.transform = '';
        });
    };
    var origCQ = window.mmCollapseQuick, origEQ = window.mmExpandQuick;
    function quickBody() { return root.querySelector('.dash.on .dm-quick'); }
    function resizing() { return document.body.style.userSelect === 'none'; } // a panel edge is being dragged
    window.mmCollapseQuick = function () {
        var q = quickBody();
        if (!q || resizing() || q.classList.contains('dm-quick-collapsed')) return origCQ();
        slide(q, false, origCQ);
    };
    window.mmExpandQuick = function () {
        var q = quickBody();
        if (!q || resizing() || !q.classList.contains('dm-quick-collapsed')) return origEQ();
        slide(q, true, origEQ);
    };
    function gridAnim() {
        if (REDUCE) return;
        root.classList.add('mmx-gridanim');
        clearTimeout(gridAnim.t);
        gridAnim.t = setTimeout(function () { root.classList.remove('mmx-gridanim'); moveAllUl(); }, 450);
    }
    wrap('mmMira', null, gridAnim);
    wrap('mmLeft', null, gridAnim);

    /* ── Quick segments: rise in once, counts tick to new values, ripple where you click ── */
    var SEG_PREV = {}, SEG_FIRST = true, SEG_RIP = null;
    document.addEventListener('pointerdown', function (e) {
        var b = e.target.closest && e.target.closest('#mmSegs .mmx-seg');
        if (!b) return;
        var i = Array.prototype.indexOf.call(b.parentNode.children, b), r = b.getBoundingClientRect();
        SEG_RIP = { i: i, x: e.clientX - r.left, y: e.clientY - r.top, d: Math.max(r.width, r.height) };
    }, true);
    wrap('mmSegRender', function () {
        var box = document.getElementById('mmSegs');
        if (!box) return;
        box.classList.toggle('mmx-in', SEG_FIRST);
        SEG_FIRST = false;
        box.querySelectorAll('.mmx-seg').forEach(function (b, i) {
            var g = MM_SEGMENTS[i], n = b.querySelector('.mmx-seg-n');
            if (!g || !n) return;
            var txt = n.textContent;
            if (SEG_PREV[g.k] !== undefined && SEG_PREV[g.k] !== txt) countUp(n, txt);
            SEG_PREV[g.k] = txt;
            if (SEG_RIP && SEG_RIP.i === i && !REDUCE) {
                var sp = document.createElement('span');
                sp.className = 'mmx-rip';
                sp.style.cssText = 'width:' + SEG_RIP.d + 'px;height:' + SEG_RIP.d + 'px;left:' + (SEG_RIP.x - SEG_RIP.d / 2) + 'px;top:' + (SEG_RIP.y - SEG_RIP.d / 2) + 'px';
                b.appendChild(sp);
                setTimeout(function () { sp.remove(); }, 650);
            }
        });
        SEG_RIP = null;
    });

    /* ── Ask Mira: typing dots while it thinks, answer fades in ── */
    function watchChat(chat) {
        new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                m.addedNodes.forEach(function (n) {
                    if (n.nodeType === 1 && n.classList.contains('msg') && n.classList.contains('bot') && n.textContent.trim() === 'Thinking…') {
                        n.innerHTML = '<span class="mmx-typing" role="status" aria-label="Thinking…"><i></i><i></i><i></i></span>';
                    }
                });
                var t = m.target;
                if (t !== chat && t.classList && t.classList.contains('msg') && m.addedNodes.length && !t.querySelector('.mmx-typing')) replay(t, 'mmx-reveal');
            });
        }).observe(chat, { childList: true, subtree: true });
    }

    /* ── Applied-filter chips above each table ── */
    var CAMP_APPLIED = new URLSearchParams(location.search);
    function fmtDate(v) {
        var d = new Date(v + 'T00:00:00');
        return isNaN(d) ? v : d.toLocaleDateString('en-AU', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function thLabel(th) { return ((th.childNodes[0] && th.childNodes[0].textContent) || th.textContent).trim(); }
    function rangeText(f) {
        var lo = f.min !== null && f.min !== undefined, hi = f.max !== null && f.max !== undefined;
        return lo && hi ? f.min + ' to ' + f.max : (lo ? '≥ ' + f.min : '≤ ' + f.max);
    }
    function menuBtn(th, num) { return th && th.querySelector(num ? '.mm-nf-btn' : '.mm-pf-btn:not(.mm-nf-btn)'); }
    function isCampaign(table) { return !!table.querySelector('#mmCampaignBody'); }
    function listBar(table) {
        var scope = table.closest('.mm-scr-list') || table.closest('.mm-panel');
        return scope && scope.querySelector('.mm-lf');
    }
    function barBtn(bar, fn) { return bar.querySelector('button[onclick^="' + fn + '"]'); }

    function chipsFor(table) {
        var out = [], p = mmPfParts(table);
        if (!p) return out;
        var camp = isCampaign(table), bar = camp ? null : listBar(table);
        if (camp) {
            var form = document.getElementById('mmCampaignFilter');
            [['subscription', 'Subscription'], ['from', 'Payment from'], ['to', 'Payment to']].forEach(function (f) {
                var v = CAMP_APPLIED.get(f[0]);
                if (!v) return;
                out.push({ k: f[0] + v, label: f[1], value: f[0] === 'subscription' ? v : fmtDate(v),
                    edit: function () { var el = form.elements[f[0]]; if (el) el.focus(); },
                    clear: function () { var el = form.elements[f[0]]; if (el) el.value = ''; mmCampaignGo(1); } });
            });
            if (MM_CAMPAIGN_SEGMENT && MM_CAMPAIGN_SEGMENT !== 'all') {
                out.push({ k: 'seg' + MM_CAMPAIGN_SEGMENT, label: 'Segment', value: mmSegLabel(MM_CAMPAIGN_SEGMENT),
                    edit: function () {
                        var w = document.getElementById('mmSegWrap');
                        if (w.classList.contains('collapsed')) mmSegToggle();
                        w.scrollIntoView({ behavior: REDUCE ? 'auto' : 'smooth', block: 'nearest' });
                    },
                    clear: function () { mmSegPick(MM_CAMPAIGN_SEGMENT); } }); // picking the active card again clears it
            }
            if (MM_CAMPAIGN_SORT.key) {
                var sth = table.querySelector('th[data-sort-key="' + MM_CAMPAIGN_SORT.key + '"]');
                out.push({ k: 'sort' + MM_CAMPAIGN_SORT.key + MM_CAMPAIGN_SORT.dir, label: 'Sorted', value: (sth ? thLabel(sth) : MM_CAMPAIGN_SORT.key) + (MM_CAMPAIGN_SORT.dir === 'desc' ? ' ↓' : ' ↑'),
                    edit: function () { var b = menuBtn(sth, true); if (b) b.click(); },
                    clear: function () { MM_CAMPAIGN_SORT.key = null; MM_CAMPAIGN_SORT.dir = 'asc'; mmCampaignSortIndicators(); mmCampaignGo(1); } });
            }
            if (typeof MM_CAMPAIGN_CF !== 'undefined') Object.keys(MM_CAMPAIGN_CF).forEach(function (k) { // column dropdowns (Part 8)
                var v = MM_CAMPAIGN_CF[k];
                if (!v) return;
                out.push({ k: 'cf' + k + v, label: MMX_CF[k].label, value: mmxCfLabel(k, v),
                    edit: function () { var b = table.querySelector('th[data-mm-col-filter="' + k + '"] .mmx-cf-btn'); if (b) b.click(); },
                    clear: function () { mmxCfSet(k, null); } });
            });
        } else if (bar && bar.__active) {
            var lp = new URLSearchParams(bar.__active);
            var sub = bar.getAttribute('data-sub-label') || 'Subscription', dl = bar.getAttribute('data-date-label') || 'Date';
            [['subscription', sub], ['from', dl + ' from'], ['to', dl + ' to']].forEach(function (f) {
                var v = lp.get(f[0]);
                if (!v) return;
                out.push({ k: f[0] + v, label: f[1], value: f[0] === 'subscription' ? v : fmtDate(v),
                    edit: function () { var el = bar.querySelector('[name="' + f[0] + '"]'); if (el) el.focus(); },
                    clear: function () { var el = bar.querySelector('[name="' + f[0] + '"]'); if (el) el.value = ''; mmListFilterApply(barBtn(bar, 'mmListFilterApply')); } });
            });
        }
        var pv = table.getAttribute('data-plan-filter');
        if (pv && pv !== 'all' && p.th) {
            out.push({ k: 'plan' + pv, label: 'Plan', value: mmPfLabel(pv),
                edit: function () { var b = menuBtn(p.th, false); if (b) b.click(); },
                clear: function () {
                    table.setAttribute('data-plan-filter', 'all');
                    mmPlanFilterApply(table);
                    if (camp) { MM_CAMPAIGN_PLAN = null; mmCampaignGo(1); }
                } });
        }
        var num = table.__mmNum || {};
        Object.keys(num).forEach(function (idx) {
            var f = num[idx], th = p.headRow.children[idx];
            if (!mmNumActive(f) || !th) return;
            out.push({ k: 'num' + idx + rangeText(f), label: thLabel(th), value: rangeText(f),
                edit: function () { var b = menuBtn(th, true); if (b) b.click(); },
                clear: function () { delete table.__mmNum[idx]; mmPlanFilterApply(table); } });
        });
        if (table.__mmSort) {
            var s = table.__mmSort, sth2 = p.headRow.children[s.idx];
            out.push({ k: 'csort' + s.idx + s.dir, label: 'Sorted', value: (sth2 ? thLabel(sth2) : '') + (s.dir === 'desc' ? ' ↓' : ' ↑'),
                edit: function () { var b = menuBtn(sth2, true); if (b) b.click(); },
                clear: function () { table.__mmSort = null; mmPlanFilterApply(table); } });
        }
        return out;
    }
    function clearAll(table) {
        if (isCampaign(table)) {
            table.__mmNum = {};
            mmCampaignReset();
            mmPlanFilterApply(table);
            return;
        }
        var bar = listBar(table);
        if (bar && bar.__active) mmListFilterReset(barBtn(bar, 'mmListFilterReset'));
        table.setAttribute('data-plan-filter', 'all');
        table.__mmNum = {};
        table.__mmSort = null;
        mmPlanFilterApply(table);
    }
    function chipsHost(table) {
        if (table.__mmxChips) return table.__mmxChips;
        var anchor = isCampaign(table) ? document.getElementById('mmCampaignFilter') : listBar(table);
        var box = document.createElement('div');
        box.className = 'mmx-chips';
        box.hidden = true;
        box.setAttribute('aria-label', 'Applied filters');
        if (anchor && (anchor.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING)) anchor.after(box);
        else table.before(box);
        box.__keys = {};
        table.__mmxChips = box;
        return box;
    }
    function chipsRefresh(table) {
        if (!table || !table.closest('#bhRoot .mm-panel')) return;
        var list = chipsFor(table);
        var box = list.length ? chipsHost(table) : table.__mmxChips;
        if (!box) return;
        var keys = {};
        box.innerHTML = list.length ? '<span class="mmx-chips-l">Filters</span>' : '';
        list.forEach(function (c) {
            keys[c.k] = 1;
            var el = document.createElement('span');
            el.className = 'mmx-chip' + (box.__keys[c.k] ? '' : ' new');
            el.innerHTML = '<button type="button" class="mmx-chip-e" title="Change this filter">' + mmEsc(c.label) + ': <b>' + mmEsc(c.value) + '</b></button>' +
                '<button type="button" class="mmx-chip-x" title="Remove" aria-label="Remove ' + mmEsc(c.label) + ' filter">×</button>';
            el.querySelector('.mmx-chip-e').onclick = function () { c.edit(); };
            el.querySelector('.mmx-chip-x').onclick = function () { c.clear(); };
            box.appendChild(el);
        });
        if (list.length > 1) {
            var all = document.createElement('button');
            all.type = 'button';
            all.className = 'mmx-chip all' + (box.__keys.__all ? '' : ' new');
            all.textContent = 'Clear all';
            all.onclick = function () { clearAll(table); };
            box.appendChild(all);
            keys.__all = 1;
        }
        box.__keys = keys;
        box.hidden = !list.length;
    }
    function campaignTable() { var b = document.getElementById('mmCampaignBody'); return b && b.closest('table'); }
    wrap('mmPlanFilterApply', function (table) { chipsRefresh(table); });
    wrap('mmCampaignLoad', function () { chipsRefresh(campaignTable()); }, function (query) { CAMP_APPLIED = new URLSearchParams(query || ''); });
    wrap('mmListFilterReset', function (btn) {
        var bar = btn && btn.closest('.mm-lf'), scope = bar && mmLfScope(bar);
        chipsRefresh(scope && scope.querySelector('table.dtbl'));
    });
    function watchTable(table) {
        var t = null;
        new MutationObserver(function () { clearTimeout(t); t = setTimeout(function () { chipsRefresh(table); }, 60); })
            .observe(table, { childList: true, subtree: true });
    }

    /* ── Sidebar card (this page only): data sources, progress bar, last sync ── */
    var SYNC_AT = null, DATA_TO = null;
    function syncCard(data) {
        SYNC_AT = data.lastSync ? new Date(data.lastSync) : null;
        DATA_TO = data.dataTo ? new Date(data.dataTo) : null;
        syncText();
    }
    function ago(d) {
        var m = Math.round((Date.now() - d) / 60000);
        if (m < 1) return 'just now';
        if (m < 60) return m + ' min ago';
        var h = Math.round(m / 60);
        if (h < 24) return h + ' h ago';
        var days = Math.round(h / 24);
        return days + ' day' + (days > 1 ? 's' : '') + ' ago';
    }
    function syncText() {
        var el = document.getElementById('mmxLastSync');
        if (!el) return;
        var t = SYNC_AT && !isNaN(SYNC_AT) ? 'Last sync ' + ago(SYNC_AT)
            : (DATA_TO && !isNaN(DATA_TO) ? 'Data up to ' + DATA_TO.toLocaleDateString('en-AU', { day: 'numeric', month: 'short' }) : '');
        el.textContent = t ? (el.previousElementSibling.textContent ? ' · ' : '') + t : '';
    }
    setInterval(syncText, 30000);
    function sidebarDecor() {
        var sb = document.getElementById('platformSidebar');
        if (!sb) return;
        var host = Array.prototype.find.call(sb.children, function (c) { return /data sources connected/.test(c.textContent); });
        if (host && !host.querySelector('.mmx-src')) {
            var m = host.textContent.match(/(\d+)\s+of\s+(\d+)\s+data sources/);
            var n = m ? +m[1] : 0, tot = m ? +m[2] : 5;
            var comp = host.querySelector('p'), link = host.querySelector('a');
            var card = document.createElement('div');
            card.className = 'mmx-src';
            card.innerHTML = '<div class="mmx-src-t"><b>' + n + ' of ' + tot + ' data sources</b> connected</div>' +
                '<div class="mmx-src-bar"><i style="width:' + Math.round(n / (tot || 1) * 100) + '%"></i></div>' +
                '<div class="mmx-src-s"><span>' + mmEsc(comp ? comp.textContent.trim() : '') + '</span><span id="mmxLastSync"></span></div>' +
                (link ? '<a href="' + mmEsc(link.getAttribute('href')) + '">' + mmEsc(link.textContent.trim()) + '</a>' : '');
            host.classList.add('mmx-src-host');
            host.appendChild(card);
            syncText();
        }
        // Groups close smoothly too (opening is animated in CSS). The layout's own toggle still does the work.
        ['Email', 'Reports', 'Arch', 'Revenue', 'Mockmaster', 'Success', 'Alerts'].forEach(function (g) {
            var name = 'toggle' + g + 'Menu', orig = window[name];
            if (typeof orig !== 'function' || orig.__mmx) return;
            var id = g.charAt(0).toLowerCase() + g.slice(1) + 'Submenu';
            var fn = function () {
                var sub = document.getElementById(id);
                if (!sub || sub.classList.contains('hidden') || REDUCE) return orig.apply(this, arguments);
                var chev = document.getElementById(id.replace('Submenu', 'Chevron'));
                if (chev) chev.classList.remove('rotate-180');
                slide(sub, false, function () { orig(); });
            };
            fn.__mmx = true;
            window[name] = fn;
        });
    }

    function init() {
        vtPill();
        atSlider();
        root.querySelectorAll('.dash').forEach(stepsDecor);
        moveAllUl();
        if (window.ResizeObserver) {
            var ro = new ResizeObserver(function (entries) {
                entries.forEach(function (e) { if (e.target.classList.contains('dash-vtabs')) moveUl(e.target); else atSlider(); });
            });
            root.querySelectorAll('.dash-vtabs, .atabs').forEach(function (el) { ro.observe(el); });
        }
        root.querySelectorAll('.dm-chat').forEach(watchChat);
        root.querySelectorAll('.mm-panel table.dtbl').forEach(function (t) { watchTable(t); chipsRefresh(t); });
        sidebarDecor();
        window.addEventListener('resize', vtPill);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { vtPill(); atSlider(); moveAllUl(); });
        setTimeout(function () { READY = true; }, 400);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

/* ── Part 7 (Nov 2026): redesigned Performance / Audience / Insights, Scripts / Objections, Root cause / Offers ──
   Open / close cards, plus charts and live counts that load the first time their tab is opened
   (fresh from the mm_* tables, so they follow Sync Data). Clicking a card, bar or highlight opens the
   same details window as before (mmKpiOpen). */
var MM_TAB_URL = '{{ route('client.mock-master-helper.tab-data', ['tab' => '__TAB__']) }}';
var MM_TAB_DONE = {};

function mmxAccToggle(e, hd) {
    if (e && e.target.closest('button')) return; // Copy and other buttons in the header keep their own job
    var card = hd.closest('.mmx-acc');
    var open = !card.classList.contains('open');
    card.classList.toggle('open', open);
    hd.setAttribute('aria-expanded', open ? 'true' : 'false');
}
document.addEventListener('keydown', function (e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('mmx-acc-hd')) {
        e.preventDefault();
        mmxAccToggle(null, e.target);
    }
});
/* Any element with data-kpi-key opens that details list (keys can hold spaces, so no inline JS). */
document.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('#bhRoot [data-kpi-key]');
    if (el) mmKpiOpen(el.getAttribute('data-kpi-key'), el.getAttribute('data-kpi-label') || '');
});
function mmxKpiAttrs(key, label) {
    return key ? ' data-kpi-key="' + mmEsc(key) + '" data-kpi-label="' + mmEsc(label) + '" title="Click to see the list"' : ' disabled';
}

function mmxTabLoad(tab) {
    var boxes = document.querySelectorAll('[data-tab-data="' + tab + '"]');
    if (MM_TAB_DONE[tab] || !boxes.length) return;
    MM_TAB_DONE[tab] = true;
    fetch(MM_TAB_URL.replace('__TAB__', tab), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
        .then(function (d) { boxes.forEach(function (b) { MMX_TAB_RENDER[tab](b, d); }); })
        .catch(function () {
            MM_TAB_DONE[tab] = false; // opening the tab again retries
            var msg = 'Couldn\'t load — open this tab again to retry.';
            boxes.forEach(function (b) {
                if (tab === 'offers') b.querySelectorAll('[data-offer-meta]').forEach(function (m) { m.textContent = msg; });
                else b.innerHTML = '<div class="mmx-td-wait">' + msg + '</div>';
            });
        });
}
(function () {
    var orig = window.mmSelectStep;
    window.mmSelectStep = function (agent, key) {
        var r = orig.apply(this, arguments);
        var tab = { 'mk-performance': 'performance', 'mk-audience': 'audience', 'ch-offers': 'offers' }[agent + '-' + key];
        if (tab) mmxTabLoad(tab);
        return r;
    };
})();

function mmxBarRows(items, total, max) {
    return '<div class="mmx-bars">' + items.map(function (it, i) {
        var w = max ? Math.max(1.5, it.n / max * 100) : 0;
        var pct = total ? Math.round(it.n / total * 1000) / 10 : null;
        return '<button type="button" class="mmx-bar-row"' + mmxKpiAttrs(it.key, it.title || it.label) + '>' +
            '<span title="' + mmEsc(it.label) + '">' + mmEsc(it.label) + '</span>' +
            '<em><i style="width:' + w + '%;animation-delay:' + (i * 0.07) + 's"></i></em>' +
            '<b>' + Number(it.n).toLocaleString() + (pct !== null ? '<small>' + pct + '%</small>' : '') + '</b></button>';
    }).join('') + '</div>';
}

var MMX_TAB_RENDER = {
    /* Weekly average score (line) and tests taken (bars), section averages, highlights */
    performance: function (box, d) {
        var weeks = d.weeks || [];
        var W = 640, H = 210, pl = 34, pr = 12, pt = 14, pb = 28;
        var vals = weeks.filter(function (w) { return w.avg !== null; }).map(function (w) { return w.avg; });
        var maxC = Math.max.apply(null, weeks.map(function (w) { return w.count; }).concat([1]));
        var lo = vals.length ? Math.floor(Math.min.apply(null, vals) - 3) : 0, hi = vals.length ? Math.ceil(Math.max.apply(null, vals) + 3) : 90;
        var sx = (W - pl - pr) / Math.max(1, weeks.length - 1);
        var X = function (i) { return pl + i * sx; };
        var Y = function (v) { return pt + (H - pt - pb) * (1 - (v - lo) / ((hi - lo) || 1)); };
        var bars = '', line = '', dots = '', labels = '', prev = false;
        weeks.forEach(function (w, i) {
            var bh = (H - pt - pb) * 0.42 * w.count / maxC;
            var tip = 'Week to ' + w.label + ': ' + (w.avg !== null ? 'average ' + w.avg + ' · ' : '') + w.count.toLocaleString() + ' tests';
            bars += '<rect class="bar" x="' + (X(i) - 9).toFixed(1) + '" y="' + (H - pb - bh).toFixed(1) + '" width="18" height="' + bh.toFixed(1) + '" rx="3" style="animation-delay:' + (i * 0.05) + 's"><title>' + mmEsc(tip) + '</title></rect>';
            labels += '<text x="' + X(i).toFixed(1) + '" y="' + (H - 8) + '" text-anchor="middle">' + mmEsc(w.label) + '</text>';
            if (w.avg === null) { prev = false; return; }
            line += (prev ? 'L' : 'M') + X(i).toFixed(1) + ' ' + Y(w.avg).toFixed(1) + ' ';
            prev = true;
            dots += '<circle cx="' + X(i).toFixed(1) + '" cy="' + Y(w.avg).toFixed(1) + '" r="4" style="animation-delay:' + (0.9 + i * 0.07) + 's"><title>' + mmEsc(tip) + '</title></circle>';
        });
        var area = vals.length === weeks.length && weeks.length > 1
            ? '<path class="a" d="' + line + 'L' + X(weeks.length - 1).toFixed(1) + ' ' + (H - pb) + ' L' + X(0).toFixed(1) + ' ' + (H - pb) + ' Z"/>' : '';
        var chart = '<svg viewBox="0 0 ' + W + ' ' + H + '" role="img" aria-label="Average overall score and tests taken per week">' +
            '<text x="0" y="' + (Y(hi) + 4).toFixed(1) + '">' + hi + '</text><text x="0" y="' + (Y(lo) + 4).toFixed(1) + '">' + lo + '</text>' +
            bars + area + (line ? '<path class="l" pathLength="1" d="' + line + '"/>' : '') + dots + labels + '</svg>';

        var sec = d.sections || {}, names = Object.keys(sec);
        var secHtml = names.length
            ? mmxBarRows(names.map(function (k) { return { label: k, n: sec[k], key: 'avg_score', title: 'Avg Overall Score' }; }), 0, 90)
            : '<div class="mmx-td-wait" style="padding:6px 0">No section scores in the last 90 days.</div>';
        var hl = (d.highlights || []).map(function (h, i) {
            return '<button type="button"' + (h.bad ? ' class="bad"' : '') + mmxKpiAttrs(h.key, h.text) + ' style="animation-delay:' + (i * 0.08) + 's">' + mmEsc(h.text) + '<small>' + mmEsc(h.sub || '') + '</small></button>';
        }).join('');

        box.innerHTML =
            '<div class="mmx-panel mmx-chart"' + mmxKpiAttrs('mock_tests', 'Mock Tests Taken') + '><h3>Average overall score, week by week<span>last 8 weeks · to ' + mmEsc(d.asOf || '') + '</span></h3>' + chart +
                '<div class="mmx-chart-k"><span><i></i>Average overall score</span><span><i class="b"></i>Mock tests taken</span></div></div>' +
            '<div class="mmx-grid2">' +
                '<div class="mmx-panel" style="animation-delay:.08s"><h3>Section averages<span>last 90 days · out of 90</span></h3>' + secHtml + '</div>' +
                '<div class="mmx-panel" style="animation-delay:.16s"><h3>Highlights<span>click for the list</span></h3><div class="mmx-hl">' + hl + '</div></div>' +
            '</div>';
    },
    /* Breakdown bars: course, package status, readiness, sign-ups by month */
    audience: function (box, d) {
        box.innerHTML = '<div class="mmx-grid2" style="margin-top:0">' + (d.groups || []).map(function (g, gi) {
            var max = Math.max.apply(null, g.items.map(function (it) { return it.n; }).concat([0]));
            var items = g.items.map(function (it) { return { label: it.label, n: it.n, key: it.key, title: g.title + ' · ' + it.label }; });
            return '<div class="mmx-panel" style="animation-delay:' + (gi * 0.07) + 's"><h3>' + mmEsc(g.title) + '<span>' + mmEsc(g.sub || '') + '</span></h3>' +
                mmxBarRows(items, g.title === 'New sign-ups by month' ? 0 : d.total, max) + '</div>';
        }).join('') + '</div>';
    },
    /* Live count and audience for each offer card */
    offers: function (box, d) {
        box.querySelectorAll('.mmx-offer').forEach(function (card) {
            var o = d[card.getAttribute('data-offer')];
            if (!o) return;
            card.querySelector('[data-offer-n]').textContent = Number(o.count).toLocaleString();
            card.querySelector('[data-offer-meta]').textContent = Number(o.count).toLocaleString() + ' students · ' + o.meta;
            card.querySelector('[data-offer-n2]').textContent = '(' + Number(o.count).toLocaleString() + ')';
        });
    }
};

/* ── Part 8 (Nov 2026) ─────────────────────────────────────────────────────────────
   1. Dropdown filters on the Campaign columns Payment date, Expires, Stage, Approach, Last active and
      Next best action. Like the Plan dropdown they run on the server across every student (so paging,
      the Board, Send to all and the segment counts follow them), and they show as chips above the table.
   2. Maximise / minimise button on every popup that lists students or records. It stays a popup. */
var MMX_CF = {
    payment: { label: 'Payment date', opts: [['7d', 'Last 7 days'], ['30d', 'Last 30 days'], ['90d', 'Last 90 days'], ['older', 'Older than 90 days'], ['none', 'No payment linked']] },
    expires: { label: 'Expires', opts: [['expired', 'Expired', 'Already ended'], ['week', 'Within 7 days', '0 to 7 days left'], ['month', '8 to 30 days'], ['quarter', '31 to 90 days'], ['later', 'More than 90 days'], ['none', 'No end date']] },
    stage: { label: 'Stage', opts: [['Active', 'Active'], ['Renewal Due', 'Renewal Due', 'Package ends within 14 days'], ['Expired', 'Expired']] },
    approach: { label: 'Approach', opts: [['Proof-led', 'Proof-led', 'Trust below 65'], ['Offer-led', 'Offer-led', 'Trust 65 or more']] },
    last: { label: 'Last active', opts: [['week', 'This week', 'Logged in within 7 days'], ['month', '8 to 30 days ago'], ['quarter', '31 to 90 days ago'], ['older', 'More than 90 days ago'], ['never', 'Never logged in']] },
    nba: { label: 'Next best action', opts: [['Win back', 'Win back'], ['Renew now', 'Renew now'], ['Renewal reminder', 'Renewal reminder'], ['Re-engage', 'Re-engage'], ['Book a mock test', 'Book a mock test'], ['Lead with proof', 'Lead with proof'], ['Lead with offer', 'Lead with offer']] }
};
var MM_CAMPAIGN_CF = (function () {
    var q = new URLSearchParams(location.search), out = {};
    Object.keys(MMX_CF).forEach(function (k) {
        var v = q.get('f_' + k);
        out[k] = MMX_CF[k].opts.some(function (o) { return o[0] === v; }) ? v : null;
    });
    return out;
})();
var MMX_CF_MENU = null;
function mmxCfLabel(k, v) {
    var o = MMX_CF[k] && MMX_CF[k].opts.find(function (x) { return x[0] === v; });
    return o ? o[1] : v;
}
function mmxCfButtons() {
    document.querySelectorAll('th[data-mm-col-filter]').forEach(function (th) {
        var k = th.getAttribute('data-mm-col-filter'), btn = th.querySelector('.mmx-cf-btn');
        if (!btn) {
            btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mmx-cf-btn';
            btn.setAttribute('aria-haspopup', 'listbox');
            btn.setAttribute('aria-expanded', 'false');
            btn.innerHTML = MM_PF_CHEVRON;
            th.appendChild(btn);
        }
        var v = MM_CAMPAIGN_CF[k];
        btn.classList.toggle('on', !!v);
        btn.title = v ? MMX_CF[k].label + ': ' + mmxCfLabel(k, v) + ' (change)' : 'Filter by ' + MMX_CF[k].label.toLowerCase();
        btn.setAttribute('aria-label', btn.title);
    });
}
function mmxCfSet(k, v) {
    MM_CAMPAIGN_CF[k] = v || null;
    mmxCfButtons();
    mmCampaignGo(1);
}
function mmxCfClearAll() {
    Object.keys(MM_CAMPAIGN_CF).forEach(function (k) { MM_CAMPAIGN_CF[k] = null; });
    mmxCfButtons();
}
function mmxCfClose() {
    if (!MMX_CF_MENU) return;
    var m = MMX_CF_MENU;
    MMX_CF_MENU = null;
    m.btn.classList.remove('open');
    m.btn.setAttribute('aria-expanded', 'false');
    m.el.classList.remove('show');
    setTimeout(function () { m.el.remove(); }, 160);
}
function mmxCfOpen(btn) {
    var k = btn.closest('th').getAttribute('data-mm-col-filter'), cur = MM_CAMPAIGN_CF[k] || '';
    var item = function (v, label, desc) {
        var sel = v === cur;
        return '<button type="button" role="option" class="mm-pf-opt' + (sel ? ' sel' : '') + '" aria-selected="' + sel + '" data-cf="' + mmEsc(v) + '">' +
            '<span class="mm-pf-txt"><b>' + mmEsc(label) + '</b>' + (desc ? '<small>' + mmEsc(desc) + '</small>' : '') + '</span>' +
            '<span class="mm-pf-check">' + (sel ? MM_PF_CHECK : '') + '</span></button>';
    };
    var el = document.createElement('div');
    el.className = 'mm-pf-menu mmx-cf-menu';
    el.setAttribute('role', 'listbox');
    el.setAttribute('aria-label', 'Filter by ' + MMX_CF[k].label);
    el.innerHTML = item('', 'Any ' + MMX_CF[k].label.toLowerCase(), 'Show every student') +
        '<div class="mm-pf-sep" role="presentation">' + mmEsc(MMX_CF[k].label) + ' · all students</div>' +
        MMX_CF[k].opts.map(function (o) { return item(o[0], o[1], o[2]); }).join('');
    document.body.appendChild(el);
    var r = btn.getBoundingClientRect(), w = el.offsetWidth, h = el.offsetHeight;
    el.style.left = Math.min(Math.max(8, r.left - 8), window.innerWidth - w - 8) + 'px';
    el.style.top = (r.bottom + 6 + h > window.innerHeight - 8 ? Math.max(8, r.top - h - 6) : r.bottom + 6) + 'px';
    MMX_CF_MENU = { el: el, btn: btn, k: k };
    btn.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    var tip = document.getElementById('mmTip');
    if (tip) tip.style.display = 'none';
    requestAnimationFrame(function () { el.classList.add('show'); });
    var first = el.querySelector('.mm-pf-opt.sel') || el.querySelector('.mm-pf-opt');
    if (first) first.focus({ preventScroll: true });
}
// Capture phase, so a click on the arrow doesn't also sort the column (same as the Plan / number arrows).
document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.mmx-cf-btn');
    if (btn) {
        e.stopPropagation();
        e.preventDefault();
        var reopen = !(MMX_CF_MENU && MMX_CF_MENU.btn === btn);
        mmxCfClose();
        if (typeof mmPlanMenuClose === 'function') mmPlanMenuClose();
        if (reopen) mmxCfOpen(btn);
        return;
    }
    var opt = e.target.closest && e.target.closest('.mmx-cf-menu .mm-pf-opt');
    if (opt && MMX_CF_MENU) {
        var k = MMX_CF_MENU.k;
        mmxCfClose();
        mmxCfSet(k, opt.getAttribute('data-cf'));
        return;
    }
    if (MMX_CF_MENU && !(e.target.closest && e.target.closest('.mmx-cf-menu'))) mmxCfClose();
}, true);
document.addEventListener('keydown', function (e) {
    if (!MMX_CF_MENU) return;
    if (e.key === 'Escape') { e.stopPropagation(); var b = MMX_CF_MENU.btn; mmxCfClose(); b.focus(); return; }
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        var opts = Array.prototype.slice.call(MMX_CF_MENU.el.querySelectorAll('.mm-pf-opt'));
        var i = opts.indexOf(document.activeElement);
        opts[(i + (e.key === 'ArrowDown' ? 1 : -1) + opts.length) % opts.length].focus();
    }
}, true);
window.addEventListener('resize', mmxCfClose);
document.addEventListener('scroll', function (e) {
    if (MMX_CF_MENU && !(e.target.closest && e.target.closest('.mmx-cf-menu'))) mmxCfClose();
}, true);

/* Maximise / minimise for the list popups: KPI and student lists (mmKpiModal), "view list" (mmListModalOverlay)
   and Ask Mira's results list (mmResultsModal). Button sits next to ✕; double-clicking the title bar works too.
   Closing the popup puts it back to normal size. */
var MMX_LIST_POPUPS = ['mmKpiModal', 'mmListModalOverlay', 'mmResultsModal'];
var MMX_MAX_ICONS = '<svg class="mmx-i-max" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>' +
    '<svg class="mmx-i-min" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>';
function mmxPopupMax(box, on) {
    on = on === undefined ? !box.classList.contains('mmx-max') : on;
    box.classList.toggle('mmx-max', on);
    var b = box.querySelector('.mmx-max-btn');
    if (b) { b.title = on ? 'Minimise' : 'Maximise'; b.setAttribute('aria-label', b.title); }
    // The KPI / student list pages 10 rows at a time; when maximised it shows as many rows as fit, then back to 10.
    if (box.closest('#mmKpiModal')) {
        if (MM_KPI_PAGE_SIZE !== 10) { MM_KPI_PAGE_SIZE = 10; if (MM_KPI_DATA) mmKpiRender(MM_KPI_DATA, 1); }
        if (on) setTimeout(function () { mmxKpiFit(box); }, 330); // after the size animation
    }
}
function mmxKpiFit(box) {
    if (!box.classList.contains('mmx-max') || MM_KPI_PAGE_SIZE !== 10) return;
    var body = document.getElementById('mmKpiBody'), row = body && body.querySelector('tbody tr');
    if (!row || !MM_KPI_DATA) return;
    // Rows that fit in the space left under the 10-row page.
    var used = Array.prototype.reduce.call(body.children, function (s, c) { return s + c.offsetHeight; }, 0);
    var cs = getComputedStyle(body), free = body.clientHeight - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom) - used - 4;
    var extra = Math.max(0, Math.floor(free / Math.max(30, row.offsetHeight)));
    if (extra) { MM_KPI_PAGE_SIZE = 10 + extra; mmKpiRender(MM_KPI_DATA, 1); }
}
function mmxPopupsCheck() {
    MMX_LIST_POPUPS.forEach(function (id) {
        var ov = document.getElementById(id);
        if (!ov) return;
        var box = ov.querySelector('.risk-modal, .mm-list-modal'), hd = box && box.querySelector('.risk-modal-hd, .mm-list-modal-hd');
        if (!hd) return;
        if (!hd.querySelector('.mmx-max-btn')) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'mmx-max-btn';
            b.title = 'Maximise';
            b.setAttribute('aria-label', 'Maximise');
            b.innerHTML = MMX_MAX_ICONS;
            b.addEventListener('click', function (e) { e.stopPropagation(); mmxPopupMax(box); });
            var close = hd.querySelector('button:not(.mmx-max-btn)');
            hd.insertBefore(b, close || null);
            hd.addEventListener('dblclick', function (e) { if (!e.target.closest('button')) mmxPopupMax(box); });
        }
        if (!ov.classList.contains('show') && box.classList.contains('mmx-max')) mmxPopupMax(box, false); // closed → normal size next time
    });
}
// Popups open and close on clicks and Esc; check right after each.
document.addEventListener('click', function () { requestAnimationFrame(mmxPopupsCheck); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') requestAnimationFrame(mmxPopupsCheck); });

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { mmxCfButtons(); mmxPopupsCheck(); });
else { mmxCfButtons(); mmxPopupsCheck(); }

/* ── Part 9 (Nov 2026): column filters inside the list popups ──
   ▾ on every column of the details / student list, the "View the list" popup and Mira's results list
   (shared code: client.partials.popup-table-filter). They filter the rows the popup has loaded;
   reopening a popup starts with no filters. */
var MMX_KPI_TF = {};
var MMX_RES = { rows: [], tf: {} };
(function () {
    var origOpen = window.mmKpiOpen;
    window.mmKpiOpen = function () {
        MMX_KPI_TF = {};
        if (window.MMXTF) MMXTF.close();
        return origOpen.apply(this, arguments);
    };
    // Details / student list: filter all loaded rows first, then page them as before.
    var origRender = window.mmKpiRender;
    window.mmKpiRender = function (data, page) {
        if (!window.MMXTF || !data || !data.columns) return origRender.apply(this, arguments);
        var src = data.__src || data, all = src.rows || [];
        var rows = MMXTF.filterRows(all, MMX_KPI_TF, function (r, k) { return r[k]; });
        var view = Object.assign({}, src, { rows: rows, __src: src });
        if (rows.length !== all.length) view.total = rows.length; // the match count is shown above the table instead
        origRender(view, page);
        var body = document.getElementById('mmKpiBody'), table = body && body.querySelector('table');
        if (!table) return;
        var cols = src.columns.map(function (c, i) { return { idx: i + 1, key: c.key, label: c.label, values: all.map(function (r) { return r[c.key]; }) }; });
        var redraw = function () { mmKpiRender(src, 1); };
        MMXTF.decorate(table, { state: MMX_KPI_TF, columns: cols, onChange: redraw });
        MMXTF.bar(body, table, { state: MMX_KPI_TF, columns: cols, shown: rows.length, total: all.length, noun: 'loaded rows', onChange: redraw });
    };
    // "View the list" popup: every loaded row is in the table, so rows are hidden in place (Load more keeps the filters).
    var origListOpen = window.openMmListModal;
    window.openMmListModal = function (id) {
        if (MM_LIST_CACHE[id]) MM_LIST_CACHE[id].__tf = {};
        if (window.MMXTF) MMXTF.close();
        return origListOpen.apply(this, arguments);
    };
    var origList = window.mmRenderListTable;
    window.mmRenderListTable = function (entry) {
        var r = origList.apply(this, arguments);
        var table = document.querySelector('#mmListModalBody table');
        if (table && window.MMXTF) {
            entry.__tf = entry.__tf || {};
            table.__mmxTfApply = MMXTF.attachDom(table, entry.__tf);
        }
        return r;
    };
    // Its Plan dropdown also hides rows: keep the match count right after a Plan change.
    var origPfApply = window.mmPlanFilterApply;
    window.mmPlanFilterApply = function (table) {
        var r = origPfApply.apply(this, arguments);
        if (table && table.__mmxTfApply && !table.__mmxTfBusy) {
            table.__mmxTfBusy = true;
            try { table.__mmxTfApply(); } finally { table.__mmxTfBusy = false; }
        }
        return r;
    };
    // Mira's results list (payments).
    var origResults = window.mmShowResultsModal;
    window.mmShowResultsModal = function (results) {
        if (window.MMXTF) MMXTF.close();
        var r = origResults.apply(this, arguments);
        MMX_RES = { rows: (results && results.payments_list) || [], tf: {} }; // also used by its Send to all (Part 10)
        var table = document.querySelector('#mmResultsBody table');
        if (table && window.MMXTF) MMXTF.attachDom(table, MMX_RES.tf);
        return r;
    };
})();

/* ── Part 10 (Nov 2026) ───────────────────────────────────────────────────────────
   1. Board: an expand button on every column (Stage: Active / Renewal Due / Expired; Next best action:
      one per action) opens that column's students in the list popup — with the column filters,
      paging, maximise and the new Send to all.
   2. "Send to all" in every list popup (student / records list, "View the list", Mira's payments list):
      emails or WhatsApps the students that match the popup's filters, through the existing Send to all
      window. Contacts are looked up by student id (or email) so WhatsApp has the phone numbers. */
var MM_CONTACTS_URL = '{{ route('client.mock-master-helper.contacts') }}';
var MMX_AGENT_CTX = { mk: 'marketing', sl: 'prospect', ch: 'retention' };
var MMX_SEND_ICON = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>';

/* Opens rows in the list popup (#mmKpiModal) without a server call — used by the Board columns. */
function mmxShowList(title, data) {
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
    MMX_KPI_TF = {};
    if (window.MMXTF) MMXTF.close();
    MM_KPI_PAGE_SIZE = 10;
    document.getElementById('mmKpiTitle').textContent = title;
    mmKpiRender(data, 1);
    overlay.classList.add('show');
    requestAnimationFrame(function () { mmxPopupsCheck(); mmxSendBtns(); });
}

/* Board column → popup with every student in that column. */
var MMX_COL_EXPAND_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
function mmxBoardColumnOpen(name) {
    var field = MM_BOARD_GROUP === 'stage' ? 'stage' : 'nextAction';
    var list = MM_BOARD_ROWS.filter(function (s) { return s[field] === name; });
    var expires = function (s) {
        if (s.stage === 'Expired' || (s.expiresIn !== null && s.expiresIn < 0)) return 'Expired' + (s.expireDate ? ' ' + s.expireDate : '');
        return s.expireDate || '—';
    };
    mmxShowList('Board — ' + (MM_BOARD_GROUP === 'stage' ? 'Stage' : 'Next best action') + ': ' + name + ' · ' + list.length.toLocaleString() + ' students', {
        title: name,
        columns: [
            { key: 'name', label: 'Name' }, { key: 'sub', label: 'Package' }, { key: 'plan', label: 'Plan' }, { key: 'value', label: 'Value' },
            { key: 'paymentDate', label: 'Payment date' }, { key: 'expires', label: 'Expires' }, { key: 'stage', label: 'Stage' },
            { key: 'readiness', label: 'Readiness' }, { key: 'trust', label: 'Trust' }, { key: 'lastActive', label: 'Last active' }, { key: 'nextAction', label: 'Next best action' }
        ],
        rows: list.map(function (s) {
            return { sid: s.sid, email: s.email, phone: s.phone, name: s.name, sub: s.sub, plan: s.plan || '—', value: s.value, paymentDate: s.paymentDate,
                     expires: expires(s), stage: s.stage, readiness: s.readiness, trust: s.trust, lastActive: s.lastActive, nextAction: s.nextAction || '—' };
        }),
        total: list.length,
        __ctx: 'marketing'
    });
}
(function () {
    var orig = window.mmBoardRender;
    window.mmBoardRender = function () {
        var r = orig.apply(this, arguments);
        document.querySelectorAll('#mmBoardCols .mmx-col h4').forEach(function (h4) {
            if (h4.querySelector('.mmx-col-exp')) return;
            var name = (h4.childNodes[1] && h4.childNodes[1].textContent || '').trim();
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'mmx-col-exp';
            b.title = 'Open all ' + name + ' students in a list (filters, Send to all)';
            b.setAttribute('aria-label', b.title);
            b.innerHTML = MMX_COL_EXPAND_ICON;
            b.onclick = function (e) { e.stopPropagation(); mmxBoardColumnOpen(name); };
            h4.appendChild(b);
        });
        return r;
    };
})();

/* Which students a popup's Send to all goes to: its rows that pass the popup's filters. */
function mmxVisibleDataRows(table) {
    var head = Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
    return Array.prototype.filter.call(table.querySelectorAll('tr'), function (tr) {
        return tr !== head && !tr.classList.contains('mm-pf-empty') && !tr.classList.contains('mmx-tf-none') && !(tr.children.length === 1 && tr.children[0].hasAttribute('colspan'));
    });
}
function mmxDomFilterLabels(table, state) {
    var head = Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
    return Object.keys(state || {}).filter(function (k) { return MMXTF.active(state[k]); }).map(function (k) {
        var th = head && head.children[+k.slice(1)];
        var name = th ? ((th.childNodes[0] && th.childNodes[0].textContent) || th.textContent).trim() : k;
        return name + ': ' + MMXTF.label(state[k]);
    });
}
function mmxSendCtx(id) {
    var titleEl = { mmKpiModal: 'mmKpiTitle', mmListModalOverlay: 'mmListModalTitle', mmResultsModal: 'mmResultsTitle' }[id];
    var title = ((document.getElementById(titleEl) || {}).textContent || 'this list').split(' · ')[0];
    if (id === 'mmKpiModal') {
        if (!MM_KPI_DATA || !window.MMXTF) return null;
        var src = MM_KPI_DATA.__src || MM_KPI_DATA;
        if (!(src.columns || []).some(function (c) { return c.key === 'name' || c.key === 'email'; })) return null; // not a list of people
        var labels = {};
        src.columns.forEach(function (c) { labels[c.key] = c.label; });
        return {
            title: title, ctx: src.__ctx || MMX_AGENT_CTX[document.getElementById('bhRoot').getAttribute('data-agent')] || 'marketing',
            rows: MMXTF.filterRows(src.rows || [], MMX_KPI_TF, function (r, k) { return r[k]; }),
            filters: Object.keys(MMX_KPI_TF).filter(function (k) { return MMXTF.active(MMX_KPI_TF[k]); }).map(function (k) { return (labels[k] || k) + ': ' + MMXTF.label(MMX_KPI_TF[k]); })
        };
    }
    var table = document.querySelector(id === 'mmListModalOverlay' ? '#mmListModalBody table' : '#mmResultsBody table');
    if (!table) return null;
    var trs = mmxVisibleDataRows(table);
    var shown = function (r, i) { return trs[i] && !trs[i].hidden && !trs[i].classList.contains('mmx-tf-hide'); };
    if (id === 'mmListModalOverlay') {
        var e = MM_LIST_CURRENT;
        if (!e) return null;
        var f = mmxDomFilterLabels(table, e.__tf);
        var plan = table.getAttribute('data-plan-filter');
        if (plan && plan !== 'all') f.unshift('Plan: ' + mmPfLabel(plan));
        return { title: title, ctx: MM_CMP_DATASET_CTX[e.dataset] || 'marketing', rows: e.rows.filter(shown), filters: f };
    }
    return {
        title: title, ctx: 'marketing', filters: mmxDomFilterLabels(table, MMX_RES.tf),
        rows: MMX_RES.rows.filter(shown).map(function (p) { return { name: p.student, email: p.email, product: p.product, amount: '$' + Number(p.amount).toLocaleString(), paid_on: p.paid_on }; })
    };
}
function mmxSendAll(id, btn) {
    var c = mmxSendCtx(id);
    if (!c) return;
    if (!c.rows.length) { alert('Nobody matches the current filters in this list.'); return; }
    var label = btn.innerHTML;
    btn.disabled = true;
    btn.textContent = 'Loading…';
    fetch(MM_CONTACTS_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
            ids: c.rows.map(function (r) { return r.sid; }).filter(Boolean).slice(0, 3000),
            emails: c.rows.filter(function (r) { return !r.sid && r.email && r.email !== '—'; }).map(function (r) { return r.email; }).slice(0, 3000)
        })
    })
    .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
    .then(function (d) {
        var bySid = {}, byEmail = {};
        (d.contacts || []).forEach(function (ct) { bySid[ct.sid] = ct; if (ct.email) byEmail[ct.email.toLowerCase()] = ct; });
        var seen = {}, missing = 0, rows = [];
        c.rows.forEach(function (r) {
            var ct = (r.sid && bySid[r.sid]) || (r.email && byEmail[String(r.email).toLowerCase()]);
            if (!ct) { missing++; return; }
            if (seen[ct.sid]) return;
            seen[ct.sid] = true;
            var name = r.name && r.name !== '—' ? r.name : ct.name;
            var dateKey = ['expires', 'last_login', 'lastActive', 'paymentDate', 'paid_on', 'taken_on', 'joined', 'latest_on', 'last_test', 'expired_on'].find(function (k) { return r[k] && r[k] !== '—'; });
            rows.push({ id: ct.sid, name: name, email: ct.email, phone: ct.phone, group: r.plan || r.stage || r.course || r.product || '—',
                        date: dateKey ? r[dateKey] : '', dateSub: r.sub || r.product || r.package || '',
                        search: Object.keys(r).map(function (k) { return r[k]; }).join(' '), raw: Object.assign({}, r, { name: name }) });
        });
        var filters = c.filters.map(function (l) { return { label: l, test: function () { return true; } }; }); // already applied in the popup
        if (missing) filters.push({ label: missing + ' row' + (missing === 1 ? '' : 's') + ' without a student record left out', test: function () { return true; } });
        var cfg = {
            title: 'Send to all — ' + c.title,
            reportTitle: 'Mock Master — Send to all report (' + c.title + ')',
            fileBase: 'mock-master-list',
            channels: ['email', 'whatsapp'],
            groupLabel: rows.some(function (r) { return r.raw.plan; }) ? 'Plan' : 'Group',
            groupClass: rows.some(function (r) { return r.raw.plan; }) ? mmPlanClassJs : undefined,
            filters: filters,
            templates: MM_CMP_TEMPLATES[c.ctx] || MM_CMP_TEMPLATES.marketing,
            fill: function (text, r) { return mmCmpFill(text, mmCmpVars(r.raw, {})); },
            note: '{first}, {name}, {course}, {package}, {amount}, {expires} and {expired} are filled in for each student where known.',
            send: mmBulkSendBatch,
            load: function () { return rows; }
        };
        XBulk.open(cfg.groupLabel === 'Plan' ? mmBulkPlanCfg(cfg) : cfg);
        var xo = document.getElementById('xbOverlay');
        if (xo) document.body.appendChild(xo); // on top of the list popup
    })
    .catch(function () { alert('Couldn\'t load the students\' contact details — please try again.'); })
    .then(function () { btn.disabled = false; btn.innerHTML = label; });
}
/* The button sits in each list popup's title bar, before maximise; hidden for lists that aren't people. */
function mmxSendBtns() {
    ['mmKpiModal', 'mmListModalOverlay', 'mmResultsModal'].forEach(function (id) {
        var ov = document.getElementById(id), hd = ov && ov.querySelector('.risk-modal-hd, .mm-list-modal-hd');
        if (!hd) return;
        var b = hd.querySelector('.mmx-send-btn');
        if (!b) {
            b = document.createElement('button');
            b.type = 'button';
            b.className = 'mmx-send-btn';
            b.title = 'Email or WhatsApp everyone in this list who matches the column filters';
            b.innerHTML = MMX_SEND_ICON + '<span>Send to all</span>';
            b.addEventListener('click', function (e) { e.stopPropagation(); mmxSendAll(id, b); });
            hd.insertBefore(b, hd.querySelector('.mmx-max-btn') || hd.querySelector('button:not(.mmx-send-btn)'));
        }
        b.hidden = id === 'mmKpiModal' && !mmxSendCtx(id);
    });
}
document.addEventListener('click', function () { requestAnimationFrame(function () { requestAnimationFrame(mmxSendBtns); }); });
(function () {
    // Show / hide right after a details list finishes loading too (it renders after the click).
    var orig = window.mmKpiRender;
    window.mmKpiRender = function () { var r = orig.apply(this, arguments); requestAnimationFrame(mmxSendBtns); return r; };
})();
</script>
@include('client.partials.popup-table-filter')

@include('client.partials.bulk-send')
@endsection
