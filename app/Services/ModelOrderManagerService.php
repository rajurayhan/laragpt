<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Integer;

// Algorithm:

// Step  : Add New Entry
//     -> Fetch All Data with order greater than or equal 'n', Here n is provided order.
//     -> If data Exists, Shift all data order by +1 (Right Shift).
//     -> Save all shifted order and then finaly save the new entry.

// Step: Update an existing Entry:
//     -> Find the existing entry and it's order. Refer as 'm'
//     -> Check if existing order is greater or less than new order.
//     -> If 'm' is greater than 'n',
//         => Find all entries greatet than or equal 'n', and less than 'm'
//             -> Shift all data order by +1 (Right Shift).
//             -> Update the record.
//     -> Else,
//         => Find all entries greater than 'm' and less than or equal 'n'
//             -> Shift all data order by -1 (Left Shift).
//             -> Update the record.
class ModelOrderManagerService
{
    private $modelClass;

    private $fieldName;

    public function __construct(string $modelClass, string $fieldName = null)
    {
        $this->modelClass = $modelClass;
        $this->fieldName = $modelClass ?? 'order';
    }

    public function addOrUpdateItem(array $newItem, $id = null, $parentField = null, $parentId = null)
    {
        return DB::transaction(function () use ($newItem, $id, $parentField, $parentId) {
            $model = app($this->modelClass);

            if (isset($id)) {
                return $this->updateExistingItem($model, $newItem, $id, $parentField, $parentId);
            } else {
                return $this->insertNewItem($model, $newItem, $parentField, $parentId);
            }
        });
    }

    private function updateExistingItem($model, $newItem, $id, $parentField = null, $parentId = null)
    {
        $instance = $model->find($id);

        $query = $this->buildQuery($model, $newItem['order'], $instance->order, $parentField, $parentId);

        $this->shiftOrder($query->get(), $newItem['order'] < $instance->order ? 1 : -1);

        $instance->update($newItem);
        return $instance;
    }

    private function insertNewItem($model, $newItem, $parentField = null, $parentId = null)
    {
        $query = $this->buildQuery($model, $newItem['order'], null, $parentField, $parentId);

        $this->shiftOrder($query->get(), 1);

        return $model->create($newItem);
    }

    private function buildQuery($model, $newOrder, $instanceOrder, $parentField, $parentId)
    {
        $query = $model::query();

        if ($parentField && $parentId) {
            $query->where($parentField, $parentId);
        }

        if ($instanceOrder !== null) {
            $query->where('order', $newOrder < $instanceOrder ? '>=' : '<=', $newOrder)
                  ->where('order', $newOrder < $instanceOrder ? '<' : '>', $instanceOrder);
        } else {
            $query->where('order', '>=', $newOrder);
        }

        return $query;
    }

    private function shiftOrder($data, $shiftValue)
    {
        foreach ($data as $item) {
            $item->update(['order' => $item->order + $shiftValue]);
        }
    }
}
