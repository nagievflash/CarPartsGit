<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>{{$shop->title}} Shop</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a href="{{Route('settings')}}">Settings</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a href="{{Route('settings.shop')}}">Shop</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$shop->title}} Shop</li>
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
                    <p><strong>Press Save to update shop settings</strong></p>
                    <form action="/admin/settings/shop/{{$shop->slug}}/update" method="POST">
                        <input type="hidden" name="_method" value="put" />
                        @csrf
                        <div class="col-12">
                            <div class="row">
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="title" placeholder="Shop title" value="{{$shop->title}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="slug" placeholder="Shop slug" value="{{$shop->slug}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="email" name="email" placeholder="Shop email" value="{{$shop->email}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="store_url" placeholder="Store url" value="{{$shop->store_url}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="username" placeholder="Ebay Username" value="{{$shop->username}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="token" placeholder="Ebay auth token" value="{{$shop->token}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="percent" placeholder="Markup percent" value="{{$shop->percent}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="number" name="max_qty" placeholder="Maximum available inventory for sale" value="{{$shop->max_qty}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="qty_reserve" placeholder="Amount that minus current stock" value="{{$shop->qty_reserve}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="shipping_profile_name" placeholder="Shipping Profile Name" value="{{$shop->shipping_profile_name}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="shipping_profile_id" placeholder="Shipping Profile ID" value="{{$shop->shipping_profile_id}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="return_profile_name" placeholder="Return Profile Name" value="{{$shop->return_profile_name}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="return_profile_id" placeholder="Return Profile ID" value="{{$shop->return_profile_id}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="payment_profile_name" placeholder="Payment Profile Name" value="{{$shop->payment_profile_name}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <input class="form-control form-control-lg" type="text" name="payment_profile_id" placeholder="Payment Profile ID" value="{{$shop->payment_profile_id}}">
                                </div>
                            </div>
                        </div>


                        <button type="submit" class="btn btn-success">Save</button>
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
