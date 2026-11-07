<?php

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $category = ServiceCategory::first();
        $categoryId = $category?->id;

        $services = [
            [
                'tenant_id' => 1,
                'branch_id' => null,
                'category_id' => $categoryId,
                'service_code' => 'ADH-MOB-01',
                'main_service_name' => 'AADHAAR CARD',
                'sub_service_name' => 'Aadhaar Mobile Link / Biometric Update',
                'service_variant' => 'UIDAI myAadhaar Portal',
                'description' => 'Linking mobile number, email, or updating biometric/photo in existing Aadhaar card',
                'price' => 100.00,
                'govt_fee' => 50.00,
                'service_charge' => 50.00,
                'expected_processing_days' => 3,
                'required_documents_json' => [
                    'Aadhaar Card Copy',
                    'Mobile Number with Active SIM for OTP',
                    'Biometric Fingerprint / Iris Verification'
                ],
                'service_portal_link' => 'https://myaadhaar.uidai.gov.in/',
                'work_portal_link' => 'https://myaadhaar.uidai.gov.in/',
                'video_instruction_link' => 'https://www.youtube.com/results?search_query=aadhaar+card+mobile+update+online',
                'is_active' => true,
            ],
            [
                'tenant_id' => 1,
                'branch_id' => null,
                'category_id' => $categoryId,
                'service_code' => 'ADH-CHILD-02',
                'main_service_name' => 'AADHAAR CARD',
                'sub_service_name' => 'New Child Aadhaar Enrollment (Baal Aadhaar)',
                'service_variant' => 'UIDAI Baal Aadhaar 0-5 Years',
                'description' => 'Fresh Baal Aadhaar blue card registration for children aged below 5 years',
                'price' => 150.00,
                'govt_fee' => 0.00,
                'service_charge' => 150.00,
                'expected_processing_days' => 7,
                'required_documents_json' => [
                    'Child Birth Certificate (Official Municipal / Gram Panchayat)',
                    'Parent Aadhaar Card (Father or Mother)',
                    'Parent Active Mobile Number'
                ],
                'service_portal_link' => 'https://appointments.uidai.gov.in/',
                'work_portal_link' => 'https://appointments.uidai.gov.in/',
                'video_instruction_link' => 'https://www.youtube.com/results?search_query=child+baal+aadhaar+card+apply',
                'is_active' => true,
            ]
        ];

        foreach ($services as $s) {
            $existing = Service::where('main_service_name', $s['main_service_name'])
                ->where('sub_service_name', $s['sub_service_name'])
                ->first();

            if (!$existing) {
                Service::create($s);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Service::where('main_service_name', 'AADHAAR CARD')->forceDelete();
    }
};
