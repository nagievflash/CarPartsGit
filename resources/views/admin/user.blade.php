<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Users</h3>
                <p class="text-subtitle text-muted">Edit user</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/users">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit user</li>
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
                        <div id="form">
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="name" value="{{$user->name}}" placeholder="Username">
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="{{$user->lastname}}" id="lastname"  placeholder="Lastname">
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="{{$user->email}}" id="email"  placeholder="Email">
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" id="phone"  value="{{$user->phone}}"  class="form-control" placeholder="Phone">
                            </div>
                            <br>
                            <input class="btn btn-secondary submit" value="Submit" type="submit">
                        </div>
                        <br>
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <p class="text-subtitle text-muted">Reset Password</p>
                        </div>
                        <div id="form-password">
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <div class="input-group mb-3">
                                <input type="password" class="form-control" id="new_password" placeholder="New password">
                            </div>
                            <div class="input-group mb-3">
                                <input type="password" class="form-control" id="new_confirm_password"  placeholder="Confirm password">
                            </div>
                            <br>
                            <input class="btn btn-secondary submit-password" value="Submit" type="submit">
                        </div>
                </div>
            </div>

        </section>
    </div>
    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
        <script>
            $(document).on('click', '.submit', function(e){
                $.ajax({
                    url: '{{Route('user.update',$user->id)}}',
                    method: 'PUT',
                    data: {'_token': $('meta[name="csrf-token"]').attr('content'),
                            'name': $("#name").val(),
                            'lastname': $("#lastname").val(),
                            'email': $("#email").val(),
                            'phone': $("#phone").val()
                    },
                    error: function (data) {
                    $("#alert").removeClass('alert-success').addClass('alert-danger').text(data.responseJSON.message)
                }
                }).done(function (data) {
                    $("#alert").removeClass('alert-danger').addClass('alert-success').text(data.message)
                });
            });
            $(document).on('click', '.submit-password', function(e){
                $.ajax({
                    url: '{{Route('user.password-update',$user->id)}}',
                    method: 'POST',
                    data: {'_token': $('meta[name="csrf-token"]').attr('content'),
                        'new_confirm_password': $("#new_confirm_password").val(),
                        'new_password': $("#new_password").val(),
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
