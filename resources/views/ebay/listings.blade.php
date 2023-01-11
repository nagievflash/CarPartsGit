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
                    <div id="alert" class="alert" role="alert"></div>
                    <div class="col-md-6 mb-1">
                        <div class="input-group mb-3 align-items-center">
                            @csrf
                            <select id="key" style="margin-right: 3%" class="form-select">
                                <option @php if(!empty($_GET) && array_key_exists('id',$_GET)) echo 'selected' @endphp value="id" selected>Id</option>
                                <option @php if(!empty($_GET) && array_key_exists('type',$_GET)) echo 'selected' @endphp value="type">Type</option>
                                <option @php if(!empty($_GET) && array_key_exists('value',$_GET)) echo 'selected' @endphp value="value">Value</option>
                            </select>
                            <select id="sort" style="margin-right: 3%" class="form-select">
                                <option @php if(!empty($_GET) && in_array('asc',$_GET)) echo 'selected' @endphp value="asc">Ascending</option>
                                <option @php if(!empty($_GET) && in_array('desc',$_GET)) echo 'selected' @endphp value="desc" selected>Descending</option>
                            </select>
                            <input id="value" type="text" class="form-control" style="width: 150px" placeholder="Sort by selected attribute" aria-label="Sort by selected attribute" name="name" value="{{ app('request')->input('search') }}">
                            <button class="btn submit btn-outline-secondary" type="submit">Search</button>
                        </div>
                    </div>
                    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        {{ $listings->appends(request()->input())->links() }}
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
                $(document).on('click', '.submit', function(e){
                    e.preventDefault();
                    var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    var newUrl = baseUrl + '?sort=' + $("#sort").val() + '&' + $("#key").val() + '=' + $("#value").val();
                    location.href = newUrl
                });
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
