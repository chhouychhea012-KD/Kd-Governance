<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Http\Resources\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    public function index()
    {
        return TeamResource::collection(Team::with('parent')->get());
    }

    /**
     * Get only parent teams (teams without parent_id)
     */
    public function getParentTeams()
    {
        return TeamResource::collection(Team::whereNull('parent_id')->orderBy('name')->get());
    }

    /**
     * Get parent teams with their children (nested structure)
     */
    public function getParentTeamsWithChildren()
    {
        $parentTeams = Team::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return TeamResource::collection($parentTeams);
    }

    /**
     * Get sub-teams for a specific parent team
     */
    public function getSubTeams($parentId)
    {
        return TeamResource::collection(Team::where('parent_id', $parentId)->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:teams,id',
        ]);

        // Prevent self-reference
        if ($request->parent_id && (int) $request->parent_id === 0) {
            $validated['parent_id'] = null;
        }

        $team = Team::create($validated);
        return new TeamResource($team);
    }

    public function show(Team $team)
    {
        $team->load('parent');
        return new TeamResource($team);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:teams,id',
        ]);

        $team = Team::findOrFail($id);

        // Prevent circular reference (team can't be its own parent)
        if ($team->id === (int) $request->parent_id) {
            return response()->json(['error' => 'A team cannot be its own parent'], 422);
        }

        // Handle parent_id = 0 or null
        if (!$request->parent_id || (int) $request->parent_id === 0) {
            $validated['parent_id'] = null;
        }

        $team->update($validated);

        return new TeamResource($team);
    }

    public function destroy($id)
    {
        $team = Team::findOrFail($id);

        $team->delete();

        return response()->noContent(); // 204
    }
}