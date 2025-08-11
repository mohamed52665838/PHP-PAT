<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-.4-2h0m6 16h.01M19 21h.01"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">Panier ID: <span class="font-semibold text-blue-600">#5</span></span>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start xl:gap-x-16">
            <!-- Cart Items -->
            <section class="lg:col-span-7">
                <div class="bg-white rounded-2xl shadow-xl p-6 animate-fade-in">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Your Items</h2>
                    
                    <div class="flow-root">
                        <ul class="divide-y divide-gray-200">
                            <!-- Cart Item 1 -->
                            <li class="py-6 flex animate-slide-up">
                                <div class="flex-shrink-0 w-24 h-24 border border-gray-200 rounded-xl overflow-hidden bg-gradient-to-br from-blue-100 to-purple-100">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex flex-col flex-1">
                                    <div class="flex justify-between">
                                        <div class="pr-6">
                                            <h3 class="text-lg font-medium text-gray-900 hover:text-blue-600 transition-colors cursor-pointer">
                                                Product #23
                                            </h3>
                                            <div class="mt-1 flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    In Stock
                                                </span>
                                                <span class="text-sm text-gray-500">SKU: PRD-23</span>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600">Premium quality product with advanced features</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900">$49.99</p>
                                            <p class="text-sm text-gray-500 line-through">$69.99</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-between items-center">
                                        <div class="flex items-center border border-gray-300 rounded-lg">
                                            <button class="p-2 hover:bg-gray-100 transition-colors rounded-l-lg" onclick="updateQuantity(23, -1)">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                </svg>
                                            </button>
                                            <span class="px-4 py-2 bg-gray-50 text-center min-w-[3rem] font-medium" id="qty-23">32</span>
                                            <button class="p-2 hover:bg-gray-100 transition-colors rounded-r-lg" onclick="updateQuantity(23, 1)">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4">
                                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                                                Save for later
                                            </button>
                                            <button class="text-red-600 hover:text-red-800 transition-colors" onclick="removeItem(23)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            
                            <!-- Cart Item 2 -->
                            <li class="py-6 flex animate-slide-up" style="animation-delay: 0.1s">
                                <div class="flex-shrink-0 w-24 h-24 border border-gray-200 rounded-xl overflow-hidden bg-gradient-to-br from-green-100 to-blue-100">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex flex-col flex-1">
                                    <div class="flex justify-between">
                                        <div class="pr-6">
                                            <h3 class="text-lg font-medium text-gray-900 hover:text-blue-600 transition-colors cursor-pointer">
                                                Product #16
                                            </h3>
                                            <div class="mt-1 flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    In Stock
                                                </span>
                                                <span class="text-sm text-gray-500">SKU: PRD-16</span>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600">High-performance device with latest technology</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900">$89.99</p>
                                            <p class="text-sm text-gray-500 line-through">$119.99</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-between items-center">
                                        <div class="flex items-center border border-gray-300 rounded-lg">
                                            <button class="p-2 hover:bg-gray-100 transition-colors rounded-l-lg" onclick="updateQuantity(16, -1)">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                </svg>
                                            </button>
                                            <span class="px-4 py-2 bg-gray-50 text-center min-w-[3rem] font-medium" id="qty-16">32</span>
                                            <button class="p-2 hover:bg-gray-100 transition-colors rounded-r-lg" onclick="updateQuantity(16, 1)">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4">
                                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                                                Save for later
                                            </button>
                                            <button class="text-red-600 hover:text-red-800 transition-colors" onclick="removeItem(16)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Continue Shopping -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <button class="flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Continue Shopping
                        </button>
                    </div>
                </div>
            </section>

            <!-- Order Summary -->
            <section class="mt-8 lg:mt-0 lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8 animate-slide-up" style="animation-delay: 0.2s">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Summary</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal (64 items)</span>
                            <span class="font-medium">$4,479.36</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-green-600">FREE</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">$447.94</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Discount</span>
                            <span class="font-medium text-red-600">-$200.00</span>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span class="text-blue-600">$4,727.30</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Promo Code -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
                        <div class="flex space-x-2">
                            <input type="text" placeholder="Enter code" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                Apply
                            </button>
                        </div>
                    </div>
                    
                    <!-- Checkout Button -->
                    <button class="w-full mt-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl">
                        Proceed to Checkout
                    </button>
                    
                    <!-- Security Badge -->
                    <div class="mt-4 flex items-center justify-center space-x-2 text-sm text-gray-500">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Secure 256-bit SSL encryption</span>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-700 mb-3">We accept</p>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-6 bg-gradient-to-r from-blue-600 to-blue-800 rounded text-white text-xs flex items-center justify-center font-bold">VISA</div>
                            <div class="w-10 h-6 bg-gradient-to-r from-red-500 to-yellow-500 rounded text-white text-xs flex items-center justify-center font-bold">MC</div>
                            <div class="w-10 h-6 bg-gradient-to-r from-blue-500 to-blue-700 rounded text-white text-xs flex items-center justify-center font-bold">AMEX</div>
                            <div class="w-10 h-6 bg-gradient-to-r from-blue-600 to-blue-800 rounded text-white text-xs flex items-center justify-center font-bold">PAY</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- JavaScript for interactivity -->
    <script>
        function updateQuantity(productId, change) {
            const qtyElement = document.getElementById(`qty-${productId}`);
            let currentQty = parseInt(qtyElement.textContent);
            let newQty = currentQty + change;
            
            if (newQty < 1) newQty = 1;
            if (newQty > 99) newQty = 99;
            
            qtyElement.textContent = newQty;
            
            // Add visual feedback
            qtyElement.classList.add('text-blue-600', 'font-bold');
            setTimeout(() => {
                qtyElement.classList.remove('text-blue-600', 'font-bold');
            }, 300);
            
            updateTotals();
        }
        
        function removeItem(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                // In a real application, you would make an AJAX call here
                alert(`Product ${productId} would be removed from cart`);
            }
        }
        
        function updateTotals() {
            // This would calculate actual totals based on quantities and prices
            // For demo purposes, we'll just show a brief loading state
            const totalElement = document.querySelector('.text-blue-600');
            if (totalElement && totalElement.textContent.includes('$')) {
                totalElement.classList.add('animate-pulse');
                setTimeout(() => {
                    totalElement.classList.remove('animate-pulse');
                }, 500);
            }
        }
        
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>