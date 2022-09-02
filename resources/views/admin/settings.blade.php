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
                    <ul class="nav">
                        <li class="nav-item">
                            <a href="{{Route('settings.shop')}}">Shop settings</a>
                        </li>
                    </ul>
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
