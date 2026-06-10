<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Module Authentification</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0d2353;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: sans-serif;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 35px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        h3 {
            color: #0d2353;
            font-weight: bold;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 0.95rem;
            color: #333;
        }

        .form-control {
            background-color: #ffffff !important;
            border: 1px solid #0a2957;
            border-radius: 8px;
            padding: 10px 12px;
            color: #333;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: none;
        }

        .btn-custom {
            background-color: #2563eb;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            color: white;
            transition: 0.2s;
        }

        .btn-custom:hover {
            background-color: #1d4ed8;
        }

        .link-custom {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .link-custom:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-4">Connexion</h3>

    <form action="{{ route('login.traiter') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Adresse e-mail</label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email"
                value="{{ old('email') }}"
                required
            >

            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Mot de passe</label>
            <input
                type="password"
                class="form-control"
                name="password"
                required
            >
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('inscription.afficher') }}" class="link-custom">
                Pas encore inscrit ?
            </a>

            <button type="submit" class="btn text-white" style="background-color: #032f75d7; width: 100%; padding: 10px; font-weight: bold;">Se connecter
            </button>
        </div>
    </form>
</div>

</body>
</html>