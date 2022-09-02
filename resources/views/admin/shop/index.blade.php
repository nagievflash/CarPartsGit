<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Settings</h3>
                <p class="text-subtitle text-muted">Store list</p>
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
                <div class="card-header">
                    <a href="{{Route('settings.shop.create')}}" class="btn btn-primary d-flex align-items-center justify-content-center" style="max-width:220px;"><i class="bi bi-bag-plus" style="margin-top: -12px;margin-right: 10px;"></i> <span>Add New Store</span></a>
                </div>
                <div class="card-body">
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
                    <table class="table table-striped" id="table1">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th class="d-flex justify-content-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($shops as $shop)
                            <tr>
                                <td>{{$shop->title}}</td>
                                <td>{{$shop->slug}}</td>
                                <td class="d-flex justify-content-center">
                                    <a href="/admin/settings/shop/{{$shop->slug}}" class="btn btn-success me-2 d-flex" title="Delete product"><i class="bi bi-trash me-1"></i><span class="d-none d-md-block">Edit</span></a>
                                    <form action="/admin/settings/shop/{{$shop->slug}}/delete" method="POST">
                                        @csrf
                                        <input type="hidden" name="_method" value="delete" />
                                        <button type="submit" class="btn btn-danger d-flex" title="Delete product"><i class="bi bi-trash me-1"></i><span class="d-none d-md-block">Delete</span></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
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
