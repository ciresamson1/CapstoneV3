<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PCMS</title>
    @vite(['resources/js/app.jsx'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl lg:grid-cols-[0.95fr_1.05fr]">
            <aside class="bg-slate-950 px-7 py-8 text-white sm:px-10 sm:py-10">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">PCMS Portal</p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight">Welcome back</h1>
                <p class="mt-3 text-sm text-slate-300">Sign in to continue managing projects, teams, and client updates.</p>

                <div class="mt-8 space-y-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Security</p>
                        <p class="mt-2 text-sm text-slate-200">Role-based access ensures each user lands on the right dashboard.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Productivity</p>
                        <p class="mt-2 text-sm text-slate-200">Track project status, workload, and completion in one workspace.</p>
                    </div>
                </div>
            </aside>

            <main class="px-7 py-8 sm:px-10 sm:py-10">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Account Access</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Login</h2>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="you@company.com" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="Enter your password" required>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="w-full rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Login</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>
</html>