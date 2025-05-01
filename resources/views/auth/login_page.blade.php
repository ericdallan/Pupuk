<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        .message-container {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            width: 90%;
            max-width: 400px;
        }

        .login-container {
            width: 90%;
            max-width: 350px;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Center items horizontally */
        }

        .logo-container {
            margin-bottom: 20px;
            /* Add space below the logo */
            display: flex;
            /* Enable flexbox for centering content */
            justify-content: center;
            /* Center content horizontally */
            width: 100%;
            /* Ensure the container takes full width */
        }

        .logo-container img {
            max-width: 150px;
            max-height: 150px;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 20px;
            }

            .message-container {
                width: 95%;
            }
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="message-container">
        @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
        @endif

        @if (session('failed'))
        <div class="alert alert-danger mb-3">
            {{ session('failed') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @elseif (session('message'))
        <div class="alert alert-danger mb-3">
            {{ session('message') }}
        </div>
        @endif
    </div>

    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('logo/LogoInni.png') }}" alt="DeveloperLogo" style="max-width: 150px; max-height: 150px;">
        </div>
        <h2 class="text-center mb-4">Admin Login</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                @error('email')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                @error('password')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>