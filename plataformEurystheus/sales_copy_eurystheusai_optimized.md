# EurystheusAI Sales Copy Document (Optimized)

## I. Homepage (`resources/views/marketing/home.blade.php`)

### 1. Header Navigation
    *   **Logo/Brand Name:** EurystheusAI
        *   _Location:_ `<a href="{{ route('marketing.home') }}" class="text-xl font-bold ...">EurystheusAI</a>`
    *   **Login Link:** Login
        *   _Location:_ `<a href="{{ route('login') }}" ...>Login</a>`
    *   **Register Link:** Register
        *   _Location:_ `<a href="{{ route('register') }}" ...>Register</a>`

### 2. Hero Section (Main Welcome Area)
    *   **Headline:** Chega de Prompts Ruins. Transforme Problemas Complexos em Resultados Brilhantes.
        *   _Location:_ `<h1 class="text-5xl font-extrabold ...">Chega de Prompts Ruins. <span class="text-orange-500 dark:text-yellow-400">Transforme Problemas Complexos</span> em Resultados Brilhantes.</h1>`
    *   **Sub-headline/Pitch:** Descreva seu problema. Nossa IA não apenas entende, ela cria a "cadeia de pensamento" perfeita para que outros LLMs executem sua tarefa com precisão de mestre. Deixe o trabalho pesado para a nossa IA, e fique com os resultados hercúleos.
        *   _Location:_ `<p class="mt-6 text-xl ...">Descreva seu problema. Nossa IA não apenas entende...</p>`
    *   **Primary Call to Action (Button):** Gere Seu Primeiro Prompt Grátis
        *   _Location:_ `<a href="{{ route('register') }}?plan=free" ...>Gere Seu Primeiro Prompt Grátis</a>` (links to Register page with free plan pre-selected or identified)

### 3. "Pare de Lutar Contra a IA. Faça Ela Trabalhar Para Você." Section
    *   **Section Title:** Pare de Lutar Contra a IA. Faça Ela Trabalhar Para Você.
        *   _Location:_ `<h2 class="text-3xl font-bold ...">Pare de Lutar Contra a IA. Faça Ela Trabalhar Para Você.</h2>`
    *   **Feature 1 Title:** Economize Horas, Não Minutos
        *   _Location:_ `<h3 class="text-xl font-semibold ...">Economize Horas, Não Minutos</h3>`
    *   **Feature 1 Description:** Esqueça a tentativa e erro. Descreva seu objetivo e nossa IA constrói a engenharia de prompt por você, entregando em segundos o que levaria horas para aperfeiçoar.
        *   _Location:_ `<p class="text-gray-600 ...">Esqueça a tentativa e erro...</p>`
    *   **Feature 2 Title:** Resultados de Especialista, Sem Ser um
        *   _Location:_ `<h3 class="text-xl font-semibold ...">Resultados de Especialista, Sem Ser um</h3>`
    *   **Feature 2 Description:** Você não precisa ser um mestre em engenharia de prompt. Extraia o máximo potencial de LLMs complexos com cadeias de pensamento que garantem resultados mais ricos, detalhados e precisos.
        *   _Location:_ `<p class="text-gray-600 ...">Você não precisa ser um mestre...</p>`
    *   **Feature 3 Title:** Organize e Reutilize Sua Genialidade
        *   _Location:_ `<h3 class="text-xl font-semibold ...">Organize e Reutilize Sua Genialidade</h3>`
    *   **Feature 3 Description:** Salve suas "cadeias de pensamento" mais poderosas como *blueprints* reutilizáveis. Crie uma biblioteca pessoal de soluções para seus maiores desafios e replique o sucesso com um clique.
        *   _Location:_ `<p class="text-gray-600 ...">Salve suas '''cadeias de pensamento'''...</p>`

### 4. "Pronto Para Conquistar Seus 12 Trabalhos?" Section (Secondary Call to Action)
    *   **Section Title:** Pronto Para Conquistar Seus 12 Trabalhos?
        *   _Location:_ `<h2 class="text-3xl font-bold ...">Pronto Para Conquistar Seus 12 Trabalhos?</h2>`
    *   **Encouragement Text:** Junte-se a milhares de criadores que já transformaram seus maiores desafios em conquistas épicas com a EurystheusAI.
        *   _Location:_ `<p class="text-lg ...">Junte-se a milhares de criadores...</p>`
    *   **Secondary Call to Action (Button):** Criar Minha Conta Grátis
        *   _Location:_ `<a href="{{ route('register') }}" ...>Criar Minha Conta Grátis</a>`

### 5. Footer
    *   **Copyright:** &copy; {{ date('Y') }} EurystheusAI. All rights reserved.
        *   _Location:_ `<p>&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>`
    *   **Tagline:** Harnessing AI, Honoring Legend.
        *   _Location:_ `<p class="mt-1">Harnessing AI, Honoring Legend.</p>`

## II. Sales/Pricing Page (`resources/views/marketing/sales.blade.php`)

### 1. Header Navigation
    *   _(Same as Homepage)_

### 2. Hero Section (Pricing Page Intro)
    *   **Headline:** O Poder Certo Para Cada Desafio.
        *   _Location:_ `<h1 class="text-5xl font-extrabold ...">O Poder Certo Para Cada <span class="text-orange-500 dark:text-yellow-400">Desafio</span>.</h1>`
    *   **Sub-headline/Pitch:** Do seu primeiro experimento à automação em escala, temos o plano perfeito para transformar seus desafios em vitórias. Todos os planos pagos incluem uma **garantia de 14 dias de satisfação ou seu dinheiro de volta.**
        *   _Location:_ `<p class="mt-6 text-xl ...">Do seu primeiro experimento à automação em escala...</p>`

### 3. Pricing Plans Section
    *   **Plan 1: Free Plan**
        *   **Name:** Apprentice
            *   _Location:_ `<h2 class="text-2xl font-bold ...">Apprentice</h2>`
        *   **Price:** Free
            *   _Location:_ `<p class="text-orange-500 ... text-4xl ...">Free</p>`
        *   **Description:** Perfeito para explorar o poder da engenharia de prompt automatizada. Dê o primeiro passo na sua jornada hercúlea.
            *   _Location:_ `<p class="text-gray-600 ... mb-6">Perfeito para explorar o poder...</p>`
        *   **Features List:**
            *   15 gerações de prompt/mês
            *   Acesso a templates básicos para começar rápido
            *   Suporte da comunidade
            *   _Location:_ Inside the `<ul>` for the Apprentice plan.
        *   **Call to Action (Button):** Get Started
            *   _Location:_ `<a href="{{ route('register') }}?plan=free" ...>Get Started</a>`

    *   **Plan 2: Paid Plan (Most Popular)**
        *   **Badge:** Most Popular
            *   _Location:_ `<span class="absolute top-0 ...">Most Popular</span>`
        *   **Name:** Hero
            *   _Location:_ `<h2 class="text-2xl font-bold mb-2">Hero</h2>`
        *   **Price:** $12/month (Consider adding a toggle for "Pagar Anual e Ganhar 2 Meses Grátis")
            *   _Location:_ `<p class="text-4xl font-extrabold">$12<span class="text-xl font-normal">/month</span></p>`
        *   **Description:** Para profissionais e criadores que não têm tempo a perder. Libere todo o potencial da IA para resultados ilimitados.
            *   _Location:_ `<p class="opacity-90 mb-6">Para profissionais e criadores...</p>`
        *   **Features List:**
            *   Gerações de prompt ilimitadas
            *   Acesso a **toda a biblioteca** de templates avançados
            *   Histórico e execução direta de prompts
            *   **Crie e salve seus próprios blueprints** para replicar o sucesso
            *   **Suporte prioritário** para nunca ficar travado
            *   _Location:_ Inside the `<ul>` for the Hero plan.
        *   **Call to Action (Button):** Começar Com o Plano Hero
            *   _Location:_ `<a href="{{ route('register') }}?plan=hero" ...>Começar Com o Plano Hero</a>`

    *   **Plan 3: Enterprise/Custom Plan**
        *   **Name:** Titan
            *   _Location:_ `<h2 class="text-2xl font-bold ...">Titan</h2>`
        *   **Price:** Custom
            *   _Location:_ `<p class="text-orange-500 ... text-4xl ...">Custom</p>`
        *   **Description:** Soluções sob medida para equipes e empresas que buscam integrar o poder da EurystheusAI em sua escala.
            *   _Location:_ `<p class="text-gray-600 ... mb-6">Soluções sob medida para equipes...</p>`
        *   **Features List:**
            *   Tudo do plano Hero, e mais:
            *   Descontos por volume para sua equipe
            *   Gerente de conta dedicado
            *   Integrações customizadas e acesso à API
            *   _Location:_ Inside the `<ul>` for the Titan plan.
        *   **Call to Action (Button):** Contact Sales
            *   _Location:_ `<a href="mailto:sales@eurystheus.ai..." ...>Contact Sales</a>`

### 4. Social Proof Section (NEW)
    *   **Section Title:** O Que Nossos Heróis Estão Dizendo
        *   _Location:_ `<h2 class="text-3xl font-bold text-center ...">O Que Nossos Heróis Estão Dizendo</h2>`
    *   **Testimonial 1 (Placeholder):**
        *   **Quote:** "A EurystheusAI economizou para nossa equipe cerca de 10 horas por semana em P&D. O que antes era um processo de tentativa e erro, agora é uma linha de produção de resultados."
        *   **Attribution:** - Nome do Cliente, Cargo, Empresa
        *   _Location:_ First `div` within the social proof grid.
    *   **Testimonial 2 (Placeholder):**
        *   **Quote:** "Eu não sou programador, mas com a EurystheusAI consigo criar prompts que geram scripts e análises complexas. É um verdadeiro superpoder."
        *   **Attribution:** - Nome do Cliente, Profissão
        *   _Location:_ Second `div` within the social proof grid.

### 5. Frequently Asked Questions (FAQ) Section
    *   **Section Title:** Frequently Asked Questions
        *   _Location:_ `<h2 class="text-3xl font-bold ...">Frequently Asked Questions</h2>`
    *   **Question 1:** What is EurystheusAI?
        *   _Location:_ `<summary ...>What is EurystheusAI?</summary>`
    *   **Answer 1:** É uma plataforma de IA que constrói automaticamente a "engenharia de prompt" ideal para você. Em vez de adivinhar como pedir algo a um LLM, você simplesmente descreve seu problema e a EurystheusAI cria a instrução perfeita para obter o melhor resultado possível.
        *   _Location:_ `<p class="mt-2 ...">É uma plataforma de IA que constrói...</p>`
    *   **Question 2:** How does the 'Hero' plan differ from the free 'Apprentice' plan?
        *   _Location:_ `<summary ...>How does the 'Hero' plan differ...</summary>`
    *   **Answer 2:** O plano Hero é para uso profissional e remove todos os limites. Você obtém gerações ilimitadas, acesso a todos os templates e, o mais importante, a capacidade de salvar seus próprios "blueprints" para reutilizar suas melhores estratégias de prompt, economizando ainda mais tempo.
        *   _Location:_ `<p class="mt-2 ...">O plano Hero é para uso profissional...</p>`
    *   **Question 3:** Can I upgrade or downgrade my plan?
        *   _Location:_ `<summary ...>Can I upgrade or downgrade my plan?</summary>`
    *   **Answer 3:** Sim! Você pode mudar de plano a qualquer momento no seu painel. O upgrade é imediato e o sistema calcula a diferença automaticamente. Sem complicações.
        *   _Location:_ `<p class="mt-2 ...">Sim! Você pode mudar de plano...</p>`

### 6. Footer
    *   **Copyright:** &copy; {{ date('Y') }} EurystheusAI. All rights reserved.
        *   _Location:_ `<p>&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>`
    *   **Tagline:** Empowering Your AI Labors.
        *   _Location:_ `<p class="mt-1">Empowering Your AI Labors.</p>`

