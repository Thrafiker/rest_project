<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


use App\Fabricante;
use App\Vehiculo;



class FabricanteVehiculoController extends Controller {


	/** =============================================
	Creando mi middleware de autentificacion para algunos metodos URL
	============================================= */
	public function __construct(){

		// Authentication basic
		// $this->middleware('auth.basic.once', ['only' => ['store', 'update', 'destroy']]  );

		// Authentication OAUTH
		$this->middleware('oauth', ['only' => ['store', 'update', 'destroy']]  );
	}


	/** http://localhost/curso%20laravel/rest_project/server.php/fabricantes/1/vehiculos
	 * Display a listing of the resource.
	 * Mostrar una lista del recurso.
	 * 
	 * Regresa los vehiculos de un fabricante
	 *
	 * @return Response
	 */
	public function index($id){

		$fabricante = Fabricante::find($id);

		if(!fabricante){
			
			return response()->json([
				'menssage' => "Not found manufacturer",
				"error" => True
				], 404);
		}else{
			// 2 forma de hacerlo 
			// return response()->json(['data' => $fabricante->vehiculos()->get()], 200);

			return response()->json([
				'data' => $fabricante->vehiculos,
				"error" => False
				], 200);

		}
		
	}


	/** http://localhost/curso%20laravel/rest_project/server.php/fabricantes/1/vehiculos
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id){ // ...fabricantes/{id}

		// Campos de vehiculo:  'color', 'cilindraje', 'potencia', 'peso', 'fabricante_id'

		/** ========================================
		Campos/ fields de Postman
		======================================== */
		if( !$request-> input('color') || 
			!$request-> input('cilindraje') || 
			!$request-> input('potencia') || 
			!$request-> input('peso') ){

			return response()->json([
				'menssage' => "Complete required fields",
				"error" => True
				], 422);
		}

		$fabricante = Fabricante::find( $request->input($id) );
		if( !$fabricante){
			return response()->json([
				'menssage' => "There is no manufacturer",
				"error" => True
				], 422);
		
		}

		$fabricante->vehiculos()->create( $request->all() );
		
		return response()->json([
			'menssage' => 'successful',
			'error' => False,
			'data_fabricante' => $fabricante,
			'data_vehiculo' => $fabricante->vehiculos()
			], 200);

	}


	/** http://localhost/curso%20laravel/rest_project/server.php/fabricantes/3/vehiculos/5
	*
	* Update the specified resource in storage.
	*
	*	SOLO SI ese Vehiculo {id} tiene ese Fabricante {id}
	*
	* @param  int  $idFabricante, $idVehiculo
	* @return Response
	*/
	public function update(Request $request, $idFabricante, $idVehiculo){

		$metodo = $request->method();
		$fabricante = Fabricante::find($idFabricante);
		
		if(!$fabricante){
			return response()->json([
				'menssage' => "Not found manufacturer",
				"error" => True
				], 404);
		}


		$vehiculo = $fabricante->vehiculos()->find($idVehiculo);
		if(!$vehiculo){
			return response()->json([
				'menssage' => "This vehicle is not found associated with this manufacturer.", 
				"error" => True
				], 404);
		}

		$color = $request->input('color');
		$cilindraje = $request->input('cilindraje');
		$potencia = $request->input('potencia');
		$peso = $request->input('peso');
		
		if($metodo === 'PATCH'){

			$bandera = false;

			if($color != null && $color != ''){
				$vehiculo->color = $color;
				$bandera = true;
			}

			if($cilindraje != null && $cilindraje != ''){
				$vehiculo->cilindraje = $cilindraje;
				$bandera = true;
			}

			if($potencia != null && $potencia != ''){
				$vehiculo->potencia = $potencia;
				$bandera = true;
			}

			if($peso != null && $peso != ''){
				$vehiculo->peso = $peso;
				$bandera = true;
			}

			if($bandera){
				$vehiculo->save();
				return response()->json(['message' => 'vehiculo editado'],200);
			}
			
			return response()->json(['message' => 'None vehicle was modified'], 200);	
		}

		// es PUT

		if(!$color || !$cilindraje || !$potencia || !$peso){
			return response()->json([
				'message' => 'Incomplete fields', 
				'error' => True
				],422);
		}

		$vehiculo->color = $color;
		$vehiculo->cilindraje = $cilindraje;
		$vehiculo->potencia = $potencia;
		$vehiculo->peso = $peso;
		$vehiculo->save();
		
		return response()->json(['mensaje' => 'Vehiculo editado'],200);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
 	public function destroy($idFabricante, $idVehiculo){
		
		$fabricante =Fabricante::find($idFabricante);

		if(!$fabricante)
			return response()->json([
				'message' => 'No se encuentra este fabricante', 
				'error' => True
				],404);
		
		$vehiculo = $fabricante->vehiculos()->find($idVehiculo);
		
		if(!$vehiculo)
			return response()->json([
				'message' => 'No se encuentra este vehiculo asociado a ese fabricante',
				'error' => True
				],404);
		
		$vehiculo->delete();
		return response()->json(['message' => 'Vehiculo eliminado'], 200);
	}

}
