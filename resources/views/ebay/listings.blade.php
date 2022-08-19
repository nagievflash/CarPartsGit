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
                            <input type="text" class="form-control" placeholder="Product SKU" aria-label="Product SKU" name="search" value="{{ app('request')->input('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                        </form>
                    </div>
                    <table class="table table-striped" id="table1">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Image</th>
                            <th>SKU</th>
                            <th>Title</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($listings as $listing)
                            <tr>
                                @php
                                    $image = explode(',', $listing->product->images)[0];
                                @endphp

                                <td style="text-align: center;">
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" class="form-check-input" id="{{$listing->product->sku}}" name="{{$listing->product->sku}}">
                                        </div>
                                    </div>
                                </td>
                                <td style="">
                                    @if ($image)
                                        <img src="{{$image}}" alt="{{$listing->product->getTitle()}}" width="75px" />
                                    @endif
                                </td>
                                <td>{{$listing->product->sku}}</td>
                                <td>{{$listing->product->getTitle()}}</td>
                                <td>{{$listing->product->qty}}</td>
                                <td>{{$listing->product = $listing->product->price + $listing->product->price * .25}}</td>
                                <td>
                                    <span class="badge bg-success">{{$listing->type}}</span>
                                </td>
                                <td>
                                    <a href="/admin/ebay/revise?ebay_id={{$listing->ebay_id}}" class="text-success h4 p-1" title="Revise Item at Ebay"><i class="bi bi-arrow-repeat"></i></a>
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

            })
        </script>
    </x-slot>
</x-app-layout>
