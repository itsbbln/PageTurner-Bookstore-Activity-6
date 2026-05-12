<footer class="bg-matcha-900 text-white py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Grid Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Brand -->
            <div>
                <h3 class="text-lg font-semibold mb-4">
                    PageTurner Bookstore
                </h3>
                <p class="text-matcha-100">
                    Your destination for quality books at great prices.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">
                    Quick Links
                </h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('home') }}" class="text-matcha-100 hover:text-white">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.index') }}" class="text-matcha-100 hover:text-white">
                            Browse Books
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('categories.index') }}" class="text-matcha-100 hover:text-white">
                            Categories
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-lg font-semibold mb-4">
                    Contact
                </h3>
                <p class="text-matcha-100">
                    Email: support@pageturner.com
                </p>
                <p class="text-matcha-100">
                    Phone: (123) 456-7890
                </p>
            </div>

        </div>

        <!-- Bottom Section -->
        <div class="border-t border-white-700 mt-8 pt-8 text-center text-gray-400">
            <p>
                &copy; {{ date('Y') }} PageTurner Bookstore. All rights reserved.
            </p>
        </div>

    </div>
</footer>
