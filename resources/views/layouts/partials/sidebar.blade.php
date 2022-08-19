<x-maz-sidebar :href="route('dashboard')" :logo="asset('images/logo/logo.png')">

    <!-- Add Sidebar Menu Items Here -->

    <x-maz-sidebar-item name="Dashboard" :link="route('dashboard')" icon="bi bi-grid-fill"></x-maz-sidebar-item>
    <x-maz-sidebar-item name="Products" :link="route('products.list')" icon="bi bi-box"></x-maz-sidebar-item>
    <x-maz-sidebar-item name="Ebay Listings" :link="route('ebay.listings')" icon="bi bi-shop"></x-maz-sidebar-item>
    <x-maz-sidebar-item name="CSV Import" :link="route('import')" icon="bi bi-box-arrow-in-down-left"></x-maz-sidebar-item>
    <x-maz-sidebar-item name="Upload Ebay" :link="route('ebayUpload')" icon="bi bi-box-arrow-in-down-left"></x-maz-sidebar-item>

</x-maz-sidebar>
