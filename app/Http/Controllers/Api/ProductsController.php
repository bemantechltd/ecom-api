<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\ProductsCollection as ProductsResource;
use App\Http\Resources\ProductSingleCollection as ProductSingleResource;

use App\Models\Products;
use App\Models\Generics;
use App\Models\DiseaseInfos;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use Cache;
use Auth;
use DB;

class ProductsController extends Controller
{
    protected $cache_tag_name = 'products';
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function existCheck(Products $obj, Request $request){
        $getData = $obj::where('slug', $request['slug'])->whereNull('deleted_at')->first();

        if(!empty($getData)) return response()->json(['status' => true], 200);
        else return response()->json(['status' => false], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Products $obj, Request $request)
    {
        // return $request->all();

        // return ModificationController::save_content($obj, $request);
        return $this->save_content($obj, $request);
    }

    /**
     * MULTI DATA RELATIONSHIP CONTENT
     */
    protected $getGenericIds = [], $getDiseaseIds = [], $getTagIds = [];
    protected function multi_data_management($data, $user_id){
        /**
         * Generics item test
         */
        $this->getGenericIds = [];
        if(!empty($data['generics'])){
            try{
                $QryStr = 'INSERT INTO `generics`(generic_name,slug,status,created_by,updated_by,created_at,updated_at) VALUES';
                $co=0; foreach($data['generics'] as $key => $val){
                    if($co++>0) $QryStr .= ',';
                    $QryStr .= '("'.($val).'","'.str_replace(' ','-',trim(strtolower($val))).'",1,'.$user_id.','.$user_id.',"'.date('Y-m-d H:i:s').'","'.date('Y-m-d H:i:s').'")';
                }
                $QryStr .= ' ON DUPLICATE KEY UPDATE generic_name=VALUES(generic_name)';
                DB::select($QryStr);

                $this->getGenericIds = Generics::select('id')->whereIn('generic_name',$data['generics'])->get();
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return response()->json(['error' => $e, 'msg' => 'Product generic info didn\'t submit properly', 'status' => false], 200);
            }
        }

        /**
         * Disease item test
         */
        $this->getDiseaseIds = [];
        if(!empty($data['disease'])){
            try{
                $QryStr = 'INSERT INTO `disease_infos`(disease_title,slug,status,created_by,updated_by,created_at,updated_at) VALUES';
                $co=0; foreach($data['disease'] as $key => $val){
                    if($co++>0) $QryStr .= ',';
                    $QryStr .= '("'.($val).'","'.str_replace(' ','-',trim(strtolower($val))).'",1,'.$user_id.','.$user_id.',"'.date('Y-m-d H:i:s').'","'.date('Y-m-d H:i:s').'")';
                }
                $QryStr .= ' ON DUPLICATE KEY UPDATE disease_title=VALUES(disease_title)';
                DB::select($QryStr);

                $this->getDiseaseIds = DiseaseInfos::select('id')->whereIn('disease_title',$data['disease'])->get();
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return response()->json(['error' => $e, 'msg' => 'Product disease info didn\'t submit properly', 'status' => false], 200);
            }
        }

        /**
         * Tags item test
         */
        $this->getTagIds = [];
        if(!empty($data['tags'])){
            try{
                $QryStr = 'INSERT INTO `tags`(tag_title,status,created_by,updated_by,created_at,updated_at) VALUES';
                $co=0; foreach($data['tags'] as $key => $val){
                    if($co++>0) $QryStr .= ',';
                    $QryStr .= '("'.($val).'",1,'.$user_id.','.$user_id.',"'.date('Y-m-d H:i:s').'","'.date('Y-m-d H:i:s').'")';
                }
                $QryStr .= ' ON DUPLICATE KEY UPDATE tag_title=VALUES(tag_title)';
                DB::select($QryStr);

                $this->getTagIds = Tags::select('id')->whereIn('tag_title',$data['tags'])->get();
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return response()->json(['error' => $e, 'msg' => 'Product tag info didn\'t submit properly', 'status' => false], 200);
            }
        }
    }

    protected function more_featured_management($obj,$data,$req_id,$action){
        /**
         * Remove product category data first
         */
        try{
            if($action=='update') DB::select('DELETE FROM `product_cat_infos` WHERE product_id='.$req_id);

            /**
             * Save to product category table
             */
            $productCatQry = 'INSERT INTO `product_cat_infos`(product_id,product_cat_id) VALUES';
            $co=0; foreach($data['cat_id'] as $key => $val){
                if($co++>0) $productCatQry .= ',';
                $productCatQry .= '('.$req_id.','.$key.')';
            }
            $obj->cat_info =  DB::select($productCatQry);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product category didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Product Company Information
         */
        try{
            /**
             * Remove product company first
             */
            if($action=='update') DB::select('DELETE FROM `product_company_infos` WHERE product_id='.$req_id);

            /**
             * Save to product company table
             */
            if(@$data['company_id']>0){
                $productCompanyQry = 'INSERT INTO `product_company_infos`(product_id,product_company_id) VALUES('.$req_id.','.$data['company_id'].')';
                $obj->company_info =  DB::select($productCompanyQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product company didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Product Type Information
         */
        try{
            /**
             * Remove product type first
             */
            if($action=='update') DB::select('DELETE FROM `product_type_infos` WHERE product_id='.$req_id);

            /**
             * Save to product type table
             */
            if(@$data['product_type_id']>0){
                $productTypeQry = 'INSERT INTO `product_type_infos`(product_id,product_type_id) VALUES('.$req_id.','.$data['product_type_id'].')';
                $obj->product_type_info =  DB::select($productTypeQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product type didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Product details Information
         */
        try{
            /**
             * Remove product infos first
             */
            if($action=='update') DB::select('DELETE FROM `product_infos` WHERE product_id='.$req_id);

            /**
             * Save to product infos table
             */
            if(!empty($data['product_infos'])){
                $productInfoQry = 'INSERT INTO `product_infos`(product_id,product_info_type_id,content) VALUES';
                $co=0; foreach($data['product_infos'] as $key => $val){
                    if($co++>0) $productInfoQry .= ',';
                    $productInfoQry .= '('.$req_id.','.$val['id'].',"'.addslashes($val['content']).'")';
                }
                $obj->product_infos =  DB::select($productInfoQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product information didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Product price Information
         */
        try{
            /**
             * Remove product price infos first
             */
            if($action=='update') DB::select('DELETE FROM `product_price_infos` WHERE product_id='.$req_id);

            /**
             * Save to product price infos table
             */
            if(!empty($data['product_price_infos'])){
                $productPriceInfoQry = 'INSERT INTO `product_price_infos`(product_id,product_price_type_id,price,discount_price,remarks,min_qty,max_qty) VALUES';
                $co=0; foreach($data['product_price_infos'] as $key => $val){
                    if($co++>0) $productPriceInfoQry .= ',';
                    $productPriceInfoQry .= '('.$req_id.','.$val['id'].','.$val['price'].','.(@$val['discount_price']?$val['discount_price']:'NULL').',"'.$val['remarks'].'",'.($val['min_qty']==null?'NULL':(int)$val['min_qty']).','.($val['max_qty']==null?'NULL':(int)$val['max_qty']).')';
                }                
                $obj->product_price_infos =  DB::select($productPriceInfoQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product price didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Product photo Information
         */
        try{
            /**
             * Remove product photo infos first
             */
            if($action=='update') DB::select('DELETE FROM `product_photo_infos` WHERE product_id='.$req_id);

            /**
             * Save to product photo infos table
             */
            if(!empty($data['product_photo_infos'])){
                $productPhotoInfoQry = 'INSERT INTO `product_photo_infos`(product_id,product_photo_id) VALUES';
                $co=0; foreach($data['product_photo_infos'] as $key => $val){
                    if($co++>0) $productPhotoInfoQry .= ',';
                    $productPhotoInfoQry .= '('.$req_id.','.$val['id'].')';
                }
                $obj->product_photo_infos =  DB::select($productPhotoInfoQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product photo didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Save product generics data
         */
        try{
            /**
             * Remove product tags data first
             */
            if($action=='update') DB::select('DELETE FROM `product_generic_infos` WHERE product_id='.$req_id);

            /**
             * Save to product generics table
             */
            if(!empty($this->getGenericIds)){
                $productGenericsQry = 'INSERT INTO `product_generic_infos`(product_id,product_generic_id) VALUES';
                $co=0; foreach($this->getGenericIds as $key => $val){
                    if($co++>0) $productGenericsQry .= ',';
                    $productGenericsQry .= '('.$req_id.','.$val['id'].')';
                }
                $obj->generic_infos = DB::select($productGenericsQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product generic info didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Save product disease data
         */
        try{
            /**
             * Remove product disease data first
             */
            if($action=='update') DB::select('DELETE FROM `product_disease_infos` WHERE product_id='.$req_id);

            /**
             * Save to product disease table
             */
            if(!empty($this->getDiseaseIds)){
                $productDiseaseQry = 'INSERT INTO `product_disease_infos`(product_id,product_disease_id) VALUES';
                $co=0; foreach($this->getDiseaseIds as $key => $val){
                    if($co++>0) $productDiseaseQry .= ',';
                    $productDiseaseQry .= '('.$req_id.','.$val['id'].')';
                }
                $obj->disease_infos = DB::select($productDiseaseQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product disease info didn\'t submit properly', 'status' => false], 200);
        }

        /**
         * Save product tags data
         */
        try{
            /**
             * Remove product tags data first
             */
            if($action=='update') DB::select('DELETE FROM `product_tag_infos` WHERE product_id='.$req_id);

            /**
             * Save to product tags table
             */
            if(!empty($this->getTagIds)){
                $prodcutTagsQry = 'INSERT INTO `product_tag_infos`(product_id,product_tag_id) VALUES';
                $co=0; foreach($this->getTagIds as $key => $val){
                    if($co++>0) $prodcutTagsQry .= ',';
                    $prodcutTagsQry .= '('.$req_id.','.$val['id'].')';
                }
                $obj->tag_infos = DB::select($prodcutTagsQry);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Product tag info didn\'t submit properly', 'status' => false], 200);
        }
        
        // Cache flush
        Cache::tags($this->cache_tag_name)->flush();

        return $obj;
    }

    /**
     * SAVE CONTENT
     */
    protected function save_content($obj, $data){
        DB::beginTransaction();

        try{                
            $user_id = Auth::id();            
            
            // Call multi data management
            $this->multi_data_management($data, $user_id);

            $obj->product_title     = $data['product_title'];
            $obj->slug              = $data['slug'];
            $obj->status            = $data['status'];
            $obj->registered        = $data['registered'];
            if(isset($data['selected']))
            $obj->selected          = $data['selected'];
            $obj->created_by        = $user_id;

            if($obj->save()){
                /**
                 * MORE FEATURED MANAGEMENT FUNCTION
                 */
                $obj = $this->more_featured_management($obj,$data,$obj->id,'update');

                $data = [
                    'data'      => $obj,
                    'status'    => true,
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
                ];

                DB::commit();

                return response()->json($data, 200);
            }else{
                DB::rollback();

                return response()->json(['msg' => 'Product didn\'t not save properly', 'status' => false], 200);
            }
        }catch(\Exception $e){
            $data = [
                'status'  => false,
                'code'    => '404',
                'msg'     => 'Error Occured!!! Try again or contact with developer',
                'message' => $e->getMessage(),
            ];

            DB::rollback();
            return response()->json($data, 404);
        }
    }

    /**
     * UPDATE CONTENT
     */
    protected function update_content($obj, $data, $req_id){
        DB::beginTransaction();

        try{                
            $user_id = Auth::id();

            // Call multi data management
            $this->multi_data_management($data, $user_id);

            /**
             * QUERY SETUP
             */
            $obj = $obj->find($req_id);

            $obj->product_title     = $data['product_title'];
            $obj->slug              = $data['slug'];
            $obj->status            = $data['status'];
            $obj->registered        = $data['registered'];
            if(isset($data['selected']))
            $obj->selected          = $data['selected'];
            $obj->updated_by        = $user_id;            

            if($obj->update()){
                /**
                 * MORE FEATURED MANAGEMENT FUNCTION
                 */
                $obj = $this->more_featured_management($obj,$data,$obj->id,'update');

                $data = [
                    'data'      => $obj,
                    'status'    => true,
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been updated successfully.',
                ];

                DB::commit();

                return response()->json($data, 200);
            }else{
                DB::rollback();

                return response()->json(['msg' => 'Product didn\'t not update properly', 'status' => false], 200);
            }
        }catch(\Exception $e){            
            
            $data = [
                'status'  => false,
                'code'    => '404',
                'msg'     => 'Error Occured!!! Try again or contact with developer',
                'message' => $e->getMessage(),
            ];

            DB::rollback();
            
            return response()->json($data, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(Products $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $srch_alpha = $request->has('alpha')?$request['alpha']:'';
        $srch_company_id = $request->has('company_id')?$request['company_id']:'';
        $srch_lazz_pharma = $request->has('lazz_pharma')?$request['lazz_pharma']:'';
        $srch_selected = $request->has('selected')?$request['selected']:'';
        $srch_cat_id = $request->has('cat_id')?$request['cat_id']:'';
        $srch_product_type_id = $request->has('product_type_id')?$request['product_type_id']:'';
        $srch_status = $request->has('status')?$request['status']:'';
        if($srch_status=='0') $srch_status = 'up';
        $own_result = $request->has('own_result')?$request['own_result']:'';
        
        $getData = $obj::select('products.*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->leftJoin('product_generic_infos AS pgi','pgi.product_id','=','products.id')
            ->leftJoin('generics','generics.id','=','pgi.product_generic_id')        
            ->where('products.product_title', 'LIKE' , "%$srch_keyword%")
            ->orWhere('generics.generic_name','LIKE', "%$srch_keyword%");
        })->when($srch_lazz_pharma, function($q) use($srch_lazz_pharma){
            return $q->where('products.lazz_id','>',0);
        })->when($srch_selected, function($q) use($srch_selected){
            return $q->where('products.selected','1');
        })->when($srch_alpha, function($q) use($srch_alpha){
            return $q->where('products.product_title','LIKE',"$srch_alpha%");
        })->when($srch_company_id, function($q) use($srch_company_id){
            return $q->join('product_company_infos AS pcoi','pcoi.product_id','=','products.id')
            ->where('pcoi.product_company_id', $srch_company_id);
        })->when($srch_cat_id, function($q) use($srch_cat_id){
            return $q->join('product_cat_infos AS pcai','pcai.product_id','=','products.id')
            ->where('pcai.product_cat_id', $srch_cat_id);
        })->when($srch_product_type_id, function($q) use($srch_product_type_id){
            return $q->join('product_type_infos AS pti','pti.product_id','=','products.id')
            ->where('pti.product_type_id', $srch_product_type_id);
        })->when($own_result, function($q) use($user_id){
            return $q->where('products.created_by',$user_id);
        })->when($srch_status, function($q) use($srch_status){
            return $q->where('products.status',$srch_status=='up'?0:$srch_status);
        })->orderBy('products.product_title','ASC')
        ->with(['CatInfo','CompanyInfo','ProductTypeInfo','ProductInfos','ProductPriceInfos','ProductPhotoInfos','GenericInfo','DiseaseInfo','TagInfo','OperatorInfo','UpdateOperatorInfo'])
        ->groupBy('products.id')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return ProductsResource::collection($getData);
    }

    public function recommended(Products $obj, Request $request){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $page = $request->has('page')?$request['page']:1;
        $tag_ids = $request->has('tag_ids') && (gettype($request['tag_ids'])=='object' || gettype($request['tag_ids'])=='array')?json_decode($request['tag_ids']):explode(',',$request['tag_ids']);
        // return gettype($tag_ids);
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":recommended:{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->remember($cacheKey, 60, function() use($obj,$tag_ids,$limit){
            //DB::connection()->enableQueryLog();
            
            $getData = $obj::select('products.*')
            ->when($tag_ids, function($q) use($tag_ids){
                return $q->leftJoin('product_tag_infos as pti','products.id', '=', 'pti.product_id')
                ->whereIn('pti.product_tag_id',$tag_ids);
            })->where('products.status','1')
            ->whereNull('products.deleted_at')
            ->orderByRaw('RAND()')
            ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
            ->paginate($limit);
            
            //$log = DB::getQueryLog();

            return ProductsResource::collection($getData);
            
        });

        // return response()->json($getData, 200);
        return $getResponseData;
    }

    public function selected(Products $obj, Request $request){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $page = $request->has('page')?$request['page']:1;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":selected:{$page}";
        
        // $getResponseData = Cache::tags([$this->cache_tag_name])->remember($cacheKey, 60, function() use($obj,$limit){
            
            $getData = $obj::select('products.*')
            ->where('products.selected','1')
            ->whereNull('products.deleted_at')
            ->orderBy('id','DESC')
            ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
            ->paginate($limit);
    
            // return response()->json($getData, 200);
            return ProductsResource::collection($getData);
        // });
        
        return $getResponseData;
    }
    
    public function hot(Products $obj, Request $request){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $page = $request->has('page')?$request['page']:1;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":hot:{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->remember($cacheKey, 60, function() use($obj,$limit){
            
            $getData = $obj::select('products.*')
            ->join('total_sales_by_products as tsp','tsp.product_id','=','products.id')
            ->where('products.status','1')
            ->whereNull('products.deleted_at')
            ->orderBy('tsp.total','DESC')
            ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
            ->paginate($limit);
    
            // return response()->json($getData, 200);
            return ProductsResource::collection($getData);
        });
        
        return $getResponseData;
    }

    public function getAllData(Products $obj, Request $request){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $page = $request->has('page')?$request['page']:1;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":all_data:{$limit}:{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$limit){
        
            $getData = $obj::select('*')
            ->where('status','1')
            ->whereNull('deleted_at')
            ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
            ->orderBy('id','DESC')
            ->paginate($limit);

            // return response()->json($getData, 200);
            return ProductsResource::collection($getData);
        });
        
        return $getResponseData;
    }
    
    public function getDataBySearch(Products $obj, Request $request){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $category_slug = $request->has('category')?$request['category']:'';
        $product_type_slug = $request->has('product-type')?$request['product-type']:'';
        $company_slug = $request->has('company')?$request['company']:'';
        
        $getData = $obj::select('products.*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->leftJoin('product_generic_infos AS pgi','pgi.product_id','=','products.id')
            ->leftJoin('generics','generics.id','=','pgi.product_generic_id')
            ->where(function($qr) use($srch_keyword){
                $qr->where('products.product_title', 'LIKE' , "%$srch_keyword%")
                ->orWhere('generics.generic_name','LIKE', "%$srch_keyword%");
            });            
        })->when($category_slug, function($q) use($category_slug){
            return $q->leftJoin('product_cat_infos as pci','products.id', '=', 'pci.product_id')
            ->leftJoin('categories as c','c.id', '=', 'pci.product_cat_id')
            ->where('c.slug',$category_slug);
        })->when($product_type_slug, function($q) use($product_type_slug){
            return $q->leftJoin('product_type_infos as pti','products.id', '=', 'pti.product_id')
            ->leftJoin('product_types as pt','pt.id', '=', 'pti.product_type_id')
            ->where('pt.slug',$product_type_slug);
        })->when($company_slug, function($q) use($company_slug){
            return $q->leftJoin('product_company_infos as pcom','products.id', '=', 'pcom.product_id')
            ->leftJoin('pharmaceuticals_companies as pc','pc.id', '=', 'pcom.product_company_id')
            ->where('pc.slug',$company_slug);
        })
        ->where('products.status',1)
        // ->orWhere(function($q) use($srch_keyword){
        //     $q->leftJoin('product_generic_infos as pgi','products.id', '=', 'pgi.product_id')
        //     ->leftJoin('generics','generics.id','=','pgi.product_generic_id')
        //     ->where('generics.generic_name', 'LIKE' , "%$srch_keyword%");
        // })
        // ->orWhere(function($q) use($srch_keyword){
        //     $q->leftJoin('product_disease_infos as pdi','products.id', '=', 'pdi.product_id')
        //     ->leftJoin('disease_infos','disease_infos.id','=','pdi.product_disease_id')
        //     ->where('disease_infos.disease_title', 'LIKE' , "%$srch_keyword%");
        // })
        ->whereNull('products.deleted_at')
        ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
        ->groupBy('products.id')
        ->orderBy('products.id','DESC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return ProductsResource::collection($getData);
    }

    public function getDataBySlug(Products $obj, Request $request, $slug){
        // return $request->all();
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":slug:{$slug}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$slug){
            
            try{
                $getData = $obj::select('*')
                ->where('slug', $slug)
                ->where('status',1)
                ->whereNull('deleted_at')
                ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
                ->firstOrFail();
    
                // return response()->json($getData, 200);
                return new ProductsResource($getData);
            }catch(\Exception $e) {
                // something went wrong
                return json_encode(['error' => $e, 'status' => false]);
            }
        });
        
        return $getResponseData;
    }

    public function getDataByCategory(Products $obj, Request $request, $cat_id){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;

        // Cache key init
        $cacheKey = $this->cache_tag_name . ":{$cat_id}:{$limit}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$limit,$cat_id){
            try{
                $getData = $obj::select(DB::RAW('DISTINCT(products.id)'),'products.*')
                ->leftJoin('product_cat_infos as pci', 'products.id', '=', 'pci.product_id')        
                ->where('pci.product_cat_id', $cat_id)
                ->where('products.status',1)
                ->orderBy('products.id','DESC')
                ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
                ->paginate($limit);

                // return response()->json($getData, 200);
                return ProductsResource::collection($getData);
            }catch(\Exception $e) {
                // something went wrong
                return json_encode(['error' => $e, 'status' => false]);
            }
        });

        return $getResponseData;
    }

    public function getDataByCompany(Products $obj, Request $request, $company_id){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $page = $request->has('page')?$request['page']:10;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":company_wise:{$company_id}:{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$company_id,$limit){
            
            $getData = $obj::select('products.*')
            ->leftJoin('product_company_infos as pci', 'products.id', '=', 'pci.product_id')
            ->where('pci.product_company_id', $company_id)     
            ->where('products.status',1)
            ->whereNull('products.deleted_at')
            ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
            ->paginate($limit);
    
            // return response()->json($getData, 200);
            return ProductsResource::collection($getData);
        });
        
        return $getResponseData;
    }

    public function getDataByCatSlug(Products $obj, Request $request, $cat_slug){
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;        
        $product_type_slug = $request->has('product-type')?$request['product-type']:'';
        $company_slug = $request->has('company')?$request['company']:'';
        
        $page = $request->has('page')?$request['page']:10;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name . ":category_wise:{$cat_slug}".($company_slug?":".$company_slug:"").($product_type_slug?":".$product_type_slug:"").":{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$cat_slug,$product_type_slug,$company_slug,$limit){

            $getData = $obj::select('products.*')
            ->leftJoin('product_cat_infos as pci', 'products.id', '=', 'pci.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'pci.product_cat_id')
            ->when($product_type_slug, function($q) use($product_type_slug){
                return $q->leftJoin('product_type_infos as pti','products.id', '=', 'pti.product_id')
                ->leftJoin('product_types as pt','pt.id', '=', 'pti.product_type_id')
                ->where('pt.slug',$product_type_slug);
            })->when($company_slug, function($q) use($company_slug){
                return $q->leftJoin('product_company_infos as pcom','products.id', '=', 'pcom.product_id')
                ->leftJoin('pharmaceuticals_companies as pc','pc.id', '=', 'pcom.product_company_id')
                ->where('pc.slug',$company_slug);
            })->where('c.slug', $cat_slug)
            ->where('products.status',1)
            ->whereNull('products.deleted_at')
            ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
            ->paginate($limit);
    
            // return response()->json($getData, 200);
            return ProductsResource::collection($getData);
        });
        
        return $getResponseData;
    }

    public function relatedData(Products $obj, Request $request){
        // return $request->all();
        $getGenericsArr = json_decode($request['generics'], true);
        $limit = $request->has('limit')?$request['limit']:10;
        $getData = $obj::select('products.*')
        ->leftJoin('product_generic_infos as pgi', 'products.id', '=', 'pgi.product_id')
        ->whereIn('pgi.product_generic_id', $getGenericsArr)
        ->where('products.status',1)
        ->whereNull('products.deleted_at')
        ->with(['CompanyInfo','ProductTypeInfo','ProductPriceInfos','ProductPhotoInfos','GenericInfo'])
        ->paginate($limit);

        // return response()->json($getData, 200);
        return ProductsResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Products  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(Products $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with(['CatIds','CompanyIdInfo','ProductTypeIdInfo','ProductInfos','ProductPriceInfos','ProductPhotoInfos','GenericInfo','DiseaseInfo','TagInfo','OperatorInfo','UpdateOperatorInfo'])
        ->first();        

        // return response()->json($getData, 200);
        return new ProductSingleResource($getData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Products $obj, $req_id)
    {
        // return ModificationController::update_content($obj, $request, $req_id);
        return $this->update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(Products $obj, $id)
    {
        $geResult = $obj::find($id)->delete();
        
        // Cache flush with tag name
        Cache::tags($this->cache_tag_name)->flush();

        return response()->json($geResult, 200);
    }

    /**
     * Search tags
     */
    public function search(Products $obj, Request $request)
    {
        // return request()->get('term');
        $limit = $request->has('limit')?$request['limit']:10;
        
        $getData = $obj::select('products.id','products.product_title','products.slug','generics.generic_name')
        ->leftJoin('product_generic_infos AS pgi','pgi.product_id','=','products.id')
        ->leftJoin('generics','generics.id','=','pgi.product_generic_id')
        ->where(function($q){
            $q->where('products.product_title','LIKE',request()->get('term').'%')
            ->orWhere('generics.generic_name','LIKE',request()->get('term').'%');  
        })->where('products.status',1)
        ->whereNull('products.deleted_at')
        ->orderBy('products.product_title','ASC')
        ->take($limit)->distinct()->get(['products.id']);

        return response()->json($getData, 200);
    }
    
    /**
     * Data crawl form lazz pharma
     */
    // public function lazzPharmaData(Request $request, Products $obj){
    //     $pg = $request->has('page')?$request['page']:1;
    //     $pp = 100;
        
    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_URL,"https://www.lazzpharma.com/ProductArea/Product/Searches");
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS,
    //                 "PageNumber=".$pg."&PageSize=".$pp);
        
    //     // In real life you should use something like:
    //     // curl_setopt($ch, CURLOPT_POSTFIELDS, 
    //     //          http_build_query(array('postvar1' => 'value1')));
        
    //     // Receive server response ...
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
    //     $server_output = curl_exec($ch);
        
    //     curl_close ($ch);
        
    //     if(!empty($server_output)){
    //         $getServerData = json_decode($server_output);
    //         // print_r($getServerData->Data);
            
    //         $query = 'INSERT INTO `lazz_pharma_products` (`lazz_id`,`product_title`,`generic_name`,`product_type`,`company_name`,`price`) VALUES';
    //         $co=0; foreach($getServerData->Data as $key => $val){
    //             if($co++>0) $query .= ',';
    //             $lazz_id = $val[0];
    //             $product_title = addslashes($val[1]." ".$val[3]);
    //             $generic_name = addslashes($val[2]);
    //             $product_type = addslashes($val[8]);
    //             $company_name = addslashes($val[9]);
    //             $price = $val[4];
    //             $query .= '("'.$lazz_id.'","'.$product_title.'","'.$generic_name.'","'.$product_type.'","'.$company_name.'","'.$price.'")';
    //         }
    //         if($co>0){
    //             $query .= ' ON DUPLICATE KEY UPDATE product_title=VALUES(product_title),generic_name=VALUES(generic_name),product_type=VALUES(product_type),company_name=VALUES(company_name),price=VALUES(price)';
                
    //             // echo $query;
    //             DB::select($query);
                
    //             echo 'INSERTED DONE : '.($pg * $pp);
    //             echo '<meta http-equiv="refresh" content="3;url=https://api.medquicker.com/lp-bulk-data-crawler?page='.($pg+1).'" />';
    //         }
    //     }
    // }
}
