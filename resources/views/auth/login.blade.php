<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div id="app">
        <div class="min-h-screen flex items-center justify-center">
            <div class="bg-white p-8 rounded-lg shadow-lg w-96">
                <h1 class="text-2xl font-bold text-center mb-6">POS Login</h1>
                <p class="text-gray-600 text-center mb-6">Enter your PIN to login</p>
                
                <div class="space-y-4">
                    <input 
                        type="password" 
                        id="pin-input"
                        placeholder="Enter PIN"
                        class="w-full px-4 py-3 border rounded-lg text-center text-2xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="4"
                    >
                    
                    <div id="error-message" class="text-red-500 text-center hidden">
                        Invalid PIN. Please try again.
                    </div>
                    
                    <button 
                        onclick="login()"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition"
                    >
                        Login
                    </button>
                </div>
                
                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Test PINs:</p>
                    <p>Admin: 1234 | Cashier: 2222 | Waiter: 3333</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function login() {
            const pin = document.getElementById('pin-input').value;
            const errorDiv = document.getElementById('error-message');
            
            try {
                const response = await fetch('/api/login-pin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ pin })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    window.location.href = '/dashboard';
                } else {
                    errorDiv.classList.remove('hidden');
                    setTimeout(() => errorDiv.classList.add('hidden'), 3000);
                }
            } catch (error) {
                errorDiv.textContent = 'Login failed. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        }
        
        document.getElementById('pin-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') login();
        });
    </script>
</body>
</html>
