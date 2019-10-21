<?php
include_once ('ClaseModeloP.php');
Class LibroIvas extends ModeloP {
    private $fecha_inicio;
    private $fecha_final;

    
    public function comprobarPost($post=''){
        $ok = 'KO';
        if (isset($post['emitido'])){
            $ok = 'OK';
        }
        if (isset($post['soportado'])) {
            $ok = 'OK';
        }
        
        $this->fecha_inicio = $_POST['fecha_inicio'];
        $this->fecha_final = $_POST['fecha_final'];

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
			  .$this->fecha_inicio.'" AND FECHA<="'.$this->fecha_final.'" ORDER BY `FECHA` ASC';
        $registros = parent::query($sql,'SELECT');
        // Obtenemos datos que nos falta.
        $registros = $this->setMasDatos($registros,'emitidos');

        $resultado = $registros['items'];
        return $resultado;

    }
     public function getSoportados(){
        $sql ='SELECT * FROM `diario` WHERE `SUBCTA`>="47200000" AND `SUBCTA`<="47200099" AND FECHA>="'
			  .$this->fecha_inicio.'" AND FECHA<="'.$this->fecha_final.'" ORDER BY `FECHA` ASC ';
		error_log($sql);
        $registros = parent::query($sql,'SELECT');
        $registros = $this->setMasDatos($registros,'soportados');

        $resultado = $registros['items'];
        return $resultado;

    }

    public function setMasDatos($registros,$tipo){
        // @ Objetivo
        // Es añadir datos que falta:
        //  - Total de cada asiento
        //  - Obtener Nombre y DNI de contrapartida
        $total = 0;
        $asiento_anterior = 0;
        foreach ( $registros['items'] as $key=>$registro){
            // Obtenemos Nombre y NIF subcuenta.
            $sql = 'SELECT * FROM `subcta` WHERE `COD`="'.$registro->CONTRA.'"';
            $datos_contra = parent::query($sql,'SELECT');
            $registros['items'][$key]->{'nif'}     = $datos_contra['items'][0]->NIF;
            $registros['items'][$key]->{'nombre'}  = $datos_contra['items'][0]->TITULO;
            if ($tipo === 'emitidos'){
                $cuota_iva= $registro->EUROHABER;
            } else {
                $cuota_iva= $registro->EURODEBE;
                // Si existe HABER, quiere decir que la bas es negativo y el iva tambien.
                if (floatval($registro->EUROHABER) >0){
                    error_log('tipo'.gettype($registro->EUROHABER));
                    $registro->BASEEURO =-$registro->BASEEURO ;
                    $registros['items'][$key]->BASEEURO =-$registro->BASEEURO*(-1);
                    $cuota_iva= $registro->EUROHABER*(-1);
                    $registros['items'][$key]->BASEEURO =-$registro->BASEEURO*(-1);
                    $registros['items'][$key]->EURODEBE = $cuota_iva;


                    error_log('Cuota:'.$cuota_iva.' Base:'. $registro->BASEEURO);
                }

            }
            
            $total = $total+($registro->BASEEURO +$cuota_iva);
            if ($registro->ASIEN <> $asiento_anterior){
                $asiento_anterior=$registro->ASIEN;
            }
            if ( $registros['items'][$key+1]->ASIEN <> $registros['items'][$key]->ASIEN){
                $registros['items'][$key]->{'total'}=$total;
                $total = 0;
            }
            
        }
        return $registros;

    }

    public function getFecha_inicial(){
		
			return $this->fecha_inicio;
	}
	
	public function getFecha_final(){
		
			return $this->fecha_final;
	}

}
