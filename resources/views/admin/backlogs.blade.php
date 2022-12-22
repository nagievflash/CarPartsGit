<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Backlogs</h3>
                <p class="text-subtitle text-muted">Backlogs</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Backlogs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div id="alert" class="alert" role="alert"></div>
                    <table class="table table-striped" id="table1">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Created_at</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        @if(!empty($backlogs))
                            @foreach ($backlogs as $backlog)
                                <tr>
                                    <td>{{$backlog->id}}</td>
                                    <td>{{$backlog->type}}</td>
                                    <td>{{$backlog->value}}</td>
                                    <td>{{$backlog->created_at}}</td>
                                    <td><button type="button" data-id="{{$backlog->id}}" class="btn btn-danger backlog_delete">Delete</button></td>
                                </tr>
                            @endforeach
                            {{ $backlogs->links() }}
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>
    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
        <script>
            $(document).on('click', '.backlog_delete', function(e){
                $.ajax({
                    url: '{{Route('backlog.delete')}}',
                    method: 'DELETE',
                    data: {'_token': $('meta[name="csrf-token"]').attr('content'),
                            'id':$(this).attr('data-id')
                    },
                    error: function (data) {
                        $("#alert").removeClass('alert-success').addClass('alert-danger').text(data.responseJSON.message)
                    }
                }).done(function (data) {
                    $("#alert").removeClass('alert-danger').addClass('alert-success').text(data.message)
                });
            });
        </script>
    </x-slot>
</x-app-layout>
