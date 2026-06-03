<h2>Ajouter une voiture</h2>

<form method="POST" action="/voitures">
    @csrf

    <input type="text" name="marque" placeholder="Marque"><br>
    <input type="text" name="modele" placeholder="Modèle"><br>
    <input type="number" name="prix" placeholder="Prix"><br>

    <textarea name="description" placeholder="Description"></textarea><br>

    <select name="type">
        <option value="vente">Vente</option>
        <option value="location">Location</option>
    </select><br>

    <button type="submit">Ajouter</button>
</form>
