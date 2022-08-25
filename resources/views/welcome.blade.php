<html>
<head>
    <title>FreeBusy | home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<div class="container-fluid">
    <div class="container">
        <h1>
            FeeBusy System
        </h1>
        @if($errors->any())
            {!! implode('', $errors->all('<div style="color:red">:message</div>')) !!}
            <hr>
        @endif
        <div class="row">
            <div class="col-12">
                <form id="form" method="post" action="{{url('results')}}">
                    <div class="form-group">
                        <label for="length">Desired meeting length (in minutes):</label>
                        <input type="number"
                               min="1"
                               class="form-control"
                               id="length"
                               value="{{request()->length ?? '30'}}"
                               name="length"
                               placeholder="in minutes"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="ids">Select needed employees</label>
                        <select id="ids" class="form-control" name="ids[]" multiple required>
                            @foreach($employees as $employee)
                                <option value="{{$employee->id}}">{{$employee->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="earliest">Earliest Date</label>
                        <input type="datetime-local"
                               class="form-control"
                               id="earliest"
                               name="earliest"
                               value="{{request()->earliest}}"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="latest">Latest Date</label>
                        <input type="datetime-local"
                               class="form-control"
                               id="latest"
                               name="latest"
                               value="{{request()->latest}}"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="startAt">Work starts At</label>
                        <input type="number"
                               class="form-control"
                               id="startAt"
                               min="0"
                               max="23"
                               name="startAt"
                               value="8"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="endAt">Work ends At</label>
                        <input type="number"
                               class="form-control"
                               id="endAt"
                               min="0"
                               max="24"
                               name="endAt"
                               value="17"
                               required>
                    </div>

                    <button class="btn btn-dark">check</button>
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

