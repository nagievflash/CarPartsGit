<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Settings</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Settings</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <a href="{{Route('settings.shop')}}" class="btn btn-xl btn-light-primary font-bold m-3">Shop settings</a>
                    <a href="{{Route('settings.suppliers')}}" class="btn btn-xl btn-light-secondary font-bold m-3">Suppliers settings</a>
                    <a href="{{Route('settings.taxes')}}" class="btn btn-xl btn-light-success font-bold m-3">Tax settings</a>
                    <hr />

                    <form action="/admin/inventory/start_inventory" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-xl btn-primary font-bold m-3">Update PF / LKQ inventory</button>
                    </form>

                    <form action="/admin/inventory/update_ebay_listings" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-xl btn-success font-bold m-3">Send inventory to Ebay</button>
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
