<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>RentSure | Rental Verification in Nakawa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
            <a
                href="{{ route('home') }}"
                class="flex items-center gap-3"
            >
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-800 text-xl font-black text-white">
                    R
                </span>

                <span>
                    <strong class="block text-xl text-emerald-950">
                        RentSure
                    </strong>

                    <small class="text-slate-500">
                        Nakawa Division
                    </small>
                </span>
            </a>

            <nav class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white"
                    >
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="text-sm font-bold text-slate-700"
                    >
                        Sign in
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white"
                    >
                        Create account
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="bg-emerald-950 text-white">
            <div class="mx-auto max-w-7xl px-5 py-16 lg:py-24">
                <span class="inline-flex rounded-full border border-emerald-700 bg-emerald-900 px-4 py-2 text-xs font-bold text-emerald-200">
                    HUMAN-REVIEWED RENTAL LISTINGS
                </span>

                <h1 class="mt-7 max-w-3xl text-4xl font-black leading-tight sm:text-6xl">
                    A clearer and safer way to rent in Nakawa.
                </h1>

                <p class="mt-5 max-w-2xl text-lg leading-8 text-emerald-100/75">
                    Search verified rental houses, preserve tenancy
                    records and report suspicious activity.
                </p>

                <form
                    method="GET"
                    action="{{ route('home') }}"
                    class="mt-9 grid gap-3 rounded-3xl bg-white p-4 text-slate-900 shadow-2xl md:grid-cols-[1fr_220px_auto]"
                >
                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by property, area or type"
                        class="rounded-xl border-slate-200 bg-slate-50"
                    >

                    <select
                        name="neighbourhood"
                        class="rounded-xl border-slate-200 bg-slate-50"
                    >
                        <option value="">
                            All neighbourhoods
                        </option>

                        @foreach([
                            'Banda',
                            'Bugolobi',
                            'Kyanja',
                            'Kyambogo',
                            'Naguru',
                            'Ntinda'
                        ] as $area)
                            <option
                                value="{{ $area }}"
                                @selected(
                                    request('neighbourhood') === $area
                                )
                            >
                                {{ $area }}
                            </option>
                        @endforeach
                    </select>

                    <button class="rounded-xl bg-amber-400 px-7 py-3 font-bold text-slate-950 hover:bg-amber-300">
                        Search
                    </button>
                </form>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-12">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">
                        Available homes
                    </p>

                    <h2 class="mt-2 text-3xl font-black">
                        Verified rental listings
                    </h2>
                </div>

                <p class="text-sm text-slate-500">
                    {{ $properties->total() }} properties
                </p>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($properties as $property)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative h-56 bg-emerald-950">
                            @if($property->imageUrl())
                                <img
                                    src="{{ $property->imageUrl() }}"
                                    alt="{{ $property->title }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div class="grid h-full place-items-center text-6xl font-black text-emerald-200">
                                    R
                                </div>
                            @endif

                            <span class="absolute left-4 top-4 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800 shadow">
                                ✓ Evidence reviewed
                            </span>
                        </div>

                        <div class="p-6">
                            <p class="text-xl font-black">
                                UGX {{ number_format($property->monthly_rent) }}

                                <span class="text-xs font-medium text-slate-400">
                                    / month
                                </span>
                            </p>

                            <h3 class="mt-3 text-lg font-bold">
                                {{ $property->title }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $property->address }},
                                {{ $property->neighbourhood }}
                            </p>

                            <div class="mt-5 flex gap-5 border-t border-slate-100 pt-4 text-sm font-semibold text-slate-600">
                                <span>
                                    {{ $property->bedrooms }} bedrooms
                                </span>

                                <span>
                                    {{ $property->bathrooms }} bathrooms
                                </span>
                            </div>

                            <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-500">
                                {{ $property->description }}
                            </p>

                            <div class="mt-5 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
                                Advertised by
                                <strong>
                                    {{ $property->advertiser->name }}
                                </strong>

                                · {{ ucfirst($property->advertiser->role) }}
                                · {{ $property->reference }}
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                        <h3 class="text-xl font-bold">
                            No matching properties
                        </h3>

                        <p class="mt-2 text-slate-500">
                            Try another search or neighbourhood.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $properties->links() }}
            </div>

            <div class="mt-12 rounded-3xl border border-amber-200 bg-amber-50 p-7">
                <h3 class="font-bold text-amber-950">
                    Never pay because someone creates urgency
                </h3>

                <p class="mt-2 text-sm leading-6 text-amber-900/75">
                    Visit the property, confirm the advertiser's
                    authority, use a written agreement and keep every
                    receipt. Verification does not replace an official
                    title search.
                </p>
            </div>
        </section>
    </main>
</body>
</html>