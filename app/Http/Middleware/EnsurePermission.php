<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\Message;

class EnsurePermission
{
    /**
     * Map Laravel default actions to required Permission names
     */
    protected array $actionMap = [
        'index'   => 'FINDALL',
        'show'    => 'FIND',
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

        // 2. Get the current action name from the Route (e.g., App\Http\Controllers\UserController@index)
        $routeAction = $request->route()->getActionName();

        // Skip if the route is a Closure function (not using a Controller)
        if ($routeAction === 'Closure') {
            return $next($request);
        }

        // 3. Extract Controller and Method names
        $classBasename = class_basename($routeAction); // Result: UserController@index
        list($controllerClass, $method) = explode('@', $classBasename);

        // 4. Format the Module name from the Controller (e.g., UserController -> USER)
        $module = strtoupper(str_replace('Controller', '', $controllerClass));

        // 5. Format the Action name (use actionMap if exists, otherwise uppercase the method name)
        $mappedAction = $this->actionMap[$method] ?? strtoupper($method);

        // 6. Combine to form the required Permission name (e.g., USER_FINDALL)
        $requiredPermission = $module . '_' . $mappedAction;
        
        if (!$user->role) {
            return response()->json([
                'message' => Message::NO_ROLE_ASSIGNED
            ], 403);
        }

        // 7. Check if the User's Role contains this Permission
        // Note: Ensure the 'permissions()' relationship is properly defined in the Role Model
        $hasPermission = $user->role->permissions->contains('name', $requiredPermission);

        if (!$hasPermission) {
            return response()->json([
                'message' => Message::FORBIDDEN . $requiredPermission
            ], 403);
        }

        return $next($request);
    }
}