<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Ebay listing {{$listing->shop}} <a href="https://www.ebay.com/itm/{{$listing->ebay_id}}" style="text-decoration: underline;" target="_blank">{{$listing->ebay_id}}</a></h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ebay Listing</li>
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
                                <option @php if(!empty($_GET) && array_key_exists('shop',$_GET)) echo 'selected' @endphp value="shop" selected>Shop</option>
                                <option @php if(!empty($_GET) && array_key_exists('ebay_id',$_GET)) echo 'selected' @endphp value="ebay_id">Ebay_id</option>
                            </select>
                            <select id="sort" style="margin-right: 3%" class="form-select">
                                <option @php if(!empty($_GET) && in_array('asc',$_GET)) echo 'selected' @endphp value="asc">Ascending</option>
                                <option @php if(!empty($_GET) && in_array('desc',$_GET)) echo 'selected' @endphp value="desc" selected>Descending</option>
                            </select>
                            <input id="value" type="text" class="form-control" style="width: 150px" placeholder="Sort by selected attribute" aria-label="Sort by selected attribute" name="name" value="{{ app('request')->input('search') }}">
                            <button class="btn submit btn-outline-secondary" type="submit">Search</button>
                        </div>
                    </div>
                    <table class="table table-responsive">
                        <thead>
                        <tr>
                            <th>Shop</th>
                            <th>Ebay_id</th>
                            <th width="100px">Qty</th>
                            <th>Price</th>
                            <th style="width:120px; padding-left:30px">Fixed price</th>
                            <th style="width:120px; text-align: center">Ebay Link</th>
                            <th style="width:200px; text-align: center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge bg-success">{{$listing->shop}}</span>
                                </td>
                                <td>{{$listing->ebay_id}}</td>
                                <td><input class="form-control" type="number" name="listing_quantity" value="{{$listing->getQuantity()}}" /></td>
                                <td><input type="text" name="listing_price" value="{{$listing->getPrice()}}" class="form-control" /></td>
                                <td style="width:120px; padding-left:55px" align="center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input cursor-pointer" type="checkbox" name="fixed" />
                                    </div>
                                </td>
                                <td style="width:140px; text-align: center">
                                    <a href="https://www.ebay.com/itm/{{$listing->ebay_id}}" class="text-success" target="_blank">See listing</a>
                                </td>
                                <td style="width:200px; text-align: center">
                                    <a href="/admin/ebay/update_price?ebay_id={{$listing->ebay_id}}" class="text-success h4 p-1" title="Revise Item at Ebay"><i class="bi bi-cloud-upload"></i></a>
                                    <a data-href="/admin/ebay/update-listing/{{$listing->id}}" class="text-info cursor-pointer h4 p-1 update-listing" title="Update Listing Price"><i class="bi bi-arrow-repeat"></i></a>
                                    <a href="/admin/ebay/listings/{{$listing->ebay_id}}" class="text-dark h4 p-1" title="Revise Item at Ebay"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a data-href="/admin/ebay/remove-listing/{{$listing->id}}" class="text-danger h4 p-1 remove-listing" title="Remove Listing from CRM"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h3 class="mt-5">Listing composition</h3>
                    <table class="table table-responsive table-lg table-crm">
                        <thead>
                        <tr>
                            <th>Partslink</th>
                            <th width="20px">Quantity</th>
                            <th width="80px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($listing->partslinks() as $part)
                            <tr data-id="{{$part->id}}">
                                <td><input class="form-control" type="text" name="partslink" value="{{$part->partslink}}" /></td>
                                <td><input class="form-control" type="number" name="quantity" value="{{$part->quantity}}" /></td>
                                <td>
                                    <a data-href="/admin/ebay/remove-part/{{$listing->id}}" class="text-danger cursor-pointer h4 p-1 remove-part" title="Remove part from listing"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        {{ $listing->appends(request()->input())->links() }}
                        </tbody>
                    </table>
                    <pre>
                        @dump($listing->getPriceGraph(true))
                    </pre>
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

                $('.remove-part').click(function(e){
                    let result = confirm('Are you want to remove this part from the listing?');
                    if (result) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: "POST",
                            url: $(this).data('href'),
                        })
                        .done(function() {
                            $(this).remove()
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
