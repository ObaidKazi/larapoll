@extends('layouts.public', ['title' => $poll->question])

@section('content')
<div x-data="pollApp" x-init="init()" x-cloak>

    {{-- Header --}}
    <div class="mb-4">
        @if($poll->isOpen())
            <span class="badge bg-success mb-2">● Live</span>
        @else
            <span class="badge bg-secondary mb-2">Closed</span>
        @endif
        <h1 class="h3 fw-bold">{{ $poll->question }}</h1>
        @if($poll->ends_at && $poll->isOpen())
            <p class="text-muted small mb-0">Closes {{ $poll->ends_at->timezone(config('app.timezone'))->diffForHumans() }}</p>
        @endif
    </div>

    {{-- Notices --}}
    @if($hasVoted)
        <div class="alert alert-warning py-2">You've already voted — live results below.</div>
    @elseif(!$poll->isOpen())
        <div class="alert alert-secondary py-2">This poll is closed. Final results below.</div>
    @endif
    @php
       $resultsById = collect($results['options'])->keyBy('id');
    @endphp
    {{-- Options --}}
    <div class="list-group mb-4">
        @foreach($poll->options as $option)
            @php
                $optResult = $resultsById[$option->id];
                $pct = $optResult['percentage'];
                $total = $optResult['votes_count'];
            @endphp
            <div class="list-group-item position-relative
                        {{ (!$hasVoted && $poll->isOpen()) ? 'list-group-item-action' : '' }}"
                 style="cursor: {{ (!$hasVoted && $poll->isOpen()) ? 'pointer' : 'default' }};"
                 data-option-id="{{ $option->id }}"
                 @if(!$hasVoted && $poll->isOpen()) x-on:click="vote({{ $option->id }})" @endif>

                {{-- progress fill --}}
                <div class="position-absolute top-0 start-0 h-100 bg-primary bg-opacity-10"
                     style="width: {{ $pct }}%; transition: width .6s; z-index:0;"
                     data-bar="{{ $option->id }}"></div>

                <div class="position-relative d-flex justify-content-between align-items-center"
                     style="z-index:1;">
                    <div class="d-flex align-items-center gap-2">
                        @if(!$hasVoted && $poll->isOpen())
                            <input class="form-check-input mt-0" type="radio"
                                   x-bind:checked="selected === {{ $option->id }}">
                        @endif
                        <span class="fw-medium">{{ $option->label }}</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold" data-pct="{{ $option->id }}">{{ $pct }}%</span>
                        <small class="text-muted" data-count="{{ $option->id }}">({{$total}})</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Vote button --}}
    @if(!$hasVoted && $poll->isOpen())
        <div class="text-center mb-4" id="vote-action">
            <button class="btn btn-primary px-4" x-on:click="submitVote()" x-bind:disabled="!selected || loading">
                <span x-show="!loading">Cast vote</span>
                <span x-show="loading">
                    <span class="spinner-border spinner-border-sm"></span> Submitting…
                </span>
            </button>
        </div>
    @endif

    {{-- Total --}}
    <p class="text-center text-muted small">
        <strong id="total-votes">{{ $poll->total_votes }}</strong> total votes · live
    </p>

    {{-- Toast --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast align-items-center text-bg-dark border-0" x-bind:class="toast ? 'show' : ''" x-cloak>
            <div class="d-flex">
                <div class="toast-body" x-text="toastMsg"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pollApp', () => ({
        selected: null,
        loading: false,
        toast: false,
        toastMsg: '',

        pollId:   {{ $poll->id }},
        hasVoted: @json($hasVoted),
        isOpen:   @json($poll->isOpen()),
        csrf:     document.querySelector('meta[name="csrf-token"]').content,

        init() {
            if (window.Echo) this.listenChannel();
            else window.addEventListener('echo-ready', () => this.listenChannel());
        },

        listenChannel() {
            window.Echo.channel(`poll.${this.pollId}`)
                .listen('.vote.cast', (e) => this.updateUI(e));
        },

        vote(id) {
            if (this.hasVoted || !this.isOpen) return;
            this.selected = id;
        },

        async submitVote() {
            if (!this.selected || this.loading) return;
            this.loading = true;
            try {
                const res = await fetch(`/polls/${this.pollId}/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ poll_option_id: this.selected }),
                });
                const data = await res.json();
                if (data.success) {
                    this.updateUI(data.data);
                    this.showToast('Vote recorded!');
                    document.getElementById('vote-action')?.remove();
                } else {
                    this.showToast(data.message || 'Could not record vote.');
                }
            } catch {
                this.showToast('Something went wrong.');
            } finally {
                this.loading = false;
            }
        },

        updateUI(data) {
            const t = document.getElementById('total-votes');
            if (t) t.textContent = data.total;
            data.options.forEach(opt => {
                const bar = document.querySelector(`[data-bar="${opt.id}"]`);
                const pct = document.querySelector(`[data-pct="${opt.id}"]`);
                const cnt = document.querySelector(`[data-count="${opt.id}"]`);
                if (bar) bar.style.width   = opt.percentage + '%';
                if (pct) pct.textContent   = opt.percentage + '%';
                if (cnt) cnt.textContent   = `(${opt.votes_count})`;
            });
        },

        showToast(msg) {
            this.toastMsg = msg;
            this.toast = true;
            setTimeout(() => this.toast = false, 3000);
        }
    }));
});
</script>
@endpush