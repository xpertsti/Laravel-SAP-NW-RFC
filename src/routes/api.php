<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\SapController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



    Route::prefix('v1')->group(function () {
        Route::prefix('saprfc')->group(function () {
            Route::post('avisos/consulta', [SapController::class, 'ZSGDEA_CONSULTA_AVISOS']);
            Route::get('personal/habilitado/{iCentroCosto}', [SapController::class, 'ZSGDEA_PERSONAL_HABILITADO']);
            Route::get('cuenta-contrato/detalles/{cuentaContrato}', [SapController::class, 'ZSGDEA_DETALLES_CTA_CONTRATO']);
            Route::get('aviso/detalles/{iNumero}', [SapController::class, 'ZSGDEA_DETALLE_AVISO']);
            Route::get('medidas/{iFechaIni}/{iFechaFin}', [SapController::class, 'ZSGDEA_CONSULTA_MEDIDAS']);
            Route::get('interlocutor/detalles', [SapController::class, 'ZPM_DETALLE_INTERLOCUTOR']);
            Route::post('zona-grupo-planifica/consulta', [SapController::class, 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA']);
            Route::post('solicitudes/consulta', [SapController::class, 'ZSGDEA_CONSULTA_SOLICITUDES']);
            Route::post('contacto/crear', [SapController::class, 'ZSGDEA_CREAR_CONTACTO']);
            Route::patch('contacto/actualizar', [SapController::class, 'ZSGDEA_ACTUALIZAR_CONTACTO']);
        });

        Route::prefix('test')->group(function () {
            Route::get('conexion', [SapController::class, 'index']);
            //Route::post('v1/saprfc', [SapController::class, 'saprfc']);
        });

    });

