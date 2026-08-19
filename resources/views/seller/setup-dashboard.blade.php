<x-layouts.seller title="Seller Setup Dashboard">
    <section class="seller-content max-w-5xl mx-auto py-8 px-4">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Store Setup Dashboard</h1>
            <p class="text-gray-600">Complete the mandatory steps below to activate your store and start selling.</p>
        </header>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-700 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                @php
                    $mappedItems = [
                        'business_identity' => ['icon' => 'fa-store', 'title' => 'Store Basics', 'description' => 'Business name, description, and logo.', 'step' => 'business'],
                        'business_contact' => ['icon' => 'fa-address-book', 'title' => 'Contact Details', 'description' => 'Phone and email address.', 'step' => 'business'],
                        'store_location' => ['icon' => 'fa-map-marker-alt', 'title' => 'Business Address', 'description' => 'Where your business is located.', 'step' => 'business'],
                        'delivery_configuration' => ['icon' => 'fa-truck-fast', 'title' => 'Delivery Setup', 'description' => 'Delivery radius, fees, and pickup location.', 'step' => 'delivery'],
                        'storefront_category' => ['icon' => 'fa-tags', 'title' => 'Categories', 'description' => 'Select your store categories.', 'step' => 'business'],
                        'first_product' => ['icon' => 'fa-box', 'title' => 'First Product', 'description' => 'Add at least one product to your catalog.', 'url' => route('seller.products.create')],
                        'plan_activation' => ['icon' => 'fa-credit-card', 'title' => 'Choose Plan', 'description' => 'Select a seller plan to activate your store.', 'step' => 'plan'],
                    ];
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-800 text-lg">Mandatory Requirements</h2>
                        <span class="text-sm font-medium px-2.5 py-0.5 rounded-full {{ $setupChecklist['publish_eligible'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $setupChecklist['percentage'] }}% Complete
                        </span>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        @foreach($setupChecklist['items'] as $item)
                            @if($item['mandatory'])
                                @php
                                    $map = $mappedItems[$item['key']] ?? ['icon' => 'fa-check-circle', 'title' => $item['label'], 'description' => 'Required step.', 'url' => $item['url']];
                                    $actionUrl = isset($map['step']) ? route('seller.onboarding.step', $map['step']) : ($map['url'] ?? $item['url']);
                                @endphp
                                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors hover:bg-gray-50/50">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-1">
                                            @if($item['complete'])
                                                <i class="fa-solid fa-circle-check text-2xl text-green-500"></i>
                                            @else
                                                <i class="fa-regular fa-circle text-2xl text-gray-300"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                                {{ $map['title'] }}
                                            </h3>
                                            <p class="text-sm text-gray-500 mt-1">{{ $map['description'] }}</p>
                                        </div>
                                    </div>
                                    <div class="sm:flex-shrink-0 ml-10 sm:ml-0">
                                        <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $item['complete'] ? 'text-gray-600 bg-gray-100 hover:bg-gray-200' : 'text-white bg-blue-600 hover:bg-blue-700 shadow-sm' }}">
                                            {{ $item['complete'] ? 'Edit details' : 'Complete step' }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="font-semibold text-gray-800 text-lg">Optional Improvements</h2>
                        <p class="text-sm text-gray-500 mt-1">Enhance your store's appeal and operations.</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($setupChecklist['items'] as $item)
                            @if(!$item['mandatory'])
                                <div class="px-6 py-4 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        @if($item['complete'])
                                            <i class="fa-solid fa-check text-green-500"></i>
                                        @else
                                            <i class="fa-solid fa-minus text-gray-300"></i>
                                        @endif
                                        <span class="text-sm font-medium {{ $item['complete'] ? 'text-gray-900' : 'text-gray-500' }}">{{ $item['label'] }}</span>
                                    </div>
                                    @if(!$item['complete'] && $vendor->onboarding_status === 'complete')
                                        <a href="{{ $item['url'] }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Add</a>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                    <div class="mb-6">
                        <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-rocket text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Go Live</h2>
                        <p class="text-sm text-gray-500 mt-2">
                            Once all mandatory steps are completed, submit your store for review to activate your seller account.
                        </p>
                    </div>
                    
                    @if($vendor->approval_status === \App\Models\Vendor::APPROVAL_PENDING_APPROVAL)
                        <div class="p-4 rounded-lg bg-blue-50 border border-blue-100 text-blue-800 text-sm font-medium mb-4 flex items-start gap-3">
                            <i class="fa-solid fa-clock mt-0.5"></i>
                            <div>
                                <p class="font-semibold">Review Pending</p>
                                <p class="text-blue-600 mt-1 font-normal">Your store is currently under review by our admin team. We will notify you once approved.</p>
                            </div>
                        </div>
                    @else
                        @if($setupChecklist['publish_eligible'])
                            <form action="{{ route('seller.onboarding.submit-review') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 shadow-sm transition-colors">
                                    <i class="fa-solid fa-paper-plane mr-2"></i> Submit for Review
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full inline-flex items-center justify-center px-4 py-3 text-sm font-medium rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                <i class="fa-solid fa-lock mr-2"></i> Submit for Review
                            </button>
                            <p class="text-xs text-center text-gray-500 mt-3">Complete all mandatory steps to unlock.</p>
                        @endif
                    @endif
                    
                    @if($vendor->dashboard_access_enabled)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="{{ route('seller.dashboard') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                                Go to Main Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.seller>
