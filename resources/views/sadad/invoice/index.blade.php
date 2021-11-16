<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">
</head>
<body class="bg-dark">
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-3 mt-5">
            <div class="card">
                <div class="card-header">
                    <h5>Laravel 7 with Validation - Sadad Bahrin Create Invoice</h5>
                </div>
                <div class="card-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-block">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>{{ $message }}</strong>
                        </div>
                    @endif

                    <form action="{{ route('invoice.index') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label><strong>Customer Name : </strong></label>
                            <input type="text" name="customerName" class="form-control">
                        </div>
                        <div class="form-group">
                            <label><strong>Amount : </strong></label>
                            <input type="text" name="amount" class="form-control">
                        </div>
                        <div class="form-group">
                            <label><strong>External Reference : </strong></label>
                            <input type="text" name="externalReference" class="form-control">
                        </div>
                        <div class="form-group">
                            <label><strong>Msisdn : </strong></label>
                            <input type="text" name="msisdn" class="form-control">
                        </div>
                        <div class="form-group">
                            <label><strong>Email : </strong></label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="exampleFormControlTextarea3">Description :</label>
                            <textarea name="description" class="form-control" id="exampleFormControlTextarea3" rows="7"></textarea>
                        </div>
                        <div class="form-group text-center">
                            <input type="submit" class="btn btn-block w-100 btn-success" name="submit" value="Save">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>
