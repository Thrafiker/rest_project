<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// Casi todas en MAYUS al inicio por ser clases

# use App\Fabricante as FabricanteModel; 
use App\Fabricante;

class FabricanteController extends Controller {



	/** =============================================
	Creando mi middleware de autentificacion para algunos metodos URL
	============================================= */
	public function __construct(){

		// Authentication basic
		// $this->middleware('auth.basic.once', ['only' => ['store', 'update', 'destroy']]  );

		// Authentication OAUTH
		$this->middleware('oauth', ['only' => ['store', 'update', 'destroy']]  );
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return JSON
	 */
	public function index(){
		
		// Respuestas en CACHE
		$fabricante_cache = Cache::remember('fabricantes', 15/60, function(){

			// return Fabricante::all();

			/** =======================================
			paginate para paginas y no REST
			Paginar cada 15 elementos

			ACCESO URL  ?page=2 (num de pag)

			http://localhost/curso%20laravel/rest_project/server.php/fabricantes?page=2

			======================================= */
			return Fabricante::simplePaginate(15); 

		});

		
		//return response()->json(['data' => $fabricante_cache], 200);

		// Con paginacion
		return response()->json([
			'data' => $fabricante_cache->items(),
			'previous' => $fabricante_cache->previousPageUrl(),
			'next' => $fabricante_cache->nextPageUrl()
			], 200);


		// antes Responce::JSON
		// respuesta sin cache
		//return response()->json(['data' => Fabricante::all()], 200);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/** http://localhost/curso%20laravel/rest_project/server.php/fabricantes/
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store( Request $request ){

		if( !$request-> input('nombre') || 
			!$request-> input('telefono') || 
			!$request-> input('tipo') || 
			!$request-> file('foto')  ){

			return response()->json([
				'menssage' => "Complete required fields",
				"error" => True
				], 422);
		}

		// Fabricante::create( $request->all() );
		$fabricante = new Fabricante( $request->input() );

		$this->validate($request, [
			'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		]);

		if($file = $request->hasFile('foto')) {

			$file = $request->file('foto') ;
			$fileName = $file->getClientOriginalName() ;
			
			$destinationPath = public_path().'/storage/' ;
			$file->move($destinationPath, $fileName);
			$fabricante->foto = $fileName ;
		}

		$fabricante->save() ;

		return response()->json([
			'menssage' => "successful",
			"error" => False, 
			'data' => $fabricante
			], 200);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id){
		$fabricante = Fabricante::find($id);
		
		if(!$fabricante){
			return response()->json([
				'menssage' => "Not found manufacturer",
				"error" => True
				], 404);
		}else{
			return response()->json([
				'menssage' => $fabricante,
				'error' => False,
				// 'image_url' => URL::to('/').'/public/image/'.$fabricante->foto
				'foto' => Storage::url('uam.jpg'),
				], 200);
		}
	}


	/**
		Obtener la imagen de un fabricante por su id
	*/
	public function obtener_foto($id){
		$fabricante = Fabricante::find($id);
		
		if(!$fabricante){
			return response()->json([
				'menssage' => "Not found manufacturer",
				"error" => True
				], 404);
		}else{
			
	      $public_path = public_path();
     		$url = $public_path.'/storage/'.$fabricante->foto;

	      return response()->download($url);
		}
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * solo modifica el nombre de foto, no vuelve a cargarla
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id){

		/** ========================================
			PUT actualizar conjunto completo 
			PATCH obtener y sustituir solo 1 elemento
		======================================== */
		
		$fabricante = Fabricante::find($id);

		if(!$fabricante){
			return response()->json([
				'menssage' => "Not found manufacturer",
				"error" => True
				], 404);
		}else{

			$metodo = $request->method();

			if( $method === 'PATCH' ){

				$nombre = $request->input('nombre');
				if($nombre != null && $nombre != '')
					$fabricante->nombre = $nombre;
 
				$telefono = $request->input('telefono');

				if($telefono != null && $telefono != '')
					$fabricante->telefono = $telefono;
				
				$tipo = $request->input('tipo');
				if($tipo != null && $tipo != '')
					$fabricante->tipo = $tipo;
 
				$foto = $request->input('foto');

				if($foto != null && $foto != '')
					$fabricante->foto = $foto;

			
				$fabricante->save();
													
				return response()->json([
					'menssage' => 'successful update',
					'data' => $fabricante,
					'error' => False
					], 200);
					
			}else{ // PUT
				
				$nombre = $request->input('nombre');
				$telefono = $request->input('telefono');
				$tipo = $request->input('tipo');
				$foto = $request->input('foto');


				if(!$nombre || !$telefono || !$tipo || !$foto )
					return response()->json([
						'mensaje' => 'No se pudieron procesar los valores', 
						'error' => True
						],422);

				$fabricante->nombre = $nombre;
				$fabricante->telefono = $telefono;
				$fabricante->tipo = $tipo;
				$fabricante->foto = $foto;

				$fabricante->save();
				
				return response()->json([
					'mensaje' => 'Fabricante editado',
					'error' => False
					], 200);
					
			}
			
		}

	}



	private function procesa_patch($val_input){
	if( $val_input != null && $val_input != '' ){
			$nuevo_val->nombre = $request->input('nombre');
			return $nuevo_val;
	}else{
		return;
	}

	}



	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id){
		$fabricante = Fabricante::find($id);
 
		if(!$fabricante)
			return response()->json([
				'message' => 'No se encuentra este fabricante',
				'error' => True
				],404);
			
		$vehiculos = $fabricante->vehiculos;

		if(sizeof($vehiculos) > 0)
			return response()->json([
				'message' => 'Este fabricante posee vehiculos asociados, elimine primero sus vehiculos.', 
				'error' => True
				],409);

		$fabricante->delete();

		return response()->json(['message' => 'Fabricante eliminado'],200);
	}

}
