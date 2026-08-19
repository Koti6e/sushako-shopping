<x-layouts.admin title="Seller Banners - Admin">
    <x-admin.shell title="Promotional Banners">
        <x-slot:actions>
            <x-admin.action :href="route('admin.sellers.index')" tone="secondary" icon="fa-solid fa-store">Sellers</x-admin.action>
        </x-slot:actions>

        <section class="customer-admin admin-banner-workspace">
            @if ($errors->any())
                <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
            @endif

            <form class="customer-filter-card admin-banner-form" method="POST" action="{{ route('admin.sellers.banners.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="customer-filter-card__head">
                    <div>
                        <p class="eyebrow">Banner Setup</p>
                        <h2>Create a seller promotion</h2>
                    </div>
                </div>

                <div class="customer-filter-grid">
                    <label>Title<input name="title" value="{{ old('title') }}" required></label>
                    <label>Description<input name="description" value="{{ old('description') }}" required></label>
                    <x-ui.file-upload class="admin-field-wide" name="desktop_image" label="Desktop image" hint="Wide banner image. PNG, JPG or WebP." accept="image/*" required />
                    <x-ui.file-upload class="admin-field-wide" name="mobile_image" label="Mobile image" hint="Tall mobile-friendly image. PNG, JPG or WebP." accept="image/*" required />
                    <label>CTA Label<input name="cta_label" value="{{ old('cta_label') }}" required></label>
                    <label>CTA URL<input name="cta_url" value="{{ old('cta_url', route('seller.login')) }}" required></label>
                    <label>Display Order<input type="number" name="display_order" value="{{ old('display_order', 0) }}" required></label>
                    <label>Start Date<input type="date" name="starts_at" value="{{ old('starts_at') }}"></label>
                    <label>End Date<input type="date" name="ends_at" value="{{ old('ends_at') }}"></label>
                    <label class="admin-toggle-field"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> <span>Active</span></label>
                </div>

                <div class="customer-filter-card__actions">
                    <x-admin.action type="submit" tone="primary" icon="fa-solid fa-floppy-disk">Save Banner</x-admin.action>
                </div>
            </form>

            <div class="customer-card-grid">
                @forelse($banners as $banner)
                    <article class="customer-card admin-banner-card">
                        <img src="{{ asset('storage/'.$banner->desktop_image_path) }}" alt="{{ $banner->title }}">
                        <h2>{{ $banner->title }}</h2>
                        <p>{{ $banner->description }}</p>
                        <span>{{ $banner->is_active ? 'Active' : 'Inactive' }}</span>
                    </article>
                @empty
                    <div class="customer-empty-state">
                        <h2>No banners yet</h2>
                        <p>Create the first seller banner when a promotion is ready.</p>
                    </div>
                @endforelse
            </div>
            {{ $banners->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
