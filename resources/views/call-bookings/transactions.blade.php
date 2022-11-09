@extends('adminlte::page')

@php
    $title = 'Transactions for Call Booking: '.$callBooking->id;
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('call-bookings.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                                        <th>Sub Total</th>
                                        <th>Tax</th>
                                        <th>Discount Amount</th>
                                        <th>Discount Code</th>
                                        <th>Total</th>
                                        <th>Payment Type</th>
                                        <th>Affiliate User</th>
                                        <th>Payment Settled</th>
                                        <th>Payment Status</th>
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
            // ajax: "{{ route('call-bookings.index') }}",
            ajax: "{{ route('call-bookings.transactions',[$callBooking->id]) }}",
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'user.full_name', name: 'user.full_name', defaultContent: defaultText, orderable: false, searchable: false},
                {data: 'payment_gateway', name: 'payment_gateway', defaultContent: defaultText},
                {data: 'transaction_id', name: 'transaction_id', defaultContent: defaultText},
                {data: 'sub_total', name: 'sub_total', defaultContent: defaultText},
                {data: 'tax', name: 'tax', defaultContent: defaultText},
                {data: 'discount_amount', name: 'discount_amount', defaultContent: defaultText},
                {data: 'discount_code', name: 'discount_code', defaultContent: defaultText},
                {data: 'total', name: 'total', defaultContent: defaultText},
                {data: 'payment_type', name: 'payment_type', defaultContent: defaultText},
                {data: 'affiliate_user.full_name', name: 'affiliate_user.full_name', defaultContent: defaultText, orderable: false, searchable: false},
                {data: 'payment_settled', name: 'payment_settled', defaultContent: defaultText},
                {data: 'status', name: 'status', defaultContent: defaultText},
                
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
