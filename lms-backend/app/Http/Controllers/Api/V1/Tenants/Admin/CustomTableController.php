<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\RegistrationTableRequest;
use App\Models\RegistrationTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CustomTableController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.admin');
  }

  public function index()
  {
    $data = [];
    $tables = RegistrationTable::orderBy('sort')->get();

    foreach ($tables as $key => $value) {
      $data[$key] = [
        'id' => $value['id'],
        'table_name' => $value['table_name'],
        'columns' => $this->getColumns($value['table_name'])
      ];
    }
    return $this->sendResponse($data, 'List of all custom tables', 200);
  }

  public function show($table_name)
  {
    $data = $this->getColumns($table_name);
    return $this->sendResponse($data, 'List of all columns', 200);
  }


  public function getColumns($table_name)
  {
    $data = [];
    // getDoctrineColumn() was removed in Laravel 11; getColumns() reports nullability natively.
    $columns = collect(Schema::getColumns($table_name))->keyBy('name');

    foreach ($columns as $value => $column) {
      if($value === 'id' || $value === 'user_id' || $value === 'created_at' || $value === 'updated_at') continue;
      $data[] = [
        'name' => $value,
        'type' =>  DB::getSchemaBuilder()->getColumnType($table_name, $value),
        'optional' => !$column['nullable'],
      ];
    }

    return array_values($data);
  }


    /**
     * Create dynamic table along with dynamic fields
     *
     * @param       $table_name
     * @param array $fields
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function createTable($table_name, $fields = [])
    {
        // check if table is not already exists
        if (!Schema::hasTable($table_name)) {
            Schema::create($table_name, function (Blueprint $table) use ($fields, $table_name) {
                $table->increments('id');
                // if (count($fields) > 0) {
                //     foreach ($fields as $field) {
                //         $table->{$field['type']}($field['name'])->nullable($field['is_required']);
                //     }
                // }
                $table->timestamps();
            });

            return response()->json(['message' => 'Table has been successfully created!'], 200);
        }

        return response()->json(['message' => 'Table is already exist.'], 400);
    }

  public function newTable(RegistrationTableRequest $request)
  {
    $table_name = strtolower(str_replace(' ', '_', $request->table_name));
    if (!Schema::hasTable($table_name)) {
      Schema::create($table_name, function (Blueprint $table) {
          $table->increments('id');
          $table->unsignedBigInteger('user_id')->nullable();
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          $table->timestamps();
      });
      RegistrationTable::create([
        'table_name' => $table_name
      ]);
      return response()->json(['message' => 'Table has been successfully created!'], 200);
    }
    return response()->json(['message' => 'Table is already exist.'], 400);
      // set dynamic table name according to your requirements
      // $table_name = gmdate('yymmddhis');

      // $field_types = $request['field_type'];
      // $field_labels = $request['field_label'];
      // $field_is_requireds = $request['field_is_required'];

      // foreach ($field_types as $key => $field_type) {

      //     $field_type = $field_type;
      //     $field_label = $field_labels[$key];
      //     $field_is_required = $field_is_requireds[$key];

      //     if ($field_is_required != 'Y') {
      //         $field_is_required = false;
      //     }

      //     // set your dynamic fields (you can fetch this data from database this is just an example)
      //     $fields[] = [
      //         'name' => $field_label,
      //         'type' => $field_type,
      //         'is_required' => $field_is_required
      //     ];
      // }

      // echo "<pre>", var_dump($fields), "</pre>";
  }

  public function deleteTable(Request $request)
  {
    $table_name = $request['table_name'];

    if(Schema::hasTable($table_name)) {
      Schema::dropIfExists($table_name);
      RegistrationTable::where('table_name', $table_name)->delete();
      return response()->json(['message' => 'Table successfully drop.'], 204);
    }
  
    RegistrationTable::where('table_name', $table_name)->delete();
    return response()->json(['message' => 'Table successfully drop.'], 204);

    // if (Schema::hasTable($table_name)) {

    //   Schema::dropIfExists($table_name);

    //   if (!Schema::hasTable($table_name)) {
    //       return response()->json(['message' => 'Table is no longer exist.'], 400);
    //   }
    // }



    // return response()->json(['message' => 'Table is not exist.'], 400);
  }

  public function deleteColumn(Request $request)
  {
    $table_name = $request['table_name'];
    $column_name = $request['column_name'];

    if (Schema::hasTable($table_name)) {

        if (!Schema::hasColumn($table_name, $column_name)) {
            return response()->json(['message' => 'Column is not exist.'], 400);
        } else {

            Schema::table($table_name, function (Blueprint $table) use ($column_name) {
                $table->dropColumn($column_name);
            });

            return response()->json(['message' => 'Column has been successfully deleted!'], 200);
        }

    }

    return response()->json(['message' => 'Table is not exist.'], 400);
  }

  public function createColumns($table_name, $fields = [])
  {
    // check if table is not already exists
    if (Schema::hasTable($table_name)) {
        Schema::table($table_name, function (Blueprint $table) use ($fields, $table_name) {
            if (count($fields) > 0) {
                foreach ($fields as $field) {
                    if (!Schema::hasColumn($table_name, $field['name'])) {
                        $table->{$field['type']}($field['name'])->nullable($field['is_required']);
                    }
                }
            }
        });

        return response()->json(['message' => 'Columns has been successfully created!'], 200);
    }

    return response()->json(['message' => 'Table is not exist.'], 400);
  }

  public function addColumns(Request $request)
  {
    $table_name = $request->table_name;

    if (Schema::hasTable($table_name)) {
      Schema::table($table_name, function (Blueprint $table) use ($request, $table_name) {
        if (!Schema::hasColumn($table_name, $request->field_name)) {
          $name = strtolower(str_replace(' ', '_', $request->field_name));
          $table->{$request->field_type}($name)
            ->nullable($request->required)
            ->after($request->field_after ? $request->field_after : 'user_id');
        }
      });
      return response()->json(['message' => 'Columns has been successfully created!'], 200);
    }
    return response()->json(['message' => 'Table is not exist.'], 400);
      // // set dynamic table name according to your requirements
      // $table_name = $request['table_name'];

      // $field_types = $request['field_type'];
      // $field_labels = $request['field_label'];
      // $field_is_requireds = $request['field_is_required'];

      // foreach ($field_types as $key => $field_type) {

      //     $field_type = $field_type;
      //     $field_label = $field_labels[$key];
      //     $field_is_required = $field_is_requireds[$key];

      //     if ($field_is_required != 'Y') {
      //         $field_is_required = false;
      //     }

      //     // set your dynamic fields (you can fetch this data from database this is just an example)
      //     $fields[] = [
      //         'name' => $field_label,
      //         'type' => $field_type,
      //         'is_required' => $field_is_required
      //     ];
      // }

      // // echo "<pre>", var_dump($fields), "</pre>";
      // return $this->createColumns($table_name, $fields);

  }

  public function changeColumnPosition(Request $request)
  {
    $table = $request->table;

    //rename to temp column
    Schema::table($table, function($table) use ($request) {
      $table->renameColumn($request->column, 'old');
    });

    //create the desired column after column declared
    Schema::table($table, function(Blueprint $table) use ($request) {
      $table->{$request->type}($request->column)->after($request->after == 'first' ? 'user_id' : $request->after);
    });

    //passed the data from temp to the new column
    DB::table($table)->update([$request->column => DB::raw('old')]);

    //drop temp
    Schema::table($table, function(Blueprint $table) {
      $table->dropColumn('old');
    });

    return response()->json(['message' => 'Column has been successfully moved!'], 204);
  }

  public function sortTable(Request $request)
  {
    foreach ($request->table_names as $key => $table_name) {
      RegistrationTable::where('table_name', $table_name)->update(['sort' => $key]);
    }
    return response()->json(['message' => 'Table has been successfully sorted!'], 204);
  }

  public function updateColumn(Request $request) {
    $table_name = $request->table_name;

    if (Schema::hasTable($table_name)) {
      $name = strtolower(str_replace(' ', '_', $request->field_name));

      if($request->field_name !== $request->old_column_name) {
        Schema::table($table_name, function (Blueprint $table) use ($request, $table_name, $name) { 
          $table->renameColumn($request->old_column_name, $name);
        });
      }

      Schema::table($table_name, function (Blueprint $table) use ($request, $table_name, $name) {
        $table->{$request->field_type}($name)->nullable($request->required)->change();
      });
      return response()->json(['message' => 'Columns has been successfully updated!'], 200);
    }
    return response()->json(['message' => 'Table is not exist.'], 400);
  }
}
