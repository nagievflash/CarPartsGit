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
                <form action="">
                    @csrf
                    <x-maz-input :id="'csvimport'"
                                 :name="'csvimport'"
                                 :label="'Download correct CSV File with products'"
                                 :type="'file'">
                    </x-maz-input>
                </form>
            </div>

        </div>
    </section>
</x-app-layout>
