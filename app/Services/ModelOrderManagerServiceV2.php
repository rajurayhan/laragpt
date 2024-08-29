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

    public function __construct(string $modelClass, ?string $fieldName = 'order')
    {
        // Set the field name, defaulting to 'order' if none is provided
        $this->fieldName = $fieldName;
        $this->modelClass = $modelClass;
    }

    public function addOrUpdateItem(array $newItem, $id = null, $parentField = null, $parentId = null)
    {
        return DB::transaction(function () use ($newItem, $id, $parentField, $parentId) {
            $model = app($this->modelClass);

            // Determine if the custom field name is present in the array or model, and adjust accordingly
            $this->detectFieldName($newItem, $model);

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
        // This method can be adjusted to perform custom field detection logic if needed
        if (array_key_exists($this->fieldName, $newItem)) {
            // Field name exists in the array, keep it as is
        } elseif (isset($model->{$this->fieldName})) {
            // Field name exists in the model, keep it as is
        } else {
            // If neither exists, you can handle the scenario, or throw an exception
            throw new \Exception("Field {$this->fieldName} not found in array or model.");
        }
    }
}
