<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers · Swipewire Admin</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; color: #16252e; background: #f4f7f8; }
        * { box-sizing: border-box; } body { margin: 0; } main { max-width: 1120px; margin: 0 auto; padding: 48px 24px 72px; }
        .eyebrow { color: #16867e; font-size: .76rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; } h1 { margin: 7px 0; font-size: clamp(2rem, 5vw, 3.2rem); letter-spacing: -.05em; }
        .intro { margin: 0; color: #64727a; } .notice { margin: 24px 0; padding: 14px 16px; color: #08554f; border: 1px solid #a4ded7; background: #e7faf6; border-radius: 12px; }
        .table-wrap { margin-top: 28px; overflow-x: auto; border: 1px solid #dce5e7; background: white; border-radius: 16px; box-shadow: 0 14px 40px #1730400c; }
        table { width: 100%; min-width: 720px; border-collapse: collapse; } th, td { padding: 17px 18px; border-bottom: 1px solid #edf1f2; text-align: left; } th { color: #69777d; font-size: .72rem; letter-spacing: .1em; text-transform: uppercase; } tr:last-child td { border-bottom: 0; }
        .email { color: #66737a; } .pill { display: inline-block; padding: 5px 9px; border-radius: 999px; font-size: .75rem; font-weight: 800; } .on { color: #06685f; background: #daf5ed; } .off { color: #a54232; background: #fff0eb; } .admin { color: #704b00; background: #fff4cf; }
        button { padding: 9px 12px; border: 1px solid #ccdbdd; border-radius: 9px; color: #183039; background: #fff; cursor: pointer; font: inherit; font-weight: 750; } button:hover { border-color: #16867e; color: #08726b; } button.danger { color: #a54232; border-color: #f0beb2; }
        .pagination { display: flex; gap: 8px; align-items: center; margin-top: 24px; color: #64727a; } .pagination a { color: #08726b; font-weight: 800; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <div class="eyebrow">Swipewire administration</div>
    <h1>Customer access</h1>
    <p class="intro">Review accounts and control whether a customer can sign in, purchase, or download their library.</p>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <div class="table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Access</th><th>Marketing</th><th>Joined</th><th>Action</th></tr></thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong><br><span class="email">{{ $user->email }}</span></td>
                    <td><span class="pill {{ $user->is_active ? 'on' : 'off' }}">{{ $user->is_active ? 'Enabled' : 'Disabled' }}</span></td>
                    <td>{{ $user->marketing_consent ? 'Subscribed' : 'Not subscribed' }}</td>
                    <td>{{ $user->created_at->format('M j, Y') }}</td>
                    <td>
                        @if ($user->is_admin)
                            <span class="pill admin">Administrator</span>
                        @else
                            <form method="post" action="{{ route('admin.users.toggle-status', $user) }}">
                                @csrf @method('PATCH')
                                <button class="{{ $user->is_active ? 'danger' : '' }}" type="submit">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
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

    @if ($users->hasPages())
        <nav class="pagination" aria-label="Customer pages">
            @if ($users->onFirstPage()) <span>Previous</span> @else <a href="{{ $users->previousPageUrl() }}">Previous</a> @endif
            <span>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
            @if ($users->hasMorePages()) <a href="{{ $users->nextPageUrl() }}">Next</a> @else <span>Next</span> @endif
        </nav>
    @endif
</main>
</body>
</html>
