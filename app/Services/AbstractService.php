<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AbstractService
{
    protected $model;
    protected $relations = null;

    public function all($relations = [], $withTrash = false, $where = []){
        $model = $this->model;
        if (count($relations) > 0)
            $model =  $this->model->with($relations);

        if ($withTrash == true)
            $model = $model->withTrashed();

        if (count($where) > 0)
            $model = $model->where($where);

        return $model->get();
    }

    /**
     * Creates and store an instance of the model
     * validates data before creating
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return \stdClass
     */
    public function create($data = [], $rules = [], $messages = [])
    {
        if (!count($data)) null;

        $response = $this->validate($data, $rules, $messages);

        if ($response->status) {
            $instance = $this->store($response->data);
            $response->data = $instance;
        }

        return $response;
    }

    /**@description Stores new model instance into database
     * validates data with respect to model table columns
     * @param array $attributes
     * @return mixed
     */
    public function store($attributes = [])
    {
        $valid_data = $this->get_valid_data($attributes);
        if (!count($valid_data)) return null;
        $instance = $this->model()->fill($valid_data);
        $instance->save();
        return $instance;
    }

    /**@description filters $attributes against field names in model table
     * @param array $attributes
     * @return array: valid data
     */
    public function get_valid_data($attributes = [])
    {
        if (!count($attributes)) {
            return [];
        }
        $table = $this->model()->getTable();
        $valid_data = [];

        foreach ($attributes as $key => $value){
            if ($this->tableHasColumn($table, $key)) {
                $valid_data[$key] = $value;
            }
        }

        return $valid_data;
    }

    /**@description if model is set, creates an instance
     * @return null
     */
    public function model()
    {
        return $this->model ? new $this->model : null;
    }

    /**@description check if model table has the given column
     * @param $table
     * @param $column
     * @return bool
     */
    public function tableHasColumn($table, $column)
    {
        if (!$column || !$table) return false;
        $has_column = Schema::hasColumn($table, $column);
        return $has_column;
    }

    /**@description validates given data against the given rules
     * @param $data
     * @param $rules
     * @param array $messages
     * @return \stdClass: {status: true/false, errors/data},
     */
    public function validate($data, $rules, $messages = [])
    {
        $validator = Validator::make($data, $rules, $messages);

        $response = new \stdClass();
        if ($validator->fails()) {
            $response->status = false;
            $response->errors = $validator->errors();
            $response->statusCode = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
        } else {
            $response->status = true;
            $response->data = $data;
        }

        return $response;
    }

    public function deleteInstance($id)
    {
        if ($id) {
            $this->model->find($id)->delete();
        }
    }

    public function restoreInstance ($id)
    {
        if ($id) {
            $this->model->onlyTrashed()->find($id)->restore();
        }
    }

    public function withRelation($relations, $where = [], $first = false) {
        if (count($where) > 0) {

            $query =  $this->model->with($relations)->where($where);

            return $first ? $query->first() : $query->get();
        }
        $query2 = $this->model->with($relations);

        return $first ? $query2->first() : $query2->get();
    }

    public function withRelationIn($relations,$column = 'id' ,$where = []) {
        return $this->model->with($relations)->whereIn($column, $where)->get();
    }

    public function withRelationNotIn($relations,$column = 'id' ,$where = []) {
        return $this->model->with($relations)->whereNotIn($column, $where)->get();
    }


    public function find($where, $first = false) {

        $query =  $this->model->where($where);

        return $first ? $query->first() : $query->get();

    }

    public function findById($id)
    {
        return $this->find(['id' => $id], true);
    }


    public function update($id, $attributes = [], $rules = [], $messages = []) {
        $response = $this->validate($attributes, $rules, $messages);

        if ($response->status == true) {
            $instance  = $this->model->find($id);
            if ($instance != null) {
                $valid_data = $this->get_valid_data($attributes);
                $instance->update($valid_data);
            }

        }

        return $response;
    }

    public function getById($id, $relation = []) {
        if (count($relation) > 0) {
            return $this->model->with($relation)->find($id);
        }
        return $this->model->find($id);
    }

    public function getByWithTrash($value, $column = 'id', $relation = [])
    {
        $model = $this->model->withTrashed();

        if (count($relation) > 0) {
            $model = $model->with($relation);
        }
        return $model->where($column, $value)->first();
    }

    public function notFound($message = 'Not Found')
    {
        return prepareResponse(false, ['message' => $message], Response::HTTP_NOT_FOUND);
    }

    public function storeFailed($message = 'Failed to Save Data')
    {
        return prepareResponse(false, ["message" => $message], Response::HTTP_EXPECTATION_FAILED);
    }
}
