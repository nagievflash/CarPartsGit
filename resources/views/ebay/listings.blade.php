<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Products</h3>
                <p class="text-subtitle text-muted">Ebay listings</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ebay Listings</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-body">
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
                    <div class="col-md-6 mb-1">
                        <form action="{{Route('ebay.listings')}}" method="GET" class="input-group mb-3 align-items-center">
                            @csrf
                            <input type="text" class="form-control" placeholder="Product SKU or Ebay Listing ID" aria-label="Product SKU" name="search" value="{{ app('request')->input('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                        </form>
                    </div>
                    <table class="table table-striped" id="table1">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Shop</th>
                            <th>Type</th>
                            <th>Ebay_id</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Ebay Link</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($listings as $listing)
                            <tr>

                                <td style="text-align: center;">
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" class="form-check-input" id="{{$listing->id}}" name="id">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{$listing->shop}}</span>
                                </td>
                                <td>
                                    {{$listing->type}}
                                </td>
                                <td>{{$listing->ebay_id}}</td>
                                <td>{{$listing->getQuantity()}}</td>
                                <td>{{$listing->getPrice()}}</td>
                                <td>
                                    <a href="https://www.ebay.com/itm/{{$listing->ebay_id}}" class="text-success" target="_blank">See listing</a>
                                </td>
                                <td>
                                    <a href="/admin/ebay/update_price?ebay_id={{$listing->ebay_id}}" class="text-success h4 p-1" title="Revise Item at Ebay"><i class="bi bi-cloud-upload"></i></a>
                                    <a data-href="/admin/ebay/update-listing/{{$listing->id}}" class="text-info cursor-pointer h4 p-1 update-listing" title="Update Listing Price"><i class="bi bi-arrow-repeat"></i></a>
                                    <a href="/admin/ebay/listings/{{$listing->ebay_id}}" class="text-dark h4 p-1" title="Revise Item at Ebay"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a data-href="/admin/ebay/remove-listing/{{$listing->id}}" class="text-danger h4 p-1 remove-listing" title="Remove Listing from CRM"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="dataTable-bottom">
                        {{ $listings->links() }}
                    </div>
                </div>
            </div>

        </section>
    </div>
    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
        <script>
            $('document').ready(function(){
                $('.remove-listing').click(function(e){
                    let result = confirm('Are you want to remove this listing from CRM system?');
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

                $('.update-listing').click(function(e){
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
                })
            })
        </script>
    </x-slot>
</x-app-layout>
