<?php
// app/Http/Controllers/SapController.php

namespace App\Http\Controllers;

use App\Services\SapConnectionService;
use App\Utils\TransactionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\ResponseService;



class SapController extends Controller
{

    use ResponseService;

    protected $sapService;

    public function __construct(SapConnectionService $sapService)
    {
        $this->sapService = $sapService;
    }

    public function index()
    {
        $connection = $this->sapService->getConnection();

        // Realiza tus operaciones SAP aquí

        // Cierra la conexión al final
        $this->sapService->close();

        return response()->json(['message' => 'Operación completada con éxito']);
    }


    public function callRfcFunction(Request $request)
    {

        
        $functionName = 'ZSGDEA_PERSONAL_HABILITADO';
        $parameters = [
            'I_CENTRO_COSTO' => '3431001',
            'I_FECHA_FIN' => '20240720',
        ];
        /*
        
        $functionName = 'ZSGDEA_DETALLES_CTA_CONTRATO';
        $parameters = [
            'I_CUENTAS_CONTRATO' => ['11557160'],
        ];

        $functionName = 'ZSGDEA_DETALLE_AVISO';
        $parameters = [
            'I_QMNUM' => '11557160',
        ];
        //Consulta medidas de personal
        $functionName = 'ZSGDEA_CONSULTA_MEDIDAS';
        $parameters = [
            'I_FECHA_INI' => '',
            'I_FECHA_FIN' => '',
            'I_CLASES' => '',
        ];


        //Consulta de Información básica de los avisos
        $functionName = 'ZSGDEA_CONSULTA_AVISOS';
        $parameters = [
            'I_FECHA_INI' => '20240101',
            'I_HORA_INI' => '010101',
            'I_FECHA_FIN' => '20240131',
            'I_HORA_FIN' => '235959',
        ];


        // PROBADO
        //Consultar detalle del interlocutor
        $functionName = 'ZPM_DETALLE_INTERLOCUTOR';
        $parameters = [
            'CTA_CONTRATO' => '',  //11557160
            'INTERLOCUTOR' => '',  //10215971
        ];
        
        
        // NO FUNCIONA LA FUNCION
        //Busqueda de agrupacion de estructura regional
        $functionName = 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA';
        $parameters = [
            'ZCALLE' => '3',  
            'ZHOUSE1' => '2',  
            'ZQMART' => '',  
            'ZCIUDAD' => 'BOGOTA',  
        ];
        
        */

        $result = $this->sapService->callRFC($functionName, $parameters);

        // Cierra la conexión después de hacer la llamada
        $this->sapService->close();

        return response()->json($result);
    }


    public function saprfc(Request $request)
    {
        try{

            if (empty($request->input('functionName')))  return $this->statusHttp(400); 
            if (empty($request->input('parameters')))  return $this->statusHttp(400); 

            $functionName = $request->input('functionName');
            $parameters = $request->input('parameters');

            

            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serversaprfc',
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


}
