<?php
include_once ('ClaseModeloP.php');
Class LibroIvas extends ModeloP {
    private $fecha_inicio;
    private $fecha_final;
    private $tipo = ''; // Tipo de listado que vamos ejecutar.
    public $campos =array(); // Campo opcionales a mostrar.
    public $ivas = array(  '4'=> 0,'10'=>0,'21'=>0); // Valores a cero de los ivas.
    
    public function comprobarPost($post=''){
        $ok = 'KO';
        if (isset($post['emitido'])){
            $ok = 'OK';
            $this->tipo = 'emitido';
        }
        if (isset($post['soportado'])) {
            if ($this->tipo===''){
                $ok = 'OK';
                $this->tipo = 'soportado';
            } else {
                // Ya entro en emitido y no entra soportado.
                // no permitimos continuar
                $ok = 'KO';
            }
        }
        $this->setOpcionCampos($post);
        
        $this->fecha_inicio = $post['fecha_inicio'];
        $this->fecha_final = $post['fecha_final'];

        return $ok;
    }
   

    public function getAvisosHtml($id,$tipo,$parametro=array()){
        //@ Objetivo
        // Obtener los mensajes maquetados bootstrap.
        //@ Parametros
        // $id          -> (int) Indice mensaje.
        // $tipo        -> (string) Donde indicamos si es: info, danger o warning
        // $parametros  -> (array) Podemos mandar los parametros que necesite el mensaje.

        // Array de mensajes

        $mensaje = array(
                        
                   
                    );
        
        $html = '<div class="alert alert-'.$tipo.'">'
                .$mensaje[$id]
                .'</div>';

        return $html;
                    

    }

    public function getEmitidos(){
        $sql ='SELECT * FROM `diario` WHERE `SUBCTA`>="47700000" AND `SUBCTA`<="47700099" AND FECHA>="'
			  .$this->fecha_inicio.'" AND FECHA<="'.$this->fecha_final.'" ORDER BY `FECHA`,DOCUMENTO ASC';
        $registros = parent::query($sql,'SELECT');
        // Obtenemos datos que nos falta.
        $registros = $this->setMasDatos($registros,'emitidos');

        $resultado = $registros['items'];
        return $resultado;

    }
     public function getSoportados(){
        $sql ='SELECT * FROM `diario` WHERE `SUBCTA`>="47200000" AND `SUBCTA`<="47200099" AND FECHA>="'
			  .$this->fecha_inicio.'" AND FECHA<="'.$this->fecha_final.'" ORDER BY `FECHA` ASC ';
        $registros = parent::query($sql,'SELECT');
        $registros = $this->setMasDatos($registros,'soportados');

        $resultado = $registros['items'];
        return $resultado;

    }

    public function setMasDatos($registros,$tipo){
        // @ Objetivo
        // Es añadir datos que falta:
        //  - Total de cada asiento
        //  - Obtener Nombre y DNI de contrapartida de tabla subcta
        $total = 0;
        $n_registro = count($registros['items']);
        foreach ( $registros['items'] as $key=>$registro){
            // Obtenemos numero registro anterior y siguiente
            $previos = $key-1;
            $next = $key+1;
            // Obtenemos Nombre y NIF subcuenta.
            $sql = 'SELECT * FROM `subcta` WHERE `COD`="'.$registro->CONTRA.'"';
            $datos_contra = parent::query($sql,'SELECT');
            $datos =[];
            if ($previos < 0 || $registros['items'][$previos]->ASIEN <> $registro->ASIEN){
                $fecha = $this->getfecha($registro->FECHA);
                $datos= array( 'fecha'     => $fecha,
                                'n_asiento' => $registro->ASIEN,
                                'subcta'    => $registro->SUBCTA,
                                'contra'    => $registro->CONTRA.' '.$registro->nombre,
                                'documento' => $registro->DOCUMENTO
                            );
                            $total = 0;
            } else {
                $datos = array( 'fecha'     => '',
                                'n_asiento' => '',
                                'subcta'    => '',
                                'contra'    => '',
                                'documento' => ''
                            );
            }
            $datos['nif'] = $datos_contra['items'][0]->NIF;
            $datos['nombre'] = $datos_contra['items'][0]->TITULO;
            //~ $registros['items'][$key]->{'nif'}     = $datos_contra['items'][0]->NIF;
            //~ $registros['items'][$key]->{'nombre'}  = $datos_contra['items'][0]->TITULO;
            if ($tipo === 'emitidos'){
                $cuota_iva= $registro->EUROHABER;
            } else {
                $cuota_iva= $registro->EURODEBE;
                // Si existe HABER, quiere decir que es negativo tanto base como cuota iva.
                if (floatval($registro->EUROHABER) >0){
                    $registros['items'][$key]->BASEEURO =-$registro->BASEEURO;
                    $registros['items'][$key]->EURODEBE =-$registro->EUROHABER;
                }

            }
            
            $total = $total+($registro->BASEEURO +$cuota_iva);
            if ( $next === $n_registros || $registros['items'][$next]->ASIEN <> $registro->ASIEN){
                $datos['total'] = $total;
            }
            // Añadimos $datos al item de registro.
            $registros['items'][$key]->{'datos'}=$datos;
            
        }
        
        return $registros;

    }

    public function getFecha_inicial(){
		
			return $this->fecha_inicio;
	}
	
	public function getFecha_final(){
		
			return $this->fecha_final;
	}
    public function setOpcionCampos($post){
        // Saber que campos se muestra de los opcionales
        $campos = array('asiento','subcta','documento','contrapartida');
        $opcionales= [];
        foreach ($campos as $campo){
            if (isset($post[$campo])){
                $opcionales +=  array($campo =>$post[$campo]);
            }
        }
        $this->campos = $opcionales;
    }


    public function getTrimestres(){
        // @ Objetivo
        // Enviar array con los periodos 
        $trimestres =array( 1 =>array(
                             'fi'=>'2019-01-01',
                             'ff'=>'2019-03-31'
                            ),
                            2 =>array(
                            'fi'=>'2019-04-01',
                            'ff'=>'2019-06-30'
                            ),
                            3 =>array(
                            'fi'=>'2019-07-01',
                            'ff'=>'2019-09-30'
                            ),
                            4 =>array(
                            'fi'=>'2019-10-01',
                            'ff'=>'2019-12-31'
                            )
                        );
        $eliminados = [] ; // Bandera para eliminar trimestres que no esten entre las fechas indicadas.
        foreach ($trimestres as $key=>$trimestre){
            if ($this->fecha_inicio > $trimestre['ff'] || $this->fecha_final < $trimestre['fi']){
                // Quiere decir que ese trimestre no se suba.
                $eliminados[] = $key;
            }
        }
        // Eliminaos de array los trimestres que no corresponde.
        foreach ($eliminados as $a){
            unset($trimestres[$a]);
        }
        return $trimestres;
    }

    public function getTituloInforme(){
        // @Objetivo:
        // Obtener el html del titulo del listado.

        $html =  '<h1>Libros de iva '.$this->tipo.'</h1>'
                .'<p><b>'.$this->empresa.'</b> - CIF: '.$this->cif.'</p>'
                .'<p>Fecha inicio:'.$this->getFecha_inicial().' a fecha final:'.$this->getFecha_final().'</p>';

        return $html;
    }

    public function getfecha($fecha_original){
        //@ Objetivo
        // Recibo fecha formato Y-m-d y devuelvo formato d-m-y
        $fecha = date_create_from_format('Y-m-d', $fecha_original);
        $fecha = date_format($fecha,'d-m-Y');
        return $fecha;
    
    }

}
