<x-app-layout>
    <x-slot name="header">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Orders</h3>
                <p class="text-subtitle text-muted">Edit order</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/users">Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit order</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>


    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div id="alert" class="alert" role="alert"></div>
                    <div id="form">
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        <select id="status" style="margin-right: 3%" class="form-select">
                            <option @php if(!empty($order->status) && $order->status === 'test1') echo 'selected' @endphp value="test1" selected>test1</option>
                            <option @php if(!empty($order->status) && $order->status === 'test2') echo 'selected' @endphp value="test2" selected>test2</option>
                        </select>
                        <div class="form-group">
                            <label for="exampleFormControlTextarea1">Note</label>
                            <textarea id="note" class="form-control" rows="3">{{$order->note}}</textarea>
                        </div>
                        <br>
                        <input class="btn btn-secondary submit" value="Submit" type="submit">
                    </div>
                </div>
            </div>

        </section>
    </div>
    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
        <script>
            $(document).on('click', '.submit', function(e){
                $.ajax({
                    url: '{{Route('order.update',$order->id)}}',
                    method: 'PUT',
                    data: {'_token': $('meta[name="csrf-token"]').attr('content'),
                        'status': $("#status").val(),
                        'note': $("#note").val(),
                    },
                    error: function (data) {
                        $("#alert").removeClass('alert-success').addClass('alert-danger').text(data.responseJSON.message)
                    }
                }).done(function (data) {
                    $("#alert").removeClass('alert-danger').addClass('alert-success').text(data.message)
                });
            });
        </script>
    </x-slot>
</x-app-layout>
