<?php
include './php/ClaseSession.php';
include './php/ClaseLibroivas.php';
$usuario_sesion = new ClaseSession();
$libroIvas = new Libroivas();
if ($libroIvas->comprobarPost($_POST) === 'KO'){
    // Si no viene de index de proyecto no continuamos.
    header("Location: index.php");

}
$registros = $libroIvas->getEmitidos();

echo '<pre>';
print_r($_POST);
echo '</pre>';
?>
<html>
 <head>
  <title>Libro de iva- Emitido</title>
  <link href="css/bootstrap431/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/template.css" rel="stylesheet">

 </head>
 <body>
<div class="col-md-12">
    <h1>Libros de iva Emitidos</h1>
    <p><?php echo 'Fecha inicio:'.$libroIvas->getFecha_inicial();?></p>
    <table class="table">
  <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Asiento</th>
        <th scope="col">Fecha</th>
        <th scope="col">Subcta</th>
        <th scope="col">contrapartida <br/> nombre</th>
        <th scope="col">NIF</th>
        <th scope="col">Concepto</th>
        <th scope="col">Base</th>
        <th scope="col">iva</th>
        <th scope="col">Cuota Iva</th>
        <th scope="col">Total Factura</th>


    </tr>
  </thead>
  <tbody>
    <?php
    $key = 0;
    $asiento_anterior = 0;
    $suma = array( 'total' =>0,
                   'base_4' => 0,
                   'base_10' => 0,
                   'base_21' => 0,
                   'iva_4' => 0,
                   'iva_10' => 0,
                   'iva_21' => 0
                   );
    foreach ( $registros as $registro){
        $asiento = $registro->ASIEN;
        
        if ($asiento <> $asiento_anterior){
            $key++;
            $row = $key;
            $class_row = ' class="inicio"';
            $asiento_anterior=$asiento;
            $fecha = date_create_from_format('Y-m-d', $registro->FECHA);
            $fecha = date_format($fecha,'d-m-Y');
            $datos = array( 'fecha'     => $fecha,
                            'n_asiento' => $registro->ASIEN,
                            'subcta'    => $registro->SUBCTA,
                            'contra'    => $registro->CONTRA.' '.$registro->nombre
                        );
        } else {
            $row= '';
            $class_row = '';
            $datos = array( 'fecha'     => '',
                            'n_asiento' => '',
                            'subcta'    => '',
                            'contra'    => ''
                        );
        }
        if (isset($registro->total)){
            $suma['total'] = $suma['total'] +$registro->total;
        }
        if ($registro->IVA==4.00){
            $suma['base_4'] = $suma['base_4']+$registro->BASEEURO;
            $suma['iva_4'] = $suma['iva_4']+$registro->EUROHABER;
        }
        if ($registro->IVA==10.00){
            $suma['base_10'] = $suma['base_10']+$registro->BASEEURO;
            $suma['iva_10'] = $suma['iva_10']+$registro->EUROHABER;
        }
        if ($registro->IVA==21.00){
            $suma['base_21'] = $suma['base_21']+$registro->BASEEURO;
            $suma['iva_21'] = $suma['iva_21']+$registro->EUROHABER;
        }
        //~ //foreach ($registros as $key=>$registro) {
        //~ if($key <10){
        //~ echo '<tr><th><pre>';
        //~ print_r($registro);
        //~ echo '</pre></th></tr>';
         //~ }
    ?>
   

    <tr<?php echo $class_row;?>>
        <th scope="row"><?php echo $row;?></th>
        <td><?php echo $datos['n_asiento'];?></td>
        <td><?php echo $datos['fecha'];?></td>
        <td><?php echo $datos['subcta'];?></td>
        <td><?php echo $datos['contra'];?></td>
        <td><?php echo $registro->nif;?>
        <td><?php echo $registro->CONCEPTO;?>
        </td>
        <td class="text-right"><?php echo $registro->BASEEURO;?></td>
        <td class="text-right"><?php echo $registro->IVA;?></td>
        <td class="text-right"><?php echo $registro->EUROHABER;?></td>
        <td class="text-right"><?php
            if (isset($registro->total)){
                echo '<b>'.number_format ($registro->total,2,"."," ").'</b>';
            }?>
        </td>
    </tr>


    <?php }
    echo '<tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Total</td>
            <td>'.$suma['total'].'</td>'
        .'</td></tr>';
    ?>
  </tbody>
</table>
<h4>Desglose de ivas</h4>
<table class="table">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Base</th>
        <th scope="col">Cuota</th>
    </tr>
  </thead>
  <tbody>
    <?php
     echo '<tr><th> 4%</th><td>'.$suma['base_4'].'<td>'.$suma['iva_4'].'</td>'
        .'</td></tr>';
     echo '<tr><th>10%</th><td>'.$suma['base_10'].'<td>'.$suma['iva_10'].'</td>'
        .'</td></tr>';
    echo '<tr><th>21%</th><td>'.$suma['base_21'].'<td>'.$suma['iva_21'].'</td>'
        .'</td></tr>';
    
    ?>
</tbody>
</table>
</div>
</body>
</html>
