<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Innoventix Solutions | Next-Gen Digital Products & AI</title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #090d16;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        .hero-gradient {
            background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.05) 50%, transparent 100%);
        }
    </style>
</head>

<body class="min-h-screen bg-[#070b13] text-slate-100 antialiased overflow-x-hidden">

    <!-- Floating Navigation Bar -->
    <header class="fixed top-0 inset-x-0 z-50 backdrop-blur-md bg-[#070b13]/60 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-1.svg') }}" alt="Logo" class="h-10 w-auto">
                <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent"></span>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#services" class="hover:text-indigo-400 transition-colors duration-200">Services</a>
                <a href="#about" class="hover:text-indigo-400 transition-colors duration-200">About Us</a>
                <a href="#chatbot" class="hover:text-indigo-400 transition-colors duration-200">AI Assistant</a>
                <a href="#careers" class="hover:text-indigo-400 transition-colors duration-200">Careers</a>
            </nav>

            <!-- CTA Buttons -->
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                @auth
                <a href="{{ url('/admin') }}" class="text-sm font-medium hover:text-indigo-400 transition-colors duration-200">Admin Panel</a>
                @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200">Admin Login</a>
                @endauth
                @endif
                <a href="/save-details" class="bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold uppercase tracking-wider py-2.5 px-5 rounded-lg shadow-lg shadow-indigo-600/20 transform hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                    Apply Now
                </a>
            </div>
        </div>
    </header>

    <!-- Glowing Background Elements -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[600px] hero-gradient pointer-events-none -z-10"></div>
    <div class="absolute top-1/4 left-[-10%] w-[45vw] h-[45vw] bg-indigo-600/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>
    <div class="absolute top-1/2 right-[-10%] w-[45vw] h-[45vw] bg-purple-600/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>

    <!-- Hero Section -->
    <section class="relative pt-36 pb-20 md:pt-48 md:pb-32 overflow-hidden flex flex-col items-center text-center px-4">
        <div class="max-w-4xl mx-auto flex flex-col items-center">
            <!-- Specified Logo -->
            <img src="{{ asset('images/logo-1.svg') }}" alt="Logo" class="h-25 mx-auto shadow-xl mb-6 transform hover:scale-105 transition-transform duration-300">

            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold uppercase tracking-wider mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                We are hiring remote builders
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold text-white tracking-tight leading-[1.1] mb-6">
                Engineering <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-indigo-300 bg-clip-text text-transparent">Next-Gen</span> Intelligent Products
            </h1>

            <p class="text-lg md:text-xl text-slate-400 font-medium max-w-2xl mx-auto mb-10 leading-relaxed">
                Innoventix is an elite product development studio. We architect premium Laravel applications, custom AI automation agents, and modern cloud infrastructure.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full">
                <a href="/save-details" class="w-full sm:w-auto bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-4 px-8 rounded-xl shadow-lg hover:shadow-indigo-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer">
                    Join Our Elite Team
                </a>
                <a href="#services" class="w-full sm:w-auto bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 font-semibold py-4 px-8 rounded-xl transition-all duration-200">
                    Explore Our Expertise
                </a>
            </div>
        </div>
    </section>

    <!-- Services / Strengths Section -->
    <section id="services" class="py-20 md:py-32 border-t border-white/5 relative bg-slate-950/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 md:mb-24">
                <h2 class="text-xs uppercase tracking-[0.2em] font-semibold text-indigo-400 mb-3">Our Capability</h2>
                <p class="text-3xl md:text-5xl font-extrabold text-white tracking-tight">Focusing on what truly matters: quality code and exceptional UX.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="group backdrop-blur-xl bg-slate-900/30 border border-white/5 p-8 rounded-2xl hover:border-indigo-500/30 hover:bg-slate-900/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75 16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Custom Web Engineering</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        We build robust web applications using Laravel, Filament, and Livewire. Clean structure, tested architectures, and blazing performance.
                    </p>
                </div>

                <!-- Service 2 -->
                <div class="group backdrop-blur-xl bg-slate-900/30 border border-white/5 p-8 rounded-2xl hover:border-indigo-500/30 hover:bg-slate-900/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 21l8.982-11.861H13.62l.812-5.096L5.45 15.904h4.363Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">AI & Conversational Bots</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Intelligent AI chatbot integrations and custom agents tailored to orchestrate workflows, parse data sources, and streamline user requests.
                    </p>
                </div>

                <!-- Service 3 -->
                <div class="group backdrop-blur-xl bg-slate-900/30 border border-white/5 p-8 rounded-2xl hover:border-indigo-500/30 hover:bg-slate-900/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Cloud & DevOps Automation</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Scaleable configurations on server platforms like Laravel Cloud. Smooth pipelines, auto-scaling instances, and rigid database provisioning.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Feature Showcase Section -->
    <section id="chatbot" class="py-20 md:py-32 border-t border-white/5 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                <div>
                    <h2 class="text-xs uppercase tracking-[0.2em] font-semibold text-indigo-400 mb-3">AI Capabilities</h2>
                    <h3 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-6 leading-tight">Meet the Innoventix Bot Ecosystem</h3>
                    <p class="text-slate-400 leading-relaxed mb-8 font-medium">
                        We developed a modular chatbot engine that securely executes system workflows. Our Bot parses documentation, queries databases, and monitors attendance or assets natively with Spatie permission policies.
                    </p>

                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-slate-300 font-medium">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Semantic database query assistant
                        </li>
                        <li class="flex items-center gap-3 text-slate-300 font-medium">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Real-time natural language analytics
                        </li>
                        <li class="flex items-center gap-3 text-slate-300 font-medium">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Automated alerting and scheduled triggers
                        </li>
                    </ul>
                </div>

                <!-- Chatbot Mockup Widget -->
                <div class="relative">
                    <div class="absolute -inset-0.5 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-2xl blur-lg opacity-30 pointer-events-none"></div>
                    <div class="relative backdrop-blur-xl bg-slate-950/80 border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                        <!-- Chat Header -->
                        <div class="px-6 py-4 bg-white/5 border-b border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-ping absolute"></div>
                                <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 relative"></div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Innoventix Bot</h4>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">AI Assistant</p>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400 font-medium">Online</span>
                        </div>

                        <!-- Chat Messages -->
                        <div class="p-6 space-y-4 h-80 overflow-y-auto text-sm leading-relaxed">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">IB</div>
                                <div class="bg-white/5 text-slate-200 border border-white/5 rounded-2xl px-4 py-3 max-w-[80%]">
                                    Hello! I am the Innoventix AI assistant. How can I help you manage candidate reviews, attendance, or assets today?
                                </div>
                            </div>

                            <div class="flex items-start gap-3 justify-end">
                                <div class="bg-indigo-600 text-white rounded-2xl px-4 py-3 max-w-[80%]">
                                    How many candidates applied for the Laravel Developer position today?
                                </div>
                            </div>

                            <div class="flex items-start gap-3 animate-pulse">
                                <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">IB</div>
                                <div class="bg-white/5 text-slate-400 border border-white/5 rounded-2xl px-4 py-2">
                                    <div class="flex gap-1.5 items-center py-1">
                                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div class="px-6 py-4 bg-white/5 border-t border-white/10 flex gap-3">
                            <input type="text" placeholder="Type a message..." disabled class="flex-1 bg-white/5 border border-white/5 rounded-xl px-4 py-2.5 text-xs text-slate-400 outline-none">
                            <button disabled class="bg-indigo-600/30 text-indigo-300/50 px-4 py-2.5 rounded-xl text-xs font-semibold">Send</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Stats showcase section -->
    <section id="about" class="py-20 md:py-32 border-t border-white/5 bg-slate-950/40 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12">
                <div class="text-center">
                    <p class="text-4xl md:text-6xl font-extrabold text-white bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">150+</p>
                    <p class="text-slate-400 font-semibold uppercase tracking-wider text-xs mt-3">Projects Delivered</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl md:text-6xl font-extrabold text-white bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">99%</p>
                    <p class="text-slate-400 font-semibold uppercase tracking-wider text-xs mt-3">Client Happiness</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl md:text-6xl font-extrabold text-white bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">24/7</p>
                    <p class="text-slate-400 font-semibold uppercase tracking-wider text-xs mt-3">Smart Automation</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl md:text-6xl font-extrabold text-white bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">100%</p>
                    <p class="text-slate-400 font-semibold uppercase tracking-wider text-xs mt-3">Remote Operations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Careers Call To Action Section -->
    <section id="careers" class="py-20 md:py-32 border-t border-white/5 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 relative z-10 text-center">
            <div class="absolute -inset-0.5 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-3xl blur-xl opacity-10 pointer-events-none -z-10"></div>

            <div class="backdrop-blur-xl bg-slate-900/30 border border-white/10 rounded-3xl p-8 md:p-16">
                <h2 class="text-xs uppercase tracking-[0.25em] font-semibold text-indigo-400 mb-3">Join The Studio</h2>
                <h3 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-6">Build the Future of Automation</h3>

                <p class="text-slate-400 leading-relaxed max-w-2xl mx-auto mb-10 font-medium">
                    We are constantly looking for talented software developers, UI/UX designers, and systems architects who are passionate about crafting outstanding digital experiences.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto">
                    <a href="/save-details" class="w-full sm:flex-1 bg-white text-slate-950 hover:bg-slate-100 font-semibold py-4 px-6 rounded-xl shadow-lg transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer">
                        Apply Now
                    </a>
                    <a href="#services" class="w-full sm:flex-1 bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 font-semibold py-4 px-6 rounded-xl transition-all duration-200">
                        Our Culture
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 border-t border-white/5 bg-[#05080e]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-1.svg') }}" alt="Logo" class="h-8 w-auto">
                <span class="text-sm font-bold text-slate-400">&copy; {{ date('Y') }} Innoventix Solutions. All rights reserved.</span>
            </div>

            <div class="flex items-center gap-6 text-xs font-medium text-slate-500">
                <a href="#" class="hover:text-indigo-400 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-indigo-400 transition-colors">Terms of Service</a>
                <a href="mailto:info@innoventix.com" class="hover:text-indigo-400 transition-colors">Contact</a>
            </div>
        </div>
    </footer>

</body>

</html>