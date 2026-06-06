<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Recherche Prix</title>

<style>

body{
    background:#111;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    font-family:Arial, sans-serif;
}

.card{
    width:350px;
    padding:25px;
    border-radius:20px;
    background:linear-gradient(135deg,#0A0A4F,#05052E);
    color:white;
}

.search-box{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.search-box input{
    flex:1;
    padding:12px;
    border:none;
    border-radius:12px;
    background:#6D5DFD;
    color:white;
    font-size:16px;
    outline:none;
}

.search-box input::placeholder{
    color:white;
}

.search-box button{
    padding:12px 18px;
    border:none;
    border-radius:12px;
    background:white;
    color:#0A0A4F;
    font-weight:bold;
    cursor:pointer;
}

.result{
    margin-top:20px;
    background:rgba(255,255,255,0.1);
    padding:15px;
    border-radius:12px;
}

.car{
    margin-bottom:10px;
    padding:10px;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

.car:last-child{
    border-bottom:none;
}

.price{
    color:#7CFFB2;
    font-weight:bold;
}

</style>
</head>

<body>

<div class="card">

    <div class="search-box">
        <input type="number" id="priceInput" placeholder="Entrez un prix">
        <button onclick="searchPrice()">Rechercher</button>
    </div>

    <div id="result" class="result">
        Entrez un prix pour rechercher un véhicule.
    </div>

</div>

<script>

const cars = [
    {name:"Toyota Corolla", price:18500000},
    {name:"Mercedes C220", price:22000000},
    {name:"Hyundai Tucson", price:9000000},
    {name:"Audi RS6 2023", price:15800000},
    {name:"Peugeot 3008 2022", price:16500000},
    {name:"Range Rover Evoque", price:25000000}
];

function searchPrice(){

    const input = document.getElementById("priceInput").value;
    const result = document.getElementById("result");

    if(input === ""){
        result.innerHTML = "Veuillez entrer un prix.";
        return;
    }

    const filteredCars = cars.filter(car => car.price <= input);

    if(filteredCars.length > 0){

        result.innerHTML = filteredCars.map(car => `
            <div class="car">
                <strong>${car.name}</strong><br>
                <span class="price">${car.price.toLocaleString()} FCFA</span>
            </div>
        `).join("");

    } else {

        result.innerHTML = "Aucun véhicule trouvé pour ce prix.";

    }
}

</script>

</body>
</html>