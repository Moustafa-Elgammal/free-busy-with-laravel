<html>
<head>
    <title>FreeBusy | results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<div class="container-fluid">
    <div class="container">
        <h1>
            FeeBusy System Results: <a href="{{url('/')}}">New check</a>
        </h1>
        <div class="row">
            @foreach($slots as $slot)
                <div class="col-sm-2 p-1">
                    <button class="btn btn-success">{{$slot}}</button>
                </div>
            @endforeach
        </div>
    </div>
</div>
</body>
</html>

