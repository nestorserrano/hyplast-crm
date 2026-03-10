@extends('adminlte::page')

@section('title', __('clients.title'))

@section('template_linked_css')
    <style type="text/css" media="screen">
        .clients-table {
          border: 0;
        }
        .clients-table tr td:first-child {
            padding-left: 15px;
        }
        .clients-table tr td:last-child {
            padding-right: 15px;
        }
        .clients-table.table-responsive,
        .clients-table.table-responsive table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content_header')
    <h1><i class="fas fa-users"></i> {{ __('clients.management') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h3 class="card-title mb-0">{{ __('clients.softland_clients') }}</h3>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="d-inline-flex align-items-center">
                                    <label class="mb-0 mr-2 font-weight-bold" for="tipo_moneda" style="white-space:nowrap;">Moneda:</label>
                                    <select class="form-control form-control-sm d-inline-block" id="tipo_moneda" style="width: 170px;">
                                        <option value="local">Moneda Local (RD$)</option>
                                        <option value="dolar">Dólares (US$)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive clients-table">
                            <table id="clients-table" class="table table-bordered table-striped table-hover table-sm" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('clients.code') }}</th>
                                        <th>{{ __('clients.name') }}</th>
                                        <th>{{ __('clients.contact') }}</th>
                                        <th>{{ __('clients.phone') }}</th>
                                        <th>{{ __('clients.email') }}</th>
                                        <th>Saldo</th>
                                        <th>{{ __('clients.status') }}</th>
                                        <th class="text-center">{{ __('clients.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    @include('scripts.datatables.datatables-clients')
    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
@endsection
