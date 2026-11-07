<?php

namespace Tests\Feature;

use App\Models\FraudReport;
use App\Models\Property;
use App\Models\User;
use App\Models\VerificationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RentSureSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('RentSure');
    }

    public function test_landlord_can_create_property(): void
    {
        Storage::fake('public');

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'account_status' => 'active',
        ]);

        $response = $this
            ->actingAs($landlord)
            ->post(route('properties.store'), [
                'title' => 'Test Apartment',
                'type' => 'apartment',
                'neighbourhood' => 'Ntinda',
                'address' => 'Test Road',
                'monthly_rent' => 600000,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'description' => 'A test property.',
                'image_path' => UploadedFile::fake()->create('image.jpg', 500, 'image/jpeg'),
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('properties', [
            'title' => 'Test Apartment',
            'user_id' => $landlord->id,
            'verification_status' => 'pending',
        ]);
    }

    public function test_tenant_cannot_create_property(): void
    {
        $tenant = User::factory()->create([
            'role' => 'tenant',
            'account_status' => 'active',
        ]);

        $response = $this
            ->actingAs($tenant)
            ->post(route('properties.store'), [
                'title' => 'Illegal Listing',
                'type' => 'house',
                'neighbourhood' => 'Banda',
                'address' => 'Test',
                'monthly_rent' => 500000,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'description' => 'Should not be created.',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('properties', [
            'title' => 'Illegal Listing',
        ]);
    }

    public function test_administrator_can_approve_verification(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
            'account_status' => 'active',
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'account_status' => 'active',
        ]);

        $property = Property::create([
            'user_id' => $landlord->id,
            'reference' => 'NKW-TEST1',
            'title' => 'Verification Test',
            'type' => 'house',
            'neighbourhood' => 'Naguru',
            'address' => 'Test Road',
            'monthly_rent' => 1000000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'description' => 'Test',
            'availability_status' => 'available',
            'verification_status' => 'pending',
        ]);

        $document = VerificationDocument::create([
            'property_id' => $property->id,
            'user_id' => $landlord->id,
            'document_type' => 'ownership_evidence',
            'original_name' => 'evidence.pdf',
            'file_path' => 'verification-documents/test.pdf',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->patch(
                route('verification.review', $document),
                [
                    'decision' => 'approved',
                    'notes' => 'Evidence accepted.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'verification_documents',
            [
                'id' => $document->id,
                'status' => 'approved',
            ]
        );

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'verification_status' => 'verified',
        ]);

        $this->assertDatabaseHas(
            'verification_decisions',
            [
                'verification_document_id' => $document->id,
                'administrator_id' => $administrator->id,
                'decision' => 'approved',
            ]
        );
    }

    public function test_tenant_can_submit_fraud_report(): void
    {
        $tenant = User::factory()->create([
            'role' => 'tenant',
            'account_status' => 'active',
        ]);

        $response = $this
            ->actingAs($tenant)
            ->post(route('fraud-reports.store'), [
                'reason' => 'advance_payment',
                'description' =>
                    'The advertiser requested an advance payment.',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('fraud_reports', [
            'reported_by' => $tenant->id,
            'reason' => 'advance_payment',
            'status' => 'submitted',
        ]);
    }
}