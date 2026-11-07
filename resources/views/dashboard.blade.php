<x-app-layout>
    <div class="min-h-screen bg-slate-100 py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
                    <p class="font-bold">
                        Please correct the following:
                    </p>

                    <ul class="mt-2 list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-3xl bg-emerald-950 p-8 text-white shadow-xl">
                <p class="text-sm font-bold uppercase tracking-widest text-emerald-300">
                    {{ auth()->user()->roleLabel() }} workspace
                </p>

                <h1 class="mt-3 text-3xl font-black">
                    Welcome, {{ auth()->user()->name }}
                </h1>

                <p class="mt-3 max-w-3xl leading-7 text-emerald-100/70">
                    Manage properties, verification evidence, rental
                    records, receipts and fraud reports.
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">Properties</p>
                    <p class="mt-2 text-3xl font-black">
                        {{ $properties->count() }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Verification records
                    </p>
                    <p class="mt-2 text-3xl font-black">
                        {{ $documents->count() }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Tenancies
                    </p>
                    <p class="mt-2 text-3xl font-black">
                        {{ $tenancies->count() }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Fraud reports
                    </p>
                    <p class="mt-2 text-3xl font-black">
                        {{ $reports->count() }}
                    </p>
                </div>
            </section>

            @if(auth()->user()->canAdvertiseProperties())
                <section class="grid gap-6 xl:grid-cols-2">
                    <div class="rounded-3xl bg-white p-7 shadow-sm">
                        <h2 class="text-2xl font-black">
                            Add rental property
                        </h2>

                        <form
                            method="POST"
                            action="{{ route('properties.store') }}"
                            enctype="multipart/form-data"
                            class="mt-6 grid gap-4 sm:grid-cols-2"
                        >
                            @csrf

                            <input
                                name="title"
                                required
                                placeholder="Property title"
                                class="rounded-xl border-slate-300 sm:col-span-2"
                            >

                            <select
                                name="type"
                                required
                                class="rounded-xl border-slate-300"
                            >
                                <option value="">Property type</option>
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="studio">Studio</option>
                                <option value="single_room">Single room</option>
                            </select>

                            <select
                                name="neighbourhood"
                                required
                                class="rounded-xl border-slate-300"
                            >
                                <option value="">Neighbourhood</option>

                                @foreach([
                                    'Banda',
                                    'Bugolobi',
                                    'Kyanja',
                                    'Kyambogo',
                                    'Naguru',
                                    'Ntinda'
                                ] as $area)
                                    <option value="{{ $area }}">
                                        {{ $area }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                name="address"
                                required
                                placeholder="Road or landmark"
                                class="rounded-xl border-slate-300 sm:col-span-2"
                            >

                            <input
                                name="monthly_rent"
                                type="number"
                                min="0"
                                required
                                placeholder="Monthly rent"
                                class="rounded-xl border-slate-300"
                            >

                            <div class="grid grid-cols-2 gap-3">
                                <input
                                    name="bedrooms"
                                    type="number"
                                    min="0"
                                    value="1"
                                    required
                                    class="rounded-xl border-slate-300"
                                >

                                <input
                                    name="bathrooms"
                                    type="number"
                                    min="0"
                                    value="1"
                                    required
                                    class="rounded-xl border-slate-300"
                                >
                            </div>

                            <textarea
                                name="description"
                                required
                                rows="3"
                                placeholder="Amenities and rental terms"
                                class="rounded-xl border-slate-300 sm:col-span-2"
                            ></textarea>

                            <input
                                name="image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="rounded-xl border border-slate-300 p-3 sm:col-span-2"
                            >

                            <button class="rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white sm:col-span-2">
                                Save property
                            </button>
                        </form>
                    </div>

                    <div class="rounded-3xl bg-white p-7 shadow-sm">
                        <h2 class="text-2xl font-black">
                            Submit verification evidence
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Documents are private and available only
                            to the owner and administrators.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('verification.store') }}"
                            enctype="multipart/form-data"
                            class="mt-6 grid gap-4"
                        >
                            @csrf

                            <select
                                name="property_id"
                                required
                                class="rounded-xl border-slate-300"
                            >
                                <option value="">
                                    Choose property
                                </option>

                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}">
                                        {{ $property->reference }}
                                        — {{ $property->title }}
                                    </option>
                                @endforeach
                            </select>

                            <select
                                name="document_type"
                                required
                                class="rounded-xl border-slate-300"
                            >
                                <option value="">Document type</option>
                                <option value="national_id">National ID</option>
                                <option value="ownership_evidence">
                                    Ownership evidence
                                </option>
                                <option value="authority_letter">
                                    Authority letter
                                </option>
                                <option value="owner_consent">
                                    Owner consent
                                </option>
                                <option value="other">Other</option>
                            </select>

                            <input
                                name="document"
                                type="file"
                                required
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="rounded-xl border border-slate-300 p-3"
                            >

                            <button class="rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white">
                                Upload evidence
                            </button>
                        </form>
                    </div>
                </section>
            @endif

            <section class="rounded-3xl bg-white p-7 shadow-sm">
                <h2 class="text-2xl font-black">
                    Property records
                </h2>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="p-4">Reference</th>
                                <th class="p-4">Property</th>
                                <th class="p-4">Rent</th>
                                <th class="p-4">Verification</th>
                                <th class="p-4">Availability</th>
                                <th class="p-4">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse($properties as $property)
                                <tr>
                                    <td class="p-4 font-bold">
                                        {{ $property->reference }}
                                    </td>

                                    <td class="p-4">
                                        <p class="font-bold">
                                            {{ $property->title }}
                                        </p>

                                        <p class="text-slate-500">
                                            {{ $property->neighbourhood }}
                                        </p>
                                    </td>

                                    <td class="p-4">
                                        UGX
                                        {{ number_format($property->monthly_rent) }}
                                    </td>

                                    <td class="p-4 capitalize">
                                        {{ $property->verification_status }}
                                    </td>

                                    <td class="p-4 capitalize">
                                        {{ $property->availability_status }}
                                    </td>

                                    <td class="p-4">
                                        @can('update', $property)
                                            <form
                                                method="POST"
                                                action="{{ route('properties.toggle', $property) }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button class="rounded-lg border border-slate-300 px-3 py-2 font-bold">
                                                    Toggle availability
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="6"
                                        class="p-8 text-center text-slate-500"
                                    >
                                        No properties available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if(auth()->user()->isAdministrator())
                <section class="rounded-3xl border border-amber-200 bg-white p-7 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-700">
                        Administrator review
                    </p>

                    <h2 class="mt-2 text-2xl font-black">
                        Verification review queue
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Review identity separately from authority to
                        advertise. Every decision is stored in the
                        decision history and audit log.
                    </p>

                    <div class="mt-6 space-y-4">
                        @forelse($documents as $document)
                            <article class="rounded-2xl border border-slate-200 p-5">
                                <div class="grid gap-4 lg:grid-cols-[1fr_auto]">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="font-black">
                                                {{ $document->submitter->name }}
                                            </h3>

                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ str_replace('_', ' ', ucfirst($document->document_type)) }}
                                            ·
                                            {{ $document->property->reference }}
                                            ·
                                            {{ $document->property->title }}
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route('verification.download', $document) }}"
                                        class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-bold"
                                    >
                                        Download protected document
                                    </a>
                                </div>

                                @if($document->status === 'pending')
                                    <form
                                        method="POST"
                                        action="{{ route('verification.review', $document) }}"
                                        class="mt-5 grid gap-3 border-t border-slate-100 pt-5 md:grid-cols-[1fr_auto_auto_auto]"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            name="notes"
                                            placeholder="Administrator review notes"
                                            class="rounded-xl border-slate-300"
                                        >

                                        <button
                                            name="decision"
                                            value="approved"
                                            class="rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white"
                                        >
                                            Approve
                                        </button>

                                        <button
                                            name="decision"
                                            value="clarification_required"
                                            class="rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-slate-950"
                                        >
                                            Request clarification
                                        </button>

                                        <button
                                            name="decision"
                                            value="rejected"
                                            class="rounded-xl bg-red-700 px-4 py-3 text-sm font-bold text-white"
                                        >
                                            Reject
                                        </button>
                                    </form>
                                @endif

                                @if($document->decisions->isNotEmpty())
                                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Decision history
                                        </p>

                                        @foreach($document->decisions as $decision)
                                            <div class="mt-3 text-sm">
                                                <strong class="capitalize">
                                                    {{ str_replace('_', ' ', $decision->decision) }}
                                                </strong>

                                                by
                                                {{ $decision->administrator->name }}

                                                <span class="text-slate-400">
                                                    ·
                                                    {{ $decision->created_at->format('d M Y H:i') }}
                                                </span>

                                                @if($decision->notes)
                                                    <p class="mt-1 text-slate-500">
                                                        {{ $decision->notes }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-2xl bg-slate-50 p-8 text-center text-slate-500">
                                No verification documents submitted.
                            </p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if(auth()->user()->canAdvertiseProperties())
                <section class="rounded-3xl bg-white p-7 shadow-sm">
                    <h2 class="text-2xl font-black">
                        Create tenancy
                    </h2>

                    <form
                        method="POST"
                        action="{{ route('tenancies.store') }}"
                        class="mt-6 grid gap-4 md:grid-cols-3"
                    >
                        @csrf

                        <select
                            name="property_id"
                            required
                            class="rounded-xl border-slate-300"
                        >
                            <option value="">Choose property</option>

                            @foreach($properties as $property)
                                <option value="{{ $property->id }}">
                                    {{ $property->title }}
                                </option>
                            @endforeach
                        </select>

                        <select
                            name="tenant_id"
                            required
                            class="rounded-xl border-slate-300"
                        >
                            <option value="">Choose tenant</option>

                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}">
                                    {{ $tenant->name }}
                                    — {{ $tenant->email }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            name="monthly_rent"
                            type="number"
                            min="0"
                            required
                            placeholder="Monthly rent"
                            class="rounded-xl border-slate-300"
                        >

                        <input
                            name="start_date"
                            type="date"
                            required
                            class="rounded-xl border-slate-300"
                        >

                        <input
                            name="end_date"
                            type="date"
                            class="rounded-xl border-slate-300"
                        >

                        <input
                            name="notes"
                            placeholder="Optional notes"
                            class="rounded-xl border-slate-300"
                        >

                        <button class="rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white md:col-span-3">
                            Create tenancy
                        </button>
                    </form>
                </section>
            @endif

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-3xl bg-white p-7 shadow-sm">
                    <h2 class="text-2xl font-black">
                        Tenancy records
                    </h2>

                    <div class="mt-6 space-y-4">
                        @forelse($tenancies as $tenancy)
                            <article class="rounded-2xl bg-slate-50 p-5">
                                <div class="flex justify-between gap-4">
                                    <div>
                                        <p class="font-black">
                                            {{ $tenancy->property->title }}
                                        </p>

                                        <p class="text-sm text-slate-500">
                                            {{ $tenancy->reference }}
                                            · {{ ucfirst($tenancy->status) }}
                                        </p>
                                    </div>

                                    <strong>
                                        UGX
                                        {{ number_format($tenancy->monthly_rent) }}
                                    </strong>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('payments.store') }}"
                                    class="mt-5 grid gap-3 border-t border-slate-200 pt-5 sm:grid-cols-2"
                                >
                                    @csrf

                                    <input
                                        type="hidden"
                                        name="tenancy_id"
                                        value="{{ $tenancy->id }}"
                                    >

                                    <input
                                        name="amount"
                                        type="number"
                                        min="0"
                                        required
                                        placeholder="Amount"
                                        class="rounded-xl border-slate-300"
                                    >

                                    <input
                                        name="payment_date"
                                        type="date"
                                        required
                                        class="rounded-xl border-slate-300"
                                    >

                                    <select
                                        name="payment_type"
                                        required
                                        class="rounded-xl border-slate-300"
                                    >
                                        <option value="rent">Rent</option>
                                        <option value="deposit">Deposit</option>
                                        <option value="viewing_fee">
                                            Viewing fee
                                        </option>
                                        <option value="other">Other</option>
                                    </select>

                                    <input
                                        name="payment_method"
                                        placeholder="Payment method"
                                        class="rounded-xl border-slate-300"
                                    >

                                    <button class="rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white sm:col-span-2">
                                        Save receipt
                                    </button>
                                </form>
                            </article>
                        @empty
                            <p class="text-center text-slate-500">
                                No tenancy records.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-7 shadow-sm">
                    <h2 class="text-2xl font-black">
                        Recent payment receipts
                    </h2>

                    <div class="mt-6 space-y-3">
                        @forelse(
                            $tenancies->flatMap->paymentReceipts
                                ->sortByDesc('payment_date')
                            as $receipt
                        )
                            <div class="flex justify-between rounded-xl bg-slate-50 p-4">
                                <div>
                                    <p class="font-bold">
                                        {{ $receipt->receipt_number }}
                                    </p>

                                    <p class="text-xs capitalize text-slate-500">
                                        {{ str_replace('_', ' ', $receipt->payment_type) }}
                                        ·
                                        {{ $receipt->payment_date->format('d M Y') }}
                                    </p>
                                </div>

                                <strong>
                                    UGX
                                    {{ number_format($receipt->amount) }}
                                </strong>
                            </div>
                        @empty
                            <p class="text-center text-slate-500">
                                No payment receipts.
                            </p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-red-200 bg-white p-7 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">
                    Anti-fraud centre
                </p>

                <h2 class="mt-2 text-2xl font-black">
                    Report suspicious rental activity
                </h2>

                @unless(auth()->user()->isAdministrator())
                    <form
                        method="POST"
                        action="{{ route('fraud-reports.store') }}"
                        enctype="multipart/form-data"
                        class="mt-6 grid gap-4 md:grid-cols-2"
                    >
                        @csrf

                        <select
                            name="property_id"
                            class="rounded-xl border-slate-300"
                        >
                            <option value="">General report</option>

                            @foreach($properties as $property)
                                <option value="{{ $property->id }}">
                                    {{ $property->reference }}
                                    — {{ $property->title }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            name="advertiser_name"
                            placeholder="Advertiser name, if known"
                            class="rounded-xl border-slate-300"
                        >

                        <select
                            name="reason"
                            required
                            class="rounded-xl border-slate-300"
                        >
                            <option value="">Choose reason</option>
                            <option value="advance_payment">
                                Advance payment
                            </option>
                            <option value="copied_photos">
                                Copied photographs
                            </option>
                            <option value="impersonation">
                                Impersonation
                            </option>
                            <option value="nonexistent_property">
                                Non-existent property
                            </option>
                            <option value="multiple_renting">
                                Multiple renting
                            </option>
                            <option value="other">Other</option>
                        </select>

                        <input
                            name="evidence"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="rounded-xl border border-slate-300 p-3"
                        >

                        <textarea
                            name="description"
                            required
                            rows="4"
                            placeholder="Describe what happened"
                            class="rounded-xl border-slate-300 md:col-span-2"
                        ></textarea>

                        <button class="rounded-xl bg-red-700 px-5 py-3 font-bold text-white md:col-span-2">
                            Submit report
                        </button>
                    </form>
                @endunless

                <div class="mt-7 space-y-4">
                    @forelse($reports as $report)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="grid gap-4 lg:grid-cols-[1fr_auto]">
                                <div>
                                    <p class="font-black">
                                        {{ $report->reference }}
                                        ·
                                        {{ str_replace('_', ' ', ucfirst($report->reason)) }}
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        {{ $report->description }}
                                    </p>
                                </div>

                                <span class="h-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                    {{ str_replace('_', ' ', ucfirst($report->status)) }}
                                </span>
                            </div>

                            @if(auth()->user()->isAdministrator())
                                <form
                                    method="POST"
                                    action="{{ route('fraud-reports.review', $report) }}"
                                    class="mt-5 grid gap-3 border-t border-slate-100 pt-5 md:grid-cols-[180px_1fr_auto]"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <select
                                        name="status"
                                        required
                                        class="rounded-xl border-slate-300"
                                    >
                                        <option value="under_review">
                                            Under review
                                        </option>
                                        <option value="resolved">
                                            Resolved
                                        </option>
                                        <option value="dismissed">
                                            Dismissed
                                        </option>
                                    </select>

                                    <input
                                        name="administrator_notes"
                                        placeholder="Administrator notes"
                                        class="rounded-xl border-slate-300"
                                    >

                                    <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white">
                                        Update report
                                    </button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="text-center text-slate-500">
                            No fraud reports.
                        </p>
                    @endforelse
                </div>
            </section>

            @if(auth()->user()->isAdministrator())
                <section class="rounded-3xl bg-white p-7 shadow-sm">
                    <h2 class="text-2xl font-black">
                        Recent audit trail
                    </h2>

                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="p-4">Date</th>
                                    <th class="p-4">User</th>
                                    <th class="p-4">Action</th>
                                    <th class="p-4">Record</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @foreach($auditLogs as $log)
                                    <tr>
                                        <td class="p-4">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </td>

                                        <td class="p-4">
                                            {{ $log->user?->name ?? 'System' }}
                                        </td>

                                        <td class="p-4">
                                            {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                        </td>

                                        <td class="p-4">
                                            {{ class_basename($log->entity_type) }}
                                            #{{ $log->entity_id }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>