@extends('adminlte::page')

@php
    $title = 'Invited Creators';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
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
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Contact Number</th>
                                        <th>Whatsapp Number</th>
                                        <th>action</th>
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
            ajax: "{{ route('creators.invited') }}",
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'first_name', name: 'first_name', defaultContent: defaultText},
                {data: 'last_name', name: 'last_name', defaultContent: defaultText},
                {data: 'email', name: 'email', defaultContent: defaultText, 
                    render: function (data) {
                        return data ? '<a href="mailto:'+data+'">'+data+'</a>' : null;
                    },
                },
                {data: 'contact_number', name: 'contact_number', defaultContent: defaultText,
                    render: function (data) {
                        return data ? '<a href="tel:'+data+'">'+data+'</a>' : null;
                    },
                },
                {data: 'whatsapp_number', name: 'whatsapp_number', defaultContent: defaultText,
                    render: function (data) {
                        return data ? '<a href="tel:'+data+'">'+data+'</a>' : null;
                    },
                },
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
            // dom: 'Bfrtip',
            // buttons: [
            //     {
            //         text: 'My button',
            //         action: function ( e, dt, node, config ) {
            //             // alert( 'Button activated' );
            //         }
            //     }
            // ]
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
                        url: "{{ url('admin/creators/invited/delete') }}/"+id,
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

        $(document).on('click', '.approveBtn', function(e) {
            Swal.fire({
                title: 'Are you sure want to approve?',
                // text: "All related items will be affected!",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = $(this).attr('data-id');
                    var user_status = "{{config('constant.invitation.approve')}}";
                    $.ajax({
                        url: "{{ url('admin/creators/invited/change-status') }}"+'/'+id,
                        data:{
                            'user_status': user_status,
                            '_token': '{{ csrf_token() }}',
                        },
                        method: 'POST',
                        cache: false,
                        success: function(html){
                            Swal.fire(
                                'Approved!',
                                'Invitation has been approved.',
                                'success'
                            );

                            var oTable = $('#index_table').dataTable();
                            oTable.fnDraw(false);
                        }
                    });
                }
            });
        });

        $(document).on('click', '.rejectBtn', function(e) {
            Swal.fire({
                title: 'Are you sure want to reject?',
                // text: "All related items will be affected!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reject it!',
                input: 'textarea',
                inputPlaceholder: 'Rejection note',
                inputValidator: (result) => {
                    return !result && 'Please add rejection note'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = $(this).attr('data-id');
                    var user_status = "{{config('constant.invitation.reject')}}";
                    var user_note = (result && result.value) ? result.value : null;
                    $.ajax({
                        url: "{{ url('admin/creators/invited/change-status') }}"+'/'+id,
                        data:{
                            'user_status': user_status,
                            'user_note': user_note,
                            '_token': '{{ csrf_token() }}',
                        },
                        method: 'POST',
                        cache: false,
                        success: function(html){
                            Swal.fire(
                                'Rejected!',
                                'Invitation has been rejected.',
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
