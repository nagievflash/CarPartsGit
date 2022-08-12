<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>{{$product->getTitle()}}</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/admin">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$product->getTitle()}}</li>
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
                        <form action="/admin/products" method="GET" class="input-group mb-3 align-items-center">
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
                        @foreach ($products as $product)
                        <tr>
                            @php
                            $image = explode(',', $product->images)[0];
                            @endphp

                            <td style="text-align: center;">
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" class="form-check-input" id="{{$product->sku}}" name="{{$product->sku}}">
                                    </div>
                                </div>
                            </td>
                            <td style=""><img src="{{$image}}" alt="{{$product->title}}" width="75px" /></td>
                            <td>{{$product->sku}}</td>
                            <td></td>
                            <td>{{$product->qty}}</td>
                            <td>{{$price = $product->price + $product->price * .3}}</td>
                            <td>
                                <span class="badge bg-danger">Inactive</span>
                            </td>
                            <td>
                                <a href="/admin/products/{{$product->id}}/edit" class="text-primary h4 p-1" title="Edit product"><i class="bi bi-pen"></i></a>
                                <a href="/admin/products/{{$product->id}}/delete" class="text-danger h4 p-1" title="Delete product"><i class="bi bi-trash"></i></a>
                                <a href="/admin/ebay/upload?sku={{$product->sku}}" class="text-success h4 p-1" title="Upload product to Ebay"><i class="bi bi-cloud-upload"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="dataTable-bottom">
                        {{ $products->links() }}
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
