<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::all();
        return $marcas;
    }

    public function store(Request $request)
    {
        $marca = new Marca();
        $marca->mrc_nombre = $request->mrc_nombre;
        $marca->mrc_descripcion = $request->mrc_descripcion;

        // storing image
        if ( $request->mrc_imagen ) {
            $rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
            $folder_destination = '/images/logos_marcas/';
            $file = $request->mrc_imagen;

            // file name corrections
            $file_extension = $file->getClientOriginalExtension();
            $file_name_modified = str_replace('ñ', 'n', 'logo_' . $request->name);
            $file_name_modified = str_replace(' ', '_', $file_name_modified);
            $file_name_modified = strtolower($file_name_modified . '.' . $file_extension);

            $file->move($rootDir.$folder_destination, $file_name_modified );
            $file_path = config('app.domainUrl.urlApiPublic') . $folder_destination . $file_name_modified;
            $marca->mrc_image_path = $file_path;
        }

        $marca->save();

        return response()->json([
            'status' => 1,
            'msg' => 'registro de marca exitosa',
            'data' => $marca
        ], 200);
    }

    public function show($id)
    {
        $marca = Marca::find($id);
        return $marca;
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);
        $marca->mrc_nombre = $request->mrc_nombre;
        $marca->mrc_descripcion = $request->mrc_descripcion;

        // actualizando imagen
        if ( $request->mrc_imagen ) {
            $rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
            $folder_destination = '/images/logos_marcas/';
            $file = $request->mrc_imagen;

            // file name corrections
            $file_extension = $file->getClientOriginalExtension();
            $file_name_modified = str_replace('ñ', 'n', 'logo_' . $request->name);
            $file_name_modified = str_replace(' ', '_', $file_name_modified);
            $file_name_modified = strtolower($file_name_modified . '.' . $file_extension);

            //delete previous file
            try{
                $previousFilePath = $marca->mrc_image_path;
                $previousFilePath = str_replace(env("URL_API_PUBLIC").'/', '', $previousFilePath);
                File::delete($previousFilePath);
            }catch(Throwable $e){
                report($e);
            }

            $file->move($rootDir.$folder_destination, $file_name_modified );
            $file_path = config('app.domainUrl.urlApiPublic') . $folder_destination . $file_name_modified;
            $marca->mrc_image_path = $file_path;
        }

        $marca->save();

        return $marca;
    }

    public function destroy($id)
    {
        $marca = Marca::destroy($id);
        return $marca;
    }
}
