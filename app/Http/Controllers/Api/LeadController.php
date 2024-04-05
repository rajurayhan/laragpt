<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

/**
 * @group Leads
 * @authenticated
 *
 * APIs for managing leads.
 */
class LeadController extends Controller
{
    /**
     * Get all Leads
     *
     * Get a list of all Leads.
     *
     * @queryParam firstName string Filter by first name.
     * @queryParam lastName string Filter by last name.
     * @queryParam company string Filter by company.
     * @queryParam phone string Filter by phone.
     * @queryParam email string Filter by email.
     * @queryParam projectTypeId string Filter by projectTypeId.
     * @queryParam per_page integer Number of items per page.
     * @queryParam page integer page number.
     *
     */
    public function index(Request $request)
    {
        try {
            $query = Lead::query();

            if ($request->filled('firstName')) {
                $query->where('firstName', 'like', '%' . $request->input('firstName') . '%');
            }

            if ($request->filled('lastName')) {
                $query->where('lastName', 'like', '%' . $request->input('lastName') . '%');
            }

            if ($request->filled('company')) {
                $query->where('company', 'like', '%' . $request->input('company') . '%');
            }

            if ($request->filled('phone')) {
                $query->where('phone', 'like', '%' . $request->input('phone') . '%');
            }

            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            }
            if ($request->filled('projectTypeId')) {
                $query->where('projectTypeId', $request->input('projectTypeId'));
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified

            $leads = $query->with('projectType')->latest()->paginate($perPage);

            return response()->json([
                'data' => $leads->items(),
                'total' => $leads->total(),
                'current_page' => $leads->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching leads', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Lead
     *
     * Get details of a specific Lead.
     *
     * @urlParam id required The ID of the Lead. Example: 1
     *
     */
    public function show($id)
    {
        try {
            $lead = Lead::with('projectType')->find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $lead
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching lead details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Lead
     *
     * Create a new Lead.
     *
     * @bodyParam firstName string required The first name of the Lead. Example: John
     * @bodyParam lastName string required The last name of the Lead. Example: Doe
     * @bodyParam company string required The company of the Lead. Example: ABC Inc.
     * @bodyParam phone string required The phone number of the Lead. Example: +1234567890
     * @bodyParam email string required The email of the Lead. Example: john.doe@example.com
     * @bodyParam projectTypeId integer required The type of the project.
     * @bodyParam description text nullable The description of the Lead.
     *
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'company' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'projectTypeId' => 'required|integer|exists:project_types,id',
            'description' => 'nullable|string',
        ]);

        $lead = Lead::create($validatedData);

        $response = [
            'message' => 'Created Successfully',
            'data' => $lead->load('projectType')
        ];

        return response()->json($response, 201);
    }

    /**
     * Update a Lead
     *
     * Update details of a specific lead.
     *
     * @urlParam id required The ID of the lead. Example: 1
     *
     * @bodyParam firstName string required The first name of the lead. Example: Updated John
     * @bodyParam lastName string required The last name of the lead. Example: Updated Doe
     * @bodyParam company string required The company of the lead. Example: Updated ABC Inc.
     * @bodyParam phone string required The phone number of the lead. Example: +1234567890
     * @bodyParam email string required The email of the lead. Example: updated.john.doe@example.com
     * @bodyParam projectTypeId integer required The type of the project.
     * @bodyParam description text nullable The description of the lead.
     *
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'company' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'projectTypeId' => 'required|integer|exists:project_types,id',
            'description' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update($validatedData);

        $response = [
            'message' => 'Updated Successfully',
            'data' => $lead
        ];

        return response()->json($response, 200);
    }

    /**
     * Delete a Lead
     *
     * Delete a specific lead.
     *
     * @urlParam id required The ID of the lead. Example: 1
     *
     * @response {
     *  "message": "Lead deleted successfully"
     * }
     */
    public function destroy($id)
    {
        try {
            $lead = Lead::findOrFail($id);
            $lead->delete();

            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting lead', 'error' => $e->getMessage()], 500);
        }
    }
}
