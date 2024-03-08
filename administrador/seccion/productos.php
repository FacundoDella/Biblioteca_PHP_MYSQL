<?php

include("../template/header.php");
include("../config/db.php");


?>

<?php
$smsInfo = "";
$smsInfo2="";
$txtID = (isset($_POST['txtID'])) ? $_POST['txtID'] : "";
$txtNombre = (isset($_POST['txtNombre'])) ? $_POST['txtNombre'] : "";

$txtImagen = (isset($_FILES['txtImagen']['name'])) ? $_FILES['txtImagen']['name'] : "";
$txtAccion = (isset($_POST['accion'])) ? $_POST['accion'] : "";



switch ($txtAccion) {
    case 'Agregar':
        if ($txtImagen == "" || $txtNombre == "") {

            if ($txtImagen == "") {
                $smsInfo = "Agrega una Imagen";
            }
            if ($txtNombre == "") {
                $smsInfo2 = "Agrega una Nombre";
            }
        } else {
            $sentenciaSQL = $conexion->prepare("INSERT INTO `libros` (nombre, imagen) VALUES (:nombre, :imagen)"); // Query
            $sentenciaSQL->bindParam(':nombre', $txtNombre); // Le asocio los parametros a las variables de los inputs


            // Guardas imagenes en la carpeta img
            $fecha = new DateTime();
            $nombreArchivo = ($txtImagen != "") ? $fecha->getTimestamp() . "_" . $_FILES['txtImagen']['name'] : "imagen.jpg";

            $tempImagen = $_FILES["txtImagen"]["tmp_name"]; // Variable Global de PHP que contiene el nombre temporal del archivo 

            if ($tempImagen != "") {
                move_uploaded_file($tempImagen, "../../img/" . $nombreArchivo); // El archivo se guarda en img con su nuevo nombre  $nombreArchivo
            }

            $sentenciaSQL->bindParam(':imagen', $nombreArchivo);
            $sentenciaSQL->execute(); // Se ejecuta la Query
            header("Location:productos.php");
        }
        break;

    case 'Modificar':
        $sentenciaSQL = $conexion->prepare("UPDATE libros SET nombre=:nombre WHERE id=:id"); //Preparo la instruccion SQL de seleccion (Query)
        $sentenciaSQL->bindParam(':nombre', $txtNombre);
        $sentenciaSQL->bindParam(':id', $txtID);
        $sentenciaSQL->execute(); // La ejecuto

        if ($txtImagen != "") {

            // Modificacion de nombres
            $fecha = new DateTime();
            $nombreArchivo = ($txtImagen != "") ? $fecha->getTimestamp() . "_" . $_FILES['txtImagen']['name'] : "imagen.jpg";
            $tempImagen = $_FILES["txtImagen"]["tmp_name"];

            // Subida a img
            move_uploaded_file($tempImagen, "../../img/" . $nombreArchivo);

            // Sentencia de borrado db
            $sentenciaSQL = $conexion->prepare("SELECT imagen FROM libros WHERE id=:id"); //Preparo la instruccion SQL de seleccion (Query)
            $sentenciaSQL->bindParam(':id', $txtID);
            $sentenciaSQL->execute(); // La ejecuto
            $libro = $sentenciaSQL->fetch(PDO::FETCH_LAZY); // Creo un array con el resultado
            if (isset($libro['imagen']) && $libro['imagen'] != "imagen.jpg") {
                if (file_exists("../../img/" . $libro['imagen'])) {

                    unlink("../../img/" . $libro['imagen']);
                }
            }

            // Subida de nuevo archivo db
            $sentenciaSQL = $conexion->prepare("UPDATE libros SET imagen=:imagen WHERE id=:id"); //Preparo la instruccion SQL de seleccion (Query)
            $sentenciaSQL->bindParam(':imagen', $nombreArchivo);
            $sentenciaSQL->bindParam(':id', $txtID);
            $sentenciaSQL->execute(); // La ejecuto
        }

        header("Location:productos.php");
        break;

    case 'Cancelar':
        header("Location:productos.php");
        break;

    case 'Seleccionar':
        $sentenciaSQL = $conexion->prepare("SELECT * FROM libros WHERE id=:id"); //Preparo la instruccion SQL de seleccion (Query)
        $sentenciaSQL->bindParam(':id', $txtID);
        $sentenciaSQL->execute(); // La ejecuto
        $libro = $sentenciaSQL->fetch(PDO::FETCH_LAZY); // Creo un array con el resultado
        $txtNombre = $libro['nombre'];
        $txtImagen = $libro['imagen'];
        break;

    case 'Borrar':

        //Borrar las imagenes de la carpeta img
        $sentenciaSQL = $conexion->prepare("SELECT imagen FROM libros WHERE id=:id"); //Preparo la instruccion SQL de seleccion (Query)
        $sentenciaSQL->bindParam(':id', $txtID);
        $sentenciaSQL->execute(); // La ejecuto
        $libro = $sentenciaSQL->fetch(PDO::FETCH_LAZY); // Creo un array con el resultado

        if (isset($libro['imagen']) && $libro['imagen'] != "imagen.jpg") {
            if (file_exists("../../img/" . $libro['imagen'])) {

                unlink("../../img/" . $libro['imagen']);
            }
        }

        //Borrar la info en la base de datos
        $sentenciaSQL = $conexion->prepare("DELETE FROM libros WHERE id=:id"); //Preparo la instruccion SQL de seleccion (Query)
        $sentenciaSQL->bindParam(':id', $txtID);
        $sentenciaSQL->execute(); // La ejecuto

        header("Location:productos.php");
        break;
}

$sentenciaSQL = $conexion->prepare("SELECT * FROM libros"); //Preparo la instruccion SQL de seleccion (Query)
$sentenciaSQL->execute(); // La ejecuto
$listaLibros = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC); // Creo un array asociativo con cada resultado


?>

<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            Datos del Libro
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="exampleInputEmail1">ID:</label>
                    <input type="text" class="form-control" id="txtID" name="txtID" placeholder="ID" value="<?php echo $txtID ?>" required readonly>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Nombre:</label>
                    <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Nombre del Libro" value="<?php echo $txtNombre ?>">
                    <p class="text-danger"><?php echo $smsInfo2; ?></p>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Imagen:</label>
                    <br>
                    <?php
                    if ($txtImagen != '' && $txtNombre !='') { ?>
                        <img class="img-thumbnail rounded" src="../../img/<?php echo $txtImagen;  ?>" width="50" alt="">
                    <?php } ?>

                    <input type="file" class="form-control" id="txtImagen" name="txtImagen" placeholder="Imagen del Libro">
                    <p class="text-danger"><?php echo $smsInfo; ?></p>
                </div>

                <div class="btn-group" role="group">
                    <button name="accion"  <?php echo ($txtAccion == 'Seleccionar') ? "disabled" : "" ?> value="Agregar" class="btn btn-success">Agregar</button>
                    <button name="accion" type="submit" <?php echo ($txtAccion != 'Seleccionar') ? "disabled" : "" ?> value="Modificar" class="btn btn-warning">Modificar</button>
                    <button name="accion" type="submit" value="Cancelar" class="btn btn-info">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="col-md-6">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listaLibros as $libro) { ?>
                <tr>
                    <td><?php echo $libro['id'] ?></td>
                    <td><?php echo $libro['nombre'] ?></td>

                    <td>
                        <img class="img-thumbnail rounded" src="../../img/<?php echo $libro['imagen'] ?>" width="50" alt="">
                    </td>

                    <td>
                        <form method="POST">
                            <input type="hidden" name="txtID" id="txtID" value="<?php echo $libro['id'] ?>">
                            <input name="accion" type="submit" value="Seleccionar" class="btn btn-primary">
                            <input name="accion" type="submit" value="Borrar" class="btn btn-danger">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include("../template/footer.php") ?>