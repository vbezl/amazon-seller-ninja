<?php

namespace App\Http\Controllers\Panel;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ProductRequest as StoreRequest;
use App\Http\Requests\ProductRequest as UpdateRequest;

use App\Models\Product;
use Log;
use App\Traits\AmazonFunctionsTrait;

class ProductCrudController extends CrudController {

    use AmazonFunctionsTrait;

    public function setUp() {

        /*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
        $this->crud->setModel("App\Models\Product");
        $this->crud->setRoute("panel/products");
        $this->crud->setEntityNameStrings('product', 'products');

        /*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/

		//$this->crud->setFromDb();

		// ------ CRUD FIELDS
        $this->crud->addField([
            'name' => 'asin',
            'label' => 'ASIN',
            'type' => 'text',
        ]);
        // $this->crud->addField($options, 'update/create/both');
        // $this->crud->addFields($array_of_arrays, 'update/create/both');
        // $this->crud->removeField('name', 'update/create/both');
        // $this->crud->removeFields($array_of_names, 'update/create/both');

        // ------ CRUD COLUMNS
        $this->crud->addColumn([ // image
            'label' => "Image",
            'name' => "image_url",
            'type' => 'image',
        ]);
        $this->crud->addColumn([
            'name' => 'asin',
            'label' => 'ASIN',
        ]);
        $this->crud->addColumn([
            'name' => 'title',
            'label' => 'Title',
        ]);
        $this->crud->addColumn([
            // run a function on the CRUD model and show its return value
            'label' => "Rank", // Table column heading
            'type' => "model_function",
            'function_name' => 'getLatestProductRank', // the method in your Model
        ]);
        $this->crud->addColumn([
            // run a function on the CRUD model and show its return value
            'label' => "Price", // Table column heading
            'type' => "model_function",
            'function_name' => 'getLatestProductPrice', // the method in your Model
        ]);

        // $this->crud->addColumn(); // add a single column, at the end of the stack
        // $this->crud->addColumns(); // add multiple columns, at the end of the stack
        // $this->crud->removeColumn('column_name'); // remove a column from the stack
        // $this->crud->removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
        // $this->crud->setColumnDetails('column_name', ['attribute' => 'value']); // adjusts the properties of the passed in column (by name)
        // $this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);
        
        // ------ CRUD BUTTONS
        // possible positions: 'beginning' and 'end'; defaults to 'beginning' for the 'line' stack, 'end' for the others;
        $this->crud->addButtonFromView('line', 'Chart', 'chart', 'end');
        // $this->crud->addButton($stack, $name, $type, $content, $position); // add a button; possible types are: view, model_function
        // $this->crud->addButtonFromModelFunction($stack, $name, $model_function_name, $position); // add a button whose HTML is returned by a method in the CRUD model
        // $this->crud->addButtonFromView($stack, $name, $view, $position); // add a button whose HTML is in a view placed at resources\views\vendor\backpack\crud\buttons
        // $this->crud->removeButton($name);
        // $this->crud->removeButtonFromStack($name, $stack);

        // ------ CRUD ACCESS
        $this->crud->allowAccess(['list', 'create', 'delete']);
        $this->crud->denyAccess(['update', 'reorder']);

        // ------ CRUD REORDER
        // $this->crud->enableReorder('label_name', MAX_TREE_LEVEL);
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('reorder');

        // ------ CRUD DETAILS ROW
        // $this->crud->enableDetailsRow();
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('details_row');
        // $this->crud->allowAccess('details_row');
        // NOTE: you also need to do overwrite the showDetailsRow($id) method in your EntityCrudController to show whatever you'd like in the details row OR overwrite the views/backpack/crud/details_row.blade.php

        // ------ REVISIONS
        // You also need to use \Venturecraft\Revisionable\RevisionableTrait;
        // Please check out: https://laravel-backpack.readme.io/docs/crud#revisions
        // $this->crud->allowAccess('revisions');

        // ------ AJAX TABLE VIEW
        // Please note the drawbacks of this though:
        // - 1-n and n-n columns are not searchable
        // - date and datetime columns won't be sortable anymore
        // $this->crud->enableAjaxTable();
        
        
        // ------ DATATABLE EXPORT BUTTONS
        // Show export to PDF, CSV, XLS and Print buttons on the table view.
        // Does not work well with AJAX datatables.
        // $this->crud->enableExportButtons();

        // ------ ADVANCED QUERIES
        // $this->crud->addClause('active');
        // $this->crud->addClause('type', 'car');
        // $this->crud->addClause('where', 'name', '==', 'car');
        // $this->crud->addClause('whereName', 'car');
        $this->crud->addClause('whereHas', 'users', function($query) {
            $query->where('id', request()->user()->id);
        });
        // $this->crud->with(); // eager load relationships
        // $this->crud->orderBy();
        // $this->crud->groupBy();
        // $this->crud->limit();
    }

	public function store(StoreRequest $request)
	{

        $this->crud->hasAccessOrFail('create');

        // fallback to global request instance
        if (is_null($request)) {
            $request = \Request::instance();
        }

        // replace empty values with NULL, so that it will work with MySQL strict mode on
        foreach ($request->input() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $request->request->set($key, null);
            }
        }

        // check if ASIN is already in DB
        $product = Product::where('asin', $request->input('asin'))->first();
        if($product === null){

            // insert item in the db
            $product = $this->crud->create($request->except(['redirect_after_save', '_token']));
            $this->data['entry'] = $this->crud->entry = $product;

        }

        $user = $request->user();
        Log::info('checking if in user seller account, user_id: '.$user->id);
        $in_user_seller_account = $this->checkIfInSellerAccount($product, $user);
        Log::info('product is '.(!$in_user_seller_account?'not ':'').'in user seller account');

        // attaching product to user
        Log::info('attaching product to user, product_id -> user_id: '.$product->id.'->'.$user->id);
        $product->users()->syncWithoutDetaching([$user->id => [
            'in_user_seller_account' => $in_user_seller_account,
            'track' => false
        ]]);


        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // redirect the user where he chose to be redirected
        switch ($request->input('redirect_after_save')) {
            case 'current_item_edit':
                return \Redirect::to($this->crud->route.'/'.$item->getKey().'/edit');

            default:
                return \Redirect::to($request->input('redirect_after_save'));
        }

	}

	public function update(UpdateRequest $request)
	{
		// your additional operations before save here
        $redirect_location = parent::updateCrud();
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
	}

}
