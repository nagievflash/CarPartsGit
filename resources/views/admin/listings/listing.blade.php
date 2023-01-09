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
                                        <input class="form-check-input cursor-pointer" type="checkbox" name="fixed" @if ($listing->fixed) checked @endif />
                                    </div>
                                </td>
                                <td style="width:140px; text-align: center">
                                    <a href="https://www.ebay.com/itm/{{$listing->ebay_id}}" class="text-success" target="_blank">See listing</a>
                                </td>
                                <td style="width:200px; text-align: center">
                                    <a href="/admin/ebay/update_price?ebay_id={{$listing->ebay_id}}" class="text-success h4 p-1" title="Revise Item at Ebay"><i class="bi bi-cloud-upload"></i></a>
                                    <a data-href="/admin/update-listing/{{$listing->id}}" class="text-info cursor-pointer h4 p-1 update-listing" title="Update Listing Price"><i class="bi bi-arrow-repeat"></i></a>
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
                            <th width="140px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($listing->partslinks() as $part)
                            <tr data-id="{{$part->id}}">
                                <td><input class="form-control" type="text" name="partslink" value="{{$part->partslink}}" /></td>
                                <td><input class="form-control" type="number" name="quantity" value="{{$part->quantity}}" /></td>
                                <td>
                                    <a data-id="{{$listing->id}}" class="text-info cursor-pointer h4 p-1 update-part" title="Update Listing Part"><i class="bi bi-arrow-repeat"></i></a>
                                    <a data-href="/admin/listings/parts/remove/{{$part->id}}" class="text-danger cursor-pointer h4 p-1 remove-part" title="Remove part from listing"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="button"  class="btn btn-success d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add-new-part"><i class="bi bi-plus-circle d-inline-flex"></i><span class="ml-5">Add part</span></button>
                    <pre>
                        @dump($listing->getPriceGraph(true))
                    </pre>
                </div>
            </div>

        </section>
    </div>
    <div class="modal fade" id="add-new-part" role="dialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="/admin/listings/parts/add" id="listing_partslink" method="POST">
                    @csrf
                    <input type="hidden" name="listing_id" value ="{{$listing->id}}" />
                    <div class="modal-header">
                        <h5 class="modal-title">Add new part</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" class="form-control" name="partslink" placeholder="Partslink" />
                        </div>
                        <div class="form-group">
                            <input type="number" class="form-control" name="quantity" placeholder="Quantity" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add part</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
        <script>
            $('document').ready(function(){
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
                            location.reload()
                        });
                    }
                })

                $('.update-listing').click(function(e){
                    let qty = $('input[name="listing_quantity"]').val()
                    let price = $('input[name="listing_price"]').val()
                    let fixed = $('input[name="fixed"]').prop('checked')
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        data: { qty : qty, price : price, fixed : fixed },
                        url: $(this).data('href'),
                    })
                    .done(function() {
                       location.reload()
                    });
                })

                $('#listing_partslink').submit(function(e) {
                    e.preventDefault();
                    let data = $(this).serialize()
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        data: data,
                        url: $(this).attr('action'),
                    })
                    .done(function() {
                        location.reload()
                    });
                })

                $('.update-part').click(function(e){
                    let part = $(this).closest('tr')
                    let partslink = part.find('input[name="partslink"]').val()
                    let quantity = part.find('input[name="quantity"]').val()
                    let id = $(this).data('id')
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        data: {
                            listing_id : id,
                            partslink : partslink,
                            quantity : quantity
                        },
                        url: '/admin/listings/parts/add',
                    })
                    .done(function() {
                        location.reload()
                    });
                })

            })
        </script>
    </x-slot>
</x-app-layout>
