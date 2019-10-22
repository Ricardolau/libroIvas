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
echo '<pre>';
print_r($_POST);
echo '</pre>';
?>
<html>
 <head>
  <title>Libro de iva- Soportado</title>
  <link href="css/bootstrap431/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/template.css" rel="stylesheet">

 </head>
 <body>
<div class="col-md-12">
    <h1>Libros de iva de Soportado</h1>
    <table class="table">
  <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Asiento</th>
        <th scope="col">Ref_Tpv</th>
        <th scope="col">Fecha</th>
        <th scope="col">Subcta</th>
        <th scope="col">contrapartida <br/> nombre</th>
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
    $ivas = array(  '4'=> 0,'10'=>0,'21'=>0);
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
                            'contra'    => $registro->CONTRA,
                            'n_factura_tpv' => $registro->DOCUMENTO
                        );
        } else {
            $row= '';
            $class_row = '';
            $datos = array( 'fecha'     => '',
                            'n_asiento' => '',
                            'subcta'    => '',
                            'contra'    => '',
                            'n_factura_tpv' => ''
                        );
        }
        if (isset($registro->total)){
            $suma['total'] = $suma['total'] +$registro->total;
        }
        
        $suma['totalBases'] = $suma['totalBases'] +$registro->BASEEURO;;
        $suma['totalCuotas'] = $suma['totalCuotas'] +$registro->EURODEBE;
        $trimestres = $libroIvas->getTrimestres();
        foreach ($trimestres as $k=>$trimestre){
            if ($registro->FECHA >=$trimestre['fi'] and $registro->FECHA <=$trimestre['ff']){
                if ($registro->IVA==="4.00"){
                    $suma['base'][$k]['4']= $suma['base'][$k]['4']+$registro->BASEEURO;
                    $suma['cuota_iva'][$k]['4'] = $suma['cuota_iva'][$k]['4']+$registro->EURODEBE;
                }
                if ($registro->IVA==="10.00"){
                     $suma['base'][$k]['10']= $suma['base'][$k]['10']+$registro->BASEEURO;
                    $suma['cuota_iva'][$k]['10'] = $suma['cuota_iva'][$k]['10']+$registro->EURODEBE;
                }
                if ($registro->IVA==="21.00"){
                    $suma['base'][$k]['21']= $suma['base'][$k]['21']+$registro->BASEEURO;
                    $suma['cuota_iva'][$k]['21'] = $suma['cuota_iva'][$k]['21']+$registro->EURODEBE;
                }
            }
        }

        
    ?>
   

    <tr<?php echo $class_row;?>>
        <th scope="row"><?php echo $row;?></th>
        <td><?php echo $datos['n_asiento'];?></td>
        <td><?php echo $datos['n_factura_tpv'];?></td>
        <td><?php echo $datos['fecha'];?></td>
        <td><?php echo $datos['subcta'];?></td>
        <td><?php echo $datos['contra'];?></td>
        <td><?php echo $registro->nif;?>
        <td><?php echo $registro->nombre;?>
        <td><?php echo $registro->CONCEPTO;?>
        </td>
        <td class="text-right"><?php echo $registro->BASEEURO;?></td>
        <td class="text-right"><?php echo $registro->IVA;?></td>
        <td class="text-right"><?php echo $registro->EURODEBE;?></td>
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
            <td><b>TOTAL</b></td>
            <td><b>'.$suma['totalBases'].'</b></td>'
            .'<td></td>'
            .'</td><td><b>'.$suma['totalCuotas'].'</b></td>'
            .'</td><td><b>'.$suma['total'].'</b></td>'

        .'</td></tr>';
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
