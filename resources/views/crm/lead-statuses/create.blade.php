@extends('adminlte::page')

@section('title', 'Create Lead Status')

@section('content_header')
    <h1><i class="fas fa-plus"></i> Create Lead Status</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Lead Status</h3>
        </div>
        <form action="{{ route('crm.lead-statuses.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="color">Badge Color <span class="text-danger">*</span></label>
                            <select name="color" id="color" class="form-control @error('color') is-invalid @enderror" required>
                                <option value="">Select color...</option>
                                <option value="primary" {{ old('color') == 'primary' ? 'selected' : '' }}>Primary (Blue)</option>
                                <option value="secondary" {{ old('color') == 'secondary' ? 'selected' : '' }}>Secondary (Gray)</option>
                                <option value="success" {{ old('color') == 'success' ? 'selected' : '' }}>Success (Green)</option>
                                <option value="danger" {{ old('color') == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                <option value="warning" {{ old('color') == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                                <option value="info" {{ old('color') == 'info' ? 'selected' : '' }}>Info (Cyan)</option>
                                <option value="light" {{ old('color') == 'light' ? 'selected' : '' }}>Light</option>
                                <option value="dark" {{ old('color') == 'dark' ? 'selected' : '' }}>Dark</option>
                            </select>
                            @error('color')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="order">Display Order <span class="text-danger">*</span></label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}" min="0" required>
                            @error('order')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save
                </button>
                <a href="{{ route('crm.lead-statuses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    // Preview del color seleccionado
    $('#color').on('change', function() {
        const selectedColor = $(this).val();
        $(this).removeClass('bg-primary bg-secondary bg-success bg-danger bg-warning bg-info bg-light bg-dark');
        if (selectedColor) {
            $(this).addClass('bg-' + selectedColor);
        }
    });
</script>
@stop
