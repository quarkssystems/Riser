@extends('adminlte::page')

@php
    $title = 'States';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('states.create')}}" class="btn btn-secondary"><i class="fa fa-plus mr-1"></i> Add State</a>
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
                                        <th>State Name</th>
                                        <th>Country Name</th>
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
            ajax: "{{ route('states.index') }}",
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name', defaultContent: defaultText},
                {data: 'country_id', name: 'country_id', defaultContent: defaultText},
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

        $(document).on('click', '.deleteBtn', function(e) {
            Swal.fire({
                title: 'Are you sure want to delete?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = $(this).attr('data-id');
                    $.ajax({
                        url: "{{ url('admin/states') }}/"+id,
                        data:{
                            'id': id,
                            '_token': '{{ csrf_token() }}',
                        },
                        method: 'DELETE',
                        cache: false,
                        success: function(html){
                            Swal.fire(
                                'Deleted!',
                                'Record has been deleted.',
                                'success'
                            );

                            var oTable = $('#index_table').dataTable();
                            oTable.fnDraw(false);
                        }
                    });
                }
            });
        });

        $(document).on('click', '.statusBtn', function(e) {
            Swal.fire({
                title: 'Are you sure want to change status?',
                // text: "All related items will be affected!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = $(this).attr('data-id');
                    var status = $(this).attr('data-status');
                    $.ajax({
                        url: "{{ url('admin/states/change-status') }}"+'/'+id,
                        data:{
                            'status': status,
                            '_token': '{{ csrf_token() }}',
                        },
                        method: 'POST',
                        cache: false,
                        success: function(html){
                            Swal.fire(
                                'Changed!',
                                'Status has been changed.',
                                'success'
                            );

                            var oTable = $('#index_table').dataTable();
                            oTable.fnDraw(false);
                        }
                    });
                }
            });
        });
    });

</script>
@stop
