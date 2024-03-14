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

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function addOrUpdateItem(array $newItem, $id = null, $parentField = null, $parentId = null)
    {
        // \Log::info($id);
        return DB::transaction(function () use ($newItem, $id, $parentField, $parentId) {
            $model = app($this->modelClass);

            if (isset($id)) {
                // \Log::info('On Update');
                return $this->updateExistingItem($model, $newItem, $id, $parentField, $parentId);
            } else {
                // \Log::info('On Create');
                return $this->insertNewItem($model, $newItem, $parentField, $parentId);
            }
        });
    }

    private function updateExistingItem($model, $newItem, $id, $parentField = null, $parentId = null)
    {
        $modelQuery = $model::query();

        $instance = $model->find($id);

        if($parentField && $parentId){
            $modelQuery->where($parentField, $parentId);
        }

        if ($instance->order > $newItem['order']) {
            $shiftData = $modelQuery->where('order', '>=', $newItem['order'])
                ->where('order', '<', $instance->order)
                ->get();

            $this->shiftOrder($shiftData, 1);
        } else {
            $shiftData = $modelQuery->where('order', '<=', $newItem['order'])
                ->where('order', '>', $instance->order)
                ->get();

            $this->shiftOrder($shiftData, -1);
        }

        $instance->update($newItem);
        return $instance;
    }

    private function insertNewItem($model, $newItem, $parentField = null, $parentId = null)
    {
        $existingData = $model->where('order', '>=', $newItem['order'])
            ->get();

        $this->shiftOrder($existingData, 1);

        $createdModel = $model->create($newItem);

        return $createdModel;
    }

    private function shiftOrder($data, $shiftValue)
    {
        foreach ($data as $item) {
            $item->order = $item->order + $shiftValue;
            $item->save();
        }
    }
}
