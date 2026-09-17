<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates POST requests from Google Apps Script (Google Form submissions).
 *
 * Google Form fields (Phase 1):
 *   1. First Name (of faculty using the room)
 *   2. Room to Use   (dropdown matching room names in the rooms table)
 *   3. Subject       (the class/subject they're using the room for)
 *
 * Expected JSON payload from Apps Script:
 * {
 *   "type":            "room_checkout" | "tool_checkout" | "return",
 *   "borrower_name":   "Juan",
 *   "room_name":       "LAB 104",
 *   "subject":         "CC 102 - Programming Fundamentals",
 *   "tool_name":       "HDMI Cable #1",    // only for tool_checkout
 *   "quantity":        1,                  // optional, defaults to 1
 *   "expected_return": "2024-01-01 17:00", // optional
 *   "notes":           "...",              // optional
 *   "return_for_id":   12                  // required for type=return
 * }
 *
 * API authentication: checked via X-Api-Key header in SubmissionController.
 */
class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Submission type
            'type' => ['required', 'string', 'in:room_checkout,tool_checkout,return'],

            // Faculty first name (from Google Form field "First Name")
            'borrower_name'  => ['required', 'string', 'max:255'],

            // Faculty email (optional — not in the basic form but can be added later)
            'borrower_email' => ['nullable', 'email', 'max:255'],

            // Subject / class the room is being used for (from Google Form field "Subject")
            'subject' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,room_checkout',
            ],

            // Room name — must match a room name in the database exactly
            'room_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,room_checkout',
            ],

            // Tool name — must match a tool name in the database exactly
            'tool_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,tool_checkout',
            ],

            // How many units of the tool to borrow (defaults to 1)
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Expected return time
            'expected_return' => ['nullable', 'date'],

            // Free text notes
            'notes' => ['nullable', 'string', 'max:2000'],

            // Transaction ID being closed (required for return events)
            'return_for_id' => [
                'nullable',
                'integer',
                'exists:transactions,id',
                'required_if:type,return',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'                   => 'Invalid type. Must be room_checkout, tool_checkout, or return.',
            'subject.required_if'       => 'Subject is required for room checkout.',
            'room_name.required_if'     => 'room_name is required for room_checkout.',
            'tool_name.required_if'     => 'tool_name is required for tool_checkout.',
            'return_for_id.required_if' => 'return_for_id is required for return submissions.',
            'return_for_id.exists'      => 'The referenced transaction does not exist.',
        ];
    }
}
