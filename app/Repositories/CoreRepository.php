<?php


namespace App\Repositories;


/**
 * Class CoreRepository
 * @package App\Repositories
 *
 * Репозиторий работы с сущностью.
 * Может выдавать наборы данных.
 */
abstract class CoreRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * CoreRepository constructor
     */
    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    /**
     * @return mixed
     */
    abstract protected function getModelClass();

    /**
     * @return Model|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function startConditions() {
        return $this->model->factory();
    }
}
