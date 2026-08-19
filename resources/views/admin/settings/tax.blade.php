<x-layouts.admin title="Tax Settings - Admin">
    <x-admin.shell eyebrow="Settings" title="GST management" subtitle="Manage GST slabs and product assignments.">
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                @if (session('status')) <div class="status-banner">{{ session('status') }}</div> @endif
                @if ($errors->any()) <div class="status-banner status-banner--error">{{ $errors->first() }}</div> @endif
                <p class="eyebrow">Tax Settings</p>
                <h1>GST slabs and product assignment</h1>
                <div class="admin-editor-grid">
                    <section class="admin-settings-card">
                        <h2>Create GST Slab</h2>
                        <form class="admin-edit-form" method="POST" action="{{ route('admin.settings.tax-slabs.store') }}">
                            @csrf
                            <label>Name<input name="name" value="{{ old('name') }}" placeholder="GST 18%" required></label>
                            <label>Rate %<input name="rate" type="number" min="0" max="28" step="0.01" value="{{ old('rate') }}" required></label>
                            <label class="admin-check-row"><input name="is_active" type="checkbox" value="1" checked> Active</label>
                            <button class="button button--secondary" type="submit">Add Slab</button>
                        </form>
                    </section>
                    <section class="admin-settings-card">
                        <h2>Available Slabs</h2>
                        @foreach ($taxSlabs as $slab)
                            <p class="admin-setting-row"><strong>{{ $slab->name }}</strong><span>{{ number_format($slab->rate, 2) }}% · {{ $slab->is_active ? 'Active' : 'Inactive' }}</span></p>
                        @endforeach
                    </section>
                </div>
                <form class="admin-edit-form admin-settings-form" method="POST" action="{{ route('admin.settings.tax.assignments.update') }}">
                    @csrf
                    @method('PUT')
                    <section class="admin-settings-card">
                        <h2>Category GST Slabs</h2>
                        <div class="admin-settings-table">
                            @foreach ($categories as $category)
                                <label>{{ $category->name }}
                                    <select name="categories[{{ $category->id }}][tax_slab_id]" required>
                                        <option value="">Select GST slab</option>
                                        @foreach ($taxSlabs as $slab)
                                            <option value="{{ $slab->id }}" @selected((int) old("categories.{$category->id}.tax_slab_id", $category->tax_slab_id) === $slab->id)>{{ $slab->name }} ({{ number_format($slab->rate, 2) }}%)</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                    </section>
                    <section class="admin-settings-card">
                        <h2>Product GST Overrides</h2>
                        <p>Leave blank to inherit from category. This keeps future tax changes clean.</p>
                        <div class="admin-settings-table">
                            @foreach ($products as $product)
                                <label>{{ $product->name }}
                                    <select name="products[{{ $product->id }}][tax_slab_id]">
                                        <option value="">Inherit category slab</option>
                                        @foreach ($taxSlabs as $slab)
                                            <option value="{{ $slab->id }}" @selected((int) old("products.{$product->id}.tax_slab_id", $product->tax_slab_id) === $slab->id)>{{ $slab->name }} ({{ number_format($slab->rate, 2) }}%)</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                    </section>
                    <button class="button button--primary" type="submit">Save Tax Assignments</button>
                </form>
            </section>
    </x-admin.shell>
</x-layouts.admin>
