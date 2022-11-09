@extends('adminlte::page')

@php
    $title = 'Master Classes';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            {{-- <a href="{{route('master-classes.create')}}" class="btn btn-secondary"><i class="fa fa-plus mr-1"></i> Add Post</a> --}}
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
                                        <th>Banner Image</th>
                                        <th>Title</th>
                                        <th>Date Time</th>
                                        <th>Amount</th>
                                        <th>Creator</th>
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
            ajax: "{{ route('master-classes.index') }}",
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'banner_image', name: 'banner_image', defaultContent: defaultText,
                    render: function (data) {
                        return data ? '<img src="'+data+'" alt="" width="50" />' : null;
                    },
                    orderable: false, searchable: false
                },
                {data: 'title', name: 'title', defaultContent: defaultText},
                {data: 'start_date', name: 'start_date', defaultContent: defaultText},
                {data: 'amount', name: 'amount', defaultContent: defaultText},
                {data: 'user.full_name', name: 'user.full_name', defaultContent: defaultText,
                    render: function (data, type, row) {
                        return data ? data+'<br/><small>'+row.user.vPhoneNumber+'</small>' : null;
                    },
                    orderable: false, searchable: false
                },
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
                        url: "{{ url('admin/master-classes') }}/"+id,
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
            var status = $(this).attr('data-status');
            if(status == "{{config('constant.status.processing_value')}}") {
                return true;
            }
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
                        url: "{{ url('admin/master-classes/change-status') }}"+'/'+id,
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
