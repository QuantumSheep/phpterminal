@extends('../layout')
@section('title', 'Signin')

@section('content')
<div class="container">
    @if(!empty($_SESSION["errors"]))
        <div class="row col-12 alert alert-danger" role="alert">
            @foreach ($_SESSION["errors"] as $error)
                {{$error}}
            @endforeach
        </div>
    @endif
    @if(!empty($_SESSION["success"]))
        <div class="row col-12 alert alert-success" role="alert">
            @foreach ($_SESSION["success"] as $success)
                {{$success}}
            @endforeach
        </div>
    @endif
    <div class="row">
        <form class="col-md-6 m-auto" method="POST">
            {!! csrf_token() !!}
            <div class="form-group">
                <label for="password">Email address</label>
                <input type="email" required="required" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ $_SESSION["data"]["email"] ?? null }}">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" required="required" class="form-control" id="password" name="password" placeholder="Password">
            </div>

            <br>
            <button type="submit" class="btn btn-primary col-12">Sign In</button>
            <a href="/signup">Or create a new account here.</a>
        </form>
    </div>
</div>
@endsection
