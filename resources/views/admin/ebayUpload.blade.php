<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Import custom products from csv/txt files and upload to Ebay</h3>
                <p class="text-subtitle text-muted">This is the Products import page.</p>
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
                <form action="/admin/import/products/custom/" method="POST" enctype="multipart/form-data">
                    @csrf
                    <x-maz-input :id="'csv-import'"
                                 :name="'csv-import'"
                                 :label="'Download correct txt or csv products file'"
                                 :type="'file'">
                    </x-maz-input>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="form-field mt-4">
                        <button type="submit" class="btn btn-success form-submit">Upload to Ebay</button>
                    </div>
                </form>
            </div>

        </div>
    </section>


    <x-slot name="scripts">
        <script>

        </script>
    </x-slot>
</x-app-layout>
