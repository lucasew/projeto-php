<?php
require "prelude.php";

function is_get() {
    return !empty($_GET);
}

function is_post() {
    return !empty($_POST);
}

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

    // var_dump($params_parts);
    // var_dump($route_parts);

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

use_route("/inspect", "routes/inspect.php");
exact_route("/healthcheck", "routes/healthcheck.php");
exact_route("/dbdemo", "routes/dbdemo.php");

exact_with_route_param("/say/:word/", "routes/paramtest.php");
exact_with_route_param("/say/:word/wednesday", "routes/paramtest.php");
// fallback
respond_error(404, "page not found");

?>