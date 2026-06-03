<h2>Liste des voitures</h2>

<a href="/voitures/create">Ajouter une voiture</a>

@foreach($voitures as $voiture)
    <div>
        <h3>{{ $voiture->marque }} - {{ $voiture->modele }}</h3>
        <p>Prix : {{ $voiture->prix }}</p>
        <p>{{ $voiture->description }}</p>
        <hr>
    </div>
@endforeach
