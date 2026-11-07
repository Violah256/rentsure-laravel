<?php

namespace Database\Seeders;

use App\Models\FraudReport;
use App\Models\PaymentReceipt;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RentSureDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = User::updateOrCreate(
            ['email' => 'tenant@rentsure.test'],
            [
                'name' => 'Amina Nakato',
                'role' => 'tenant',
                'phone' => '0701001001',
                'neighbourhood' => 'Ntinda',
                'account_status' => 'active',
                'password' => Hash::make('RentSure2025!'),
            ]
        );

        $landlord = User::updateOrCreate(
            ['email' => 'landlord@rentsure.test'],
            [
                'name' => 'Sarah Nambasa',
                'role' => 'landlord',
                'phone' => '0702002002',
                'neighbourhood' => 'Naguru',
                'account_status' => 'active',
                'password' => Hash::make('RentSure2025!'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'agent@rentsure.test'],
            [
                'name' => 'Apex Property Agents',
                'role' => 'agent',
                'phone' => '0703003003',
                'neighbourhood' => 'Bugolobi',
                'account_status' => 'active',
                'password' => Hash::make('RentSure2025!'),
            ]
        );

        $property = Property::updateOrCreate(
            ['reference' => 'NKW-1042'],
            [
                'user_id' => $landlord->id,
                'title' => 'Sunlit two-bedroom apartment',
                'type' => 'apartment',
                'neighbourhood' => 'Ntinda',
                'address' => 'Kira Road',
                'monthly_rent' => 850000,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'description' =>
                    'A bright and secure apartment close to shops and public transport.',
                'availability_status' => 'unavailable',
                'verification_status' => 'verified',
                'verification_notes' =>
                    'Identity and ownership evidence reviewed.',
            ]
        );

        $tenancy = Tenancy::updateOrCreate(
            ['reference' => 'TEN-2025-1042'],
            [
                'property_id' => $property->id,
                'tenant_id' => $tenant->id,
                'landlord_id' => $landlord->id,
                'start_date' => '2025-03-01',
                'end_date' => '2026-02-28',
                'monthly_rent' => 850000,
                'status' => 'active',
                'notes' => 'Twelve-month tenancy.',
            ]
        );

        PaymentReceipt::updateOrCreate(
            ['receipt_number' => 'RCT-JUN-1042'],
            [
                'tenancy_id' => $tenancy->id,
                'recorded_by' => $tenant->id,
                'amount' => 850000,
                'payment_date' => '2025-06-03',
                'payment_type' => 'rent',
                'payment_method' => 'Mobile money',
                'notes' => 'June rent',
            ]
        );

        FraudReport::updateOrCreate(
            ['reference' => 'FR-2407'],
            [
                'reported_by' => $tenant->id,
                'property_id' => $property->id,
                'advertiser_name' =>
                    'Unverified social-media account',
                'reason' => 'advance_payment',
                'description' =>
                    'The advertiser requested payment before allowing a property viewing.',
                'status' => 'submitted',
            ]
        );
    }
}