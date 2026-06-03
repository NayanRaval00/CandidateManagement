<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Job Application | Innoventix Solutions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom autofill styles to keep form looking glassy */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        textarea:-webkit-autofill:hover,
        textarea:-webkit-autofill:focus {
            -webkit-text-fill-color: #f8fafc;
            -webkit-box-shadow: 0 0 0px 1000px rgba(15, 23, 42, 0.6) inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Smooth transitions */
        .glass-input {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>

<body class="min-h-screen bg-[#090d16] text-slate-100 antialiased relative overflow-x-hidden py-12 px-4 flex items-center justify-center">

    <!-- Futuristic Glowing Background Blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-indigo-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[30vw] h-[30vw] rounded-full bg-blue-500/5 blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-4xl relative z-10">
        <!-- Logo / Brand Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-1.svg') }}" alt="Logo" class="h-25 mx-auto shadow-xl mb-3">
            <h2 class="text-xs uppercase tracking-[0.25em] font-semibold text-white mt-4">Join Our Team</h2>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight drop-shadow-md">
                Job Application Form
            </h1>
            <p class="text-sm text-slate-400 mt-2 max-w-md mx-auto">
                Submit your application details below and upload your resume. Let's build the future together.
            </p>
        </div>

        <!-- Glassmorphism Form Container -->
        <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.37)] rounded-2xl p-6 md:p-10 transition-all duration-300 hover:border-white/15">

            <!-- Success Notification -->
            @if(session('success'))
            <div class="mb-6 flex items-start gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 rounded-xl p-4">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div>
                    <h4 class="font-bold text-emerald-200">Success!</h4>
                    <p class="text-sm text-emerald-300/90 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <!-- Errors Notification -->
            @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 bg-rose-500/10 border border-rose-500/20 text-rose-300 rounded-xl p-4">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div>
                    <h4 class="font-bold text-rose-200">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside text-sm text-rose-300/90 mt-1 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Section: Personal Details -->
                <div>
                    <div class="flex items-center mb-6">
                        <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase mr-4">Personal Details</span>
                        <div class="flex-grow h-[1px] bg-gradient-to-r from-indigo-500/30 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Full Name <span class="text-rose-400">*</span></label>
                            <input type="text" name="name" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('name') }}" required placeholder="e.g. John Doe">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Email Address <span class="text-rose-400">*</span></label>
                            <input type="email" name="email" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('email') }}" required placeholder="e.g. john@example.com">
                        </div>

                        <!-- Mobile Number -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Mobile Number <span class="text-rose-400">*</span></label>
                            <input type="text" name="mobile" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('mobile') }}" required placeholder="e.g. +91 98765 43210">
                        </div>

                        <!-- Position Applying For -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Position Applying For <span class="text-rose-400">*</span></label>
                            <input type="text" name="position" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('position') }}" required placeholder="e.g. Senior Laravel Developer">
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">City <span class="text-rose-400">*</span></label>
                            <input type="text" name="city" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('city') }}" required placeholder="e.g. Mumbai">
                        </div>

                        <!-- State -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">State <span class="text-rose-400">*</span></label>
                            <input type="text" name="state" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('state') }}" required placeholder="e.g. Maharashtra">
                        </div>
                    </div>
                </div>

                <!-- Section: Professional Details -->
                <div>
                    <div class="flex items-center mb-6">
                        <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase mr-4">Professional Details</span>
                        <div class="flex-grow h-[1px] bg-gradient-to-r from-indigo-500/30 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Current Company Name -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Current Company Name</label>
                            <input type="text" name="current_company_name" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('current_company_name') }}" placeholder="e.g. TechCorp Solutions">
                        </div>

                        <!-- Current Position -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Current Position</label>
                            <input type="text" name="current_position" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('current_position') }}" placeholder="e.g. Software Engineer">
                        </div>

                        <!-- Current CTC -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Current CTC (in ₹)</label>
                            <input type="number" name="current_ctc" step="0.01" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('current_ctc') }}" placeholder="e.g. 800000">
                        </div>

                        <!-- Expected CTC -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Expected CTC (in ₹)</label>
                            <input type="number" name="expected_ctc" step="0.01" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('expected_ctc') }}" placeholder="e.g. 1200000">
                        </div>

                        <!-- Notice Period -->
                        <div class="md:col-span-2">
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Notice Period</label>
                            <input type="text" name="notice_period" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('notice_period') }}" placeholder="e.g. 30 days, Immediate">
                        </div>

                        <!-- Reason for Job Change -->
                        <div class="md:col-span-2">
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Reason for Job Change</label>
                            <textarea name="reason_for_job_change" rows="3" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" placeholder="Briefly explain your reason for seeking a new opportunity...">{{ old('reason_for_job_change') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Education & Resume -->
                <div>
                    <div class="flex items-center mb-6">
                        <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase mr-4">Education & Resume</span>
                        <div class="flex-grow h-[1px] bg-gradient-to-r from-indigo-500/30 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Education -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Education</label>
                            <input type="text" name="education" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10" value="{{ old('education') }}" placeholder="e.g. B.E. in Computer Science">
                        </div>

                        <!-- Upload Resume -->
                        <div>
                            <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Upload Resume (PDF or DOC) <span class="text-rose-400">*</span></label>
                            <input type="file" name="resume" class="glass-input w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-slate-300 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white/10 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 file:cursor-pointer" accept=".pdf,.doc,.docx" required>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl shadow-lg hover:shadow-indigo-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>