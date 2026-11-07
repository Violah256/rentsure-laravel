<nav class="border-b border-emerald-900 bg-emerald-950 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-20 items-center justify-between gap-4 py-4">
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3"
            >
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-400 text-xl font-black text-emerald-950">
                    R
                </span>

                <span>
                    <strong class="block text-lg">
                        RentSure
                    </strong>

                    <small class="text-emerald-200/70">
                        Nakawa Division
                    </small>
                </span>
            </a>

            <div class="flex items-center gap-4">
                <a
                    href="{{ route('home') }}"
                    class="hidden text-sm font-bold text-emerald-100 sm:block"
                >
                    Browse properties
                </a>

                <div class="hidden text-right sm:block">
                    <p class="text-sm font-bold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-emerald-200/70">
                        {{ auth()->user()->roleLabel() }}
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-bold hover:bg-emerald-900">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>