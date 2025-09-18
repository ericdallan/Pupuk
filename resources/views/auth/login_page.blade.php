<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6B7280 0%, #D1D5DB 100%);
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
        }

        .message-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: 90%;
            max-width: 400px;
        }

        .logo-container img {
            transition: transform 0.3s ease;
        }

        .logo-container img:hover {
            transform: scale(1.05);
        }

        .input-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-field:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #3B82F6, #60A5FA);
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #2563EB, #3B82F6);
            transform: translateY(-2px);
        }

        .alert {
            animation: slideIn 0.5s ease;
        }

        .password-toggle {
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .password-toggle:hover {
            opacity: 0.7;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 640px) {
            .login-container {
                padding: 1.5rem;
            }

            .message-container {
                width: 95%;
            }
        }
    </style>
</head>

<body>
    <div class="message-container">
        @if (session('success'))
            <div class="alert bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('failed'))
            <div class="alert bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-3 rounded-lg">
                {{ session('failed') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif (session('message'))
            <div class="alert bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-3 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        @if (session('url.intended') && !session('success') && !session('failed') && !$errors->any())
            <div class="alert bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-3 rounded-lg">
                Anda Harus Login Terlebih Dahulu, Silahkan Login Akun.
            </div>
        @endif
    </div>

    <div class="login-container w-full max-w-sm p-8 flex flex-col items-center">
        <div class="logo-container mb-6">
            <img src="{{ asset('logo/LogoInniDigi.png') }}" alt="DeveloperLogo" class="max-w-[150px] max-h-[150px]">
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Admin Login</h2>
        <form method="POST" action="{{ route('login.post') }}" class="w-full">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input type="email" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg"
                    id="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg"
                        id="password" name="password">
                    <span class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2">
                        <svg id="eye-icon" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                    </span>
                </div>
                @error('password')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-primary w-full py-2 px-4 rounded-lg text-white font-medium">Login</button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        let isPasswordVisible = false;

        eyeIcon.addEventListener('click', () => {
            isPasswordVisible = !isPasswordVisible;
            passwordInput.type = isPasswordVisible ? 'text' : 'password';
            eyeIcon.innerHTML = isPasswordVisible ?
                `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.975 9.975 0 011.689-2.353m2.474-.297l-3.293 3.293m0 0l3.293 3.293m-3.293-3.293L6.586 9.414M12 5c4.478 0 8.268 2.943 9.542 7a9.975 9.975 0 01-1.689 2.353m-2.474.297l3.293-3.293m0 0l-3.293-3.293m3.293 3.293L17.414 14.586"/>` :
                `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
        });
    </script>
</body>

</html>
