<nav class="bg-matcha-900 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- Left Side -->
            <div class="flex items-center">
                
                <!-- Logo -->
                <a href="{{ route('home') }}" class="text-xl font-bold">
                    PageTurner
                </a>

                <!-- Navigation Links -->
                <div class="hidden md:flex ml-10 space-x-4">
                    <a href="{{ route('home') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                        Home
                    </a>

                    <a href="{{ route('books.index') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                        Books
                    </a>

                    <a href="{{ route('categories.index') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                        Categories
                    </a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                                Dashboard
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                        Login
                    </a>

                    <a href="{{ route('register') }}" class="bg-matcha-300 text-matcha-900 px-4 py-2 rounded-md font-medium hover:bg-matcha-200">
                        Register
                    </a>
                @endguest

                @auth
                    @if(!auth()->user()->isAdmin())
                        <!-- Shopping Cart (Customer only) -->
                        <a href="{{ route('cart.index') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span id="cart-count" class="text-sm bg-matcha-700 px-2 py-1 rounded-full">
                                {{ count(session('cart', [])) }}
                            </span>
                        </a>

                        <a href="{{ route('orders.index') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                            My Orders
                        </a>

                        <a href="{{ route('profile.edit') }}" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:bg-matcha-800 px-3 py-2 rounded-md">
                                Logout
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <!-- Admin Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center hover:bg-matcha-800 px-3 py-2 rounded-md text-yellow-300 font-semibold transition duration-150 ease-in-out">
                                <span>Admin</span>
                                <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
                                 style="display: none;">
                                
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">Profile</a>
                                 
                                 <div class="border-t border-gray-200"></div>
                                 
                                 <a href="{{ route('admin.books.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">Manage Books</a>
                                 <a href="{{ route('admin.categories.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">Manage Categories</a>
                                 <a href="{{ route('admin.orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">Manage Orders</a>
                                 <a href="{{ route('admin.data-management.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">Data Management</a>
                                 <a href="{{ route('admin.audit.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">Audit Logs</a>
                                 
                                 <div class="border-t border-gray-200"></div>
                                 
                                 <form method="POST" action="{{ route('logout') }}">
                                     @csrf
                                     <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-matcha-800 hover:text-white transition">
                                         Logout
                                     </button>
                                 </form>
                            </div>
                        </div>
                    @endif

                    <span class="text-matcha-100 hidden lg:inline">
                        {{ auth()->user()->name }}
                    </span>
                @endauth
            </div>

        </div>
    </div>
</nav>
