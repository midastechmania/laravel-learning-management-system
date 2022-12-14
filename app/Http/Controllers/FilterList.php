<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Association;
use App\Models\Client;
use App\Models\Division;
use App\Models\District;
use App\Models\Area;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;



class FilterList extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	
	public function __get_json_up($request)
	{
		$stores = [];
		$areas = [];
		$divisions = [];
		$districts = [];
		$clients = [];
		$associations = [];

		if(!empty($request->store)){
			$arr = preg_split ("/\,/", $request->store);
			foreach ($arr as $key => $value) {
				array_push($stores, (int)$value);
			}
		}

		if(!empty($request->area)){
			$arr = preg_split ("/\,/", $request->area);
			foreach ($arr as $key => $value) {
				array_push($areas, (int)$value);
			}
		}

		if(!empty($request->district)){
			$arr = preg_split ("/\,/", $request->district);
			foreach ($arr as $key => $value) {
				array_push($districts, (int)$value);
			}
		}

		if(!empty($request->division)){
			$arr = preg_split ("/\,/", $request->division);
			foreach ($arr as $key => $value) {
				array_push($divisions, (int)$value);
			}
		}

		if(!empty($request->client)){
			$arr = preg_split ("/\,/", $request->client);
			foreach ($arr as $key => $value) {
				array_push($clients, (int)$value);
			}
		}

		if(!empty($request->association)){
			$arr = preg_split ("/\,/", $request->association);
			foreach ($arr as $key => $value) {
				array_push($associations, (int)$value);
			}
		}	

		foreach ($areas as $key => $value) {
			$res = Store::where('apr', $value)->get();
			if(!empty($res))	
			{
				foreach ($res as $key => $value) {
					array_push($stores, (int)$value->id);
				}
			}	
		}

		foreach ($stores as $key => $value) {
			$res = Store::where('id', $value)->first();
			if(!empty($res))		
				array_push($districts, (int)$res->district_id);
		}

		$districts = array_unique($districts);
		
		foreach ($districts as $key => $value) {
			$res = District::where('id', $value)->first();
			if(!empty($res)){
				array_push($divisions, (int)$res->division_id);
			}
		}
		$divisions = array_unique($divisions);

		foreach ($divisions as $key => $value) {
			$res = Division::where('id', $value)->first();
			if(!empty($res)){
					array_push($clients, (int)$res->client_id);
			}
		}
		$clients = array_unique($clients);

		foreach ($clients as $key => $value) {
			$res = Client::where('id', $value)->first();
			if(!empty($res))
					array_push($associations, (int)$res->association_id);
		}
		$associations = array_unique($associations);

	
		return array(
			'Associate' => $associations,
			'Client'=> $clients,
			'Division'=> $divisions,
			'District'=> $districts,
			'Area'=> $areas,
			'Store'=> $stores
		);	
    }
	
	public function __get_json_down($request)
    {		
		// downgrade
		$down_stores = [];
		$down_areas = [];
		$down_divisions = [];
		$down_districts = [];
		$down_clients = [];
		$down_associations = [];

		if(!empty($request->association)){
			$arr = preg_split ("/\,/", $request->association);
			
			foreach ($arr as $key => $value) {
				array_push($down_associations, (int)$value);
				$association = Association::where('id', $value)->first();
				if(empty($association)) continue;
				$clients = $association->client;
				if(empty($clients)) continue;

				foreach ($clients as $key => $client) {
					array_push($down_clients, (int)$client->id);
					$divisions = $client->division;
					if(empty($divisions)) continue;					
			
					foreach ($divisions as $key => $division) {
						array_push($down_divisions, (int)$division->id);
						// $district = $value->division;
						$district = District::where('division_id', $division->id)->get();
						if(empty($district)) continue;
			
						foreach ($district as $key => $value) {
							array_push($down_districts, (int)$value->id);
							$div = Store::where('district_id', $value->id)->get();
							if(empty($div)) continue;
			
							foreach ($div as $key => $value) {
								array_push($down_stores, (int)$value->id);
								$div = Area::where('id', $value->apr)->first();
								if(!empty($div))
								array_push($down_areas, (int)$div->id);
							}
						}
					}
				}
			}
		}
		
		if(!empty($request->client)){
			$cli = preg_split ("/\,/", $request->client);
			foreach ($cli as $key => $client) {
				array_push($down_clients, (int)$client);
				$res = Client::where('id', $client)->first();
				if(!empty($res))
					$div = Division::where('client_id', $res->id)->get();
					if(!empty($div)){
						foreach ($div as $key => $value) {
							array_push($down_divisions, (int)$value->id);
							$div = District::where('division_id', $value->id)->get();
							if(!empty($div)){
								foreach ($div as $key => $value) {
									array_push($down_districts, (int)$value->id);
									$div = Store::where('district_id', $value->id)->get();
									if(!empty($div)){
										foreach ($div as $key => $value) {
											array_push($down_stores, (int)$value->id);
											$div = Area::where('id', $value->apr)->first();
											if(!empty($div))
											array_push($down_areas, (int)$div->id);
										}
									}
								}
							}
						}
					}
				}
			}
		
			
		if(!empty($request->division)){
			$div = preg_split ("/\,/", $request->division);
			foreach ($div as $key => $division) {
				array_push($down_divisions, (int)$division);
				$value = Division::where('id', $division)->first();
				if(!empty($value)){
					$div = District::where('division_id', $value->id)->get();
					if(!empty($div)){
						foreach ($div as $key => $value) {
							array_push($down_districts, (int)$value->id);
							$div = Store::where('district_id', $value->id)->get();
							if(!empty($div)){
								foreach ($div as $key => $value) {
									array_push($down_stores, (int)$value->id);
									$div = Area::where('id', $value->apr)->first();
									if(!empty($div))
									array_push($down_areas, (int)$div->id);
								}
							}
						}
					}
				}
			}
		}
		
		if(!empty($request->district)){
			$div = preg_split ("/\,/", $request->district);
			foreach ($div as $key => $district) {
				array_push($down_districts, (int)$district);
					$div = District::where('id', $district)->first();
					if(!empty($div)){
						$div = Store::where('district_id', $div->id)->get();
						if(!empty($div)){
							foreach ($div as $key => $value) {
								array_push($down_stores, (int)$value->id);
								$div = Area::where('id', $value->apr)->first();
								if(!empty($div))
								array_push($down_areas, (int)$div->id);
							}
						}
					}
				}
			}
			
		if(!empty($request->store)){
			$div = preg_split ("/\,/", $request->store);
			foreach ($div as $key => $store) {
				array_push($down_stores, (int)$store);
					$value = Store::where('id', $store)->first();
					if(!empty($value)){
						$div = Area::where('id', $value->apr)->first();
						if(!empty($div))
						array_push($down_areas, (int)$div->id);
					}
				}
			}


		return array(
			'Associate' => $down_associations,
			'Client'=> $down_clients,
			'Division'=> $down_divisions,
			'District'=> $down_districts,
			'Area'=> $down_areas,
			'Store'=> $down_stores
		);	
    }

	public function __get_json($request)
	{
		$down = FilterList::__get_json_down($request);
		$up = FilterList::__get_json_up($request);
		return array(
			'Associate' => array_unique(array_merge($down["Associate"], $up["Associate"])),
			'Client'=> array_unique(array_merge($down["Client"], $up["Client"])),
			'Division'=> array_unique(array_merge($down["Division"], $up["Division"])),
			'District'=> array_unique(array_merge($down["District"], $up["District"])),
			'Area'=> array_unique(array_merge($down["Area"], $up["Area"])),
			'Store'=> array_unique(array_merge($down["Store"], $up["Store"])),
		);	
	}

	public function __append_name($req)
	{
		$res = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);

		foreach ($req['Associate'] as $key => $value) {
			$association = Association::where('id', $value)->first();
			if(!empty($association)){ 
				$res['Associate'][$value] = $association->name;
			}
		}
		foreach ($req['Client'] as $key => $value) {
			$client = Client::where('id', $value)->first();
			if(!empty($client)){ 
				$res['Client'][$value] = $client->name;
			}
		}
		foreach ($req['Division'] as $key => $value) {
			$division = Division::where('id', $value)->first();
			if(!empty($division)){ 
				$res['Division'][$value] = $division->name;
			}
		}
		foreach ($req['District'] as $key => $value) {
			$district = District::where('id', $value)->first();
			if(!empty($district)){ 
				$res['District'][$value] = $district->number;
			}
		}
		foreach ($req['Area'] as $key => $value) {
			$area = Area::where('id', $value)->first();
			if(!empty($area)){ 
				$res['Area'][$value] = $area->title;
			}
		}
		foreach ($req['Store'] as $key => $value) {
			$store = Store::where('id', $value)->first();
			if(!empty($store)){ 
				$res['Store'][$value] = $store->name;
			}
		}
		return $res;
	}

    public function __invoke(Request $request)
    {		
		$result = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);

		$ret_stores = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);
		$ret_areas = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);
		$ret_divisions = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);
		$ret_districts = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);
		$ret_clients = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);
		$ret_associations = array('Associate' => [], 'Client'=> [], 'Division'=> [], 'District'=> [], 'Area'=> [], 'Store'=> []);

		function __intersect($ret, $res, $state)
		{
			if($state == false){
				$res['Associate'] = $ret['Associate'];
				$res['Client'] = $ret['Client'];
				$res['Division'] = $ret['Division'];
				$res['District'] = $ret['District'];
				$res['Area'] = $ret['Area'];
				$res['Store'] = $ret['Store'];
			}
			else{	
				$res['Associate'] = array_intersect($ret['Associate'], $res['Associate']);
				$res['Client'] = array_intersect($ret['Client'], $res['Client']);
				$res['Division'] = array_intersect($ret['Division'], $res['Division']);
				$res['District'] = array_intersect($ret['District'], $res['District']);
				$res['Area'] = array_intersect($ret['Area'], $res['Area']);
				$res['Store'] = array_intersect($ret['Store'], $res['Store']);
			}
			return $res;
		}

		function __json_encode($ret)
		{
			$ret['Associate'] = array_values($ret['Associate']);
			$ret['Client'] = array_values($ret['Client']);
			$ret['Division'] = array_values($ret['Division']);
			$ret['District'] = array_values($ret['District']);
			$ret['Area'] = array_values($ret['Area']);
			$ret['Store'] = array_values($ret['Store']);
			return $ret;
		}

		$state = false;
		if(!empty($request->store)){
			$ret_stores = FilterList::__get_json((object)['store' => $request->store]);	
			$result = __intersect($ret_stores, $result, $state);
			$state = true;
		}

		if(!empty($request->area)){
			$ret_areas = FilterList::__get_json((object)['area' => $request->area]);	
			$result = __intersect($ret_areas, $result, $state);
			$state = true;
		}

		if(!empty($request->district)){
			$ret_districts = FilterList::__get_json((object)['district' => $request->district]);	
			$result = __intersect($ret_districts, $result, $state);
			$state = true;
		}

		if(!empty($request->division)){
			$ret_divisions = FilterList::__get_json((object)['division' => $request->division]);	
			$result = __intersect($ret_divisions, $result, $state);
			$state = true;
		}

		if(!empty($request->client)){
			$ret_clients = FilterList::__get_json((object)['client' => $request->client]);	
			$result = __intersect($ret_clients, $result, $state);
			$state = true;
		}

		if(!empty($request->association)){
			$ret_associations = FilterList::__get_json((object)['association' => $request->association]);
			$result = __intersect($ret_associations, $result, $state);
			$state = true;
		}	
		return Response::json(FilterList::__append_name($result));
		// return Response::json(__json_encode($result));
    }
}
