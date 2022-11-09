@extends('adminlte::page')

@php
    $title = 'Payment Transactions';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            {{-- <a href="{{route('payment-transactions.create')}}" class="btn btn-secondary"><i class="fa fa-plus mr-1"></i> Add Post</a> --}}
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header">
                            @if(Session::has('message'))
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
                            @endif
                        </div>
                        <div class="box-body">
                            <table id="index_table" class="table table-bordered table-striped table-responsive">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Id</th>
                                        <th>User</th>
                                        <th>Payment Gateway</th>
                                        <th>Transaction Id</th>
                                        <th>Module Name</th>
                                        <th>Total</th>
                                        <th>Payment Type</th>
                                        <th>Affiliate User</th>
                                        <th>Payment Settled</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.dataTables_wrapper -->
    </div>
    <!-- /.card-body -->
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        var defaultText = '-';
        $('#index_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('payment-transactions.index') }}",
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'user.full_name', name: 'user_full_name', defaultContent: defaultText, orderable: false},
                {data: 'payment_gateway', name: 'payment_gateway', orderable: false, searchable: false},
                {data: 'transaction_id', name: 'transaction_id', defaultContent: defaultText},
                {data: 'module_name', name: 'module_name', orderable: false},
                {data: 'total', name: 'total', 
                    render: function (data, type, row) {
                        return data ? "{{config('constant.rupee_symbol')}}"+data : null;
                    },
                    orderable: false, searchable: false
                },
                {data: 'payment_type', name: 'payment_type', orderable: false, searchable: false},
                {data: 'affiliate_user.full_name', name: 'affiliate_user_full_name', defaultContent: defaultText, orderable: false},
                {data: 'payment_settled', name: 'payment_settled', orderable: false, searchable: false},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            columnDefs: [
                {
                    'targets': 0,
                    'checkboxes': {
                        'selectRow': true
                    }
                }
            ],
            responsive: true,
            select: {
                'style': 'multi'
            },
            order: [[1, 'asc']],
        });
    });

</script>
@stop
