<?php

namespace Chaos\Support\Middleware;

use Illuminate\Support\Str;

/**
 * Class LaravelConvertCaseMiddleware.
 *
 * @author t(-.-t) <ntd1712@mail.com>
 */
class LaravelConvertCaseMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request.
     * @param \Closure $next Closure.
     * @param string|array $options Optional.
     *
     * @return mixed
     */
    public function handle($request, $next, $options = [])
    {
        $now = now(config('app.timezone'));
        $user = $request->user();
        $route = $request->route();
        $input = $request->input();

        switch ($request->getMethod()) {
            case 'POST':
                $input['created_at'] = $now;
                $input['created_by'] = isset($user) ? @$user->name : null;
                $input['app_key'] = config('app.key');
                break;
            case 'PUT':
                $input['updated_at'] = $now;
                $input['updated_by'] = isset($user) ? @$user->name : null;
                $input['id'] = $route->parameters[$route->parameterNames[0]];
                break;
            case 'PATCH':
                $input['deleted_at'] = $now;
                $input['deleted_by'] = isset($user) ? @$user->name : null;
                $input['id'] = $route->parameters[$route->parameterNames[0]];
                break;
            default:
        }

        if (!empty($options)) {
            switch ($options) {
                case 'camel':
                    foreach ($input as $key => $value) {
                        if (is_array($value)) {
                            for ($i = 0, $count = count($value); $i < $count; $i++) {
                                foreach ($value[$i] as $k => $v) {
                                    $value[$i][Str::camel($k)] = $v;
                                }
                            }
                        }

                        $input[Str::camel($key)] = $value;
                    }
                    break;
                case 'kebab':
                    foreach ($input as $key => $value) {
                        if (is_array($value)) {
                            for ($i = 0, $count = count($value); $i < $count; $i++) {
                                foreach ($value[$i] as $k => $v) {
                                    $value[$i][Str::kebab(Str::camel($k))] = $v;
                                }
                            }
                        }

                        $input[Str::kebab(Str::camel($key))] = $value;
                    }
                    break;
                case 'pascal':
                    foreach ($input as $key => $value) {
                        if (is_array($value)) {
                            for ($i = 0, $count = count($value); $i < $count; $i++) {
                                foreach ($value[$i] as $k => $v) {
                                    $value[$i][ucfirst(Str::camel($k))] = $v;
                                }
                            }
                        }

                        $input[ucfirst(Str::camel($key))] = $value;
                    }
                    break;
                case 'snake':
                    foreach ($input as $key => $value) {
                        if (is_array($value)) {
                            for ($i = 0, $count = count($value); $i < $count; $i++) {
                                foreach ($value[$i] as $k => $v) {
                                    $value[$i][Str::snake($k)] = $v;
                                }
                            }
                        }

                        $input[Str::snake($key)] = $value;
                    }
                    break;
                default:
            }
        }

        $request->replace($input);

        return $next($request);
    }
}
