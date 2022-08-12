<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Import products from CSV</h3>
                <p class="text-subtitle text-muted">This is the products import page.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">CSV import</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">File import form</h4>
            </div>
            <div class="card-body">
                <form action="/admin/import" method="POST" enctype="multipart/form-data">
                    @csrf
                    <x-maz-input :id="'csv-import'"
                                 :name="'csv-import'"
                                 :label="'Download correct CSV File with products'"
                                 :type="'file'">
                    </x-maz-input>
                    <a href="https://auth.ebay.com/oauth2/authorize?client_id=fastdeal-autoelem-PRD-4f2fb35bc-cbb0b166&response_type=code&redirect_uri=fastdeal24-fastdeal-autoel-ymxyoese&scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly https://api.ebay.com/oauth/api_scope/sell.finances https://api.ebay.com/oauth/api_scope/sell.payment.dispute https://api.ebay.com/oauth/api_scope/commerce.identity.readonly https://api.ebay.com/oauth/api_scope/commerce.notification.subscription https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly">Get OAuth Access Token</a>
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
{{--                 <div class="form-field mt-3">
                        <textarea class="form-control" name="token">{{$code}}</textarea>
                    </div>
                    <div class="form-field mt-3">
                        <input type="text" class="form-control" name="query" placeholder="Название категории">
                    </div>--}}
                    <div class="form-field mt-3">
                        <button type="submit" class="btn btn-success form-submit">Отправить запрос</button>
                    </div>
                </form>
            </div>

        </div>
    </section>


    <x-slot name="scripts">
        <script>
/*            $('document').ready(function(){
                $('.form-submit').click(function(e){
                    e.preventDefault()
                    let data = {
                        _token  : $('input[name="_token"]').val(),
                        token   : $('textarea[name="token"]').val(),
                        query   : $('input[name="query"]').val(),
                        contentType: "text/xml"
                    }

                    $.post( "/admin/getSuggestedCategories", data)
                    .done(function( data ) {
                        console.log( data );
                    });
                })
            })*/
        </script>
    </x-slot>
</x-app-layout>
