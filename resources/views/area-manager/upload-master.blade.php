@extends('layouts.app')

@section('title', 'Upload Data Master')

@push('styles')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link href="{{ asset('css/upload-data.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="dashboard-content">

    {{-- NAVBAR --}}
    @include('components.navbar')
    
 
    <div class="upload-container">

        <div class="upload-card">
            <h2>Upload Data Master</h2>
            <p class="subtitle">Upload data produk, store, atau karyawan</p>

            <form action="{{ route('area-manager.import-master') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- JENIS DATA --}}
                <div class="input-group" id="fileUploadGroup">
                    <label class="label-file">Jenis Data</label>
                    <select name="type" id="typeSelect" required>
                        <option value="">-- Pilih Data --</option>
                        <option value="product">Master Product</option>
                        <option value="store">Master Store</option>
                        <option value="employee">Master Employee</option>
                        <option value="target">Target Bulanan</option>
                    </select>
                </div>

                <div id="uploadSection">
                <div class="format-info" id="formatInfo" style="margin-top:10px; font-size:14px; color:#666;">
                    Format Excel: -
                </div>

                {{-- FILE --}}
                <div class="input-group">
                    <label class="label-file">Upload File</label>
                    <input type="file" name="file" required>
                </div>

                {{-- BUTTON --}}
                <div class="action-group">
                    <button type="submit" class="btn btn-primary">
                        ↑ Upload Data Master
                    </button>
                </div>
                </div>
            </form>

{{-- FORM TARGET BULANAN --}}
<div id="targetForm" style="display:none; margin-top:30px;">

    <h3>Input Target Bulanan</h3>

    <form action="{{ route('area-manager.store-target') }}" method="POST">
        @csrf

        {{-- PILIH BULAN --}}
        <div class="input-group">
            <label>Bulan</label>
            <input type="month" name="month" required>
        </div>

        {{-- PILIH STORE --}}
        <div class="input-group">
            <label>Pilih Store</label>
            <select name="site_code" required>
                <option value="">-- Pilih Store --</option>
                @foreach($stores as $store)
                    <option value="{{ $store->code }}">
                        {{ $store->name}} ({{ $store->code }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TARGET SALES (AMAN, ADA FORMAT) --}}
            <div class="input-group">
            <label>Target Sales</label>
            <input type="number" 
                name="target_sales" 
                step="0.01" 
                min="0" 
                required 
                placeholder="Contoh: 150000000 atau 150000000.5"
                value="{{ isset($target) ? rtrim(rtrim(number_format($target->target_sales, 2, '.', ''), '0'), '.') : '' }}">
        </div>

        <div class="action-group">
            <button type="submit" class="btn btn-primary">
                ↑ Upload Data Master
            </button>
        </div>
        
    </form>
    </form>
</div>
            {{-- SUCCESS --}}
            @if(session('success'))
                <div class="success-section" style="display:block;">
                    <div class="success-title">✅ Berhasil</div>
                    <div class="success-details">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- ERROR --}}
            @if(session('error'))
                <div class="success-section" style="display:block; background:#fee2e2;">
                    <div class="success-title">❌ Error</div>
                    <div class="success-details">
                        {{ session('error') }}
                    </div>
                </div>
            @endif
            
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                
                    const typeSelect = document.getElementById('typeSelect');
                    const formatInfo = document.getElementById('formatInfo');
                    const targetForm = document.getElementById('targetForm');
                    const uploadSection = document.getElementById('uploadSection');
                
                    function updateUI() {
                        const type = typeSelect.value;
                        let text = '';
                
                        if (type === 'product') {
                            text = 'Format Excel: article_code | name | size | type | series | brand';
                            uploadSection.style.display = 'block';
                            targetForm.style.display = 'none';
                        } 
                        else if (type === 'store') {
                            text = 'Format Excel: code | name | city | is_active';
                            uploadSection.style.display = 'block';
                            targetForm.style.display = 'none';
                        } 
                        else if (type === 'employee') {
                            text = 'Format Excel: nik | name | store_code | position | is_active';
                            uploadSection.style.display = 'block';
                            targetForm.style.display = 'none';
                        } 
                        else if (type === 'target') {
                            text = 'Input target bulanan per store';
                            uploadSection.style.display = 'none'; // 🔥 HILANGKAN UPLOAD
                            targetForm.style.display = 'block';   // 🔥 TAMPILKAN TARGET
                        } 
                        else {
                            text = 'Format Excel: -';
                            uploadSection.style.display = 'none';
                            targetForm.style.display = 'none';
                        }
                
                        formatInfo.innerHTML = text;
                    }
                
                    typeSelect.addEventListener('change', updateUI);
                    updateUI();
                });
                </script>

        </div>

    </div>
</div>
@endsection