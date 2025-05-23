# EurystheusAI Sales Copy Document

## I. Homepage (`resources/views/marketing/home.blade.php`)

### 1. Header Navigation
    *   **Logo/Brand Name:** EurystheusAI
        *   _Location:_ `<a href="{{ route('marketing.home') }}" class="text-xl font-bold ...">EurystheusAI</a>`
    *   **Login Link:** Login
        *   _Location:_ `<a href="{{ route('login') }}" ...>Login</a>`
    *   **Register Link:** Register
        *   _Location:_ `<a href="{{ route('register') }}" ...>Register</a>`

### 2. Hero Section (Main Welcome Area)
    *   **Headline:** Unleash the Power of AI. Conquer Your Tasks.
        *   _Location:_ `<h1 class="text-5xl font-extrabold ...">Unleash the Power of <span class="text-orange-500 dark:text-yellow-400">AI</span>. Conquer Your Tasks.</h1>`
    *   **Sub-headline/Pitch:** Inspired by the legendary Twelve Labors of Hercules, EurystheusAI empowers you to overcome your most challenging creative and analytical hurdles with cutting-edge AI prompt engineering.
        *   _Location:_ `<p class="mt-6 text-xl ...">Inspired by the legendary Twelve Labors of Hercules...</p>`
    *   **Primary Call to Action (Button):** Discover Your Power
        *   _Location:_ `<a href="{{ route('marketing.sales') }}" ...>Discover Your Power</a>` (links to Sales page)

### 3. "Why Choose EurystheusAI?" Section
    *   **Section Title:** Why Choose EurystheusAI?
        *   _Location:_ `<h2 class="text-3xl font-bold ...">Why Choose EurystheusAI?</h2>`
    *   **Feature 1 Title:** Intelligent Prompt Crafting
        *   _Location:_ `<h3 class="text-xl font-semibold ...">Intelligent Prompt Crafting</h3>`
    *   **Feature 1 Description:** Our platform guides you in creating highly effective prompts, turning complex requests into actionable AI instructions.
        *   _Location:_ `<p class="text-gray-600 ...">Our platform guides you...</p>` (within the first feature div)
    *   **Feature 2 Title:** Streamlined Workflow
        *   _Location:_ `<h3 class="text-xl font-semibold ...">Streamlined Workflow</h3>`
    *   **Feature 2 Description:** From idea to execution, manage your AI interactions seamlessly. Save, refine, and reuse your most successful prompts.
        *   _Location:_ `<p class="text-gray-600 ...">From idea to execution...</p>` (within the second feature div)
    *   **Feature 3 Title:** Achieve Herculean Results
        *   _Location:_ `<h3 class="text-xl font-semibold ...">Achieve Herculean Results</h3>`
    *   **Feature 3 Description:** Tackle your projects with the strength of AI, delivering results that once seemed impossible.
        *   _Location:_ `<p class="text-gray-600 ...">Tackle your projects...</p>` (within the third feature div)

### 4. "Ready to Start Your Labors?" Section (Secondary Call to Action)
    *   **Section Title:** Ready to Start Your Labors?
        *   _Location:_ `<h2 class="text-3xl font-bold ...">Ready to Start Your Labors?</h2>`
    *   **Encouragement Text:** Join EurystheusAI today and transform your approach to AI-powered creation.
        *   _Location:_ `<p class="text-lg ...">Join EurystheusAI today...</p>`
    *   **Secondary Call to Action (Button):** Sign Up for Free
        *   _Location:_ `<a href="{{ route('register') }}" ...>Sign Up for Free</a>`

### 5. Footer
    *   **Copyright:** &copy; {{ date('Y') }} EurystheusAI. All rights reserved.
        *   _Location:_ `<p>&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>`
    *   **Tagline:** Harnessing AI, Honoring Legend.
        *   _Location:_ `<p class="mt-1">Harnessing AI, Honoring Legend.</p>`

## II. Sales/Pricing Page (`resources/views/marketing/sales.blade.php`)

### 1. Header Navigation
    *   _(Same as Homepage)_

### 2. Hero Section (Pricing Page Intro)
    *   **Headline:** Choose Your Weapon. Master Your Prompts.
        *   _Location:_ `<h1 class="text-5xl font-extrabold ...">Choose Your <span class="text-orange-500 dark:text-yellow-400">Weapon</span>. Master Your <span class="text-orange-500 dark:text-yellow-400">Prompts</span>.</h1>`
    *   **Sub-headline/Pitch:** Select the plan that best suits your ambitions. Each tier is designed to equip you with the tools for AI-driven success.
        *   _Location:_ `<p class="mt-6 text-xl ...">Select the plan that best suits your ambitions...</p>`

### 3. Pricing Plans Section
    *   **Plan 1: Free Plan**
        *   **Name:** Apprentice
            *   _Location:_ `<h2 class="text-2xl font-bold ...">Apprentice</h2>`
        *   **Price:** Free
            *   _Location:_ `<p class="text-orange-500 ... text-4xl ...">Free</p>`
        *   **Description:** Begin your journey. Explore the fundamentals of prompt engineering.
            *   _Location:_ `<p class="text-gray-600 ... mb-6">Begin your journey...</p>`
        *   **Features List:**
            *   Limited prompt generations
            *   Access to basic prompt templates
            *   Community support
            *   _Location:_ Inside the `<ul>` for the Apprentice plan.
        *   **Call to Action (Button):** Get Started
            *   _Location:_ `<a href="{{ route('register') }}?plan=free" ...>Get Started</a>`

    *   **Plan 2: Paid Plan (Most Popular)**
        *   **Badge:** Most Popular
            *   _Location:_ `<span class="absolute top-0 ...">Most Popular</span>`
        *   **Name:** Hero
            *   _Location:_ `<h2 class="text-2xl font-bold mb-2">Hero</h2>` (within the Hero plan div)
        *   **Price:** $12/month
            *   _Location:_ `<p class="text-4xl font-extrabold mb-4">$12<span class="text-xl font-normal">/month</span></p>`
        *   **Description:** For serious creators. Unlock the full potential of AI prompt engineering.
            *   _Location:_ `<p class="opacity-90 mb-6">For serious creators...</p>`
        *   **Features List:**
            *   Unlimited prompt generations
            *   Access to all prompt templates
            *   Direct prompt execution & history
            *   Create and save custom blueprints
            *   Priority support
            *   _Location:_ Inside the `<ul>` for the Hero plan.
        *   **Call to Action (Button):** Choose Hero Plan
            *   _Location:_ `<a href="{{ route('register') }}?plan=hero" ...>Choose Hero Plan</a>`

    *   **Plan 3: Enterprise/Custom Plan**
        *   **Name:** Titan
            *   _Location:_ `<h2 class="text-2xl font-bold ...">Titan</h2>`
        *   **Price:** Custom
            *   _Location:_ `<p class="text-orange-500 ... text-4xl ...">Custom</p>`
        *   **Description:** For organizations with unique needs. Tailored solutions and dedicated support.
            *   _Location:_ `<p class="text-gray-600 ... mb-6">For organizations with unique needs...</p>`
        *   **Features List:**
            *   All Hero features, plus:
            *   Volume discounts
            *   Dedicated account manager
            *   Custom integrations
            *   _Location:_ Inside the `<ul>` for the Titan plan.
        *   **Call to Action (Button):** Contact Sales
            *   _Location:_ `<a href="mailto:sales@eurystheus.ai..." ...>Contact Sales</a>`

### 4. Frequently Asked Questions (FAQ) Section
    *   **Section Title:** Frequently Asked Questions
        *   _Location:_ `<h2 class="text-3xl font-bold ...">Frequently Asked Questions</h2>`
    *   **Question 1:** What is EurystheusAI?
        *   _Location:_ `<summary ...>What is EurystheusAI?</summary>`
    *   **Answer 1:** EurystheusAI is a platform designed to help you create, manage, and execute powerful AI prompts, inspired by the efficiency and problem-solving of Hercules' labors.
        *   _Location:_ `<p class="mt-2 ...">EurystheusAI is a platform...</p>` (within the first details tag)
    *   **Question 2:** How does the 'Hero' plan differ from the free 'Apprentice' plan?
        *   _Location:_ `<summary ...>How does the 'Hero' plan differ...</summary>`
    *   **Answer 2:** The Hero plan offers unlimited prompt generations, access to all prompt templates, direct prompt execution with history, the ability to create and save custom prompt blueprints, and priority support. The Apprentice plan has limitations on these features.
        *   _Location:_ `<p class="mt-2 ...">The Hero plan offers...</p>` (within the second details tag)
    *   **Question 3:** Can I upgrade or downgrade my plan?
        *   _Location:_ `<summary ...>Can I upgrade or downgrade my plan?</summary>`
    *   **Answer 3:** Yes, you can change your plan at any time. Changes will be prorated accordingly.
        *   _Location:_ `<p class="mt-2 ...">Yes, you can change your plan...</p>` (within the third details tag)

### 5. Footer
    *   **Copyright:** &copy; {{ date('Y') }} EurystheusAI. All rights reserved.
        *   _Location:_ `<p>&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>`
    *   **Tagline:** Empowering Your AI Labors.
        *   _Location:_ `<p class="mt-1">Empowering Your AI Labors.</p>` (Note: This tagline is slightly different from the homepage)
