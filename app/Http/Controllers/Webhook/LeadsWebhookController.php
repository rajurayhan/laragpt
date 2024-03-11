<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\ProjectType;
use Illuminate\Http\Request;

class LeadsWebhookController extends Controller
{
    public function handleLHGLeadWebookData(Request $request){
        $webhookData = $request->all();

        // \Log::info(["webhookData" => $webhookData]);
        // if($webhookData['status'] == 'spam'){
        //     return response()->json(['error' => 'Smap Detected'], 409);
        // }

        // Extract relevant information from the webhook data
        $firstName = $webhookData['field_values'][1] ?? null;
        $lastName = $webhookData['field_values'][9] ?? null;
        $company = $webhookData['field_values'][2] ?? null;
        $email = $webhookData['field_values'][8] ?? null;
        $phone = $webhookData['field_values'][37] ?? null;
        $projectType = $webhookData['field_values'][33] ?? null;
        $description = $webhookData['field_values'][6] ?? null;

        if(!isset($email)){
            return response()->json(['error' => 'Data Not valid'], 409);
        }

        // Check if a lead with the given email already exists
        $existingLead = Lead::where('email', $email)->first();

        if ($existingLead) {
            return response()->json(['message' => 'Lead with the given email already exists.'], 409);
        }

        $projectTypeData = ProjectType::where('name', $projectType)->first();

        // Create a new lead
        $lead = new Lead();
        $lead->firstName = $firstName;
        $lead->lastName = $lastName;
        $lead->company = $company;
        $lead->email = $email;
        $lead->phone = $phone;
        $lead->projectTypeId = $projectTypeData ? $projectTypeData->id : null;
        $lead->description = $description;

        // Save the lead to the database
        $lead->save();

        // \Log::info(["Lead" => $lead]);

        // You can perform additional actions or return a response here
        return response()->json(['message' => 'Lead Saved successfully.'], 201);
    }
}
