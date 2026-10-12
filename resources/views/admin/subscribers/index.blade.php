@extends('layouts.admin')

@section('title', 'Subscribers')

@section('content')
    <div class="section-head">
        <div>
            <span class="eyebrow">Newsletter</span>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);margin-top:6px">Newsletter subscribers</h1>
            <p>Customers appear here only after they explicitly opt in while creating their account.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Email address</th><th>Status</th><th>Opted in</th></tr></thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                    <tr>
                        <td>{{ $subscriber->email }}</td>
                        <td><span class="pill {{ $subscriber->is_subscribed ? 'pill-live' : 'pill-off' }}">{{ $subscriber->is_subscribed ? 'Subscribed' : 'Unsubscribed' }}</span></td>
                        <td>{{ $subscriber->subscribed_at?->format('j M Y') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No newsletter subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
