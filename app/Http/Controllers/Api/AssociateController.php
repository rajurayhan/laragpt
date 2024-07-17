<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Associate;
use App\Models\Deliberable;
use App\Models\DeliverablesNotes;
use App\Models\EstimationTask;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Models\ScopeOfWorkAdditionalService;
use App\Models\ServiceDeliverables;
use App\Models\ServiceDeliverableTasks;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


/**
 * @authenticated
 */

class AssociateController extends Controller
{

    /**
     * Get Associate list
     *
     * @group Associates
     */
    public function index(){
        $data = $this->getAssociates();
        // Fetch all data if no page number is provided
        return response()->json([
            'data'=> $data,
        ]);
    }

    public static function getAssociates(){
        $associates = Associate::orderBy('id','ASC')->get();

        return $associates;
    }
}

