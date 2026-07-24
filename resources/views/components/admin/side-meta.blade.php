<div class="admin-side-meta">
    <span>{{ now()->format('D, d M Y') }}</span>
    <strong>{{ now()->format('h:i A') }}</strong>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</button>
    </form>
</div>
