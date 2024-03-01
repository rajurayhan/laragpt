<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class ModelOrderManagerService
{
    private $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function addOrUpdateItem(array $newItem)
    {
        DB::transaction(function () use ($newItem) {
            $model = app($this->modelClass);

            if (isset($newItem['id'])) {
                $instance = $model->find($newItem['id']);

                if ($instance->order > $newItem['order']) {
                    $shiftData = $model->where('order', '>=', $newItem['order'])
                        ->where('order', '<', $instance->order)
                        ->get();

                    self::shiftOrder($shiftData, 1);
                } else {
                    $shiftData = $model->where('order', '<=', $newItem['order'])
                        ->where('order', '>', $instance->order)
                        ->get();

                    self::shiftOrder($shiftData, -1);
                }

                $instance->order = $newItem['order'];
                $instance->save();
            } else {
                $existingData = $model->where('order', '>=', $newItem['order'])
                    ->get();

                self::shiftOrder($existingData, 1);

                $model->create($newItem);
            }
        });
    }

    private static function shiftOrder($data, $shiftValue)
    {
        foreach ($data as $item) {
            $item->order = $item->order + $shiftValue;
            $item->save();
        }
    }
}
