<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Dashboard</h3>
                <p class="text-subtitle text-muted">Autoelements CRM system</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <section class="section">
        <div class="card">
            <div class="card-body">

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

    <x-slot name="scripts">
        <script>

        </script>
    </x-slot>

</x-app-layout>
