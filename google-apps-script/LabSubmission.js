/**
 * Google Apps Script — Lab Utilization System Integration
 * =========================================================
 *
 * Replace your existing Google Form Apps Script with this file.
 *
 * SETUP:
 * 1. Open your Google Form → ⋮ menu → Script editor
 * 2. Paste this entire file (replace existing code)
 * 3. Project Settings → Script Properties → Add:
 *      LARAVEL_API_URL  = https://your-domain.com/api/submission
 *      LARAVEL_API_KEY  = (same value as API_SECRET_KEY in your .env)
 * 4. Triggers → Add Trigger → onFormSubmit → "On form submit"
 *
 * GOOGLE FORM QUESTIONS (Phase 1):
 * Your form should have these fields:
 *   - "First Name"         → faculty first name
 *   - "Room to Use"        → dropdown (LAB 104, LAB 105, ... LEC 306)
 *   - "Subject"            → the class/subject (e.g. "CC 102")
 *
 * Edit the FIELD_MAP below to match your exact question titles.
 */

// ─────────────────────────────────────────────────────────────────────────────
// CONFIGURATION — Edit these to match your Google Form question titles exactly
// ─────────────────────────────────────────────────────────────────────────────

var FIELD_MAP = {
  // Google Form question title         →  Laravel field name
  "First Name":                            "borrower_name",
  "Room to Use":                           "room_name",
  "Subject":                               "subject",

  // Optional fields — remove or leave as-is if not in your form:
  "Transaction ID to Return":              "return_for_id",   // for return events
  "Tool / Equipment to Borrow":            "tool_name",       // if you add tool checkout later
  "Notes":                                 "notes",
};

// What value in your form indicates a "room checkout"?
// If your form has a "Transaction Type" field, set this:
var TYPE_FIELD = "";   // e.g. "Transaction Type" — leave empty if all submissions are room checkouts

// Default type when no TYPE_FIELD is set
var DEFAULT_TYPE = "room_checkout";

// ─────────────────────────────────────────────────────────────────────────────
// Main trigger — fires on every Google Form submission
// ─────────────────────────────────────────────────────────────────────────────

function onFormSubmit(e) {
  try {
    var payload = buildPayload(e);
    var result  = postToLaravel(payload);

    // Keep writing to Sheets as a backup during the transition period.
    // When Laravel is confirmed stable, you can comment out this line.
    logToSheet(e, result);

    Logger.log("Laravel response: " + JSON.stringify(result));

  } catch (err) {
    Logger.log("ERROR in onFormSubmit: " + err.toString());
    // Uncomment to receive error emails:
    // MailApp.sendEmail("your-email@pup.edu.ph", "Lab Form Script Error", err.toString());
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Build the payload for the Laravel API
// ─────────────────────────────────────────────────────────────────────────────

function buildPayload(e) {
  var responses = e.response.getItemResponses();

  // Build answer lookup: { "Question Title": "Answer" }
  var answers = {};
  for (var i = 0; i < responses.length; i++) {
    var title = responses[i].getItem().getTitle();
    var value = responses[i].getResponse();
    answers[title] = value;
  }

  // Map using FIELD_MAP
  var payload = {};
  for (var question in FIELD_MAP) {
    var laravelField = FIELD_MAP[question];
    if (answers.hasOwnProperty(question) && answers[question]) {
      payload[laravelField] = answers[question];
    }
  }

  // Determine submission type
  if (TYPE_FIELD && answers[TYPE_FIELD]) {
    var typeMap = {
      "Room Checkout":  "room_checkout",
      "Tool Checkout":  "tool_checkout",
      "Return":         "return",
    };
    payload["type"] = typeMap[answers[TYPE_FIELD]] || DEFAULT_TYPE;
  } else {
    payload["type"] = DEFAULT_TYPE;
  }

  // Convert return_for_id to integer if present
  if (payload["return_for_id"]) {
    payload["return_for_id"] = parseInt(payload["return_for_id"]) || null;
  }

  Logger.log("Payload: " + JSON.stringify(payload));
  return payload;
}

// ─────────────────────────────────────────────────────────────────────────────
// POST to Laravel API
// ─────────────────────────────────────────────────────────────────────────────

function postToLaravel(payload) {
  var props  = PropertiesService.getScriptProperties();
  var apiUrl = props.getProperty("LARAVEL_API_URL");
  var apiKey = props.getProperty("LARAVEL_API_KEY");

  if (!apiUrl || !apiKey) {
    throw new Error("LARAVEL_API_URL or LARAVEL_API_KEY not set in Script Properties.");
  }

  var options = {
    method:             "post",
    contentType:        "application/json",
    headers: {
      "X-Api-Key": apiKey,
      "Accept":    "application/json",
    },
    payload:            JSON.stringify(payload),
    muteHttpExceptions: true,
  };

  var response   = UrlFetchApp.fetch(apiUrl, options);
  var statusCode = response.getResponseCode();
  var body;

  try {
    body = JSON.parse(response.getContentText());
  } catch (e) {
    body = { raw: response.getContentText() };
  }

  if (statusCode >= 400) {
    Logger.log("⚠️ Laravel API error " + statusCode + ": " + JSON.stringify(body));
  } else {
    Logger.log("✅ Recorded! Transaction ID: " + (body.transaction_id || "?"));
  }

  return { status: statusCode, body: body };
}

// ─────────────────────────────────────────────────────────────────────────────
// Backup: write to Google Sheets (keep during transition)
// ─────────────────────────────────────────────────────────────────────────────

function logToSheet(e, apiResult) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();

    var laravelStatus = apiResult.status === 201 ? "✅ Saved" : "⚠️ Error " + apiResult.status;
    var txId          = (apiResult.body && apiResult.body.transaction_id) ? apiResult.body.transaction_id : "—";

    var row = [new Date(), laravelStatus, txId];
    var responses = e.response.getItemResponses();
    for (var i = 0; i < responses.length; i++) {
      row.push(responses[i].getResponse());
    }

    sheet.appendRow(row);
  } catch (err) {
    // Don't let a Sheets error break the whole script
    Logger.log("Sheets logging failed: " + err.toString());
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// ROOM LIST — for Google Form dropdown reference
// ─────────────────────────────────────────────────────────────────────────────
// Your Google Form "Room to Use" dropdown choices must match EXACTLY:
//
//  Laboratory Rooms:     LAB 104, LAB 105, LAB 109B, LAB 109C
//                        LAB 203, LAB 204, LAB 205, LAB 208
//
//  Lecture Rooms:        LEC 200, LEC 201, LEC 209, LEC 210,
//                        LEC 211, LEC 212, LEC 213
//                        LEC 301, LEC 303, LEC 304, LEC 305, LEC 306
