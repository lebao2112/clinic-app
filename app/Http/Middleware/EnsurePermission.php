<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\Message; 
use Illuminate\Support\Str;

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
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Check if the user is authenticated 
        if (!$user) {
            return response()->json(['message' => Message::UNAUTHORIZED], 401);
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
        $module = strtoupper(Str::plural($modelName));

        // 5. Format the Action name using the spec map
        $mappedAction = $this->actionMap[$method] ?? strtoupper($method);

        // 6. Combine to form the required Permission name (e.g., PATIENTS.FINDALL, PATIENTS.CREATE)
        $requiredPermission = $module . '.' . $mappedAction;
        
        if (!$user->role) {
            return response()->json([
                'message' => Message::NO_ROLE_ASSIGNED
            ], 403);
        }

        // 7. Check if the User's Role contains this Permission
        $hasPermission = $user->role->permissions->contains('name', $requiredPermission);

        if (!$hasPermission) {
            return response()->json([
                'message' => Message::FORBIDDEN . $requiredPermission
            ], 403);
        }

        return $next($request);
    }
}