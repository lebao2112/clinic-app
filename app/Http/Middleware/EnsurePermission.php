<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\Message; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EnsurePermission
{
    /**
     * Map Laravel default actions to required Permission names based on specs
     */
    protected array $actionMap = [
        'index'   => 'FINDALL',
        'show'    => 'FINDONE',
        'store'   => 'CREATE',
        'update'  => 'UPDATE',
        'destroy' => 'DELETE',
        'changeStatus' => 'UPDATESTATUS',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Check if the user is authenticated 
        if (!$user) {
            return response()->json(['message' => Message::UNAUTHORIZED], 401);
        }

        $user->unsetRelation('role');
        if ($user->role) {
            $user->role->unsetRelation('permissions');
        }

        // 2. Get the current action name from the Route
        $routeAction = $request->route()->getActionName();

        if ($routeAction === 'Closure') {
            return $next($request);
        }

        // 3. Extract Controller and Method names
        $classBasename = class_basename($routeAction);
        list($controllerClass, $method) = explode('@', $classBasename);

        // 4. Format the Module name (e.g., PatientController -> PATIENTS)
        $modelName = str_replace('Controller', '', $controllerClass);
        
        if ($modelName === 'Stats') {
            $requiredPermission = 'STATS.SHOW';
        } else {
            $module = strtoupper(Str::plural($modelName));
            // 5. Format the Action name using the spec map
            $mappedAction = $this->actionMap[$method] ?? strtoupper($method);
            // 6. Combine to form the required Permission name
            $requiredPermission = $module . '.' . $mappedAction;
        }
        
        if (!$user->role_id) {
            return response()->json([
                'message' => Message::NO_ROLE_ASSIGNED
            ], 403);
        }

        $hasPermission = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role_id', $user->role_id)
            ->where('permissions.name', $requiredPermission)
            ->exists();

        if (!$hasPermission) {
            return response()->json([
                'message' => Message::FORBIDDEN . $requiredPermission
            ], 403);
        }

        return $next($request);
    }
}