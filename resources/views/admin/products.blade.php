<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Products</h3>
                <p class="text-subtitle text-muted">Product listings</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
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
                            <div class="input-group mb-3 align-items-center">
                                @csrf
                                <select id="key" style="margin-right: 3%" class="form-select">
                                    <option @php if(!empty($_GET) && array_key_exists('title',$_GET)) echo 'selected' @endphp value="title" selected>Title</option>
                                    <option @php if(!empty($_GET) && array_key_exists('sku',$_GET)) echo 'selected' @endphp value="sku">Sku</option>
                                    <option @php if(!empty($_GET) && array_key_exists('price',$_GET)) echo 'selected' @endphp value="price">Price</option>
                                    <option @php if(!empty($_GET) && array_key_exists('qty',$_GET)) echo 'selected' @endphp value="qty">Qty</option>
                                    <option @php if(!empty($_GET) && array_key_exists('status',$_GET)) echo 'selected' @endphp value="status">Status</option>
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
                            <td style="">
                                @if ($image)
                                <img src="{{$image}}" alt="{{$product->title}}" width="75px" />
                                @endif
                            </td>
                            <td>{{$product->sku}}</td>
                            <td>{{$product->getTitle()}}</td>
                            <td>{{$product->qty}}</td>
                            <td>{{$product->price}}</td>
                            <td>
                                <span class="badge bg-danger">Inactive</span>
                            </td>
                            <td>
                                {{--<a href="/admin/products/{{$product->id}}/edit" class="text-primary h4 p-1" title="Edit product"><i class="bi bi-pen"></i></a>
                                <a href="/admin/products/{{$product->id}}/delete" class="text-danger h4 p-1" title="Delete product"><i class="bi bi-trash"></i></a>--}}
                                <form action="/admin/ebay/upload" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="ebay4">
                                    <input type="hidden" name="sku" value="{{$product->sku}}">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="dataTable-bottom">
                        {{ $products->appends(request()->input())->links() }}
                    </div>
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
        </script>
    </x-slot>
</x-app-layout>
