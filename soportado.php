<?php
include './php/ClaseSession.php';
include './php/ClaseLibroivas.php';
$usuario_sesion = new ClaseSession();
$libroIvas = new Libroivas();
if ($libroIvas->comprobarPost($_POST) === 'KO'){
    // Si no viene de index de proyecto no continuamos.
    header("Location: index.php");

}
$registros = $libroIvas->getSoportados();   
$opciones = $libroIvas->campos;

?>
<html>
 <head>
  <title>Libro de iva- Soportado</title>
  <link href="css/bootstrap431/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/template.css" rel="stylesheet">

 </head>
 <body>
<div class="col-md-12">
    <?php echo $libroIvas->getTituloInforme();?>
    <table class="table">
  <thead>
    <tr>
        <th scope="col">#</th>
        <?php
        if (isset($opciones['asiento'])){?>
            <th scope="col">Asiento</th>
        <?php
        }
        if (isset($opciones['documento'])){?>
            <th scope="col">Documento</th>
        <?php
        }
        ?>
            <th scope="col">Fecha</th>
        <?php
        if (isset($opciones['subcta'])){?>
            <th scope="col">Subcta</th>
        <?php
        }
        if (isset($opciones['contrapartida'])){?>
            <th scope="col">contrapartida <br/> nombre</th>
         <?php
        }
        ?>
        <th scope="col">NIF</th>
        <th scope="col">Nombre</th>
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
    $ivas = $libroIvas->ivas;
    $suma = array( 'total' =>0,
                   'base' => array (1 =>$ivas,
                                    2 =>$ivas,
                                    3 =>$ivas,
                                    4 =>$ivas
                                    ),
                   'cuota_iva' =>array (1 =>$ivas,
                                    2 =>$ivas,
                                    3 =>$ivas,
                                    4 =>$ivas
                                    ),
                   'totalBases' =>0,
                   'totalCuotas'=>0
                   );
    foreach ($registros as $registro){
        $asiento = $registro->ASIEN;
        $datos = $registro->datos;
        $row= '';
        $class_row = '';
        if ($asiento <> $asiento_anterior){
            $key++;
            $row = $key;
            $class_row = ' class="inicio"';
            $asiento_anterior=$asiento;
        }
        if (isset($datos['total'])){
            $suma['total'] += $datos['total'];
        }
        
        $suma['totalBases'] += $registro->BASEEURO;;
        $suma['totalCuotas'] += $registro->EURODEBE;
        $trimestres = $libroIvas->getTrimestres();
        foreach ($trimestres as $k=>$trimestre){
            if ($registro->FECHA >=$trimestre['fi'] and $registro->FECHA <=$trimestre['ff']){
                foreach ($ivas as $x=>$iva) {
                    $str_iva = $x.'.00';
                    if ($registro->IVA===$str_iva){
                        $suma['base'][$k][$x] += $registro->BASEEURO;
                        $suma['cuota_iva'][$k][$x] += $registro->EURODEBE;
                    }
                }
            }
        }
        
    ?>
   

    <tr<?php echo $class_row;?>>
        <th scope="row"><?php echo $row;?></th>
    <?php if (isset($opciones['asiento'])){?>
        <td><?php echo $datos['n_asiento'];?></td>
    <?php }
          if (isset($opciones['documento'])){?>
        <td><?php echo $datos['documento'];?></td>
    <?php } ?>
        <td><?php echo $datos['fecha'];?></td>
    <?php
         if (isset($opciones['subcta'])){?>
        <td><?php echo $datos['subcta'];?></td>
    <?php }
         if (isset($opciones['contrapartida'])){?>
        <td><?php echo $datos['contra'];?></td>
    <?php } ?>
        <td><?php echo $datos['nif'];?>
        <td><?php echo $datos['nombre'];?>
        <td><?php echo $registro->CONCEPTO;?>
        </td>
        <td class="text-right"><?php echo $registro->BASEEURO;?></td>
        <td class="text-right"><?php echo $registro->IVA;?></td>
        <td class="text-right"><?php echo $registro->EURODEBE;?></td>
        <td class="text-right"><?php
            if (isset($datos['total'])){
                echo '<b>'.number_format ($datos['total'],2,"."," ").'</b>';
            }?>
        </td>
    </tr>


    <?php }
    echo '<tr>'.
            str_repeat ('<td class="a"></td>',count($opciones)+4)
            .'<td><b>TOTAL</b></td>
            <td class="text-right"><b>'.$suma['totalBases'].'</b></td>'
            .'<td></td>'
            .'</td><td class="text-right"><b>'.$suma['totalCuotas'].'</b></td>'
            .'</td><td class="text-right"><b>'.$suma['total'].'</b></td>'

        .'</tr>';
    ?>
  </tbody>
</table>
<div class="row">

<?php foreach ($trimestres as $k=>$trimestre){ ?>
<div class="col-sm-6">
    <h4>Resumen de trimestre <?php echo $k?></h4>
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
         foreach ($ivas as $x=>$iva){
            echo '<tr><th>'.$x.'%</th><td>'
            .number_format ($suma['base'][$k][$x],2,"."," ").'<td>'.number_format ($suma['cuota_iva'][$k][$x],2,"."," ").'</td>'
            .'</td></tr>';
         }
        
        ?>
    </tbody>
    </table>
</div>
<?php }
?>
</div>
</div>

</body>
</html>
