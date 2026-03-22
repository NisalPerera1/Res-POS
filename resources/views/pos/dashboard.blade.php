<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Dashboard - Restaurant POS System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div id="app">
        <div class="min-h-screen">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-4 py-3 flex justify-between items-center">
                    <h1 class="text-xl font-bold text-gray-900">Restaurant POS</h1>
                    <div class="flex items-center space-x-4">
                        <span id="user-name" class="text-gray-600"></span>
                        <button onclick="logout()" class="text-red-600 hover:text-red-800">
                            Logout
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="p-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Tables Section -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold mb-4">Tables</h2>
                            <div id="tables-grid" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <!-- Tables will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                            <div class="space-y-3">
                                <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                    New Order
                                </button>
                                <button class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                                    Take Orders
                                </button>
                                <button class="w-full bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700">
                                    View Menu
                                </button>
                                <button class="w-full bg-orange-600 text-white py-2 px-4 rounded hover:bg-orange-700">
                                    Reports
                                </button>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="bg-white rounded-lg shadow p-6 mt-6">
                            <h2 class="text-lg font-semibold mb-4">Recent Orders</h2>
                            <div id="recent-orders" class="space-y-2">
                                <!-- Recent orders will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Check authentication
        const token = localStorage.getItem('token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        
        if (!token) {
            window.location.href = '/login';
            return;
        }

        // Display user info
        document.getElementById('user-name').textContent = user.name || 'User';

        // Logout function
        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        // Load tables
        async function loadTables() {
            try {
                const response = await fetch('/api/tables', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                if (response.ok) {
                    const tables = await response.json();
                    const tablesGrid = document.getElementById('tables-grid');
                    
                    tablesGrid.innerHTML = tables.map(table => `
                        <div class="bg-gray-50 border rounded-lg p-4 hover:bg-gray-100 cursor-pointer">
                            <div class="font-semibold">${table.name}</div>
                            <div class="text-sm text-gray-600">${table.section}</div>
                            <div class="text-sm">Capacity: ${table.capacity}</div>
                            <div class="mt-2">
                                <span class="inline-block px-2 py-1 text-xs rounded ${
                                    table.status === 'free' ? 'bg-green-100 text-green-800' : 
                                    table.status === 'occupied' ? 'bg-red-100 text-red-800' :
                                    table.status === 'reserved' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-gray-100 text-gray-800'
                                }">
                                    ${table.status || 'free'}
                                </span>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Failed to load tables:', error);
            }
        }

        // Load recent orders
        async function loadRecentOrders() {
            try {
                const response = await fetch('/api/orders?limit=5', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                if (response.ok) {
                    const orders = await response.json();
                    const recentOrders = document.getElementById('recent-orders');
                    
                    if (orders.data && orders.data.length > 0) {
                        recentOrders.innerHTML = orders.data.map(order => `
                            <div class="border-l-4 border-blue-500 pl-3 py-1">
                                <div class="font-medium text-sm">${order.order_number}</div>
                                <div class="text-xs text-gray-600">$${order.total}</div>
                            </div>
                        `).join('');
                    } else {
                        recentOrders.innerHTML = '<p class="text-gray-500 text-sm">No recent orders</p>';
                    }
                }
            } catch (error) {
                console.error('Failed to load orders:', error);
            }
        }

        // Initialize
        loadTables();
        loadRecentOrders();
    </script>
</body>
</html>
