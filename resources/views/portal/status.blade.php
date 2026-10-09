@extends('layouts.portal')

@section('title', 'Status: ' . $application->application_number)

@section('content')
<div style="max-width: 800px; margin: 20px auto;">
    <!-- Top Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: #ea580c; text-transform: uppercase;">Application Status Report</span>
            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; font-family: var(--font-mono);">{{ $application->application_number }}</h1>
        </div>
        <div>
            <a href="{{ route('portal.track') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Track Another Application
            </a>
        </div>
    </div>

    <!-- Live Status Banner -->
    <div class="erp-card" style="border-left: 5px solid #ea580c; margin-bottom: 24px;">
        <div class="erp-card-body" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Current Processing Status</span>
                    @php
                        $stBadge = match($application->work_status) {
                            'APPROVED', 'READY' => 'badge-ready',
                            'DELIVERED', 'CLOSED' => 'badge-delivered',
                            'DOCUMENT_PENDING' => 'badge-pending',
                            default => 'badge-process'
                        };
                    @endphp
                    <div style="margin-top: 4px;">
                        <span class="badge {{ $stBadge }}" style="font-size: 15px; padding: 6px 16px;">{{ $application->work_status }}</span>
                    </div>
                </div>

                <div style="text-align: right;">
                    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Estimated Completion</span>
                    <div style="font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 4px;">
                        {{ $application->expected_completion_date ? $application->expected_completion_date->format('d M Y') : 'Within 7 Business Days' }}
                    </div>
                </div>
            </div>

            @if($application->pending_remarks)
                <div style="margin-top: 16px; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 12px 16px; font-size: 13.5px; color: #92400e;">
                    <strong><i class="fa-solid fa-circle-info"></i> Notice from Service Center:</strong>
                    <p style="margin-top: 4px;">{{ $application->pending_remarks }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Details Grid -->
    <div class="form-grid-2">
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-file-lines"></i> Service Information</h3>
            </div>
            <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong>Applicant Name:</strong> {{ $application->customer->name }}</div>
                <div><strong>Service Category:</strong> {{ $application->service->category->name }}</div>
                <div><strong>Service Requested:</strong> {{ $application->service->main_service_name }} ({{ $application->service->sub_service_name }})</div>
                <div><strong>Registration Date:</strong> {{ $application->application_date->format('d M Y') }}</div>
                @if($application->external_acknowledgement_no)
                    <div><strong>Govt Ack / Application ID:</strong> <span style="font-family: var(--font-mono); font-weight: 700;">{{ $application->external_acknowledgement_no }}</span></div>
                @endif
                <div><strong>Delivery Status:</strong> <span class="badge badge-process">{{ $application->delivery_status }}</span></div>
            </div>
        </div>

        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Payment & Balance Summary</h3>
            </div>
            <div class="erp-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong>Total Service Fee:</strong> ₹{{ number_format($application->total_amount, 2) }}</div>
                <div><strong>Amount Paid:</strong> <strong style="color: #166534;">₹{{ number_format($application->received_amount, 2) }}</strong></div>
                <div><strong>Remaining Balance:</strong> <strong style="color: {{ $application->remaining_amount > 0 ? '#dc2626' : '#166534' }};">₹{{ number_format($application->remaining_amount, 2) }}</strong></div>
                <div style="margin-top: 8px;">
                    <strong>Payment Status:</strong> 
                    <span class="badge {{ $application->payment_status === 'PAID' ? 'badge-paid' : 'badge-pending' }}">{{ $application->payment_status }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Status Checklist -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-paperclip"></i> Documents Verification Checklist</h3>
        </div>
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Document Required</th>
                        <th>Submission Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($application->service->required_documents_json))
                        @foreach($application->service->required_documents_json as $docName)
                            @php
                                $matchedDoc = $application->documents->firstWhere('document_type_name', $docName);
                            @endphp
                            <tr>
                                <td><strong>{{ $docName }}</strong></td>
                                <td>
                                    @if($matchedDoc && $matchedDoc->status === 'VERIFIED')
                                        <span class="badge badge-paid"><i class="fa-solid fa-check"></i> Verified</span>
                                    @elseif($matchedDoc && $matchedDoc->status === 'RECEIVED')
                                        <span class="badge badge-process"><i class="fa-solid fa-clock"></i> Received - Under Scrutiny</span>
                                    @elseif($matchedDoc && $matchedDoc->status === 'REJECTED')
                                        <span class="badge badge-pending"><i class="fa-solid fa-xmark"></i> Rejected / Re-upload Needed</span>
                                    @else
                                        <span class="badge badge-pending"><i class="fa-solid fa-triangle-exclamation"></i> Document Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 16px; color: var(--text-muted);">No specific document attachments required.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Citizen Progress Timeline -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-timeline"></i> Processing Milestone Timeline</h3>
        </div>
        <div class="erp-card-body">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($application->statusHistories as $hist)
                    <div style="display: flex; gap: 14px; border-left: 2px solid #10b981; padding-left: 16px; position: relative;">
                        <div style="position: absolute; left: -7px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                        <div>
                            <div style="font-weight: 700; font-size: 14px;">{{ $hist->new_status }}</div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $hist->created_at->format('d M Y, h:i A') }}</div>
                            @if($hist->remarks)
                                <div style="font-size: 13px; color: #334155; margin-top: 4px;">{{ $hist->remarks }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
