@extends('admin/layout')
@section('title', 'Terminal')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-1">
                <h1>Terminals list</h1>
                <div>,
                    <a href="/admin/terminal/add?network={{ $model->network->mac }}" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                </div>
            </div>
            <div class="list-group">
                @foreach($model->terminals as $terminal)
                    <a href="/admin/terminal/{{ $terminal->mac }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>{{ $model->accounts[$terminal->account]->username }} - {{ $model->accounts[$terminal->account]->email }}</span>
                        <span class="badge badge-primary badge-pill">{{ $terminal->mac }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
