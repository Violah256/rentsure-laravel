<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference')->unique();
            $table->string('title');

            $table->enum('type', [
                'apartment',
                'house',
                'studio',
                'single_room',
            ]);

            $table->enum('neighbourhood', [
                'Banda',
                'Bugolobi',
                'Kyanja',
                'Kyambogo',
                'Naguru',
                'Ntinda',
            ]);

            $table->string('address');
            $table->decimal('monthly_rent', 12, 2);
            $table->unsignedInteger('bedrooms')->default(1);
            $table->unsignedInteger('bathrooms')->default(1);
            $table->text('description');
            $table->string('image_path')->nullable();

            $table->enum('availability_status', [
                'available',
                'unavailable',
            ])->default('available');

            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->text('verification_notes')->nullable();
            $table->timestamps();

            $table->index([
                'neighbourhood',
                'availability_status',
                'verification_status'], 'properties_status_idx'
            );
        });

        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('document_type', [
                'national_id',
                'ownership_evidence',
                'authority_letter',
                'owner_consent',
                'other',
            ]);

            $table->string('original_name');
            $table->string('file_path');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->timestamps();
        });

        Schema::create('verification_decisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('verification_document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('administrator_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('decision', [
                'approved',
                'rejected',
                'clarification_required',
            ]);

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('property_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('tenant_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('landlord_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rent', 12, 2);

            $table->enum('status', [
                'active',
                'ended',
                'cancelled',
            ])->default('active');

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenancy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('recorded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');

            $table->enum('payment_type', [
                'rent',
                'deposit',
                'viewing_fee',
                'other',
            ])->default('rent');

            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('fraud_reports', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('reported_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('property_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('advertiser_name')->nullable();

            $table->enum('reason', [
                'advance_payment',
                'copied_photos',
                'impersonation',
                'nonexistent_property',
                'multiple_renting',
                'other',
            ]);

            $table->text('description');
            $table->string('evidence_path')->nullable();

            $table->enum('status', [
                'submitted',
                'under_review',
                'resolved',
                'dismissed',
            ])->default('submitted');

            $table->text('administrator_notes')->nullable();

            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action');
            $table->string('entity_type');
            $table->string('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index([
                'entity_type',
                'entity_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('fraud_reports');
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('tenancies');
        Schema::dropIfExists('verification_decisions');
        Schema::dropIfExists('verification_documents');
        Schema::dropIfExists('properties');
    }
};