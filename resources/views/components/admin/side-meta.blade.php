<div class="admin-side-meta">
    <span>{{ auth()->user()?->name ?? 'Sushako Admin' }}</span>
    <strong>{{ str(auth()->user()?->role ?? 'admin')->replace('_', ' ')->title() }}</strong>
    <small>{{ now()->format('D, d M Y · h:i A') }}</small>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit"><x-admin.nav-icon name="logout" /> Logout</button>
    </form>
</div>
