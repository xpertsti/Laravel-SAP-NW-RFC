<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\SapConnectionService;
use App\Traits\LogService;
use App\Traits\UtilitiesService;
use App\Traits\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SapController extends Controller
{

    use LogService, ResponseService, UtilitiesService;

    protected $sapService;

    public function __construct(SapConnectionService $sapService)
    {
        $this->sapService = $sapService;
    }

    public function index()
    {
        $connection = $this->sapService->getConnection();

        $this->sapService->close();

        return response()->json(['message' => 'Operación completada con éxito']);
    }




    /** PROBADA */
    /** PRIORIDAD (5) */

/**
 * @OA\Post(
 *     path="/api/v1/saprfc/avisos/consulta",
 *     summary="Consulta de avisos",
 *     description="Este método consulta avisos utilizando la función RFC de SAP ZSGDEA_CONSULTA_AVISOS.",
 *     tags={"Avisos"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="iCentro", type="string", example="0010", description="Centro (opcional)"),
 *             @OA\Property(property="iFechaIni", type="string", format="date", example="2024-08-15", description="Fecha de inicio"),
 *             @OA\Property(property="iHoraIni", type="string", format="time", example="08:00:00", description="Hora de inicio"),
 *             @OA\Property(property="iFechaFin", type="string", format="date", example="2024-08-15", description="Fecha de fin"),
 *             @OA\Property(property="iHoraFin", type="string", format="time", example="18:00:00", description="Hora de fin"),
 *             @OA\Property(property="asttx", type="array", @OA\Items(type="string"), example={"estado1", "estado2"}, description="Estados (opcional)"),
 *             @OA\Property(property="vkont", type="array", @OA\Items(type="string"), example={"cuenta1", "cuenta2"}, description="Cuentas de contrato (opcional)"),
 *             @OA\Property(property="notifType", type="array", @OA\Items(type="string"), example={"tipo1", "tipo2"}, description="Tipos de notificación (opcional)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Consulta exitosa",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="origin", type="string", example="serverSapRfc"),
 *             @OA\Property(property="transactionId", type="string", example="123456789"),
 *             @OA\Property(property="timestamp", type="string", format="date-time", example="2024-08-15T14:30:00Z"),
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="data", type="object", description="Datos retornados por SAP"),
 *             @OA\Property(property="message", type="string", example="success")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Solicitud incorrecta",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error en la solicitud")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error en el servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error interno del servidor")
 *         )
 *     )
 * )
 */
    public function ZSGDEA_CONSULTA_AVISOS(Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/avisos/consulta';

        try{

            $estadosArray = [];
            if (is_array($request->asttx) && !empty($request->asttx)) {
                foreach ($request->asttx as $estado) {
                    if (!empty($estado)) { 
                        $estadosArray[] = ["ASTTX" => $estado];
                    }
                }
            }

            $cuentascontratoArray = [];
            if (is_array($request->vkont) && !empty($request->vkont)) {
                foreach ($request->vkont as $cuentacontrato) {
                    if (!empty($cuentacontrato)) { 
                        $cuentascontratoArray[] = ["VKONT" => $cuentacontrato];
                    }
                }
            }

            $clasesArray = [];
            if (is_array($request->notifType) && !empty($request->notifType)) {
                foreach ($request->notifType as $clase) {
                    if (!empty($clase)) { 
                        $clasesArray[] = ["NOTIF_TYPE" => $clase];
                    }
                }
            }


            if ($request->iCentro == NULL){
                $request->iCentro = "";
            }

            //Consulta de Información básica de los avisos
            $functionName = 'ZSGDEA_CONSULTA_AVISOS';
            $parameters = [
                'I_CENTRO' => $request->iCentro, // (opcional) -> si viene vacio, no debe incluirse
                'I_FECHA_INI' => $request->iFechaIni,
                'I_HORA_INI' => $request->iHoraIni,
                'I_FECHA_FIN' => $request->iFechaFin,
                'I_HORA_FIN' => $request->iHoraFin,
                'I_GRUPO_AVISOS' => "SGDEA",
                "I_CLASES" => $clasesArray,
                "I_ESTADOS" => $estadosArray,
                "I_CUENTAS_CONTRATO" => $cuentascontratoArray

            ];
    
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {

            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
            
        }

    }



    /** PROBADA */
    /** PRIORIDAD (6) */
    public function ZSGDEA_DETALLE_AVISO($iNumero, Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/aviso/detalles';

        try{

            if ($iNumero == NULL){
                $iNumero = "";
            }
            
            $functionName = 'ZSGDEA_DETALLE_AVISO';
            $parameters = [
                'I_NUMERO' => $iNumero,
                'I_GRUPO_AVISOS' => 'SGDEA',
            ];

        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }

    }





    /** PROBADA */
    /** PRIORIDAD (9) */


    public function ZSGDEA_PERSONAL_HABILITADO($iCentroCosto, Request $request)
    {

        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/personal/habilitado';



        try{

            if ($iCentroCosto == NULL){
                $iCentroCosto = "";
            }

            $functionName = 'ZSGDEA_PERSONAL_HABILITADO';
            $parameters = [
                'I_CENTRO_COSTO' => $iCentroCosto, //'3431001',
            ];
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
            //throw new \Exception('Simulated error');

            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {

            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);

        }

    }


    /** PROBADO - PENDIENTE REVISAR TABLE_LINE, NO LO TOMA COMO CAMPO VALIDO */
    /** PRIORIDAD (3) */

    public function ZSGDEA_DETALLES_CTA_CONTRATO($cuentaContratoId, Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/cuenta-contrato/detalles';

        try{


            $cuentascontratoArray = [];
            $arrayCuentaContrato = [$cuentaContratoId];
            if (is_array($arrayCuentaContrato) && !empty($arrayCuentaContrato)) {
                foreach ($arrayCuentaContrato as $cuentacontrato) {
                    if (!empty($cuentacontrato)) {
                        $cuentascontratoArray[] = ["CUENTA_CONTRATO" => $cuentacontrato];
                    }
                }
            }

            
            $functionName = 'ZSGDEA_DETALLES_CTA_CONTRATO';
            $parameters = [
                "I_CUENTAS_CONTRATO" => $cuentascontratoArray,

            ];

        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();

            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }

    }




    /** PROBADA */
    /** PRIORIDAD (10) */

    public function ZSGDEA_CONSULTA_MEDIDAS($iFechaIni, $iFechaFin, Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/medidas';


        try{


            if ($iFechaIni == NULL){
                $iFechaIni = "";
            }

            if ($iFechaFin == NULL){
                $iFechaFin = "";
            }

            $functionName = 'ZSGDEA_CONSULTA_MEDIDAS';
            $parameters = [
                'I_FECHA_INI' => "$iFechaIni",
                'I_FECHA_FIN' => $iFechaFin,
                "I_CLASES" => [
                    ["MASSN" => "MA"],
                    ["MASSN" => "MB"]
                ],

            ];

    
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }

    }

    /** PROBADA */
    /** PRIORIDAD (12) */

    /** Nota:  agregar formato con ceros a la izquierda hasta 12 caracteres $request->ctaContrato
     *         agregar formato con ceros a la izquierda hasta 10 caracteres $request->interlocutor
    */
    public function ZPM_DETALLE_INTERLOCUTOR(Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/interlocutor/detalles';

        try{
            $opcion = 0;
            $message = "success";
            $formattedData = [];
            $parameters = [];
            $status = true;

            if (!empty($request->ctaContrato)) {
                $parameters = [
                    'CTA_CONTRATO' => $request->ctaContrato,  //11557160
                ];
            } else {
                if (!empty($request->interlocutor)) {
                    $parameters = [
                        'INTERLOCUTOR' => $request->interlocutor,  //10215971
                    ];
                } else {
                    $opcion = 1;
                    $message = "error";
                    $status = false;
                }
            }
            
            if ($opcion == 0) {
                $functionName = 'ZPM_DETALLE_INTERLOCUTOR';
                $result = $this->sapService->callRFC($functionName, $parameters);
                $formattedData = $result;
                $this->sapService->close();
            }
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => $status,
                'data'          => $formattedData,
                'message'       => $message,
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }

    }

    /** PROBADA - NO TIENE HABILITADO CONSUMO REMOTO - NO FUNCIONA */
    /** PRIORIDAD (2) */

    public function Z_WM_FIND_ZONA_GRUPO_PLANIFICA(Request $request)
    {
        try{

            //Busqueda de agrupacion de estructura regional
            $functionName = 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA';
            $parameters = [
                'ZCALLE' => $request->zCalle,  
                'ZHOUSE1' => $request->zHouse1,  
                'ZQMART' => $request->zQmart,  
                'ZCIUDAD' => $request->zCiudad,  
            ];
            
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'server',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }


    public function ZSGDEA_CONSULTA_SOLICITUDES(Request $request)
    {

        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/solicitudes/consulta';
        

        try{

            if ($request->iCentro == NULL){
                $request->iCentro = "";
            }

            if ($request->iEstado == NULL){
                $request->iEstado = "";
            }

            if ($request->iHoraIni == NULL){
                $request->iHoraIni = "010101";
            }
            if ($request->iHoraFin == NULL){
                $request->iHoraFin = "235959";
            }

            if ($request->iFechaIni == NULL){
                $request->iFechaIni = "00000000";
            }
            if ($request->iFechaFin == NULL){
                $request->iFechaFin = "00000000";
            }


            $parametrosArray = [];
            if (is_array($request->parametros) && count($request->parametros) > 0) {
                foreach ($request->parametros as $value) {
                    if (!empty($value)) { 

                        if ($value['radicados'] == NULL){
                            $value['radicados'] = "";
                        }
                        if ($value['contacto'] == NULL){
                            $value['contacto'] = "";
                        }
                        if ($value['cuentaContrato'] == NULL){
                            $value['cuentaContrato'] = "";
                        }
                        if ($value['interlocutor'] == NULL){
                            $value['interlocutor'] = "";
                        }
            
                        $parametrosArray[] = [
                            "RADICADO" => $value['radicados'],
                            "CONTACTO" => $value['contacto'],
                            "CUENTA_CONTRATO" => $value['cuentaContrato'],
                            "INTERLOCUTOR" => $value['interlocutor'],
                        ];
                    }
                }
            }


            /*
            $iEstado = '1';
            if (!empty($request->iEstado)) {
                $iEstado = $request->iEstado;
            }
            */

            $functionName = 'ZSGDEA_CONSULTA_SOLICITUDES';
            $parameters = [
                'I_CENTRO' => $request->iCentro, // (opcional) -> si viene vacio, no debe incluirse
                'I_ESTADO' => $request->iEstado, 
                'I_FECHA_INI' => $request->iFechaIni,
                'I_HORA_INI' => $request->iHoraIni,
                'I_FECHA_FIN' => $request->iFechaFin,
                'I_HORA_FIN' => $request->iHoraFin,
                "I_PARAMETROS" => $parametrosArray,
            ];
    
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }
    }


    public function ZSGDEA_CREAR_CONTACTO(Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/contacto/crear';

        try{


            if ($request->iCuentaContrato == NULL){
                $request->iCuentaContrato = "";
            }
            if ($request->iFechaRadicacion == NULL){
                $request->iFechaRadicacion = "";
            }
            if ($request->iHoraRadicacion == NULL){
                $request->iHoraRadicacion = "";
            }
            if ($request->iRadicado == NULL){
                $request->iRadicado = "";
            }
            if ($request->iTipo == NULL){
                $request->iTipo = "";
            }
            if ($request->iUsuario == NULL){
                $request->iUsuario = "";
            }

            $functionName = 'ZSGDEA_CREAR_CONTACTO';
            $parameters = [
                'I_CUENTA_CONTRATO' => $request->iCuentaContrato, 
                'I_FECHA_RADICACION' => $request->iFechaRadicacion,
                'I_HORA_RADICACION' => $request->iHoraRadicacion,
                'I_RADICADO' => $request->iRadicado,
                'I_TIPO' => $request->iTipo,
                "I_USUARIO" => $request->iUsuario,
            ];
    
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }
    }


    public function ZSGDEA_ACTUALIZAR_CONTACTO(Request $request)
    {
        $dataHeader = $this->verificaEncabezados($request);
        $timestamp = $dataHeader['timestamp'];
        $transactionId = $dataHeader['transactionId'];
        $headerUserid = $dataHeader['headerUserid'];
        if ($dataHeader['status']) {
            $dataHeader = $dataHeader['data']; 
        } else {
            return response()->json($dataHeader['data'], 400);
        }
        $endpoint = '/api/v1/saprfc/contacto/actualizar';

        try{

            if ($request->iClase == NULL){
                $request->iClase = "";
            }
            if ($request->iActividad == NULL){
                $request->iActividad = "";
            }
            if ($request->iAnular == NULL){
                $request->iAnular = "";
            }
            if ($request->iUsuario == NULL){
                $request->iUsuario = "";
            }

            $functionName = 'ZSGDEA_ACTUALIZAR_CONTACTO';
            $parameters = [
                'I_CONTACTO' => $request->iContacto, 
                'I_CLASE' => $request->iClase,
                'I_ACTIVIDAD' => $request->iActividad,
                'I_ANULAR' => $request->iAnular,
                "I_USUARIO" => $request->iUsuario,
            ];
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    
            $this->createLog($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint);

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            $data = $this->createLogError($transactionId, $timestamp, $dataHeader,$headerUserid, $endpoint, $e->getMessage());
            return response()->json($data, 500);
        }
    }
    



}
