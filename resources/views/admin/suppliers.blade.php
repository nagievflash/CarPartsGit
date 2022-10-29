<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Suppliers</h3>
                <p class="text-subtitle text-muted">Suppliers settings</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Suppliers</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-body p-5">
                    <h3>LKQ Package Prices</h3>

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
                    <p><strong>Press Save to update suppliers settings</strong></p>
                    <form action="/admin/settings/suppliers/lkq/update" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="row">
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <label for="">SP cost</label>
                                    <input class="form-control form-control-lg" type="text" name="lkq_cost_sp" placeholder="SP cost" value="{{$suppliers["lkq_cost_sp"]}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <label for="">MP cost</label>
                                    <input class="form-control form-control-lg" type="text" name="lkq_cost_mp" placeholder="MP cost" value="{{$suppliers["lkq_cost_mp"]}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <label for="">LP cost</label>
                                    <input class="form-control form-control-lg" type="text" name="lkq_cost_lp" placeholder="LP cost" value="{{$suppliers["lkq_cost_lp"]}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <label for="">LTL cost</label>
                                    <input class="form-control form-control-lg" type="text" name="lkq_cost_lt" placeholder="LTL cost" value="{{$suppliers["lkq_cost_lt"]}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <label for="">Additional SP cost</label>
                                    <input class="form-control form-control-lg" type="text" name="lkq_cost_additional_sp" placeholder="Additional SP cost" value="{{$suppliers["lkq_cost_additional_sp"]}}">
                                </div>
                                <div class="form-group position-relative mb-4 col-md-6">
                                    <label for="">Additional MP cost</label>
                                    <input class="form-control form-control-lg" type="text" name="lkq_cost_additional_mp" placeholder="Additional MP cost" value="{{$suppliers["lkq_cost_additional_mp"]}}">
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
