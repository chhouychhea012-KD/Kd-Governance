<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        // Get target_score from general_settings table
        $targetScore = GeneralSetting::where('key', 'target-score')->value('value') ?? 0;

        return response()->json([
            'totalUsers' => User::count(),
            'totalTeams' => Team::count(),
            'targetScore' => (float)$targetScore
        ]);
    }

    public function teamsWithUsers()
    {
        // Helper to populate the selects in frontend
        // Only return top-level teams (teams without parent_id)
        return response()->json(Team::with('users:id,name,team_id')->whereNull('parent_id')->get());
    }

    public function chartData(Request $request)
    {
        $userId = $request->user_id;
        $year = $request->year ?? now()->year;

        if (!$userId) {
            return response()->json(['error' => 'User ID required'], 400);
        }

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $scoreData = array_fill(0, 12, 0);
        $improveCountData = array_fill(0, 12, 0);

        return response()->json([
            'labels' => $labels,
            'scores' => $scoreData,
            'improvements' => $improveCountData,
            'improvementList' => []
        ]);
    }

    public function recentEvaluations(Request $request)
    {
        return response()->json([
            'data' => [],
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 10,
            'total' => 0
        ]);
    }
}