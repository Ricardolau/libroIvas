<?php
include './php/ClaseSession.php';
include './php/ClaseLibroivas.php';
$usuario_sesion = new ClaseSession();
$libroIvas = new Libroivas();
?>
<html>
 <head>
  <title>Libro de iva</title>
  <link href="css/bootstrap431/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/template.css" rel="stylesheet">
  <script type="text/javascript" src="./js/libroIvas.js"></script>


 </head>
 <body>
    <div class="container">
         <h1>Libros de iva de empresa</h1>
         <form id="id_libroIva" name="libroIva" action="" method="POST" onkeypress="return anular(event)" onSubmit="return ComprobarBtn(this)">
            <div class="row">
                <legend>
                    Selecciona intervalo de fechas:
                </legend>
                <div class="col">
                    <input type="date" name="fecha_inicio" class="form-control" placeholder="Fecha Inicio" value="2019-01-01"
       min="2019-01-01" max="2019-12-31">
                </div>
                <div class="col">
                    <input type="date" name="fecha_final" class="form-control" placeholder="Fecha Final" value="2019-12-31"
       min="2019-01-01" max="2019-12-31">
                </div>
                
            </div>
            <div class="row">
                <legend>
                    Tipo de listado:
                </legend>
                <div class="col">
                <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="emitido" id="emitido">
                      <label class="form-check-label" for="inlineRadio1">Emitidos</label>
                </div>
                <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="soportado" id="soportado" >
                      <label class="form-check-label" for="inlineRadio2">Soportados</label>
                </div>
                </div>
            </div>
            <div class="row">
                <legend>
                    Campos a mostrar generales:
                </legend>
                <div class="col">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="asiento" id="asiento_id" checked>
                    <label class="form-check-label" for="asiento">
                    Numeros de asiento
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="subcta" id="subcta_id" checked>
                    <label class="form-check-label" for="subcta">
                    Numeros de subcuenta
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="documento" id="documento_id" checked>
                    <label class="form-check-label" for="documento">
                    Numeros de Documento(tpv) 
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="contrapartida" id="contrapartida_id" checked>
                    <label class="form-check-label" for="contrapartida">
                    Numeros de contrapartida 
                    </label>
                </div>
                
                </div>
            </div>
            <div class="row">
                <legend>
                </legend>
                <button type="submit" class="btn btn-primary mb-2">Solicitar Listado</button>
            </div>
                    

        </form>


       
</div>
</body>
</html>
