<?php
include("./template/header.php");
include("./administrador/config/db.php");


$sentenciaSQL = $conexion->prepare("SELECT * FROM libros"); //Preparo la instruccion SQL de seleccion (Query)
$sentenciaSQL->execute(); // La ejecuto
$listaLibros = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC); // Creo un array asociativo con cada resultado



?>


<?php foreach($listaLibros as $libro){ ?>
<div class="col-md-2 mb-3">
    <div class="card">
        <img class="card-img-top" src="./img/<?php echo $libro['imagen'] ?>" alt="">

        <div class="card-body">
            <h4 class="card-title"><?php echo $libro['nombre'] ?></h4>
            <a name="" id="" class="btn btn-primary" href="https://goalkicker.com/" role="button">ver mas</a>
        </div>
    </div>
</div>
<?php } ?>
<?php
include("./template/footer.php")
?>