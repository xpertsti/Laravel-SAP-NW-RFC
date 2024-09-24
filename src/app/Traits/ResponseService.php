<?php
namespace App\Traits;

use Illuminate\Support\Facades\Request;

trait ResponseService {

    /**
     * Retorna una respuesta JSON con el estado HTTP especificado y datos opcionales.
     *
     * @param int $option   Opción que indica el código de estado HTTP (404, 200, 500, etc.).
     * @param mixed $data   Datos opcionales que se incluirán en la respuesta JSON (puede ser nulo).
     * @param Request $request   Objeto Request opcional para incluir en la respuesta JSON (puede ser nulo).
     * @param string $message   Mensaje opcional que se incluirá en la respuesta JSON (puede ser nulo).
     * @param bool|null $status   Estado opcional que indica el estado de la respuesta (puede ser nulo).
     * @return \Illuminate\Http\JsonResponse   Respuesta JSON con el estado y datos especificados.
     */
    public function statusHttp($option, $data=null, $message=null, $request=null, $status=null)
    {
        switch ($option)
        {
            case 404:
               return  response()->json([
                    'status'  => is_null($status)?false:$status,
                    'message' => is_null($message)?'Recurso no encontrado':$message,
                ], 404);
                break;
            case 200:
               return  response()->json([
                    'status'  => is_null($status)?true:$status,
                    'data'    => $data, 
                    //'data'    => HelperEncryptAES::encrypt($request, $data, true),
                    'message' => is_null($message)?'success':$message,
                ], 200);
                break;
            case 204:
               return  response()->json([
                    'status'  => is_null($status)?true:$status,
                    'data'    => $data, 
                    //'data'    => HelperEncryptAES::encrypt($request, $data, true),
                    'message' => is_null($message)?'success:solicitud se ha procesado correctamente pero no hay contenido que devolver o no se realizaron cambios significativos.':$message,
                ], 204);
                break;
            case 500:
                return response()->json([
                    'status'  => is_null($status)?false:$status,
                    //'message' => 'Error: ' . !is_null($message)?$message:$data->getMessage()
                    'message' => 'Error: ' . $data->getMessage()
                ], 500);
                break;
            case 400:
                return response()->json([
                        'status'  =>  is_null($status)?false:$status,
                        'message' =>  is_null($message)?'Error(400 datos faltantes)':$message,
                ], 400);
                break;
            case 401:
                return response()->json([
                    'status'  =>  is_null($status)?false:$status,
                    'message' =>  is_null($message)?'Usuario no Autorizado (401 Unauthorized)':$message,
                ], 401);
                break;
            case 403:
                return response()->json([
                    'status'  =>  is_null($status)?false:$status,
                    'message' =>  is_null($message)?'Usuario no Autorizado (403 Prohibido)':$message,
                ], 403);
                    break;
            case 'paginate':
                return response()->json([
                    'status' => true,
                    'data' => [],
                    'links' => [],
                    'meta' => [
                        'current_page' => 1,
                        'from' => null,
                        'last_page' => 1,
                        'path' => $data['url'],
                        'per_page' => $data['per_page'],
                        'to' => null,
                        'total' => 0,
                    ],
                    'message' => 'No se encontraron resultados',
                ], 200);
            default :
                error_log('Log default', LOG_INFO);
        }
    }
}