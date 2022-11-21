<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Create new shop item</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><a href="{{Route('settings')}}">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create New Shop</li>
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
                        <p><strong>Please fill all required fields then save</strong></p>
                        <form action="{{Route('settings.shop.store')}}" method="POST">
                            @csrf
                            <div class="col-12">
                                <div class="row">
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="title">Shop title</label>
                                        <input class="form-control form-control-lg" type="text" name="title" placeholder="Shop title">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="slug">Shop slug</label>
                                        <input class="form-control form-control-lg" type="text" name="slug" placeholder="Shop slug">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="email">Shop email</label>
                                        <input class="form-control form-control-lg" type="email" name="email" placeholder="Shop email">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="store_url">Store url</label>
                                        <input class="form-control form-control-lg" type="text" name="store_url" placeholder="Store url">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="username">Ebay Username</label>
                                        <input class="form-control form-control-lg" type="text" name="username" placeholder="Ebay Username">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="token">Ebay auth token</label>
                                        <input class="form-control form-control-lg" type="text" name="token" placeholder="Ebay auth token">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="percent">Markup percent</label>
                                        <input class="form-control form-control-lg" type="text" name="percent" placeholder="Markup percent">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="max_qty">Maximum available inventory for sale</label>
                                        <input class="form-control form-control-lg" type="number" name="max_qty" placeholder="Maximum available inventory for sale">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="qty_reserve">Amount that minus current stock</label>
                                        <input class="form-control form-control-lg" type="text" name="qty_reserve" placeholder="Amount that minus current stock">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="shipping_profile_name">Shipping Profile Name</label>
                                        <input class="form-control form-control-lg" type="text" name="shipping_profile_name" placeholder="Shipping Profile Name">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="shipping_profile_id">Shipping Profile ID</label>
                                        <input class="form-control form-control-lg" type="text" name="shipping_profile_id" placeholder="Shipping Profile ID">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="return_profile_name">Return Profile Name</label>
                                        <input class="form-control form-control-lg" type="text" name="return_profile_name" placeholder="Return Profile Name">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="return_profile_id">Return Profile ID</label>
                                        <input class="form-control form-control-lg" type="text" name="return_profile_id" placeholder="Return Profile ID">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="payment_profile_name">Payment Profile Name</label>
                                        <input class="form-control form-control-lg" type="text" name="payment_profile_name" placeholder="Payment Profile Name">
                                    </div>
                                    <div class="form-group position-relative mb-4 col-md-6">
                                        <label for="payment_profile_id">Payment Profile ID</label>
                                        <input class="form-control form-control-lg" type="text" name="payment_profile_id" placeholder="Payment Profile ID">
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-success">Create shop</button>
                        </form>
                </div>
            </div>

        </section>
    </div>
    <x-slot name="scripts">
        <script>
            $('document').ready(function(){

            })
        </script>
    </x-slot>
</x-app-layout>
