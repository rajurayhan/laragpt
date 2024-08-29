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
class ModelOrderManagerServiceV2
{
    private string $fieldName;
    private string $modelClass;

    public function __construct(string $modelClass, ?string $fieldName = null)
    {
        // If no specific field name is provided, default to 'order'
        $this->fieldName = $fieldName ?? 'order';
        $this->modelClass = $modelClass;
    }

    public function addOrUpdateItem(array $newItem, $id = null, $parentField = null, $parentId = null)
    {
        return DB::transaction(function () use ($newItem, $id, $parentField, $parentId) {
            $model = app($this->modelClass);

            // Determine if 'order' is present in the array or model, and set fieldName dynamically
            // $this->detectFieldName($newItem, $model);

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

        $query = $this->buildQuery($model, $newItem[$this->fieldName], $instance->{$this->fieldName}, $parentField, $parentId);

        $this->shiftOrder($query->get(), $newItem[$this->fieldName] < $instance->{$this->fieldName} ? 1 : -1);

        $instance->update($newItem);
        return $instance;
    }

    private function insertNewItem($model, $newItem, $parentField = null, $parentId = null)
    {
        $query = $this->buildQuery($model, $newItem[$this->fieldName], null, $parentField, $parentId);

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
            $query->where($this->fieldName, $newOrder < $instanceOrder ? '>=' : '<=', $newOrder)
                  ->where($this->fieldName, $newOrder < $instanceOrder ? '<' : '>', $instanceOrder);
        } else {
            $query->where($this->fieldName, '>=', $newOrder);
        }

        return $query;
    }

    private function shiftOrder($data, $shiftValue)
    {
        foreach ($data as $item) {
            $item->update([$this->fieldName => $item->{$this->fieldName} + $shiftValue]);
        }
    }

    private function detectFieldName(array $newItem, $model)
    {
        // Check if the 'order' field exists in the array, if so, set it as the fieldName
        if (array_key_exists('order', $newItem)) {
            $this->fieldName = 'order';
        }
        // If 'order' is not in the array, check if the model has an 'order' field
        elseif (isset($model->order)) {
            $this->fieldName = 'order';
        }
        // If neither, you can set it to a fallback value or throw an exception if it's required
        else {
            // You can set a fallback or handle the scenario differently
            throw new \Exception("Order field not found in array or model.");
        }
    }
}
