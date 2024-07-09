<?php

namespace App\Http\Controllers\Api;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Question Management
 * @authenticated
 *
 * APIs for managing question
 */
class QuestionController extends Controller
{

    /**
     * Display a listing of Question.
     *
     * @group Question Management
     * @queryParam page integer page number.
     * @queryParam serviceId integer page number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $questionQuery = Question::query();

        if ($request->get('serviceId')) {
            $questionQuery->whereRaw('JSON_CONTAINS(serviceIds, ?)', [$request->get('serviceId')] );
        }

        if ($request->has('page')) {
            $question = $questionQuery->paginate(10);
            return response()->json([
                'data' => $question->items(),
                'total' => $question->total(),
                'current_page' => $question->currentPage(),
            ]);
        } else {
            $questions = $questionQuery->get();
            return response()->json([
                'data' => $questions,
                'total' => $questions->count(),
            ]);
        }
    }

    /**
     * Store a new Question
     *
     * @group Question Management
     *
     * @bodyParam title string required.
     * @bodyParam serviceIds int[] required An array of services. Example: [1,2,3]
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'serviceIds' => 'required|array',
        ]);

        $question = new Question;
        $question->title = $validatedData['title'];
        $question->serviceIds = $validatedData['serviceIds'];
        $question->save();


        $response = [
            'message' => 'Created Successfully ',
            'data' => $question,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified question.
     *
     * @group Question Management
     *
     * @urlParam question required The ID of the question to display. Example: 1
     *
     * @param  Question $question
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $question = Question::findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $question,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified question.
     *
     * @group Question Management
     *
     * @urlParam question required The ID of the question to update. Example: 1
     * @bodyParam title string required.
     * @bodyParam serviceIds int[] required An array of services. Example: [1,2,3]
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Question $question
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'serviceIds' => 'required|array',
        ]);
        $question = Question::findOrFail($id);

        $question->title = $request->title;
        $question->serviceIds = $validatedData['serviceIds'];
        $question->save();

        $response = [
            'message' => 'Update Successfully ',
            'data' => $question,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified question from storage.
     *
     * @group Question Management
     *
     * @urlParam question required The ID of the question to delete. Example: 1
     * @param  Question $question
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

