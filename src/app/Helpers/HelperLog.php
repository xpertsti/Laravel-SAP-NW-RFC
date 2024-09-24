<?php
/**

 */
namespace App\Helpers;

use App\Models\Log;
use App\Models\RadiLogRadicado;
use App\Models\GdHistoricoExpediente;
use App\Models\Radicado;
use App\Models\User;

class HelperLog
{

    public static function logAdd($type = false, $id, $username, $modulo, $evento, $dataOld, $data, $exceptions)
    {

        /***
            Ip del cliente
        ***/
        $ipCliente = "0.0.0.0";
        if(isset($_SERVER["HTTP_CLIENT_IP"]))
        {
            $ipCliente = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $ipCliente = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif(isset($_SERVER["HTTP_X_FORWARDED"]))
        {
            $ipCliente = $_SERVER["HTTP_X_FORWARDED"];
        }
        elseif(isset($_SERVER["HTTP_FORWARDED_FOR"]))
        {
            $ipCliente = $_SERVER["HTTP_FORWARDED_FOR"];
        }
        elseif(isset($_SERVER["HTTP_FORWARDED"]))
        {
            $ipCliente = $_SERVER["HTTP_FORWARDED"];
        } else {
            $ipCliente = $_SERVER["REMOTE_ADDR"];
        }
        /***
            Fin ip del cliente
        ***/

        /***
            Valida cambios en el save de cada acción, si es manual se almacena el antes y el despues directamente sin validar atributos
        ***/
        $antes = "";
        $antesConcat = "";
        $despues = "";
        $despuesConcat = "";
        
        #Recibe el modelo $data y $dataOld como un string
        if($type){

            /***
            Guardamos la información
            ***/
            $modelLog = new Log();
            $modelLog->idUser = $id;
            $modelLog->userNameLog = $username;
            $modelLog->fechaLog = date("Y-m-d H:i:s");
            $modelLog->ipLog = $ipCliente;
            $modelLog->moduloLog = $modulo;
            $modelLog->eventoLog = $evento;
            $modelLog->antesLog = $dataOld;
            $modelLog->despuesLog = $data;

            if( ! $modelLog->save() ) {
                return array('data' => $modelLog->getError());
            }
            /***
                Fin
            ***/

        } else { // Sino $data y $dataOld se obtendra de los atributos del modelo

            for($i = 0; $i < count($data); $i++) {

                if(isset($data[$i])) {

                    for($j = 0; $j < count($data[$i]->Attributes()); $j++) {

                        if(!in_array($data[$i]->Attributes()[$j], $exceptions)) {

                            if(isset($dataOld[$i])) {

                                if(isset($dataOld[$i][$data[$i]->Attributes()[$j]])) {

                                    if(!strcmp($dataOld[$i][$data[$i]->Attributes()[$j]], $data[$i]->getAttributes()[$data[$i]->Attributes()[$j]]) == 0) {

                                        $antesConcat .= $data[$i]->attributeLabels()[$data[$i]->Attributes()[$j]] .": ". $dataOld[$i][$data[$i]->Attributes()[$j]] .", ";
                                        $despuesConcat .= $data[$i]->attributeLabels()[$data[$i]->Attributes()[$j]] .": ". $data[$i]->getAttributes()[$data[$i]->Attributes()[$j]] .", ";

                                    }

                                }

                            } else {

                                $attributeLabels = $data[$i]->attributeLabels();
                                $attributes = $data[$i]->Attributes();
                                
                                if (isset($attributeLabels[$attributes[$j]]) && isset($attributes[$j])) {
                                    $despuesConcat .= $attributeLabels[$attributes[$j]] . ": " . $data[$i]->getAttributes()[$attributes[$j]] . ", ";
                                } else {
                                    // Handle the case where the keys are not present
                                    // You can set a default value or handle this situation based on your requirements
                                    $despuesConcat .= "Key Not Found, ";
                                }

                            }

                        }

                    }
                }
            }

            if(strlen($antesConcat) > 3) {
                $antes = substr($antesConcat, 0, -2);
            } else {
                $antes = $antesConcat;
            }

            if(strlen($despuesConcat) > 3) {
                $despues = substr($despuesConcat, 0, -2);
            } else {
                $despues = $despuesConcat;
            }

            /***
                Fin
            ***/

            /***
                Guardamos la información
            ***/
            $modelLog = new Log();
            $modelLog->idUser = $id;
            $modelLog->userNameLog = $username;
            $modelLog->fechaLog = date("Y-m-d H:i:s");
            $modelLog->ipLog = $ipCliente;
            $modelLog->moduloLog = $modulo;
            $modelLog->eventoLog = $evento;
            $modelLog->antesLog = $antes;
            $modelLog->despuesLog = $despues;

            if(!$modelLog->save()) {
                return array('data' => $modelLog->getError());
            }
            /***
                Fin
            ***/
        }
    }


    // Proceso de guardar el registro de la trazabilidad del radicado
    public static function logAddFiling($idUser, $idDependencia, $idRadiRadicado, $idTransaccion, $observaciones, $data, $exceptions)
    {
        // Obtener los labels de los modelos utilizados
        $attributeLabels = Radicado::attributeLabels();

        // Inicializar variables para almacenar las diferencias
        $antes = '';
        $antesConcat = '';
        $despues = '';
        $despuesConcat = '';

        // Verificar si el parámetro $data es un array manual
        if (is_array($data)) {
            if (count($data) > 0) {
                if (isset($data[0]) && $data[0] == 'Manual') {
                    $antes = $data[1];
                    $despues = $data[2];
                }
            }
        } else {
            // Procesar las diferencias entre los valores originales y los actuales
            foreach ($data->getAttributes() as $attribute => $currentValue) {
                if (!in_array($attribute, $exceptions)) {
                    $originalValue = $data->getOriginal($attribute);

                    // Comparar los valores originales y actuales
                    if ($originalValue !== $currentValue) {
                        // Obtener el label del atributo si está disponible
                        $label = array_key_exists($attribute, $attributeLabels)
                            ? $attributeLabels[$attribute]
                            : $attribute;

                        // Concatenar las diferencias
                        $antesConcat .= $label . ": " . $originalValue . ", ";
                        $despuesConcat .= $label . ": " . $currentValue . ", ";
                    }
                }
            }

            // Eliminar la última coma y espacio de las cadenas concatenadas
            $antes = rtrim($antesConcat, ', ');
            $despues = rtrim($despuesConcat, ', ');
        }

        // Guardar la información en el modelo RadiLogRadicado
        $modelLog = new RadiLogRadicado();
        $modelLog->idUser = $idUser;
        $modelLog->idDependencia = $idDependencia;
        $modelLog->idRadiRadicado = $idRadiRadicado;
        $modelLog->idTransaccion = $idTransaccion;
        $modelLog->observacionRadiLogRadicado = $observaciones . ' ' . $despues;
        $modelLog->fechaRadiLogRadicado = now()->toDateTimeString(); // Usar Carbon para obtener la fecha actual

        // Guardar el registro de log
        if ($modelLog->save()) {
            return true;
        } else {
            // Manejar errores de validación
            return $modelLog->getError();
        }
    }

    public static function logAddExpedient($idUser, $idDependencia, $idExpediente , $operacion, $observacion){

        
        /***
            Guardamos la información
        ***/

            $modelHistoricoExpedeinte = new GdHistoricoExpediente();

            $modelHistoricoExpedeinte->idGdExpediente = $idExpediente;
            $modelHistoricoExpedeinte->idUser = $idUser;
            $modelHistoricoExpedeinte->idGdTrdDependencia = $idDependencia;
            $modelHistoricoExpedeinte->operacionGdHistoricoExpediente = $operacion;
            $modelHistoricoExpedeinte->observacionGdHistoricoExpediente = $observacion;

            if(!$modelHistoricoExpedeinte->save()){
                print_r($modelHistoricoExpedeinte->getErrors());die();
            }else{
                
                
            }

        /***
            Fin
        ***/


    }

    /**
     * Funcion para validar los nombres de los modulos que no estan registrados 
     * como permisos en la base de datos
    */
    public static function getDefaultModule($route)
    {
       
        if($route == 'user/load-massive-file'){
            $nombreModulo = 'Carga Masiva de usuarios';

        } elseif($route == 'site/login'){
            $nombreModulo = 'Inicio de sesión';

        } elseif($route == 'user/logout'){
            $nombreModulo = 'Cerrar sesión';
            
        } elseif($route == 'site/reset-password'){
            $nombreModulo = 'Cambio de contraseña';

        } elseif($route == 'user/change-status'){
            $nombreModulo = 'Cambio de estado';

        } elseif($route == 'site/signup'  || $route == 'registro-pqrs/index' || $route == 'consulta-pqrs/index' || $route == 'consulta-pqrs/desistimiento-radicado'){
            $nombreModulo = 'Página Pública PQRSD';

        } else {
            $nombreModulo = $route;
        }

        return $nombreModulo;
    }

}
?>
