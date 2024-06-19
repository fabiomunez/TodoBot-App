@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">{{ __('Dashboard') }}</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4 class="text text-center">Your Todos</h4>
                    @if($todos->isEmpty())
                        <p>You have no pending todos.</p>
                    @else
                        <table class="table table-light table-striped">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>TASK</th>
                                    <th>STATUS</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todos as $index => $todo)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $todo->task }}</td>
                                        <td>{{ $todo->completed ? 'Completed' : 'Pending' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var botmanWidget = {
        aboutText: 'Start the conversation with Hi',
        introMessage: "WELCOME {{ strtoupper(Auth::user()->name) }}",
        title: "To Do ChatBot"
    };
</script>

<script src='https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js'></script>
@endsection
