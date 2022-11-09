@extends('adminlte::page')

@php
    $title = 'Admin Transactions';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            {{-- <a href="{{route('admin-transactions.create')}}" class="btn btn-secondary"><i class="fa fa-plus mr-1"></i> Add Post</a> --}}
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
                                        <th>Module Type</th>
                                        <th>Module Title</th>
                                        <th>Creator Name</th>
                                        <th>Payment Amount</th>
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
            ajax: "{{ route('admin-transactions.index') }}",
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'module_name', name: 'module_name', defaultContent: defaultText, orderable: false, searchable: false},
                {data: 'module_title', name: 'module_title', defaultContent: defaultText, orderable: false, searchable: false},
                {data: 'creator.full_name', name: 'creator_full_name', defaultContent: defaultText},
                {data: 'payout_amount', name: 'payout_amount', defaultContent: defaultText},
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
