<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Update Ebay Listing IDs from csv/txt files</h3>
                <p class="text-subtitle text-muted">This is the Update Ebay Listing IDs page.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">csv/txt import</li>
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
                <form action="/admin/import/updateEbayListingId" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <select name="shop" class="form-control ">
                            <option value="ebay3">Ebay 3</option>
                            <option value="ebay4">Ebay 4</option>
                        </select>
                    </div>
                    <x-maz-input :id="'csv-import'"
                                 :name="'csv-import'"
                                 :label="'Download correct txt or csv ebay listings'"
                                 :type="'file'">
                    </x-maz-input>
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
                                            <input type="text" class="form-control" name="query" placeholder="???????????????? ??????????????????">
                                        </div>--}}
                    <div class="form-field mt-4">
                        <button type="submit" class="btn btn-success form-submit">Update Listings</button>
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
