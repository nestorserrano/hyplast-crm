@extends('adminlte::page')

@section('title', 'Nuevo Mensaje')

@section('content_header')
    <h1>
        <i class="fas fa-paper-plane"></i> Nuevo Mensaje
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Selecciona un usuario</h3>
                </div>
                <form action="{{ route('messages.create') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="user_id">Usuario:</label>
                            <select name="user_id" id="user_id" class="form-control select2" required>
                                <option value="">Selecciona un usuario...</option>
                                @foreach($users as $user)
                                    @php
                                        $displayName = trim($user->name);
                                        if (empty($displayName)) {
                                            $displayName = trim($user->first_name . ' ' . $user->last_name);
                                        }
                                        if (empty($displayName)) {
                                            $displayName = 'Usuario #' . $user->id;
                                        }
                                    @endphp
                                    <option value="{{ $user->id }}">
                                        {{ $displayName }}
                                        @if($user->email)
                                            ({{ $user->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Iniciar Chat
                        </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Buscar usuario...'
    });
});
</script>
@stop
