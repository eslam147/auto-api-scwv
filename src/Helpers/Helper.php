<?php

if(!function_exists('render'))
{
    function render($view = null, $data = [])
    {
        $guards = array_filter(array_keys(config('auth.guards')));
        $data['auth'] = '';
        foreach($guards as $guard)
        {
            if(auth()->guard($guard)->check())
            {
                if($guard == 'web')
                {
                    $data['auth']['user'] = auth()->user();
                }
                else
                {
                    
                    if(!in_array($guard ,config('auth.gitignore')))
                    {
                        $data['auth'][$guard] = auth()->guard($guard)->user();
                    }
                }
            }
        }
        if(request()->ajax() || request()->is('api/*') || request()->is('api'))
        {
            $view = view($view, $data);
            return response()->json([
                'props' => $data,
                'view' => $view->render()
            ]);
        }
        return view($view, $data);
    }
}

if (!function_exists('response_with_success')) {
    function response_with_success($message, $redirectUrl = null, $data = [])
    {
        // التحقق من نوع الطلب
        if (request()->ajax() || request()->is('api/*') || request()->is('api')) {
            // رد JSON للـ API
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
        }
        // إعادة التوجيه للطلبات العادية
        if ($redirectUrl) {
            return redirect($redirectUrl)->with('success', $message)->with($data);
        }
        // رد عادي بدون إعادة توجيه
        return back()->with('success', $message)->with($data);
    }
}

if (! function_exists('routeApi')) {
    /**
     * Get the URL for a named route depending on whether the request is from API or not.
     *
     * @param string $name
     * @param array  $parameters
     * @return string
     */
    function routeApi($name, $parameters = [])
    {
        $isApiRequest = request()->is('api/*') || request()->is('api') ? true : false;
        $explode = explode(".", $name);
        if (!$isApiRequest && current($explode) != 'api') {
            $route = route($name, $parameters);
            $route = str_replace('/api', '', $route);
            return $route;
        }
        return route($name, $parameters);
    }
}
