@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div class="section-head">
        <div>
            <span class="eyebrow">Accounts</span>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);margin-top:6px">Customer access</h1>
            <p>Review accounts and control whether a customer can sign in, purchase or download their library.</p>
        </div>
    </div>

    @if(session('status'))
        <p class="notice" style="margin-bottom:20px">{{ session('status') }}</p>
    @endif

    <div class="table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Access</th><th>Marketing</th><th>Joined</th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong><br><span class="muted" style="font-size:.875rem">{{ $user->email }}</span></td>
                        <td><span class="pill {{ $user->is_active ? 'pill-live' : 'pill-off' }}">{{ $user->is_active ? 'Enabled' : 'Disabled' }}</span></td>
                        <td>{{ $user->marketing_consent ? 'Subscribed' : 'Not subscribed' }}</td>
                        <td>{{ $user->created_at->format('j M Y') }}</td>
                        <td style="text-align:right">
                            @if($user->is_admin)
                                <span class="pill pill-admin">Administrator</span>
                            @else
                                <form method="post" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="button button-small {{ $user->is_active ? 'button-danger' : 'button-ghost' }}" type="submit">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No customer accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <nav class="pagination" aria-label="Customer pages">
            @if($users->onFirstPage())
                <span class="muted">Previous</span>
            @else
                <a href="{{ $users->previousPageUrl() }}">Previous</a>
            @endif
            <span>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}">Next</a>
            @else
                <span class="muted">Next</span>
            @endif
        </nav>
    @endif
@endsection
