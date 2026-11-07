<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\AuditLog;
use App\Models\FraudReport;
use App\Models\PaymentReceipt;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use App\Models\VerificationDecision;
use App\Models\VerificationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RentSureController extends Controller
{
    use AuthorizesRequests;
    public function home(Request $request): View
    {
        $properties = Property::query()
            ->with('advertiser')
            ->where('availability_status', 'available')
            ->where('verification_status', 'verified')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search');

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere(
                                'address',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'neighbourhood',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'type',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                $request->filled('neighbourhood'),
                fn ($query) => $query->where(
                    'neighbourhood',
                    $request->string('neighbourhood')
                )
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('welcome', compact('properties'));
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdministrator()) {
            $properties = Property::with('advertiser')
                ->latest()
                ->get();

            $documents = VerificationDocument::with([
                'property',
                'submitter',
                'decisions.administrator',
            ])
                ->latest()
                ->get();

            $tenancies = Tenancy::with([
                'property',
                'tenant',
                'landlord',
                'paymentReceipts',
            ])
                ->latest()
                ->get();

            $reports = FraudReport::with([
                'property',
                'reporter',
                'handler',
            ])
                ->latest()
                ->get();

            $auditLogs = AuditLog::with('user')
                ->latest()
                ->take(20)
                ->get();
        } elseif ($user->canAdvertiseProperties()) {
            $properties = Property::where(
                'user_id',
                $user->id
            )
                ->latest()
                ->get();

            $documents = VerificationDocument::with([
                'property',
                'decisions.administrator',
            ])
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $tenancies = Tenancy::with([
                'property',
                'tenant',
                'paymentReceipts',
            ])
                ->where('landlord_id', $user->id)
                ->latest()
                ->get();

            $reports = FraudReport::with('property')
                ->where('reported_by', $user->id)
                ->latest()
                ->get();

            $auditLogs = collect();
        } else {
            $properties = Property::where(
                'verification_status',
                'verified'
            )
                ->where(
                    'availability_status',
                    'available'
                )
                ->latest()
                ->get();

            $documents = collect();

            $tenancies = Tenancy::with([
                'property',
                'landlord',
                'paymentReceipts',
            ])
                ->where('tenant_id', $user->id)
                ->latest()
                ->get();

            $reports = FraudReport::with('property')
                ->where('reported_by', $user->id)
                ->latest()
                ->get();

            $auditLogs = collect();
        }

        $tenants = User::where('role', 'tenant')
            ->where('account_status', 'active')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact(
            'properties',
            'documents',
            'tenancies',
            'reports',
            'tenants',
            'auditLogs'
        ));
    }

    public function storeProperty(
        Request $request
    ): RedirectResponse {
        $this->authorize('create', Property::class);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                'in:apartment,house,studio,single_room',
            ],
            'neighbourhood' => [
                'required',
                'in:Banda,Bugolobi,Kyanja,Kyambogo,Naguru,Ntinda',
            ],
            'address' => [
                'required',
                'string',
                'max:255',
            ],
            'monthly_rent' => [
                'required',
                'numeric',
                'min:0',
            ],
            'bedrooms' => [
                'required',
                'integer',
                'min:0',
                'max:50',
            ],
            'bathrooms' => [
                'required',
                'integer',
                'min:0',
                'max:50',
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request
                ->file('image')
                ->store('properties', 'public');
        }

        $validated['user_id'] = $request->user()->id;
        $validated['reference'] = $this->reference(
            'NKW'
        );
        $validated['verification_status'] = 'pending';
        $validated['availability_status'] = 'available';

        $property = Property::create($validated);

        $this->audit(
            $request,
            'property_created',
            $property,
            null,
            $property->toArray()
        );

        return back()->with(
            'success',
            'Property created and submitted for verification.'
        );
    }

    public function toggleProperty(
        Request $request,
        Property $property
    ): RedirectResponse {
        $this->authorize('update', $property);

        $oldValues = $property->toArray();

        $property->update([
            'availability_status' =>
                $property->availability_status === 'available'
                    ? 'unavailable'
                    : 'available',
        ]);

        $this->audit(
            $request,
            'property_availability_changed',
            $property,
            $oldValues,
            $property->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Property availability updated.'
        );
    }

    public function submitVerification(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'property_id' => [
                'required',
                'exists:properties,id',
            ],
            'document_type' => [
                'required',
                'in:national_id,ownership_evidence,authority_letter,owner_consent,other',
            ],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $property = Property::findOrFail(
            $validated['property_id']
        );

        $this->authorize('update', $property);

        $path = $request
            ->file('document')
            ->store('verification-documents', 'local');

        $document = VerificationDocument::create([
            'property_id' => $property->id,
            'user_id' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'original_name' => $request
                ->file('document')
                ->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);

        $property->update([
            'verification_status' => 'pending',
        ]);

        $this->audit(
            $request,
            'verification_document_submitted',
            $document,
            null,
            [
                'property_id' => $property->id,
                'document_type' =>
                    $document->document_type,
            ]
        );

        return back()->with(
            'success',
            'Verification evidence uploaded securely.'
        );
    }

    public function downloadVerification(
        Request $request,
        VerificationDocument $document
    ) {
        $allowed = $request->user()->isAdministrator()
            || $document->user_id === $request->user()->id;

        abort_unless($allowed, 403);

        abort_unless(
            Storage::disk('local')
                ->exists($document->file_path),
            404
        );

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }

    public function reviewVerification(
        Request $request,
        VerificationDocument $document
    ): RedirectResponse {
        abort_unless(
            $request->user()->isAdministrator(),
            403
        );

        $validated = $request->validate([
            'decision' => [
                'required',
                'in:approved,rejected,clarification_required',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $document,
            $validated
        ) {
            $oldValues = $document->toArray();

            VerificationDecision::create([
                'verification_document_id' =>
                    $document->id,
                'administrator_id' =>
                    $request->user()->id,
                'decision' => $validated['decision'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $documentStatus = match (
                $validated['decision']
            ) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                default => 'pending',
            };

            $document->update([
                'status' => $documentStatus,
            ]);

            $propertyStatus = match (
                $validated['decision']
            ) {
                'approved' => 'verified',
                'rejected' => 'rejected',
                default => 'pending',
            };

            $document->property->update([
                'verification_status' => $propertyStatus,
                'verification_notes' =>
                    $validated['notes'] ?? null,
            ]);

            $this->audit(
                $request,
                'verification_reviewed',
                $document,
                $oldValues,
                [
                    'document_status' => $documentStatus,
                    'property_status' => $propertyStatus,
                    'decision' => $validated['decision'],
                    'notes' => $validated['notes'] ?? null,
                ]
            );
        });

        return back()->with(
            'success',
            'Verification decision recorded successfully.'
        );
    }

    public function storeTenancy(
        Request $request
    ): RedirectResponse {
        abort_unless(
            $request->user()->canAdvertiseProperties(),
            403
        );

        $validated = $request->validate([
            'property_id' => [
                'required',
                'exists:properties,id',
            ],
            'tenant_id' => [
                'required',
                'exists:users,id',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'monthly_rent' => [
                'required',
                'numeric',
                'min:0',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $property = Property::findOrFail(
            $validated['property_id']
        );

        $this->authorize('update', $property);

        $tenant = User::whereKey(
            $validated['tenant_id']
        )
            ->where('role', 'tenant')
            ->where('account_status', 'active')
            ->firstOrFail();

        $tenancy = DB::transaction(function () use (
            $request,
            $validated,
            $property,
            $tenant
        ) {
            $tenancy = Tenancy::create([
                'reference' => $this->reference('TEN'),
                'property_id' => $property->id,
                'tenant_id' => $tenant->id,
                'landlord_id' => $property->user_id,
                'start_date' => $validated['start_date'],
                'end_date' =>
                    $validated['end_date'] ?? null,
                'monthly_rent' =>
                    $validated['monthly_rent'],
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            $property->update([
                'availability_status' => 'unavailable',
            ]);

            $this->audit(
                $request,
                'tenancy_created',
                $tenancy,
                null,
                $tenancy->toArray()
            );

            return $tenancy;
        });

        return back()->with(
            'success',
            "Tenancy {$tenancy->reference} created."
        );
    }

    public function storePayment(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'tenancy_id' => [
                'required',
                'exists:tenancies,id',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'payment_date' => [
                'required',
                'date',
            ],
            'payment_type' => [
                'required',
                'in:rent,deposit,viewing_fee,other',
            ],
            'payment_method' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $tenancy = Tenancy::findOrFail(
            $validated['tenancy_id']
        );

        $allowed = $request->user()->isAdministrator()
            || $tenancy->tenant_id === $request->user()->id
            || $tenancy->landlord_id === $request->user()->id;

        abort_unless($allowed, 403);

        $receipt = PaymentReceipt::create([
            'tenancy_id' => $tenancy->id,
            'recorded_by' => $request->user()->id,
            'receipt_number' => $this->reference('RCT'),
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_type' => $validated['payment_type'],
            'payment_method' =>
                $validated['payment_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->audit(
            $request,
            'payment_receipt_created',
            $receipt,
            null,
            $receipt->toArray()
        );

        return back()->with(
            'success',
            "Receipt {$receipt->receipt_number} saved."
        );
    }

    public function submitFraudReport(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'property_id' => [
                'nullable',
                'exists:properties,id',
            ],
            'advertiser_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reason' => [
                'required',
                'in:advance_payment,copied_photos,impersonation,nonexistent_property,multiple_renting,other',
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
            ],
            'evidence' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        if ($request->hasFile('evidence')) {
            $validated['evidence_path'] = $request
                ->file('evidence')
                ->store('fraud-evidence', 'local');
        }

        $report = FraudReport::create([
            'reference' => $this->reference('FR'),
            'reported_by' => $request->user()->id,
            'property_id' =>
                $validated['property_id'] ?? null,
            'advertiser_name' =>
                $validated['advertiser_name'] ?? null,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'evidence_path' =>
                $validated['evidence_path'] ?? null,
            'status' => 'submitted',
        ]);

        $this->audit(
            $request,
            'fraud_report_submitted',
            $report,
            null,
            [
                'reference' => $report->reference,
                'reason' => $report->reason,
            ]
        );

        return back()->with(
            'success',
            "Fraud report {$report->reference} submitted."
        );
    }

    public function reviewFraudReport(
        Request $request,
        FraudReport $report
    ): RedirectResponse {
        abort_unless(
            $request->user()->isAdministrator(),
            403
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'in:under_review,resolved,dismissed',
            ],
            'administrator_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $oldValues = $report->toArray();

        $report->update([
            'status' => $validated['status'],
            'administrator_notes' =>
                $validated['administrator_notes'] ?? null,
            'handled_by' => $request->user()->id,
            'resolved_at' => in_array(
                $validated['status'],
                ['resolved', 'dismissed'],
                true
            ) ? now() : null,
        ]);

        $this->audit(
            $request,
            'fraud_report_reviewed',
            $report,
            $oldValues,
            $report->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Fraud report updated.'
        );
    }

    private function reference(string $prefix): string
    {
        do {
            $reference = $prefix.'-'.strtoupper(
                Str::random(8)
            );
        } while (
            Property::where('reference', $reference)->exists()
            || Tenancy::where('reference', $reference)->exists()
            || PaymentReceipt::where(
                'receipt_number',
                $reference
            )->exists()
            || FraudReport::where(
                'reference',
                $reference
            )->exists()
        );

        return $reference;
    }

    private function audit(
        Request $request,
        string $action,
        object $entity,
        ?array $oldValues,
        ?array $newValues
    ): void {
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'entity_type' => $entity::class,
            'entity_id' => (string) $entity->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
