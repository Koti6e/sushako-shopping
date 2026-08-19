<x-layouts.customer title="My Account - Sushako Shopping">
    <section class="site-shell account-screen">
        <div class="account-layout">
            <aside class="account-sidebar">
                <div class="account-profile-card">
                    <div class="account-avatar">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}">
                        @else
                            <span>{{ str($user->name)->substr(0, 1)->upper() }}</span>
                        @endif
                    </div>
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->email }}</span>
                    <div class="account-progress" aria-label="Profile completion {{ $profileCompletion }}%">
                        <span style="width: {{ $profileCompletion }}%"></span>
                    </div>
                    <small>{{ $profileCompletion }}% complete</small>
                </div>

                <nav class="account-side-nav" aria-label="Customer account navigation">
                    <a href="#profile"><i class="fa-solid fa-user" aria-hidden="true"></i> Profile</a>
                    <a href="#addresses"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Addresses</a>
                    <a href="#orders"><i class="fa-solid fa-box-open" aria-hidden="true"></i> Orders</a>
                    <a href="#tickets"><i class="fa-solid fa-headset" aria-hidden="true"></i> Tickets</a>
                    <a href="#security"><i class="fa-solid fa-lock" aria-hidden="true"></i> Security</a>
                </nav>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="account-side-logout" type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</button>
                </form>
            </aside>

            <main class="account-main">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Customer Account</p>
                        <h1>Your Sushako account</h1>
                        <p class="lede">Manage profile, saved delivery addresses, orders and support from one place.</p>
                    </div>
                </div>

                <div class="account-summary-grid">
                    <article>
                        <span>Email</span>
                        <strong>{{ $user->email }}</strong>
                    </article>
                    <article>
                        <span>Mobile</span>
                        <strong>{{ $user->phone ?: 'Not added' }}</strong>
                    </article>
                    <article>
                        <span>WhatsApp</span>
                        <strong>{{ $user->phone_is_whatsapp ? 'Opted In' : 'Not Confirmed' }}</strong>
                    </article>
                    <article>
                        <span>Addresses</span>
                        <strong>{{ $addresses->count() }}</strong>
                    </article>
                    <article>
                        <span>Orders</span>
                        <strong>{{ $orders->count() }}</strong>
                    </article>
                    <article>
                        <span>Completion</span>
                        <strong>{{ $profileCompletion }}%</strong>
                    </article>
                </div>

                <section class="account-panel account-panel--wide" id="profile">
                    <p class="eyebrow">Personal Information</p>
                    <h2>Profile</h2>
                    @if (session('profile_status'))
                        <div class="status-banner">{{ session('profile_status') }}</div>
                    @endif
                    @if ($errors->has('name') || $errors->has('phone') || $errors->has('avatar'))
                        <div class="status-banner">{{ $errors->first('name') ?: $errors->first('phone') ?: $errors->first('avatar') }}</div>
                    @endif

                    <div class="profile-completion-list">
                        @foreach ([
                            'name' => 'Full name',
                            'email' => 'Email address',
                            'mobile' => 'Mobile number',
                            'whatsapp' => 'Mobile confirmed as WhatsApp number',
                            'profile_photo' => 'Profile picture',
                            'saved_address' => 'Default delivery address',
                        ] as $key => $label)
                            <span class="@if($profileChecklist[$key]) is-done @endif">
                                <i class="fa-solid fa-check" aria-hidden="true"></i>{{ $label }}
                            </span>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('account.profile.update') }}" class="account-form account-profile-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <label for="account-avatar">Profile Picture</label>
                        <input id="account-avatar" name="avatar" type="file" accept="image/*">
                        <label for="account-name">Full Name</label>
                        <input id="account-name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        <label for="account-phone">Mobile Number</label>
                        <input id="account-phone" name="phone" value="{{ old('phone', $user->phone) }}" required autocomplete="tel">
                        <label class="policy-agreement">
                            <input name="phone_is_whatsapp" type="checkbox" value="1" @checked(old('phone_is_whatsapp', $user->phone_is_whatsapp))>
                            <span>This mobile number is also my WhatsApp number for order updates and support.</span>
                        </label>
                        <button class="button button--primary" type="submit">Update Profile</button>
                    </form>
                </section>

                <section class="account-panel account-panel--wide" id="addresses">
                    <p class="eyebrow">Saved Addresses</p>
                    <h2>Delivery addresses</h2>
                    @if (session('address_status'))
                        <div class="status-banner">{{ session('address_status') }}</div>
                    @endif
                    @if ($errors->has('label') || $errors->has('address_line_1') || $errors->has('city') || $errors->has('pincode'))
                        <div class="status-banner">{{ $errors->first('label') ?: $errors->first('address_line_1') ?: $errors->first('city') ?: $errors->first('pincode') }}</div>
                    @endif
                    <div class="saved-address-list">
                        @forelse ($addresses as $address)
                            <article class="saved-address-card">
                                <div>
                                    <strong>{{ $address->label }} @if ($address->is_default)<span>Default</span>@endif</strong>
                                    <p>{{ $address->recipient_name }} · {{ $address->phone }}</p>
                                    <p>{{ $address->address_line_1 }}{{ $address->address_line_2 ? ', '.$address->address_line_2 : '' }}, {{ $address->city }} - {{ $address->pincode }}</p>
                                    @if ($address->landmark)
                                        <p>Landmark: {{ $address->landmark }}</p>
                                    @endif
                                    @if ($address->delivery_location_url)
                                        <p>Google Maps link saved</p>
                                    @endif
                                </div>
                                <div class="saved-address-card__actions">
                                    @unless ($address->is_default)
                                        <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                            @csrf
                                            @method('PUT')
                                            <button class="button button--ghost" type="submit">Make Default</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button--ghost" type="submit">Remove</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p>Save your delivery address once and use it quickly during checkout.</p>
                        @endforelse
                    </div>
                    <form method="POST" action="{{ route('account.addresses.store') }}" class="account-form address-form">
                        @csrf
                        <label for="address-label">Label</label>
                        <input id="address-label" name="label" value="{{ old('label', 'Home') }}" required>
                        <label for="address-recipient">Receiver Name</label>
                        <input id="address-recipient" name="recipient_name" value="{{ old('recipient_name', $user->name) }}" required autocomplete="name">
                        <label for="address-phone">Mobile Number</label>
                        <input id="address-phone" name="phone" value="{{ old('phone', $user->phone) }}" required autocomplete="tel">
                        <label for="saved-address-line-1">Address Line 1</label>
                        <input id="saved-address-line-1" name="address_line_1" value="{{ old('address_line_1') }}" required autocomplete="address-line1">
                        <label for="saved-address-line-2">Address Line 2</label>
                        <input id="saved-address-line-2" name="address_line_2" value="{{ old('address_line_2') }}" autocomplete="address-line2">
                        <label for="saved-address-city">City</label>
                        <input id="saved-address-city" name="city" value="{{ old('city') }}" required autocomplete="address-level2">
                        <label for="saved-address-pincode">Pincode</label>
                        <input id="saved-address-pincode" name="pincode" value="{{ old('pincode') }}" required autocomplete="postal-code">
                        <label for="saved-address-landmark">Landmark <span class="field-hint">Optional</span></label>
                        <input id="saved-address-landmark" name="landmark" value="{{ old('landmark') }}">
                        <label for="saved-address-map">Google Maps Link <span class="field-hint">Optional</span></label>
                        <input id="saved-address-map" name="delivery_location_url" type="url" value="{{ old('delivery_location_url') }}" placeholder="https://maps.google.com/...">
                        <label class="policy-agreement">
                            <input name="is_default" type="checkbox" value="1" @checked(! $addresses->count())>
                            <span>Use as default checkout address</span>
                        </label>
                        <button class="button button--primary" type="submit">Save Address</button>
                    </form>
                </section>

                <div class="account-grid">
                    <section class="account-panel" id="orders">
                        <p class="eyebrow">Orders</p>
                        <h2>{{ $orders->count() }} order{{ $orders->count() === 1 ? '' : 's' }}</h2>
                        @forelse ($orders as $order)
                            <article class="mini-cart-line">
                                <div>
                                    <h3>{{ $order->order_number }}</h3>
                                    <p>{{ str($order->status)->replace('_', ' ')->title() }} · {{ ucfirst($order->payment_status) }} · {{ $order->placed_at?->format('d M Y') ?? $order->created_at->format('d M Y') }}</p>
                                    <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                                </div>
                                @if ($order->status === 'payment_pending')
                                    <a class="button button--primary" href="{{ route('order.payment', $order->order_number) }}">Complete Payment</a>
                                @endif
                            </article>
                        @empty
                            <p>Orders placed from your Sushako account will appear here.</p>
                        @endforelse
                    </section>

                    <section class="account-panel" id="tickets">
                        <p class="eyebrow">Support</p>
                        <h2>Tickets</h2>
                        <p>Support tickets will appear here after the ticket module is enabled. For now, use WhatsApp support for order help.</p>
                        <a class="button button--secondary" href="https://wa.me/{{ config('services.whatsapp.support_number') }}" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp Support
                        </a>
                    </section>
                </div>

                <section class="account-panel account-panel--wide" id="security">
                    <p class="eyebrow">Security</p>
                    <h2>Change Password</h2>
                    @if (session('password_status'))
                        <div class="status-banner">{{ session('password_status') }}</div>
                    @endif
                    @if ($errors->has('current_password') || $errors->has('password'))
                        <div class="status-banner">{{ $errors->first('current_password') ?: $errors->first('password') }}</div>
                    @endif
                    <form method="POST" action="{{ route('account.password.update') }}" class="account-form account-form--security">
                        @csrf
                        @method('PUT')
                        <label for="current-password">Current Password</label>
                        <input id="current-password" name="current_password" type="password" required autocomplete="current-password">
                        <label for="new-password">New Password</label>
                        <input id="new-password" name="password" type="password" required autocomplete="new-password">
                        <label for="new-password-confirmation">Confirm New Password</label>
                        <input id="new-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                        <button class="button button--primary" type="submit">Update Password</button>
                    </form>
                </section>
            </main>
        </div>
    </section>
</x-layouts.customer>
