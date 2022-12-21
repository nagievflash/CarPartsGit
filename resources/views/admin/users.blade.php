<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Users</h3>
                <p class="text-subtitle text-muted">Users</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
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
                            <th>Name</th>
                            <th>Lastname</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Created_at</th>
                            <th>Updated_at</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        @if(!empty($users))
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->lastname}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->phone}}</td>
                                    <td>{{$user->created_at}}</td>
                                    <td>{{$user->updated_at}}</td>
                                    <td><a href="{{Route('user.edit',$user->id)}}" class='sidebar-link'><button type="button" class="btn btn-secondary order_edit">Edit</button></a></td>
                                    <td><button type="button" data-id="{{$user->id}}" class="btn btn-danger user_delete">Delete</button></td>
                                </tr>
                            @endforeach
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
            $(document).on('click', '.user_delete', function(e){
                $.ajax({
                    url: '{{Route('user.delete')}}',
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
