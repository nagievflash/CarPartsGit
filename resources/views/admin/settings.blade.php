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
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                    <a href="{{Route('settings.shop')}}" class="btn btn-xl btn-light-primary font-bold m-3">Shop settings</a>
                    <a href="{{Route('settings.suppliers')}}" class="btn btn-xl btn-light-secondary font-bold m-3">Suppliers settings</a>
                    <a href="{{Route('settings.taxes')}}" class="btn btn-xl btn-light-success font-bold m-3">Tax settings</a>
                    <div class="form-check form-switch">
                        <input class="form-check-input maintenance maintenance_checked" @php if($settings['maintenance']) echo 'checked' @endphp type="checkbox" value="true" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault">Maintenance mode</label>
                    </div>
                </div>
            </div>

        </section>
    </div>
    <x-slot name="scripts">
        <script>
            $(document).on('click', '.maintenance', function(e){
                $.ajax({
                    url: '{{Route('maintenance')}}',
                    method: 'POST',
                    data: {'_token': $('meta[name="csrf-token"]').attr('content'),
                        'maintenance':$('.maintenance_checked').is(':checked')
                    },
                    error: function (data) {
                       //
                    }
                }).done(function (data) {
                    //
                });
            });
        </script>
    </x-slot>
</x-app-layout>
