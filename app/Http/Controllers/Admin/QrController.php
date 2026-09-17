<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tool;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Admin QR Code Controller
 *
 * Generates printable QR code sheets for rooms and tools.
 * Lab Head prints these once and posts them physically:
 *   - Room QR codes → on the door of each room
 *   - Tool QR codes → taped to the tool or stored with it
 *
 * Routes (all lab_head only):
 *   GET /admin/qr/rooms       → printable grid of all room QR codes
 *   GET /admin/qr/tools       → printable grid of all tool QR codes
 *   GET /admin/qr/room/{room} → single room QR (for reprinting one)
 *   GET /admin/qr/tool/{tool} → single tool QR (for reprinting one)
 */
class QrController extends Controller
{
    /**
     * Printable QR sheet for all active rooms.
     * Use browser Print → "Print to PDF" or Ctrl+P.
     */
    public function rooms(): View
    {
        $rooms = Room::orderByRaw("
            CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END,
            name
        ")->get();

        return view('admin.qr.rooms', compact('rooms'));
    }

    /**
     * Printable QR sheet for all active tools.
     */
    public function tools(): View
    {
        $tools = Tool::active()
            ->with('room')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('admin.qr.tools', compact('tools'));
    }

    /**
     * Single room QR printout (for reprinting one specific room).
     */
    public function singleRoom(Room $room): View
    {
        return view('admin.qr.single-room', compact('room'));
    }

    /**
     * Single tool QR printout.
     */
    public function singleTool(Tool $tool): View
    {
        return view('admin.qr.single-tool', compact('tool'));
    }
}
