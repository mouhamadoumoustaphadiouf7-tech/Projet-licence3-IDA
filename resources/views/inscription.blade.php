<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>

    <style>
        body {
            background-color: #0d2353;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: sans-serif;
        }

        .register-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            color: #0d2353;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            background-color: #ffffff;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
        }

        .btn-register {
            width: 100%;
            background-color: #061f57;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn-register:hover {
            background-color: #1d4ed8;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
        }

        .footer-links a {
            color: #2563eb;
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .success-message {
            background-color: #d1fae5;
            color: #065f46;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="register-card">

    <h2>Inscription</h2>

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('inscription.traiter') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nom</label>
            <input
                type="text"
                class="form-control"
                name="nom"
                value="{{ old('nom') }}"
                required
            >
        </div>

        <div class="form-group">
            <label>Adresse e-mail</label>
            <input
                type="email"
                class="form-control"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </div>

        <div class="form-group">
            <label>Vous êtes ?</label>
            <select class="form-control" name="role" required>
                <option value="client"> Client</option>
                <option value="vendeur"> Vendeur</option>
                
            </select>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input
                type="password"
                class="form-control"
                name="password"
                required
            >
        </div>

        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input
                type="password"
                class="form-control"
                name="password_confirmation"
                required
            >
        </div>
<button type="submit" class="btn-register">
    S'inscrire
</button>

        <div class="footer-links">
            <a href="{{ route('login') }}">
                Déjà inscrit ? Se connecter
            </a>
        </div>

    </form>

</div>

</body>
</html>