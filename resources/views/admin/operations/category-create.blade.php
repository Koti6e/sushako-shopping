<x-layouts.admin title="Add Category - Admin">
    <x-admin.shell
        eyebrow="Catalog"
        title="Add Category"
        subtitle="Create a marketplace category or place it under an existing category."
    >
        <x-slot:actions>
            <x-admin.action
                :href="route('admin.categories.index')"
                tone="secondary"
                icon="fa-solid fa-layer-group"
            >
                Categories
            </x-admin.action>
        </x-slot:actions>

        <section class="admin-dashboard-panel">
            @if ($errors->any())
                <div class="status-banner status-banner--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <p class="eyebrow">Marketplace Taxonomy</p>

            <h1>Add category</h1>

            <p class="lede">
                Create a top-level category or add a subcategory beneath
                an existing category.
            </p>

            <form
                class="admin-edit-form"
                method="POST"
                action="{{ route('admin.categories.store') }}"
                enctype="multipart/form-data"
            >
                @csrf

                <label>
                    Parent Category

                    <select name="parent_id">
                        <option value="">
                            None — Top-level Category
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('parent_id') == $category->id)
                            >
                                {{ $category->name }}
                            </option>

                            @foreach ($category->children as $child)
                                <option
                                    value="{{ $child->id }}"
                                    @selected(old('parent_id') == $child->id)
                                >
                                    — {{ $child->name }}
                                </option>

                                @foreach ($child->children as $grandchild)
                                    <option
                                        value="{{ $grandchild->id }}"
                                        @selected(old('parent_id') == $grandchild->id)
                                    >
                                        —— {{ $grandchild->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        @endforeach
                    </select>

                    <small>
                        Select "None" for a main marketplace category.
                    </small>
                </label>

                <label>
                    Category Name

                    <input
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Example: EV Spare Parts"
                        required
                    >
                </label>

                <label>
                    GST Slab

                    <select name="tax_slab_id" required>
                        <option value="">
                            Select GST slab
                        </option>

                        @foreach ($taxSlabs as $slab)
                            <option
                                value="{{ $slab->id }}"
                                @selected(old('tax_slab_id') == $slab->id)
                            >
                                {{ $slab->name }}
                                ({{ number_format($slab->rate, 2) }}%)
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Slug

                    <input
                        name="slug"
                        value="{{ old('slug') }}"
                        placeholder="Auto-generated when blank"
                    >
                </label>

                <label>
                    Tagline

                    <input
                        name="tagline"
                        value="{{ old('tagline') }}"
                        placeholder="Category tagline"
                    >
                </label>

                <label>
                    Headline

                    <input
                        name="headline"
                        value="{{ old('headline') }}"
                        placeholder="Category headline"
                    >
                </label>

                <label>
                    Description

                    <textarea
                        name="description"
                        placeholder="Short category description"
                        required
                    >{{ old('description') }}</textarea>
                </label>

                <label>
                    Accent Colour

                    <input
                        name="accent"
                        value="{{ old('accent', '#2f6b4f') }}"
                    >
                </label>

                <label>
                    Sort Order

                    <input
                        type="number"
                        name="sort_order"
                        min="0"
                        max="9999"
                        value="{{ old('sort_order', 0) }}"
                    >
                </label>

                <label class="admin-checkbox">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', true))
                    >

                    Active
                </label>

                <label class="admin-checkbox">
                    <input
                        type="checkbox"
                        name="available_to_sellers"
                        value="1"
                        @checked(old('available_to_sellers', true))
                    >

                    Available to Sellers
                </label>

                <x-ui.file-upload
                    class="admin-field-wide"
                    name="image"
                    label="Upload category banner"
                    hint="JPG, PNG or WebP category image."
                    accept="image/jpeg,image/png,image/webp"
                />

                <div class="admin-form-actions admin-field-wide">
                    <button
                        class="button button--primary"
                        type="submit"
                    >
                        Save Category
                    </button>
                </div>
            </form>
        </section>
    </x-admin.shell>
</x-layouts.admin>