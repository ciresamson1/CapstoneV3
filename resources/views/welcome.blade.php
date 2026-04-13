{{-- FOR ADVISER CHECKING - Sir tan will provide comment to this app --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Coordination System</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">

    <div class="flex min-h-screen flex-col lg:flex-row">

        {{-- Left panel --}}
        <div class="relative flex w-full flex-col justify-between bg-slate-950 px-6 py-10 text-white sm:px-10 sm:py-12 lg:w-[46%]">
            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white p-1">
                        <img src="/images/sgpro-logo.webp" alt="SGpro Logo" class="h-full w-full object-contain">
                    </div>
                    <span class="text-sm font-semibold tracking-wide text-slate-300">SGpro.co PCMS Portal</span>
                </div>

                <h1 class="mt-10 text-3xl font-bold leading-tight sm:mt-14 sm:text-4xl lg:text-5xl">
                    Manage projects.<br>
                    <span class="text-brand-400">Coordinate teams.</span>
                </h1>
                <p class="mt-4 max-w-sm text-base text-slate-400 sm:mt-5">
                    A single workspace for project managers, digital marketers, and clients to stay aligned and move faster.
                </p>

                <div class="mt-8 space-y-3 sm:mt-10 sm:space-y-4">
                    <div class="flex items-start gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-500/20 text-brand-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Security</p>
                            <p class="mt-1 text-sm text-slate-300">Role-based access — everyone lands on the right dashboard.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-400/20 text-brand-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Productivity</p>
                            <p class="mt-1 text-sm text-slate-300">Track tasks, comments, timelines and completion in one place.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-600/20 text-brand-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Collaboration</p>
                            <p class="mt-1 text-sm text-slate-300">Real-time comments, Link share and instant notifications.</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-10 text-xs text-slate-600 sm:mt-12">&copy; {{ date('Y') }} Project Coordination System</p>
        </div>

        {{-- Right panel --}}
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-10 sm:px-8 sm:py-12">
            <div class="w-full max-w-md">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Get started</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Welcome back</h2>
                <p class="mt-2 text-sm text-slate-500">Sign in to your account or create a new one.</p>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:mt-8 sm:grid-cols-2 sm:gap-4">
                    <a href="{{ route('login') }}"
                        class="flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 py-4 text-sm font-semibold text-white shadow-lg shadow-[#dbe2ff] transition hover:bg-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Login
                    </a>
                    <a href="https://SGpro.co" target="_blank" rel="noopener noreferrer"
                        class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Go to our website
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-3 gap-3 text-center sm:mt-12 sm:gap-4">
                    <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm sm:p-4">
                        <p class="text-xl font-bold text-brand-600 sm:text-2xl">PM</p>
                        <p class="mt-1 text-xs text-slate-500">Project Manager</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm sm:p-4">
                        <p class="text-xl font-bold text-brand-400 sm:text-2xl">DM</p>
                        <p class="mt-1 text-xs text-slate-500">Digital Marketer</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm sm:p-4">
                        <p class="text-xl font-bold text-brand-600 sm:text-2xl">CL</p>
                        <p class="mt-1 text-xs text-slate-500">Client</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>