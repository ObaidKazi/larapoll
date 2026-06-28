@extends('layouts.public', ['title' => 'Welcome'])

@section('content')
    <div class="text-center py-5 mt-4">
        <h1 class="display-5 fw-bold text-dark mb-3">Welcome to Lara Poll</h1>
        
        <p class="lead text-muted mx-auto mb-5" style="max-width: 600px;">
            The simplest way to gather opinions in real-time. 
            Watch the results update instantly as votes come in from around the world.
        </p>

        <div class="card shadow-sm border-0 bg-light mx-auto" style="max-width: 450px;">
            <div class="card-body py-4">
                <h5 class="fw-medium mb-2">Ready to vote?</h5>
                <p class="text-secondary small mb-0">
                    If you have a poll link, simply paste it into your browser's address bar to cast your vote.
                </p>
            </div>
        </div>
    </div>
@endsection