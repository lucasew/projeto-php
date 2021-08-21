<?php
require "prelude.php";


function is_empty_string(string $s) {
    return $s == "";
}

$input_data = array_merge_recursive($_GET, $_POST);
define("ROUTE", parse_url($_SERVER["REQUEST_URI"])["path"]);

/**
 * create a route that matches anything starting with $base_route
 */
function use_route(string $base_route, string $handler_script) {
    if (str_starts_with(ROUTE, $base_route)) {
        require $handler_script;
    }
}

/**
 * create a route that matches exactly $selected_route
 */
function exact_route(string $selected_route, string $handler_script) {
    if (strcmp(ROUTE, $selected_route) == 0) {
        require $handler_script;
    }
}

/**
 * create a route with route params, like /users/:user/info 
 * and pass the route param with input_data
 */
function exact_with_route_param(string $selected_route, string $handler_script) {
    global $input_data;
    $preprocess = function (string $raw_route) {
        $splitted = preg_split("/\//", $raw_route);
        $splitted = array_filter($splitted, function($v, $k) {
            return !is_empty_string($v);
        }, ARRAY_FILTER_USE_BOTH);
        return array_values($splitted);
    };
    $params_parts = $preprocess($selected_route);
    $route_parts = $preprocess(ROUTE);
    log_httpd(json_encode($params_parts));
    log_httpd(json_encode($route_parts));

    $extra_params = [];
    if (count($params_parts) == count($route_parts)) {
        for ($i = 0; $i < count($params_parts); $i++) {
            if (str_starts_with($params_parts[$i], ":")) {
                $extra_params[substr($params_parts[$i], 1)] = $route_parts[$i];
            } else {
                if (strcmp($params_parts[$i], $route_parts[$i]) != 0) {
                    return;
                }
            }
        }
    } else {
        return;
    }
    $input_data = array_merge_recursive($input_data, $extra_params);
    require $handler_script;
}

// healthcheck
exact_route("/healthcheck", "routes/healthcheck.php");

// demos
exact_with_route_param("/demo/say/:word/", "routes/demo/say.php");
exact_with_route_param("/demo/say/:word/wednesday", "routes/demo/say.php");
exact_route("/demo/db", "routes/demo/db.php");
use_route("/demo/inspect", "routes/demo/inspect.php");

// fallback
respond_error(404, "page not found");

?>