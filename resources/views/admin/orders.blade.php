<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Orders</h3>
                <p class="text-subtitle text-muted">Orders</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
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
                                <option @php if(!empty($_GET) && array_key_exists('shipping',$_GET)) echo 'selected' @endphp value="shipping" selected>Shipping</option>
                                <option @php if(!empty($_GET) && array_key_exists('discount',$_GET)) echo 'selected' @endphp value="discount">Discount</option>
                                <option @php if(!empty($_GET) && array_key_exists('total',$_GET)) echo 'selected' @endphp value="total">Total</option>
                                <option @php if(!empty($_GET) && array_key_exists('total_quantity',$_GET)) echo 'selected' @endphp value="total_quantity">Total_quantity</option>
                                <option @php if(!empty($_GET) && array_key_exists('tax',$_GET)) echo 'selected' @endphp value="tax">Tax</option>
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
                            <th>Shipping</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Total_quantity</th>
                            <th>Tax</th>
                            <th>Note</th>
                            <th>Created_at</th>
                            <th>Updated_at</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        @if(!empty($orders))
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{$order->shipping}}</td>
                                    <td>{{$order->discount}}</td>
                                    <td>{{$order->total}}</td>
                                    <td>{{$order->total_quantity}}</td>
                                    <td>{{$ordert->tax}}</td>
                                    <td>{{$ordert->note}}</td>
                                    <td>{{$order->created_at}}</td>
                                    <td>{{$order->updated_at}}</td>
                                    <td><a href="{{Route('order.edit',$order->id)}}" class='sidebar-link'><button type="button" class="btn btn-secondary order_edit">Edit</button></a></td>
                                    <td><button type="button" data-id="{{$order->id}}" class="btn btn-danger order_delete">Delete</button></td>
                                </tr>
                            @endforeach
                            {{ $orders->appends(request()->input())->links() }}
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
            $(document).on('click', '.order_delete', function(e){
                $.ajax({
                    url: '{{Route('order.delete')}}',
                    method: 'DELETE',
                    data: {'_token': $('meta[name="csrf-token"]').attr('content'),
                            'id': $(this).attr('data-id')
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
