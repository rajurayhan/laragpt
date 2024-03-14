<?php

namespace App\Console\Commands;

use App\Models\ServiceDeliverables;
use App\Models\ServiceDeliverableTasks;
use App\Models\ServiceGroups;
use App\Models\Services;
use App\Models\ServiceScopes;
use Illuminate\Console\Command;

class SetDefaultOrderOnServiceModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:set-order';
    protected $description = 'Set a default order on service module data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Services::get();
        foreach ($services as $key => $service) {
            $service->order = $key+1;
            $service->save();
        }

        $serviceGroup = ServiceGroups::get();

        $filteredData = [];
        foreach ($serviceGroup as $key => $group) {
            $filteredData[$group->serviceId][] = $group;
        }
        foreach($filteredData as $dataset){
            foreach ($dataset as $key => $data) {
                $data->order = $key+1;
                $data->save();
            }
        }

        $serviceScope = ServiceScopes::get();

        $filteredData = [];
        foreach ($serviceScope as $key => $scope) {
            $filteredData[$scope->serviceGroupId][] = $scope;
        }
        foreach($filteredData as $dataset){
            foreach ($dataset as $key => $data) {
                $data->order = $key+1;
                $data->save();
            }
        }

        $serviceDeliverables = ServiceDeliverables::get();

        $filteredData = [];
        foreach ($serviceDeliverables as $key => $deliverable) {
            $filteredData[$deliverable->serviceScopeId][] = $deliverable;
        }
        foreach($filteredData as $dataset){
            foreach ($dataset as $key => $data) {
                $data->order = $key+1;
                $data->save();
            }
        }

        $serviceDeliverableTask = ServiceDeliverableTasks::get();

        $filteredData = [];
        foreach ($serviceDeliverableTask as $key => $task) {
            $filteredData[$task->serviceDeliverableId][] = $task;
        }
        foreach($filteredData as $dataset){
            foreach ($dataset as $key => $data) {
                $data->order = $key+1;
                $data->save();
            }
        }

        // $serviceScope = ServiceScopes::get();
        // foreach ($serviceScope as $key => $scope) {
        //     $scope->order = $key+1;
        //     $scope->save();
        // }

        // $serviceDeliverables = ServiceDeliverables::get();
        // foreach ($serviceDeliverables as $key => $deliverable) {
        //     $deliverable->order = $key+1;
        //     $deliverable->save();
        // }

        // $serviceDeliverableTasks = ServiceDeliverableTasks::get();
        // foreach ($serviceDeliverableTasks as $key => $task) {
        //     $task->order = $key+1;
        //     $task->save();
        // }

        $this->info('Order Set Successfully');

    }
}
