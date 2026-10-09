@extends('layouts.app')

@section('title', 'Import Customers CSV')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Bulk Customer CSV Ingestion</h1>
        <p class="page-subtitle">Batch import citizen records with duplicate detection and error validation</p>
    </div>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Directory
    </a>
</div>

@if(session('import_result'))
    @php $res = session('import_result'); @endphp
    <div class="erp-card" style="border-left: 4px solid #10b981; background-color: #f0fdf4;">
        <div class="erp-card-body">
            <h3 style="font-size: 16px; font-weight: 700; color: #166534; margin-bottom: 8px;">
                <i class="fa-solid fa-circle-check"></i> Ingestion Batch Summary
            </h3>
            <div style="font-size: 13.5px; color: #14532d;">
                Successfully Imported: <strong>{{ $res['imported'] }}</strong> | Skipped / Existing: <strong>{{ $res['skipped'] }}</strong>
            </div>
            @if(!empty($res['errors']))
                <div style="margin-top: 12px; background: #fff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; max-height: 150px; overflow-y: auto; font-size: 12px; color: #991b1b;">
                    <strong>Skipped Rows Details:</strong>
                    <ul style="margin-left: 18px; margin-top: 4px;">
                        @foreach($res['errors'] as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="form-grid-2">
    <!-- Upload Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-cloud-arrow-up"></i> Upload CSV File</h3>
        </div>
        <div class="erp-card-body">
            <form action="{{ route('import_export.import.customers.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label required">Target Branch Assignment</label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Select CSV File (.csv)</label>
                    <input type="file" name="file" class="form-control" required accept=".csv,text/csv">
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fa-solid fa-upload"></i> Process & Ingest Customers
                </button>
            </form>
        </div>
    </div>

    <!-- Sample Format Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa-solid fa-file-lines"></i> Expected CSV Format</h3>
        </div>
        <div class="erp-card-body" style="font-size: 13px; line-height: 1.6;">
            <p>Your CSV file should have headers matching the following columns:</p>
            <div style="background: #0f172a; color: #38bdf8; font-family: var(--font-mono); padding: 12px; border-radius: 6px; font-size: 12px; overflow-x: auto; margin: 12px 0;">
                name,mobile,gender,email,address,city,pincode<br>
                Suresh Gaikwad,9823456789,MALE,suresh@example.com,Kothrud,Pune,411038<br>
                Sunita Kadam,9823456788,FEMALE,,FC Road,Pune,411005
            </div>
            <ul style="margin-left: 20px; color: var(--text-muted);">
                <li><strong>name</strong> and <strong>mobile</strong> are mandatory.</li>
                <li>Duplicates based on <strong>mobile</strong> number within your tenant will be automatically detected and safely skipped.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
