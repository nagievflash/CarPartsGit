<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Taxes configuration</h3>
                <p class="text-subtitle text-muted">Setup the tax rate that is used by system</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Taxes</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-body p-5">

                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <ul>
                                <li>{!! \Session::get('success') !!}</li>
                            </ul>
                        </div>
                    @endif

                    @if (\Session::has('error'))
                        <div class="alert alert-danger">
                            <ul>
                                <li>{!! \Session::get('error') !!}</li>
                            </ul>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center w-100 mb-4">
                        <h3>Taxes list</h3>
                        <a class="btn btn-success" data-bs-toggle="modal" data-bs-target="#add-tax-modal">+ Add tax</a>
                    </div>
                    <table class="table-striped table">
                        <thead>
                            <tr>
                                <td width="5%">id</td>
                                <td>State</td>
                                <td>Rate</td>
                                <td width="15%">Actions</td>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($taxes as $key => $tax)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $tax->state }}</td>
                                <td>{{ $tax->rate }}</td>
                                <td>
                                    <a data-href="/admin/taxes/remove/{{$tax->id}}" class="text-danger h4 p-1 remove-tax cursor-pointer" title="Remove tax from CRM"><i class="bi bi-trash"></i></a>
                                    <a data-href="/admin/taxes/update/{{$tax->id}}" class="text-secondary h4 p-1 edit-tax cursor-pointer" data-state="{{$tax->state}}" data-state="{{$tax->state}}" title="Edit tax rate"><i class="bi bi-pen"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
        <div class="modal fade" id="add-tax-modal" tabindex="-1" aria-labelledby="add-tax-modal-title" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="add-tax-modal-total">
                            Add new tax record
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                    <form action="/admin/taxes/store" method="POST">
                        @csrf
                    <div class="modal-body">
                        <div class="from-group">
                            <label for="state">State</label>
                            <select class="form-control" name="state">
                                @foreach(\App\Models\State::all() as $state)
                                    <option value="{{$state->abbreviation}}">{{$state->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="from-group mt-4">
                            <label for="rate">Rate</label>
                            <input class="form-control" type="text" name="rate">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Close</span>
                        </button>
                        <button type="submit" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                            <i class="bx bx-check d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Accept</span>
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="scripts">
        <script>
            $('document').ready(function(){
                $('.remove-tax').click(function(e){
                    let result = confirm('Are you want to remove this tax rate from CRM system?');
                    if (result) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: "POST",
                            url: $(this).data('href'),
                        })
                        .done(function() {
                            location.reload()
                        });
                    }
                    $('.edit-tax').click(function(e){
                        let result = prompt('Edit state', $(this).fi);
                        if (result) {
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: "POST",
                                url: $(this).data('href'),
                            })
                                .done(function() {
                                    location.reload()
                                });
                        }
                })
            })
        </script>
    </x-slot>
</x-app-layout>
