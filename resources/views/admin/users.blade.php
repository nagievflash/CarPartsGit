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
                    <div class="col-md-6 mb-1">
                        <div class="input-group mb-3 align-items-center">
                            @csrf
                            <select id="key" style="margin-right: 3%" class="form-select">
                                <option @php if(!empty($_GET) && array_key_exists('name',$_GET)) echo 'selected' @endphp value="name" selected>Name</option>
                                <option @php if(!empty($_GET) && array_key_exists('lastname',$_GET)) echo 'selected' @endphp value="lastname">Lastname</option>
                                <option @php if(!empty($_GET) && array_key_exists('email',$_GET)) echo 'selected' @endphp value="email">Email</option>
                                <option @php if(!empty($_GET) && array_key_exists('phone',$_GET)) echo 'selected' @endphp value="phone">Phone</option>
                            </select>
                            <select id="sort" style="margin-right: 3%" class="form-select">
                                <option @php if(!empty($_GET) && in_array('asc',$_GET)) echo 'selected' @endphp value="asc">Ascending</option>
                                <option @php if(!empty($_GET) && in_array('desc',$_GET)) echo 'selected' @endphp value="desc" selected>Descending</option>
                            </select>
                            <input id="value" type="text" class="form-control" style="width: 150px" placeholder="Sort by selected attribute" aria-label="Sort by selected attribute" name="name" value="{{ app('request')->input('search') }}">
                            <button class="btn submit btn-outline-secondary" type="submit">Search</button>
                        </div>
                    </div>
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
                            {{ $users->appends(request()->input())->links() }}
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
            $(document).on('click', '.submit', function(e){
                e.preventDefault();
                var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                var newUrl = baseUrl + '?sort=' + $("#sort").val() + '&' + $("#key").val() + '=' + $("#value").val();
                location.href = newUrl
            });

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
