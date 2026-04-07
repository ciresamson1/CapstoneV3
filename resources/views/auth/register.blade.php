<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Registration | PCMS</title>
    @vite(['resources/js/app.jsx'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl">
            <div class="border-b border-slate-200 bg-slate-950 px-6 py-8 text-white sm:px-10">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">PCMS Invite</p>
                <h1 class="mt-3 text-2xl font-semibold sm:text-3xl">Complete your account setup</h1>
                <p class="mt-2 text-sm text-slate-300">Finish your profile to start collaborating in the project dashboard.</p>
            </div>

            <div class="px-6 py-8 sm:px-10">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="role" value="{{ old('role', request('role', 'client')) }}">

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="johndoe" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="John" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="Doe" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Position</label>
                        <input type="text" name="position" value="{{ old('position') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="Project Manager" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Company</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="Your company" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', request('email')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" placeholder="you@company.com" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" required>
                    </div>

                    <div class="pt-2 md:col-span-2">
                        <button type="submit" class="w-full rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>