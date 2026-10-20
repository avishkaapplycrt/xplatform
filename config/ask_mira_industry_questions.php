<?php

/*
|--------------------------------------------------------------------------
| Ask Mira home: industry starter questions
|--------------------------------------------------------------------------
| Three questions per industry, each with Mira's answer, from the "Ask Mira
| Starter Questions" design. When a visitor picks an industry in the Ask Mira
| sidebar, its questions appear under "For <industry>"; asking one returns
| the answer below word for word (MarketingChatBotService::curatedAnswer).
|
| 'name' must match industries.name. Answers use light markdown: **bold**,
| '- ' bullet lines and blank lines between paragraphs (turned into plain
| text for the chat). 'cta' is the button shown under the answer: analyse
| (Analyse my website), premium (Mira Premium) or contact (book a demo).
*/

return [
    'industries' => [
        [
            'name'      => 'E-commerce',
            'questions' => [
                [
                    'question' => 'How do I get star ratings and prices to show on Google?',
                    'answer'   => 'Add **Product structured data** to every product page, including price, availability and review ratings. Google can then show stars, prices and stock right in the search results, which wins more clicks.

You can also list your products for free in **Google Merchant Center** so they appear in the Shopping tab.

Mira checks this under **Rich Results Eligibility**.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How can my products show up in ChatGPT shopping answers?',
                    'answer'   => 'AI shopping answers favour products with clear, complete information. To improve your chances:

- Write product descriptions with real specs: size, material, compatibility, what\'s in the box.
- Add Product structured data with price and availability.
- Collect reviews on your site and on trusted review platforms.
- Make sure your robots.txt doesn\'t block AI search crawlers like OAI-SearchBot and PerplexityBot.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'Why are visitors leaving my store without buying?',
                    'answer'   => 'The most common reasons are:

- **Slow pages**, especially on mobile
- **Surprise costs** like shipping shown only at checkout
- **Forced account creation** before paying
- **Weak product pages** with few photos or no reviews

Use the checkout funnel in Google Analytics to see where people drop off. Mira\'s check shows if speed or mobile issues are part of the problem.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'EdTech',
            'questions' => [
                [
                    'question' => 'How do I get my courses to show up on Google?',
                    'answer'   => 'Give every course its own page that clearly states who it\'s for, what students will learn, how long it takes, the price and how it\'s delivered.

Add **Course structured data** so search engines and AI tools understand your offer, and use the words students actually search for, like “online data analytics course for beginners”.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How can students find us when they ask ChatGPT for course recommendations?',
                    'answer'   => 'AI tools recommend courses they can describe with confidence. Help them by:

- Publishing clear outcomes, syllabus, duration and price on each course page
- Adding an FAQ that answers common student questions
- Getting reviews on independent platforms, not just your own site
- Being mentioned in articles and comparison lists in your field',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I turn free trial sign-ups into paying students?',
                    'answer'   => 'Most trial users decide in the first few days. Give them a **quick first win**, like a short lesson they can finish in 10 minutes, then follow up with helpful emails that point to the next step.

Track how many trial users finish their first lesson. That number usually predicts how many will pay.',
                    'cta'      => 'premium',
                ],
            ],
        ],
        [
            'name'      => 'Finance',
            'questions' => [
                [
                    'question' => 'How do I build trust online as a financial business?',
                    'answer'   => 'Google holds finance websites to a higher standard because they affect people\'s money. Show visitors and search engines that you\'re legitimate:

- Your licence or registration numbers
- Team names, photos and qualifications
- A real address and contact details
- Clear fees and terms
- Reviews from real clients

Mira checks your security and trust basics in a free website check.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I write finance content that ranks and stays compliant?',
                    'answer'   => 'Have content written or reviewed by qualified people, and show their name and credentials on the page. Add the date it was last updated, link to official sources, and include a general-advice notice where needed.

Always follow your regulator\'s advertising and disclosure rules. Accurate, well-sourced content is also what Google and AI tools prefer to show.',
                    'cta'      => 'contact',
                ],
                [
                    'question' => 'Can AI tools like ChatGPT recommend my financial services?',
                    'answer'   => 'Yes, if they can clearly understand what you offer and see that others trust you. Help them by:

- Creating a separate page for each service, explained in plain language
- Keeping your business name, address and phone the same everywhere online
- Collecting reviews and getting mentioned by trusted publications
- Adding Organization structured data to your website',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Gaming',
            'questions' => [
                [
                    'question' => 'How do I get more people to discover my game online?',
                    'answer'   => 'Give each game its own landing page with the trailer, screenshots, platforms, price and release date, and link to your store pages like Steam.

Post development updates regularly, send a press kit to creators and journalists, and keep your store page tags and description accurate. Most players find games through stores, video creators and communities.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I make my game links look great on Discord and social media?',
                    'answer'   => 'Add **Open Graph tags** to your website. Discord, X, Facebook and Reddit read them to build the link preview.

Use a strong key art image at 1200×630 pixels for og:image, a short punchy og:title, and an og:description that sells the game in one line. Mira checks this under **Social Sharing Preview**.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How can I build a player community before launch?',
                    'answer'   => 'Give interested players somewhere to go:

- A wishlist button or email sign-up on your website
- A Discord server where you share progress and ask for feedback
- Regular devlogs and short gameplay clips
- A playtest or demo for early fans

Ask for wishlists everywhere, because they help store algorithms show your game to more players at launch.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Government',
            'questions' => [
                [
                    'question' => 'How can people find the right service on our website faster?',
                    'answer'   => 'Name pages after the task people want to do, in plain language: “Renew your licence” instead of “Licensing Services Division”.

Use clear headings, a good site search, and put the most-used services on the home page. Clear titles also help Google show the right page in search results.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do we make our website accessible to everyone?',
                    'answer'   => 'Follow the **WCAG 2.2 AA** guidelines. The basics:

- Alt text on every meaningful image
- Headings in the right order
- Enough colour contrast
- Everything usable with a keyboard
- Captions on videos
- Forms with clear labels and error messages

Mira checks some of these, like alt text and heading order. Test with real assistive technology users for the rest.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How can AI assistants give correct answers about our services?',
                    'answer'   => 'AI assistants repeat what they can read. Publish clear, up-to-date pages for each service with eligibility, steps, fees and deadlines, and show when each page was last updated.

Remove or redirect outdated pages so old information isn\'t quoted, and check that your robots.txt doesn\'t block AI search crawlers from your public information.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Healthcare',
            'questions' => [
                [
                    'question' => 'How do patients find my clinic online?',
                    'answer'   => 'Most patients search for a service plus a location, like “physio near Richmond”. To show up:

- Create a page for each service you offer
- Keep your **Google Business Profile** complete, with hours, photos and a booking link
- Collect reviews and reply to them
- Use the same name, address and phone number everywhere online',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I build trust for health content on my website?',
                    'answer'   => 'Google holds health content to a high standard. Show practitioner names, qualifications and registration details, note who reviewed each article and when, and link to reliable medical sources.

Avoid promising outcomes, and follow your country\'s rules for advertising health services.',
                    'cta'      => 'contact',
                ],
                [
                    'question' => 'How can I get more appointment bookings from my website?',
                    'answer'   => 'Make booking the easiest thing to do:

- A **Book now** button on every page, visible on mobile
- Online booking instead of call-only
- Opening hours, location and parking info clearly shown
- A booking link on your Google Business Profile

Mira checks mobile friendliness and page speed, which both affect bookings.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Insurance',
            'questions' => [
                [
                    'question' => 'How do I rank for insurance searches against the big brands?',
                    'answer'   => 'Broad terms like “car insurance” are dominated by big brands. Win the specific searches instead, like “insurance for tradies” or “pet insurance for older dogs”.

Create a page for each niche and location you serve, and answer the questions people ask before they buy. Specific, helpful pages are also what AI tools quote.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How can I explain policies so customers actually understand them?',
                    'answer'   => 'Use plain language and real examples: “If a storm damages your roof, this covers repairs up to $X.”

Add comparison tables between policy levels, a clear list of what isn\'t covered, and an FAQ. Clear explanations build trust, cut support calls, and make your pages more likely to be quoted by AI assistants.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I get more quote requests from my website?',
                    'answer'   => 'Make getting a quote quick and safe-feeling:

- Ask only for what you need at the first step
- Show a progress bar for longer forms
- Display trust signals near the form: licences, reviews, security
- Make sure the form works well on mobile

Track where people drop out of the form to find the step that needs fixing.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Manufacturing',
            'questions' => [
                [
                    'question' => 'How do B2B buyers find manufacturers online?',
                    'answer'   => 'Engineers and buyers search for specific capabilities, like “CNC machining stainless steel Melbourne”. Help them find you with:

- A page for each capability and material
- Certifications such as ISO 9001
- Industries you serve and example projects
- Downloadable spec sheets',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'Should our product catalogue be on our website or in a PDF?',
                    'answer'   => 'Both, but the website matters more. Google and AI tools struggle to show individual products hidden inside a long PDF.

Give each product or product family its own web page with specs and photos, and offer the PDF as a download from that page.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I get more quote requests (RFQs) from my website?',
                    'answer'   => 'Make it easy for buyers to send everything you need:

- An RFQ form that accepts drawings and CAD files
- Lead times and minimum order quantities shown upfront
- A direct phone number and email on every page
- A fast reply time, stated on the page',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Marketing / AdTech',
            'questions' => [
                [
                    'question' => 'How do I prove my agency\'s results to new clients?',
                    'answer'   => 'Publish case studies with real numbers: the starting point, what you did, and the result, such as “+120% organic leads in 6 months”.

Add client logos, testimonials and named contacts where clients allow it. Mira\'s competitor comparison also makes a strong sales tool, because it shows prospects exactly where they fall behind.',
                    'cta'      => 'premium',
                ],
                [
                    'question' => 'How is AI search changing SEO for our clients?',
                    'answer'   => 'AI Overviews and chatbots answer simple questions directly, so some searches send fewer clicks. The goal is shifting from ranking to **being the source AI quotes**:

- Answer-ready content with short, direct answers
- Structured data on every important page
- Brand mentions on trusted sites
- Tracking traffic from AI tools in analytics

Mira scores AI visibility in every website check.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I track which marketing channels bring real customers?',
                    'answer'   => 'Tag every campaign link with **UTM parameters**, set up conversions in Google Analytics for actions that matter (purchases, leads, bookings), and connect them to your CRM.

Then you can see which channels bring paying customers, not just clicks.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Media',
            'questions' => [
                [
                    'question' => 'How do I get my articles into Google Top Stories and Discover?',
                    'answer'   => 'Google picks timely, trustworthy content that looks good in a feed:

- Add **Article structured data** with headline, author and dates
- Use large images (at least 1200px wide) and allow large previews
- Show clear author bylines and publication dates
- Keep pages fast on mobile',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I stop AI tools from using my content without credit?',
                    'answer'   => 'You can block AI training crawlers like GPTBot, CCBot and Google-Extended in your robots.txt file.

There\'s a trade-off: blocking AI **search** crawlers like OAI-SearchBot or PerplexityBot also removes you from AI answers that link back to you. Many publishers block training bots but allow search bots. Mira shows which AI crawlers your site allows.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How can I grow my returning readers?',
                    'answer'   => 'Readers who come back are worth far more than one-off visitors. Build the habit:

- A newsletter sign-up on every article
- Related articles at the end of each piece
- A regular publishing schedule
- Topics or series readers can follow',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Real Estate',
            'questions' => [
                [
                    'question' => 'How do I get my listings seen beyond the big property portals?',
                    'answer'   => 'Put every listing on your own website as well, with its own page, photos, floor plan and suburb details. Add suburb guides with recent sales, schools and transport.

These pages build your own search traffic over time, instead of renting visibility from the portals.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I rank for “real estate agent in [suburb]” searches?',
                    'answer'   => 'Local searches reward local proof:

- A page for each suburb you serve, with your recent results there
- A complete **Google Business Profile** with lots of recent reviews
- Agent profile pages with sales history
- RealEstateAgent structured data on your site',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I get more appraisal requests from my website?',
                    'answer'   => 'Offer a free appraisal on every page with a short form: address, name and contact details only.

Show proof near the form, such as recently sold properties, average days on market and reviews from sellers. Mobile matters, because most owners browse on their phones.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'Recruitment',
            'questions' => [
                [
                    'question' => 'How do I get my job ads into Google\'s job search?',
                    'answer'   => 'Add **JobPosting structured data** to each job page, including the title, description, date posted, closing date, employer and location. Google can then show your jobs in its job search results.

Remove or mark jobs as expired once they\'re filled. Mira checks your structured data in a website check.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I get more candidates to apply on our website?',
                    'answer'   => 'Remove friction from applying:

- Show the salary range and location upfront
- Let people apply in a few minutes on mobile
- Accept a CV upload instead of long forms
- Use clear job titles people actually search for, not internal ones',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I win more employer clients online?',
                    'answer'   => 'Show employers you know their industry. Create a page for each sector you recruit for, publish placement case studies, and offer useful resources like a yearly **salary guide** in exchange for an email.

Specialist expertise is also what AI tools look for when recommending agencies.',
                    'cta'      => 'contact',
                ],
            ],
        ],
        [
            'name'      => 'Retail',
            'questions' => [
                [
                    'question' => 'How do I bring more local shoppers into my store?',
                    'answer'   => 'Most shoppers check Google before they visit. Keep your **Google Business Profile** up to date with opening hours, holiday hours, photos and products.

Add your address, map and hours to your website with LocalBusiness structured data, and you can show in-store stock on Google through Merchant Center.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I compete with big online retailers?',
                    'answer'   => 'Compete on what they can\'t offer:

- Expert advice and buying guides
- Local pickup and same-day options
- Personal service and easy returns
- Real reviews from your local community

Write helpful content about the products you know best. Specific expertise ranks well and gets quoted by AI tools.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I get more Google reviews?',
                    'answer'   => 'Ask at the right moment, right after a good purchase. Use a short review link or a QR code at the counter and on receipts, and reply to every review, good or bad.

Don\'t offer discounts or gifts in exchange for reviews. That\'s against Google\'s rules and can get reviews removed.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
        [
            'name'      => 'SaaS',
            'questions' => [
                [
                    'question' => 'How do I get my software recommended by ChatGPT?',
                    'answer'   => 'AI tools recommend software they can compare with confidence. Publish:

- Clear feature and pricing pages
- Comparison and “alternatives” pages
- Integration pages for the tools you connect with
- Public documentation

Also collect reviews on sites like G2 and Capterra, which AI tools often read.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'Which pages should a SaaS website have to rank well?',
                    'answer'   => 'Beyond the home page, the pages that usually bring the most qualified traffic are:

- One page per key feature
- Use-case and industry pages
- A clear pricing page
- Integration pages
- Comparison pages against competitors
- Help docs and a blog answering real customer questions',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I turn website visitors into trial sign-ups?',
                    'answer'   => 'Say what the product does and who it\'s for in the first sentence. Show the product in action with a short video or screenshots, make pricing easy to find, and keep sign-up to as few fields as possible.

Add customer logos and reviews near the sign-up button.',
                    'cta'      => 'premium',
                ],
            ],
        ],
        [
            'name'      => 'Travel',
            'questions' => [
                [
                    'question' => 'How do I compete with Booking.com and big travel sites on Google?',
                    'answer'   => 'You won\'t outrank them for broad searches, so win on local expertise. Write detailed guides they can\'t match, like “best time to visit Ella for hiking”, with real tips and photos.

Target specific searches about your destination and type of trip, and make direct booking easy from every guide.',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do I get more direct bookings instead of paying commission?',
                    'answer'   => 'Give travellers a reason to book with you directly:

- A best-price promise or a direct-booking perk, like free breakfast
- A fast booking button on every page, especially on mobile
- A booking link on your Google Business Profile
- Recent reviews and real photos',
                    'cta'      => 'analyse',
                ],
                [
                    'question' => 'How do travellers using AI trip planners find us?',
                    'answer'   => 'AI trip planners pull from detailed, trusted pages. Publish sample itineraries, prices, seasons and practical FAQs, and keep your listings and reviews up to date on Google and TripAdvisor.

Make sure your robots.txt doesn\'t block AI search crawlers. Mira checks this in your AI Visibility score.',
                    'cta'      => 'analyse',
                ],
            ],
        ],
    ],
];
