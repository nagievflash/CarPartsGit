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
                        <form action="/admin/orders" method="GET" class="input-group mb-3 align-items-center">
                            @csrf
                            <input type="text" class="form-control" placeholder="Search by order id" aria-label="Search by order id" name="search" value="{{ app('request')->input('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                        </form>
                    </div>
                    <table class="table table-striped" id="table1">
                        <thead>
                        <tr>
                            <th>Shipping</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Total_quantity</th>
                            <th>Tax</th>
                            <th>Created_at</th>
                            <th>Updated_at</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($orders))
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{$order->shipping}}</td>
                                    <td>{{$order->discount}}</td>
                                    <td>{{$order->total}}</td>
                                    <td>{{$order->total_quantity}}</td>
                                    <td>{{$ordert->tax}}</td>
                                    <td>{{$order->created_at}}</td>
                                    <td>{{$order->updated_at}}</td>
                                    <td>{{$order->updated_at}}</td>
                                    <td><button type="button" class="btn btn-secondary order_edit">Edit</button></td>
                                    <td><button type="button" data-id="{{$order->id}}" class="btn btn-danger order_delete">Delete</button></td>
                                </tr>
                            @endforeach
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
            $(document).on('click', '.order_delete', function () {

                let id = $(this).attr('data-id')

                $.ajax({
                    url: '/orders',
                    type: 'delete',
                    data: {
                        id: id,
                    },
                    success() {

                    }
                }).then(() => {
                    location.reload();

                })
            });

        </script>
    </x-slot>
</x-app-layout>
