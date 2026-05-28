@extends('layouts.admin')

@section('title', '🛡️ SANZY PROTECT')

@section('content-header')
    <h1>🛡️ SANZY PROTECT <small>Panel Protection System</small></h1>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">🔒 Protection Level</h3>
            </div>
            <div class="box-body">
                <p>Current Level: <strong>Level {{ $level }}/9</strong></p>
                
                <form action="{{ route('admin.sanzy-protect.level') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="level">Set Protection Level</label>
                        <select name="level" id="level" class="form-control">
                            @for($i = 0; $i <= 9; $i++)
                                <option value="{{ $i }}" {{ $level == $i ? 'selected' : '' }}>
                                    Level {{ $i }} - 
                                    @if($i == 0) Disabled
                                    @elseif($i <= 3) Basic Protection
                                    @elseif($i <= 6) Advanced Protection
                                    @else Maximum Security
                                    @endif
                                </option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Update Level
                    </button>
                </form>
            </div>
            <div class="box-footer">
                <small class="text-muted">
                    Level 9 recommended for production. Lower levels for testing.
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">👥 Whitelist Management</h3>
            </div>
            <div class="box-body">
                <p>Admins yang bisa membuat admin baru (Level 8+)</p>
                
                @if(count($whitelist) > 0)
                    <ul class="list-group">
                        @foreach($whitelist as $adminId)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-shield"></i> Admin #{{ $adminId }}</span>
                                <form action="{{ route('admin.sanzy-protect.whitelist') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="admin_id" value="{{ $adminId }}">
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i> Remove
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="alert alert-info">No admins in whitelist yet.</div>
                @endif
                
                <form action="{{ route('admin.sanzy-protect.whitelist') }}" method="POST" class="mt-3">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="admin_id" class="form-control" placeholder="Admin ID" min="1" required>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">📋 Protection Features</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-check text-success"></i> Level 1: Anti-Kudeta & Anti-Sabotase</li>
                            <li><i class="fa fa-check text-success"></i> Level 2: Anti-User Tampering</li>
                            <li><i class="fa fa-check text-success"></i> Level 3: Anti-Server Delete</li>
                            <li><i class="fa fa-check text-success"></i> Level 4: Anti-2FA Manipulation</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-check text-success"></i> Level 5-6: Anti-File Access</li>
                            <li><i class="fa fa-check text-success"></i> Level 7: Anti-Server Snooping</li>
                            <li><i class="fa fa-check text-success"></i> Level 8: Whitelist Admin</li>
                            <li><i class="fa fa-check text-success"></i> Level 9: Auto-Filter API</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">
                            <strong>Note:</strong><br>
                            All protection levels are cumulative. 
                            Higher levels include all lower level protections.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
