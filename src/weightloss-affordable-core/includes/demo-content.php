<?php
/**
 * Demo content definitions.
 *
 * Original English content written for this build. Nothing here is copied
 * from another website, and no clinical claim, price or eligibility rule is
 * invented: figures marked as such must be confirmed with the pharmacy and
 * clinical partner before launch (see docs/LAUNCH-CHECKLIST.md).
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pages included in the demo import.
 *
 * @return array
 */
function wla_demo_pages() {
	return array(

		'home' => array(
			'title'      => 'Home',
			'menu_order' => 1,
			'excerpt'    => 'Clinician-led weight care with clear pricing and honest information.',
			'seo'        => array(
				'title'       => 'Clinician-Led Weight Loss Treatment | WeightLossAffordable',
				'description' => 'Explore prescription weight-management options reviewed by licensed clinicians, with transparent monthly pricing, English-language support and no guaranteed-outcome claims.',
			),
			'overrides'  => array(
				'hero' => array(
					'eyebrow'        => 'Medically guided weight care · English-language support',
					'line1'          => 'Weight care that',
					'emphasis_word'  => 'makes',
					'line2_suffix'   => 'sense.',
					'line3'          => 'Start with a real conversation.',
					'body'           => 'Learn how clinician-led weight management works, what treatment options exist, and what to ask before you decide. Every prescription request is reviewed by a licensed clinician — a questionnaire alone never guarantees treatment.',
					'primary_text'   => 'Request a consultation',
					'primary_url'    => '/consultation-eligibility',
					'secondary_text' => 'See how it works',
					'secondary_url'  => '/how-it-works',
					'float_badge'    => 'Clinician-led care',
					'float_sub'      => 'Independent review for every request',
					'lang_mini'      => 'NO INSURANCE REQUIRED',
					'lang_title'     => 'Transparent monthly pricing',
					'proof'          => array(
						array( 'value' => 'Email', 'label' => 'Written reply to every request' ),
						array( 'value' => '04', 'label' => 'Clear stages' ),
						array( 'value' => '0', 'label' => 'Guaranteed outcomes promised' ),
					),
				),
			),
		),

		'about' => array(
			'title'      => 'About us',
			'menu_order' => 20,
			'excerpt'    => 'Who we are, how we work, and what we will not claim.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'ABOUT',
					'title'     => 'A calmer, clearer way to approach weight care.',
					'lead'      => 'We built this service for people who are tired of contradictory advice, unclear pricing and promises nobody can keep. Our role is to explain the options honestly and connect you with licensed clinicians who make the clinical decisions.',
					'image'     => 'lifestyle-walk.jpg',
					'image_alt' => 'Two people walking together outdoors in soft morning light',
					'badge'     => 'Transparent information · No guaranteed outcomes',
				),
				'intro' => array(
					'index'   => '01',
					'eyebrow' => 'OUR POINT OF VIEW',
					'title'   => 'Information first. Pressure never.',
					'body'    => 'Weight management sits at the intersection of biology, habits, medication and circumstance. We do not pretend there is one answer. What we can do is describe the treatment pathway accurately, publish our pricing, and be explicit about the limits of what any medication can do. Where information depends on your clinician or your state, we say so instead of guessing.',
					'note'    => 'We do not sell medication directly to the public. Treatment is available only on prescription, after a clinician decides it is appropriate.',
					'link_label' => 'Read how the process works',
					'link_url'   => '/how-it-works',
				),
			),
		),

		'weight-loss-treatments' => array(
			'title'      => 'Weight loss treatments',
			'menu_order' => 2,
			'excerpt'    => 'Prescription treatment options, explained without hype.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'TREATMENTS',
					'title'     => 'Treatment options, explained properly.',
					'lead'      => 'Two prescription medication classes are commonly discussed for weight management. Below you will find what each one is, how it is supplied, what it may cost, and what a clinician will want to know before prescribing anything.',
					'image'     => 'product-vials.jpg',
					'image_alt' => 'Unbranded pharmacy vials on a linen surface',
					'bullets'   => "Prescription-only treatment\nIndependent clinician review\nPrices published up front",
				),
				'treatment_grid' => array(
					'index'          => '01',
					'eyebrow'        => 'AVAILABLE OPTIONS',
					'title'          => 'Compare our treatment options',
					'intro'          => 'Availability depends on where you live and on your medical history. A clinician decides whether any option is appropriate.',
					'secondary_text' => 'Request a consultation',
				),
				'product' => array(
					'index' => '02',
					'eyebrow' => 'WHAT TREATMENT LOOKS LIKE',
					'title' => 'Prescribed, prepared and shipped with instructions.',
					'body'  => 'When a clinician approves treatment, your prescription is prepared by a state-licensed compounding pharmacy and shipped with written storage and injection guidance. Dose changes are reviewed by your clinician, not adjusted on your own.',
				),
			),
		),

		'compare-treatments' => array(
			'title'      => 'Compare treatments',
			'menu_order' => 3,
			'excerpt'    => 'A side-by-side look at the two medication classes we can prescribe.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'COMPARE',
					'title'     => 'Semaglutide or tirzepatide?',
					'lead'      => 'Both are prescription medications used in weight management, and both are supplied as compounded preparations by licensed pharmacies. They are not identical, and neither is automatically the better choice — that depends on your history and your clinician\'s assessment.',
					'image'     => 'nutrition.jpg',
					'image_alt' => 'Fresh vegetables and whole foods on a light surface',
				),
				'services' => array(
					'index'   => '02',
					'eyebrow' => 'SIDE BY SIDE',
					'title'   => 'What actually differs',
					'intro'   => 'Use this as a starting point for a conversation with a clinician, not as a decision tool.',
					'cards'   => array(
						array( 'tag' => 'RECEPTOR CLASS', 'title' => 'Single vs dual agonist', 'text' => 'Semaglutide acts on the GLP-1 receptor. Tirzepatide acts on both GLP-1 and GIP receptors. Published research describes these mechanisms; it does not predict your individual response.', 'icon' => 'spark' ),
						array( 'tag' => 'PUBLISHED PRICE', 'title' => 'Monthly cost', 'text' => 'Compounded semaglutide is currently listed at $139 per month and compounded tirzepatide at $239 per month. Confirm current pricing before you commit — figures can change.', 'icon' => 'shield' ),
						array( 'tag' => 'SUPPLY', 'title' => 'How it arrives', 'text' => 'Both are supplied as multi-dose vials with injection supplies, shipped temperature-controlled with written instructions and a dosing schedule set by your clinician.', 'icon' => 'truck' ),
					),
					'notice'  => 'This comparison is general information. It is not a recommendation, and it does not establish that either medication is appropriate or effective for you.',
				),
			),
		),

		'how-it-works' => array(
			'title'      => 'How it works',
			'menu_order' => 4,
			'excerpt'    => 'From questionnaire to follow-up, in four honest steps.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'HOW IT WORKS',
					'title'     => 'The process, without the mystery.',
					'lead'      => 'Here is exactly what happens after you submit a consultation request — including the step where a clinician may decide that treatment is not right for you.',
					'image'     => 'hero-care.jpg',
					'image_alt' => 'A patient speaking with a clinician by video call at home',
				),
				'approach' => array(
					'index'         => '02',
					'eyebrow'       => 'WHAT WE ASK OF YOU',
					'title'         => 'Straight answers make better care.',
					'body'          => 'Accurate information about your medical history, current prescriptions and previous treatments helps your clinician make a safe decision. Nothing you share is used to sell you a product, and clinical records are kept separate from marketing data.',
					'bullets'       => array(
						'Answer the questionnaire as completely as you can',
						'Tell your clinician about other medications and conditions',
						'Ask about side effects before you start',
						'Report new or severe symptoms promptly',
					),
					'button_text'   => 'Read our safety information',
					'button_url'    => '/treatment-safety-information',
				),
			),
		),

		'consultation-eligibility' => array(
			'title'      => 'Consultation and eligibility',
			'menu_order' => 5,
			'excerpt'    => 'Request a consultation. A licensed clinician reviews every request.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'CONSULTATION',
					'title'     => 'Request a consultation.',
					'lead'      => 'Tell us a little about what you are looking for and where you live. A member of our team will confirm the next step, and a licensed clinician will decide whether treatment is appropriate.',
					'image'     => 'hero-care.jpg',
					'image_alt' => 'Video consultation with a clinician',
					'badge'     => 'No charge until a clinician approves a prescription',
				),
				'contact' => array(
					'index'           => '02',
					'eyebrow'         => 'CONSULTATION REQUEST',
					'title'           => 'Start with a short request.',
					'body'            => 'This form is for a consultation request, not a medical assessment. Do not include detailed medical history here — your clinician will collect that securely after we confirm your state and contact preferences.',
					'form_type'       => 'consultation',
					'form_head_title' => 'Consultation request',
					'form_head_sub'   => 'Reviewed within 1–2 business days',
					'checks'          => "Consultation request, not a prescription\nNo payment details requested\nReply within 1–2 business days",
					'topics'          => "new|I am new to treatment\ntransfer|I am transferring from another provider\npricing|I have a pricing question\nexisting|I am an existing patient",
					'submit_text'     => 'Request my consultation',
				),
				'intro' => array(
					'index'   => '01',
					'eyebrow' => 'ELIGIBILITY OVERVIEW',
					'title'   => 'What clinicians typically consider.',
					'body'    => 'Prescription weight-management medication is generally considered for adults with a body-mass index in a specified range, sometimes alongside related conditions such as prediabetes or high blood pressure. Clinicians also screen for conditions that make these medications unsuitable, including certain thyroid, pancreatic, kidney and gastrointestinal histories, and for pregnancy or breastfeeding. This summary is not a set of rules and it is not a substitute for a clinical assessment.',
					'note'    => 'A clinician licensed in your state makes the final decision. Submitting a request does not guarantee approval, a prescription, or a particular outcome.',
					'link_label' => 'Read the full safety information',
					'link_url'   => '/treatment-safety-information',
				),
			),
		),

		'pricing-and-plans' => array(
			'title'      => 'Pricing and plans',
			'menu_order' => 6,
			'excerpt'    => 'Monthly pricing with no long-term contract.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'PRICING',
					'title'     => 'Pricing you can read before you decide.',
					'lead'      => 'Our plans are billed monthly and include the clinical review, the medication and shipping. There is no long-term contract, and nothing is charged until a clinician approves a prescription.',
					'image'     => 'nutrition.jpg',
					'image_alt' => 'Fresh food on a light table',
					'price'     => '$139 / month',
					'price_note' => 'Compounded semaglutide — confirm current price before launch',
				),
				'pricing' => array(
					'index'   => '01',
					'eyebrow' => 'PUBLISHED PLAN PRICES',
					'title'   => 'What each plan includes.',
					'body'    => 'Every plan includes the clinician review, the medication itself and shipping. Prices below are the currently published monthly amounts for self-pay patients. Confirm the exact charge with our team before you pay anything.',
					'status'  => 'Published pricing',
					'sub'     => 'Update these figures if your prices change',
					'rows'    => array(
						array( 'label' => 'Compounded semaglutide', 'value' => '$139 / month' ),
						array( 'label' => 'Compounded tirzepatide', 'value' => '$239 / month' ),
						array( 'label' => 'Clinician review', 'value' => 'Included' ),
						array( 'label' => 'Shipping', 'value' => 'Included' ),
						array( 'label' => 'Contract length', 'value' => 'Month to month' ),
					),
					'note'    => 'No charge is taken until a clinician approves a prescription. See the refund and cancellation policy for how cancellations are handled.',
				),
			),
		),

		'patient-reviews' => array(
			'title'      => 'Patient reviews',
			'menu_order' => 7,
			'excerpt'    => 'How we collect, verify and publish patient feedback.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'REVIEWS',
					'title'     => 'Reviews, only when they are real.',
					'lead'      => 'We do not write testimonials for this website and we do not publish outcomes we cannot verify. This page explains how patient feedback is collected and what will appear here once genuine, consented reviews have been approved.',
					'image'     => 'lifestyle-walk.jpg',
					'image_alt' => 'People walking outdoors together',
				),
				'reviews' => array(
					'index'   => '01',
					'eyebrow' => 'REVIEW POLICY',
					'title'   => 'How reviews are handled here.',
					'body'    => 'Feedback is collected after at least twelve weeks of treatment, with written permission to publish. Reviews are stored in WordPress and only appear on the site once an administrator publishes them. We never edit a review to add claims the patient did not make, and we never display before-and-after imagery as a promise of results.',
					'badge'   => 'No reviews published yet',
					'caption' => 'When consented reviews are added under Treatments → Reviews, they will appear in this panel automatically.',
					'link_text' => 'Read the medical disclaimer',
					'link_url'  => '/medical-disclaimer',
				),
			),
		),

		'frequently-asked-questions' => array(
			'title'      => 'Frequently asked questions',
			'menu_order' => 8,
			'excerpt'    => 'Answers about pricing, eligibility, safety and cancellation.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'FAQS',
					'title'     => 'Questions people actually ask.',
					'lead'      => 'If something is missing here, send us a message and a member of the team will answer it directly.',
					'image'     => 'nutrition.jpg',
					'image_alt' => 'Fresh vegetables on a table',
				),
				'faq' => array(
					'index'    => '01',
					'eyebrow'  => 'EVERYTHING ON ONE PAGE',
					'title'    => 'Frequently asked questions',
					'intro'    => 'These answers are general information, not medical advice.',
					'show_all' => '0',
					'faqs'     => array(
						array( 'question' => 'Do I need insurance?', 'answer' => 'No. Plans are self-pay with a fixed monthly price that includes the clinical review, medication and shipping. There are no deductibles or surprise copays.' ),
						array( 'question' => 'Does requesting a consultation mean I will be approved?', 'answer' => 'No. A consultation request starts a clinical review. A licensed clinician decides independently whether any prescription is appropriate, and may decide that it is not.' ),
						array( 'question' => 'Are compounded medications FDA-approved?', 'answer' => 'Compounded preparations are not FDA-approved products and are not interchangeable with branded medications. They are prepared by state-licensed pharmacies under a prescription, and your clinician will explain the risks and benefits before you start.' ),
						array( 'question' => 'What information will I be asked for?', 'answer' => 'Your clinician will ask about your medical history, current medications, allergies, previous weight-management attempts and your goals. Accurate answers are what make a safe decision possible.' ),
						array( 'question' => 'Which conditions may make treatment unsuitable?', 'answer' => 'Clinicians screen for a range of factors, including certain thyroid, pancreatic, kidney and gastrointestinal conditions, and pregnancy or breastfeeding. This list is not exhaustive — your clinician will assess your own history.' ),
						array( 'question' => 'How much does it cost?', 'answer' => 'Compounded semaglutide is currently listed at $139 per month and compounded tirzepatide at $239 per month, including review and shipping. Confirm current pricing before you commit.' ),
						array( 'question' => 'When am I charged?', 'answer' => 'Only after a licensed clinician approves a prescription. If treatment is not appropriate, no medication charge is taken.' ),
						array( 'question' => 'Can I cancel?', 'answer' => 'Yes. Plans are month-to-month. Cancel before your next billing date and you will not be charged again. See the refund and cancellation policy for details.' ),
						array( 'question' => 'What if I have side effects?', 'answer' => 'Common effects such as nausea or fatigue often ease over the first weeks, and your clinician can adjust your dose. Seek urgent care for severe abdominal pain, persistent vomiting, difficulty breathing or signs of an allergic reaction.' ),
						array( 'question' => 'Where is the service available?', 'answer' => 'Availability depends on whether a clinician licensed in your state can treat you. Confirm your state during the consultation request; we will tell you if we cannot help in your location.' ),
					),
				),
			),
		),

		'resources' => array(
			'title'      => 'Resources',
			'menu_order' => 9,
			'excerpt'    => 'Plain-language guides about weight-management treatment.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'LEARN',
					'title'     => 'Understand the options before you decide.',
					'lead'      => 'Short guides on how these medications are described in the medical literature, how eligibility is assessed, and what to ask a clinician.',
					'image'     => 'nutrition.jpg',
					'image_alt' => 'Fresh ingredients arranged on a table',
				),
				'resources' => array(
					'index' => '01',
					'all_url' => '/articles',
				),
			),
		),

		'contact' => array(
			'title'      => 'Contact',
			'menu_order' => 10,
			'excerpt'    => 'Ask a general question about services, pricing or availability.',
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow'   => 'CONTACT',
					'title'     => 'Talk to our team.',
					'lead'      => 'Send a general question about our services, pricing or availability. Please do not send medical details through this form.',
					'image'     => 'hero-care.jpg',
					'image_alt' => 'Person at a desk with a laptop during a video call',
				),
				'contact' => array(
					'index' => '01',
					'eyebrow' => 'GENERAL ENQUIRY',
					'title' => 'What would you like to know?',
				),
			),
		),

		'articles' => array(
			'title'      => 'Articles',
			'menu_order' => 11,
			'excerpt'    => 'Guidance and context on weight-management treatment.',
			'is_posts_page' => true,
		),

		'privacy-policy' => array(
			'title'      => 'Privacy policy',
			'menu_order' => 30,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'LEGAL',
					'title'   => 'Privacy policy',
					'lead'    => 'How we collect, use, store and delete information submitted through this website.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'PRIVACY',
					'title'   => 'Your information',
					'updated' => 'Draft template — have this reviewed by qualified counsel before publication.',
					'body'    => '<h2>Who we are</h2><p>This website is operated by the business named in the site footer. For privacy questions, use the contact details published on the contact page.</p><h2>What we collect</h2><ul><li><strong>Enquiry details</strong> you submit through the contact or consultation form: name, email address, optional phone number, state, topic and message.</li><li><strong>Technical data</strong> needed to operate the site securely, such as a hashed (pseudonymised) IP address used for spam prevention.</li></ul><h2>What we do not collect on this website</h2><p>Do not submit medical records, insurance identifiers or payment details through the website forms. Clinical information is collected separately, by the clinical partner, through a secure channel once your consultation is confirmed.</p><h2>Why we use it</h2><p>To reply to your enquiry, to confirm whether care is available in your state, and to keep records required by law. We do not sell personal information.</p><h2>How long we keep it</h2><p>Enquiries are deleted automatically after the retention period configured in the website settings. Records subject to legal or clinical retention requirements may be held by the clinical partner for longer.</p><h2>Your choices</h2><p>You can ask us to confirm, correct or delete the information you submitted by writing to the contact address on this site. You may also withdraw consent for marketing communication at any time.</p><h2>Third parties</h2><p>We use service providers for hosting, email delivery and spam prevention. Where a provider processes data on our behalf, they are bound by a data-processing agreement.</p>',
				),
			),
		),

		'terms-and-conditions' => array(
			'title'      => 'Terms and conditions',
			'menu_order' => 31,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'LEGAL',
					'title'   => 'Terms and conditions',
					'lead'    => 'The terms that apply to your use of this website and our services.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'TERMS',
					'title'   => 'Using this website',
					'updated' => 'Draft template — have this reviewed by qualified counsel before publication.',
					'body'    => '<h2>1. Information only</h2><p>Content on this website is general information about weight-management services. It is not medical advice, does not create a clinician–patient relationship, and must not be used to diagnose or treat any condition.</p><h2>2. Clinical services</h2><p>Clinical services are provided by licensed clinicians engaged by our clinical partner. Treatment is available only on prescription after an assessment, and not every person is eligible. Submitting a form does not guarantee a consultation, a prescription, or any particular result.</p><h2>3. Pricing</h2><p>Published prices are for self-pay patients and may change. The amount you will be charged is confirmed before payment is taken.</p><h2>4. Compounded medications</h2><p>Compounded preparations are not FDA-approved products and are not interchangeable with branded medications. Your clinician will explain the risks and benefits, including that compounded preparations may vary in potency and are not evaluated in formal clinical trials.</p><h2>5. Acceptable use</h2><p>Do not use this website to submit false information, to attempt to access another person\'s data, or to interfere with its operation.</p><h2>6. Liability</h2><p>To the extent permitted by law, we are not liable for decisions made solely on the basis of general information published here. Nothing in these terms limits liability that cannot legally be limited.</p><h2>7. Changes</h2><p>We may update these terms. The version published on this page applies from the date it is posted.</p>',
				),
			),
		),

		'medical-disclaimer' => array(
			'title'      => 'Medical disclaimer',
			'menu_order' => 32,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'SAFETY',
					'title'   => 'Medical disclaimer',
					'lead'    => 'What this website does and does not do.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'DISCLAIMER',
					'title'   => 'Read this before acting on anything here',
					'updated' => 'Reviewed by the clinical reviewer configured in Settings.',
					'body'    => '<h2>Not medical advice</h2><p>Nothing on this website is a diagnosis, a treatment plan, or a substitute for consultation with a licensed healthcare professional who knows your medical history.</p><h2>No guaranteed results</h2><p>Individual responses to any weight-management treatment vary. We do not promise weight loss, a specific amount of weight loss, or any clinical outcome.</p><h2>Eligibility</h2><p>Eligibility for prescription treatment is determined solely by a licensed clinician. Conditions that may make treatment unsuitable include, among others, a personal or family history of medullary thyroid carcinoma, multiple endocrine neoplasia syndrome type 2, pancreatitis, severe gastrointestinal disease, and pregnancy or breastfeeding. This is not an exhaustive list.</p><h2>Urgent symptoms</h2><p>If you experience severe abdominal pain, persistent vomiting, difficulty breathing, signs of an allergic reaction, or any symptom that worries you, seek urgent medical care or call your local emergency number. Do not wait for a reply through this website.</p><h2>External links</h2><p>Links to third-party resources are provided for convenience. We are not responsible for their content or accuracy.</p>',
				),
			),
		),

		'treatment-safety-information' => array(
			'title'      => 'Treatment safety information',
			'menu_order' => 33,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'SAFETY',
					'title'   => 'Treatment safety information',
					'lead'    => 'Common side effects, serious risks and when to seek help.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'SAFETY',
					'title'   => 'What to know before you start',
					'updated' => 'Clinical review required before publication.',
					'body'    => '<h2>About these medications</h2><p>Semaglutide and tirzepatide are GLP-1 receptor agonists (tirzepatide also acts on the GIP receptor). They are prescription medicines. They are not vitamins, supplements or over-the-counter products, and they should not be obtained without a prescription.</p><h2>Common side effects</h2><p>Reported side effects include nausea, vomiting, diarrhoea, constipation, abdominal discomfort, fatigue, headache and reduced appetite. Many people find these ease over the first weeks as the body adjusts, and clinicians often start at a low dose and increase gradually.</p><h2>Serious risks to discuss with your clinician</h2><ul><li>Pancreatitis — severe, persistent abdominal pain, sometimes radiating to the back.</li><li>Gallbladder disease — sudden pain in the upper right abdomen, fever or jaundice.</li><li>Dehydration and kidney injury from persistent vomiting or diarrhoea.</li><li>Severe hypoglycaemia when combined with insulin or sulfonylureas.</li><li>Allergic reactions — rash, swelling, difficulty breathing.</li><li>Thyroid C-cell tumours observed in animal studies with this class; a personal or family history of medullary thyroid carcinoma or MEN2 is a contraindication.</li></ul><h2>Pregnancy and breastfeeding</h2><p>These medications are not for use during pregnancy or breastfeeding. Tell your clinician if you are pregnant, planning pregnancy or breastfeeding.</p><h2>Compounded preparations</h2><p>Compounded medications are prepared by state-licensed pharmacies under a prescription. They are not FDA-approved products and are not interchangeable with branded medications. Their potency and purity depend on the compounding pharmacy.</p><h2>When to get urgent help</h2><p>Seek urgent medical attention for severe abdominal pain, persistent vomiting, signs of dehydration, difficulty breathing, or swelling of the face, lips or throat.</p>',
				),
			),
		),

		'cookie-policy' => array(
			'title'      => 'Cookie policy',
			'menu_order' => 34,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'LEGAL',
					'title'   => 'Cookie policy',
					'lead'    => 'The small number of cookies this website uses, and why.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'COOKIES',
					'title'   => 'Cookies and storage',
					'updated' => 'Draft template — review before publication.',
					'body'    => '<h2>Essential cookies</h2><p>WordPress sets cookies for logged-in users and for comment forms. These are required for the site to function and for security. Logged-in administrators also receive authentication cookies.</p><h2>Analytics</h2><p>This build does not include an analytics package by default. If you add one, describe it here, choose an appropriate consent model for your jurisdiction, and update this page.</p><h2>Managing cookies</h2><p>You can clear or block cookies in your browser settings. Blocking essential cookies may prevent parts of the site from working.</p>',
				),
			),
		),

		'accessibility-statement' => array(
			'title'      => 'Accessibility statement',
			'menu_order' => 35,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'ACCESSIBILITY',
					'title'   => 'Accessibility statement',
					'lead'    => 'What we have done to make this site usable, and how to tell us when it is not.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'ACCESSIBILITY',
					'title'   => 'Our commitment',
					'updated' => 'Draft template — confirm against your market\'s requirements.',
					'body'    => '<h2>Design approach</h2><p>The site is built with semantic headings, visible keyboard focus styles, a skip link, descriptive image alternative text, form labels and controls that meet minimum contrast targets. Motion is reduced automatically when your device requests reduced motion.</p><h2>Known limitations</h2><p>Interactive components such as the care-journey board and the guide explorer are operable with a keyboard and announce state changes, but we are still reviewing them against WCAG 2.2 AA. Third-party content may not meet the same standard.</p><h2>Tell us</h2><p>If any part of this site is difficult to use, contact us and describe the page and the problem. We will respond and, where possible, provide the information in an alternative format.</p>',
				),
			),
		),

		'refund-cancellation-policy' => array(
			'title'      => 'Refund and cancellation policy',
			'menu_order' => 36,
			'overrides'  => array(
				'subpage_hero' => array(
					'eyebrow' => 'LEGAL',
					'title'   => 'Refund and cancellation policy',
					'lead'    => 'How cancellations and refunds work.',
					'image'   => '',
				),
				'rich_text' => array(
					'index'   => '01',
					'eyebrow' => 'REFUNDS',
					'title'   => 'Cancelling your plan',
					'updated' => 'Draft template — confirm the final commercial terms before publication.',
					'body'    => '<h2>No charge before approval</h2><p>If a licensed clinician decides that treatment is not appropriate, no medication charge is taken.</p><h2>Cancelling</h2><p>Plans are month-to-month. Contact us before your next billing date to cancel and you will not be charged again. Medication already dispensed and shipped cannot usually be returned for safety reasons.</p><h2>Refunds</h2><p>If a shipment arrives damaged or you were charged in error, contact us within 30 days of the charge and we will review the case. Refund decisions are made in line with the consumer-protection rules that apply in your location.</p><h2>No results-based guarantee</h2><p>We do not offer a guarantee of weight loss. Any promotion that claims a guaranteed outcome should be treated with caution; ask us for written terms before you rely on it.</p>',
				),
			),
		),
	);
}

/**
 * Treatments included in the demo import.
 *
 * @return array
 */
function wla_demo_treatments() {
	return array(
		'semaglutide' => array(
			'title'     => 'Compounded semaglutide',
			'menu_order' => 1,
			'excerpt'   => 'A once-weekly GLP-1 receptor agonist preparation supplied by a licensed compounding pharmacy, prescribed only after a clinician review.',
			'meta'      => array(
				'short_description' => 'A once-weekly GLP-1 receptor agonist preparation, prescribed only after a licensed clinician reviews your history.',
				'featured'          => '1',
				'display_order'     => '1',
				'medication_name'   => 'Compounded semaglutide',
				'active_ingredient' => 'Semaglutide',
				'mechanism'         => 'glp1',
				'form'              => 'Multi-dose vial for subcutaneous injection',
				'frequency'         => 'Once weekly, as directed by your clinician',
				'supplied_by'       => 'State-licensed compounding pharmacy',
				'medication_notes'  => 'Supplied with injection supplies and written storage instructions. Keep refrigerated as directed on the pharmacy label.',
				'price'             => '$139',
				'billing_period'    => 'per month',
				'compare_price'     => '$199',
				'price_note'        => 'Published monthly price for self-pay patients. Confirm the current amount with our team before payment.',
				'eligibility'       => 'Prescription treatment for adults is generally considered when body-mass index falls within a specified range, sometimes alongside a related condition such as prediabetes or high blood pressure. A clinician licensed in your state assesses your history and decides whether treatment is appropriate.',
				'safety'            => 'Not for use during pregnancy or breastfeeding. A personal or family history of medullary thyroid carcinoma or MEN2 is a contraindication. Discuss pancreatitis, gallbladder disease, kidney disease, diabetic retinopathy and any medicines you take, especially insulin or sulfonylureas. Common side effects include nausea, vomiting, diarrhoea, constipation and fatigue. Seek urgent care for severe abdominal pain, persistent vomiting or signs of an allergic reaction.',
				'disclaimer'        => 'Semaglutide is a prescription medicine. Compounded preparations are not FDA-approved products and are not interchangeable with branded medications. Individual results vary and are not guaranteed.',
				'cta_label'         => 'Request a consultation',
				'cta_url'           => '/consultation-eligibility',
				'seo_title'         => 'Compounded semaglutide — clinician-led weight care',
				'seo_description'   => 'How compounded semaglutide is prescribed, supplied and priced, who reviews eligibility, and what safety information to consider before starting treatment.',
			),
			'benefits'  => array(
				array( 'icon' => 'spark', 'title' => 'Appetite signalling', 'text' => 'GLP-1 receptor agonists are described in the literature as reducing appetite signalling in the brain, which some people experience as feeling hungry less often.' ),
				array( 'icon' => 'clock', 'title' => 'Slower gastric emptying', 'text' => 'The medication class is associated with slower stomach emptying, so meals may feel more satisfying for longer.' ),
				array( 'icon' => 'shield', 'title' => 'Clinician-managed dosing', 'text' => 'Your clinician sets a gradual dosing schedule and adjusts it based on how you respond and any side effects you report.' ),
			),
			'variations' => array(
				array( 'label' => 'Monthly plan', 'price' => '$139', 'period' => 'per month', 'note' => 'Includes clinician review, medication and shipping. Month to month.' ),
				array( 'label' => 'Dose escalation', 'price' => 'Included', 'period' => '', 'note' => 'Dose increases are reviewed by your clinician at no extra charge.' ),
			),
			'faqs'      => array(
				array( 'question' => 'Is this the same as a branded medication?', 'answer' => 'No. This is a compounded preparation made by a licensed pharmacy under a prescription. It is not an FDA-approved product and is not interchangeable with a branded medication.' ),
				array( 'question' => 'How is it taken?', 'answer' => 'As a once-weekly subcutaneous injection, using the dose and schedule set by your clinician. Written instructions and injection supplies are included.' ),
				array( 'question' => 'What does it cost?', 'answer' => 'The published price is $139 per month, including clinician review and shipping. Confirm the current price before payment.' ),
				array( 'question' => 'Can I stop at any time?', 'answer' => 'Yes. Plans are month-to-month and you can cancel before your next billing date. Speak with your clinician before stopping so your care can be adjusted safely.' ),
			),
			'image' => 'product-vials.jpg',
			'hero'  => array(
				'eyebrow'    => 'GLP-1 RECEPTOR AGONIST',
				'title'      => 'Compounded semaglutide',
				'lead'       => 'A once-weekly prescription treatment used in weight management, supplied by a state-licensed compounding pharmacy and reviewed by a licensed clinician.',
				'image'      => 'product-vials.jpg',
				'image_alt'  => 'Unbranded pharmacy vials on a linen surface',
				'price'      => '$139',
				'price_note' => 'per month, including review and shipping',
				'bullets'    => "Prescription only\nOnce-weekly injection\nClinician-set dosing schedule",
			),
		),

		'tirzepatide' => array(
			'title'     => 'Compounded tirzepatide',
			'menu_order' => 2,
			'excerpt'   => 'A once-weekly dual GLP-1 and GIP receptor agonist preparation. Clinical suitability is decided by a licensed clinician.',
			'meta'      => array(
				'short_description' => 'A once-weekly dual GLP-1 and GIP receptor agonist preparation. Suitability is decided by a licensed clinician.',
				'featured'          => '1',
				'display_order'     => '2',
				'medication_name'   => 'Compounded tirzepatide',
				'active_ingredient' => 'Tirzepatide',
				'mechanism'         => 'glp1_gip',
				'form'              => 'Multi-dose vial for subcutaneous injection',
				'frequency'         => 'Once weekly, as directed by your clinician',
				'supplied_by'       => 'State-licensed compounding pharmacy',
				'medication_notes'  => 'Supplied with injection supplies and written storage instructions. Keep refrigerated as directed on the pharmacy label.',
				'price'             => '$239',
				'billing_period'    => 'per month',
				'compare_price'     => '$299',
				'price_note'        => 'Published monthly price for self-pay patients. Confirm the current amount with our team before payment.',
				'eligibility'       => 'Prescription treatment for adults is generally considered when body-mass index falls within a specified range, sometimes alongside a related condition. Eligibility is decided by a clinician licensed in your state after reviewing your medical history.',
				'safety'            => 'Not for use during pregnancy or breastfeeding. A personal or family history of medullary thyroid carcinoma or MEN2 is a contraindication. Discuss pancreatitis, gallbladder disease, kidney disease and any medicines you take, especially insulin or sulfonylureas. Common side effects include nausea, vomiting, diarrhoea, constipation and fatigue. Seek urgent care for severe abdominal pain, persistent vomiting or signs of an allergic reaction.',
				'disclaimer'        => 'Tirzepatide is a prescription medicine. Compounded preparations are not FDA-approved products and are not interchangeable with branded medications. Individual results vary and are not guaranteed.',
				'cta_label'         => 'Request a consultation',
				'cta_url'           => '/consultation-eligibility',
				'seo_title'         => 'Compounded tirzepatide — dual GLP-1 / GIP treatment',
				'seo_description'   => 'How compounded tirzepatide is prescribed and supplied, what the dual receptor mechanism means, current pricing, and the safety factors clinicians review.',
			),
			'benefits'  => array(
				array( 'icon' => 'spark', 'title' => 'Dual receptor action', 'text' => 'Tirzepatide acts on both GLP-1 and GIP receptors, a mechanism described in published literature about this medication class.' ),
				array( 'icon' => 'heart', 'title' => 'Appetite and satiety', 'text' => 'The class is associated with reduced appetite and increased feelings of fullness after eating.' ),
				array( 'icon' => 'shield', 'title' => 'Clinician-managed dosing', 'text' => 'Your clinician starts low and increases gradually, reviewing your response and any side effects at each step.' ),
			),
			'variations' => array(
				array( 'label' => 'Monthly plan', 'price' => '$239', 'period' => 'per month', 'note' => 'Includes clinician review, medication and shipping. Month to month.' ),
				array( 'label' => 'Dose escalation', 'price' => 'Included', 'period' => '', 'note' => 'Dose increases are reviewed by your clinician at no extra charge.' ),
			),
			'faqs'      => array(
				array( 'question' => 'How is this different from semaglutide?', 'answer' => 'Tirzepatide acts on both GLP-1 and GIP receptors, while semaglutide acts on the GLP-1 receptor. Published average effects differ between the two classes, but no medication can predict your individual response.' ),
				array( 'question' => 'How is it taken?', 'answer' => 'As a once-weekly subcutaneous injection, following the dose and schedule set by your clinician.' ),
				array( 'question' => 'What does it cost?', 'answer' => 'The published price is $239 per month, including clinician review and shipping. Confirm the current price before payment.' ),
				array( 'question' => 'Is compounded tirzepatide FDA-approved?', 'answer' => 'No. Compounded preparations are not FDA-approved products and are not interchangeable with branded medications.' ),
			),
			'image' => 'product-vials.jpg',
			'hero'  => array(
				'eyebrow'    => 'DUAL GLP-1 / GIP AGONIST',
				'title'      => 'Compounded tirzepatide',
				'lead'       => 'A once-weekly prescription treatment that acts on both GLP-1 and GIP receptors, supplied by a state-licensed compounding pharmacy after clinician review.',
				'image'      => 'product-vials.jpg',
				'image_alt'  => 'Unbranded pharmacy vials on a linen surface',
				'price'      => '$239',
				'price_note' => 'per month, including review and shipping',
				'bullets'    => "Prescription only\nOnce-weekly injection\nDual receptor mechanism",
			),
		),
	);
}

/**
 * Demo blog posts.
 *
 * @return array
 */
function wla_demo_posts() {
	return array(
		array(
			'slug'     => 'how-glp-1-medications-are-described-in-the-literature',
			'title'    => 'How GLP-1 medications are described in the medical literature',
			'excerpt'  => 'What peer-reviewed research says about this medication class, what it does not say, and why a compounded preparation is different from a branded product.',
			'category' => 'Medication basics',
			'content'  => '<p>When people read that a medication “works on appetite”, it is usually a compressed version of a longer explanation. This article unpacks that explanation in plain language, and is deliberately careful about the difference between describing a mechanism and promising a result.</p><h2>What the class does</h2><p>GLP-1 receptor agonists mimic a hormone your gut releases after eating. In published research, that signalling is associated with reduced appetite, slower stomach emptying and glucose-dependent insulin release. Tirzepatide adds action at the GIP receptor, which is why it is described as a dual agonist.</p><h2>What the research does not tell you</h2><p>Clinical studies report averages across large groups. They cannot tell you what will happen in your body, how quickly you might notice anything, or whether a medication is safe for your particular history. Those are clinical questions, and they belong in a consultation.</p><h2>Why compounded is different</h2><p>A compounded preparation is made by a licensed pharmacy under a prescription. It is not an FDA-approved product, it is not interchangeable with a branded medication, and its potency depends on the pharmacy preparing it. That does not make it illegal or unregulated — it makes it different, and worth discussing explicitly with your clinician.</p><h2>Questions worth asking</h2><ul><li>What is the exact product, and which pharmacy prepares it?</li><li>What are the common side effects, and which symptoms need urgent care?</li><li>How will my dose be adjusted, and who reviews it?</li><li>What happens if the medication does not suit me?</li></ul><p>General information only. This article is not medical advice.</p>',
		),
		array(
			'slug'     => 'who-typically-qualifies-for-prescription-weight-management',
			'title'    => 'Who typically qualifies for prescription weight management',
			'excerpt'  => 'A plain-language overview of the factors clinicians usually weigh, and why a website quiz can never decide eligibility.',
			'category' => 'Eligibility',
			'content'  => '<p>Eligibility questions are the most common thing people ask before starting. The honest answer is that eligibility is a clinical judgement, made by a licensed professional who has reviewed your history. What we can do here is explain the shape of that judgement.</p><h2>Body-mass index and related conditions</h2><p>Prescribing guidance for weight-management medication generally centres on body-mass index, sometimes in combination with a related condition such as prediabetes, type 2 diabetes, high blood pressure or sleep apnoea. The exact thresholds belong to your clinician and to the guidance that applies where you live.</p><h2>What else is reviewed</h2><ul><li>Your medical history, including thyroid, pancreatic, kidney and gastrointestinal conditions.</li><li>Other medications, especially insulin or sulfonylureas.</li><li>Pregnancy, breastfeeding, or plans to become pregnant.</li><li>Previous weight-management attempts and what happened.</li></ul><h2>Why a quiz cannot decide</h2><p>A questionnaire collects information; it does not interpret it. Any service that guarantees approval from a form is not describing clinical care. At minimum, a licensed clinician should review your answers, be able to ask follow-up questions, and be free to say no.</p><h2>If you are not eligible</h2><p>Being declined is not a judgement about you. It usually means the risks outweigh the possible benefits for your particular history, and the right next step is a conversation with your own doctor about alternatives.</p><p>General information only. This article is not medical advice.</p>',
		),
		array(
			'slug'     => 'side-effects-what-to-expect-and-when-to-get-help',
			'title'    => 'Side effects: what to expect and when to get help',
			'excerpt'  => 'Common effects during the first weeks, the warning signs that need urgent attention, and how dose adjustments usually work.',
			'category' => 'Safety',
			'content'  => '<p>Most medications have side effects, and pretending otherwise does not help anyone make a good decision. Here is a measured summary of what people commonly report with GLP-1 class medications, and the symptoms that should never wait.</p><h2>Common, usually temporary</h2><p>Nausea, vomiting, diarrhoea, constipation, abdominal discomfort, fatigue and headache are frequently reported. Many people find these settle as the body adjusts, which is one reason clinicians often begin at a low dose and increase gradually.</p><h2>Seek urgent care</h2><ul><li>Severe or persistent abdominal pain, especially radiating to the back.</li><li>Persistent vomiting or signs of dehydration.</li><li>Pain in the upper right abdomen with fever or yellowing of the skin.</li><li>Difficulty breathing, or swelling of the face, lips or throat.</li></ul><p>These can indicate serious problems that need assessment, not a message through a website.</p><h2>How dose adjustments work</h2><p>Your clinician reviews how you are responding and may hold, reduce or increase your dose. Do not change your dose on your own, and do not take extra doses to make up for a missed one without advice.</p><h2>Keep a simple record</h2><p>Noting when symptoms occur, how long they last and what you ate can make a follow-up conversation much more useful.</p><p>General information only. This article is not medical advice.</p>',
		),
	);
}
